<?php
/**
 * TURKY HUB — Customer Accounts, Subscription Plans & RivoPay Checkout Portal
 * Handles Registration, Login, Active Plan Countdown, API Quota, Licensed Code Snippets,
 * and Server-Side RivoPay / TurkyHubPay Checkout Initiation.
 */

session_start();
require_once __DIR__ . '/includes/rivopay.php';

$pdo = saas_db();
$origin = saas_origin_url();
$gwCfg = rivopay_get_config();
$csrf = saas_csrf_token('customer_csrf');

$flashError = '';
$flashSuccess = '';
if (!empty($_SESSION['cust_flash_ok'])) {
    $flashSuccess = (string)$_SESSION['cust_flash_ok'];
    unset($_SESSION['cust_flash_ok']);
}
if (!empty($_SESSION['cust_flash_err'])) {
    $flashError = (string)$_SESSION['cust_flash_err'];
    unset($_SESSION['cust_flash_err']);
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['ff_customer_id']);
    header('Location: account.php');
    exit;
}

$customerId = (int)($_SESSION['ff_customer_id'] ?? 0);
$customer = $customerId > 0 ? saas_get_user_by_id($customerId) : null;
if ($customerId > 0 && !$customer) {
    unset($_SESSION['ff_customer_id']);
    $customerId = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string)($_POST['cust_action'] ?? ''));
    if (!saas_verify_csrf($_POST['csrf_token'] ?? '', 'customer_csrf')) {
        $flashError = 'Security token expired. Please refresh and try again.';
    } elseif ($action === 'register') {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $pass = (string)($_POST['password'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
            $flashError = 'Please enter a valid name, email address, and password (minimum 6 characters).';
        } else {
            $dup = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $dup->execute([$email]);
            if ($dup->fetch()) {
                $flashError = 'An account with this email already exists. Please sign in instead.';
            } else {
                $apiKey = saas_generate_api_key();
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $ins = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, api_key, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, ?)");
                $ins->execute([$name, $email, $phone, $hash, $apiKey, time()]);
                $_SESSION['ff_customer_id'] = (int)$pdo->lastInsertId();
                $_SESSION['cust_flash_ok'] = 'Account created! Choose a subscription plan below to activate your API Key.';
                header('Location: account.php');
                exit;
            }
        }
    } elseif ($action === 'login') {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $pass = (string)($_POST['password'] ?? '');
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($pass, (string)$row['password_hash'])) {
            $flashError = 'Invalid email or password.';
        } elseif ((int)$row['is_active'] !== 1) {
            $flashError = 'Your account is suspended. Please contact administrator.';
        } else {
            $_SESSION['ff_customer_id'] = (int)$row['id'];
            header('Location: account.php');
            exit;
        }
    } elseif ($action === 'rotate_key' && $customer) {
        $newKey = saas_generate_api_key();
        $pdo->prepare("UPDATE users SET api_key = ? WHERE id = ?")->execute([$newKey, (int)$customer['id']]);
        $_SESSION['cust_flash_ok'] = 'API License Key rotated! Previous copied keys are now revoked.';
        header('Location: account.php');
        exit;
    } elseif ($action === 'purchase_plan') {
        if (!$customer) {
            $flashError = 'Please sign in or create an account before purchasing a plan.';
        } elseif ((int)$gwCfg['status'] !== 1) {
            $flashError = 'The payment gateway is currently disabled by administrator.';
        } else {
            $planId = (int)($_POST['plan_id'] ?? 0);
            $pStmt = $pdo->prepare("SELECT * FROM subscription_plans WHERE id = ? AND is_active = 1 LIMIT 1");
            $pStmt->execute([$planId]);
            $plan = $pStmt->fetch();

            if (!$plan) {
                $flashError = 'Selected subscription plan is unavailable.';
            } else {
                $orderRef = saas_generate_order_ref();
                $now = time();
                $amount = round((float)$plan['price_bdt'], 2);

                if ($amount <= 0) {
                    // Free plan instant activation
                    $pdo->exec('BEGIN IMMEDIATE');
                    $pdo->prepare("INSERT INTO payment_orders (order_ref, user_id, plan_id, gateway_code, amount_bdt, currency, status, payment_method, paid_at, created_at, updated_at) VALUES (?, ?, ?, 'free', 0, 'BDT', 'paid', 'free_trial', ?, ?, ?)")
                        ->execute([$orderRef, (int)$customer['id'], (int)$plan['id'], $now, $now, $now]);
                    saas_activate_user_plan($pdo, (int)$customer['id'], $plan, $orderRef);
                    $pdo->exec('COMMIT');
                    $_SESSION['cust_flash_ok'] = 'Plan "' . $plan['name'] . '" activated immediately!';
                    header('Location: account.php');
                    exit;
                }

                // Create local pending transaction BEFORE calling gateway
                $pdo->prepare("INSERT INTO payment_orders (order_ref, user_id, plan_id, gateway_code, amount_bdt, currency, status, created_at, updated_at) VALUES (?, ?, ?, 'rivopay', ?, ?, 'pending', ?, ?)")
                    ->execute([$orderRef, (int)$customer['id'], (int)$plan['id'], $amount, (string)$gwCfg['currency'], $now, $now]);

                $orderRow = [
                    'order_ref' => $orderRef,
                    'amount_bdt' => $amount,
                ];
                $payRes = rivopay_create_payment($orderRow, $customer, $plan, $origin);
                if (!empty($payRes['ok']) && !empty($payRes['payment_url'])) {
                    header('Location: ' . $payRes['payment_url']);
                    exit;
                } else {
                    $pdo->prepare("UPDATE payment_orders SET status = 'failed', gateway_response_json = ?, updated_at = ? WHERE order_ref = ?")
                        ->execute([json_encode($payRes, JSON_UNESCAPED_SLASHES), time(), $orderRef]);
                    $flashError = (string)($payRes['error'] ?? 'Could not connect to payment gateway. Please verify Admin RivoPay settings.');
                }
            }
        }
    }
}

