<?php
/**
 * Master Generator for Turky Hub: index.php, index.html, admin.php, and cdn/ff-badges.js
 * Enhanced with 1:1 In-Game Free Fire Prime Level VFX
 */

$repo = 'tamimhasan1280/ff-bages1280';
$badges_data = json_decode(file_get_contents(__DIR__ . '/api/badges.json'), true);
$badges = $badges_data['badges'] ?? [];

// 1. UPDATE cdn/ff-badges.js
$db_entries = [];
foreach ($badges as $b) {
    $entry = [
        'id' => $b['id'],
        'slug' => $b['slug'],
        'icon_id' => $b['icon_id'],
        'name' => $b['name'],
        'title' => $b['title'],
        'category' => $b['category'],
        'category_slug' => $b['category_slug'],
        'file' => $b['file'] ?? '',
        'glowColor' => $b['glow_color'],
        'hexGlow' => $b['hex_glow'],
        'priority' => $b['priority'],
        'defaultSize' => $b['default_size'],
        'rarity' => $b['rarity'],
        'patch' => $b['patch'] ?? 'OB55',
        'type' => $b['type'],
        'prime_level' => $b['prime_level'] ?? null,
        'sweep_enabled' => $b['sweep_enabled'] ?? null,
        'sweep_direction' => $b['sweep_direction'] ?? null,
        'sweep_speed' => $b['sweep_speed'] ?? null,
        'star_type' => $b['star_type'] ?? null,
        'star_density' => $b['star_density'] ?? null,
        'star_theme' => $b['star_theme'] ?? null,
        'has_gem_glint' => $b['has_gem_glint'] ?? null,
        'description' => $b['description'],
        'fn_call' => $b['fn_call'] ?? ''
    ];
    if (isset($b['svg_code'])) $entry['svgCode'] = $b['svg_code'];
    if (isset($b['text_code'])) $entry['textCode'] = $b['text_code'];

    $db_entries[$b['slug']] = $entry;
    $db_entries[$b['id']] = $entry;
}

