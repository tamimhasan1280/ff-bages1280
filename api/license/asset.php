<?php
/**
 * TURKY HUB — Subscription-Gated Animated SVG & PNG Asset Delivery
 * Enforces real-time expiration & API quota with strict no-cache headers so copied URLs stop when expired.
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once dirname(__DIR__, 2) . '/includes/saas_db.php';

$apiKey = saas_extract_api_key();
$badgeId = trim((string)($_GET['id'] ?? ''));
$format = strtolower(trim((string)($_GET['format'] ?? 'svg')));
$sig = trim((string)($_GET['sig'] ?? ''));

// When loaded as an internal <img> sub-resource from an already-validated sdk.php call, verify HMAC signature & check validity without double-charging quota
$increment = true;
if ($sig !== '' && $badgeId !== '' && $apiKey !== '') {
    $expectedSig = hash_hmac('sha256', $apiKey . '|' . $badgeId, __DIR__ . 'TURKY_SDK_SALT_V1');
    if (hash_equals($expectedSig, $sig)) {
        $increment = false;
    }
}

$check = saas_validate_and_consume_key($apiKey, 'asset_' . $format, $badgeId, $increment);
if (!$check['valid']) {
    http_response_code((int)($check['http'] ?? 403));
    if ($format === 'svg') {
        header('Content-Type: image/svg+xml; charset=UTF-8');
        $label = htmlspecialchars($check['code'] === 'api_limit_exceeded' ? 'QUOTA FULL' : 'EXPIRED', ENT_QUOTES, 'UTF-8');
        echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="120" height="120"><rect x="10" y="10" width="100" height="100" rx="22" fill="#18181B" stroke="#EF4444" stroke-width="3"/><text x="60" y="56" text-anchor="middle" fill="#EF4444" font-family="sans-serif" font-weight="bold" font-size="13">' . $label . '</text><text x="60" y="76" text-anchor="middle" fill="#A1A1AA" font-family="sans-serif" font-size="9">TURKY HUB</text></svg>';
    } else {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => false, 'error' => $check['code'], 'message' => $check['message']]);
    }
    exit;
}

if ($badgeId === '') {
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => false, 'error' => 'missing_badge_id']);
    exit;
}

$badgesFile = dirname(__DIR__) . '/badges.json';
$rawData = file_exists($badgesFile) ? json_decode(file_get_contents($badgesFile), true) : ['badges' => []];
$badges = $rawData['badges'] ?? [];

$badge = null;
$idLower = strtolower($badgeId);
foreach ($badges as $b) {
    if ((string)($b['id'] ?? '') === $badgeId || strtolower((string)($b['slug'] ?? '')) === $idLower) {
        $badge = $b;
        break;
    }
}

if (!$badge) {
    http_response_code(404);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => false, 'error' => 'badge_not_found']);
    exit;
}

$actualId = (string)$badge['id'];
if ($format === 'png') {
    $file = basename((string)($badge['file'] ?? 'ff_ui_prime_icon_08.png'));
    $path = dirname(__DIR__, 2) . '/badges/' . $file;
    if (!is_file($path)) {
        http_response_code(404);
        exit;
    }
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = ($ext === 'svg') ? 'image/svg+xml' : (($ext === 'webp') ? 'image/webp' : (($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : 'image/png'));
    header('Content-Type: ' . $mime);
    readfile($path);
    exit;
}

$svgPath = dirname(__DIR__, 2) . '/badges/svg/' . $actualId . '.svg';
if (!is_file($svgPath)) {
    http_response_code(404);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok' => false, 'error' => 'svg_not_found']);
    exit;
}
header('Content-Type: image/svg+xml; charset=UTF-8');
readfile($svgPath);
