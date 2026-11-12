<?php
/**
 * TURKY HUB — RivoPay / TurkyHubPay Browser Redirect Callback
 * Security: NEVER marks paid from query parameters alone; always calls server-side Verify API.
 */

session_start();
require_once __DIR__ . '/../../includes/rivopay.php';

$origin = saas_origin_url();
$orderRef = trim((string)($_GET['order_id'] ?? ''));
$isCancelled = (isset($_GET['cancelled']) && $_GET['cancelled'] == '1') || strtolower(trim((string)($_GET['status'] ?? ''))) === 'failed';
$transactionId = trim((string)($_GET['transactionId'] ?? $_GET['transaction_id'] ?? ''));
$paymentMethod = trim((string)($_GET['paymentMethod'] ?? $_GET['payment_method'] ?? ''));
$paymentFee = (float)($_GET['paymentFee'] ?? $_GET['payment_fee'] ?? 0);

$state = 'ERROR';
$headline = 'Payment Verification Failed';
$message = 'Unable to verify transaction.';
$order = null;
$sub = null;

if ($orderRef !== '') {
    $stmt = saas_db()->prepare("SELECT * FROM payment_orders WHERE order_ref = ? LIMIT 1");
    $stmt->execute([$orderRef]);
    $order = $stmt->fetch() ?: null;
}

if ($order && $order['status'] === 'paid') {
    $state = 'COMPLETED';
    $headline = 'Subscription Activated!';
    $message = '';
    $sub = saas_get_active_subscription((int)$order['user_id']);
} elseif ($isCancelled && $transactionId === '') {
    if ($order && $order['status'] === 'pending') {
        saas_db()->prepare("UPDATE payment_orders SET status = 'cancelled', updated_at = ? WHERE id = ? AND status = 'pending'")
            ->execute([time(), (int)$order['id']]);
    }
    $state = 'CANCELLED';
    $headline = 'Payment Cancelled';
    $message = '';
} elseif ($order && $transactionId !== '') {
    // Always verify server-side with RivoPay /api/payment/verify
    $res = rivopay_complete_order($orderRef, $transactionId, $paymentMethod, $paymentFee);
    $state = $res['state'] ?? 'ERROR';
    $message = $res['message'] ?? '';
    if (!empty($res['ok'])) {
        $headline = 'Subscription Activated!';
        $order = $res['order'] ?? $order;
        $sub = saas_get_active_subscription((int)$order['user_id']);
    } elseif ($state === 'PENDING') {
        $headline = 'Payment Pending Verification';
    } else {
        $headline = 'Payment Not Completed';
    }
} elseif ($order && $order['transaction_id']) {
    $res = rivopay_complete_order($orderRef, (string)$order['transaction_id']);
    $state = $res['state'] ?? 'ERROR';
    $message = $res['message'] ?? '';
    if (!empty($res['ok'])) {
        $headline = 'Subscription Activated!';
        $order = $res['order'] ?? $order;
        $sub = saas_get_active_subscription((int)$order['user_id']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status | TURKY HUB</title>
    <script>
        (function() {
            try {
                var t = localStorage.getItem('turky_theme');
                if (t !== 'light' && t !== 'dark') {
                    t = (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) ? 'light' : 'dark';
                }
                document.documentElement.setAttribute('data-theme', t);
                document.documentElement.classList.add(t);
            } catch (e) {}
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($origin) ?>/assets/apple-theme.css">
</head>
<body class="apple-ui min-h-screen flex items-center justify-center p-4">
    <div class="apple-window w-full max-w-lg overflow-hidden">
        <div class="macos-window-bar">
            <div class="macos-traffic-lights" aria-hidden="true">
                <span class="macos-dot macos-dot-red"></span>
                <span class="macos-dot macos-dot-yellow"></span>
                <span class="macos-dot macos-dot-green"></span>
            </div>
            <span class="macos-window-title">RivoPay · Payment Verification</span>
            <button type="button" data-theme-toggle class="theme-toggle-btn !h-7 !px-2.5 !text-[11px]" aria-label="Switch Theme" aria-pressed="false">
                <span class="theme-icon-wrap" aria-hidden="true">
                    <i class="fas fa-sun icon-sun text-[10px]"></i>
                    <i class="fas fa-moon icon-moon text-[10px]"></i>
                </span>
                <span data-theme-label>Dark</span>
            </button>
        </div>

        <div class="p-6 sm:p-8 text-center space-y-5">
            <?php if ($state === 'COMPLETED'): ?>
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-400 flex items-center justify-center mx-auto text-2xl">
                    <i class="fas fa-circle-check"></i>
                </div>
            <?php elseif ($state === 'PENDING'): ?>
                <div class="w-16 h-16 rounded-2xl bg-amber-500/15 border border-amber-500/40 text-amber-400 flex items-center justify-center mx-auto text-2xl">
                    <i class="fas fa-clock"></i>
                </div>
            <?php else: ?>
                <div class="w-16 h-16 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-400 flex items-center justify-center mx-auto text-2xl">
                    <i class="fas fa-circle-xmark"></i>
                </div>
            <?php endif; ?>

            <div class="space-y-1.5">
                <h1 class="text-xl sm:text-2xl font-bold text-white"><?= htmlspecialchars($headline) ?></h1>
                <?php if ($state !== 'COMPLETED' && $message !== ''): ?>
                    <p class="text-xs sm:text-sm text-gray-400"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
            </div>

            <?php if ($order): ?>
                <div class="apple-surface-secondary p-4 text-left space-y-2 text-xs font-mono">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Order Ref:</span>
                        <span class="text-white font-semibold"><?= htmlspecialchars($order['order_ref']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Amount:</span>
                        <span class="text-white font-semibold"><?= number_format((float)$order['amount_bdt'], 2) ?> <?= htmlspecialchars($order['currency']) ?></span>
                    </div>
                    <?php if (!empty($order['transaction_id'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Transaction ID:</span>
                            <span class="text-emerald-400 font-semibold"><?= htmlspecialchars($order['transaction_id']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($order['payment_method'])): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Method:</span>
                            <span class="text-white uppercase"><?= htmlspecialchars($order['payment_method']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($sub): ?>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Active Plan:</span>
                            <span class="text-emerald-400 font-semibold"><?= htmlspecialchars($sub['plan_name']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Expires:</span>
                            <span class="text-white"><?= $sub['expires_at'] === null ? 'Lifetime / Never' : date('Y-m-d H:i:s', (int)$sub['expires_at']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                <?php if ($state === 'PENDING'): ?>
                    <a href="<?= htmlspecialchars($origin . '/payment/rivopay/callback?order_id=' . urlencode($orderRef) . '&transactionId=' . urlencode($transactionId)) ?>" class="apple-btn-secondary px-5 py-2.5 text-xs flex items-center gap-2">
                        <i class="fas fa-rotate-right"></i>
                        <span>Re-Check</span>
                    </a>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($origin) ?>/account.php" class="apple-btn-primary apple-keep-white px-6 py-2.5 text-xs flex items-center gap-2">
                    <i class="fas fa-key"></i>
                    <span>Account</span>
                </a>
                <a href="<?= htmlspecialchars($origin) ?>/index.php" class="apple-btn-secondary px-5 py-2.5 text-xs">
                    Explorer
                </a>
            </div>
        </div>
    </div>
    <script src="<?= htmlspecialchars($origin) ?>/assets/apple-theme.js"></script>
</body>
</html>
