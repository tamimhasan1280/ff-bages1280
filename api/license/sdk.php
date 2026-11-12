<?php
/**
 * TURKY HUB — Subscription-Gated Dynamic JavaScript SDK
 * Usage: <script src="https://YOUR_HOST/api/license/sdk.php?key=YOUR_API_KEY"></script>
 * Automatically disables/clears <ff-badge> renders if subscription is expired, suspended, or over API limit.
 */

header('Content-Type: application/javascript; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once dirname(__DIR__, 2) . '/includes/saas_db.php';

$apiKey = saas_extract_api_key();
$origin = saas_origin_url();
$check = saas_validate_and_consume_key($apiKey, 'sdk_js', '', true);

if (!$check['valid']) {
    $errCode = json_encode($check['code']);
    $errMsg = json_encode($check['message']);
    $renewUrl = json_encode($origin . '/account.php');
    echo <<<JS
(function(w, d) {
    'use strict';
    var ERR_CODE = {$errCode};
    var ERR_MSG = {$errMsg};
    var RENEW_URL = {$renewUrl};
    try { console.warn('[TURKY HUB SDK] ' + ERR_CODE + ': ' + ERR_MSG); } catch (e) {}

    function renderLocked(el) {
        if (!el) return;
        el.innerHTML = '<span title="' + ERR_MSG.replace(/"/g, '&quot;') + '" style="display:inline-flex;align-items:center;gap:4px;padding:3px 7px;border-radius:8px;background:#18181B;color:#EF4444;border:1px solid rgba(239,68,68,0.45);font:600 10px/1 -apple-system,sans-serif;">Locked</span>';
    }

    function lockAll() {
        var nodes = d.querySelectorAll('ff-badge, [data-ff-badge], [data-ff-badge-id], .ff-badge-auto');
        for (var i = 0; i < nodes.length; i++) renderLocked(nodes[i]);
    }

    if (typeof w.customElements !== 'undefined' && !w.customElements.get('ff-badge')) {
        var LockedEl = function() { return Reflect.construct(HTMLElement, [], LockedEl); };
        LockedEl.prototype = Object.create(HTMLElement.prototype);
        LockedEl.prototype.connectedCallback = function() { renderLocked(this); };
        try { w.customElements.define('ff-badge', LockedEl); } catch (e) {}
    }

    w.FFBadges = {
        version: '3.2.0-locked',
        active: false,
        error: ERR_CODE,
        message: ERR_MSG,
        renewUrl: RENEW_URL,
        render: function() { return ''; },
        mount: function() { lockAll(); },
        list: function() { return []; },
        get: function() { return null; }
    };

    if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', lockAll);
    else lockAll();
})(window, document);
JS;
    exit;
}

// Active Subscription: Load badges metadata and rewrite asset URLs with authenticated & time/quota-verified asset proxy
$badgesFile = dirname(__DIR__) . '/badges.json';
$rawData = file_exists($badgesFile) ? json_decode(file_get_contents($badgesFile), true) : ['badges' => []];
$badges = $rawData['badges'] ?? [];

$catalogMap = [];
$listArr = [];
foreach ($badges as $b) {
    $bid = (string)($b['id'] ?? '');
    if ($bid === '') continue;
    $sig = hash_hmac('sha256', $apiKey . '|' . $bid, __DIR__ . 'TURKY_SDK_SALT_V1');
    $pngUrl = $origin . '/api/license/asset.php?id=' . urlencode($bid) . '&format=png&key=' . urlencode($apiKey) . '&sig=' . urlencode($sig);
    $svgUrl = $origin . '/api/license/asset.php?id=' . urlencode($bid) . '&format=svg&key=' . urlencode($apiKey) . '&sig=' . urlencode($sig);

    $entry = [
        'id' => $bid,
        'slug' => (string)($b['slug'] ?? ''),
        'name' => (string)($b['name'] ?? ''),
        'rarity' => (string)($b['rarity'] ?? 'MYTHIC'),
        'type' => (string)($b['type'] ?? 'image'),
        'prime_level' => (int)($b['prime_level'] ?? 0),
        'default_size' => (int)($b['default_size'] ?? 48),
        'sweep_enabled' => $b['sweep_enabled'] ?? true,
        'sweep_direction' => (string)($b['sweep_direction'] ?? 'diag_bl_tr'),
        'sweep_speed' => (string)($b['sweep_speed'] ?? '2.2s'),
        'star_density' => (int)($b['star_density'] ?? 0),
        'star_theme' => (string)($b['star_theme'] ?? 'gold'),
        'has_gem_glint' => $b['has_gem_glint'] ?? false,
        'glow_enabled' => $b['glow_enabled'] ?? true,
        'glow_color' => (string)($b['glow_color'] ?? 'rgba(255,215,0,0.85)'),
        'pulse_enabled' => $b['pulse_enabled'] ?? true,
        'bone_mode' => (string)($b['bone_mode'] ?? 'inside'),
        'bone_data' => is_array($b['bone_data'] ?? null) ? $b['bone_data'] : [],
        'svg_code' => (string)($b['svg_code'] ?? ''),
        'text_code' => (string)($b['text_code'] ?? ''),
        'png_url' => $pngUrl,
        'svg_url' => $svgUrl,
    ];
    $catalogMap[$bid] = $entry;
    if ($entry['slug'] !== '') {
        $catalogMap[strtolower($entry['slug'])] = $entry;
    }
    $listArr[] = $entry;
}

$catalogJson = json_encode($catalogMap, JSON_UNESCAPED_SLASHES);
$listJson = json_encode($listArr, JSON_UNESCAPED_SLASHES);
$cssUrlJson = json_encode($origin . '/cdn/prime-badge.css');
$planMetaJson = json_encode([
    'plan' => $check['plan_name'],
    'expires_at' => $check['expires_at'],
    'limit' => $check['limit'],
    'used' => $check['used'],
    'remaining' => $check['remaining'],
]);

echo <<<JS
(function(w, d) {
    'use strict';
    var CATALOG = {$catalogJson};
    var LIST = {$listJson};
    var CSS_URL = {$cssUrlJson};
    var PLAN = {$planMetaJson};

    function ensureCss() {
        if (!d.getElementById('turky-prime-badge-css') && d.head) {
            var link = d.createElement('link');
            link.id = 'turky-prime-badge-css';
            link.rel = 'stylesheet';
            link.href = CSS_URL;
            d.head.appendChild(link);
        }
    }

    function getBadge(idOrSlug) {
        if (!idOrSlug) return null;
        var s = String(idOrSlug).trim();
        return CATALOG[s] || CATALOG[s.toLowerCase()] || null;
    }

    function renderHtml(idOrSlug, opts) {
        opts = opts || {};
        var b = getBadge(idOrSlug);
        if (!b) return '';
        var sz = parseInt(opts.size || b.default_size || 48, 10);
        if (!isNaN(sz) && sz < 16) sz = 16;
        var glow = b.glow_enabled !== false ? 'filter:drop-shadow(0 0 8px ' + (b.glow_color || 'rgba(255,215,0,0.8)') + ');' : '';
        var pulseCls = b.pulse_enabled !== false ? ' ff-pulse-on' : ' ff-pulse-off';

        if (b.type === 'svg_code' && b.svg_code) {
            return '<span class="ff-badge ff-prime-badge-wrap' + pulseCls + '" style="display:inline-flex;align-items:center;justify-content:center;width:' + sz + 'px;height:' + sz + 'px;position:relative;' + glow + '"><span class="ff-badge-img" style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">' + b.svg_code + '</span></span>';
        }
        if (b.type === 'text_code' && b.text_code) {
            return '<span class="ff-badge ff-prime-badge-wrap' + pulseCls + '" style="display:inline-flex;align-items:center;justify-content:center;position:relative;' + glow + '">' + b.text_code + '</span>';
        }

        var colorMap = { cyan: '#00f0ff', ruby: '#ef4444', purple: '#c084fc', white: '#ffffff', emerald: '#10b981', amber: '#f59e0b', rainbow: '#ff007f', gold: '#FFFCE0' };
        var sc = colorMap[b.star_theme] || '#FFFCE0';
        var stars = '';
        var density = Math.min(Math.max(parseInt(b.star_density || 0, 10), 0), 50);
        if (density > 0) {
            stars = '<span class="ff-prime-stars-layer star-theme-' + (b.star_theme || 'gold') + '">';
            for (var i = 0; i < density; i++) {
                stars += '<svg class="ff-pinpoint-star star-pos-' + (i + 1) + '" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="' + sc + '"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>';
            }
            stars += '</span>';
        }
        var shine = b.sweep_enabled !== false ? '<span class="ff-badge-shine sweep-dir-' + (b.sweep_direction || 'diag_bl_tr') + '" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url(' + b.png_url + ');mask-image:url(' + b.png_url + ');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;"><span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;animation-duration:' + (b.sweep_speed || '2.2s') + ';mix-blend-mode:screen;display:block;"></span></span>' : '';
        var glint = b.has_gem_glint ? '<span class="ff-gem-glint"></span>' : '';
        var wrapCls = 'ff-badge ff-prime-badge-wrap' + (b.prime_level ? ' ff-prime-lvl-' + b.prime_level : '') + pulseCls;
        return '<span class="' + wrapCls + '" style="display:inline-flex;width:' + sz + 'px;height:' + sz + 'px;position:relative;vertical-align:middle;' + glow + '">' + stars + '<img src="' + b.png_url + '" class="ff-badge-img" alt="' + b.name.replace(/"/g, '&quot;') + '">' + shine + glint + '</span>';
    }

    function mountNode(el) {
        if (!el) return;
        var candidates = [
            el.getAttribute('badge'),
            el.getAttribute('data-ff-badge'),
            el.getAttribute('data-ff-badge-id'),
            el.getAttribute('id')
        ];
        var id = '';
        for (var i = 0; i < candidates.length; i++) {
            if (candidates[i] && getBadge(candidates[i])) {
                id = candidates[i];
                break;
            }
        }
        var size = el.getAttribute('size') || el.getAttribute('data-size') || '';
        var html = renderHtml(id, { size: size });
        if (html) el.innerHTML = html;
    }

    function mountAll() {
        ensureCss();
        var nodes = d.querySelectorAll('ff-badge, [data-ff-badge], [data-ff-badge-id], .ff-badge-auto');
        for (var i = 0; i < nodes.length; i++) mountNode(nodes[i]);
    }

    if (typeof w.customElements !== 'undefined' && !w.customElements.get('ff-badge')) {
        var FFBadgeEl = function() { return Reflect.construct(HTMLElement, [], FFBadgeEl); };
        FFBadgeEl.prototype = Object.create(HTMLElement.prototype);
        FFBadgeEl.prototype.connectedCallback = function() { ensureCss(); mountNode(this); };
        try { w.customElements.define('ff-badge', FFBadgeEl); } catch (e) {}
    }

    w.FFBadges = {
        version: '3.2.0-licensed',
        active: true,
        plan: PLAN,
        get: getBadge,
        list: function() { return LIST.slice(); },
        render: renderHtml,
        mount: mountAll
    };

    ensureCss();
    if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', mountAll);
    else mountAll();
})(window, document);
JS;
