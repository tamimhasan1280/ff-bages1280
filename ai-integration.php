<?php
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
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts: Orbitron, Fira Code, Inter, Rajdhani -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;800;900&family=Fira+Code:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Authentic Prime Badges CSS -->
    <link rel="stylesheet" href="cdn/prime-badge.css">

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
</head>
<body class="min-h-screen flex flex-col cyber-grid selection:bg-emerald-500 selection:text-black">

<?php if (!$is_logged_in): ?>
    <!-- ADMIN AUTHENTICATION GATE -->
    <div class="fixed inset-0 z-50 bg-[#060814] flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-[#0a0d22]/95 border border-emerald-500/40 rounded-3xl p-8 shadow-2xl backdrop-blur-xl">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/40 flex items-center justify-center mb-3 shadow-[0_0_25px_rgba(16,185,129,0.2)]">
                    <i class="fas fa-robot text-2xl text-emerald-400"></i>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-mono text-[10px] uppercase font-bold tracking-wider mb-2">
                    <i class="fas fa-shield-halved text-[9px]"></i>
                    <span>Restricted Studio Access</span>
                </div>
                <h2 class="text-2xl font-black font-orbitron text-white cyber-text-glow">AI INTEGRATION</h2>
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
                    <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1.5 font-mono">
                        Email or Username
                    </label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                        <input type="text" name="username" required placeholder="Enter email or username..." class="w-full bg-[#04060e] border border-[#23294c] focus:border-emerald-400 rounded-xl pl-9 pr-4 py-3 text-xs text-white placeholder-gray-500 focus:outline-none transition font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1.5 font-mono">
                        Password
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                        <input type="password" name="password" required placeholder="••••••••••••" class="w-full bg-[#04060e] border border-[#23294c] focus:border-emerald-400 rounded-xl pl-9 pr-4 py-3 text-xs text-white placeholder-gray-500 focus:outline-none transition font-mono">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:from-emerald-600 hover:to-teal-600 text-black font-extrabold py-3.5 px-6 rounded-xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 transition transform active:scale-[0.98] uppercase tracking-wider text-xs flex items-center justify-center gap-2 cursor-pointer font-orbitron">
                    <span>Authenticate & Access AI Hub</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="index.php" class="text-xs text-gray-500 hover:text-gray-300 transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    <span>Back to FF Items Explorer</span>
                </a>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- TOP NAVIGATION BAR -->
    <header class="border-b border-emerald-500/20 bg-[#080b1c]/95 sticky top-0 z-40 backdrop-blur-md">
        <div class="max-w-[1780px] mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between gap-4">
            
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                    <i class="fas fa-robot text-black text-base"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold font-orbitron text-white text-base tracking-wider cyber-text-glow">TURKY HUB</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-400 text-black font-extrabold font-mono uppercase tracking-wider">
                            AI STUDIO
                        </span>
                    </div>
                    <p class="text-[11px] text-emerald-400/80 font-mono">OB55 Universal AI Integration Directive & SDK Guide</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="admin.php" class="px-3.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-bold flex items-center gap-2 transition">
                    <i class="fas fa-crown text-amber-400"></i>
                    <span>Admin Studio</span>
                </a>
                <a href="index.php" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white border border-white/10 text-xs font-semibold flex items-center gap-2 transition">
                    <i class="fas fa-eye text-cyan-400"></i>
                    <span>Open Live Explorer</span>
                </a>
                <a href="admin.php?action=logout" class="px-3.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-bold flex items-center gap-1.5 transition">
                    <i class="fas fa-power-off text-xs"></i>
                    <span>Logout</span>
                </a>
            </div>

        </div>
    </header>

    <main class="flex-1 max-w-[1780px] mx-auto px-4 sm:px-6 py-6 space-y-8 w-full">

        <!-- HERO BANNER -->
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#090d24] via-[#060a1c] to-[#040816] border border-emerald-500/30 p-6 sm:p-8 shadow-2xl">
            <!-- Ambient Glow -->
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div class="space-y-3 max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-mono font-bold tracking-wide">
                        <i class="fas fa-microchip"></i>
                        <span>UNIVERSAL AI INTEGRATION DIRECTIVE</span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black font-orbitron text-white tracking-wide cyber-text-glow">
                        AI INTEGRATION & MASTER PROMPT STUDIO
                    </h1>
                    <p class="text-sm text-gray-300 leading-relaxed">
                        Copy the single <strong class="text-emerald-400">Universal Master Prompt</strong> below and feed it into any AI system (<strong class="text-white">ChatGPT, Claude, Gemini, Cursor, Copilot, DeepSeek</strong>). It contains the complete technical logic, calling methods, 12-digit Badge ID matrix, authentic Prime Level (1-8) VFX rules, and headless CDN integration so any AI can build this into any website, app or game seamlessly.
                    </p>
                </div>

                <!-- Action Button: Copy Master Prompt -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <button type="button" onclick="copyMasterPrompt()" class="bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:from-emerald-600 hover:to-teal-600 text-black font-black px-6 py-4 rounded-2xl shadow-xl shadow-emerald-500/25 hover:shadow-emerald-500/40 transition transform active:scale-[0.98] font-orbitron text-xs sm:text-sm flex items-center justify-center gap-2.5 cursor-pointer">
                        <i class="fas fa-copy text-sm"></i>
                        <span>COPY MASTER AI PROMPT</span>
                    </button>
                    <a href="#quick-matrix" class="bg-[#0f142e] hover:bg-[#161c40] text-emerald-400 border border-emerald-500/30 font-bold px-5 py-4 rounded-2xl transition text-xs sm:text-sm flex items-center justify-center gap-2">
                        <i class="fas fa-table-list"></i>
                        <span>ID Matrix</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- NAVIGATION TABS -->
        <div class="flex flex-wrap items-center gap-2 border-b border-white/5 pb-3">
            <button type="button" onclick="switchTab('master-prompt')" id="tabBtn-master-prompt" class="tab-btn px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-500 text-black shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition cursor-pointer">
                <i class="fas fa-brain"></i>
                <span>Master AI System Prompt</span>
            </button>
            <button type="button" onclick="switchTab('web-integration')" id="tabBtn-web-integration" class="tab-btn px-4 py-2.5 rounded-xl text-xs font-semibold text-gray-400 hover:text-white hover:bg-white/5 border border-white/5 flex items-center gap-2 transition cursor-pointer">
                <i class="fab fa-js text-yellow-400"></i>
                <span>Web & React (HTML/JS)</span>
            </button>
            <button type="button" onclick="switchTab('mobile-integration')" id="tabBtn-mobile-integration" class="tab-btn px-4 py-2.5 rounded-xl text-xs font-semibold text-gray-400 hover:text-white hover:bg-white/5 border border-white/5 flex items-center gap-2 transition cursor-pointer">
                <i class="fas fa-mobile-screen-button text-cyan-400"></i>
                <span>Mobile Apps (Flutter & React Native)</span>
            </button>
            <button type="button" onclick="switchTab('backend-api')" id="tabBtn-backend-api" class="tab-btn px-4 py-2.5 rounded-xl text-xs font-semibold text-gray-400 hover:text-white hover:bg-white/5 border border-white/5 flex items-center gap-2 transition cursor-pointer">
                <i class="fas fa-server text-purple-400"></i>
                <span>Backend REST API (Node / Python / PHP)</span>
            </button>
            <button type="button" onclick="switchTab('id-matrix')" id="tabBtn-id-matrix" class="tab-btn px-4 py-2.5 rounded-xl text-xs font-semibold text-gray-400 hover:text-white hover:bg-white/5 border border-white/5 flex items-center gap-2 transition cursor-pointer">
                <i class="fas fa-fingerprint text-amber-400"></i>
                <span>12...80 Badge ID Matrix (28 Assets)</span>
            </button>
        </div>

        <!-- TAB CONTENT 1: MASTER AI SYSTEM PROMPT -->
        <section id="tab-master-prompt" class="tab-content space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span>
                    <h2 class="text-lg font-bold text-white font-orbitron cyber-text-glow">MASTER_AI_INTEGRATION_DIRECTIVE.md</h2>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Markdown Ready</span>
                </div>
                <button type="button" onclick="copyMasterPrompt()" class="text-xs font-mono text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition cursor-pointer">
                    <i class="fas fa-copy"></i>
                    <span>Copy Text</span>
                </button>
            </div>

            <!-- Cyberpunk Green Code Container -->
            <div class="relative cyber-code-block rounded-2xl p-5 sm:p-7 overflow-hidden">
                <div class="flex items-center justify-between border-b border-emerald-500/20 pb-3 mb-4 text-xs font-mono text-emerald-400/70">
                    <span>// UNIVERSAL AI SYSTEM PROMPT - INSTRUCTS CHATGPT / CLAUDE / GEMINI / CURSOR</span>
                    <span>OB55_PRIME_ENGINE_V3.2</span>
                </div>
                <pre id="masterPromptText" class="font-fira text-xs sm:text-sm text-emerald-400 leading-relaxed overflow-x-auto whitespace-pre-wrap select-all custom-scroll max-h-[700px]"># FREE FIRE BADGES & PRIME SYSTEM (OB55) - UNIVERSAL AI INTEGRATION DIRECTIVE

