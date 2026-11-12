<?php
/**
 * TURKY HUB — Subscription-Gated Interactive HTML Player
 */

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');

require_once dirname(__DIR__, 2) . '/includes/saas_db.php';

$apiKey = saas_extract_api_key();
$badgeId = trim((string)($_GET['id'] ?? '126393684880'));
$size = max(24, min(280, (int)($_GET['size'] ?? 80)));

$check = saas_validate_and_consume_key($apiKey, 'player', $badgeId, true);
if (!$check['valid']) {
    http_response_code((int)($check['http'] ?? 403));
    ?><!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>License Locked</title></head>
<body style="margin:0;background:transparent;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:-apple-system,sans-serif;">
    <div style="background:#18181B;color:#FFFFFF;border:1px solid #EF4444;border-radius:12px;padding:10px 14px;text-align:center;font-size:11px;max-width:220px;">
        <div style="color:#EF4444;font-weight:700;margin-bottom:2px;">License Locked</div>
        <div style="color:#A1A1AA;font-size:10px;"><?= htmlspecialchars($check['message']) ?></div>
    </div>
</body>
</html><?php
    exit;
}

$origin = saas_origin_url();
$svgUrl = $origin . '/api/license/asset.php?id=' . urlencode($badgeId) . '&format=svg&key=' . urlencode($apiKey) . '&sig=' . urlencode(hash_hmac('sha256', $apiKey . '|' . $badgeId, __DIR__ . 'TURKY_SDK_SALT_V1'));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TURKY HUB Licensed Player</title>
    <style>
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; background: transparent !important; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        img { width: <?= $size ?>px; height: <?= $size ?>px; object-fit: contain; display: block; }
    </style>
</head>
<body>
    <img src="<?= htmlspecialchars($svgUrl) ?>" alt="Badge <?= htmlspecialchars($badgeId) ?>">
</body>
</html>
