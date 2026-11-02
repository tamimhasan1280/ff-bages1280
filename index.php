<?php
/**
 * Free Fire V-Badges Master Monster CDN & Functional Logic Hub v2.0
 * GitHub Repository: https://github.com/tamimhasan1280/ff-bages1280
 * Global CDN Base: https://cdn.jsdelivr.net/gh/tamimhasan1280/ff-bages1280@main/badges/
 */

$repo_name = 'tamimhasan1280/ff-bages1280';
$cdn_base = 'https://cdn.jsdelivr.net/gh/' . $repo_name . '@main/badges/';
$raw_base = 'https://raw.githubusercontent.com/' . $repo_name . '/main/badges/';
$statically_base = 'https://cdn.statically.io/gh/' . $repo_name . '/main/badges/';

// Helper function to generate clean badge image URLs
function ff_badge_cdn_url($file) {
    global $cdn_base;
    if (empty($file)) return '';
    if (preg_match('/^(https?:|\/\/|data:)/i', $file)) return $file;
    return $cdn_base . $file;
}

// Master Badge Dataset
$master_badges = [
    [
        'id' => 'official-v-badge',
        'db_id' => 2,
        'slug' => 'official-v',
        'name' => 'OFFICIAL V BAGE',
        'title' => 'Official Influencer V-Badge',
        'category' => 'Official Partner (KOL)',
        'category_slug' => 'official',
        'file' => 'FF_UI_Badge_KOL01.png',
        'glow_color' => 'rgba(255, 215, 0, 0.85)',
        'hex_glow' => '#FFD700',
        'priority' => 2000000,
        'default_size' => 34,
        'rarity' => 'Mythic Partner',
        'description' => 'The authentic Free Fire partner influencer verification badge with gold light-sweep beam.',
        'type' => 'image',
        'fn_call' => "generate_ff_badge_html('FF_UI_Badge_KOL01.png', 'OFFICIAL V BAGE', 'rgba(255, 215, 0, 0.85)', 'top-right', '34px');"
    ],
    [
        'id' => 'diamond-partner',
        'db_id' => 25,
        'slug' => 'diamond-partner',
        'name' => 'FF Diamond Partner',
        'title' => 'Diamond Partner / Elite VIP',
        'category' => 'Crystal VIP Partner',
        'category_slug' => 'vip',
        'file' => 'FF_UI_Badge_KOL03.png',
        'glow_color' => 'rgba(168, 85, 247, 0.85)',
        'hex_glow' => '#A855F7',
        'priority' => 1500000,
        'default_size' => 34,
        'rarity' => 'Crystal VIP',
        'description' => 'Prestigious violet diamond partner badge for elite server patrons and top-tier partners.',
        'type' => 'image',
        'fn_call' => "generate_ff_badge_html('FF_UI_Badge_KOL03.png', 'FF Diamond Partner', 'rgba(168, 85, 247, 0.85)', 'top-right', '34px');"
    ],
    [
        'id' => 'esports-pro-crown',
        'db_id' => 21,
        'slug' => 'esports-crown',
        'name' => 'FF Esports PRO Crown',
        'title' => 'Verified Competitor & Tournament Champion Crown',
        'category' => 'Competitive & Esports',
        'category_slug' => 'esports',
        'file' => 'FF_UI_Badge_ESPORTS01.png',
        'glow_color' => 'rgba(251, 191, 36, 0.85)',
        'hex_glow' => '#FBBF24',
        'priority' => 95000,
        'default_size' => 34,
        'rarity' => 'Tournament Champion',
        'description' => 'Official crown badge designated for esports champions, tournament MVPs, and verified pro contenders.',
        'type' => 'image',
        'fn_call' => "generate_ff_badge_html('FF_UI_Badge_ESPORTS01.png', 'FF Esports PRO Crown', 'rgba(251, 191, 36, 0.85)', 'top-right', '34px');"
    ],
    [
        'id' => 'official-staff-gm',
        'db_id' => 22,
        'slug' => 'gm-staff',
        'name' => 'FF Official Staff / GM',
        'title' => 'Garena Official Staff, Admin & Server Moderator',
        'category' => 'Administration & Authority',
        'category_slug' => 'staff',
        'file' => 'FF_UI_Badge_GM01.png',
        'glow_color' => 'rgba(239, 68, 68, 0.85)',
        'hex_glow' => '#EF4444',
        'priority' => 90000,
        'default_size' => 34,
        'rarity' => 'Official Authority',
        'description' => 'Crimson Garena server moderator and administrator badge symbolizing platform authority.',
        'type' => 'image',
        'fn_call' => "generate_ff_badge_html('FF_UI_Badge_GM01.png', 'FF Official Staff / GM', 'rgba(239, 68, 68, 0.85)', 'top-right', '34px');"
    ],
    [
        'id' => 'craftland-creator',
        'db_id' => 23,
        'slug' => 'craftland-creator',
        'name' => 'FF Craftland Creator',
        'title' => 'Craftland Map Architect & Certified Creator',
        'category' => 'Creators & Architects',
        'category_slug' => 'creator',
        'file' => 'craftland.png',
        'glow_color' => 'rgba(6, 182, 212, 0.85)',
        'hex_glow' => '#06B6D4',
        'priority' => 85000,
        'default_size' => 34,
        'rarity' => 'Master Architect',
        'description' => 'Cyan cube badge awarded to certified Craftland map designers, builders, and creative architects.',
        'type' => 'image',
        'fn_call' => "generate_ff_badge_html('craftland.png', 'FF Craftland Creator', 'rgba(6, 182, 212, 0.85)', 'top-right', '34px');"
    ],
    [
        'id' => 'emerging-creator',
        'db_id' => 24,
        'slug' => 'emerging-streamer',
        'name' => 'FF Emerging Creator',
        'title' => 'Rising Streamer & Tier-2 Partner Badge',
        'category' => 'Rising Creators & Streamers',
        'category_slug' => 'creator',
        'file' => 'FF_UI_Badge_KOL02.png',
        'glow_color' => 'rgba(16, 185, 129, 0.85)',
        'hex_glow' => '#10B981',
        'priority' => 80000,
        'default_size' => 34,
        'rarity' => 'Rising Talent',
        'description' => 'Emerald lightning badge for verified up-and-coming gaming streamers and content creators.',
        'type' => 'image',
        'fn_call' => "generate_ff_badge_html('FF_UI_Badge_KOL02.png', 'FF Emerging Creator', 'rgba(16, 185, 129, 0.85)', 'top-right', '34px');"
    ],
    [
        'id' => 'vbadge-classic',
        'db_id' => 201,
        'slug' => 'vbadge-classic',
        'name' => 'Classic Gold V-Badge',
        'title' => 'Classic Free Fire Verified V-Badge',
        'category' => 'Legacy & Classics',
        'category_slug' => 'legacy',
        'file' => 'vbadge.png',
        'glow_color' => 'rgba(255, 215, 0, 0.85)',
        'hex_glow' => '#FFD700',
        'priority' => 75000,
        'default_size' => 32,
        'rarity' => 'Classic Legacy',
        'description' => 'The timeless golden V-Badge that started it all.',
        'type' => 'image',
        'fn_call' => "generate_ff_badge_html('vbadge.png', 'Classic Gold V-Badge', 'rgba(255, 215, 0, 0.85)', 'in-line', '32px');"
    ],
    [
        'id' => 'vbadge-vector',
        'db_id' => 202,
        'slug' => 'vbadge-vector',
        'name' => 'Vector SVG V-Badge',
        'title' => 'Ultra High Definition Vector V-Badge',
        'category' => 'Vector & Scalable',
        'category_slug' => 'vector',
        'file' => 'vbadge.svg',
        'glow_color' => 'rgba(255, 215, 0, 0.85)',
        'hex_glow' => '#FFD700',
        'priority' => 70000,
        'default_size' => 30,
        'rarity' => 'Vector Scalable',
        'description' => 'Infinitely scalable SVG vector V-badge for 4K and Retina displays.',
        'type' => 'image',
        'fn_call' => "generate_ff_badge_html('vbadge.svg', 'Vector SVG V-Badge', 'rgba(255, 215, 0, 0.85)', 'in-line', '30px');"
    ],
    [
        'id' => 'moderator-shield',
        'db_id' => 5,
        'slug' => 'moderator-shield',
        'name' => 'MODERETOR',
        'title' => 'Verified Community Guardian Shield',
        'category' => 'Security & Moderation',
        'category_slug' => 'security',
        'file' => '',
        'glow_color' => 'rgba(255, 215, 0, 0.9)',
        'hex_glow' => '#FFE000',
        'priority' => 10000,
        'default_size' => 22,
        'rarity' => 'Shield Guardian',
        'description' => 'Golden shield with verify checkmark for community moderators and arbiters.',
        'type' => 'svg_code',
        'svg_code' => '<svg viewBox="0 0 24 24" fill="none" style="width:100%;height:100%;animation:pulse_gold 2s infinite ease-in-out;"><path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3z" fill="url(#mod_grad)"/><path d="M10 15.5l-3.5-3.5 1.41-1.41L10 12.67l5.59-5.59L17 8.5 10 15.5z" fill="#000"/><defs><linearGradient id="mod_grad" x1="4" y1="2" x2="20" y2="22" gradientUnits="userSpaceOnUse"><stop stop-color="#FFE000"/><stop offset="1" stop-color="#799F0C"/></linearGradient></defs></svg>',
        'fn_call' => "render_verified_badge([\$badge], \$settings, 5, 'header');"
    ],
    [
        'id' => 'pro-player-text',
        'db_id' => 4,
        'slug' => 'pro-player',
        'name' => 'PRO-PLYER',
        'title' => 'Animated Fire Text Pro Player',
        'category' => 'Rank & Competitive',
        'category_slug' => 'rank',
        'file' => '',
        'glow_color' => 'rgba(249, 212, 35, 0.9)',
        'hex_glow' => '#F9D423',
        'priority' => 1000,
        'default_size' => 16,
        'rarity' => 'Competitive Tier',
        'description' => 'Cyberpunk Orbitron typography with blazing molten-fire text glow.',
        'type' => 'text_code',
        'text_code' => '<span class="pro-badge" style="font-family:Orbitron,sans-serif;font-weight:900;font-size:13px;letter-spacing:1px;color:#f9d423;animation:fire_text 2s infinite alternate;text-transform:uppercase;">PRO</span>',
        'fn_call' => "render_verified_badge([\$badge], \$settings, 4, 'inline');"
    ],
    [
        'id' => 'legendary-diamond',
        'db_id' => 3,
        'slug' => 'legendary-diamond',
        'name' => 'Legendary',
        'title' => 'Mystic Purple Aura Diamond',
        'category' => 'Exclusive Tier',
        'category_slug' => 'exclusive',
        'file' => '',
        'glow_color' => 'rgba(168, 85, 247, 0.95)',
        'hex_glow' => '#8E2DE2',
        'priority' => 95,
        'default_size' => 24,
        'rarity' => 'Mythic Legend',
        'description' => 'Prismatic diamond with rotating gradient and purple cosmic aura.',
        'type' => 'svg_code',
        'svg_code' => '<svg viewBox="0 0 24 24" style="width:100%;height:100%;animation:glow_legend 2.5s infinite ease-in-out;"><defs><linearGradient id="leg_grad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#8e2de2"/><stop offset="1" stop-color="#4a00e0"/></linearGradient></defs><path fill="url(#leg_grad)" d="M12 .5L4.5 9l7.5 14.5L19.5 9L12 .5zm0 2.31L17.5 9H6.5L12 2.81z"/></svg>',
        'fn_call' => "render_verified_badge([\$badge], \$settings, 3, 'header');"
    ],
    [
        'id' => 'hud-alive-teal',
        'db_id' => 301,
        'slug' => 'hud-alive',
        'name' => 'HUD Alive Teal',
        'title' => 'In-game Alive Players HUD Badge',
        'category' => 'In-game HUD',
        'category_slug' => 'hud',
        'file' => 'badge_alive_teal.png',
        'glow_color' => 'rgba(20, 184, 166, 0.85)',
        'hex_glow' => '#14B8A6',
        'priority' => 500,
        'default_size' => 26,
        'rarity' => 'In-Game HUD',
        'description' => 'Free Fire in-game battle interface alive survivor badge icon.',
        'type' => 'image',
        'fn_call' => "generate_ff_badge_html('badge_alive_teal.png', 'HUD Alive Teal', 'rgba(20, 184, 166, 0.85)', 'in-line', '26px');"
    ],
    [
        'id' => 'hud-kill-maroon',
        'db_id' => 302,
        'slug' => 'hud-kill',
        'name' => 'HUD Kill Maroon',
        'title' => 'In-game Elimination HUD Badge',
        'category' => 'In-game HUD',
        'category_slug' => 'hud',
        'file' => 'badge_kill_maroon.png',
        'glow_color' => 'rgba(225, 29, 72, 0.85)',
        'hex_glow' => '#E11D48',
        'priority' => 500,
        'default_size' => 26,
        'rarity' => 'In-Game HUD',
        'description' => 'Free Fire in-game battle interface elimination kill badge icon.',
        'type' => 'image',
        'fn_call' => "generate_ff_badge_html('badge_kill_maroon.png', 'HUD Kill Maroon', 'rgba(225, 29, 72, 0.85)', 'in-line', '26px');"
    ],
    [
        'id' => 'neon-stream-badge',
        'db_id' => 401,
        'slug' => 'neon-stream',
        'name' => 'Neon Gaming Streamer',
        'title' => 'Neon Live Stream Gaming Badge',
        'category' => 'Special Editions',
        'category_slug' => 'special',
        'file' => 'neon_live_stream_badge.png',
        'glow_color' => 'rgba(139, 92, 246, 0.9)',
        'hex_glow' => '#8B5CF6',
        'priority' => 450,
        'default_size' => 38,
        'rarity' => 'Special Edition',
        'description' => 'Futuristic neon gaming badge designed for top streamers.',
        'type' => 'image',
        'fn_call' => "generate_ff_badge_html('neon_live_stream_badge.png', 'Neon Gaming Streamer', 'rgba(139, 92, 246, 0.9)', 'top-right', '38px');"
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Fire V-Badges CDN & Functional Logic Hub</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800;900&family=Rajdhani:wght@600;700&family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Self-contained Global Animation Stylesheet -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800;900&family=Rajdhani:wght@600;700&display=swap');

        /* 1. Free Fire Signature Light-Sweep Reflection */
        @keyframes ff_shape_sweep {
            0% { transform: translate(-100%, -100%) rotate(45deg); }
            100% { transform: translate(100%, 100%) rotate(45deg); }
        }

        /* 2. Guardian Shield Gold Pulse */
        @keyframes pulse_gold {
            0%, 100% { filter: drop-shadow(0 0 2px gold) drop-shadow(0 2px 4px rgba(0,0,0,0.8)); }
            50% { filter: drop-shadow(0 0 10px gold) drop-shadow(0 0 16px rgba(255,215,0,0.6)); }
        }

        /* 3. Molten Fire Text Animation */
        @keyframes fire_text {
            from { text-shadow: 0 0 2px #ff8c00, 0 0 5px #ff4500; }
            to { text-shadow: 0 0 6px #ff4500, 0 0 14px #ff8c00, 0 0 20px #ff2200; }
        }

        /* 4. Cosmic Purple Legend Glow */
        @keyframes glow_legend {
            0%, 100% { filter: drop-shadow(0 0 3px #8e2de2) drop-shadow(0 1px 3px rgba(0,0,0,0.9)); }
            50% { filter: drop-shadow(0 0 12px #a855f7) drop-shadow(0 0 20px rgba(168,85,247,0.7)); }
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #080915;
            color: #e2e8f0;
        }

        .font-orbitron { font-family: 'Orbitron', sans-serif; }
        .font-rajdhani { font-family: 'Rajdhani', sans-serif; }
        .font-mono-code { font-family: 'Fira Code', monospace; }

        .ff-shape-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent !important;
            border: none !important;
            border-radius: 0 !important;
            overflow: visible !important;
            user-select: none;
            -webkit-user-select: none;
            vertical-align: middle;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .ff-shape-badge:hover {
            transform: scale(1.18);
        }

        .ff-badge-card {
            background: rgba(15, 17, 35, 0.85);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ff-badge-card:hover {
            border-color: rgba(234, 179, 8, 0.4);
            transform: translateY(-5px);
            box-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.8), 0 0 25px -5px rgba(234, 179, 8, 0.2);
        }

        .custom-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col selection:bg-yellow-400 selection:text-black">

    <!-- Header Navbar -->
    <header class="border-b border-gray-800 bg-[#0d0f22]/95 sticky top-0 z-50 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-yellow-500 to-amber-300 p-0.5 shadow-lg shadow-yellow-500/20">
                    <div class="w-full h-full bg-[#080915] rounded-[14px] flex items-center justify-center">
                        <span class="ff-shape-badge" style="width:28px;height:28px;filter:drop-shadow(0 0 6px rgba(255,215,0,0.85));">
                            <img src="badges/FF_UI_Badge_KOL01.png" onerror="this.src='<?= ff_badge_cdn_url('FF_UI_Badge_KOL01.png') ?>'" style="width:100%;height:100%;object-fit:contain;display:block;">
                            <span style="position:absolute;inset:0;pointer-events:none;overflow:hidden;-webkit-mask-image:url('badges/FF_UI_Badge_KOL01.png');mask-image:url('badges/FF_UI_Badge_KOL01.png');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                                <span style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;background:linear-gradient(45deg,transparent 35%,rgba(255,255,255,0.2) 44%,rgba(255,255,255,0.95) 50%,rgba(255,255,255,0.2) 56%,transparent 65%);animation:ff_shape_sweep 2.6s infinite linear;mix-blend-mode:screen;display:block;"></span>
                            </span>
                        </span>
                    </div>
                </div>
                <div>
                    <h1 class="text-lg font-black font-orbitron tracking-wider text-white flex items-center gap-2">
                        FF V-BADGES <span class="text-[10px] px-2 py-0.5 rounded-full bg-yellow-400 text-black font-extrabold uppercase tracking-widest font-mono">CDN & LOGIC HUB</span>
                    </h1>
                    <p class="text-[11px] text-gray-400 font-rajdhani tracking-wider">PROJECT GITHUB: <span class="text-yellow-400 font-mono"><?= $repo_name ?></span></p>
                </div>
            </div>

            <!-- Repository & Push Shortcut -->
            <div class="flex items-center gap-2.5">
                <a href="https://github.com/<?= $repo_name ?>" target="_blank" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-white text-xs font-bold flex items-center gap-2 transition">
                    <i class="fab fa-github text-sm text-yellow-400"></i>
                    <span class="hidden sm:inline">github.com/<?= $repo_name ?></span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8 w-full">

        <!-- Top Showcase & Instructions Banner -->
        <section class="ff-badge-card p-6 sm:p-8 border border-yellow-500/20 relative overflow-hidden">
            <div class="absolute -right-24 -top-24 w-80 h-80 bg-yellow-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-10">
                <div class="space-y-2 max-w-2xl">
                    <span class="text-xs font-bold uppercase tracking-widest font-mono px-2.5 py-1 rounded bg-yellow-400/10 text-yellow-300 border border-yellow-400/30">
                        <i class="fas fa-cubes text-yellow-400 mr-1"></i> Interactive Functional Logic Explorer
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black font-orbitron text-white">
                        সবগুলো বেজ অ্যানিমেশন সহকারে এবং নিচে নিচে ফাংশনাল লজিক কল
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-300 leading-relaxed">
                        নিচে প্রতিটি বেজ লাইভ অ্যানিমেশন (গোল্ডেন/ক্রিস্টাল লাইট-সুইপ বিম) সহ দেওয়া আছে। প্রতিটি বেজের ঠিক নিচেই তার <strong>PHP ফাংশনাল লজিক কল</strong>, <strong>গিটহাব সিডিএন ইমেজ ইউআরএল</strong>, <strong>ড্রপ-ইন HTML/CSS কোড</strong> এবং <strong>JSON এপিআই</strong> কপি করার জন্য রেডিমেড বাটন রয়েছে।
                    </p>
                </div>

                <!-- GitHub Push Box -->
                <div class="bg-black/60 border border-gray-800 rounded-2xl p-4 w-full md:w-auto shrink-0 space-y-2">
                    <span class="text-[10px] font-mono text-gray-400 uppercase font-bold flex items-center justify-between">
                        <span>গিটহাবে পুশ করার কমান্ড:</span>
                        <button onclick="copyToClipboard('git remote add origin https://github.com/<?= $repo_name ?>.git\ngit push -u origin main', this)" class="text-yellow-400 hover:text-white text-xs">
                            <i class="fas fa-copy"></i>
                        </button>
                    </span>
                    <pre class="text-[11px] font-mono text-emerald-400 bg-gray-900/80 p-2.5 rounded-lg overflow-x-auto custom-scroll leading-relaxed">git remote add origin https://github.com/<?= $repo_name ?>.git
git push -u origin main</pre>
                </div>
            </div>
        </section>

        <!-- Category Filter & Search -->
        <section class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0 custom-scroll" id="catFilter">
                <button type="button" onclick="filterBadges('all')" class="cat-pill active px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-yellow-400 text-black transition" data-cat="all">All (<?= count($master_badges) ?>)</button>
                <button type="button" onclick="filterBadges('official')" class="cat-pill px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-gray-800/80 text-gray-300 hover:text-white border border-white/5 transition" data-cat="official">Official V</button>
                <button type="button" onclick="filterBadges('vip')" class="cat-pill px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-gray-800/80 text-gray-300 hover:text-white border border-white/5 transition" data-cat="vip">VIP Partner</button>
                <button type="button" onclick="filterBadges('esports')" class="cat-pill px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-gray-800/80 text-gray-300 hover:text-white border border-white/5 transition" data-cat="esports">Esports PRO</button>
                <button type="button" onclick="filterBadges('staff')" class="cat-pill px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-gray-800/80 text-gray-300 hover:text-white border border-white/5 transition" data-cat="staff">Staff / GM</button>
                <button type="button" onclick="filterBadges('creator')" class="cat-pill px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-gray-800/80 text-gray-300 hover:text-white border border-white/5 transition" data-cat="creator">Creators</button>
                <button type="button" onclick="filterBadges('hud')" class="cat-pill px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-gray-800/80 text-gray-300 hover:text-white border border-white/5 transition" data-cat="hud">HUD & Rank</button>
            </div>

            <div class="relative w-full sm:w-64">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-500 text-xs"></i>
                <input type="text" id="searchInput" oninput="searchBadgeCards(this.value)" placeholder="Search badges by name or ID..." class="w-full bg-[#12142d] border border-gray-700 rounded-xl pl-8 pr-3 py-1.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-yellow-400 transition">
            </div>
        </section>

        <!-- Master Badges Grid -->
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6" id="badgesContainer">
            <?php foreach ($master_badges as $b): ?>
                <?php
                    $img_local = 'badges/' . $b['file'];
                    $img_cdn = ff_badge_cdn_url($b['file']);
                    $glow = $b['glow_color'];
                    $b_size = $b['default_size'] . 'px';
                    $slug = $b['slug'];

                    // Prepare complete HTML snippet
                    if ($b['type'] === 'image') {
                        $html_snippet = '<span class="ff-shape-badge" style="position:relative;display:inline-flex;width:' . $b_size . ';height:' . $b_size . ';filter:drop-shadow(0 0 6px ' . $glow . ') drop-shadow(0 2px 4px rgba(0,0,0,0.9));user-select:none;">' . "\n" .
                            '  <img src="' . $img_cdn . '" alt="' . htmlspecialchars($b['name']) . '" style="width:100%;height:100%;object-fit:contain;display:block;background:transparent;border:none;">' . "\n" .
                            '  <span style="position:absolute;inset:0;pointer-events:none;overflow:hidden;-webkit-mask-image:url(\'' . $img_cdn . '\');mask-image:url(\'' . $img_cdn . '\');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">' . "\n" .
                            '    <span style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;background:linear-gradient(45deg,transparent 35%,rgba(255,255,255,0.2) 44%,rgba(255,255,255,0.95) 50%,rgba(255,255,255,0.2) 56%,transparent 65%);animation:ff_shape_sweep 2.6s infinite linear;mix-blend-mode:screen;display:block;"></span>' . "\n" .
                            '  </span>' . "\n" .
                            '</span>';
                    } elseif ($b['type'] === 'svg_code') {
                        $html_snippet = '<div title="' . htmlspecialchars($b['title']) . '" style="display:inline-flex;align-items:center;justify-content:center;width:' . $b_size . ';height:' . $b_size . ';">' . "\n  " . $b['svg_code'] . "\n" . '</div>';
                    } elseif ($b['type'] === 'text_code') {
                        $html_snippet = $b['text_code'];
                    }

                    $api_url = 'https://cdn.jsdelivr.net/gh/' . $repo_name . '@main/api/v1/badge/' . $slug . '.json';
                    $web_tag = '<ff-badge name="' . $slug . '" size="' . $b['default_size'] . '"></ff-badge>';
                ?>
                <div class="ff-badge-card p-5 space-y-4" data-cat="<?= $b['category_slug'] ?>" data-name="<?= strtolower($b['name']) ?>" data-id="<?= strtolower($b['slug']) ?>">
                    
                    <!-- Top Info: Name, Priority, Rarity -->
                    <div class="flex items-start justify-between gap-2 border-b border-gray-800/80 pb-3">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-base text-white font-orbitron tracking-wide"><?= htmlspecialchars($b['name']) ?></h3>
                                <span class="text-[9px] font-mono px-2 py-0.5 rounded-full bg-yellow-400/10 text-yellow-300 border border-yellow-400/30 uppercase font-bold">
                                    Pri: <?= number_format($b['priority']) ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 font-rajdhani tracking-wide"><?= htmlspecialchars($b['title']) ?> &bull; <span class="text-gray-300"><?= $b['rarity'] ?></span></p>
                        </div>
                        <span class="text-[10px] font-mono text-cyan-400 px-2 py-0.5 rounded bg-cyan-950/40 border border-cyan-800/40 shrink-0">
                            <?= $b['category'] ?>
                        </span>
                    </div>

                    <!-- Live Animation Stage -->
                    <div class="h-28 bg-[#090a18] rounded-2xl border border-white/5 flex items-center justify-center p-3 relative overflow-hidden group shadow-inner">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-yellow-400/[0.03] to-transparent pointer-events-none"></div>

                        <!-- Animated Live Badge -->
                        <div class="scale-125 transition-transform duration-300 group-hover:scale-150">
                            <?php if ($b['type'] === 'image'): ?>
                                <span class="ff-shape-badge" style="width:<?= $b_size ?>;height:<?= $b_size ?>;filter:drop-shadow(0 0 7px <?= $glow ?>) drop-shadow(0 2px 4px rgba(0,0,0,0.9));">
                                    <img src="<?= $img_local ?>" onerror="this.src='<?= $img_cdn ?>'" alt="<?= htmlspecialchars($b['name']) ?>" style="width:100%;height:100%;object-fit:contain;display:block;">
                                    <span style="position:absolute;inset:0;pointer-events:none;overflow:hidden;-webkit-mask-image:url('<?= $img_local ?>');mask-image:url('<?= $img_local ?>');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                                        <span style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;background:linear-gradient(45deg,transparent 35%,rgba(255,255,255,0.2) 44%,rgba(255,255,255,0.95) 50%,rgba(255,255,255,0.2) 56%,transparent 65%);animation:ff_shape_sweep 2.6s infinite linear;mix-blend-mode:screen;display:block;"></span>
                                    </span>
                                </span>
                            <?php elseif ($b['type'] === 'svg_code'): ?>
                                <div style="width:<?= $b_size ?>;height:<?= $b_size ?>;display:inline-flex;align-items:center;justify-content:center;">
                                    <?= $b['svg_code'] ?>
                                </div>
                            <?php elseif ($b['type'] === 'text_code'): ?>
                                <?= $b['text_code'] ?>
                            <?php endif; ?>
                        </div>

                        <span class="absolute bottom-2 right-2 text-[9px] font-mono text-gray-500 uppercase tracking-widest">
                            <i class="fas fa-sparkles text-yellow-400 mr-1"></i> Live Sweep Animation
                        </span>
                    </div>

                    <!-- Functional Logic & Calls Container -->
                    <div class="space-y-3 pt-1">
                        
                        <!-- 1. PHP FUNCTIONAL LOGIC CALL -->
                        <div class="bg-black/50 border border-gray-800/90 rounded-xl p-2.5 space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-amber-400 font-mono flex items-center gap-1.5">
                                    <i class="fab fa-php text-sm"></i> <span>PHP Functional Logic Call</span>
                                </span>
                                <button type="button" onclick="copyToClipboard(<?= htmlspecialchars(json_encode($b['fn_call'])) ?>, this)" class="text-yellow-400 hover:text-yellow-300 text-[11px] font-semibold flex items-center gap-1 transition px-2 py-0.5 rounded bg-yellow-400/10 hover:bg-yellow-400/20">
                                    <i class="fas fa-copy text-[10px]"></i> <span>Copy PHP Call</span>
                                </button>
                            </div>
                            <div class="bg-[#0b0c1b] px-2.5 py-1.5 rounded-lg border border-gray-800/60 overflow-x-auto custom-scroll">
                                <code class="text-[11px] font-mono-code text-amber-300 whitespace-nowrap"><?= htmlspecialchars($b['fn_call']) ?></code>
                            </div>
                        </div>

                        <!-- 2. DIRECT CDN IMAGE URL -->
                        <?php if (!empty($b['file'])): ?>
                            <div class="bg-black/50 border border-gray-800/90 rounded-xl p-2.5 space-y-1">
                                <div class="flex items-center justify-between text-[11px] font-bold">
                                    <span class="text-cyan-400 font-mono flex items-center gap-1.5">
                                        <i class="fas fa-link text-xs"></i> <span>Global CDN Image URL</span>
                                    </span>
                                    <button type="button" onclick="copyToClipboard(<?= htmlspecialchars(json_encode($img_cdn)) ?>, this)" class="text-cyan-400 hover:text-cyan-300 text-[11px] font-semibold flex items-center gap-1 transition px-2 py-0.5 rounded bg-cyan-400/10 hover:bg-cyan-400/20">
                                        <i class="fas fa-copy text-[10px]"></i> <span>Copy CDN URL</span>
                                    </button>
                                </div>
                                <div class="bg-[#0b0c1b] px-2.5 py-1.5 rounded-lg border border-gray-800/60 overflow-x-auto custom-scroll">
                                    <code class="text-[11px] font-mono-code text-cyan-300 whitespace-nowrap"><?= htmlspecialchars($img_cdn) ?></code>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 3. DROP-IN HTML/CSS ANIMATED SNIPPET & WEB TAG -->
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="copyToClipboard(<?= htmlspecialchars(json_encode($html_snippet)) ?>, this)" class="px-3 py-2 rounded-xl bg-purple-600/20 hover:bg-purple-600/30 border border-purple-500/30 text-purple-200 text-xs font-bold flex items-center justify-center gap-1.5 transition">
                                <i class="fas fa-code text-[11px] text-purple-400"></i>
                                <span>Copy HTML Code</span>
                            </button>

                            <button type="button" onclick="copyToClipboard(<?= htmlspecialchars(json_encode($web_tag)) ?>, this)" class="px-3 py-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/30 text-emerald-200 text-xs font-bold flex items-center justify-center gap-1.5 transition">
                                <i class="fas fa-tag text-[11px] text-emerald-400"></i>
                                <span>Copy &lt;ff-badge&gt;</span>
                            </button>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </section>

    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-800 bg-[#070814] py-8 text-center text-xs text-gray-500 space-y-2 mt-12">
        <p class="font-rajdhani text-sm text-gray-400">Free Fire V-Badges Monster CDN & Functional Logic Hub &bull; Designed for Ultimate Scalability.</p>
        <p class="font-mono text-[11px] text-gray-500">Repository: <a href="https://github.com/<?= $repo_name ?>" target="_blank" class="text-yellow-400 hover:underline"><?= $repo_name ?></a> &bull; MIT License</p>
    </footer>

    <!-- Toast Notification -->
    <div id="copyToast" class="fixed bottom-6 right-6 bg-yellow-400 text-black font-extrabold px-4 py-2.5 rounded-2xl shadow-2xl flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 z-50 pointer-events-none">
        <i class="fas fa-check-circle text-base"></i>
        <span id="copyToastMsg">Copied!</span>
    </div>

    <!-- Client Script for Filtering & Instant Copy -->
    <script>
        function copyToClipboard(text, btn = null) {
            if (!navigator.clipboard) {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToastNotification('Copied successfully!');
                return;
            }

            navigator.clipboard.writeText(text).then(() => {
                showToastNotification('Copied successfully!');
                if (btn) {
                    const originalHtml = btn.innerHTML;
                    btn.classList.add('opacity-75');
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        btn.classList.remove('opacity-75');
                    }, 1500);
                }
            }).catch(err => {
                console.error('Failed to copy', err);
            });
        }

        function showToastNotification(msg) {
            const toast = document.getElementById('copyToast');
            document.getElementById('copyToastMsg').textContent = msg;
            toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 2000);
        }

        function filterBadges(cat) {
            document.querySelectorAll('.cat-pill').forEach(btn => {
                if (btn.getAttribute('data-cat') === cat) {
                    btn.className = 'cat-pill active px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-yellow-400 text-black transition';
                } else {
                    btn.className = 'cat-pill px-3.5 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-gray-800/80 text-gray-300 hover:text-white border border-white/5 transition';
                }
            });

            const cards = document.querySelectorAll('#badgesContainer > div');
            cards.forEach(card => {
                if (cat === 'all' || card.getAttribute('data-cat') === cat) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        function searchBadgeCards(val) {
            const q = val.toLowerCase().trim();
            const cards = document.querySelectorAll('#badgesContainer > div');
            cards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const id = card.getAttribute('data-id') || '';
                if (!q || name.includes(q) || id.includes(q)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>