You are an expert full-stack developer integrating the official Free Fire Badges & Prime Level (1-8) System (OB55 Engine) into an application (Web, Mobile App, Game, or Backend).

## 1. ARCHITECTURE & ZERO-AUTH CDN
- **Public & Headless**: The CDN, REST API, images, and Web Component SDK are 100% public.
- **NO API KEY / NO CREDENTIALS REQUIRED**: Any client app (React, Vue, Flutter, HTML, Unity, iOS, Android, Node) can consume these assets without authentication headers or tokens.
- **Base Domain**: Set `<HUB_DOMAIN>` to the hosted URL (e.g., `https://tamimhasan1280.vercel.app`, `https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main`, or local host).

## 2. ASSET & ENDPOINT RESOLUTION
For any badge identified by its 12-digit ID:
1. **Direct PNG Image URL**:
   `<HUB_DOMAIN>/badges/{filename}`
   (Global CDN Fallback: `https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/badges/{filename}`)
2. **Badge Metadata JSON API**:
   `<HUB_DOMAIN>/api/v1/badge/{12_digit_id}.json`
3. **Full Badges Catalog API**:
   `<HUB_DOMAIN>/api/v1/badges.json`
4. **VFX Effects & Presets API**:
   `<HUB_DOMAIN>/api/effects.json`
