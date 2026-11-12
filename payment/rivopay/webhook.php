<?php
/**
 * TURKY HUB — RivoPay / TurkyHubPay Server-to-Server Webhook (IPN)
 * CSRF-exempt external POST endpoint. Always verifies via /api/payment/verify.
 */

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/../../includes/rivopay.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Accept both form-urlencoded POST and JSON payloads
$rawBody = file_get_contents('php://input');
$jsonBody = is_string($rawBody) ? json_decode($rawBody, true) : null;
$payload = is_array($jsonBody) ? $jsonBody : $_POST;

$orderRef = trim((string)($_GET['order_id'] ?? $payload['order_id'] ?? ''));
if ($orderRef === '' && !empty($payload['metadata'])) {
    $meta = is_string($payload['metadata']) ? json_decode($payload['metadata'], true) : $payload['metadata'];
    if (is_array($meta) && !empty($meta['order_ref'])) {
        $orderRef = trim((string)$meta['order_ref']);
    }
}

$transactionId = trim((string)($payload['transactionId'] ?? $payload['transaction_id'] ?? ''));
$paymentMethod = trim((string)($payload['paymentMethod'] ?? $payload['payment_method'] ?? ''));
$paymentFee = (float)($payload['paymentFee'] ?? $payload['payment_fee'] ?? 0);
$rawStatus = strtolower(trim((string)($payload['status'] ?? '')));

if ($orderRef === '' || $transactionId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing order_id or transactionId']);
    exit;
}

if ($rawStatus === 'failed' || $rawStatus === 'cancelled') {
    $pdo = saas_db();
    $pdo->prepare("UPDATE payment_orders SET status = CASE WHEN status = 'paid' THEN 'paid' ELSE 'failed' END, transaction_id = COALESCE(NULLIF(transaction_id, ''), ?), updated_at = ? WHERE order_ref = ?")
        ->execute([$transactionId, time(), $orderRef]);
    http_response_code(200);
    echo json_encode(['ok' => false, 'state' => 'FAILED', 'message' => 'Gateway reported non-completed webhook status.']);
    exit;
}

// Mandatory server-side verification + atomic idempotent completion
$result = rivopay_complete_order($orderRef, $transactionId, $paymentMethod, $paymentFee);

http_response_code(200);
echo json_encode([
    'ok' => (bool)$result['ok'],
    'state' => $result['state'] ?? 'ERROR',
    'already_processed' => !empty($result['already_processed']),
    'order_ref' => $orderRef,
    'transaction_id' => $transactionId,
    'message' => $result['message'] ?? '',
]);