$plans = $pdo->query("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$activeSub = $customer ? saas_get_active_subscription((int)$customer['id']) : null;

$subState = 'none';
$subLimit = 0;
$subUsed = 0;
$subRemaining = null;
if ($activeSub) {
    $subLimit = (int)$activeSub['api_limit'];
    $subUsed = (int)$activeSub['requests_used'];
    if ($activeSub['status'] !== 'active') {
        $subState = 'expired';
    } elseif ($subLimit > 0 && $subUsed >= $subLimit) {
        $subState = 'limit_exceeded';
    } else {
        $subState = 'active';
        $subRemaining = $subLimit > 0 ? max(0, $subLimit - $subUsed) : null;
    }
}

$orders = [];
if ($customer) {
    $oStmt = $pdo->prepare("
        SELECT po.*, sp.name AS plan_name
        FROM payment_orders po
        LEFT JOIN subscription_plans sp ON sp.id = po.plan_id
        WHERE po.user_id = ?
        ORDER BY po.id DESC LIMIT 20
    ");
    $oStmt->execute([(int)$customer['id']]);
    $orders = $oStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Account &amp; Subscription Plans | TURKY HUB</title>
    <link rel="icon" type="image/png" href="badges/ff_ui_prime_icon_08.png">
    <script>
        (function() {
            try {
                var t = localStorage.getItem('turky_theme');
                if (t !== 'light' && t !== 'dark') {
                    t = (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) ? 'light' : 'dark';
                }
                document.documentElement.setAttribute('data-theme', t);
                document.documentElement.classList.add(t);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/apple-theme.css">
</head>
<body class="apple-ui min-h-screen flex flex-col">

    <!-- Single Clean Apple Top Bar -->
    <header class="apple-navbar sticky top-0 z-40">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="macos-traffic-lights hidden sm:inline-flex" aria-hidden="true">
                    <span class="macos-dot macos-dot-red"></span>
                    <span class="macos-dot macos-dot-yellow"></span>
                    <span class="macos-dot macos-dot-green"></span>
                </div>
                <a href="index.php" class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-xl bg-red-500 flex items-center justify-center shadow-sm shrink-0">
                        <i class="fas fa-fire-flame-curved text-white apple-keep-white text-sm"></i>
                    </div>
                    <span class="font-bold text-white text-sm sm:text-base tracking-tight truncate">TURKY HUB</span>
                </a>
                <span class="hidden sm:inline-flex items-center gap-1.5 text-[10px] px-2.5 py-0.5 rounded-full apple-badge-red font-bold uppercase tracking-wider">
                    API &amp; Plans
                </span>
            </div>

            <div class="flex items-center gap-2 sm:gap-2.5">
                <a href="index.php" class="apple-btn-secondary px-3 py-1.5 text-xs flex items-center gap-1.5">
                    <i class="fas fa-grid-2 text-red-500"></i>
                    <span>Explorer</span>
                </a>
                <button type="button" data-theme-toggle class="theme-toggle-btn" aria-label="Switch Theme" aria-pressed="false">
                    <span class="theme-icon-wrap" aria-hidden="true">
                        <i class="fas fa-sun icon-sun text-xs"></i>
                        <i class="fas fa-moon icon-moon text-xs"></i>
                    </span>
                    <span data-theme-label class="hidden sm:inline">Dark</span>
                </button>
                <?php if ($customer): ?>
                    <a href="account.php?action=logout" class="apple-btn-primary apple-keep-white px-3 py-1.5 text-xs flex items-center gap-1.5">
                        <i class="fas fa-sign-out-alt text-xs"></i>
                        <span>Logout</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-[1440px] mx-auto px-4 sm:px-6 py-6 space-y-6 w-full">

        <?php if ($flashError !== ''): ?>
            <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-400 text-xs font-semibold flex items-center gap-2.5">
                <i class="fas fa-circle-exclamation text-sm shrink-0"></i>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flashSuccess !== ''): ?>
            <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-400 text-xs font-semibold flex items-center gap-2.5">
                <i class="fas fa-circle-check text-sm shrink-0"></i>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!$customer): ?>
            <!-- GUEST AUTH: LOGIN / CREATE ACCOUNT -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <section class="lg:col-span-5 apple-window overflow-hidden">
                    <div class="macos-window-bar">
                        <div class="macos-traffic-lights" aria-hidden="true">
                            <span class="macos-dot macos-dot-red"></span>
                            <span class="macos-dot macos-dot-yellow"></span>
                            <span class="macos-dot macos-dot-green"></span>
                        </div>
                        <span class="macos-window-title">Account</span>
                        <span class="w-8" aria-hidden="true"></span>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="flex gap-1 apple-nav-pill">
                            <button type="button" id="authTabBtnRegister" onclick="switchAuthTab('register')" class="apple-nav-item active flex-1 py-2 text-xs cursor-pointer">Register</button>
                            <button type="button" id="authTabBtnLogin" onclick="switchAuthTab('login')" class="apple-nav-item flex-1 py-2 text-xs cursor-pointer">Sign In</button>
                        </div>

                        <form id="authPanelRegister" method="POST" action="account.php" class="space-y-3.5">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="cust_action" value="register">
                            <div>
                                <label class="block text-xs font-semibold text-gray-300 mb-1">Name</label>
                                <input type="text" name="name" required placeholder="Name" class="w-full border px-3.5 py-2.5 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-300 mb-1">Email</label>
                                <input type="email" name="email" required placeholder="Email" class="w-full border px-3.5 py-2.5 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-300 mb-1">Phone</label>
                                <input type="text" name="phone" placeholder="017XXXXXXXX" class="w-full border px-3.5 py-2.5 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-300 mb-1">Password</label>
                                <input type="password" name="password" required minlength="6" placeholder="Min 6 chars" class="w-full border px-3.5 py-2.5 text-xs">
                            </div>
                            <button type="submit" class="apple-btn-primary apple-keep-white w-full py-3 text-xs flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fas fa-user-plus"></i>
                                <span>Create Account</span>
                            </button>
                        </form>

                        <form id="authPanelLogin" method="POST" action="account.php" class="hidden space-y-3.5">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="cust_action" value="login">
                            <div>
                                <label class="block text-xs font-semibold text-gray-300 mb-1">Email</label>
                                <input type="email" name="email" required placeholder="Email" class="w-full border px-3.5 py-2.5 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-300 mb-1">Password</label>
                                <input type="password" name="password" required placeholder="Password" class="w-full border px-3.5 py-2.5 text-xs">
                            </div>
                            <button type="submit" class="apple-btn-primary apple-keep-white w-full py-3 text-xs flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fas fa-right-to-bracket"></i>
                                <span>Sign In</span>
                            </button>
                        </form>
                    </div>
                </section>

                <!-- Preview Available Plans for Visitors -->
                <section class="lg:col-span-7 apple-window overflow-hidden">
                    <div class="macos-window-bar">
                        <div class="macos-traffic-lights" aria-hidden="true">
                            <span class="macos-dot macos-dot-red"></span>
                            <span class="macos-dot macos-dot-yellow"></span>
                            <span class="macos-dot macos-dot-green"></span>
                        </div>
                        <span class="macos-window-title">Plans</span>
                        <span class="text-[10px] font-mono text-gray-400"><?= htmlspecialchars($gwCfg['display_name']) ?></span>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <?php foreach ($plans as $pl): ?>
                                <div class="apple-surface-secondary p-4 flex flex-col justify-between gap-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <h3 class="text-sm font-bold text-white"><?= htmlspecialchars($pl['name']) ?></h3>
                                        <?php if (!empty($pl['badge_label'])): ?>
                                            <span class="apple-badge-red text-[10px] font-bold px-2 py-0.5 rounded-full"><?= htmlspecialchars($pl['badge_label']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="pt-2 border-t border-white/10 flex items-center justify-between text-xs font-mono">
                                        <span class="text-white font-bold text-base"><?= (float)$pl['price_bdt'] <= 0 ? 'FREE' : number_format((float)$pl['price_bdt'], 0) . ' BDT' ?></span>
                                        <span class="text-gray-400"><?= htmlspecialchars(saas_format_duration((int)$pl['duration_value'], (string)$pl['duration_unit'])) ?> · <?= (int)$pl['api_limit'] > 0 ? number_format((int)$pl['api_limit']) : 'Unlimited' ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            </div>

        <?php else: ?>
            <!-- LOGGED IN SUBSCRIBER DASHBOARD -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- Left: Plan Status, Countdown & API Key -->
                <section class="lg:col-span-5 apple-window overflow-hidden">
                    <div class="macos-window-bar">
                        <div class="macos-traffic-lights" aria-hidden="true">
                            <span class="macos-dot macos-dot-red"></span>
                            <span class="macos-dot macos-dot-yellow"></span>
                            <span class="macos-dot macos-dot-green"></span>
                        </div>
                        <span class="macos-window-title"><?= htmlspecialchars($customer['name']) ?> · License</span>
                        <?php if ($subState === 'active'): ?>
                            <span id="subStatusPill" class="px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold uppercase">Active</span>
                        <?php elseif ($subState === 'limit_exceeded'): ?>
                            <span id="subStatusPill" class="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400 border border-amber-500/30 text-[10px] font-bold uppercase">Limit Full</span>
                        <?php elseif ($subState === 'expired'): ?>
                            <span id="subStatusPill" class="px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30 text-[10px] font-bold uppercase">Expired</span>
                        <?php else: ?>
                            <span id="subStatusPill" class="px-2 py-0.5 rounded-full bg-zinc-500/15 text-gray-400 border border-white/10 text-[10px] font-bold uppercase">No Plan</span>
                        <?php endif; ?>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="apple-surface-secondary p-4 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Plan</span>
                                <span class="font-bold text-white"><?= htmlspecialchars($activeSub['plan_name'] ?? 'None') ?></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Expires</span>
                                <?php if ($activeSub && $activeSub['status'] === 'active'): ?>
                                    <?php if ($activeSub['expires_at'] === null): ?>
                                        <span class="font-mono font-bold text-emerald-400">Lifetime</span>
                                    <?php else: ?>
                                        <span id="liveCountdown" data-expires="<?= (int)$activeSub['expires_at'] ?>" class="font-mono font-bold text-emerald-400">...</span>
                                    <?php endif; ?>
                                <?php elseif ($activeSub && $activeSub['status'] === 'expired'): ?>
                                    <span class="font-mono font-bold text-rose-400">Expired (<?= date('Y-m-d H:i:s', (int)$activeSub['expires_at']) ?>)</span>
                                <?php else: ?>
                                    <span class="font-mono text-gray-400">—</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Usage</span>
                                <span class="font-mono font-bold text-white">
                                    <?= $activeSub ? number_format($subUsed) . ' / ' . ($subLimit > 0 ? number_format($subLimit) : 'Unlimited') : '0 / 0' ?>
                                </span>
                            </div>
                            <?php if ($activeSub && $subLimit > 0):
                                $pct = min(100, round(($subUsed / max(1, $subLimit)) * 100));
                            ?>
                                <div class="w-full h-2 rounded-full bg-black/30 overflow-hidden">
                                    <div class="h-full <?= $pct >= 100 ? 'bg-red-500' : 'bg-emerald-500' ?>" style="width:<?= $pct ?>%"></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- API License Key Box -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-white uppercase tracking-wider">API Key</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" id="myApiKeyInput" readonly value="<?= htmlspecialchars($customer['api_key']) ?>" class="w-full border px-3 py-2.5 text-xs font-mono">
                                <button type="button" onclick="copyPortalText(document.getElementById('myApiKeyInput').value, 'Copied!')" class="apple-btn-primary apple-keep-white px-3.5 py-2.5 text-xs shrink-0 cursor-pointer">Copy</button>
                            </div>
                            <div class="flex items-center justify-between gap-2 pt-1">
                                <form method="POST" action="account.php" onsubmit="return confirm('Rotate API Key?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <input type="hidden" name="cust_action" value="rotate_key">
                                    <button type="submit" class="apple-btn-secondary px-3 py-1.5 text-[11px] flex items-center gap-1.5 cursor-pointer">
                                        <i class="fas fa-rotate"></i>
                                        <span>Rotate</span>
                                    </button>
                                </form>
                                <button type="button" onclick="testMyKey()" class="apple-btn-secondary px-3 py-1.5 text-[11px] flex items-center gap-1.5 cursor-pointer">
                                    <i class="fas fa-vial text-red-500"></i>
                                    <span>Test API</span>
                                </button>
                            </div>
                            <div id="liveKeyTestResult" class="hidden p-3 rounded-xl apple-surface-secondary text-[11px] font-mono"></div>
                        </div>

                        <?php if ($subState !== 'active'): ?>
                            <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-xs text-rose-400 font-semibold flex items-center gap-2">
                                <i class="fas fa-lock"></i>
                                <span>Locked — Subscribe below</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Right: Subscription Plans & RivoPay Checkout -->
                <section class="lg:col-span-7 apple-window overflow-hidden">
                    <div class="macos-window-bar">
                        <div class="macos-traffic-lights" aria-hidden="true">
                            <span class="macos-dot macos-dot-red"></span>
                            <span class="macos-dot macos-dot-yellow"></span>
                            <span class="macos-dot macos-dot-green"></span>
                        </div>
                        <span class="macos-window-title">Plans</span>
                        <span class="text-[10px] font-mono text-gray-400"><?= (int)$gwCfg['status'] === 1 ? htmlspecialchars($gwCfg['display_name']) : 'Gateway Off' ?></span>
                    </div>

                    <div class="p-6 space-y-4">
                        <?php if ((int)$gwCfg['status'] !== 1): ?>
                            <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-400">
                                Gateway disabled.
                            </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($plans as $pl):
                                $isFree = ((float)$pl['price_bdt'] <= 0);
                            ?>
                                <form method="POST" action="account.php" class="apple-surface-secondary p-4 flex flex-col justify-between gap-4 border hover:border-red-500/40 transition">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <input type="hidden" name="cust_action" value="purchase_plan">
                                    <input type="hidden" name="plan_id" value="<?= (int)$pl['id'] ?>">

                                    <div class="flex items-center justify-between gap-2">
                                        <h3 class="text-sm font-bold text-white"><?= htmlspecialchars($pl['name']) ?></h3>
                                        <?php if (!empty($pl['badge_label'])): ?>
                                            <span class="apple-badge-red text-[10px] font-bold px-2 py-0.5 rounded-full"><?= htmlspecialchars($pl['badge_label']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="space-y-3 pt-2 border-t border-white/10">
                                        <div class="flex items-baseline justify-between">
                                            <span class="text-xl font-bold text-white font-mono"><?= $isFree ? 'FREE' : number_format((float)$pl['price_bdt'], 2) . ' BDT' ?></span>
                                            <span class="text-[11px] font-mono text-gray-400">
                                                <?= htmlspecialchars(saas_format_duration((int)$pl['duration_value'], (string)$pl['duration_unit'])) ?>
                                                · <?= (int)$pl['api_limit'] > 0 ? number_format((int)$pl['api_limit']) . ' calls' : 'Unlimited' ?>
                                            </span>
                                        </div>

                                        <button type="submit" <?= (!$isFree && (int)$gwCfg['status'] !== 1) ? 'disabled' : '' ?> class="apple-btn-primary apple-keep-white w-full py-2.5 px-4 text-xs flex items-center justify-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                            <i class="fas fa-bolt"></i>
                                            <span><?= $isFree ? 'Activate' : 'Pay Now' ?></span>
                                        </button>
                                    </div>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Snippets -->
            <section class="apple-window overflow-hidden">
                <div class="macos-window-bar">
                    <div class="macos-traffic-lights" aria-hidden="true">
                        <span class="macos-dot macos-dot-red"></span>
                        <span class="macos-dot macos-dot-yellow"></span>
                        <span class="macos-dot macos-dot-green"></span>
                    </div>
                    <span class="macos-window-title">Snippets</span>
                    <span class="text-[10px] font-mono text-emerald-400">Token Gated</span>
                </div>

                <?php $encKey = urlencode($customer['api_key']); ?>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="apple-surface-secondary p-4 space-y-2">
                        <div class="flex items-center justify-between font-bold text-white">
                            <span>1. Web SDK</span>
                            <button type="button" onclick="copyPortalText(document.getElementById('snipSdk').textContent, 'Copied!')" class="text-red-500 hover:underline text-[11px] cursor-pointer">Copy</button>
                        </div>
                        <pre id="snipSdk" class="p-3 rounded-xl bg-black/80 text-zinc-100 text-[11px] overflow-x-auto font-mono">&lt;script src="<?= htmlspecialchars($origin) ?>/api/license/sdk.php?key=<?= htmlspecialchars($encKey) ?>"&gt;&lt;/script&gt;
&lt;ff-badge id="126393684880" size="64"&gt;&lt;/ff-badge&gt;</pre>
                    </div>

                    <div class="apple-surface-secondary p-4 space-y-2">
                        <div class="flex items-center justify-between font-bold text-white">
                            <span>2. SVG URL</span>
                            <button type="button" onclick="copyPortalText(document.getElementById('snipImg').textContent, 'Copied!')" class="text-red-500 hover:underline text-[11px] cursor-pointer">Copy</button>
                        </div>
                        <pre id="snipImg" class="p-3 rounded-xl bg-black/80 text-zinc-100 text-[11px] overflow-x-auto font-mono">&lt;img src="<?= htmlspecialchars($origin) ?>/api/license/asset.php?id=126393684880&amp;format=svg&amp;key=<?= htmlspecialchars($encKey) ?>" width="64" height="64" /&gt;</pre>
                    </div>

                    <div class="apple-surface-secondary p-4 space-y-2">
                        <div class="flex items-center justify-between font-bold text-white">
                            <span>3. Player</span>
                            <button type="button" onclick="copyPortalText(document.getElementById('snipPlayer').textContent, 'Copied!')" class="text-red-500 hover:underline text-[11px] cursor-pointer">Copy</button>
                        </div>
                        <pre id="snipPlayer" class="p-3 rounded-xl bg-black/80 text-zinc-100 text-[11px] overflow-x-auto font-mono">&lt;iframe src="<?= htmlspecialchars($origin) ?>/api/license/player.php?id=126393684880&amp;key=<?= htmlspecialchars($encKey) ?>" width="80" height="80" frameborder="0"&gt;&lt;/iframe&gt;</pre>
                    </div>

                    <div class="apple-surface-secondary p-4 space-y-2">
                        <div class="flex items-center justify-between font-bold text-white">
                            <span>4. JSON API</span>
                            <button type="button" onclick="copyPortalText(document.getElementById('snipJson').textContent, 'Copied!')" class="text-red-500 hover:underline text-[11px] cursor-pointer">Copy</button>
                        </div>
                        <pre id="snipJson" class="p-3 rounded-xl bg-black/80 text-zinc-100 text-[11px] overflow-x-auto font-mono"><?= htmlspecialchars($origin) ?>/api/license/badges.php?key=<?= htmlspecialchars($encKey) ?></pre>
                    </div>
                </div>
            </section>

            <!-- Payment History -->
            <section class="apple-window overflow-hidden">
                <div class="macos-window-bar">
                    <div class="macos-traffic-lights" aria-hidden="true">
                        <span class="macos-dot macos-dot-red"></span>
                        <span class="macos-dot macos-dot-yellow"></span>
                        <span class="macos-dot macos-dot-green"></span>
                    </div>
                    <span class="macos-window-title">Orders</span>
                    <span class="text-[10px] font-mono text-gray-400"><?= count($orders) ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="text-[11px] uppercase text-gray-400">
                            <tr>
                                <th class="py-3 px-4">Order</th>
                                <th class="py-3 px-4">Plan</th>
                                <th class="py-3 px-4">Amount</th>
                                <th class="py-3 px-4">Trx ID</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="6" class="py-6 px-4 text-center text-gray-400">No orders</td></tr>
                            <?php else: foreach ($orders as $o): ?>
                                <tr>
                                    <td class="py-3 px-4 font-mono text-white font-semibold"><?= htmlspecialchars($o['order_ref']) ?></td>
                                    <td class="py-3 px-4 text-white"><?= htmlspecialchars($o['plan_name'] ?? 'Plan #' . $o['plan_id']) ?></td>
                                    <td class="py-3 px-4 font-mono text-white"><?= number_format((float)$o['amount_bdt'], 2) ?> BDT</td>
                                    <td class="py-3 px-4 font-mono text-gray-400">
                                        <?= htmlspecialchars(($o['payment_method'] ?: '—') . (!empty($o['transaction_id']) ? ' · ' . $o['transaction_id'] : '')) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php
                                        $st = strtolower((string)$o['status']);
                                        $stCls = $st === 'paid' ? 'text-emerald-400 bg-emerald-500/15 border-emerald-500/30' : ($st === 'pending' ? 'text-amber-400 bg-amber-500/15 border-amber-500/30' : 'text-rose-400 bg-rose-500/15 border-rose-500/30');
                                        ?>
                                        <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase <?= $stCls ?>"><?= htmlspecialchars($st) ?></span>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-gray-400"><?= date('Y-m-d H:i', (int)$o['created_at']) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <footer class="apple-footer mt-auto">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5">
                <div class="macos-traffic-lights" aria-hidden="true">
                    <span class="macos-dot macos-dot-red"></span>
                    <span class="macos-dot macos-dot-yellow"></span>
                    <span class="macos-dot macos-dot-green"></span>
                </div>
                <span class="font-semibold text-white">TURKY HUB</span>
                <span>· Subscription &amp; RivoPay Portal</span>
            </div>
            <a href="index.php" class="hover:text-red-500 transition">Back to Explorer</a>
        </div>
    </footer>

    <div id="portalToast" class="fixed bottom-6 right-6 bg-[#18181B] text-white apple-keep-white border border-red-500/40 font-semibold text-xs px-4 py-2.5 rounded-2xl shadow-2xl flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 z-50 pointer-events-none">
        <i class="fas fa-check-circle text-red-500"></i>
        <span id="portalToastMsg">Copied!</span>
    </div>

    <script>
        function switchAuthTab(tab) {
            const regBtn = document.getElementById('authTabBtnRegister');
            const logBtn = document.getElementById('authTabBtnLogin');
            const regForm = document.getElementById('authPanelRegister');
            const logForm = document.getElementById('authPanelLogin');
            if (!regForm || !logForm) return;
            const isReg = (tab === 'register');
            regForm.classList.toggle('hidden', !isReg);
            logForm.classList.toggle('hidden', isReg);
            if (regBtn) regBtn.classList.toggle('active', isReg);
            if (logBtn) logBtn.classList.toggle('active', !isReg);
        }

        function copyPortalText(txt, msg) {
            navigator.clipboard.writeText(txt).then(() => showPortalToast(msg)).catch(() => showPortalToast(msg));
        }

        function showPortalToast(msg) {
            const el = document.getElementById('portalToast');
            document.getElementById('portalToastMsg').textContent = msg;
            el.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => el.classList.add('translate-y-20', 'opacity-0'), 2400);
        }

        async function testMyKey() {
            const key = document.getElementById('myApiKeyInput')?.value || '';
            const box = document.getElementById('liveKeyTestResult');
            if (!box) return;
            box.classList.remove('hidden');
            box.textContent = 'Checking live API license...';
            try {
                const r = await fetch('api/license/badges.php?id=126393684880&key=' + encodeURIComponent(key), { cache: 'no-store' });
                const data = await r.json();
                if (r.ok && data.ok) {
                    box.className = 'p-3 rounded-xl apple-surface-secondary text-[11px] font-mono text-emerald-400';
                    const rem = data.subscription.remaining === null ? 'Unlimited' : data.subscription.remaining;
                    box.textContent = 'VALID · Plan: ' + data.subscription.plan + ' | Used: ' + data.subscription.used + ' | Remaining: ' + rem;
                } else {
                    box.className = 'p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-[11px] font-mono text-rose-400';
                    box.textContent = 'LOCKED (' + r.status + '): ' + (data.message || data.error);
                }
            } catch (e) {
                box.textContent = 'Network error checking API key.';
            }
        }

        (function initCountdown() {
            const el = document.getElementById('liveCountdown');
            if (!el) return;
            const target = parseInt(el.getAttribute('data-expires') || '0', 10);
            function tick() {
                const diff = target - Math.floor(Date.now() / 1000);
                if (diff <= 0) {
                    el.textContent = '00d 00h 00m 00s (Expired)';
                    el.classList.remove('text-emerald-400');
                    el.classList.add('text-rose-400');
                    const pill = document.getElementById('subStatusPill');
                    if (pill) {
                        pill.textContent = 'Expired';
                        pill.className = 'px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30 text-[10px] font-bold uppercase';
                    }
                    return;
                }
                const d = Math.floor(diff / 86400);
                const h = Math.floor((diff % 86400) / 3600);
                const m = Math.floor((diff % 3600) / 60);
                const s = diff % 60;
                const pad = n => String(n).padStart(2, '0');
                el.textContent = (d > 0 ? d + 'd ' : '') + pad(h) + 'h ' + pad(m) + 'm ' + pad(s) + 's';
                setTimeout(tick, 1000);
            }
            tick();
        })();
    </script>
    <script src="assets/apple-theme.js"></script>
</body>
</html>