5. **Web Component SDK & Stylesheet**:
   - JS SDK: `<HUB_DOMAIN>/cdn/ff-badges.js`
   - Authentic CSS VFX: `<HUB_DOMAIN>/cdn/prime-badge.css`

## 3. BADGE ID FORMAT & SYSTEM MATRIX
All Badge IDs are strictly 12 digits adhering to `^12\d{8}80$` (starts with `12`, ends with `80`).
When a developer or AI uses a 12-digit ID, it yields the exact authentic badge and VFX:

- **120000010880** / **126393684880** -> Free Fire Prime Level 8 (Mythic: Supreme Pinnacle Gold Ascending Energy Beam, 16-50 360° Gunpowder Sparkle Stars, Center Gem Glint, Multi-tier Aura).
- **120000010780** -> Free Fire Prime Level 7 (Legendary: Ascending Sheen, 12 Stars, Gem Glint).
- **120000010680** -> Free Fire Prime Level 6 (Epic: Diagonal Sheen, 8 Stars).
- **120000010580** -> Free Fire Prime Level 5 (Rare: Golden Sheen, 6 Stars).
- **120000010480** -> Free Fire Prime Level 4 (Special: Cyan Neon Sheen, 4 Cyan Stars).
- **120000010380** -> Free Fire Prime Level 3 (Uncommon: Subtle Energy).
- **120000010280** -> Free Fire Prime Level 2 (Subtle Glow).
- **120000010180** -> Free Fire Prime Level 1 (Starter Prime Badge).
- **120000000080** -> OFFICIAL V-BADGE (Verified Creator / KOL).
- **120000000180** -> FF Diamond Partner (Crystal VIP).
- **120000000280** -> FF Esports PRO Crown.
- **120000000380** -> FF Official Staff / GM.
- **120000000480** -> FF Craftland Creator.
- **120000000580** -> FF Emerging Creator.
- **120000000680** -> Classic Gold V-Badge.
- **120000000880** -> Moderator Shield Badge.
- **120000001180** -> HUD Alive Survivor Badge.
- **120000001280** -> HUD Kill Elimination Badge.
- **120000001480** -> Prime Aura V-Badge.

