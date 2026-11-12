<?php
/**
 * TURKY HUB — SaaS Subscription, API Key License & Payment Gateway Database Engine
 * Atomic SQLite (PDO) storage with automatic schema initialization & seeding.
 */

if (!defined('SAAS_DB_LOADED')) {
    define('SAAS_DB_LOADED', true);
}

function saas_db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dataDir = dirname(__DIR__) . '/data';
    if (!is_dir($dataDir)) {
        @mkdir($dataDir, 0755, true);
    }

    $dbPath = $dataDir . '/turky_saas.db';
    $pdo = new PDO('sqlite:' . $dbPath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL;');
    $pdo->exec('PRAGMA foreign_keys = ON;');
    $pdo->exec('PRAGMA busy_timeout = 8000;');

    saas_init_schema($pdo);
    return $pdo;
}

function saas_init_schema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gateway_settings (
            gateway_code TEXT PRIMARY KEY,
            status INTEGER NOT NULL DEFAULT 1,
            display_name TEXT NOT NULL,
            base_url TEXT NOT NULL,
            brand_key TEXT NOT NULL,
            currency TEXT NOT NULL DEFAULT 'BDT',
            exchange_rate REAL NOT NULL DEFAULT 1.0,
            updated_at INTEGER NOT NULL
        );

        CREATE TABLE IF NOT EXISTS subscription_plans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            description TEXT NOT NULL DEFAULT '',
            price_bdt REAL NOT NULL DEFAULT 0,
            duration_value INTEGER NOT NULL DEFAULT 1,
            duration_unit TEXT NOT NULL DEFAULT 'day',
            api_limit INTEGER NOT NULL DEFAULT 0,
            badge_label TEXT NOT NULL DEFAULT '',
            is_active INTEGER NOT NULL DEFAULT 1,
            sort_order INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL
        );

        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT NOT NULL DEFAULT '',
            password_hash TEXT NOT NULL,
            api_key TEXT NOT NULL UNIQUE,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at INTEGER NOT NULL
        );

        CREATE TABLE IF NOT EXISTS user_subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            plan_id INTEGER,
            plan_name TEXT NOT NULL,
            order_ref TEXT NOT NULL,
            api_limit INTEGER NOT NULL DEFAULT 0,
            requests_used INTEGER NOT NULL DEFAULT 0,
            starts_at INTEGER NOT NULL,
            expires_at INTEGER NULL,
            status TEXT NOT NULL DEFAULT 'active',
            created_at INTEGER NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE INDEX IF NOT EXISTS idx_subs_user_status ON user_subscriptions(user_id, status);

        CREATE TABLE IF NOT EXISTS payment_orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_ref TEXT NOT NULL UNIQUE,
            user_id INTEGER NOT NULL,
            plan_id INTEGER NOT NULL,
            gateway_code TEXT NOT NULL DEFAULT 'rivopay',
            amount_bdt REAL NOT NULL,
            currency TEXT NOT NULL DEFAULT 'BDT',
            status TEXT NOT NULL DEFAULT 'pending',
            transaction_id TEXT NULL,
            payment_method TEXT NOT NULL DEFAULT '',
            payment_fee REAL NOT NULL DEFAULT 0,
            gateway_response_json TEXT NOT NULL DEFAULT '',
            paid_at INTEGER NULL,
            created_at INTEGER NOT NULL,
            updated_at INTEGER NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE UNIQUE INDEX IF NOT EXISTS idx_orders_trx_unique ON payment_orders(transaction_id) WHERE transaction_id IS NOT NULL AND transaction_id != '';

        CREATE TABLE IF NOT EXISTS api_usage_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            subscription_id INTEGER NOT NULL,
            endpoint TEXT NOT NULL,
            badge_id TEXT NOT NULL DEFAULT '',
            ip_address TEXT NOT NULL DEFAULT '',
            created_at INTEGER NOT NULL
        );
    ");

    $now = time();

    // Seed default RivoPay gateway config if missing
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM gateway_settings WHERE gateway_code = 'rivopay'");
    $stmt->execute();
    if ((int)$stmt->fetchColumn() === 0) {
        $ins = $pdo->prepare("
            INSERT INTO gateway_settings (gateway_code, status, display_name, base_url, brand_key, currency, exchange_rate, updated_at)
            VALUES ('rivopay', 1, ?, ?, ?, 'BDT', 1.0, ?)
        ");
        $ins->execute([
            'RivoPay (bKash / Nagad / Rocket)',
            'https://checkout.rivopay.shop',
            'D5ftiUZJOV9VMDpoYaeZUGNZswXgDmhwLOZrOynFcEToyJECAE',
            $now,
        ]);
    }

    // Seed starter plans only when table is empty
    $planCount = (int)$pdo->query("SELECT COUNT(*) FROM subscription_plans")->fetchColumn();
    if ($planCount === 0) {
        $seedPlans = [
            ['1-min-demo', '1 Minute Demo Pass', 'Instant micro-duration pass for live API testing & expiry demo.', 5.0, 1, 'minute', 50, 'Test', 1, 10],
            ['1-hour-flash', '1 Hour Flash Pass', 'Short-term access with 500 licensed badge & JSON API calls.', 20.0, 1, 'hour', 500, 'Quick', 1, 20],
            ['1-day-pro', '1 Day Pro Pass', '24-hour full project integration with 3,000 API & SVG requests.', 50.0, 1, 'day', 3000, 'Daily', 1, 30],
            ['1-year-unlimited', '1 Year Studio Unlimited', 'Full 365-day commercial license with unlimited API requests.', 999.0, 1, 'year', 0, 'Popular', 1, 40],
            ['lifetime-master', 'Lifetime Master License', 'Never expires — unlimited badge renders, SVG, player & JSON APIs forever.', 2499.0, 0, 'lifetime', 0, 'Lifetime', 1, 50],
        ];
        $insPlan = $pdo->prepare("
            INSERT INTO subscription_plans (slug, name, description, price_bdt, duration_value, duration_unit, api_limit, badge_label, is_active, sort_order, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($seedPlans as $p) {
            $insPlan->execute(array_merge($p, [$now]));
        }
    }
}

function saas_origin_url(): string {
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ||
        (($_SERVER['SERVER_PORT'] ?? 80) == 443)
    );
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8085';
    // Handle deployments nested inside a subdirectory
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $baseDir = '';
    if (preg_match('#^(.*?)/(?:api/license|payment/rivopay/callback|payment/rivopay/webhook|payment/rivopay|admin)(?:/|$)#', $script, $m)) {
        $baseDir = rtrim($m[1], '/');
    } else {
        $dir = rtrim(dirname($script), '/');
        if ($dir !== '' && $dir !== '.') {
            $baseDir = $dir;
        }
    }
    return $scheme . '://' . $host . $baseDir;
}

function saas_generate_api_key(): string {
    return 'th_live_' . bin2hex(random_bytes(18));
}

function saas_generate_order_ref(): string {
    return 'THPAY-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function saas_allowed_units(): array {
    return ['minute', 'hour', 'day', 'month', 'year', 'lifetime'];
}

function saas_compute_expiry(int $startsAt, int $value, string $unit): ?int {
    $unit = strtolower(trim($unit));
    if ($unit === 'lifetime' || $value <= 0) {
        return null;
    }
    switch ($unit) {
        case 'minute':
            return $startsAt + ($value * 60);
        case 'hour':
            return $startsAt + ($value * 3600);
        case 'day':
            return $startsAt + ($value * 86400);
        case 'month':
            $ts = strtotime('+' . $value . ' months', $startsAt);
            return $ts !== false ? $ts : ($startsAt + ($value * 30 * 86400));
        case 'year':
            $ts = strtotime('+' . $value . ' years', $startsAt);
            return $ts !== false ? $ts : ($startsAt + ($value * 365 * 86400));
        default:
            return $startsAt + ($value * 86400);
    }
}

function saas_format_duration(int $value, string $unit): string {
    $unit = strtolower(trim($unit));
    if ($unit === 'lifetime' || $value <= 0) {
        return 'Lifetime / Unlimited';
    }
    $label = ucfirst($unit) . ($value === 1 ? '' : 's');
    return $value . ' ' . $label;
}

function saas_csrf_token(string $bucket = 'saas_csrf'): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION[$bucket])) {
        $_SESSION[$bucket] = bin2hex(random_bytes(24));
    }
    return $_SESSION[$bucket];
}

