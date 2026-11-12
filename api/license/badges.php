<?php
/**
 * TURKY HUB — Subscription-Gated JSON REST API
 * Requires active subscription API key via ?key=..., X-API-Key header, or Authorization: Bearer ...
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once dirname(__DIR__, 2) . '/includes/saas_db.php';

$apiKey = saas_extract_api_key();
$queryId = trim((string)($_GET['id'] ?? $_GET['badge'] ?? ''));

$check = saas_validate_and_consume_key($apiKey, 'api_badges_json', $queryId, true);
if (!$check['valid']) {
    http_response_code((int)($check['http'] ?? 403));
    echo json_encode([
        'ok' => false,
        'error' => $check['code'],
        'message' => $check['message'],
        'renew_url' => saas_origin_url() . '/account.php',
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

header('X-RateLimit-Limit: ' . ((int)$check['limit'] > 0 ? $check['limit'] : 'unlimited'));
if ($check['remaining'] !== null) {
    header('X-RateLimit-Remaining: ' . $check['remaining']);
}
if ($check['expires_at'] !== null) {
    header('X-Plan-Expires-At: ' . gmdate('c', (int)$check['expires_at']));
}

$badgesFile = dirname(__DIR__) . '/badges.json';
$rawData = file_exists($badgesFile) ? json_decode(file_get_contents($badgesFile), true) : ['badges' => []];
$badges = $rawData['badges'] ?? [];
$origin = saas_origin_url();
$encodedKey = urlencode($apiKey);

$licensedBadges = [];
foreach ($badges as $b) {
    $bid = (string)($b['id'] ?? '');
    $b['cdn_urls'] = [
        'licensed_png' => "{$origin}/api/license/asset.php?id={$bid}&format=png&key={$encodedKey}",
        'animated_svg' => "{$origin}/api/license/asset.php?id={$bid}&format=svg&key={$encodedKey}",
        'animated_player' => "{$origin}/api/license/player.php?id={$bid}&key={$encodedKey}",
        'json_api' => "{$origin}/api/license/badges.php?id={$bid}&key={$encodedKey}",
    ];
    $b['direct_url'] = $b['cdn_urls']['animated_svg'];
    $licensedBadges[] = $b;
}

if ($queryId !== '') {
    $found = null;
    $qLower = strtolower($queryId);
    foreach ($licensedBadges as $b) {
        if ((string)($b['id'] ?? '') === $queryId || strtolower((string)($b['slug'] ?? '')) === $qLower) {
            $found = $b;
            break;
        }
    }
    if (!$found) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'badge_not_found', 'message' => 'Requested badge ID or slug was not found.']);
        exit;
    }
    echo json_encode([
        'ok' => true,
        'subscription' => [
            'plan' => $check['plan_name'],
            'expires_at' => $check['expires_at'],
            'limit' => $check['limit'],
            'used' => $check['used'],
            'remaining' => $check['remaining'],
        ],
        'badge' => $found,
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

echo json_encode([
    'ok' => true,
    'project' => 'TURKY HUB — Licensed Badges API',
    'total_badges' => count($licensedBadges),
    'subscription' => [
        'plan' => $check['plan_name'],
        'expires_at' => $check['expires_at'],
        'limit' => $check['limit'],
        'used' => $check['used'],
        'remaining' => $check['remaining'],
    ],
    'sdk_url' => "{$origin}/api/license/sdk.php?key={$encodedKey}",
    'badges' => $licensedBadges,
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