## 4. HOW TO RENDER & IMPLEMENT IN CODE

### Method A: 1-Line Web Component (Recommended for Web / React / Vue / HTML)
Add the stylesheet & SDK in `<head>`:
```html
<link rel="stylesheet" href="<HUB_DOMAIN>/cdn/prime-badge.css">
<script src="<HUB_DOMAIN>/cdn/ff-badges.js"></script>
```
Render anywhere using the 12-digit ID:
```html
<!-- Renders Prime 8 with authentic ascending energy beam, gem glint & scattered stars -->
<ff-badge id="120000010880" size="54px"></ff-badge>

<!-- Renders Official V-Badge next to player name -->
<div style="display:flex; align-items:center; gap:8px;">
    <span style="font-weight:bold; color:#fff;">Player_Name</span>
    <ff-badge id="120000000080" size="24px"></ff-badge>
</div>
```
No base code, custom CSS, or manual keyframes are required. The Web Component automatically fetches metadata, renders the 100% transparent PNG, injects directional sheen sweeps, and spawns the 360° scattered gunpowder stars.

### Method B: Pure React / Next.js Component
```tsx
import React, { useEffect, useState } from 'react';

interface FFBadgeProps {
  id: string; // 12-digit ID, e.g. "120000010880"
  size?: number; // size in px, default 54
  domain?: string; // your hosted domain
}

export const FFBadge: React.FC<FFBadgeProps> = ({ id, size = 54, domain = 'https://tamimhasan1280.vercel.app' }) => {
  const [badge, setBadge] = useState<any>(null);

  useEffect(() => {
    fetch(`${domain}/api/v1/badge/${id}.json`)
      .then(res => res.json())
      .then(data => setBadge(data.item))
      .catch(err => console.error("Badge load error:", err));
  }, [id, domain]);

  if (!badge) return <div style={{ width: size, height: size }} className="animate-pulse bg-white/5 rounded-full" />;

  const imgUrl = `${domain}/badges/${badge.file}`;
  return (
    <div className={`ff-prime-badge-wrap ff-prime-lvl-${badge.prime_level || 1}`} style={{ width: size, height: size, position: 'relative' }}>
      <img src={imgUrl} alt={badge.name} style={{ width: '100%', height: '100%', objectFit: 'contain' }} />
    </div>
  );
};
```

### Method C: Flutter (Mobile App - Android & iOS)
```dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class FFBadgeWidget extends StatelessWidget {
  final String badgeId; // e.g. '120000010880'
  final double size;
  final String domain;

  const FFBadgeWidget({
    Key? key,
    required this.badgeId,
    this.size = 54.0,
    this.domain = 'https://tamimhasan1280.vercel.app',
  }) : super(key: key);

  Future<Map<String, dynamic>> fetchBadge() async {
    final response = await http.get(Uri.parse('$domain/api/v1/badge/$badgeId.json'));
    if (response.statusCode == 200) {
      final json = jsonDecode(response.body);
      return json['item'];
    }
    throw Exception('Failed to load badge');
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<Map<String, dynamic>>(
      future: fetchBadge(),
      builder: (context, snapshot) {
        if (!snapshot.hasData) return SizedBox(width: size, height: size);
        final item = snapshot.data!;
        final imageUrl = '$domain/badges/${item['file']}';
        return Container(
          width: size,
          height: size,
          decoration: BoxDecoration(
            boxShadow: [
              BoxShadow(
                color: Color(int.parse((item['hex_glow'] ?? '#FFD700').replaceFirst('#', '0x88'))),
                blurRadius: 12.0,
                spreadRadius: 1.0,
              ),
            ],
          ),
          child: Image.network(imageUrl, fit: BoxFit.contain),
        );
      },
    );
  }
}
```

