<?php
// Security Hardening Headers
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

header('Connection: close');
/**
 * TURKY HUB - AI Integration & Master Prompt Studio
 * Universal integration instructions for ChatGPT, Claude, Gemini, Cursor & Copilot
 * OB55 Engine - Cyberpunk Terminal Interface
 */

session_start();

// Security / Authentication Check
$AUTH_EMAIL_HASH = 'e7e904116264a1174186582582ede731c7c9b4c084d0a0c225468552a6888179';
$AUTH_PASS_HASH = '2e162c521e8e632efc72f6501a364b575d24994d08c5baa1e74f9dc8d52b097c';
$ADMIN_USERS_FALLBACK = ['tamim1280'];
$ADMIN_PASS_FALLBACK = '123@#TAmi';

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');

    $u_clean = strtolower($u);
    $u_hash = hash('sha256', $u_clean);
    $p_hash = hash('sha256', $p);

    $is_authorized = (
        ($u_hash === $AUTH_EMAIL_HASH && $p_hash === $AUTH_PASS_HASH) ||
        (in_array($u_clean, $ADMIN_USERS_FALLBACK) && $p === $ADMIN_PASS_FALLBACK)
    );

    if ($is_authorized) {
        $_SESSION['ff_admin_logged'] = true;
        header('Location: ai-integration.php');
        exit;
    } else {
        $login_error = 'Invalid email/username or password! Access Denied.';
    }
}

$is_logged_in = !empty($_SESSION['ff_admin_logged']);

