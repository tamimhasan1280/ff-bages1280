<?php
/**
 * TURKY HUB — Admin Panel SaaS & RivoPay Management Module
 * Provides RivoPay dynamic gateway settings, custom A-Z subscription plans CRUD,
 * and subscriber / order lifecycle controls inside admin.php.
 */

require_once __DIR__ . '/rivopay.php';

function saas_admin_slugify(string $text): string {
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $text) ?? '', '-'));
    return $slug !== '' ? $slug : ('plan-' . substr(bin2hex(random_bytes(3)), 0, 6));
}

function saas_admin_handle_post(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['saas_admin_action'])) {
        return;
    }

    $action = trim((string)$_POST['saas_admin_action']);
    $tab = trim((string)($_POST['saas_tab'] ?? 'gateway'));
    if (!in_array($tab, ['gateway', 'plans', 'subscribers'], true)) {
        $tab = 'gateway';
    }

    if (!saas_verify_csrf($_POST['saas_csrf'] ?? '', 'admin_saas_csrf')) {
        header('Location: admin.php?saas_tab=' . urlencode($tab) . '&saas_err=' . urlencode('Invalid security token. Please try again.') . '#saasManagerSection');
        exit;
    }

    $pdo = saas_db();

    try {
        if ($action === 'save_rivopay_settings') {
            $tab = 'gateway';
            $status = isset($_POST['status']) && $_POST['status'] === '1' ? 1 : 0;
            $displayName = trim((string)($_POST['display_name'] ?? ''));
            if ($displayName === '') {
                $displayName = 'RivoPay (bKash / Nagad / Rocket)';
            }

            $baseUrl = rtrim(trim((string)($_POST['base_url'] ?? '')), '/');
            if ($baseUrl === '' || !filter_var($baseUrl, FILTER_VALIDATE_URL)) {
                throw new RuntimeException('Please enter a valid RivoPay Base URL (e.g., https://checkout.rivopay.shop).');
            }

            $existing = rivopay_get_config();
            $brandKeyInput = trim((string)($_POST['brand_key'] ?? ''));
            $brandKey = $brandKeyInput !== '' ? $brandKeyInput : (string)$existing['brand_key'];
            if ($brandKey === '') {
                throw new RuntimeException('Brand API Key cannot be empty.');
            }

            $currency = strtoupper(preg_replace('/[^A-Za-z]/', '', (string)($_POST['currency'] ?? 'BDT')) ?: 'BDT');
            $currency = substr($currency, 0, 6);
            $exchangeRate = (float)($_POST['exchange_rate'] ?? 1.0);
            if ($exchangeRate <= 0) {
                $exchangeRate = 1.0;
            }

            $stmt = $pdo->prepare("
                INSERT INTO gateway_settings (gateway_code, status, display_name, base_url, brand_key, currency, exchange_rate, updated_at)
                VALUES ('rivopay', ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT(gateway_code) DO UPDATE SET
                    status = excluded.status,
                    display_name = excluded.display_name,
                    base_url = excluded.base_url,
                    brand_key = excluded.brand_key,
                    currency = excluded.currency,
                    exchange_rate = excluded.exchange_rate,
                    updated_at = excluded.updated_at
            ");
            $stmt->execute([$status, $displayName, $baseUrl, $brandKey, $currency, $exchangeRate, time()]);

            header('Location: admin.php?saas_tab=gateway&saas_msg=' . urlencode('RivoPay gateway settings updated dynamically!') . '#saasManagerSection');
            exit;
        }

        if ($action === 'save_plan') {
            $tab = 'plans';
            $planId = (int)($_POST['plan_id'] ?? 0);
            $name = trim((string)($_POST['name'] ?? ''));
            if ($name === '') {
                throw new RuntimeException('Plan name is required.');
            }

            $slugRaw = trim((string)($_POST['slug'] ?? ''));
            $slug = saas_admin_slugify($slugRaw !== '' ? $slugRaw : $name);
            $description = trim((string)($_POST['description'] ?? ''));
            $priceBdt = max(0.0, (float)($_POST['price_bdt'] ?? 0));
            $durationUnit = strtolower(trim((string)($_POST['duration_unit'] ?? 'day')));
            if (!in_array($durationUnit, saas_allowed_units(), true)) {
                $durationUnit = 'day';
            }
            $durationValue = (int)($_POST['duration_value'] ?? 1);
            if ($durationUnit === 'lifetime') {
                $durationValue = 0;
            } elseif ($durationValue < 1) {
                $durationValue = 1;
            }

            $apiLimit = max(0, (int)($_POST['api_limit'] ?? 0));
            $badgeLabel = trim((string)($_POST['badge_label'] ?? ''));
            $sortOrder = (int)($_POST['sort_order'] ?? 50);
            $isActive = isset($_POST['is_active']) && $_POST['is_active'] === '1' ? 1 : 0;

            if ($planId > 0) {
                $upd = $pdo->prepare("
                    UPDATE subscription_plans
                    SET slug = ?, name = ?, description = ?, price_bdt = ?, duration_value = ?, duration_unit = ?,
                        api_limit = ?, badge_label = ?, is_active = ?, sort_order = ?
                    WHERE id = ?
                ");
                $upd->execute([$slug, $name, $description, $priceBdt, $durationValue, $durationUnit, $apiLimit, $badgeLabel, $isActive, $sortOrder, $planId]);
                $msg = 'Subscription plan "' . $name . '" updated!';
            } else {
                // Ensure unique slug on insert
                $chk = $pdo->prepare("SELECT COUNT(*) FROM subscription_plans WHERE slug = ?");
                $chk->execute([$slug]);
                if ((int)$chk->fetchColumn() > 0) {
                    $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
                }
                $ins = $pdo->prepare("
                    INSERT INTO subscription_plans (slug, name, description, price_bdt, duration_value, duration_unit, api_limit, badge_label, is_active, sort_order, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $ins->execute([$slug, $name, $description, $priceBdt, $durationValue, $durationUnit, $apiLimit, $badgeLabel, $isActive, $sortOrder, time()]);
                $msg = 'New subscription plan "' . $name . '" created!';
            }

            header('Location: admin.php?saas_tab=plans&saas_msg=' . urlencode($msg) . '#saasManagerSection');
            exit;
        }

        if ($action === 'toggle_plan') {
            $tab = 'plans';
            $planId = (int)($_POST['plan_id'] ?? 0);
            $pdo->prepare("UPDATE subscription_plans SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?")
                ->execute([$planId]);
            header('Location: admin.php?saas_tab=plans&saas_msg=' . urlencode('Plan visibility updated.') . '#saasManagerSection');
            exit;
        }

        if ($action === 'delete_plan') {
            $tab = 'plans';
            $planId = (int)($_POST['plan_id'] ?? 0);
            $pdo->prepare("DELETE FROM subscription_plans WHERE id = ?")->execute([$planId]);
            header('Location: admin.php?saas_tab=plans&saas_msg=' . urlencode('Subscription plan deleted.') . '#saasManagerSection');
            exit;
        }

        if ($action === 'grant_user_plan') {
            $tab = 'subscribers';
            $userId = (int)($_POST['user_id'] ?? 0);
            $planId = (int)($_POST['plan_id'] ?? 0);
            $u = saas_get_user_by_id($userId);
            $pStmt = $pdo->prepare("SELECT * FROM subscription_plans WHERE id = ? LIMIT 1");
            $pStmt->execute([$planId]);
            $plan = $pStmt->fetch();
            if (!$u || !$plan) {
                throw new RuntimeException('Select both a valid user and plan.');
            }
            $pdo->exec('BEGIN IMMEDIATE');
            saas_activate_user_plan($pdo, $userId, $plan, 'ADMIN-GRANT-' . date('YmdHis'));
            $pdo->exec('COMMIT');
            header('Location: admin.php?saas_tab=subscribers&saas_msg=' . urlencode('Assigned "' . $plan['name'] . '" to ' . $u['email'] . '.') . '#saasManagerSection');
            exit;
        }

        if ($action === 'expire_user_sub') {
            $tab = 'subscribers';
            $userId = (int)($_POST['user_id'] ?? 0);
            $pdo->prepare("UPDATE user_subscriptions SET status = 'expired', expires_at = ? WHERE user_id = ? AND status = 'active'")
                ->execute([time(), $userId]);
            header('Location: admin.php?saas_tab=subscribers&saas_msg=' . urlencode('Active subscription expired immediately for user #' . $userId . '. All saved API snippets are now locked.') . '#saasManagerSection');
            exit;
        }

        if ($action === 'reset_user_quota') {
            $tab = 'subscribers';
            $userId = (int)($_POST['user_id'] ?? 0);
            $pdo->prepare("UPDATE user_subscriptions SET requests_used = 0 WHERE user_id = ? AND status = 'active'")
                ->execute([$userId]);
            header('Location: admin.php?saas_tab=subscribers&saas_msg=' . urlencode('API quota counter reset to 0 for user #' . $userId . '.') . '#saasManagerSection');
            exit;
        }

        if ($action === 'toggle_user_active') {
            $tab = 'subscribers';
            $userId = (int)($_POST['user_id'] ?? 0);
            $pdo->prepare("UPDATE users SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?")
                ->execute([$userId]);
            header('Location: admin.php?saas_tab=subscribers&saas_msg=' . urlencode('Customer account status toggled.') . '#saasManagerSection');
            exit;
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->exec('ROLLBACK');
        }
        header('Location: admin.php?saas_tab=' . urlencode($tab) . '&saas_err=' . urlencode($e->getMessage()) . '#saasManagerSection');
        exit;
    }
}

function saas_admin_render_section(): void {
    $pdo = saas_db();
    $cfg = rivopay_get_config();
    $origin = saas_origin_url();
    $callbackUrl = $origin . '/payment/rivopay/callback';
    $webhookUrl = $origin . '/payment/rivopay/webhook';
    $csrf = saas_csrf_token('admin_saas_csrf');

    $activeTab = trim((string)($_GET['saas_tab'] ?? 'gateway'));
    if (!in_array($activeTab, ['gateway', 'plans', 'subscribers'], true)) {
        $activeTab = 'gateway';
    }
    $msg = trim((string)($_GET['saas_msg'] ?? ''));
    $err = trim((string)($_GET['saas_err'] ?? ''));

    $plans = $pdo->query("SELECT * FROM subscription_plans ORDER BY sort_order ASC, id ASC")->fetchAll();
    $users = $pdo->query("SELECT id, name, email, phone, api_key, is_active, created_at FROM users ORDER BY id DESC")->fetchAll();

    // Auto-expire overdue subscriptions before rendering admin table
    $now = time();
    $pdo->prepare("UPDATE user_subscriptions SET status = 'expired' WHERE status = 'active' AND expires_at IS NOT NULL AND expires_at <= ?")
        ->execute([$now]);

    $subsByUser = [];
    foreach ($pdo->query("SELECT * FROM user_subscriptions ORDER BY id DESC")->fetchAll() as $s) {
        $uid = (int)$s['user_id'];
        if (!isset($subsByUser[$uid])) {
            $subsByUser[$uid] = $s;
        }
    }

    $orders = $pdo->query("
        SELECT o.*, u.name AS user_name, u.email AS user_email, p.name AS plan_name
        FROM payment_orders o
        LEFT JOIN users u ON u.id = o.user_id
        LEFT JOIN subscription_plans p ON p.id = o.plan_id
        ORDER BY o.id DESC
        LIMIT 30
    ")->fetchAll();

    $activeSubsCount = (int)$pdo->query("SELECT COUNT(*) FROM user_subscriptions WHERE status = 'active'")->fetchColumn();
    $paidOrdersCount = (int)$pdo->query("SELECT COUNT(*) FROM payment_orders WHERE status = 'paid'")->fetchColumn();
    $totalRevenue = (float)$pdo->query("SELECT COALESCE(SUM(amount_bdt), 0) FROM payment_orders WHERE status = 'paid'")->fetchColumn();
    ?>
    <section id="saasManagerSection" class="apple-card p-5 sm:p-6 space-y-5 mt-8">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b pb-4" style="border-color: var(--apple-border);">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-500/12 border border-red-500/25 flex items-center justify-center text-red-500 shrink-0">
                    <i class="fas fa-credit-card text-base"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold tracking-tight" style="color: var(--apple-text-primary);">
                        RivoPay Gateway, Subscription Plans &amp; API Licenses
                    </h2>
                    <p class="text-xs mt-0.5" style="color: var(--apple-text-secondary);">
                        Dynamic payment config, custom durations (minute to lifetime), &amp; real-time token-gated API enforcement.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="account.php" target="_blank" rel="noopener" class="apple-btn-secondary px-3 py-1.5 text-xs flex items-center gap-1.5">
                    <i class="fas fa-external-link-alt text-[10px]"></i>
                    <span>Customer Portal</span>
                </a>
                <div class="inline-flex p-1 rounded-xl border gap-1" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border);">
                    <button type="button" data-saas-tab-btn="gateway" onclick="switchSaasTab('gateway')" class="saas-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all <?php echo $activeTab === 'gateway' ? 'bg-red-500 text-white shadow' : ''; ?>">
                        <i class="fas fa-sliders-h mr-1"></i> RivoPay Settings
                    </button>
                    <button type="button" data-saas-tab-btn="plans" onclick="switchSaasTab('plans')" class="saas-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all <?php echo $activeTab === 'plans' ? 'bg-red-500 text-white shadow' : ''; ?>">
                        <i class="fas fa-layer-group mr-1"></i> Plans (<?php echo count($plans); ?>)
                    </button>
                    <button type="button" data-saas-tab-btn="subscribers" onclick="switchSaasTab('subscribers')" class="saas-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold transition-all <?php echo $activeTab === 'subscribers' ? 'bg-red-500 text-white shadow' : ''; ?>">
                        <i class="fas fa-users mr-1"></i> Users &amp; Orders (<?php echo count($users); ?>)
                    </button>
                </div>
            </div>
        </div>

        <?php if ($msg !== ''): ?>
            <div id="saasAlertSuccess" class="rounded-xl px-4 py-3 text-xs font-semibold border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($err !== ''): ?>
            <div id="saasAlertError" class="rounded-xl px-4 py-3 text-xs font-semibold border border-red-500/30 bg-red-500/10 text-red-400 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        <?php endif; ?>

        <!-- TAB 1: RivoPay Gateway Dynamic Configuration -->
        <div id="saasTab-gateway" class="saas-tab-panel space-y-5 <?php echo $activeTab === 'gateway' ? '' : 'hidden'; ?>">
            <form method="POST" action="admin.php#saasManagerSection" class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                <input type="hidden" name="saas_csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="saas_admin_action" value="save_rivopay_settings">
                <input type="hidden" name="saas_tab" value="gateway">

                <div class="lg:col-span-7 space-y-4">
                    <div class="flex items-center justify-between p-3.5 rounded-xl border" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border);">
                        <div>
                            <div class="text-xs font-bold" style="color: var(--apple-text-primary);">Gateway Status (Enable / Disable)</div>
                            <div class="text-[11px] mt-0.5" style="color: var(--apple-text-secondary);">Turn off to pause new checkouts without breaking active subscriber keys.</div>
                        </div>
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="status" value="1" <?php echo (int)$cfg['status'] === 1 ? 'checked' : ''; ?> class="w-4 h-4 accent-red-500 rounded">
                            <span class="text-xs font-bold <?php echo (int)$cfg['status'] === 1 ? 'text-emerald-400' : 'text-red-400'; ?>">
                                <?php echo (int)$cfg['status'] === 1 ? 'Active' : 'Disabled'; ?>
                            </span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--apple-text-secondary);">Gateway Display Name</label>
                            <input type="text" name="display_name" required value="<?php echo htmlspecialchars((string)$cfg['display_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="w-full px-3 py-2 rounded-xl border text-xs bg-transparent outline-none focus:border-red-500"
                                style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--apple-text-secondary);">Gateway Base URL</label>
                            <input type="url" name="base_url" id="rivoBaseUrlInput" required value="<?php echo htmlspecialchars((string)$cfg['base_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                oninput="updateRivoEndpointPreview(this.value)"
                                placeholder="https://checkout.rivopay.shop"
                                class="w-full px-3 py-2 rounded-xl border text-xs font-mono bg-transparent outline-none focus:border-red-500"
                                style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--apple-text-secondary);">
                            Brand API Key (<code class="text-red-400">API-KEY</code> header)
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="password" name="brand_key" id="rivoBrandKeyInput" autocomplete="off"
                                value="<?php echo htmlspecialchars((string)$cfg['brand_key'], ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="Paste Brand API Key (leave unchanged to keep current)"
                                class="flex-1 px-3 py-2 rounded-xl border text-xs font-mono bg-transparent outline-none focus:border-red-500"
                                style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                            <button type="button" onclick="toggleRivoKeyVisibility()" class="apple-btn-secondary px-3 py-2 text-xs shrink-0" title="Reveal / Hide Brand Key">
                                <i id="rivoKeyEyeIcon" class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-[11px] mt-1" style="color: var(--apple-text-secondary);">
                            Masked by default. Stored server-side in SQLite and never exposed in public HTML/JS.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--apple-text-secondary);">Gateway Currency</label>
                            <input type="text" name="currency" value="<?php echo htmlspecialchars((string)$cfg['currency'], ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="BDT"
                                class="w-full px-3 py-2 rounded-xl border text-xs font-mono uppercase bg-transparent outline-none focus:border-red-500"
                                style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider mb-1" style="color: var(--apple-text-secondary);">Exchange Rate Multiplier</label>
                            <input type="number" step="0.0001" min="0.0001" name="exchange_rate" value="<?php echo htmlspecialchars((string)$cfg['exchange_rate'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="w-full px-3 py-2 rounded-xl border text-xs font-mono bg-transparent outline-none focus:border-red-500"
                                style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="submit" id="saveRivoSettingsBtn" class="apple-btn-primary px-5 py-2.5 text-xs font-bold inline-flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Save RivoPay Configuration</span>
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-5 space-y-3.5">
                    <div class="p-4 rounded-xl border space-y-3" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border);">
                        <div class="text-xs font-bold uppercase tracking-wider flex items-center gap-2" style="color: var(--apple-text-primary);">
                            <i class="fas fa-link text-red-500"></i>
                            <span>Dynamic Endpoints &amp; Webhook URLs</span>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Server Create Endpoint (Auto-Computed)</div>
                            <code id="rivoCreateUrlPreview" class="block px-2.5 py-1.5 rounded-lg text-[11px] font-mono break-all border" style="background: var(--apple-bg-secondary); border-color: var(--apple-border); color: var(--apple-text-primary);">
                                <?php echo htmlspecialchars(rivopay_create_url($cfg), ENT_QUOTES, 'UTF-8'); ?>
                            </code>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Server Verify Endpoint (Auto-Computed)</div>
                            <code id="rivoVerifyUrlPreview" class="block px-2.5 py-1.5 rounded-lg text-[11px] font-mono break-all border" style="background: var(--apple-bg-secondary); border-color: var(--apple-border); color: var(--apple-text-primary);">
                                <?php echo htmlspecialchars(rivopay_verify_url($cfg), ENT_QUOTES, 'UTF-8'); ?>
                            </code>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Callback / Redirect URL (Read-Only)</div>
                            <div class="flex items-center gap-1.5">
                                <input type="text" readonly value="<?php echo htmlspecialchars($callbackUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                    class="flex-1 px-2.5 py-1.5 rounded-lg text-[11px] font-mono border bg-transparent"
                                    style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                                <button type="button" onclick="copySaasText(<?php echo htmlspecialchars(json_encode($callbackUrl), ENT_QUOTES, 'UTF-8'); ?>, 'Callback URL copied!')" class="apple-btn-secondary px-2.5 py-1.5 text-xs">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Server Webhook URL (CSRF-Exempt POST, Read-Only)</div>
                            <div class="flex items-center gap-1.5">
                                <input type="text" readonly value="<?php echo htmlspecialchars($webhookUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                    class="flex-1 px-2.5 py-1.5 rounded-lg text-[11px] font-mono border bg-transparent"
                                    style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                                <button type="button" onclick="copySaasText(<?php echo htmlspecialchars(json_encode($webhookUrl), ENT_QUOTES, 'UTF-8'); ?>, 'Webhook URL copied!')" class="apple-btn-secondary px-2.5 py-1.5 text-xs">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- TAB 2: Subscription Plans Builder (Minute to Lifetime) -->
        <div id="saasTab-plans" class="saas-tab-panel space-y-5 <?php echo $activeTab === 'plans' ? '' : 'hidden'; ?>">
            <form method="POST" action="admin.php#saasManagerSection" id="saasPlanForm" class="p-4 rounded-xl border space-y-3.5" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border);">
                <input type="hidden" name="saas_csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="saas_admin_action" value="save_plan">
                <input type="hidden" name="saas_tab" value="plans">
                <input type="hidden" name="plan_id" id="planEditId" value="0">

                <div class="flex items-center justify-between gap-2">
                    <h3 id="planFormHeading" class="text-xs sm:text-sm font-bold" style="color: var(--apple-text-primary);">
                        Create Custom Subscription Plan
                    </h3>
                    <button type="button" onclick="resetPlanEditor()" class="apple-btn-secondary px-3 py-1 text-[11px]">
                        <i class="fas fa-plus mr-1"></i> New Plan
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Plan Name</label>
                        <input type="text" name="name" id="planEditName" required placeholder="e.g., 5 Minute Flash Pass"
                            class="w-full px-3 py-2 rounded-xl border text-xs bg-transparent outline-none focus:border-red-500"
                            style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Slug (Auto if blank)</label>
                        <input type="text" name="slug" id="planEditSlug" placeholder="5-min-flash"
                            class="w-full px-3 py-2 rounded-xl border text-xs font-mono bg-transparent outline-none focus:border-red-500"
                            style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Price (BDT)</label>
                        <input type="number" step="0.01" min="0" name="price_bdt" id="planEditPrice" required value="50"
                            class="w-full px-3 py-2 rounded-xl border text-xs font-mono bg-transparent outline-none focus:border-red-500"
                            style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">API Call Limit (0 = Unlimited)</label>
                        <input type="number" min="0" name="api_limit" id="planEditLimit" required value="500"
                            class="w-full px-3 py-2 rounded-xl border text-xs font-mono bg-transparent outline-none focus:border-red-500"
                            style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Duration Value</label>
                        <input type="number" min="0" name="duration_value" id="planEditDurVal" required value="1"
                            class="w-full px-3 py-2 rounded-xl border text-xs font-mono bg-transparent outline-none focus:border-red-500"
                            style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Duration Unit</label>
                        <select name="duration_unit" id="planEditDurUnit"
                            class="w-full px-3 py-2 rounded-xl border text-xs bg-transparent outline-none focus:border-red-500"
                            style="border-color: var(--apple-border); color: var(--apple-text-primary); background: var(--apple-bg-secondary);">
                            <?php foreach (saas_allowed_units() as $u): ?>
                                <option value="<?php echo $u; ?>"><?php echo ucfirst($u); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Badge Tag</label>
                        <input type="text" name="badge_label" id="planEditTag" placeholder="Popular / VIP"
                            class="w-full px-3 py-2 rounded-xl border text-xs bg-transparent outline-none focus:border-red-500"
                            style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Sort Order</label>
                        <input type="number" name="sort_order" id="planEditSort" value="60"
                            class="w-full px-3 py-2 rounded-xl border text-xs font-mono bg-transparent outline-none focus:border-red-500"
                            style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                    </div>
                    <div class="flex items-end pb-1.5">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold" style="color: var(--apple-text-primary);">
                            <input type="checkbox" name="is_active" id="planEditActive" value="1" checked class="w-4 h-4 accent-red-500 rounded">
                            <span>Active for Purchase</span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-[11px] font-semibold mb-1" style="color: var(--apple-text-secondary);">Description</label>
                        <input type="text" name="description" id="planEditDesc" placeholder="Short pitch visible to customers on checkout page"
                            class="w-full px-3 py-2 rounded-xl border text-xs bg-transparent outline-none focus:border-red-500"
                            style="border-color: var(--apple-border); color: var(--apple-text-primary);">
                    </div>
                    <button type="submit" id="savePlanBtn" class="apple-btn-primary px-5 py-2 text-xs font-bold shrink-0">
                        <i class="fas fa-check mr-1"></i> Save Plan
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto rounded-xl border" style="border-color: var(--apple-border);">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b uppercase text-[10px] tracking-wider" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border); color: var(--apple-text-secondary);">
                            <th class="py-2.5 px-3">Plan</th>
                            <th class="py-2.5 px-3">Duration</th>
                            <th class="py-2.5 px-3">Price</th>
                            <th class="py-2.5 px-3">API Limit</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--apple-border);">
                        <?php foreach ($plans as $plan): ?>
                            <tr class="hover:bg-white/[0.02]">
                                <td class="py-2.5 px-3">
                                    <div class="font-bold" style="color: var(--apple-text-primary);">
                                        <?php echo htmlspecialchars((string)$plan['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if (!empty($plan['badge_label'])): ?>
                                            <span class="ml-1 px-1.5 py-0.5 rounded bg-red-500/15 text-red-400 text-[10px] font-bold">
                                                <?php echo htmlspecialchars((string)$plan['badge_label'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[11px] font-mono opacity-65"><?php echo htmlspecialchars((string)$plan['slug'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="py-2.5 px-3 font-medium" style="color: var(--apple-text-primary);">
                                    <?php echo htmlspecialchars(saas_format_duration((int)$plan['duration_value'], (string)$plan['duration_unit']), ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="py-2.5 px-3 font-mono font-bold text-red-400">
                                    <?php echo number_format((float)$plan['price_bdt'], 2); ?> BDT
                                </td>
                                <td class="py-2.5 px-3 font-mono" style="color: var(--apple-text-primary);">
                                    <?php echo (int)$plan['api_limit'] > 0 ? number_format((int)$plan['api_limit']) . ' calls' : 'Unlimited'; ?>
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo (int)$plan['is_active'] === 1 ? 'bg-emerald-500/15 text-emerald-400' : 'bg-zinc-500/20 text-zinc-400'; ?>">
                                        <?php echo (int)$plan['is_active'] === 1 ? 'Active' : 'Hidden'; ?>
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" onclick='loadPlanIntoEditor(<?php echo htmlspecialchars(json_encode($plan), ENT_QUOTES, 'UTF-8'); ?>)' class="apple-btn-secondary px-2.5 py-1 text-[11px]">
                                            Edit
                                        </button>
                                        <form method="POST" action="admin.php#saasManagerSection" class="inline">
                                            <input type="hidden" name="saas_csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="saas_admin_action" value="toggle_plan">
                                            <input type="hidden" name="saas_tab" value="plans">
                                            <input type="hidden" name="plan_id" value="<?php echo (int)$plan['id']; ?>">
                                            <button type="submit" class="apple-btn-secondary px-2.5 py-1 text-[11px]">
                                                <?php echo (int)$plan['is_active'] === 1 ? 'Disable' : 'Enable'; ?>
                                            </button>
                                        </form>
                                        <form method="POST" action="admin.php#saasManagerSection" class="inline" onsubmit="return confirm('Delete this plan?');">
                                            <input type="hidden" name="saas_csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="saas_admin_action" value="delete_plan">
                                            <input type="hidden" name="saas_tab" value="plans">
                                            <input type="hidden" name="plan_id" value="<?php echo (int)$plan['id']; ?>">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-500/15 text-red-400 hover:bg-red-500 hover:text-white text-[11px] font-semibold transition-all">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: Subscribers, API Licenses & Payment Orders -->
        <div id="saasTab-subscribers" class="saas-tab-panel space-y-5 <?php echo $activeTab === 'subscribers' ? '' : 'hidden'; ?>">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3.5 rounded-xl border" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border);">
                    <div class="text-[11px] font-semibold" style="color: var(--apple-text-secondary);">Registered Customers</div>
                    <div class="text-lg font-extrabold mt-0.5" style="color: var(--apple-text-primary);"><?php echo count($users); ?></div>
                </div>
                <div class="p-3.5 rounded-xl border" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border);">
                    <div class="text-[11px] font-semibold" style="color: var(--apple-text-secondary);">Active Licenses</div>
                    <div class="text-lg font-extrabold mt-0.5 text-emerald-400"><?php echo $activeSubsCount; ?></div>
                </div>
                <div class="p-3.5 rounded-xl border" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border);">
                    <div class="text-[11px] font-semibold" style="color: var(--apple-text-secondary);">Paid RivoPay Orders</div>
                    <div class="text-lg font-extrabold mt-0.5" style="color: var(--apple-text-primary);"><?php echo $paidOrdersCount; ?></div>
                </div>
                <div class="p-3.5 rounded-xl border" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border);">
                    <div class="text-[11px] font-semibold" style="color: var(--apple-text-secondary);">Total Revenue</div>
                    <div class="text-lg font-extrabold mt-0.5 text-red-400"><?php echo number_format($totalRevenue, 2); ?> BDT</div>
                </div>
            </div>

            <?php if (!empty($users) && !empty($plans)): ?>
                <form method="POST" action="admin.php#saasManagerSection" class="p-3.5 rounded-xl border flex flex-wrap items-center gap-3" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border);">
                    <input type="hidden" name="saas_csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="saas_admin_action" value="grant_user_plan">
                    <input type="hidden" name="saas_tab" value="subscribers">
                    <span class="text-xs font-bold" style="color: var(--apple-text-primary);">Manual Plan Grant:</span>
                    <select name="user_id" class="px-3 py-1.5 rounded-xl border text-xs bg-transparent" style="border-color: var(--apple-border); color: var(--apple-text-primary); background: var(--apple-bg-secondary);">
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo (int)$u['id']; ?>">#<?php echo (int)$u['id']; ?> — <?php echo htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="plan_id" class="px-3 py-1.5 rounded-xl border text-xs bg-transparent" style="border-color: var(--apple-border); color: var(--apple-text-primary); background: var(--apple-bg-secondary);">
                        <?php foreach ($plans as $p): ?>
                            <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo number_format((float)$p['price_bdt'], 0); ?> BDT)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="apple-btn-primary px-4 py-1.5 text-xs font-bold">Activate Plan Now</button>
                </form>
            <?php endif; ?>

            <div class="overflow-x-auto rounded-xl border" style="border-color: var(--apple-border);">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b uppercase text-[10px] tracking-wider" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border); color: var(--apple-text-secondary);">
                            <th class="py-2.5 px-3">Customer</th>
                            <th class="py-2.5 px-3">License API Key</th>
                            <th class="py-2.5 px-3">Subscription</th>
                            <th class="py-2.5 px-3">Quota</th>
                            <th class="py-2.5 px-3 text-right">Controls</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--apple-border);">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" class="py-6 text-center text-xs" style="color: var(--apple-text-secondary);">No customers registered yet.</td>
                            </tr>
                        <?php else: foreach ($users as $u):
                            $sub = $subsByUser[(int)$u['id']] ?? null;
                            $subActive = $sub && $sub['status'] === 'active';
                        ?>
                            <tr>
                                <td class="py-2.5 px-3">
                                    <div class="font-bold" style="color: var(--apple-text-primary);"><?php echo htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-[11px]" style="color: var(--apple-text-secondary);"><?php echo htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="py-2.5 px-3 font-mono text-[11px]">
                                    <span><?php echo htmlspecialchars(substr($u['api_key'], 0, 14) . '...' . substr($u['api_key'], -4), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <button type="button" onclick="copySaasText(<?php echo htmlspecialchars(json_encode($u['api_key']), ENT_QUOTES, 'UTF-8'); ?>, 'User API key copied!')" class="ml-1.5 text-red-400 hover:underline">Copy</button>
                                </td>
                                <td class="py-2.5 px-3">
                                    <?php if ($sub): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $subActive ? 'bg-emerald-500/15 text-emerald-400' : 'bg-red-500/15 text-red-400'; ?>">
                                            <?php echo htmlspecialchars($sub['plan_name'] . ' (' . strtoupper($sub['status']) . ')', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <div class="text-[10px] mt-0.5" style="color: var(--apple-text-secondary);">
                                            <?php echo $sub['expires_at'] === null ? 'Never Expires' : 'Expires: ' . date('Y-m-d H:i:s', (int)$sub['expires_at']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[11px] opacity-60">No Plan</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2.5 px-3 font-mono text-[11px]" style="color: var(--apple-text-primary);">
                                    <?php if ($sub): ?>
                                        <?php echo (int)$sub['requests_used']; ?> / <?php echo (int)$sub['api_limit'] > 0 ? (int)$sub['api_limit'] : 'Unlimited'; ?>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <div class="inline-flex flex-wrap justify-end gap-1">
                                        <?php if ($subActive): ?>
                                            <form method="POST" action="admin.php#saasManagerSection" class="inline">
                                                <input type="hidden" name="saas_csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="saas_admin_action" value="expire_user_sub">
                                                <input type="hidden" name="saas_tab" value="subscribers">
                                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                <button type="submit" class="px-2 py-1 rounded-lg bg-red-500/15 text-red-400 hover:bg-red-500 hover:text-white text-[10px] font-bold">Expire Now</button>
                                            </form>
                                            <form method="POST" action="admin.php#saasManagerSection" class="inline">
                                                <input type="hidden" name="saas_csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="saas_admin_action" value="reset_user_quota">
                                                <input type="hidden" name="saas_tab" value="subscribers">
                                                <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                                <button type="submit" class="apple-btn-secondary px-2 py-1 text-[10px]">Reset Quota</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="admin.php#saasManagerSection" class="inline">
                                            <input type="hidden" name="saas_csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="saas_admin_action" value="toggle_user_active">
                                            <input type="hidden" name="saas_tab" value="subscribers">
                                            <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                            <button type="submit" class="apple-btn-secondary px-2 py-1 text-[10px]">
                                                <?php echo (int)$u['is_active'] === 1 ? 'Suspend' : 'Unsuspend'; ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="overflow-x-auto rounded-xl border" style="border-color: var(--apple-border);">
                <div class="px-3 py-2 border-b text-xs font-bold" style="background: var(--apple-bg-tertiary); border-color: var(--apple-border); color: var(--apple-text-primary);">
                    Recent RivoPay Transactions
                </div>
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b uppercase text-[10px] tracking-wider" style="border-color: var(--apple-border); color: var(--apple-text-secondary);">
                            <th class="py-2 px-3">Order Ref</th>
                            <th class="py-2 px-3">Customer / Plan</th>
                            <th class="py-2 px-3">Amount</th>
                            <th class="py-2 px-3">Transaction ID &amp; Method</th>
                            <th class="py-2 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--apple-border);">
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="5" class="py-5 text-center text-xs" style="color: var(--apple-text-secondary);">No orders recorded yet.</td></tr>
                        <?php else: foreach ($orders as $o): ?>
                            <tr>
                                <td class="py-2 px-3 font-mono text-[11px] font-semibold" style="color: var(--apple-text-primary);">
                                    <?php echo htmlspecialchars($o['order_ref'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>
                                <td class="py-2 px-3">
                                    <div class="font-semibold" style="color: var(--apple-text-primary);"><?php echo htmlspecialchars((string)($o['user_email'] ?? 'User #' . $o['user_id']), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-[10px]" style="color: var(--apple-text-secondary);"><?php echo htmlspecialchars((string)($o['plan_name'] ?? 'Plan #' . $o['plan_id']), ENT_QUOTES, 'UTF-8'); ?></div>
                                </td>
                                <td class="py-2 px-3 font-mono font-bold"><?php echo number_format((float)$o['amount_bdt'], 2); ?> <?php echo htmlspecialchars((string)$o['currency'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="py-2 px-3 font-mono text-[11px]">
                                    <?php echo htmlspecialchars((string)($o['transaction_id'] ?: '—'), ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if (!empty($o['payment_method'])): ?>
                                        <span class="opacity-70">(<?php echo htmlspecialchars((string)$o['payment_method'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $o['status'] === 'paid' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-amber-500/15 text-amber-400'; ?>">
                                        <?php echo htmlspecialchars(strtoupper((string)$o['status']), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
    function switchSaasTab(tabName) {
        document.querySelectorAll('.saas-tab-panel').forEach(function (el) {
            el.classList.add('hidden');
        });
        var target = document.getElementById('saasTab-' + tabName);
        if (target) target.classList.remove('hidden');

        document.querySelectorAll('.saas-tab-btn').forEach(function (btn) {
            var active = btn.getAttribute('data-saas-tab-btn') === tabName;
            btn.classList.toggle('bg-red-500', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('shadow', active);
        });
    }

    function toggleRivoKeyVisibility() {
        var inp = document.getElementById('rivoBrandKeyInput');
        var icon = document.getElementById('rivoKeyEyeIcon');
        if (!inp || !icon) return;
        var isPass = inp.type === 'password';
        inp.type = isPass ? 'text' : 'password';
        icon.className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
    }

    function updateRivoEndpointPreview(val) {
        var clean = String(val || '').replace(/\/+$/, '');
        var cEl = document.getElementById('rivoCreateUrlPreview');
        var vEl = document.getElementById('rivoVerifyUrlPreview');
        if (cEl) cEl.textContent = (clean || 'https://checkout.rivopay.shop') + '/api/payment/create';
        if (vEl) vEl.textContent = (clean || 'https://checkout.rivopay.shop') + '/api/payment/verify';
    }

    function copySaasText(txt, toastMsg) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(txt);
        }
        if (typeof showToast === 'function') {
            showToast(toastMsg || 'Copied!');
        }
    }

    function loadPlanIntoEditor(plan) {
        document.getElementById('planFormHeading').textContent = 'Edit Plan #' + plan.id + ' — ' + plan.name;
        document.getElementById('planEditId').value = plan.id;
        document.getElementById('planEditName').value = plan.name || '';
        document.getElementById('planEditSlug').value = plan.slug || '';
        document.getElementById('planEditPrice').value = plan.price_bdt;
        document.getElementById('planEditLimit').value = plan.api_limit;
        document.getElementById('planEditDurVal').value = plan.duration_value;
        document.getElementById('planEditDurUnit').value = plan.duration_unit || 'day';
        document.getElementById('planEditTag').value = plan.badge_label || '';
        document.getElementById('planEditSort').value = plan.sort_order || 50;
        document.getElementById('planEditDesc').value = plan.description || '';
        document.getElementById('planEditActive').checked = Number(plan.is_active) === 1;
        document.getElementById('saasPlanForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function resetPlanEditor() {
        document.getElementById('planFormHeading').textContent = 'Create Custom Subscription Plan';
        document.getElementById('planEditId').value = '0';
        document.getElementById('saasPlanForm').reset();
        document.getElementById('planEditActive').checked = true;
    }
    </script>
    <?php
}