### Method D: Direct Lightweight Static HTML
```html
<img src="<HUB_DOMAIN>/badges/ff_ui_prime_icon_08.png" 
     alt="Prime 8" 
     style="width:54px; height:54px; filter: drop-shadow(0 0 10px rgba(255,215,0,0.85));">
```

## 5. AUTHENTICATION & ACCESS RULES
- The admin dashboard (`admin.php`) and hub explorer (`index.html`) require owner authentication (`tamimhasan1281@gmail.com`).
- **External API & CDN integration**: 100% unrestricted, free, CORS-enabled (`access-control-allow-origin: *`).
- To generate custom badge IDs or tweak VFX (stars, sheen direction, speed), use the Admin Studio at `<HUB_DOMAIN>/admin.php`.</pre>
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
                <pre id="snippetWebComponent" class="font-fira text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap select-all">&lt;!-- 1. Include Authentic Styles & SDK in &lt;head&gt; --&gt;
&lt;link rel="stylesheet" href="https://tamimhasan1280.vercel.app/cdn/prime-badge.css"&gt;
&lt;script src="https://tamimhasan1280.vercel.app/cdn/ff-badges.js"&gt;&lt;/script&gt;

&lt;!-- 2. Use the Custom Element with any 12-digit ID --&gt;
&lt;ff-badge id="120000010880" size="64px"&gt;&lt;/ff-badge&gt;
&lt;ff-badge id="120000000080" size="28px"&gt;&lt;/ff-badge&gt;</pre>
            </div>

            <!-- Snippet 2: React Component -->
            <div class="cyber-code-block rounded-2xl p-5 space-y-3">
                <div class="flex items-center justify-between text-xs font-mono text-emerald-400/80">
                    <span>2. React / Next.js Component (TypeScript)</span>
                    <button type="button" onclick="copySnippet('snippetReactComponent')" class="hover:text-emerald-300 cursor-pointer">
                        <i class="fas fa-copy mr-1"></i>Copy
                    </button>
                </div>
                <pre id="snippetReactComponent" class="font-fira text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap select-all">import React, { useEffect, useState } from 'react';

interface FFBadgeProps {
  id: string; // 12-digit ID, e.g. "120000010880"
  size?: number; // size in px
  domain?: string;
}

export const FFBadge: React.FC&lt;FFBadgeProps&gt; = ({
  id,
  size = 54,
  domain = 'https://tamimhasan1280.vercel.app'
}) => {
  const [item, setItem] = useState&lt;any&gt;(null);

  useEffect(() => {
    fetch(`${domain}/api/v1/badge/${id}.json`)
      .then(res => res.json())
      .then(data => setItem(data.item))
      .catch(console.error);
  }, [id, domain]);

  if (!item) return &lt;div style={{ width: size, height: size }} className="rounded-full bg-white/5 animate-pulse" /&gt;;

  const imgUrl = `${domain}/badges/${item.file}`;
  return (
    &lt;div className={`ff-prime-badge-wrap ff-prime-lvl-${item.prime_level || 1}`} style={{ width: size, height: size, position: 'relative' }}&gt;
      &lt;img src={imgUrl} alt={item.name} style={{ width: '100%', height: '100%', objectFit: 'contain' }} /&gt;
    &lt;/div&gt;
  );
};</pre>
            </div>
        </section>

        <!-- TAB CONTENT 3: MOBILE APPS INTEGRATION -->
        <section id="tab-mobile-integration" class="tab-content hidden space-y-6">
            <div class="space-y-2">
                <h2 class="text-xl font-bold text-white font-orbitron cyber-cyan-glow flex items-center gap-2">
                    <i class="fas fa-mobile-screen-button text-cyan-400"></i>
                    <span>Mobile Apps Integration Guide (Flutter & React Native)</span>
                </h2>
                <p class="text-xs text-gray-400">
                    Use Free Fire Badges & Prime Level badges in Android and iOS native mobile applications.
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
                <pre id="snippetFlutter" class="font-fira text-xs text-emerald-400 overflow-x-auto whitespace-pre-wrap select-all">import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