function saas_verify_csrf(?string $token, string $bucket = 'saas_csrf'): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $expected = $_SESSION[$bucket] ?? '';
    return ($expected !== '' && is_string($token) && hash_equals($expected, $token));
}

function saas_get_user_by_id(int $userId): ?array {
    $stmt = saas_db()->prepare("SELECT id, name, email, phone, api_key, is_active, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function saas_get_active_subscription(int $userId): ?array {
    $pdo = saas_db();
    $stmt = $pdo->prepare("
        SELECT * FROM user_subscriptions
        WHERE user_id = ?
        ORDER BY CASE WHEN status = 'active' THEN 0 ELSE 1 END, id DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $sub = $stmt->fetch();
    if (!$sub) {
        return null;
    }
    if ($sub['status'] === 'active' && $sub['expires_at'] !== null && time() >= (int)$sub['expires_at']) {
        $upd = $pdo->prepare("UPDATE user_subscriptions SET status = 'expired' WHERE id = ?");
        $upd->execute([$sub['id']]);
        $sub['status'] = 'expired';
    }
    return $sub;
}

function saas_activate_user_plan(PDO $pdo, int $userId, array $plan, string $orderRef): array {
    $now = time();
    $expiresAt = saas_compute_expiry($now, (int)$plan['duration_value'], (string)$plan['duration_unit']);

    // Expire/supersede any existing active subscription rows for clean state
    $pdo->prepare("UPDATE user_subscriptions SET status = 'superseded' WHERE user_id = ? AND status = 'active'")
        ->execute([$userId]);

    $ins = $pdo->prepare("
        INSERT INTO user_subscriptions (user_id, plan_id, plan_name, order_ref, api_limit, requests_used, starts_at, expires_at, status, created_at)
        VALUES (?, ?, ?, ?, ?, 0, ?, ?, 'active', ?)
    ");
    $ins->execute([
        $userId,
        (int)$plan['id'],
        (string)$plan['name'],
        $orderRef,
        (int)$plan['api_limit'],
        $now,
        $expiresAt,
        $now,
    ]);
    $subId = (int)$pdo->lastInsertId();
    $row = $pdo->query("SELECT * FROM user_subscriptions WHERE id = " . $subId)->fetch();
    return $row ?: [];
}

/**
 * Atomically validate API license key, check expiration & quota, and record consumption.
 */
function saas_validate_and_consume_key(string $apiKey, string $endpoint, string $badgeId = '', bool $increment = true): array {
    $apiKey = trim($apiKey);
    if ($apiKey === '') {
        return [
            'valid' => false,
            'code' => 'missing_api_key',
            'http' => 401,
            'message' => 'Missing subscription API key. Pass ?key=YOUR_API_KEY or X-API-Key header.',
        ];
    }

    $pdo = saas_db();
    try {
        $pdo->exec('BEGIN IMMEDIATE');

        $uStmt = $pdo->prepare("SELECT id, name, email, is_active FROM users WHERE api_key = ? LIMIT 1");
        $uStmt->execute([$apiKey]);
        $user = $uStmt->fetch();
        if (!$user) {
            $pdo->exec('COMMIT');
            return [
                'valid' => false,
                'code' => 'invalid_api_key',
                'http' => 403,
                'message' => 'Invalid API license key.',
            ];
        }
        if ((int)$user['is_active'] !== 1) {
            $pdo->exec('COMMIT');
            return [
                'valid' => false,
                'code' => 'account_suspended',
                'http' => 403,
                'message' => 'Account suspended. Contact TURKY HUB support.',
            ];
        }

        $sStmt = $pdo->prepare("
            SELECT * FROM user_subscriptions
            WHERE user_id = ? AND status = 'active'
            ORDER BY id DESC LIMIT 1
        ");
        $sStmt->execute([(int)$user['id']]);
        $sub = $sStmt->fetch();

        if (!$sub) {
            $lastStmt = $pdo->prepare("SELECT status, plan_name, expires_at FROM user_subscriptions WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $lastStmt->execute([(int)$user['id']]);
            $last = $lastStmt->fetch();
            $pdo->exec('COMMIT');
            $code = ($last && $last['status'] === 'expired') ? 'subscription_expired' : 'no_active_subscription';
            return [
                'valid' => false,
                'code' => $code,
                'http' => 403,
                'message' => $code === 'subscription_expired'
                    ? 'Your subscription plan has expired. Renew your plan to re-activate saved snippets.'
                    : 'No active subscription plan found. Purchase a plan at TURKY HUB.',
            ];
        }

        $now = time();
        if ($sub['expires_at'] !== null && $now >= (int)$sub['expires_at']) {
            $pdo->prepare("UPDATE user_subscriptions SET status = 'expired' WHERE id = ?")->execute([(int)$sub['id']]);
            $pdo->exec('COMMIT');
            return [
                'valid' => false,
                'code' => 'subscription_expired',
                'http' => 403,
                'message' => 'Your subscription plan has expired. Renew your plan to re-activate saved snippets.',
            ];
        }

        $limit = (int)$sub['api_limit'];
        $used = (int)$sub['requests_used'];
        if ($limit > 0 && $used >= $limit) {
            $pdo->exec('COMMIT');
            return [
                'valid' => false,
                'code' => 'api_limit_exceeded',
                'http' => 429,
                'message' => 'API request quota exceeded (' . $used . '/' . $limit . '). Upgrade or renew your plan.',
                'limit' => $limit,
                'used' => $used,
                'remaining' => 0,
            ];
        }

        if ($increment) {
            $used++;
            $pdo->prepare("UPDATE user_subscriptions SET requests_used = requests_used + 1 WHERE id = ?")
                ->execute([(int)$sub['id']]);
            $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
            $pdo->prepare("INSERT INTO api_usage_logs (user_id, subscription_id, endpoint, badge_id, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([(int)$user['id'], (int)$sub['id'], $endpoint, $badgeId, $ip, $now]);
        }

        $pdo->exec('COMMIT');

        return [
            'valid' => true,
            'code' => 'active',
            'http' => 200,
            'user' => $user,
            'subscription_id' => (int)$sub['id'],
            'plan_name' => (string)$sub['plan_name'],
            'limit' => $limit,
            'used' => $used,
            'remaining' => $limit > 0 ? max(0, $limit - $used) : null,
            'expires_at' => $sub['expires_at'] !== null ? (int)$sub['expires_at'] : null,
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->exec('ROLLBACK');
        }
        return [
            'valid' => false,
            'code' => 'license_check_error',
            'http' => 500,
            'message' => 'Temporary license validation error.',
        ];
    }
}

function saas_extract_api_key(): string {
    $key = trim((string)($_GET['key'] ?? $_GET['api_key'] ?? ''));
    if ($key !== '') {
        return $key;
    }
    $headerKey = trim((string)($_SERVER['HTTP_X_API_KEY'] ?? ''));
    if ($headerKey !== '') {
        return $headerKey;
    }
    $auth = trim((string)($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
    if (stripos($auth, 'Bearer ') === 0) {
        return trim(substr($auth, 7));
    }
    return '';
}
