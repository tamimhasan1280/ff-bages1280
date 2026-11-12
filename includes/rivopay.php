<?php
/**
 * TURKY HUB — RivoPay / TurkyHubPay Server-Side Gateway Driver
 * Dynamically loads Brand API Key & Base URL from Admin Panel settings (gateway_settings table).
 * Never exposes API-KEY to client-side HTML/JS and strictly verifies transactions server-side.
 */

require_once __DIR__ . '/saas_db.php';

function rivopay_get_config(): array {
    $pdo = saas_db();
    $stmt = $pdo->prepare("SELECT * FROM gateway_settings WHERE gateway_code = 'rivopay' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch();
    if (!$row) {
        return [
            'gateway_code' => 'rivopay',
            'status' => 0,
            'display_name' => 'RivoPay (bKash / Nagad / Rocket)',
            'base_url' => 'https://checkout.rivopay.shop',
            'brand_key' => '',
            'currency' => 'BDT',
            'exchange_rate' => 1.0,
        ];
    }
    return [
        'gateway_code' => (string)$row['gateway_code'],
        'status' => (int)$row['status'],
        'display_name' => (string)$row['display_name'],
        'base_url' => rtrim((string)$row['base_url'], '/'),
        'brand_key' => (string)$row['brand_key'],
        'currency' => (string)($row['currency'] ?: 'BDT'),
        'exchange_rate' => max(0.0001, (float)($row['exchange_rate'] ?: 1.0)),
        'updated_at' => (int)($row['updated_at'] ?? 0),
    ];
}

function rivopay_create_url(array $cfg): string {
    return rtrim($cfg['base_url'], '/') . '/api/payment/create';
}

function rivopay_verify_url(array $cfg): string {
    return rtrim($cfg['base_url'], '/') . '/api/payment/verify';
}

function rivopay_http_post(string $url, string $apiKey, array $params): array {
    $body = http_build_query($params);
    $headers = [
        'API-KEY: ' . $apiKey,
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 12,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $err !== '') {
            return ['ok' => false, 'http' => $httpCode, 'error' => 'Gateway connection error: ' . ($err ?: 'Empty response'), 'raw' => ''];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'http' => $httpCode, 'error' => 'Invalid JSON returned by gateway (HTTP ' . $httpCode . ')', 'raw' => substr($raw, 0, 400)];
        }
        return ['ok' => true, 'http' => $httpCode, 'data' => $decoded, 'raw' => $raw];
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers) . "\r\n",
            'content' => $body,
            'timeout' => 25,
            'ignore_errors' => true,
        ],
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        return ['ok' => false, 'http' => 0, 'error' => 'Unable to reach RivoPay endpoint.', 'raw' => ''];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'http' => 0, 'error' => 'Invalid JSON from gateway.', 'raw' => substr($raw, 0, 400)];
    }
    return ['ok' => true, 'http' => 200, 'data' => $decoded, 'raw' => $raw];
}

function rivopay_create_payment(array $order, array $user, array $plan, string $origin): array {
    $cfg = rivopay_get_config();
    if ((int)$cfg['status'] !== 1) {
        return ['ok' => false, 'error' => 'Payment gateway is currently disabled by administrator.'];
    }
    if ($cfg['base_url'] === '' || $cfg['brand_key'] === '') {
        return ['ok' => false, 'error' => 'RivoPay Gateway URL or Brand API Key is not configured in Admin Panel.'];
    }

    $payableBdt = round((float)$order['amount_bdt'] * (float)$cfg['exchange_rate'], 2);
    if ($payableBdt <= 0 || $payableBdt > 1000000) {
        return ['ok' => false, 'error' => 'Order amount must be between 0.01 and 1,000,000 BDT.'];
    }

    $orderRef = urlencode((string)$order['order_ref']);
    $successUrl = rtrim($origin, '/') . '/payment/rivopay/callback?order_id=' . $orderRef;
    $cancelUrl = rtrim($origin, '/') . '/payment/rivopay/callback?cancelled=1&order_id=' . $orderRef;
    $webhookUrl = rtrim($origin, '/') . '/payment/rivopay/webhook?order_id=' . $orderRef;

    $cusName = trim((string)($user['name'] ?? 'Customer'));
    if (!empty($user['phone'])) {
        $cusName .= ' (' . trim((string)$user['phone']) . ')';
    }
    $cusEmail = trim((string)($user['email'] ?? ''));
    if ($cusEmail === '') {
        $cusEmail = 'customer@example.com';
    }

    $payload = [
        'amount' => number_format($payableBdt, 2, '.', ''),
        'cus_name' => $cusName,
        'cus_email' => $cusEmail,
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        'webhook_url' => $webhookUrl,
        'metadata' => json_encode([
            'order_ref' => (string)$order['order_ref'],
            'user_id' => (int)$user['id'],
            'plan_id' => (int)$plan['id'],
            'plan_slug' => (string)($plan['slug'] ?? ''),
        ], JSON_UNESCAPED_SLASHES),
    ];

    $res = rivopay_http_post(rivopay_create_url($cfg), $cfg['brand_key'], $payload);
    if (!$res['ok']) {
        @error_log('[RivoPay Create Error] ' . ($res['error'] ?? 'Unknown'));
        return ['ok' => false, 'error' => $res['error'] ?? 'Failed to initialize RivoPay payment.'];
    }

    $data = $res['data'];
    $statusOk = isset($data['status']) && ((int)$data['status'] === 1 || $data['status'] === true);
    $payUrl = trim((string)($data['payment_url'] ?? ''));
    if (!$statusOk || $payUrl === '') {
        $msg = (string)($data['message'] ?? 'Gateway did not return a payment URL.');
        @error_log('[RivoPay Create Rejected] ' . $msg . ' raw=' . substr((string)($res['raw'] ?? ''), 0, 200));
        return ['ok' => false, 'error' => $msg];
    }

    return [
        'ok' => true,
        'payment_url' => $payUrl,
        'gateway_message' => (string)($data['message'] ?? 'Payment Link'),
        'payable_bdt' => $payableBdt,
    ];
}