class FFBadgeWidget extends StatelessWidget {
  final String badgeId;
  final double size;
  final String domain;

  const FFBadgeWidget({
    Key? key,
    required this.badgeId,
    this.size = 54.0,
    this.domain = 'https://tamimhasan1280.vercel.app',
  }) : super(key: key);

  Future&lt;Map&lt;String, dynamic&gt;&gt; fetchBadge() async {
    final res = await http.get(Uri.parse('$domain/api/v1/badge/$badgeId.json'));
    if (res.statusCode == 200) {
      return jsonDecode(res.body)['item'];
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
        final imageUrl = '$domain/badges/${item['file']}';
        return Container(
          width: size,
          height: size,
          decoration: BoxDecoration(
            boxShadow: [
              BoxShadow(
                color: Color(int.parse((item['hex_glow'] ?? '#FFD700').replaceFirst('#', '0x88'))),
                blurRadius: 10.0,
              ),
            ],
          ),
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
  const domain = 'https://tamimhasan1280.vercel.app';
  const url = `${domain}/api/v1/badge/${badgeId}.json`;
  
  const response = await axios.get(url);
  const badge = response.data.item;
  
  return {
    id: badge.id,
    name: badge.name,
    primeLevel: badge.prime_level,
    imageUrl: `${domain}/badges/${badge.file}`,
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

def get_badge_info(badge_id: str, domain: str = 'https://tamimhasan1280.vercel.app'):
    url = f"{domain}/api/v1/badge/{badge_id}.json"
    res = requests.get(url)
    res.raise_for_status()
    data = res.json()["item"]
    return {
        "name": data["name"],
        "id": data["id"],
        "prime_level": data.get("prime_level"),
        "image_url": f"{domain}/badges/{data['file']}"
    }

# Example:
print(get_badge_info("120000010880"))</pre>
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
                                    $bFile = htmlspecialchars($b['file'] ?? 'ff_ui_prime_icon_08.png');
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
                                            <img src="badges/<?= $bFile ?>" onerror="this.src='<?= $bCdn ?>'" class="w-full h-full object-contain" alt="<?= htmlspecialchars($b['name']) ?>">
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

    <!-- TOAST NOTIFICATION COMPONENT -->
    <div id="aiToast" class="fixed bottom-6 right-6 z-50 bg-[#060a1c] border border-emerald-500/40 text-emerald-300 text-xs font-mono py-3 px-5 rounded-2xl shadow-2xl flex items-center gap-2 transform translate-y-20 opacity-0 pointer-events-none transition-all duration-300">
        <i class="fas fa-check-circle text-emerald-400"></i>
        <span id="aiToastMsg">Copied!</span>
    </div>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-emerald-500', 'text-black', 'shadow-lg', 'shadow-emerald-500/20');
                btn.classList.add('text-gray-400', 'hover:text-white', 'hover:bg-white/5');
            });

            const targetContent = document.getElementById('tab-' + tabId);
            const targetBtn = document.getElementById('tabBtn-' + tabId);

            if (targetContent) targetContent.classList.remove('hidden');
            if (targetBtn) {
                targetBtn.classList.add('bg-emerald-500', 'text-black', 'shadow-lg', 'shadow-emerald-500/20');
                targetBtn.classList.remove('text-gray-400', 'hover:text-white', 'hover:bg-white/5');
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

</body>
</html>