// Load Badges Data
$badges_data = json_decode(file_get_contents(__DIR__ . '/api/badges.json'), true);
$badges = $badges_data['badges'] ?? [];
$repo_name = 'tamimhasan1280/ff-bages1280';
$cdn_base = "https://cdn.jsdelivr.net/gh/{$repo_name}@main/badges/";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Integration Hub & Master Prompt Studio | TURKY HUB</title>
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
    <!-- Google Fonts: Orbitron, Fira Code, Inter, Rajdhani -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;800;900&family=Fira+Code:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Authentic Prime Badges CSS -->
    <link rel="stylesheet" href="cdn/prime-badge.css">
    <!-- Centralized Apple macOS Design System -->
    <link rel="stylesheet" href="assets/apple-theme.css">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #060814;
            color: #f1f5f9;
        }

        .font-orbitron { font-family: 'Orbitron', sans-serif; }
        .font-fira { font-family: 'Fira Code', monospace; }
        .font-rajdhani { font-family: 'Rajdhani', sans-serif; }

        /* Cyberpunk Glow Effects */
        .cyber-text-glow {
            text-shadow: 0 0 10px rgba(16, 185, 129, 0.45), 0 0 25px rgba(16, 185, 129, 0.25);
        }
        .cyber-amber-glow {
            text-shadow: 0 0 10px rgba(245, 158, 11, 0.45), 0 0 25px rgba(245, 158, 11, 0.25);
        }
        .cyber-cyan-glow {
            text-shadow: 0 0 10px rgba(6, 182, 212, 0.45);
        }

        /* Cyberpunk Terminal Code Styling */
        .cyber-code-block {
            background: #04060e;
            border: 1px solid rgba(16, 185, 129, 0.35);
            box-shadow: 0 0 35px rgba(16, 185, 129, 0.08), inset 0 0 20px rgba(0, 0, 0, 0.8);
            color: #10b981;
            font-family: 'Fira Code', monospace;
        }
        .cyber-code-block code {
            color: #34d399;
        }

        /* Scanline Overlay Effect */
        .cyber-grid {
            background-image: 
                linear-gradient(rgba(16, 185, 129, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(16, 185, 129, 0.03) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #080a18;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.3);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.6);
        }
    </style>
    <!-- Turky Hub Autonomous Security Guard -->
    <script>window.__TURKY_ADMIN__ = <?= $is_logged_in ? 'true' : 'false' ?>;</script>
    <script src="cdn/turky-guard.js"></script>
</head>
<body class="apple-ui min-h-screen flex flex-col selection:bg-red-500 selection:text-white">

<?php if (!$is_logged_in): ?>
    <!-- ADMIN AUTHENTICATION GATE -->
    <div class="apple-login-backdrop fixed inset-0 z-50 bg-[#09090B] flex items-center justify-center p-4">
        <button type="button" data-theme-toggle class="theme-toggle-btn fixed top-5 right-5 z-50" aria-label="Switch Theme" aria-pressed="false">
            <span class="theme-icon-wrap" aria-hidden="true">
                <i class="fas fa-sun icon-sun text-xs"></i>
                <i class="fas fa-moon icon-moon text-xs"></i>
            </span>
            <span data-theme-label>Dark</span>
        </button>

        <div class="apple-window relative w-full max-w-md overflow-hidden shadow-2xl">
            <div class="macos-window-bar">
                <div class="macos-traffic-lights" aria-hidden="true">
                    <span class="macos-dot macos-dot-red"></span>
                    <span class="macos-dot macos-dot-yellow"></span>
                    <span class="macos-dot macos-dot-green"></span>
                </div>
                <span class="macos-window-title">TURKY HUB — AI Integration</span>
                <span class="w-10" aria-hidden="true"></span>
            </div>

            <div class="p-8">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-14 h-14 rounded-2xl bg-red-500 flex items-center justify-center mb-3 shadow-lg shadow-red-500/20">
                    <i class="fas fa-robot text-2xl text-white apple-keep-white"></i>
                </div>
                <div class="apple-badge-red inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-mono text-[10px] uppercase font-bold tracking-wider mb-2">
                    <i class="fas fa-shield-halved text-[9px]"></i>
                    <span>Restricted Studio Access</span>
                </div>
                <h2 class="text-2xl font-bold text-white tracking-tight">AI INTEGRATION</h2>
                <p class="text-xs text-gray-400 mt-1">Please enter your authorized credentials to access the AI Integration Studio.</p>
            </div>

            <?php if (!empty($login_error)): ?>
                <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center gap-2">
                    <i class="fas fa-circle-exclamation shrink-0"></i>
                    <span><?= htmlspecialchars($login_error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="ai-integration.php" class="space-y-4">
                <input type="hidden" name="login_action" value="1">
                <div>
                    <label class="block text-xs font-semibold text-gray-300 tracking-wide mb-1.5">
                        Email or Username
                    </label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="username" required placeholder="Enter email or username..." class="w-full border rounded-xl pl-9 pr-4 py-3 text-xs placeholder-gray-500 focus:outline-none transition font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-300 tracking-wide mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="password" name="password" required placeholder="••••••••••••" class="w-full border rounded-xl pl-9 pr-4 py-3 text-xs placeholder-gray-500 focus:outline-none transition font-mono">
                    </div>
                </div>

                <button type="submit" class="apple-btn-primary apple-keep-white w-full py-3.5 px-6 uppercase tracking-wider text-xs flex items-center justify-center gap-2 cursor-pointer">
                    <span>Authenticate & Access AI Hub</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="index.php" class="text-xs text-gray-500 hover:text-red-500 transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    <span>Back to FF Items Explorer</span>
                </a>
            </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- TOP NAVIGATION BAR -->
    <header class="apple-navbar sticky top-0 z-40">
        <div class="max-w-[1780px] mx-auto px-4 sm:px-6 py-3 flex flex-wrap items-center justify-between gap-3">
            
            <div class="flex items-center gap-3 min-w-0">
                <div class="macos-traffic-lights hidden sm:inline-flex" aria-hidden="true">
                    <span class="macos-dot macos-dot-red"></span>
                    <span class="macos-dot macos-dot-yellow"></span>
                    <span class="macos-dot macos-dot-green"></span>
                </div>
                <div class="w-8 h-8 rounded-xl bg-red-500 flex items-center justify-center shadow-sm shrink-0">
                    <i class="fas fa-robot text-white apple-keep-white text-sm"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-white text-sm sm:text-base tracking-tight">TURKY HUB</span>
                        <span class="apple-badge-red text-[10px] px-2 py-0.5 rounded-full font-bold font-mono uppercase tracking-wider">
                            AI STUDIO
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-400 font-mono truncate hidden sm:block">OB55 Universal AI Integration Directive &amp; SDK Guide</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                <button type="button" data-theme-toggle class="theme-toggle-btn" aria-label="Switch Theme" aria-pressed="false">
                    <span class="theme-icon-wrap" aria-hidden="true">
                        <i class="fas fa-sun icon-sun text-xs"></i>
                        <i class="fas fa-moon icon-moon text-xs"></i>
                    </span>
                    <span data-theme-label class="hidden sm:inline">Dark</span>
                </button>
                <a href="admin.php" class="apple-btn-secondary px-3 sm:px-3.5 py-1.5 text-xs flex items-center gap-2">
                    <i class="fas fa-crown text-red-500"></i>
                    <span class="hidden sm:inline">Admin Studio</span>
                </a>
                <a href="index.php" target="_blank" class="apple-btn-secondary px-3 sm:px-3.5 py-1.5 text-xs flex items-center gap-2">
                    <i class="fas fa-eye text-red-500"></i>
                    <span class="hidden sm:inline">Open Live Explorer</span>
                </a>
                <a href="admin.php?action=logout" class="apple-btn-primary apple-keep-white px-3 sm:px-3.5 py-1.5 text-xs flex items-center gap-1.5">
                    <i class="fas fa-power-off text-xs"></i>
                    <span>Logout</span>
                </a>
            </div>

        </div>
    </header>

    <main class="flex-1 max-w-[1780px] mx-auto px-4 sm:px-6 py-6 space-y-8 w-full">

        <!-- HERO BANNER -->
        <section class="apple-window overflow-hidden">
            <div class="macos-window-bar">
                <div class="macos-traffic-lights" aria-hidden="true">
                    <span class="macos-dot macos-dot-red"></span>
                    <span class="macos-dot macos-dot-yellow"></span>
                    <span class="macos-dot macos-dot-green"></span>
                </div>
                <span class="macos-window-title">TURKY HUB — Universal AI Prompt Studio</span>
                <span class="text-[10px] font-mono text-gray-400">OB55 Engine</span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 p-6 sm:p-8">
                <div class="space-y-3 max-w-3xl">
                    <div class="apple-badge-red inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-bold tracking-wide">
                        <i class="fas fa-microchip"></i>
                        <span>UNIVERSAL AI INTEGRATION DIRECTIVE</span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-bold text-white tracking-tight">
                        AI INTEGRATION &amp; MASTER PROMPT STUDIO
                    </h1>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Copy the single <strong class="text-white">Universal Master Prompt</strong> below and feed it into any AI system (<strong class="text-white">ChatGPT, Claude, Gemini, Cursor, Copilot, DeepSeek</strong>). It contains the complete technical logic, calling methods, 12-digit Badge ID matrix, authentic Prime Level (1-8) VFX rules, and headless CDN integration.
                    </p>
                </div>

                <!-- Action Button: Copy Master Prompt -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                    <button type="button" onclick="copyMasterPrompt()" class="apple-btn-primary apple-keep-white px-6 py-3.5 text-xs sm:text-sm flex items-center justify-center gap-2.5 cursor-pointer">
                        <i class="fas fa-copy text-sm"></i>
                        <span>COPY MASTER AI PROMPT</span>
                    </button>
                    <button type="button" onclick="switchTab('id-matrix')" class="apple-btn-secondary px-5 py-3.5 text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fas fa-table-list text-red-500"></i>
                        <span>ID Matrix</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- NAVIGATION TABS -->
        <div class="flex flex-wrap items-center gap-1.5 apple-nav-pill w-fit">
            <button type="button" onclick="switchTab('master-prompt')" id="tabBtn-master-prompt" class="tab-btn apple-nav-item active px-4 py-2 text-xs flex items-center gap-2 cursor-pointer">
                <i class="fas fa-brain"></i>
                <span>Master AI System Prompt</span>
            </button>
            <button type="button" onclick="switchTab('web-integration')" id="tabBtn-web-integration" class="tab-btn apple-nav-item px-4 py-2 text-xs flex items-center gap-2 cursor-pointer">
                <i class="fab fa-js text-amber-500"></i>
                <span>Web &amp; React (HTML/JS)</span>
            </button>
            <button type="button" onclick="switchTab('mobile-integration')" id="tabBtn-mobile-integration" class="tab-btn apple-nav-item px-4 py-2 text-xs flex items-center gap-2 cursor-pointer">
                <i class="fas fa-mobile-screen-button text-cyan-500"></i>
                <span>Mobile Apps (Flutter &amp; React Native)</span>
            </button>
            <button type="button" onclick="switchTab('backend-api')" id="tabBtn-backend-api" class="tab-btn apple-nav-item px-4 py-2 text-xs flex items-center gap-2 cursor-pointer">
                <i class="fas fa-server text-purple-500"></i>
                <span>Backend REST API (Node / Python / PHP)</span>
            </button>
            <button type="button" onclick="switchTab('id-matrix')" id="tabBtn-id-matrix" class="tab-btn apple-nav-item px-4 py-2 text-xs flex items-center gap-2 cursor-pointer">
                <i class="fas fa-fingerprint text-red-500"></i>
                <span>12...80 Badge ID Matrix (<?= count($badges) ?> Assets)</span>
            </button>
        </div>

        <!-- TAB CONTENT 1: MASTER AI SYSTEM PROMPT -->
        <section id="tab-master-prompt" class="tab-content space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                    <h2 class="text-base sm:text-lg font-bold text-white">MASTER_AI_INTEGRATION_DIRECTIVE.md</h2>
                    <span class="apple-badge-red text-[10px] font-mono px-2 py-0.5 rounded-full font-semibold">Markdown Ready</span>
                </div>
                <button type="button" onclick="copyMasterPrompt()" class="apple-btn-secondary text-xs font-mono px-3.5 py-1.5 flex items-center gap-1.5 cursor-pointer">
                    <i class="fas fa-copy text-red-500"></i>
                    <span>Copy Text</span>
                </button>
            </div>

            <!-- macOS Code Container -->
            <div class="relative cyber-code-block rounded-2xl overflow-hidden">
                <div class="macos-window-bar">
                    <div class="macos-traffic-lights" aria-hidden="true">
                        <span class="macos-dot macos-dot-red"></span>
                        <span class="macos-dot macos-dot-yellow"></span>
                        <span class="macos-dot macos-dot-green"></span>
                    </div>
                    <span class="macos-window-title font-mono">// UNIVERSAL AI SYSTEM PROMPT — CHATGPT / CLAUDE / GEMINI / CURSOR</span>
                    <span class="text-[10px] font-mono text-gray-400">OB55_PRIME_ENGINE_V3.2</span>
                </div>
                <div class="p-4 sm:p-6">
                <pre id="masterPromptText" class="font-fira text-xs sm:text-sm text-emerald-400 leading-relaxed overflow-x-auto whitespace-pre-wrap select-all custom-scroll max-h-[700px]"># FREE FIRE BADGES &amp; PRIME SYSTEM (OB55) - UNIVERSAL AI INTEGRATION DIRECTIVE

You are an expert full-stack developer integrating the Free Fire Badges &amp; Prime Level (1-8) System (OB55 Engine) into an application (Web, React/Next.js, Vue, PHP, WordPress, Mobile App, Game UI, or Backend).

## 1. ARCHITECTURE &amp; LIVE ZERO-AUTH CDN
- **100% Public &amp; Headless CDN**: The SDK, Animated SVGs, PNG assets, CSS, and JSON APIs require **NO API key, NO auth headers, NO cookies**, and support browser CORS (`Access-Control-Allow-Origin: *`).
- **Primary Live CDN Base (`&lt;CDN_BASE&gt;`)**:
  `https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main`
- **Alternative Fallbacks**:
  - Statically: `https://cdn.statically.io/gh/tamimhasan1280/ff-bages1280/main`
  - GitHub Raw: `https://raw.githubusercontent.com/tamimhasan1280/ff-bages1280/main`
  - Local Dev / Self-Hosted Server: `http://localhost:8085` (replace with your live server origin when self-hosting PHP)

## 2. VERIFIED ENDPOINTS &amp; ASSET TYPES
Choose the right asset format for the target platform:
1. **Interactive Web Component JS SDK (Best for Web / React / Next.js / Vue / PHP)**:
   - Stylesheet (preload optional, SDK also auto-injects `#ff-badges-cdn-styles`):
     `&lt;CDN_BASE&gt;/cdn/prime-badge.css`
   - Master SDK (`&lt;ff-badge&gt;` Custom Element + `window.FFBadges` API):
     `&lt;CDN_BASE&gt;/cdn/ff-badges.js`
2. **Self-Contained Animated Transparent SVG (Zero JS, Works in plain `&lt;img&gt;` / React / Vue / Markdown / SVG Loaders)**:
   - `&lt;CDN_BASE&gt;/badges/svg/{12_digit_id}.svg`
   - Includes embedded animations (pulse, directional sheen beam, gem glint, 360deg gunpowder stars, bone lights) with **100% transparent background** and automatically reflects Admin ON/OFF toggles.
3. **Raw Transparent Cutout PNG (Static fallback / Unity / Mobile `Image.network`)**:
   - `&lt;CDN_BASE&gt;/badges/{file}` (Note: `svg_code` and `text_code` badges should use `&lt;ff-badge&gt;`, `FFBadges.render()`, or `/badges/svg/{id}.svg` instead of PNG).
4. **Badge Metadata REST API (JSON by 12-digit ID or slug)**:
   - Single Badge: `&lt;CDN_BASE&gt;/api/v1/badge/{12_digit_id_or_slug}.json` (reads `data.item`)
   - Full Catalog: `&lt;CDN_BASE&gt;/api/v1/badges.json` or `&lt;CDN_BASE&gt;/api/badges.json`
   - VFX Presets: `&lt;CDN_BASE&gt;/api/effects.json`
5. **Iframe Animated Player (Self-Hosted / Local PHP Server ONLY)**:
   - `http://localhost:8085/cdn/embed.html?id={12_digit_id}&amp;clean=1`
   - IMPORTANT: Do NOT point an `&lt;iframe&gt;` to `cdn.jsdelivr.net/.../embed.html` because jsDelivr serves `.html` with `Content-Type: text/plain`. On external sites without a self-hosted web origin, ALWAYS use `&lt;ff-badge&gt;`, `window.FFBadges.render()`, or `&lt;img src=".../badges/svg/{id}.svg"&gt;`.

## 3. LIVE BADGE ID MATRIX (<?= count($badges) ?> ACTIVE ASSETS, REGEX: `^12\d{8}80$`)
All Badge IDs strictly match `^12\d{8}80$` (or their `slug`). Never invent IDs; use only IDs below or fetch dynamically from `/api/v1/badges.json`:

<?php foreach ($badges as $idx => $b):
    $id = htmlspecialchars($b['id'] ?? '');
    $slug = htmlspecialchars($b['slug'] ?? '');
    $name = htmlspecialchars($b['name'] ?? '');
    $type = htmlspecialchars($b['type'] ?? 'image');
    $file = htmlspecialchars($b['file'] ?? '');
    $lvl = intval($b['prime_level'] ?? 0);
    $stars = intval($b['star_density'] ?? 0);
    $vfxParts = [];
    if ($lvl > 0) $vfxParts[] = "Prime Lvl {$lvl}";
    if (!empty($b['sweep_enabled'])) $vfxParts[] = "Sheen(" . htmlspecialchars($b['sweep_direction'] ?? 'diag_bl_tr') . ", " . htmlspecialchars($b['sweep_speed'] ?? '2.2s') . ")";
    if ($stars > 0) $vfxParts[] = "{$stars} Stars(" . htmlspecialchars($b['star_theme'] ?? 'gold') . ")";
    if (!empty($b['has_gem_glint'])) $vfxParts[] = "Gem Glint";
    if (!isset($b['pulse_enabled']) || !empty($b['pulse_enabled'])) $vfxParts[] = "Pulse";
    if (!isset($b['glow_enabled']) || !empty($b['glow_enabled'])) $vfxParts[] = "Glow";
    $vfxSummary = !empty($vfxParts) ? implode(', ', $vfxParts) : 'Static Clean';
    echo "- **{$id}** (`{$slug}`) -&gt; {$name} | type=`{$type}` | file=`{$file}` | VFX: {$vfxSummary}\n";
endforeach; ?>

## 4. COMPLETE INTEGRATION RECIPES BY PLATFORM

### Method A: 1-Line Universal Web Component (Vanilla HTML / PHP / Laravel / WordPress / Vue / Svelte)
Include ONCE in `&lt;head&gt;` or before `&lt;/body&gt;`:
```html
&lt;link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/cdn/prime-badge.css"&gt;
&lt;script src="https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/cdn/ff-badges.js"&gt;&lt;/script&gt;
```
Render any badge anywhere (supports ID or slug, 100% transparent, all Admin VFX synced):
```html
&lt;!-- Live Admin-synced Prime Level 8 badge --&gt;
&lt;ff-badge id="126393684880" size="54px"&gt;&lt;/ff-badge&gt;

&lt;!-- Verified V-Badge inline next to username --&gt;
&lt;div style="display:inline-flex; align-items:center; gap:6px;"&gt;
  &lt;span style="font-weight:700;"&gt;Player_Name&lt;/span&gt;
  &lt;ff-badge id="120000000080" size="24px"&gt;&lt;/ff-badge&gt;
&lt;/div&gt;

&lt;!-- Optional per-instance overrides: anim="off" | pulse="off" | glow="off" | speed="2.2s" | position="inline|top-right|top-left|bottom-right|bottom-left" --&gt;
&lt;ff-badge id="120000000080" size="28px" anim="off" glow="off"&gt;&lt;/ff-badge&gt;
```
JavaScript SDK methods exposed on `window.FFBadges`:
- `FFBadges.render(idOrSlug, { size: 54, position: 'inline', speed: '2.2s', anim: true, pulse: true, glow: true })` -&gt; returns HTML string
- `FFBadges.attachTo('#avatar-wrap', '126393684880', { size: 28, position: 'top-right' })` -&gt; mounts directly into container
- `FFBadges.get(idOrSlug)` / `FFBadges.list()` -&gt; inspect bundled badge metadata synchronously

### Method B: Zero-JS Self-Contained Animated SVG (Universal Safe Fallback / React / Emails / Markdown)
Works inside any `&lt;img&gt;` tag without loading any JavaScript, keeping 100% transparency &amp; live Admin VFX:
```html
&lt;img src="https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/badges/svg/126393684880.svg"
     alt="Free Fire Prime Level 8"
     width="54" height="54"
     style="display:inline-block; vertical-align:middle; background:transparent;" /&gt;
```

### Method C: React / Next.js (Client Component with Full VFX + Zero SSR Hydration Errors)
Do NOT put raw PNG inside `.ff-prime-badge-wrap` expecting JS/CSS layers to magically appear. Instead, render via `window.FFBadges.render(...)` (after loading `ff-badges.js` on the client) OR use the animated SVG `&lt;img&gt;`:
```tsx
'use client';
import React, { useEffect, useRef, useState } from 'react';

const CDN = 'https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main';

interface FFBadgeProps {
  id: string; // 12-digit ID or slug, e.g. "126393684880" or "official-v"
  size?: number;
  anim?: boolean;
  pulse?: boolean;
  glow?: boolean;
}

export const FFBadge: React.FC&lt;FFBadgeProps&gt; = ({ id, size = 54, anim, pulse, glow }) =&gt; {
  const hostRef = useRef&lt;HTMLSpanElement&gt;(null);
  const [ready, setReady] = useState(false);

  useEffect(() =&gt; {
    if ((window as any).FFBadges) { setReady(true); return; }
    if (!document.querySelector('script[data-ff-badges-sdk]')) {
      const s = document.createElement('script');
      s.src = `${CDN}/cdn/ff-badges.js`;
      s.async = true;
      s.dataset.ffBadgesSdk = '1';
      s.onload = () =&gt; setReady(true);
      document.head.appendChild(s);
    } else {
      const t = setInterval(() =&gt; {
        if ((window as any).FFBadges) { setReady(true); clearInterval(t); }
      }, 60);
      return () =&gt; clearInterval(t);
    }
  }, []);

  useEffect(() =&gt; {
    if (ready &amp;&amp; hostRef.current &amp;&amp; (window as any).FFBadges) {
      hostRef.current.innerHTML = (window as any).FFBadges.render(id, { size, anim, pulse, glow });
    }
  }, [ready, id, size, anim, pulse, glow]);

  if (!ready) {
    return (
      &lt;img
        src={`${CDN}/badges/svg/${encodeURIComponent(id)}.svg`}
        alt={`FF Badge ${id}`}
        width={size}
        height={size}
        style={{ display: 'inline-block', verticalAlign: 'middle', background: 'transparent' }}
      /&gt;
    );
  }
  return &lt;span ref={hostRef} style={{ display: 'inline-flex', verticalAlign: 'middle' }} /&gt;;
};
```

### Method D: Flutter (Android &amp; iOS)
Note: `Image.network` with `.png` renders a static transparent cutout. For full animated SVG in Flutter, use `flutter_svg` (`SvgPicture.network(item['cdn_urls']['animated_svg'])`), or `Image.network` for lightweight transparent PNG:
```dart
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

class FFBadgeWidget extends StatelessWidget {
  final String badgeId;
  final double size;
  final String cdnBase;

  const FFBadgeWidget({
    Key? key,
    required this.badgeId,
    this.size = 54.0,
    this.cdnBase = 'https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main',
  }) : super(key: key);

  Color _parseHexGlow(String? hex) {
    final clean = (hex ?? '#FFD700').replaceAll('#', '');
    final full = clean.length == 3 ? clean.split('').map((c) =&gt; '$c$c').join() : clean;
    return Color(int.tryParse('88$full', radix: 16) ?? 0x88FFD700);
  }

  Future&lt;Map&lt;String, dynamic&gt;&gt; fetchBadge() async {
    final res = await http.get(Uri.parse('$cdnBase/api/v1/badge/$badgeId.json'));
    if (res.statusCode == 200) {
      return Map&lt;String, dynamic&gt;.from(jsonDecode(res.body)['item']);
    }
    throw Exception('Failed to load badge $badgeId');
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder&lt;Map&lt;String, dynamic&gt;&gt;(
      future: fetchBadge(),
      builder: (context, snapshot) {
        if (!snapshot.hasData) return SizedBox(width: size, height: size);
        final item = snapshot.data!;
        final glowEnabled = item['glow_enabled'] != false;
        final pngUrl = item['cdn_urls']?['jsdelivr'] ?? '$cdnBase/badges/${item['file']}';
        return Container(
          width: size,
          height: size,
          decoration: glowEnabled
              ? BoxDecoration(
                  boxShadow: [
                    BoxShadow(
                      color: _parseHexGlow(item['hex_glow']?.toString()),
                      blurRadius: 12.0,
                      spreadRadius: 1.0,
                    ),
                  ],
                )
              : null,
          child: Image.network(pngUrl, fit: BoxFit.contain),
        );
      },
    );
  }
}
```

### Method E: Dynamic Catalog Fetching (Always Respects Future Badges Added in Admin)
If the integrating project renders a dynamic list, badge picker, or user-selected ID, fetch `&lt;CDN_BASE&gt;/api/v1/badges.json` or `&lt;CDN_BASE&gt;/api/v1/badge/{id}.json` rather than hardcoding metadata. Every badge JSON returns `sweep_enabled`, `pulse_enabled`, `glow_enabled`, `has_gem_glint`, `star_density`, `direct_url`, and `cdn_urls`.

## 5. SECURITY, AUTH &amp; ADMIN STUDIO SYNC
- **Public Headless Delivery**: `/cdn/*`, `/api/*`, and `/badges/*` on jsDelivr are completely public, free, and CORS-enabled (`*`).
- **Admin-Only Management**: Managing badges (`admin.php`) and accessing this AI Hub (`ai-integration.php`) require Admin authentication. Saving or batch-updating in Admin triggers `generate_all.php`, pushes to GitHub `main`, and purges jsDelivr automatically so `/cdn/ff-badges.js`, `/badges/svg/{id}.svg`, and `/api/v1/badge/{id}.json` stay 100% synced.
- **Anti-Tamper Note**: Do NOT inject `cdn/turky-guard.js` into third-party consumer apps unless explicit right-click/DevTools blocking is desired (it disables context menu, DevTools shortcuts, and blurs on focus loss for non-admin users).</pre>
                </div>
            </div>
        </section>

        <!-- TAB CONTENT 2: WEB & REACT INTEGRATION -->
        <section id="tab-web-integration" class="tab-content hidden space-y-6">
            <div class="space-y-2">
                <h2 class="text-xl font-bold text-white font-orbitron cyber-text-glow flex items-center gap-2">
                    <i class="fab fa-js text-yellow-400"></i>
                    <span>Web & React Integration Guide</span>
                </h2>
                <p class="text-xs text-gray-400">
                    The easiest method to display Free Fire Badges & Prime VFX on any website, single-page application (React, Next.js, Vue, Svelte) or HTML landing page.
                </p>
            </div>

            <!-- Snippet 1: Web Component -->
            <div class="cyber-code-block rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between text-xs font-mono text-emerald-400/80">
                    <span>1. HTML / Vanilla JS (Universal Web Component)</span>
                    <button type="button" onclick="copySnippet('snippetWebComponent')" class="hover:text-emerald-300 cursor-pointer">
                        <i class="fas fa-copy mr-1"></i>Copy
                    </button>
                </div>
                <pre id="snippetWebComponent" class="font-fira text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap select-all">&lt;!-- 1. Include Authentic Styles &amp; SDK in &lt;head&gt; --&gt;
&lt;link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/cdn/prime-badge.css"&gt;
&lt;script src="https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/cdn/ff-badges.js"&gt;&lt;/script&gt;

&lt;!-- 2. Use the Custom Element with any 12-digit ID --&gt;
&lt;ff-badge id="126393684880" size="64px"&gt;&lt;/ff-badge&gt;
&lt;ff-badge id="120000000080" size="28px"&gt;&lt;/ff-badge&gt;

&lt;!-- 3. Or Zero-JS Self-Contained Animated SVG --&gt;
&lt;img src="https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/badges/svg/120000000080.svg" width="28" height="28" alt="Official V Badge" style="background:transparent;" /&gt;</pre>
            </div>

            <!-- Snippet 2: React Component -->
            <div class="cyber-code-block rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between text-xs font-mono text-emerald-400/80">
                    <span>2. React / Next.js Client Component (TypeScript, Full VFX + Zero Hydration Errors)</span>
                    <button type="button" onclick="copySnippet('snippetReactComponent')" class="hover:text-emerald-300 cursor-pointer">
                        <i class="fas fa-copy mr-1"></i>Copy
                    </button>
                </div>
                <pre id="snippetReactComponent" class="font-fira text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap select-all">'use client';
import React, { useEffect, useRef, useState } from 'react';

const CDN = 'https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main';

interface FFBadgeProps {
  id: string; // 12-digit ID or slug, e.g. "126393684880"
  size?: number;
  anim?: boolean;
  pulse?: boolean;
  glow?: boolean;
}

export const FFBadge: React.FC&lt;FFBadgeProps&gt; = ({ id, size = 54, anim, pulse, glow }) =&gt; {
  const hostRef = useRef&lt;HTMLSpanElement&gt;(null);
  const [ready, setReady] = useState(false);

  useEffect(() =&gt; {
    if ((window as any).FFBadges) { setReady(true); return; }
    if (!document.querySelector('script[data-ff-badges-sdk]')) {
      const s = document.createElement('script');
      s.src = `${CDN}/cdn/ff-badges.js`;
      s.async = true;
      s.dataset.ffBadgesSdk = '1';
      s.onload = () =&gt; setReady(true);
      document.head.appendChild(s);
    }
  }, []);

  useEffect(() =&gt; {
    if (ready &amp;&amp; hostRef.current &amp;&amp; (window as any).FFBadges) {
      hostRef.current.innerHTML = (window as any).FFBadges.render(id, { size, anim, pulse, glow });
    }
  }, [ready, id, size, anim, pulse, glow]);

  if (!ready) {
    return &lt;img src={`${CDN}/badges/svg/${encodeURIComponent(id)}.svg`} alt={`Badge ${id}`} width={size} height={size} style={{ background: 'transparent' }} /&gt;;
  }
  return &lt;span ref={hostRef} style={{ display: 'inline-flex', verticalAlign: 'middle' }} /&gt;;
};</pre>
            </div>
        </section>

        <!-- TAB CONTENT 3: MOBILE APPS INTEGRATION -->
        <section id="tab-mobile-integration" class="tab-content hidden space-y-6">
            <div class="space-y-2">
                <h2 class="text-xl font-bold text-white font-orbitron cyber-cyan-glow flex items-center gap-2">
                    <i class="fas fa-mobile-screen-button text-cyan-400"></i>
                    <span>Mobile Apps Integration Guide (Flutter &amp; React Native)</span>
                </h2>
                <p class="text-xs text-gray-400">
                    Use Free Fire Badges &amp; Prime Level badges in Android and iOS native mobile applications.
                </p>
            </div>

            <!-- Snippet: Flutter -->
            <div class="cyber-code-block rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between text-xs font-mono text-emerald-400/80">
                    <span>1. Flutter Dart Widget</span>
                    <button type="button" onclick="copySnippet('snippetFlutter')" class="hover:text-emerald-300 cursor-pointer">
                        <i class="fas fa-copy mr-1"></i>Copy
                    </button>
                </div>
                <pre id="snippetFlutter" class="font-fira text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap select-all">import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

class FFBadgeWidget extends StatelessWidget {
  final String badgeId;
  final double size;
  final String cdnBase;

  const FFBadgeWidget({
    Key? key,
    required this.badgeId,
    this.size = 54.0,
    this.cdnBase = 'https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main',
  }) : super(key: key);

  Color _parseHexGlow(String? hex) {
    final clean = (hex ?? '#FFD700').replaceAll('#', '');
    final full = clean.length == 3 ? clean.split('').map((c) =&gt; '$c$c').join() : clean;
    return Color(int.tryParse('88$full', radix: 16) ?? 0x88FFD700);
  }

  Future&lt;Map&lt;String, dynamic&gt;&gt; fetchBadge() async {
    final res = await http.get(Uri.parse('$cdnBase/api/v1/badge/$badgeId.json'));
    if (res.statusCode == 200) {
      return Map&lt;String, dynamic&gt;.from(jsonDecode(res.body)['item']);
    }
    throw Exception('Failed to load badge');
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder&lt;Map&lt;String, dynamic&gt;&gt;(
      future: fetchBadge(),
      builder: (context, snapshot) {
        if (!snapshot.hasData) return SizedBox(width: size, height: size);
        final item = snapshot.data!;
        final glowEnabled = item['glow_enabled'] != false;
        final imageUrl = item['cdn_urls']?['jsdelivr'] ?? '$cdnBase/badges/${item['file']}';
        return Container(
          width: size,
          height: size,
          decoration: glowEnabled
              ? BoxDecoration(
                  boxShadow: [
                    BoxShadow(
                      color: _parseHexGlow(item['hex_glow']?.toString()),
                      blurRadius: 10.0,
                    ),
                  ],
                )
              : null,
          child: Image.network(imageUrl, fit: BoxFit.contain),
        );
      },
    );
  }
}</pre>
            </div>
        </section>

        <!-- TAB CONTENT 4: BACKEND REST APIS -->
        <section id="tab-backend-api" class="tab-content hidden space-y-6">
            <div class="space-y-2">
                <h2 class="text-xl font-bold text-white font-orbitron cyber-text-glow flex items-center gap-2">
                    <i class="fas fa-server text-purple-400"></i>
                    <span>Backend REST API Integration (Node / Python / PHP)</span>
                </h2>
                <p class="text-xs text-gray-400">
                    Fetch badge metadata, verify 12-digit IDs, or resolve asset URLs programmatically.
                </p>
            </div>

            <!-- Snippet: Node.js -->
            <div class="cyber-code-block rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between text-xs font-mono text-emerald-400/80">
                    <span>1. Node.js / Express</span>
                    <button type="button" onclick="copySnippet('snippetNode')" class="hover:text-emerald-300 cursor-pointer">
                        <i class="fas fa-copy mr-1"></i>Copy
                    </button>
                </div>
                <pre id="snippetNode" class="font-fira text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap select-all">const axios = require('axios');

async function getBadge(badgeId) {
  const cdnBase = 'https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main';
  const url = `${cdnBase}/api/v1/badge/${badgeId}.json`;

  const response = await axios.get(url);
  const badge = response.data.item;

  return {
    id: badge.id,
    name: badge.name,
    primeLevel: badge.prime_level,
    imageUrl: `${cdnBase}/badges/${badge.file}`,
    animatedSvgUrl: badge.cdn_urls.animated_svg,
    cdnUrl: badge.cdn_urls.jsdelivr
  };
}</pre>
            </div>

            <!-- Snippet: Python -->
            <div class="cyber-code-block rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between text-xs font-mono text-emerald-400/80">
                    <span>2. Python (Requests)</span>
                    <button type="button" onclick="copySnippet('snippetPython')" class="hover:text-emerald-300 cursor-pointer">
                        <i class="fas fa-copy mr-1"></i>Copy
                    </button>
                </div>
                <pre id="snippetPython" class="font-fira text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap select-all">import requests

def get_badge_info(badge_id: str, cdn_base: str = 'https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main'):
    url = f"{cdn_base}/api/v1/badge/{badge_id}.json"
    res = requests.get(url, timeout=10)
    res.raise_for_status()
    data = res.json()["item"]
    return {
        "name": data["name"],
        "id": data["id"],
        "prime_level": data.get("prime_level"),
        "image_url": f"{cdn_base}/badges/{data['file']}",
        "animated_svg_url": data.get("cdn_urls", {}).get("animated_svg")
    }

# Example:
print(get_badge_info("126393684880"))</pre>
            </div>
        </section>

        <!-- TAB CONTENT 5: 12...80 BADGE ID MATRIX -->
        <section id="tab-id-matrix" class="tab-content hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h2 class="text-xl font-bold text-white font-orbitron cyber-amber-glow flex items-center gap-2">
                        <i class="fas fa-fingerprint text-amber-400"></i>
                        <span>12...80 Badge ID Matrix & Cheat Sheet</span>
                    </h2>
                    <p class="text-xs text-gray-400">
                        Total <?= count($badges) ?> badges available. Click any ID or CDN URL to copy instantly for your AI prompt or app integration.
                    </p>
                </div>
                <input type="text" id="matrixSearchInput" oninput="filterMatrixTable(this.value)" placeholder="Search by name, ID or level..." class="bg-[#04060e] border border-emerald-500/30 rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-emerald-400 w-full sm:w-72 font-mono">
            </div>

            <!-- Table -->
            <div class="cyber-code-block rounded-2xl overflow-hidden border border-emerald-500/30">
                <div class="overflow-x-auto custom-scroll">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-[#080d22] text-emerald-400/80 border-b border-emerald-500/20 font-mono uppercase text-[11px]">
                                <th class="py-3 px-4">Preview</th>
                                <th class="py-3 px-4">12-Digit Badge ID</th>
                                <th class="py-3 px-4">Badge Name</th>
                                <th class="py-3 px-4">Level / Category</th>
                                <th class="py-3 px-4">VFX Attributes</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="matrixTableBody" class="divide-y divide-emerald-500/10 font-mono">
                            <?php foreach ($badges as $b): ?>
                                <?php
                                    $rawBFile = trim($b['file'] ?? '');
                                    $bFile = (!empty($rawBFile)) ? htmlspecialchars($rawBFile) : 'FF_UI_Badge_KOL01.png';
                                    $bLvl = $b['prime_level'] ?? 0;
                                    $bStars = intval($b['star_density'] ?? ($bLvl === 8 ? 16 : ($bLvl === 7 ? 12 : ($bLvl === 6 ? 8 : ($bLvl === 5 ? 6 : ($bLvl === 4 ? 4 : 0))))));
                                    $bSweep = !empty($b['sweep_enabled']) || ($bLvl >= 4);
                                    $bGlint = !empty($b['has_gem_glint']) || ($bLvl >= 7);
                                    $bCdn = $cdn_base . $bFile;
                                ?>
                                <tr class="hover:bg-emerald-500/[0.04] transition matrix-row" data-search="<?= strtolower($b['name'] . ' ' . $b['id'] . ' ' . ($b['prime_level'] ?? '') . ' ' . $b['category']) ?>">
                                    <!-- Preview -->
                                    <td class="py-3 px-4">
                                        <div class="w-10 h-10 rounded-xl bg-[#080a18] border border-emerald-500/20 flex items-center justify-center p-1 relative">
                                            <img src="badges/<?= $bFile ?>" onerror="this.onerror=null; this.src='<?= $bCdn ?>'" class="w-full h-full object-contain" alt="<?= htmlspecialchars($b['name']) ?>">
                                        </div>
                                    </td>
                                    <!-- ID -->
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-emerald-400 font-fira">#<?= $b['id'] ?></span>
                                            <button type="button" onclick="copyText('<?= $b['id'] ?>', 'Badge ID copied!')" class="text-gray-500 hover:text-emerald-300 cursor-pointer" title="Copy ID">
                                                <i class="fas fa-copy text-[10px]"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <!-- Name -->
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-white"><?= htmlspecialchars($b['name']) ?></div>
                                        <div class="text-[10px] text-gray-500"><?= $bFile ?></div>
                                    </td>
                                    <!-- Level / Category -->
                                    <td class="py-3 px-4">
                                        <?php if (!empty($b['prime_level'])): ?>
                                            <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-bold">
                                                Prime Lvl <?= $b['prime_level'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[10px]">
                                                <?= htmlspecialchars($b['category']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <!-- VFX Attributes -->
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-1.5 flex-wrap text-[10px]">
                                            <?php if ($bSweep): ?>
                                                <span class="px-1.5 py-0.5 bg-emerald-500/10 text-emerald-400 rounded border border-emerald-500/20">Sheen</span>
                                            <?php endif; ?>
                                            <?php if ($bStars > 0): ?>
                                                <span class="px-1.5 py-0.5 bg-amber-500/10 text-amber-400 rounded border border-amber-500/20"><?= $bStars ?> Stars</span>
                                            <?php endif; ?>
                                            <?php if ($bGlint): ?>
                                                <span class="px-1.5 py-0.5 bg-cyan-500/10 text-cyan-400 rounded border border-cyan-500/20">Gem Glint</span>
                                            <?php endif; ?>
                                            <?php if (!$bSweep && $bStars === 0 && !$bGlint): ?>
                                                <span class="px-1.5 py-0.5 bg-white/5 text-gray-400 rounded">Static Clean</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <!-- Actions -->
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" onclick="copyText('<?= $b['id'] ?>', 'ID copied!')" class="px-2.5 py-1 rounded bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] cursor-pointer">
                                                Copy ID
                                            </button>
                                            <button type="button" onclick="copyText('&lt;ff-badge id=&quot;<?= $b['id'] ?>&quot; size=&quot;54px&quot;&gt;&lt;/ff-badge&gt;', 'Tag copied!')" class="px-2.5 py-1 rounded bg-white/5 hover:bg-white/10 text-gray-300 text-[11px] cursor-pointer">
                                                Tag
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    <!-- Understated Apple Footer -->
    <footer class="apple-footer mt-auto">
        <div class="max-w-[1780px] mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5">
                <div class="macos-traffic-lights" aria-hidden="true">
                    <span class="macos-dot macos-dot-red"></span>
                    <span class="macos-dot macos-dot-yellow"></span>
                    <span class="macos-dot macos-dot-green"></span>
                </div>
                <span class="font-semibold text-white">TURKY HUB AI Studio</span>
                <span>· Headless Badge SDK</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="index.php" class="hover:text-red-500 transition">Explorer</a>
                <a href="admin.php" class="hover:text-red-500 transition">Admin Studio</a>
            </div>
        </div>
    </footer>

    <!-- TOAST NOTIFICATION COMPONENT -->
    <div id="aiToast" class="fixed bottom-6 right-6 z-50 bg-[#060a1c] border border-emerald-500/40 text-emerald-300 text-xs font-mono py-3 px-5 rounded-2xl shadow-2xl flex items-center gap-2 transform translate-y-20 opacity-0 pointer-events-none transition-all duration-300">
        <i class="fas fa-check-circle text-emerald-400"></i>
        <span id="aiToastMsg">Copied!</span>
    </div>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            const targetContent = document.getElementById('tab-' + tabId);
            const targetBtn = document.getElementById('tabBtn-' + tabId);

            if (targetContent) targetContent.classList.remove('hidden');
            if (targetBtn) {
                targetBtn.classList.add('active');
            }
        }

        function copyMasterPrompt() {
            const promptText = document.getElementById('masterPromptText').textContent;
            copyText(promptText, 'Master AI Prompt Copied to Clipboard!');
        }

        function copySnippet(elementId) {
            const text = document.getElementById(elementId).textContent;
            copyText(text, 'Code Snippet Copied!');
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
            const toast = document.getElementById('aiToast');
            const toastMsg = document.getElementById('aiToastMsg');
            toastMsg.textContent = msg;
            toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            }, 2500);
        }

        function filterMatrixTable(query) {
            const q = query.toLowerCase().trim();
            const rows = document.querySelectorAll('.matrix-row');
            rows.forEach(r => {
                const searchData = r.getAttribute('data-search') || '';
                if (!q || searchData.includes(q)) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });
        }
    </script>

<?php endif; ?>
<script src="assets/apple-theme.js"></script>
</body>
</html>