function rivopay_verify_transaction(string $transactionId): array {
    $cfg = rivopay_get_config();
    if ($cfg['base_url'] === '' || $cfg['brand_key'] === '') {
        return ['ok' => false, 'error' => 'Gateway credentials missing.'];
    }
    $transactionId = trim($transactionId);
    if ($transactionId === '') {
        return ['ok' => false, 'error' => 'Missing transaction_id.'];
    }

    $res = rivopay_http_post(rivopay_verify_url($cfg), $cfg['brand_key'], [
        'transaction_id' => $transactionId,
    ]);
    if (!$res['ok']) {
        @error_log('[RivoPay Verify Error] ' . ($res['error'] ?? 'Unknown'));
        return ['ok' => false, 'error' => $res['error'] ?? 'Verification failed.'];
    }

    return ['ok' => true, 'data' => $res['data'], 'config' => $cfg];
}

/**
 * Mandatory server-side verification + atomic idempotent order completion.
 */
function rivopay_complete_order(string $orderRef, string $transactionId, string $hintMethod = '', float $hintFee = 0.0): array {
    $pdo = saas_db();
    $orderRef = trim($orderRef);
    $transactionId = trim($transactionId);

    if ($orderRef === '') {
        return ['ok' => false, 'state' => 'ERROR', 'message' => 'Missing local order reference.'];
    }

    // Read order first for expected amount & fast-path idempotency
    $stmt = $pdo->prepare("SELECT * FROM payment_orders WHERE order_ref = ? LIMIT 1");
    $stmt->execute([$orderRef]);
    $preOrder = $stmt->fetch();
    if (!$preOrder) {
        return ['ok' => false, 'state' => 'ERROR', 'message' => 'Order reference not found: ' . $orderRef];
    }
    if ($preOrder['status'] === 'paid') {
        return ['ok' => true, 'state' => 'COMPLETED', 'already_processed' => true, 'order' => $preOrder, 'message' => 'Payment already verified & activated.'];
    }

    if ($transactionId === '') {
        return ['ok' => false, 'state' => 'ERROR', 'message' => 'Missing gateway transactionId for verification.'];
    }

    // Perform mandatory server-side /api/payment/verify call BEFORE mutating DB
    $verifyRes = rivopay_verify_transaction($transactionId);
    if (!$verifyRes['ok']) {
        return ['ok' => false, 'state' => 'ERROR', 'message' => $verifyRes['error'] ?? 'Server verification failed.'];
    }

    $vData = $verifyRes['data'];
    $cfg = $verifyRes['config'];
    $vStatus = strtoupper(trim((string)($vData['status'] ?? '')));

    if ($vStatus === 'PENDING') {
        $pdo->prepare("UPDATE payment_orders SET gateway_response_json = ?, updated_at = ? WHERE order_ref = ? AND status = 'pending'")
            ->execute([json_encode($vData, JSON_UNESCAPED_SLASHES), time(), $orderRef]);
        return ['ok' => false, 'state' => 'PENDING', 'message' => 'Payment is currently pending confirmation. Please re-check shortly.'];
    }

    if ($vStatus !== 'COMPLETED') {
        $pdo->prepare("UPDATE payment_orders SET status = CASE WHEN status = 'paid' THEN 'paid' ELSE 'failed' END, gateway_response_json = ?, updated_at = ? WHERE order_ref = ?")
            ->execute([json_encode($vData, JSON_UNESCAPED_SLASHES), time(), $orderRef]);
        return ['ok' => false, 'state' => 'ERROR', 'message' => 'Gateway verification status: ' . ($vStatus ?: 'ERROR')];
    }

    // Confirm verified amount >= expected amount (with currency exchange tolerance)
    $expectedBdt = round((float)$preOrder['amount_bdt'] * (float)($cfg['exchange_rate'] ?? 1.0), 2);
    $verifiedAmount = (float)($vData['amount'] ?? 0);
    if ($verifiedAmount + 0.01 < $expectedBdt) {
        return [
            'ok' => false,
            'state' => 'ERROR',
            'message' => 'Verified amount mismatch (expected ' . number_format($expectedBdt, 2) . ' BDT, got ' . number_format($verifiedAmount, 2) . ' BDT).',
        ];
    }

    $method = trim((string)($vData['payment_method'] ?? $hintMethod));
    $verifiedTrx = trim((string)($vData['transaction_id'] ?? $transactionId));
    if ($verifiedTrx === '') {
        $verifiedTrx = $transactionId;
    }

    // Atomic transaction for idempotency & double-credit prevention
    try {
        $pdo->exec('BEGIN IMMEDIATE');

        $oStmt = $pdo->prepare("SELECT * FROM payment_orders WHERE order_ref = ? LIMIT 1");
        $oStmt->execute([$orderRef]);
        $order = $oStmt->fetch();
        if (!$order) {
            $pdo->exec('ROLLBACK');
            return ['ok' => false, 'state' => 'ERROR', 'message' => 'Order missing during transaction.'];
        }
        if ($order['status'] === 'paid') {
            $pdo->exec('COMMIT');
            return ['ok' => true, 'state' => 'COMPLETED', 'already_processed' => true, 'order' => $order, 'message' => 'Payment already verified & activated.'];
        }

        // Prevent replay of same transaction_id across two different orders
        $dupStmt = $pdo->prepare("SELECT order_ref FROM payment_orders WHERE transaction_id = ? AND order_ref != ? AND status = 'paid' LIMIT 1");
        $dupStmt->execute([$verifiedTrx, $orderRef]);
        if ($dupStmt->fetch()) {
            $pdo->exec('ROLLBACK');
            return ['ok' => false, 'state' => 'ERROR', 'message' => 'Transaction ID already claimed by another order.'];
        }

        $pStmt = $pdo->prepare("SELECT * FROM subscription_plans WHERE id = ? LIMIT 1");
        $pStmt->execute([(int)$order['plan_id']]);
        $plan = $pStmt->fetch();
        if (!$plan) {
            $pdo->exec('ROLLBACK');
            return ['ok' => false, 'state' => 'ERROR', 'message' => 'Purchased subscription plan no longer exists.'];
        }

        $now = time();
        $upd = $pdo->prepare("
            UPDATE payment_orders
            SET status = 'paid',
                transaction_id = ?,
                payment_method = ?,
                payment_fee = ?,
                gateway_response_json = ?,
                paid_at = ?,
                updated_at = ?
            WHERE id = ? AND status != 'paid'
        ");
        $upd->execute([
            $verifiedTrx,
            $method,
            $hintFee,
            json_encode($vData, JSON_UNESCAPED_SLASHES),
            $now,
            $now,
            (int)$order['id'],
        ]);

        if ($upd->rowCount() === 0) {
            $pdo->exec('COMMIT');
            return ['ok' => true, 'state' => 'COMPLETED', 'already_processed' => true, 'order' => $order, 'message' => 'Payment already processed.'];
        }

        $sub = saas_activate_user_plan($pdo, (int)$order['user_id'], $plan, $orderRef);
        $pdo->exec('COMMIT');

        $order['status'] = 'paid';
        $order['transaction_id'] = $verifiedTrx;
        $order['payment_method'] = $method;
        return [
            'ok' => true,
            'state' => 'COMPLETED',
            'already_processed' => false,
            'order' => $order,
            'plan' => $plan,
            'subscription' => $sub,
            'message' => 'Payment verified via RivoPay and subscription activated!',
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->exec('ROLLBACK');
        }
        @error_log('[RivoPay Atomic Complete Error] ' . $e->getMessage());
        return ['ok' => false, 'state' => 'ERROR', 'message' => 'Database error while activating subscription.'];
    }
}
