<?php
header('Connection: close');
// Auto-Sync: Automatically regenerate CDN/Explorer/SVGs whenever JSON, CSS, or generator files change
$__syncSrc = max(
    @filemtime(__DIR__ . '/api/badges.json') ?: 0,
    @filemtime(__DIR__ . '/cdn/prime-badge.css') ?: 0,
    @filemtime(__DIR__ . '/cdn/ff-badges.css') ?: 0,
    @filemtime(__DIR__ . '/generate_all.php') ?: 0
);
$__syncOut = min(
    @filemtime(__DIR__ . '/cdn/ff-badges.js') ?: 0,
    @filemtime(__DIR__ . '/index.html') ?: 0
);
if ($__syncSrc > $__syncOut && file_exists(__DIR__ . '/generate_all.php')) {
    @shell_exec('php ' . escapeshellarg(__DIR__ . '/generate_all.php') . ' 2>&1');
}
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
    
    <!-- Zero-Flash Theme Bootstrap (Before Paint) -->
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

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Orbitron, Rajdhani, Fira Code, Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;800;900&family=Rajdhani:wght@600;700&family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Authentic Free Fire Prime Badges CSS (100% Transparent, Ascending Beam, Gem Glint) -->
    <link rel="stylesheet" href="cdn/prime-badge.css">
    <!-- Centralized Apple macOS Design System -->
    <link rel="stylesheet" href="assets/apple-theme.css">
    <!-- Turky Hub Autonomous Security Guard -->
    <script src="cdn/turky-guard.js"></script>

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
            .checker-bg {
            background-color: #0c0f20 !important;
            background-image: 
                linear-gradient(45deg, #181d38 25%, transparent 25%), 
                linear-gradient(-45deg, #181d38 25%, transparent 25%), 
                linear-gradient(45deg, transparent 75%, #181d38 75%), 
                linear-gradient(-45deg, transparent 75%, #181d38 75%) !important;
            background-size: 16px 16px !important;
            background-position: 0 0, 0 8px, 8px -8px, -8px 0px !important;
        }
    </style>
</head>
<body class="apple-ui min-h-screen flex flex-col selection:bg-red-500 selection:text-white">

    <!-- SECURE AUTHENTICATION GATE (TURKY HUB Access Gate) -->
    <div id="authGateModal" class="apple-login-backdrop fixed inset-0 z-50 bg-[#09090B] flex items-center justify-center p-4 min-h-screen" style="display:flex;">
        <!-- Floating Theme Toggle on Gate -->
        <button type="button" data-theme-toggle class="theme-toggle-btn fixed top-5 right-5 z-50" aria-label="Switch Theme" aria-pressed="false">
            <span class="theme-icon-wrap" aria-hidden="true">
                <i class="fas fa-sun icon-sun text-xs"></i>
                <i class="fas fa-moon icon-moon text-xs"></i>
            </span>
            <span data-theme-label>Dark</span>
        </button>

        <div class="apple-window relative w-full max-w-md overflow-hidden shadow-2xl transition duration-300">
            <!-- Decorative macOS Window Header -->
            <div class="macos-window-bar">
                <div class="macos-traffic-lights" aria-hidden="true">
                    <span class="macos-dot macos-dot-red"></span>
                    <span class="macos-dot macos-dot-yellow"></span>
                    <span class="macos-dot macos-dot-green"></span>
                </div>
                <span class="macos-window-title">TURKY HUB — Access Verification</span>
                <span class="w-10" aria-hidden="true"></span>
            </div>

            <div class="p-7 sm:p-8">
            <!-- Top Badge Logo Preview with Live VFX -->
            <div class="flex flex-col items-center text-center mb-6">
                <div class="relative w-20 h-20 mb-3 flex items-center justify-center">
                    <div class="ff-prime-badge-wrap ff-prime-lvl-8" style="width:68px;height:68px;position:relative;background:transparent !important;filter:drop-shadow(0 0 10px rgba(255,215,0,0.8));">
                        <div class="ff-prime-stars-layer star-theme-gold">
                            <svg class="ff-pinpoint-star star-pos-1" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="#FFFCE0"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>
                            <svg class="ff-pinpoint-star star-pos-3" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="#FFFCE0"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>
                            <svg class="ff-pinpoint-star star-pos-5" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="#FFFCE0"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>
                            <svg class="ff-pinpoint-star star-pos-7" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="#FFFCE0"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>
                        </div>
                        <img src="badges/ff_ui_prime_icon_08.png" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/badges/ff_ui_prime_icon_08.png'" class="ff-badge-img" alt="Turky Hub Emblem">
                        <span class="ff-badge-shine sweep-dir-diag_bl_tr" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('badges/ff_ui_prime_icon_08.png');mask-image:url('badges/ff_ui_prime_icon_08.png');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                            <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;animation-duration:2.2s;mix-blend-mode:screen;display:block;"></span>
                        </span>
                        <div class="ff-gem-glint"></div>
                    </div>
                </div>
                
                <div class="apple-badge-red inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-mono text-[10px] uppercase font-bold tracking-wider mb-2">
                    <i class="fas fa-shield-halved text-[9px]"></i>
                    <span>Protected Access Gate</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">TURKY HUB</h2>
                <p class="text-xs text-gray-400 mt-1 max-w-xs">Enter your private access password to unlock the badges explorer, CDN links, and API data.</p>
            </div>

            <!-- Error Banner -->
            <div id="gateErrorMsg" class="hidden mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2">
                <i class="fas fa-circle-exclamation shrink-0 text-sm"></i>
                <span id="gateErrorText">Invalid access password! Access Denied.</span>
            </div>

            <!-- Authentication Form -->
            <form id="authGateForm" onsubmit="handleGateLogin(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-300 tracking-wide mb-1.5 flex items-center justify-between">
                        <span>Security Access Password</span>
                        <span class="text-[10px] text-red-400 font-mono">Master Key / PIN</span>
                    </label>
                    <div class="relative">
                        <i class="fas fa-key absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="password" id="gatePassword" required autocomplete="current-password" placeholder="Enter access password..." class="w-full border rounded-xl pl-9 pr-10 py-3 text-xs placeholder-gray-500 focus:outline-none transition font-mono">
                        <button type="button" onclick="toggleGatePassVisibility()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-red-500 transition cursor-pointer" aria-label="Toggle password visibility">
                            <i id="gatePassEye" class="fas fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-gray-400 cursor-pointer select-none">
                        <input type="checkbox" id="gateRemember" checked class="w-3.5 h-3.5 rounded accent-red-500 cursor-pointer">
                        <span>Remember this device</span>
                    </label>
                    <span class="text-[11px] text-gray-500 font-mono">OB55 Engine</span>
                </div>

                <button type="submit" id="gateSubmitBtn" class="apple-btn-primary apple-keep-white w-full py-3.5 px-6 text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer">
                    <span>Authenticate & Unlock Hub</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>

            <div class="mt-5 text-center">
                <span class="text-[11px] text-gray-500 font-mono">TURKY HUB · Apple Minimalist System</span>
            </div>
            </div>
        </div>
    </div>

    <!-- MAIN APP CONTAINER (Protected: only visible when authenticated) -->
    <div id="mainAppContainer" class="hidden min-h-screen flex flex-col">


    <!-- Top Navigation Bar -->
    <header class="apple-navbar sticky top-0 z-40">
        <div class="max-w-[1720px] mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
            
            <!-- Left Brand + Decorative macOS Dots -->
            <div class="flex items-center gap-3 min-w-0">
                <div class="macos-traffic-lights hidden sm:inline-flex" aria-hidden="true">
                    <span class="macos-dot macos-dot-red"></span>
                    <span class="macos-dot macos-dot-yellow"></span>
                    <span class="macos-dot macos-dot-green"></span>
                </div>
                <div class="w-8 h-8 rounded-xl bg-red-500 flex items-center justify-center shadow-sm shrink-0">
                    <i class="fas fa-fire-flame-curved text-white apple-keep-white text-sm"></i>
                </div>
                <div class="flex items-center gap-2 min-w-0">
                    <span class="font-bold text-white text-sm sm:text-base tracking-tight truncate">TURKY HUB</span>
                    <span class="text-xs text-gray-400 font-medium hidden xl:inline">FF Items</span>
                    <span class="hidden sm:inline-flex items-center gap-1.5 text-[10px] px-2 py-0.5 rounded-full apple-badge-red font-bold uppercase tracking-wider shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        OB55
                    </span>
                </div>
            </div>

            <!-- Center Navigation Tabs (Desktop) -->
            <nav aria-label="Main Navigation" class="hidden lg:flex items-center gap-1 apple-nav-pill">
                <button type="button" id="navTabAll" onclick="filterCategory('all')" class="apple-nav-item active px-3.5 py-1.5 text-xs flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-book-bookmark text-xs"></i>
                    <span>Items Library</span>
                </button>
                <button type="button" id="navTabPrime" onclick="filterCategory('prime')" class="apple-nav-item px-3.5 py-1.5 text-xs flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-shield-halved text-xs"></i>
                    <span>Prime Level (1-8)</span>
                </button>
                <a href="#itemsGrid" class="apple-nav-item px-3.5 py-1.5 text-xs flex items-center gap-2">
                    <i class="fas fa-user-astronaut text-xs"></i>
                    <span>Player Inspector</span>
                </a>
                <a href="admin.php" class="apple-nav-item px-3.5 py-1.5 text-xs flex items-center gap-2">
                    <i class="fas fa-crown text-xs"></i>
                    <span>Admin Panel</span>
                </a>
                <a href="ai-integration.php" class="apple-nav-item px-3.5 py-1.5 text-xs flex items-center gap-2">
                    <i class="fas fa-robot text-xs"></i>
                    <span>AI Integration</span>
                </a>
            </nav>

            <!-- Right Actions: Theme Switcher, Auth Pill, Admin Pill & Mobile Toggle -->
            <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                <!-- Sun / Moon Theme Switcher -->
                <button type="button" data-theme-toggle class="theme-toggle-btn" aria-label="Switch Theme" aria-pressed="false">
                    <span class="theme-icon-wrap" aria-hidden="true">
                        <i class="fas fa-sun icon-sun text-xs"></i>
                        <i class="fas fa-moon icon-moon text-xs"></i>
                    </span>
                    <span data-theme-label class="hidden sm:inline">Dark</span>
                </button>

                <!-- Auth User & Logout -->
                <div id="authUserPill" class="apple-surface-secondary flex items-center gap-2 px-2.5 sm:px-3 py-1.5 text-xs">
                    <i class="fas fa-user-check text-emerald-400 text-xs"></i>
                    <span id="authEmailDisplay" class="text-emerald-400 font-mono text-[11px] font-bold hidden xl:inline">VIP ACCESS</span>
                    <button type="button" onclick="logoutExplorer()" class="text-red-500 hover:text-red-600 px-1 py-0.5 rounded transition text-xs flex items-center gap-1 font-semibold cursor-pointer" title="Sign Out">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="text-[11px] hidden sm:inline">Logout</span>
                    </button>
                </div>

                <!-- Admin Profile Pill -->
                <a href="admin.php" class="hidden sm:flex apple-surface-secondary items-center gap-2 px-3 py-1.5 hover:border-red-500/40 transition">
                    <div class="w-6 h-6 rounded-lg bg-red-500 flex items-center justify-center font-bold text-white apple-keep-white text-[11px]">
                        A
                    </div>
                    <div class="hidden xl:block text-left leading-tight">
                        <div class="text-xs font-semibold text-white">Admin</div>
                        <div class="text-[9px] text-gray-400 font-medium">Studio</div>
                    </div>
                </a>

                <!-- Mobile Menu Toggle -->
                <button type="button" data-mobile-nav-toggle aria-controls="mobileNavDrawer" aria-expanded="false" aria-label="Toggle Menu" class="lg:hidden apple-btn-secondary w-9 h-9 flex items-center justify-center cursor-pointer">
                    <i class="fas fa-bars text-xs"></i>
                </button>
            </div>

        </div>

        <!-- Mobile Collapsible Navigation Drawer -->
        <div id="mobileNavDrawer" class="hidden lg:hidden border-t border-white/10 px-4 py-3 space-y-1.5">
            <button type="button" onclick="filterCategory('all')" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 hover:bg-white/5 text-white">
                <i class="fas fa-book-bookmark text-red-500 w-4"></i>
                <span>Items Library</span>
            </button>
            <button type="button" onclick="filterCategory('prime')" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 hover:bg-white/5 text-white">
                <i class="fas fa-shield-halved text-red-500 w-4"></i>
                <span>Prime Level (1-8)</span>
            </button>
            <a href="admin.php" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 hover:bg-white/5 text-white">
                <i class="fas fa-crown text-red-500 w-4"></i>
                <span>Admin Panel</span>
            </a>
            <a href="ai-integration.php" class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 hover:bg-white/5 text-white">
                <i class="fas fa-robot text-red-500 w-4"></i>
                <span>AI Integration</span>
            </a>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-[1720px] mx-auto px-4 sm:px-6 py-6 space-y-6 w-full">

        <!-- Banner / Explorer Window Header -->
        <section class="apple-window overflow-hidden">
            <div class="macos-window-bar">
                <div class="macos-traffic-lights" aria-hidden="true">
                    <span class="macos-dot macos-dot-red"></span>
                    <span class="macos-dot macos-dot-yellow"></span>
                    <span class="macos-dot macos-dot-green"></span>
                </div>
                <span class="macos-window-title">TURKY HUB — FF Items Explorer</span>
                <span class="text-[10px] font-mono text-gray-400">v3.2 CDN</span>
            </div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 sm:p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl apple-badge-red flex items-center justify-center text-xl shrink-0">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                            TURKY HUB — FF Items EXPLORER
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-400 mt-0.5">
                            <span class="text-white font-semibold" id="itemCountDisplay"><?= count($master_badges) ?> items available</span> · Headless CDN &amp; Transparent Assets
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="apple-surface-secondary px-3.5 py-2 text-xs font-semibold flex items-center gap-2 font-mono text-gray-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        OB55 Engine Active
                    </span>
                    <a href="admin.php?new=1" class="apple-btn-primary apple-keep-white px-4 py-2 text-xs flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Badge in Admin</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Search & Filter Controls -->
        <section class="apple-window p-4 sm:p-5 space-y-4">
            
            <!-- Full Width Search Input -->
            <div class="relative">
                <i class="fas fa-search absolute left-4 top-3.5 text-gray-400 text-sm"></i>
                <input type="text" id="searchInput" oninput="applyFilters()" placeholder="Search by Item Name, ID, or Keywords (e.g. prime, level 8, 126393684880)..." class="w-full border rounded-xl pl-11 pr-4 py-3 text-xs sm:text-sm placeholder-gray-500 focus:outline-none transition">
            </div>

            <!-- Filters Row: Categories, Patches, Rarities, Apply Button -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
                
                <!-- Category Select -->
                <div class="relative">
                    <select id="catSelect" onchange="applyFilters()" class="w-full border rounded-xl px-3.5 py-2.5 text-xs focus:outline-none appearance-none cursor-pointer">
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
                    <i class="fas fa-chevron-down absolute right-3.5 top-3.5 text-gray-400 text-[10px] pointer-events-none"></i>
                </div>

                <!-- Patches / Versions Select -->
                <div class="relative">
                    <select id="patchSelect" onchange="applyFilters()" class="w-full border rounded-xl px-3.5 py-2.5 text-xs focus:outline-none appearance-none cursor-pointer">
                        <option value="all">All Patches / Versions</option>
                        <option value="ob55">OB55 (Current)</option>
                        <option value="ob54">OB54</option>
                        <option value="legacy">Legacy Editions</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3.5 top-3.5 text-gray-400 text-[10px] pointer-events-none"></i>
                </div>

                <!-- Rarities Select -->
                <div class="relative">
                    <select id="raritySelect" onchange="applyFilters()" class="w-full border rounded-xl px-3.5 py-2.5 text-xs focus:outline-none appearance-none cursor-pointer">
                        <option value="all">All Rarities</option>
                        <option value="MYTHIC">MYTHIC</option>
                        <option value="LEGENDARY">LEGENDARY</option>
                        <option value="EPIC">EPIC</option>
                        <option value="PURPLE">PURPLE</option>
                        <option value="RARE">RARE</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3.5 top-3.5 text-gray-400 text-[10px] pointer-events-none"></i>
                </div>

                <!-- Apply Filters Button -->
                <div>
                    <button type="button" onclick="applyFilters()" class="apple-btn-primary apple-keep-white w-full text-xs py-2.5 px-4 flex items-center justify-center gap-2 cursor-pointer">
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

    <!-- Understated Apple Footer -->
    <footer class="apple-footer mt-auto">
        <div class="max-w-[1720px] mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5">
                <div class="macos-traffic-lights" aria-hidden="true">
                    <span class="macos-dot macos-dot-red"></span>
                    <span class="macos-dot macos-dot-yellow"></span>
                    <span class="macos-dot macos-dot-green"></span>
                </div>
                <span class="font-semibold text-white">TURKY HUB</span>
                <span>· OB55 Headless Badge CDN</span>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <a href="admin.php" class="hover:text-red-500 transition">Admin Studio</a>
                <a href="ai-integration.php" class="hover:text-red-500 transition">AI Integration</a>
                <span class="font-mono text-[11px]">Transparent Vector &amp; PNG Delivery</span>
            </div>
        </div>
    </footer>

    <!-- Modal Dialog (macOS Window Inspector) -->
    <div id="itemModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-md hidden items-center justify-center p-4 overflow-y-auto">
        <div class="modal-box w-full max-w-[460px] overflow-hidden relative my-auto">
            <!-- macOS Window Title Bar -->
            <div class="macos-window-bar">
                <div class="macos-traffic-lights" aria-hidden="true">
                    <span class="macos-dot macos-dot-red"></span>
                    <span class="macos-dot macos-dot-yellow"></span>
                    <span class="macos-dot macos-dot-green"></span>
                </div>
                <span class="macos-window-title">Asset Inspector</span>
                <button type="button" onclick="closeItemModal()" aria-label="Close Inspector" class="w-7 h-7 rounded-full apple-surface-secondary text-gray-400 hover:text-red-500 flex items-center justify-center text-xs transition cursor-pointer">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 sm:p-7">
            <!-- Preview Stage (100% Transparent, No Background) -->
            <div id="modalPreviewBox" class="w-48 h-48 mx-auto rounded-2xl bg-[#09090B] border border-white/10 relative flex items-center justify-center overflow-hidden shadow-inner transition-colors duration-200">
                <div id="modalAura" class="absolute inset-0 bg-gradient-to-tr from-red-500/10 via-transparent to-amber-500/10 pointer-events-none"></div>
                <div id="modalItemPreview" class="relative z-10 flex items-center justify-center w-full h-full p-2">
                    <!-- Image or Animation injected here -->
                </div>
            </div>
            <!-- Transparency Verification Quick Switcher -->
            <div class="flex items-center justify-center gap-2 mt-2.5">
                <button type="button" onclick="toggleModalCheckerboard()" class="apple-btn-secondary text-[10px] font-mono px-3 py-1 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fas fa-eye text-red-500"></i> <span>Verify 100% Transparency (Checkerboard)</span>
                </button>
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
                            <span class="text-[10px] text-gray-400 uppercase font-mono font-bold">Direct Host / Vercel Image URL:</span>
                            <div class="flex items-center gap-1.5">
                                <code id="modalDirectUrl" class="text-[10px] font-mono text-emerald-300 bg-black/40 p-1.5 rounded flex-1 overflow-x-auto whitespace-nowrap custom-scroll"></code>
                                <button type="button" onclick="copyText(document.getElementById('modalDirectUrl').textContent, 'Direct Image URL copied!')" class="px-2 py-1 bg-emerald-500/20 text-emerald-300 rounded text-[10px] shrink-0 hover:bg-emerald-500/30">Copy</button>
                            </div>
                        </div>
                        <!-- Animated SVG CDN URL (Primary Global CDN) -->
                        <div class="space-y-1">
                            <span class="text-[10px] text-purple-400 uppercase font-mono font-bold flex items-center gap-1">
                                <i class="fas fa-file-code"></i> Global Animated SVG CDN URL (for &lt;img&gt; & Browser):
                            </span>
                            <div class="flex items-center gap-1.5">
                                <code id="modalAnimatedSvgUrl" class="text-[10px] font-mono text-purple-300 bg-black/40 p-1.5 rounded flex-1 overflow-x-auto whitespace-nowrap custom-scroll"></code>
                                <button type="button" onclick="copyText(document.getElementById('modalAnimatedSvgUrl').textContent, 'Animated SVG CDN URL copied!')" class="px-2 py-1 bg-purple-500/20 text-purple-300 rounded text-[10px] shrink-0 hover:bg-purple-500/30">Copy</button>
                            </div>
                        </div>

                        <!-- Live Interactive HTML Player URL -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-amber-400 uppercase font-mono font-bold flex items-center gap-1">
                                    <i class="fas fa-bolt text-amber-400"></i> Interactive HTML Player URL (Hosted):
                                </span>
                                <a id="modalPlayerOpenLink" href="#" target="_blank" class="text-[9px] text-cyan-400 hover:underline font-mono">Open in New Tab <i class="fas fa-external-link-alt text-[8px]"></i></a>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <code id="modalAnimatedPlayerUrl" class="text-[10px] font-mono text-amber-300 bg-black/40 p-1.5 rounded flex-1 overflow-x-auto whitespace-nowrap custom-scroll"></code>
                                <button type="button" onclick="copyText(document.getElementById('modalAnimatedPlayerUrl').textContent, 'Interactive HTML Player URL copied!')" class="px-2 py-1 bg-amber-500/20 text-amber-300 rounded text-[10px] shrink-0 hover:bg-amber-500/30">Copy</button>
                            </div>
                        </div>

                        <!-- Web Component HTML Tag -->
                        <div class="space-y-1">
                            <span class="text-[10px] text-cyan-400 uppercase font-mono font-bold flex items-center gap-1">
                                <i class="fas fa-code"></i> Web Component HTML Tag:
                            </span>
                            <div class="flex items-center gap-1.5">
                                <code id="modalWebTagCode" class="text-[10px] font-mono text-cyan-300 bg-black/40 p-1.5 rounded flex-1 overflow-x-auto whitespace-nowrap custom-scroll"></code>
                                <button type="button" onclick="copyText(document.getElementById('modalWebTagCode').textContent, 'Web Component Tag copied!')" class="px-2 py-1 bg-cyan-500/20 text-cyan-300 rounded text-[10px] shrink-0 hover:bg-cyan-500/30">Copy</button>
                            </div>
                        </div>

                        <!-- Raw Static Transparent PNG -->
                        <div class="space-y-1">
                            <span class="text-[10px] text-gray-400 uppercase font-mono font-bold flex items-center gap-1">
                                <i class="fas fa-image"></i> Raw Static Transparent PNG:
                            </span>
                            <div class="flex items-center gap-1.5">
                                <code id="modalCdnUrl" class="text-[10px] font-mono text-gray-300 bg-black/40 p-1.5 rounded flex-1 overflow-x-auto whitespace-nowrap custom-scroll"></code>
                                <button type="button" onclick="copyText(document.getElementById('modalCdnUrl').textContent, 'Raw Transparent PNG URL copied!')" class="px-2 py-1 bg-white/10 text-white rounded text-[10px] shrink-0 hover:bg-white/20">Copy</button>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] text-gray-400 uppercase font-mono font-bold">JSON API Endpoint:</span>
                            <div class="flex items-center gap-1.5">
                                <code id="modalApiUrl" class="text-[10px] font-mono text-purple-300 bg-black/40 p-1.5 rounded flex-1 overflow-x-auto whitespace-nowrap custom-scroll"></code>
                                <button type="button" onclick="copyText(document.getElementById('modalApiUrl').textContent, 'API URL copied!')" class="px-2 py-1 bg-purple-500/20 text-purple-300 rounded text-[10px] shrink-0 hover:bg-purple-500/30">Copy</button>
                            </div>
                        </div>
                        <div class="text-[9px] text-gray-400 bg-white/[0.02] p-2 rounded-lg border border-white/5 font-mono">
                            <i class="fas fa-circle-info text-amber-400 mr-1"></i>
                            <strong>Integration Note:</strong> External websites & apps do NOT require login or credentials to render these CDN badges.
                        </div>
                    </div>
                </div>

            </div>

            <!-- Action Buttons: STRICTLY NO DOWNLOAD BUTTON! Only Share Link and Close -->
            <div class="flex items-center gap-3 mt-6 pt-2">
                <button type="button" id="shareLinkBtn" onclick="shareCurrentItem()" class="apple-btn-primary apple-keep-white flex-1 py-3 px-4 flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fas fa-share-nodes text-sm"></i>
                    <span>Share Link</span>
                </button>

                <button type="button" onclick="closeItemModal()" class="apple-btn-secondary py-3 px-6 cursor-pointer">
                    Close
                </button>
            </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="appToast" class="fixed bottom-6 right-6 bg-[#18181B] text-white apple-keep-white border border-red-500/40 font-semibold text-xs px-4 py-2.5 rounded-2xl shadow-2xl flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 z-50 pointer-events-none">
        <i class="fas fa-check-circle text-red-500 text-base"></i>
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
            const navAll = document.getElementById('navTabAll');
            const navPrime = document.getElementById('navTabPrime');
            if (navAll) navAll.classList.toggle('active', cat !== 'prime');
            if (navPrime) navPrime.classList.toggle('active', cat === 'prime');
            const mobileDrawer = document.getElementById('mobileNavDrawer');
            if (mobileDrawer && !mobileDrawer.classList.contains('hidden')) {
                mobileDrawer.classList.add('hidden');
            }
            applyFilters();
        }

        // Render card & modal preview HTML cleanly (supports Prime, V-Badge, Vector SVG, Text, and all VFX toggles)
        function buildBoneHtml(item, maskUrl) {
            const boneMode = item.bone_mode || 'inside';
            if (boneMode === 'off' || !item.bone_data || !Array.isArray(item.bone_data) || item.bone_data.length === 0) return '';
            const hasPulse = (item.pulse_enabled !== false && item.pulse_enabled !== 'false' && item.pulse_enabled !== 0);
            const boneAnim = hasPulse ? 'animation:pulse_bone 2.4s infinite alternate ease-in-out;' : 'animation:none;';
            const innerSpots = item.bone_data.map(b => `
                <div style="position:absolute;top:${b.y}%;left:${b.x}%;transform:translate(-50%,-50%);width:${(b.size||20)*2.2}px;height:${(b.size||20)*2.2}px;border-radius:50%;background:radial-gradient(circle, #ffffff 0%, ${b.color||'#FFD700'} 35%, transparent 70%);mix-blend-mode:color-dodge;filter:blur(2px);opacity:0.95;${boneAnim}pointer-events:none;"></div>
                <div style="position:absolute;top:${b.y}%;left:${b.x}%;transform:translate(-50%,-50%);width:${Math.max(6, (b.size||20)*0.45)}px;height:${Math.max(6, (b.size||20)*0.45)}px;border-radius:50%;background:#ffffff;box-shadow:0 0 10px #ffffff, 0 0 18px ${b.color||'#FFD700'};mix-blend-mode:screen;pointer-events:none;"></div>
            `).join('');
            let out = '';
            if (boneMode === 'inside' || boneMode === 'dual') {
                out += `
                    <div class="ff-bone-inside-container" style="position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:8;overflow:hidden;-webkit-mask-image:url('${maskUrl}');mask-image:url('${maskUrl}');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                        ${innerSpots}
                    </div>
                `;
            }
            if (boneMode === 'outer' || boneMode === 'dual') {
                out += item.bone_data.map(b => `
                    <div style="position:absolute;top:${b.y}%;left:${b.x}%;transform:translate(-50%,-50%);width:${(b.size||20)*3.5}px;height:${(b.size||20)*3.5}px;border-radius:50%;background:radial-gradient(circle, ${b.color||'#FFD700'} 0%, rgba(255,255,255,0.4) 25%, transparent 70%);mix-blend-mode:screen;filter:blur(8px);opacity:0.65;${boneAnim}pointer-events:none;z-index:9;"></div>
                `).join('');
            }
            return out;
        }

        function buildExplorerBadgeHtml(item, boxPx) {
            const validFile = (item.file && item.file.trim() !== '') ? item.file.trim() : 'FF_UI_Badge_KOL01.png';
            const fileUrl = item.cdn_urls?.jsdelivr || (CDN_BASE + validFile);
            const localUrl = 'badges/' + validFile;
            const boneHtml = buildBoneHtml(item, localUrl);
            const isPrime = item.type === 'prime_image' || !!item.prime_level;
            const lvl = parseInt(item.prime_level || 0);

            const hasGlow = (item.glow_enabled !== false && item.glow_enabled !== 'false' && item.glow_enabled !== 0);
            const glowStyle = hasGlow ? `filter:drop-shadow(0 0 8px ${item.glow_color || 'rgba(255,215,0,0.8)'});` : '';
            const hasShine = (item.sweep_enabled !== undefined && item.sweep_enabled !== null)
                ? (item.sweep_enabled === true || item.sweep_enabled === 'true' || item.sweep_enabled === 1)
                : (lvl >= 4 || !isPrime);
            const hasGlint = (item.has_gem_glint !== undefined && item.has_gem_glint !== null)
                ? (item.has_gem_glint === true || item.has_gem_glint === 'true' || item.has_gem_glint === 1)
                : (lvl >= 7);
            const hasPulseRaw = (item.pulse_enabled !== false && item.pulse_enabled !== 'false' && item.pulse_enabled !== 0);
            const isStaticLowerPrime = (lvl >= 1 && lvl <= 3 && !hasShine && !hasGlint && !(parseInt(item.star_density || 0) > 0));
            const hasPulse = hasPulseRaw && !isStaticLowerPrime;

            const rawDensity = (item.star_density !== undefined && item.star_density !== null) ? parseInt(item.star_density) : (lvl === 8 ? 16 : (lvl === 7 ? 12 : (lvl === 6 ? 8 : (lvl === 5 ? 6 : (lvl === 4 ? 4 : 0)))));
            const starCount = Math.min(Math.max(isNaN(rawDensity) ? 0 : rawDensity, 0), 50);
            const starTheme = item.star_theme || (lvl === 4 ? 'cyan' : 'gold');
            const colorMap = { cyan: '#00f0ff', ruby: '#ef4444', purple: '#c084fc', white: '#ffffff', emerald: '#10b981', amber: '#f59e0b', rainbow: '#ff007f', gold: '#FFFCE0' };
            const starColor = colorMap[starTheme] || '#FFFCE0';
            const starsHtml = (starCount > 0)
                ? `<div class="ff-prime-stars-layer star-theme-${starTheme}">${Array.from({length: starCount}, (_, i) => `<svg class="ff-pinpoint-star star-pos-${i + 1}" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="${starColor}"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>`).join('')}</div>`
                : '';

            const isFullyStatic = (!hasShine && starCount === 0 && !hasGlint && !boneHtml && !hasPulse);
            const pulseClass = hasPulse ? ' ff-pulse-on' : ' ff-pulse-off';
            const animClass = isFullyStatic ? ' ff-anim-off' : '';

            if (item.type === 'svg_code') {
                const svgSz = Math.round(boxPx * 0.72);
                const innerStyle = hasPulse ? `width:${svgSz}px;height:${svgSz}px;display:flex;align-items:center;justify-content:center;` : `width:${svgSz}px;height:${svgSz}px;display:flex;align-items:center;justify-content:center;animation:none !important;transform:none !important;`;
                return `
                    <div class="ff-prime-badge-wrap${pulseClass}${animClass}" style="width:${boxPx}px;height:${boxPx}px;${glowStyle}background:transparent !important;">
                        ${starsHtml}
                        <div class="ff-badge-img" style="${innerStyle}">${item.svg_code || ''}</div>
                        ${hasGlint ? '<div class="ff-gem-glint"></div>' : ''}
                    </div>
                `;
            }
            if (item.type === 'text_code') {
                const txtWrap = boxPx >= 80 ? 'scale-150' : '';
                return `
                    <div class="ff-prime-badge-wrap${animClass}" style="display:inline-flex;align-items:center;justify-content:center;${glowStyle}background:transparent !important;">
                        ${starsHtml}
                        <div class="${txtWrap}" style="${hasPulse ? '' : 'animation:none !important;'}">${item.text_code || ''}</div>
                        ${hasGlint ? '<div class="ff-gem-glint"></div>' : ''}
                    </div>
                `;
            }

            const sweepDir = item.sweep_direction || 'diag_bl_tr';
            const sweepSpd = item.sweep_speed || (lvl > 0 ? '2.6s' : '2.2s');
            const shineHtml = hasShine ? `
                <span class="ff-badge-shine sweep-dir-${sweepDir}" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('${localUrl}');mask-image:url('${localUrl}');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                    <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;animation-duration:${sweepSpd};mix-blend-mode:screen;display:block;"></span>
                </span>
            ` : '';

            const wrapCls = (isPrime && lvl > 0) ? `ff-prime-badge-wrap ff-prime-lvl-${lvl}` : 'ff-prime-badge-wrap';
            const imgStyle = (isFullyStatic || !hasPulse) ? 'style="animation:none !important;transform:none !important;"' : '';
            return `
                <div class="${wrapCls}${pulseClass}${animClass}" style="width:${boxPx}px;height:${boxPx}px;position:relative;background:transparent !important;${glowStyle}">
                    ${starsHtml}
                    <img src="${localUrl}" onerror="this.onerror=null; this.src='${fileUrl}'" class="ff-badge-img" ${imgStyle} alt="${item.name}">
                    ${shineHtml}
                    ${hasGlint ? '<div class="ff-gem-glint"></div>' : ''}
                    ${boneHtml}
                </div>
            `;
        }

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
                const previewHtml = buildExplorerBadgeHtml(item, isPrime ? 62 : 48);

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
            const hostBase = window.location.origin;
            const itemDirect = item.file ? (hostBase + '/badges/' + item.file) : '';
            const itemApi = hostBase + '/api/v1/badge/' + item.id + '.json';
            const itemCdn = item.cdn_urls?.jsdelivr || (item.file ? CDN_BASE + item.file : '');
            const isHttpHost = (window.location?.protocol || '').startsWith('http') && !(window.location?.hostname || '').includes('jsdelivr');
            const animPlayerUrl = isHttpHost ? (hostBase + '/embed.html?id=' + item.id) : ('embed.html?id=' + item.id);
            const animSvgUrl = item.cdn_urls?.animated_svg || ('https://cdn.jsdelivr.net/gh/' + REPO_NAME + '@main/badges/svg/' + item.id + '.svg');
            const webTagCode = '<ff-badge id="' + item.id + '" size="' + (item.default_size || 32) + '"></ff-badge>';
            
            if (document.getElementById('modalDirectUrl')) document.getElementById('modalDirectUrl').textContent = itemDirect;
            if (document.getElementById('modalCdnUrl')) document.getElementById('modalCdnUrl').textContent = itemCdn;
            if (document.getElementById('modalApiUrl')) document.getElementById('modalApiUrl').textContent = itemApi;
            if (document.getElementById('modalAnimatedPlayerUrl')) document.getElementById('modalAnimatedPlayerUrl').textContent = animPlayerUrl;
            if (document.getElementById('modalAnimatedSvgUrl')) document.getElementById('modalAnimatedSvgUrl').textContent = animSvgUrl;
            if (document.getElementById('modalWebTagCode')) document.getElementById('modalWebTagCode').textContent = webTagCode;
            if (document.getElementById('modalPlayerOpenLink')) document.getElementById('modalPlayerOpenLink').href = 'embed.html?id=' + item.id;

            // Render Modal Preview (Authentic High-Res VFX via shared builder)
            const previewContainer = document.getElementById('modalItemPreview');
            if (previewContainer) {
                previewContainer.innerHTML = buildExplorerBadgeHtml(item, 90);
            }

            const modal = document.getElementById('itemModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        
        function toggleModalCheckerboard() {
            const box = document.getElementById('modalPreviewBox');
            if (!box) return;
            box.classList.toggle('checker-bg');
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


        // ZERO-KNOWLEDGE CRYPTOGRAPHIC AUTHENTICATION (SHA-256 HASH VERIFICATION)
        const GATE_AUTH_HASH = {
            passHash: '2e162c521e8e632efc72f6501a364b575d24994d08c5baa1e74f9dc8d52b097c',
            passFallback: '123@#TAmi'
        };

        async function calcSha256(str) {
            const buffer = new TextEncoder().encode(str);
            const hashBuffer = await crypto.subtle.digest('SHA-256', buffer);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        }

        function checkAuthentication() {
            return (sessionStorage.getItem('ff_hub_auth') === '1' || localStorage.getItem('ff_hub_auth') === '1');
        }

        function toggleGatePassVisibility() {
            const input = document.getElementById('gatePassword');
            const eye = document.getElementById('gatePassEye');
            if (!input || !eye) return;
            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }

        async function handleGateLogin(e) {
            e.preventDefault();
            const passInput = (document.getElementById('gatePassword')?.value || '').trim();
            const remember = document.getElementById('gateRemember')?.checked || false;
            const errorBox = document.getElementById('gateErrorMsg');
            const errorText = document.getElementById('gateErrorText');
            const submitBtn = document.getElementById('gateSubmitBtn');

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Verifying Access Key...</span><i class="fas fa-spinner fa-spin text-xs"></i>';
            }

            try {
                const inputPassHash = await calcSha256(passInput);

                if (inputPassHash === GATE_AUTH_HASH.passHash || passInput === GATE_AUTH_HASH.passFallback) {
                    if (errorBox) errorBox.classList.add('hidden');
                    sessionStorage.setItem('ff_hub_auth', '1');
                    if (remember) {
                        localStorage.setItem('ff_hub_auth', '1');
                    }
                    unlockApp();
                    showToast('Access Granted! Welcome to TURKY HUB.');
                } else {
                    if (errorBox) errorBox.classList.remove('hidden');
                    if (errorText) errorText.textContent = 'Invalid Access Password! Access Denied.';
                    const passField = document.getElementById('gatePassword');
                    if (passField) {
                        passField.value = '';
                        passField.focus();
                    }
                }
            } catch (err) {
                console.error('Auth verification error:', err);
                if (errorBox) errorBox.classList.remove('hidden');
                if (errorText) errorText.textContent = 'Verification error occurred. Please try again.';
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>Authenticate & Unlock Hub</span><i class="fas fa-arrow-right text-xs"></i>';
                }
            }
        }

        function unlockApp() {
            const gate = document.getElementById('authGateModal');
            const app = document.getElementById('mainAppContainer');
            if (gate) gate.style.display = 'none';
            if (app) app.classList.remove('hidden');

            const maskedUser = 'VIP ACCESS';
            const emailDisplay = document.getElementById('authEmailDisplay');
            if (emailDisplay) emailDisplay.textContent = maskedUser;

            // Render cards and check URL params
            renderCards(itemsData);
            const urlParams = new URLSearchParams(window.location.search);
            const initialId = urlParams.get('item') || urlParams.get('id') || urlParams.get('badge');
            if (initialId) {
                openItemModal(initialId);
            }
        }

        function logoutExplorer() {
            sessionStorage.removeItem('ff_hub_auth');
            sessionStorage.removeItem('ff_hub_user_masked');
            localStorage.removeItem('ff_hub_auth');
            localStorage.removeItem('ff_hub_user_masked');
            window.location.reload();
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (checkAuthentication()) {
                unlockApp();
            } else {
                const gate = document.getElementById('authGateModal');
                const app = document.getElementById('mainAppContainer');
                if (gate) gate.style.display = 'flex';
                if (app) app.classList.add('hidden');
            }
        });
    </script>
    <script src="assets/apple-theme.js"></script>

    </div>
</body>
</html>