$db_json = json_encode($db_entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

$js_code = <<<JSEOF
/**
 * Free Fire Badges & V-Badges Master Client SDK & Web Component v3.2.0
 * Ultra-fast CDN delivery, interactive Web Component, Authentic Prime System (Lvl 1-8 VFX).
 * (c) Free Fire Badges CDN Project - MIT License
 */
(function(window, document) {
    'use strict';

    const DEFAULT_REPO = '{$repo}';
    const CDN_TEMPLATES = {
        jsdelivr: 'https://cdn.jsdelivr.net/gh/{REPO}@main/badges/',
        github_raw: 'https://raw.githubusercontent.com/{REPO}/main/badges/',
        statically: 'https://cdn.statically.io/gh/{REPO}/main/badges/'
    };

    let activeRepo = DEFAULT_REPO;
    let activeCDN = 'jsdelivr';
    let customBaseUrl = '';

    const BADGES_DATABASE = {$db_json};

    const FFBadges = {
        version: '3.2.0',

        setRepo: function(repo) {
            activeRepo = repo;
            return this;
        },

        getRepo: function() {
            return activeRepo;
        },

        setCDN: function(providerOrUrl) {
            if (CDN_TEMPLATES[providerOrUrl]) {
                activeCDN = providerOrUrl;
                customBaseUrl = '';
            } else {
                customBaseUrl = providerOrUrl.replace(/\/?$/, '/');
            }
            return this;
        },

        getBaseUrl: function() {
            if (customBaseUrl) return customBaseUrl;
            return CDN_TEMPLATES[activeCDN].replace('{REPO}', activeRepo);
        },

        getImageUrl: function(file) {
            if (!file) return '';
            if (/^(https?:|\/\/|data:)/i.test(file)) return file;
            return this.getBaseUrl() + file;
        },

        get: function(keyOrId) {
            if (!keyOrId) return null;
            if (BADGES_DATABASE[keyOrId]) return BADGES_DATABASE[keyOrId];
            const lower = String(keyOrId).toLowerCase().replace(/[^a-z0-9]/g, '');
            for (const k in BADGES_DATABASE) {
                const b = BADGES_DATABASE[k];
                if (b.id === keyOrId ||
                    b.slug === keyOrId ||
                    b.icon_id === keyOrId ||
                    b.id.replace(/[^a-z0-9]/g, '') === lower ||
                    b.name.toLowerCase().replace(/[^a-z0-9]/g, '') === lower) {
                    return b;
                }
            }
            return null;
        },

        list: function() {
            const seen = new Set();
            const result = [];
            for (const k in BADGES_DATABASE) {
                const b = BADGES_DATABASE[k];
                if (!seen.has(b.id)) {
                    seen.add(b.id);
                    result.push(b);
                }
            }
            return result;
        },

        render: function(badgeKey, options) {
            options = options || {};
            const b = this.get(badgeKey) || BADGES_DATABASE['120000000080'] || BADGES_DATABASE['official-v'];
            const size = options.size ? (typeof options.size === 'number' ? options.size + 'px' : options.size) : (b.defaultSize + 'px');
            const position = options.position || 'inline';
            const speed = options.speed || '2.6s';

            let posStyle = 'display:inline-flex;position:relative;vertical-align:middle;';
            if (position === 'top-right') {
                posStyle = 'position:absolute;top:-4px;right:-4px;z-index:25;';
            } else if (position === 'top-left') {
                posStyle = 'position:absolute;top:-4px;left:-4px;z-index:25;';
            } else if (position === 'bottom-right') {
                posStyle = 'position:absolute;bottom:-4px;right:-4px;z-index:25;';
            } else if (position === 'bottom-left') {
                posStyle = 'position:absolute;bottom:-4px;left:-4px;z-index:25;';
            }

            if (b.type === 'svg_code') {
                return '<div class="ff-badge" style="' + posStyle + 'width:' + size + ';height:' + size + ';user-select:none;" title="' + b.title + '">' + b.svgCode + '</div>';
            }

            if (b.type === 'text_code') {
                return '<span class="ff-badge" style="' + posStyle + 'user-select:none;" title="' + b.title + '">' + b.textCode + '</span>';
            }

            // Universal Image Asset Renderer (Supports Prime Badges, V-Badges, KOL, Esports, etc.)
            const imgUrl = this.getImageUrl(b.file);
            const lvl = b.prime_level || 0;
            const isPrime = (b.type === 'prime_image' || lvl > 0);

            // Independent VFX Controls (Universal for ALL badges, can be turned ON/OFF anytime)
            const hasShine = (b.sweep_enabled === true || b.sweep_enabled === 'true' || b.sweep_enabled === 1);
            const hasGlint = (b.has_gem_glint === true || b.has_gem_glint === 'true' || b.has_gem_glint === 1);
            let density = (b.star_density !== undefined) ? parseInt(b.star_density) : (lvl === 8 ? 16 : (lvl === 7 ? 12 : (lvl === 6 ? 8 : (lvl === 5 ? 6 : (lvl === 4 ? 4 : 0)))));
            const starCount = Math.min(Math.max(density, 0), 50);

            // Is the badge completely static?
            const isFullyStatic = (!hasShine && starCount === 0 && !hasGlint);

            const glowColor = b.glowColor || b.glow_color || 'rgba(255, 215, 0, 0.85)';
            const filterStyle = (b.glow_enabled !== false) ? ('filter:drop-shadow(0 0 6px ' + glowColor + ') drop-shadow(0 2px 4px rgba(0,0,0,0.85));') : '';

            // Radially scattered stars (360 degrees around badge)
            let starsHtml = '';
            if (starCount > 0) {
                const starTheme = b.star_theme || (lvl === 4 ? 'cyan' : 'gold');
                const colorMap = { cyan: '#00f0ff', ruby: '#ef4444', purple: '#c084fc', white: '#ffffff', emerald: '#10b981', amber: '#f59e0b', rainbow: '#ff007f', gold: '#FFFCE0' };
                const starColor = colorMap[starTheme] || '#FFFCE0';
                const starThemeClass = 'star-theme-' + starTheme;

                const starList = Array.from({length: starCount}, (_, i) => 'star-pos-' + (i + 1));
                const starDots = starList.map(pos => '<svg class="ff-pinpoint-star ' + pos + '" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="' + starColor + '"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>').join('');
                starsHtml = '  <div class="ff-prime-stars-layer ' + starThemeClass + '">' + starDots + '</div>\n';
            }

            // Directional Metallic Sheen Sweep
            let shineHtml = '';
            if (hasShine) {
                const sweepDir = b.sweep_direction || 'diag_bl_tr';
                const sweepClass = 'sweep-dir-' + sweepDir;
                const sweepSpd = b.sweep_speed || speed || '2.2s';
                shineHtml = '  <span class="ff-badge-shine ' + sweepClass + '" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url(\'' + imgUrl + '\');mask-image:url(\'' + imgUrl + '\');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">\n' +
                            '    <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;animation-duration:' + sweepSpd + ';mix-blend-mode:screen;display:block;"></span>\n' +
                            '  </span>\n';
            }

            const glintHtml = hasGlint ? '  <div class="ff-gem-glint"></div>\n' : '';
            const wrapClass = isPrime ? ('ff-prime-badge-wrap ff-prime-lvl-' + lvl) : 'ff-prime-badge-wrap';
            const animClass = isFullyStatic ? ' ff-anim-off' : '';
            const imgStyle = isFullyStatic ? 'style="animation:none !important;"' : '';

            return '<div class="' + wrapClass + animClass + '" style="' + posStyle + 'width:' + size + ';height:' + size + ';' + filterStyle + 'position:relative;background:transparent !important;user-select:none;" title="' + b.title + '">\n' +
                   starsHtml +
                   '  <img src="' + imgUrl + '" class="ff-badge-img" ' + imgStyle + ' alt="' + b.name + '">\n' +
                   shineHtml +
                   glintHtml +
                   '</div>';
        },

        attachTo: function(container, badgeKey, options) {
            const el = (typeof container === 'string') ? document.querySelector(container) : container;
            if (!el) return null;
            if (getComputedStyle(el).position === 'static') {
                el.style.position = 'relative';
            }
            const wrapper = document.createElement('div');
            wrapper.innerHTML = this.render(badgeKey, options);
            const badgeEl = wrapper.firstElementChild;
            el.appendChild(badgeEl);
            return badgeEl;
        }
    };

    if ('customElements' in window && !customElements.get('ff-badge')) {
        class FFBadgeElement extends HTMLElement {
            static get observedAttributes() {
                return ['name', 'id', 'size', 'position', 'speed'];
            }
            connectedCallback() { this.renderBadge(); }
            attributeChangedCallback() { this.renderBadge(); }
            renderBadge() {
                const key = this.getAttribute('id') || this.getAttribute('name') || '120000000080';
                const size = this.getAttribute('size');
                const position = this.getAttribute('position') || 'inline';
                const speed = this.getAttribute('speed');
                this.innerHTML = FFBadges.render(key, { size, position, speed });
            }
        }
        customElements.define('ff-badge', FFBadgeElement);
    }

    window.FFBadges = FFBadges;

})(typeof window !== 'undefined' ? window : this, typeof document !== 'undefined' ? document : {});
JSEOF;

file_put_contents(__DIR__ . '/cdn/ff-badges.js', $js_code);

// 2. BUILD index.php with Prime VFX, Canvas Particles and In-game Showcase
$php_content = <<<'PHPEOF'
<?php
/**
 * TURKY HUB - Free Fire Items & Badges EXPLORER (OB55 Engine)
 * GitHub Repository: https://github.com/tamimhasan1280/ff-bages1280
 * Global CDN Base: https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/badges/
 */

$repo_name = 'tamimhasan1280/ff-bages1280';
$cdn_base = 'https://cdn.jsdelivr.net/gh/' . $repo_name . '@main/badges/';

$master_badges = json_decode(file_get_contents(__DIR__ . '/api/badges.json'), true)['badges'] ?? [];
$selected_item_id = $_GET['item'] ?? $_GET['id'] ?? $_GET['badge'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TURKY HUB - FF Items EXPLORER | OB55 ACTIVE</title>
    <link rel="icon" type="image/png" href="badges/FF_UI_Badge_KOL01.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Orbitron, Rajdhani, Fira Code, Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;800;900&family=Rajdhani:wght@600;700&family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Authentic Free Fire Prime Badges CSS (100% Transparent, Ascending Beam, Gem Glint) -->
    <link rel="stylesheet" href="cdn/prime-badge.css">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #0b0e1b;
            color: #f1f5f9;
        }

        .font-orbitron { font-family: 'Orbitron', sans-serif; }
        .font-rajdhani { font-family: 'Rajdhani', sans-serif; }
        .font-mono-code { font-family: 'Fira Code', monospace; }

        @keyframes ff_shape_sweep {
            0% { transform: translate(-100%, -100%) rotate(45deg); }
            100% { transform: translate(100%, 100%) rotate(45deg); }
        }

        @keyframes pulse_gold {
            0%, 100% { filter: drop-shadow(0 0 2px gold) drop-shadow(0 2px 4px rgba(0,0,0,0.8)); }
            50% { filter: drop-shadow(0 0 10px gold) drop-shadow(0 0 16px rgba(255,215,0,0.6)); }
        }

        @keyframes fire_text {
            from { text-shadow: 0 0 2px #ff8c00, 0 0 5px #ff4500; }
            to { text-shadow: 0 0 6px #ff4500, 0 0 14px #ff8c00, 0 0 20px #ff2200; }
        }

        @keyframes glow_legend {
            0%, 100% { filter: drop-shadow(0 0 3px #8e2de2) drop-shadow(0 1px 3px rgba(0,0,0,0.9)); }
            50% { filter: drop-shadow(0 0 12px #a855f7) drop-shadow(0 0 20px rgba(168,85,247,0.7)); }
        }

        .ff-shape-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent !important;
            border: none !important;
            user-select: none;
            -webkit-user-select: none;
            vertical-align: middle;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .item-card {
            background: #0e1222;
            border: 1px solid #1f2544;
            border-radius: 18px;
            transition: all 0.25s ease-out;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .item-card:hover {
            border-color: rgba(168, 85, 247, 0.5);
            transform: translateY(-4px);
            box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.7), 0 0 20px -4px rgba(168, 85, 247, 0.25);
        }

        .item-preview-box {
            background: radial-gradient(circle at center, rgba(168, 85, 247, 0.12) 0%, rgba(10, 12, 24, 0.95) 75%);
            border-radius: 12px;
            position: relative;
        }

        .modal-box {
            background: #111428;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.9), 0 0 40px rgba(168, 85, 247, 0.15);
        }

        .custom-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 4px;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col selection:bg-rose-500 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="border-b border-[#1c2242] bg-[#0c0f20]/95 sticky top-0 z-40 backdrop-blur-md">
        <div class="max-w-[1720px] mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
            
            <!-- Left Brand -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-amber-500 via-rose-500 to-red-600 flex items-center justify-center shadow-lg shadow-rose-500/20">
                    <i class="fas fa-fire-flame-curved text-white text-base"></i>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-extrabold font-orbitron text-white text-base tracking-wider">TURKY HUB</span>
                    <span class="text-xs text-gray-400 font-medium hidden sm:inline">FF Items</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#122822] text-emerald-400 border border-emerald-500/30 font-bold uppercase tracking-wider">
                        OB55 ACTIVE
                    </span>
                </div>
            </div>

            <!-- Center Navigation Tabs -->
            <nav class="hidden md:flex items-center gap-1.5 bg-[#080a15] p-1 rounded-2xl border border-white/5">
                <button type="button" onclick="filterCategory('all')" class="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-rose-500 to-pink-600 text-white shadow-md shadow-rose-500/20 flex items-center gap-2">
                    <i class="fas fa-book-bookmark text-xs"></i>
                    <span>Items Library</span>
                </button>
                <button type="button" onclick="filterCategory('prime')" class="px-4 py-2 rounded-xl text-xs font-semibold text-yellow-400 hover:text-white hover:bg-white/5 transition flex items-center gap-2">
                    <i class="fas fa-shield-halved text-xs"></i>
                    <span>Prime Level (1-8)</span>
                </button>
                <a href="#player-inspector" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-400 hover:text-white hover:bg-white/5 transition flex items-center gap-2">
                    <i class="fas fa-user-astronaut text-xs"></i>
                    <span>Player Inspector</span>
                </a>
                <a href="admin.php" class="px-4 py-2 rounded-xl text-xs font-semibold text-amber-400 hover:text-amber-300 hover:bg-amber-400/10 border border-amber-400/20 transition flex items-center gap-2">
                    <i class="fas fa-crown text-xs"></i>
                    <span>Admin Panel</span>
                </a>
            </nav>

            <!-- Right Socials & Profile -->
            <div class="flex items-center gap-3">
                <div class="hidden lg:flex items-center gap-2 text-gray-400">
                    <a href="#" class="w-8 h-8 rounded-xl bg-white/5 hover:bg-white/10 hover:text-white flex items-center justify-center transition border border-white/5">
                        <i class="fab fa-discord text-xs"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-xl bg-white/5 hover:bg-white/10 hover:text-white flex items-center justify-center transition border border-white/5">
                        <i class="fab fa-youtube text-xs"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-xl bg-white/5 hover:bg-white/10 hover:text-white flex items-center justify-center transition border border-white/5">
                        <i class="fab fa-telegram text-xs"></i>
                    </a>
                </div>

                <!-- Admin Profile Pill -->
                <a href="admin.php" class="flex items-center gap-2 bg-[#12162b] hover:bg-[#1a203f] px-3 py-1.5 rounded-2xl border border-white/10 transition">
                    <div class="w-7 h-7 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-500 flex items-center justify-center font-bold text-white text-xs">
                        A
                    </div>
                    <div class="hidden sm:block text-left leading-tight">
                        <div class="text-xs font-bold text-white">Admin</div>
                        <div class="text-[9px] text-gray-400 font-medium">Studio Access</div>
                    </div>
                </a>
            </div>

        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-[1720px] mx-auto px-4 sm:px-6 py-6 space-y-6 w-full">

        <!-- Banner / Explorer Header -->
        <section class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-[#0d1022] p-5 sm:p-6 rounded-2xl border border-[#1d2342] relative overflow-hidden">
            <div class="flex items-center gap-3.5 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500/20 to-rose-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400 text-2xl shadow-inner">
                    <i class="fas fa-cubes"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black font-orbitron tracking-wide text-white flex items-center gap-2">
                        TURKY HUB - FF Items EXPLORER
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-400 font-rajdhani tracking-wider mt-0.5">
                        <span class="text-white font-bold" id="itemCountDisplay"><?= count($master_badges) ?> items available</span> | Infinite Auto-Scroll Active
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 relative z-10">
                <span class="px-3.5 py-1.5 rounded-xl bg-[#0e271f] text-emerald-400 border border-emerald-500/30 text-xs font-semibold flex items-center gap-2 font-mono">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    OB55 Engine Active
                </span>
                <a href="admin.php" class="px-3.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-yellow-300 border border-yellow-500/30 text-xs font-bold flex items-center gap-2 transition">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Badge in Admin</span>
                </a>
            </div>
        </section>

        <!-- Search & Filter Controls -->
        <section class="bg-[#0e1224] p-4 sm:p-5 rounded-2xl border border-[#1e2444] space-y-4">
            
            <!-- Full Width Search Input -->
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-500 text-sm"></i>
                <input type="text" id="searchInput" oninput="applyFilters()" placeholder="Search by Item Name, ID, or Keywords (e.g. prime, level 8, 120000010880)..." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl pl-11 pr-4 py-3 text-xs sm:text-sm text-white placeholder-gray-500 focus:outline-none focus:border-rose-500 transition shadow-inner">
            </div>

            <!-- Filters Row: Categories, Patches, Rarities, Apply Button -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
                
                <!-- Category Select -->
                <div class="relative">
                    <select id="catSelect" onchange="applyFilters()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-3.5 py-2.5 text-xs text-gray-200 focus:outline-none focus:border-rose-500 appearance-none cursor-pointer">
                        <option value="all">All Categories</option>
                        <option value="prime">Prime System (Free Fire Prime Lvl 1-8)</option>
                        <option value="official">Official Partner (KOL)</option>
                        <option value="vip">Crystal VIP Partner</option>
                        <option value="esports">Competitive & Esports</option>
                        <option value="staff">Administration & Authority</option>
                        <option value="creator">Creators & Architects</option>
                        <option value="hud">In-game HUD</option>
                        <option value="special">Special Editions</option>
                        <option value="tokens">Exchange Tokens</option>
                        <option value="misc">Misc Items</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3.5 top-3.5 text-gray-500 text-[10px] pointer-events-none"></i>
                </div>

                <!-- Patches / Versions Select -->
                <div class="relative">
                    <select id="patchSelect" onchange="applyFilters()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-3.5 py-2.5 text-xs text-gray-200 focus:outline-none focus:border-rose-500 appearance-none cursor-pointer">
                        <option value="all">All Patches / Versions</option>
                        <option value="ob55">OB55 (Current)</option>
                        <option value="ob54">OB54</option>
                        <option value="legacy">Legacy Editions</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3.5 top-3.5 text-gray-500 text-[10px] pointer-events-none"></i>
                </div>

                <!-- Rarities Select -->
                <div class="relative">
                    <select id="raritySelect" onchange="applyFilters()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-3.5 py-2.5 text-xs text-gray-200 focus:outline-none focus:border-rose-500 appearance-none cursor-pointer">
                        <option value="all">All Rarities</option>
                        <option value="MYTHIC">MYTHIC</option>
                        <option value="LEGENDARY">LEGENDARY</option>
                        <option value="EPIC">EPIC</option>
                        <option value="PURPLE">PURPLE</option>
                        <option value="RARE">RARE</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3.5 top-3.5 text-gray-500 text-[10px] pointer-events-none"></i>
                </div>

                <!-- Apply Filters Button -->
                <div>
                    <button type="button" onclick="applyFilters()" class="w-full bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white font-bold text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-rose-500/20 transition">
                        <i class="fas fa-sliders text-xs"></i>
                        <span>Apply Filters</span>
                    </button>
                </div>

            </div>

        </section>

        <!-- Grid Cards Section -->
        <section id="itemsGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-7 2xl:grid-cols-8 gap-3 sm:gap-4">
        </section>

    </main>

    <!-- Modal Dialog (Matching Image 4) -->
    <div id="itemModal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md hidden items-center justify-center p-4 overflow-y-auto">
        <div class="modal-box w-full max-w-[460px] p-6 sm:p-7 relative my-auto animate-in fade-in zoom-in-95 duration-200">
            
            <!-- Close Button (X) -->
            <button type="button" onclick="closeItemModal()" class="absolute top-5 right-5 w-8 h-8 rounded-full bg-[#1b1f3b] text-gray-400 hover:text-white flex items-center justify-center text-sm transition">
                <i class="fas fa-xmark"></i>
            </button>

            <!-- Preview Stage (100% Transparent, No Background) -->
            <div class="w-48 h-48 mx-auto rounded-2xl bg-[#090b18] border border-white/5 relative flex items-center justify-center overflow-hidden shadow-inner">
                <div id="modalAura" class="absolute inset-0 bg-gradient-to-tr from-purple-600/15 via-transparent to-pink-500/15 pointer-events-none"></div>
                <div id="modalItemPreview" class="relative z-10 flex items-center justify-center w-full h-full p-2">
                    <!-- Image or Animation injected here -->
                </div>
            </div>

            <!-- Item Name -->
            <h2 id="modalItemName" class="text-xl font-black text-white text-center mt-5 mb-4 tracking-wide font-orbitron">
                <!-- Name -->
            </h2>

            <!-- Metadata List Rows -->
            <div class="space-y-2 text-xs">
                
                <!-- Item ID Row with Copy Button -->
                <div class="bg-[#090b18] border border-white/5 rounded-xl px-3.5 py-2.5 flex items-center justify-between">
                    <span class="text-[#f97316] font-bold text-xs tracking-wide">Item ID:</span>
                    <div class="flex items-center gap-2">
                        <span id="modalItemId" class="text-emerald-400 font-mono font-bold text-xs tracking-wider">120000010880</span>
                        <button type="button" onclick="copyModalId()" class="bg-[#1b1f3b] hover:bg-[#282f56] text-gray-200 hover:text-white px-2.5 py-1 rounded-lg text-[11px] font-semibold flex items-center gap-1.5 transition">
                            <i class="fas fa-copy text-[10px]"></i> <span>Copy</span>
                        </button>
                    </div>
                </div>

                <!-- Icon ID Row -->
                <div class="bg-[#090b18] border border-white/5 rounded-xl px-3.5 py-2.5 flex items-center justify-between">
                    <span class="text-[#f97316] font-bold text-xs tracking-wide">Icon ID:</span>
                    <span id="modalIconId" class="text-emerald-400 font-mono text-xs">Icon_FF_Prime_Level_08</span>
                </div>

                <!-- Rarity Row -->
                <div class="flex items-center justify-between px-2 py-1">
                    <span class="text-gray-400 font-medium">Rarity:</span>
                    <span id="modalRarity" class="text-white font-extrabold uppercase tracking-wider">MYTHIC</span>
                </div>

                <!-- Category Row -->
                <div class="flex items-center justify-between px-2 py-1">
                    <span class="text-gray-400 font-medium">Category:</span>
                    <span id="modalCategory" class="text-white font-medium">Prime System</span>
                </div>

                <!-- Patch / Tag Row -->
                <div class="flex items-center justify-between px-2 py-1">
                    <span class="text-gray-400 font-medium">Patch / Tag:</span>
                    <span id="modalPatch" class="text-white font-medium">OB55</span>
                </div>

                <!-- Description Block -->
                <div class="pt-1">
                    <span class="text-gray-400 font-medium block mb-1 px-2">Description:</span>
                    <div id="modalDescription" class="bg-[#090b18] border border-white/5 rounded-xl p-3 text-gray-300 italic text-xs leading-relaxed">
                        <!-- Description -->
                    </div>
                </div>

                <!-- Developer Logic accordion -->
                <div class="pt-1">
                    <button type="button" onclick="toggleDevInfo()" class="w-full text-left text-[11px] font-mono text-amber-400/90 hover:text-amber-300 flex items-center justify-between px-2 py-1">
                        <span><i class="fas fa-code mr-1"></i> Developer Integration & Logic</span>
                        <i id="devInfoIcon" class="fas fa-chevron-down text-[10px]"></i>
                    </button>
                    <div id="devInfoBlock" class="hidden space-y-2 mt-1.5 bg-[#090b18] p-3 rounded-xl border border-white/5">
                        <div class="space-y-1">
                            <span class="text-[10px] text-gray-400 uppercase font-mono font-bold">PHP Logic Call:</span>
                            <div class="flex items-center gap-1.5">
                                <code id="modalPhpCall" class="text-[10px] font-mono text-amber-300 bg-black/40 p-1.5 rounded flex-1 overflow-x-auto whitespace-nowrap custom-scroll"></code>
                                <button type="button" onclick="copyText(document.getElementById('modalPhpCall').textContent, 'PHP Call copied!')" class="px-2 py-1 bg-amber-500/20 text-amber-300 rounded text-[10px] shrink-0 hover:bg-amber-500/30">Copy</button>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] text-gray-400 uppercase font-mono font-bold">Global CDN Image URL:</span>
                            <div class="flex items-center gap-1.5">
                                <code id="modalCdnUrl" class="text-[10px] font-mono text-cyan-300 bg-black/40 p-1.5 rounded flex-1 overflow-x-auto whitespace-nowrap custom-scroll"></code>
                                <button type="button" onclick="copyText(document.getElementById('modalCdnUrl').textContent, 'CDN URL copied!')" class="px-2 py-1 bg-cyan-500/20 text-cyan-300 rounded text-[10px] shrink-0 hover:bg-cyan-500/30">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Action Buttons: STRICTLY NO DOWNLOAD BUTTON! Only Share Link and Close -->
            <div class="flex items-center gap-3 mt-6 pt-2">
                <button type="button" id="shareLinkBtn" onclick="shareCurrentItem()" class="flex-1 bg-gradient-to-r from-[#ea580c] to-[#f97316] hover:from-[#c2410c] hover:to-[#ea580c] text-white font-bold py-3 px-4 rounded-2xl flex items-center justify-center gap-2 shadow-lg shadow-orange-500/25 transition">
                    <i class="fas fa-share-nodes text-sm"></i>
                    <span>Share Link</span>
                </button>

                <button type="button" onclick="closeItemModal()" class="bg-[#1e2340] hover:bg-[#282f56] text-gray-300 font-bold py-3 px-6 rounded-2xl transition">
                    Close
                </button>
            </div>

        </div>
    </div>

    <!-- Toast Notification -->
    <div id="appToast" class="fixed bottom-6 right-6 bg-yellow-400 text-black font-extrabold px-4 py-2.5 rounded-2xl shadow-2xl flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 z-50 pointer-events-none">
        <i class="fas fa-check-circle text-base"></i>
        <span id="appToastMsg">Copied!</span>
    </div>

    <!-- Master Items Data & Engine Script -->
    <script>
        const REPO_NAME = "tamimhasan1280/ff-bages1280";
        const CDN_BASE = "https://cdn.jsdelivr.net/gh/" + REPO_NAME + "@main/badges/";
        
        const itemsData = <?php echo json_encode($master_badges, JSON_UNESCAPED_SLASHES); ?>;
        let currentItem = null;

        function filterCategory(cat) {
            document.getElementById('catSelect').value = cat;
            applyFilters();
        }

        // Render card HTML
        function renderCards(list) {
            const container = document.getElementById('itemsGrid');
            if (!list || list.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full py-16 text-center text-gray-500 space-y-3">
                        <i class="fas fa-box-open text-4xl text-gray-600"></i>
                        <p class="text-sm">No items found matching your filters.</p>
                    </div>`;
                return;
            }

            container.innerHTML = list.map(item => {
                const isPrime = item.type === 'prime_image' || !!item.prime_level;
                const fileUrl = item.cdn_urls?.jsdelivr || (item.file ? CDN_BASE + item.file : '');
                const localUrl = item.file ? 'badges/' + item.file : '';
                
                let previewHtml = '';
                if (isPrime) {
                    const lvl = item.prime_level || 1;
                    const hasGlint = (item.has_gem_glint !== undefined) ? item.has_gem_glint : (lvl >= 7);
                    const hasShine = (item.sweep_enabled !== undefined) ? item.sweep_enabled : (lvl >= 4);
                    const sweepDir = item.sweep_direction || 'diag_bl_tr';
                    const sweepClass = 'sweep-dir-' + sweepDir;
                    const sweepSpd = item.sweep_speed || '2.6s';

                    let density = (item.star_density !== undefined) ? parseInt(item.star_density) : (lvl === 8 ? 16 : (lvl === 7 ? 12 : (lvl === 6 ? 8 : (lvl === 5 ? 6 : (lvl === 4 ? 4 : 0)))));
                    const starCount = Math.min(Math.max(density, 0), 50);
                    const starTheme = item.star_theme || (lvl === 4 ? 'cyan' : 'gold');
                    const colorMap = { cyan: '#00f0ff', ruby: '#ef4444', purple: '#c084fc', white: '#ffffff', emerald: '#10b981', amber: '#f59e0b', rainbow: '#ff007f', gold: '#FFFCE0' };
                    const starColor = colorMap[starTheme] || '#FFFCE0';
                    const starThemeClass = 'star-theme-' + starTheme;

                    const starList = Array.from({length: starCount}, (_, i) => 'star-pos-' + (i + 1));
                    const starsHtml = (starCount > 0) ? `<div class="ff-prime-stars-layer ${starThemeClass}">${starList.map(p => `<svg class="ff-pinpoint-star ${p}" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="${starColor}"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>`).join('')}</div>` : '';

                    const shineHtml = hasShine ? `
                        <span class="ff-badge-shine ${sweepClass}" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('${fileUrl}');mask-image:url('${fileUrl}');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                            <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;animation-duration:${sweepSpd};mix-blend-mode:screen;display:block;"></span>
                        </span>
                    ` : '';

                    previewHtml = `
                        <div class="ff-prime-badge-wrap ff-prime-lvl-${lvl}" style="width:62px;height:62px;">
                            ${starsHtml}
                            <img src="${localUrl}" onerror="this.src='${fileUrl}'" class="ff-badge-img" alt="${item.name}">
                            ${shineHtml}
                            ${hasGlint ? '<div class="ff-gem-glint"></div>' : ''}
                        </div>
                    `;
                } else if (item.type === 'image' && item.file) {
                    const hasShine = (item.sweep_enabled === true || item.sweep_enabled === 'true' || item.sweep_enabled === 1);
                    const sweepDir = item.sweep_direction || 'diag_bl_tr';
                    const sweepClass = 'sweep-dir-' + sweepDir;
                    const sweepSpd = item.sweep_speed || '2.2s';
                    const starCount = Math.min(Math.max(parseInt(item.star_density) || 0, 0), 50);
                    const starTheme = item.star_theme || 'gold';
                    const colorMap = { cyan: '#00f0ff', ruby: '#ef4444', purple: '#c084fc', white: '#ffffff', emerald: '#10b981', amber: '#f59e0b', rainbow: '#ff007f', gold: '#FFFCE0' };
                    const starColor = colorMap[starTheme] || '#FFFCE0';
                    const starThemeClass = 'star-theme-' + starTheme;

                    const starList = Array.from({length: starCount}, (_, i) => 'star-pos-' + (i + 1));
                    const starsHtml = (starCount > 0) ? `<div class="ff-prime-stars-layer ${starThemeClass}">${starList.map(p => `<svg class="ff-pinpoint-star ${p}" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="${starColor}"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>`).join('')}</div>` : '';

                    const shineHtml = hasShine ? `
                        <span class="ff-badge-shine ${sweepClass}" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('${fileUrl}');mask-image:url('${fileUrl}');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                            <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;animation-duration:${sweepSpd};mix-blend-mode:screen;display:block;"></span>
                        </span>` : '';

                    const isFullyStatic = (!hasShine && starCount === 0 && !item.has_gem_glint);
                    const animClass = isFullyStatic ? ' ff-anim-off' : '';

                    previewHtml = `
                        <div class="ff-prime-badge-wrap${animClass}" style="width:48px;height:48px;position:relative;background:transparent !important;filter:drop-shadow(0 0 6px ${item.glow_color || 'rgba(255,215,0,0.8)'});">
                            ${starsHtml}
                            <img src="${localUrl}" onerror="this.src='${fileUrl}'" class="ff-badge-img" ${isFullyStatic ? 'style="animation:none !important;"' : ''} alt="${item.name}">
                            ${shineHtml}
                            ${item.has_gem_glint ? '<div class="ff-gem-glint"></div>' : ''}
                        </div>
                    `;
                } else if (item.type === 'svg_code') {
                    previewHtml = `<div style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">${item.svg_code}</div>`;
                } else if (item.type === 'text_code') {
                    previewHtml = item.text_code;
                }

                // Rarity pill badge
                let rarityBadge = '';
                if (item.rarity === 'MYTHIC') {
                    rarityBadge = `<span class="text-[9px] px-2 py-0.5 rounded bg-amber-950/80 text-yellow-300 border border-yellow-500/40 font-bold uppercase tracking-wider">MYTHIC</span>`;
                } else if (item.rarity === 'LEGENDARY') {
                    rarityBadge = `<span class="text-[9px] px-2 py-0.5 rounded bg-orange-950/80 text-orange-300 border border-orange-500/40 font-bold uppercase tracking-wider">LEGENDARY</span>`;
                } else if (item.rarity === 'PURPLE' || item.rarity === 'EPIC') {
                    rarityBadge = `<span class="text-[9px] px-2 py-0.5 rounded bg-purple-950/80 text-purple-300 border border-purple-500/40 font-bold uppercase tracking-wider">${item.rarity}</span>`;
                } else {
                    rarityBadge = `<span class="text-[9px] px-2 py-0.5 rounded bg-blue-950/80 text-cyan-300 border border-cyan-500/40 font-bold uppercase tracking-wider">${item.rarity}</span>`;
                }

                return `
                    <div class="item-card p-3 sm:p-3.5 flex flex-col justify-between group" onclick="openItemModal('${item.id}')">
                        <!-- Top Header: Rarity & Patch -->
                        <div class="flex items-center justify-between mb-2">
                            ${rarityBadge}
                            <span class="text-[10px] text-gray-500 font-mono font-medium">${item.patch || 'OB55'}</span>
                        </div>

                        <!-- Center Preview Box (No Background, 100% Transparent) -->
                        <div class="item-preview-box h-28 sm:h-32 flex items-center justify-center p-2 mb-3 rounded-xl border border-white/5 relative overflow-hidden group-hover:border-purple-500/30 transition">
                            <div class="scale-100 group-hover:scale-115 transition-transform duration-300 flex items-center justify-center">
                                ${previewHtml}
                            </div>
                        </div>

                        <!-- Info Section -->
                        <div class="space-y-1 mb-2">
                            <h3 class="font-bold text-xs sm:text-sm text-white truncate tracking-wide group-hover:text-amber-300 transition" title="${item.name}">
                                ${item.name}
                            </h3>
                            <p class="text-[11px] text-gray-400 truncate">
                                ${item.category}
                            </p>
                        </div>

                        <!-- Bottom ID & Copy Button -->
                        <div class="pt-2 border-t border-[#1a1f3a] flex items-center justify-between">
                            <span class="text-[11px] font-mono font-bold text-emerald-400 tracking-wider">
                                #${item.id}
                            </span>
                            <button type="button" onclick="event.stopPropagation(); copyText('${item.id}', 'Item ID #${item.id} copied!');" class="text-gray-400 hover:text-white text-xs p-1 rounded hover:bg-white/10 transition" title="Copy ID">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Apply filters & search
        function applyFilters() {
            const q = document.getElementById('searchInput').value.toLowerCase().trim();
            const cat = document.getElementById('catSelect').value;
            const patch = document.getElementById('patchSelect').value;
            const rarity = document.getElementById('raritySelect').value;

            const filtered = itemsData.filter(item => {
                const matchQuery = !q || 
                    item.name.toLowerCase().includes(q) || 
                    item.id.toLowerCase().includes(q) || 
                    item.slug.toLowerCase().includes(q) || 
                    item.icon_id.toLowerCase().includes(q) || 
                    item.category.toLowerCase().includes(q);

                const matchCat = (cat === 'all') || (item.category_slug === cat);
                const matchPatch = (patch === 'all') || (item.patch_slug === patch);
                const matchRarity = (rarity === 'all') || (item.rarity.toUpperCase() === rarity.toUpperCase());

                return matchQuery && matchCat && matchPatch && matchRarity;
            });

            renderCards(filtered);
            document.getElementById('itemCountDisplay').textContent = `${filtered.length} items available`;
        }

        // Open Modal matching Image 4
        function openItemModal(itemId) {
            const item = itemsData.find(i => i.id === itemId || i.slug === itemId);
            if (!item) return;

            currentItem = item;

            const newUrl = window.location.pathname + '?item=' + item.id;
            window.history.pushState({ itemId: item.id }, '', newUrl);

            document.getElementById('modalItemName').textContent = item.name;
            document.getElementById('modalItemId').textContent = item.id;
            document.getElementById('modalIconId').textContent = item.icon_id;
            document.getElementById('modalRarity').textContent = item.rarity;
            document.getElementById('modalCategory').textContent = item.category;
            document.getElementById('modalPatch').textContent = item.patch || 'OB55';
            document.getElementById('modalDescription').textContent = item.description || 'Exclusive Free Fire verification badge asset.';

            document.getElementById('modalPhpCall').textContent = item.fn_call || '';
            document.getElementById('modalCdnUrl').textContent = item.cdn_urls?.jsdelivr || (item.file ? CDN_BASE + item.file : '');

            // Render Modal Preview (Authentic High-Res VFX)
            const previewContainer = document.getElementById('modalItemPreview');
            const fileUrl = item.cdn_urls?.jsdelivr || (item.file ? CDN_BASE + item.file : '');
            const localUrl = item.file ? 'badges/' + item.file : '';

            if (item.type === 'prime_image' || item.prime_level) {
                const lvl = item.prime_level || 1;
                const hasGlint = (item.has_gem_glint !== undefined) ? item.has_gem_glint : (lvl >= 7);
                const hasShine = (item.sweep_enabled !== undefined) ? item.sweep_enabled : (lvl >= 4);
                const sweepDir = item.sweep_direction || 'diag_bl_tr';
                const sweepClass = 'sweep-dir-' + sweepDir;
                const sweepSpd = item.sweep_speed || '2.6s';

                let density = (item.star_density !== undefined) ? parseInt(item.star_density) : (lvl === 8 ? 16 : (lvl === 7 ? 12 : (lvl === 6 ? 8 : (lvl === 5 ? 6 : (lvl === 4 ? 4 : 0)))));
                const starCount = Math.min(Math.max(density, 0), 50);
                const starTheme = item.star_theme || (lvl === 4 ? 'cyan' : 'gold');
                const colorMap = { cyan: '#00f0ff', ruby: '#ef4444', purple: '#c084fc', white: '#ffffff', emerald: '#10b981', amber: '#f59e0b', rainbow: '#ff007f', gold: '#FFFCE0' };
                const starColor = colorMap[starTheme] || '#FFFCE0';
                const starThemeClass = 'star-theme-' + starTheme;

                const starList = Array.from({length: starCount}, (_, i) => 'star-pos-' + (i + 1));
                const starsHtml = (starCount > 0) ? `<div class="ff-prime-stars-layer ${starThemeClass}">${starList.map(p => `<svg class="ff-pinpoint-star ${p}" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="${starColor}"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>`).join('')}</div>` : '';

                const shineHtml = hasShine ? `
                    <span class="ff-badge-shine ${sweepClass}" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('${fileUrl}');mask-image:url('${fileUrl}');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                        <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;animation-duration:${sweepSpd};mix-blend-mode:screen;display:block;"></span>
                    </span>
                ` : '';

                previewContainer.innerHTML = `
                    <div class="ff-prime-badge-wrap ff-prime-lvl-${lvl}" style="width:90px;height:90px;">
                        ${starsHtml}
                        <img src="${localUrl}" onerror="this.src='${fileUrl}'" class="ff-badge-img" alt="${item.name}">
                        ${shineHtml}
                        ${hasGlint ? '<div class="ff-gem-glint"></div>' : ''}
                    </div>
                `;
            } else if (item.type === 'image' && item.file) {
                const hasShine = (item.sweep_enabled === true || item.sweep_enabled === 'true' || item.sweep_enabled === 1);
                const sweepDir = item.sweep_direction || 'diag_bl_tr';
                const sweepClass = 'sweep-dir-' + sweepDir;
                const sweepSpd = item.sweep_speed || '2.2s';
                const starCount = Math.min(Math.max(parseInt(item.star_density) || 0, 0), 50);
                const starTheme = item.star_theme || 'gold';
                const colorMap = { cyan: '#00f0ff', ruby: '#ef4444', purple: '#c084fc', white: '#ffffff', emerald: '#10b981', amber: '#f59e0b', rainbow: '#ff007f', gold: '#FFFCE0' };
                const starColor = colorMap[starTheme] || '#FFFCE0';
                const starThemeClass = 'star-theme-' + starTheme;

                const starList = Array.from({length: starCount}, (_, i) => 'star-pos-' + (i + 1));
                const starsHtml = (starCount > 0) ? `<div class="ff-prime-stars-layer ${starThemeClass}">${starList.map(p => `<svg class="ff-pinpoint-star ${p}" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="${starColor}"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>`).join('')}</div>` : '';

                const shineHtml = hasShine ? `
                    <span class="ff-badge-shine ${sweepClass}" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('${fileUrl}');mask-image:url('${fileUrl}');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                        <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;animation-duration:${sweepSpd};mix-blend-mode:screen;display:block;"></span>
                    </span>` : '';

                const isFullyStatic = (!hasShine && starCount === 0 && !item.has_gem_glint);
                const animClass = isFullyStatic ? ' ff-anim-off' : '';

                previewContainer.innerHTML = `
                    <div class="ff-prime-badge-wrap${animClass}" style="width:90px;height:90px;position:relative;background:transparent !important;filter:drop-shadow(0 0 10px ${item.glow_color || 'rgba(255,215,0,0.8)'});">
                        ${starsHtml}
                        <img src="${localUrl}" onerror="this.src='${fileUrl}'" class="ff-badge-img" ${isFullyStatic ? 'style="animation:none !important;"' : ''} alt="${item.name}">
                        ${shineHtml}
                        ${item.has_gem_glint ? '<div class="ff-gem-glint"></div>' : ''}
                    </div>
                `;
            } else if (item.type === 'svg_code') {
                previewContainer.innerHTML = `<div style="width:58px;height:58px;display:flex;align-items:center;justify-content:center;">${item.svg_code}</div>`;
            } else if (item.type === 'text_code') {
                previewContainer.innerHTML = `<div class="scale-150">${item.text_code}</div>`;
            }

            const modal = document.getElementById('itemModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeItemModal() {
            const modal = document.getElementById('itemModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            currentItem = null;
            window.history.pushState({}, '', window.location.pathname);
        }

        function copyModalId() {
            if (currentItem) {
                copyText(currentItem.id, `Item ID #${currentItem.id} copied!`);
            }
        }

        function shareCurrentItem() {
            if (!currentItem) return;
            const shareUrl = window.location.origin + window.location.pathname + '?item=' + currentItem.id;
            const btn = document.getElementById('shareLinkBtn');
            const originalHtml = btn.innerHTML;
            copyText(shareUrl, 'Share link copied to clipboard!');
            btn.innerHTML = `<i class="fas fa-check text-sm"></i> <span>Link Copied!</span>`;
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 2000);
        }

        function copyText(text, successMsg = 'Copied!') {
            if (!navigator.clipboard) {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast(successMsg);
                return;
            }
            navigator.clipboard.writeText(text).then(() => {
                showToast(successMsg);
            }).catch(err => {
                console.error('Failed to copy', err);
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('appToast');
            document.getElementById('appToastMsg').textContent = msg;
            toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 2200);
        }

        function toggleDevInfo() {
            const block = document.getElementById('devInfoBlock');
            const icon = document.getElementById('devInfoIcon');
            if (block.classList.contains('hidden')) {
                block.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                block.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeItemModal();
        });

        document.getElementById('itemModal').addEventListener('click', (e) => {
            if (e.target.id === 'itemModal') closeItemModal();
        });

        document.addEventListener('DOMContentLoaded', () => {
            renderCards(itemsData);
            const urlParams = new URLSearchParams(window.location.search);
            const initialId = urlParams.get('item') || urlParams.get('id') || urlParams.get('badge');
            if (initialId) {
                openItemModal(initialId);
            }
        });
    </script>

</body>
</html>
PHPEOF;

file_put_contents(__DIR__ . '/index.php', $php_content);

// 3. BUILD index.html
$badges_json_encoded = json_encode($badges, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$html_content = str_replace(
    '<?php echo json_encode($master_badges, JSON_UNESCAPED_SLASHES); ?>',
    $badges_json_encoded,
    $php_content
);
$html_content = preg_replace('/<\?php.*?\?>/s', '', $html_content);
file_put_contents(__DIR__ . '/index.html', $html_content);

// 4. BUNDLE cdn/prime-badge.bundle.js
$css_content = file_get_contents(__DIR__ . '/cdn/prime-badge.css');
$js_content = file_get_contents(__DIR__ . '/cdn/prime-badge.js');
$css_json = json_encode($css_content);
$bundle_code = <<<BUN
/**
 * Free Fire Prime System - All-in-One Zero-Load CDN Web Component
 * 100% Transparent Background - Pure Client-Side Zero Server Load
 */
(function(window, document) {
  'use strict';

  // 1. Auto-inject Transparent CSS Stylesheet into <head>
  const css = {$css_json};
  if (!document.getElementById('ff-prime-badge-styles')) {
    const styleEl = document.createElement('style');
    styleEl.id = 'ff-prime-badge-styles';
    styleEl.textContent = css;
    document.head.appendChild(styleEl);
  }

  // 2. Prime Badge Logic
  {$js_content}

})(typeof window !== 'undefined' ? window : this, typeof document !== 'undefined' ? document : {});
BUN;
file_put_contents(__DIR__ . '/cdn/prime-badge.bundle.js', $bundle_code);

echo "index.php, index.html, cdn/ff-badges.js, and cdn/prime-badge.bundle.js re-generated with 1:1 Free Fire Prime VFX!\n";
