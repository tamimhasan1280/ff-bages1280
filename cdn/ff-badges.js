/**
 * Free Fire Badges & V-Badges Master Client SDK & Web Component v2.5.0
 * Ultra-fast CDN delivery, interactive Web Component, and instant DOM injection.
 * Supports 12...80 IDs and Free Fire Items Explorer
 * (c) Free Fire Badges CDN Project - MIT License
 */
(function(window, document) {
    'use strict';

    const DEFAULT_REPO = 'tamimhasan1280/ff-bages1280';
    const CDN_TEMPLATES = {
        jsdelivr: 'https://cdn.jsdelivr.net/gh/{REPO}@main/badges/',
        github_raw: 'https://raw.githubusercontent.com/{REPO}/main/badges/',
        statically: 'https://cdn.statically.io/gh/{REPO}/main/badges/'
    };

    let activeRepo = DEFAULT_REPO;
    let activeCDN = 'jsdelivr';
    let customBaseUrl = '';

    // Master Registry of Free Fire Badges & Items (Indexed by ID and Slug)
    const BADGES_DATABASE = {
    "official-v": {
        "id": "120000000080",
        "slug": "official-v",
        "icon_id": "Icon_FF_KOL01_VBadge_Gold",
        "name": "OFFICIAL V BAGE",
        "title": "Official Influencer V-Badge",
        "category": "Official Partner (KOL)",
        "file": "FF_UI_Badge_KOL01.png",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 2000000,
        "defaultSize": 34,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "description": "The authentic Free Fire partner influencer verification badge with gold light-sweep beam."
    },
    "120000000080": {
        "id": "120000000080",
        "slug": "official-v",
        "icon_id": "Icon_FF_KOL01_VBadge_Gold",
        "name": "OFFICIAL V BAGE",
        "title": "Official Influencer V-Badge",
        "category": "Official Partner (KOL)",
        "file": "FF_UI_Badge_KOL01.png",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 2000000,
        "defaultSize": 34,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "description": "The authentic Free Fire partner influencer verification badge with gold light-sweep beam."
    },
    "diamond-partner": {
        "id": "120000000180",
        "slug": "diamond-partner",
        "icon_id": "Icon_FF_KOL03_Diamond_VIP",
        "name": "FF Diamond Partner",
        "title": "Diamond Partner / Elite VIP",
        "category": "Crystal VIP Partner",
        "file": "FF_UI_Badge_KOL03.png",
        "glowColor": "rgba(168, 85, 247, 0.85)",
        "hexGlow": "#A855F7",
        "priority": 1500000,
        "defaultSize": 34,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "description": "Prestigious violet diamond partner badge for elite server patrons and top-tier partners."
    },
    "120000000180": {
        "id": "120000000180",
        "slug": "diamond-partner",
        "icon_id": "Icon_FF_KOL03_Diamond_VIP",
        "name": "FF Diamond Partner",
        "title": "Diamond Partner / Elite VIP",
        "category": "Crystal VIP Partner",
        "file": "FF_UI_Badge_KOL03.png",
        "glowColor": "rgba(168, 85, 247, 0.85)",
        "hexGlow": "#A855F7",
        "priority": 1500000,
        "defaultSize": 34,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "description": "Prestigious violet diamond partner badge for elite server patrons and top-tier partners."
    },
    "esports-crown": {
        "id": "120000000280",
        "slug": "esports-crown",
        "icon_id": "Icon_FF_Esports_Crown_Gold",
        "name": "FF Esports PRO Crown",
        "title": "Verified Competitor & Tournament Champion Crown",
        "category": "Competitive & Esports",
        "file": "FF_UI_Badge_ESPORTS01.png",
        "glowColor": "rgba(251, 191, 36, 0.85)",
        "hexGlow": "#FBBF24",
        "priority": 95000,
        "defaultSize": 34,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "description": "Official crown badge designated for esports champions, tournament MVPs, and verified pro contenders."
    },
    "120000000280": {
        "id": "120000000280",
        "slug": "esports-crown",
        "icon_id": "Icon_FF_Esports_Crown_Gold",
        "name": "FF Esports PRO Crown",
        "title": "Verified Competitor & Tournament Champion Crown",
        "category": "Competitive & Esports",
        "file": "FF_UI_Badge_ESPORTS01.png",
        "glowColor": "rgba(251, 191, 36, 0.85)",
        "hexGlow": "#FBBF24",
        "priority": 95000,
        "defaultSize": 34,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "description": "Official crown badge designated for esports champions, tournament MVPs, and verified pro contenders."
    },
    "gm-staff": {
        "id": "120000000380",
        "slug": "gm-staff",
        "icon_id": "Icon_FF_GM_Staff_Crimson",
        "name": "FF Official Staff / GM",
        "title": "Garena Official Staff, Admin & Server Moderator",
        "category": "Administration & Authority",
        "file": "FF_UI_Badge_GM01.png",
        "glowColor": "rgba(239, 68, 68, 0.85)",
        "hexGlow": "#EF4444",
        "priority": 90000,
        "defaultSize": 34,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "description": "Crimson Garena server moderator and administrator badge symbolizing platform authority."
    },
    "120000000380": {
        "id": "120000000380",
        "slug": "gm-staff",
        "icon_id": "Icon_FF_GM_Staff_Crimson",
        "name": "FF Official Staff / GM",
        "title": "Garena Official Staff, Admin & Server Moderator",
        "category": "Administration & Authority",
        "file": "FF_UI_Badge_GM01.png",
        "glowColor": "rgba(239, 68, 68, 0.85)",
        "hexGlow": "#EF4444",
        "priority": 90000,
        "defaultSize": 34,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "description": "Crimson Garena server moderator and administrator badge symbolizing platform authority."
    },
    "craftland-creator": {
        "id": "120000000480",
        "slug": "craftland-creator",
        "icon_id": "Icon_FF_Craftland_Architect",
        "name": "FF Craftland Creator",
        "title": "Craftland Map Architect & Certified Creator",
        "category": "Creators & Architects",
        "file": "craftland.png",
        "glowColor": "rgba(6, 182, 212, 0.85)",
        "hexGlow": "#06B6D4",
        "priority": 85000,
        "defaultSize": 34,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "description": "Cyan cube badge awarded to certified Craftland map designers, builders, and creative architects."
    },
    "120000000480": {
        "id": "120000000480",
        "slug": "craftland-creator",
        "icon_id": "Icon_FF_Craftland_Architect",
        "name": "FF Craftland Creator",
        "title": "Craftland Map Architect & Certified Creator",
        "category": "Creators & Architects",
        "file": "craftland.png",
        "glowColor": "rgba(6, 182, 212, 0.85)",
        "hexGlow": "#06B6D4",
        "priority": 85000,
        "defaultSize": 34,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "description": "Cyan cube badge awarded to certified Craftland map designers, builders, and creative architects."
    },
    "emerging-streamer": {
        "id": "120000000580",
        "slug": "emerging-streamer",
        "icon_id": "Icon_FF_KOL02_Emerging_Streamer",
        "name": "FF Emerging Creator",
        "title": "Rising Streamer & Tier-2 Partner Badge",
        "category": "Rising Creators",
        "file": "FF_UI_Badge_KOL02.png",
        "glowColor": "rgba(16, 185, 129, 0.85)",
        "hexGlow": "#10B981",
        "priority": 80000,
        "defaultSize": 34,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "description": "Emerald lightning badge for verified up-and-coming gaming streamers and content creators."
    },
    "120000000580": {
        "id": "120000000580",
        "slug": "emerging-streamer",
        "icon_id": "Icon_FF_KOL02_Emerging_Streamer",
        "name": "FF Emerging Creator",
        "title": "Rising Streamer & Tier-2 Partner Badge",
        "category": "Rising Creators",
        "file": "FF_UI_Badge_KOL02.png",
        "glowColor": "rgba(16, 185, 129, 0.85)",
        "hexGlow": "#10B981",
        "priority": 80000,
        "defaultSize": 34,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "description": "Emerald lightning badge for verified up-and-coming gaming streamers and content creators."
    },
    "vbadge-classic": {
        "id": "120000000680",
        "slug": "vbadge-classic",
        "icon_id": "Icon_FF_Classic_Gold_VBadge",
        "name": "Classic Gold V-Badge",
        "title": "Classic Free Fire Verified V-Badge",
        "category": "Legacy & Classics",
        "file": "vbadge.png",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 75000,
        "defaultSize": 32,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "description": "The timeless golden V-Badge that started influencer verification across the Free Fire ecosystem."
    },
    "120000000680": {
        "id": "120000000680",
        "slug": "vbadge-classic",
        "icon_id": "Icon_FF_Classic_Gold_VBadge",
        "name": "Classic Gold V-Badge",
        "title": "Classic Free Fire Verified V-Badge",
        "category": "Legacy & Classics",
        "file": "vbadge.png",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 75000,
        "defaultSize": 32,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "description": "The timeless golden V-Badge that started influencer verification across the Free Fire ecosystem."
    },
    "vbadge-vector": {
        "id": "120000000780",
        "slug": "vbadge-vector",
        "icon_id": "Icon_FF_Vector_SVG_VBadge",
        "name": "Vector SVG V-Badge",
        "title": "Ultra High Definition Vector V-Badge",
        "category": "Vector & Scalable",
        "file": "vbadge.svg",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 70000,
        "defaultSize": 30,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "description": "Infinitely scalable SVG vector V-badge engineered for high-DPI displays and responsive banners."
    },
    "120000000780": {
        "id": "120000000780",
        "slug": "vbadge-vector",
        "icon_id": "Icon_FF_Vector_SVG_VBadge",
        "name": "Vector SVG V-Badge",
        "title": "Ultra High Definition Vector V-Badge",
        "category": "Vector & Scalable",
        "file": "vbadge.svg",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 70000,
        "defaultSize": 30,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "description": "Infinitely scalable SVG vector V-badge engineered for high-DPI displays and responsive banners."
    },
    "moderator-shield": {
        "id": "120000000880",
        "slug": "moderator-shield",
        "icon_id": "Icon_FF_Moderator_Shield_Gold",
        "name": "MODERETOR Shield",
        "title": "Verified Community Guardian Shield",
        "category": "Security & Moderation",
        "file": "verified-badge.svg",
        "glowColor": "rgba(255, 215, 0, 0.9)",
        "hexGlow": "#FFE000",
        "priority": 10000,
        "defaultSize": 22,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "svg_code",
        "description": "Golden shield with verify checkmark for community moderators, tournament referees, and arbiters.",
        "svgCode": "<svg viewBox=\"0 0 24 24\" fill=\"none\" style=\"width:100%;height:100%;animation:pulse_gold 2s infinite ease-in-out;\"><path d=\"M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3z\" fill=\"url(#mod_grad)\"/><path d=\"M10 15.5l-3.5-3.5 1.41-1.41L10 12.67l5.59-5.59L17 8.5 10 15.5z\" fill=\"#000\"/><defs><linearGradient id=\"mod_grad\" x1=\"4\" y1=\"2\" x2=\"20\" y2=\"22\" gradientUnits=\"userSpaceOnUse\"><stop stop-color=\"#FFE000\"/><stop offset=\"1\" stop-color=\"#799F0C\"/></linearGradient></defs></svg>"
    },
    "120000000880": {
        "id": "120000000880",
        "slug": "moderator-shield",
        "icon_id": "Icon_FF_Moderator_Shield_Gold",
        "name": "MODERETOR Shield",
        "title": "Verified Community Guardian Shield",
        "category": "Security & Moderation",
        "file": "verified-badge.svg",
        "glowColor": "rgba(255, 215, 0, 0.9)",
        "hexGlow": "#FFE000",
        "priority": 10000,
        "defaultSize": 22,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "svg_code",
        "description": "Golden shield with verify checkmark for community moderators, tournament referees, and arbiters.",
        "svgCode": "<svg viewBox=\"0 0 24 24\" fill=\"none\" style=\"width:100%;height:100%;animation:pulse_gold 2s infinite ease-in-out;\"><path d=\"M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3z\" fill=\"url(#mod_grad)\"/><path d=\"M10 15.5l-3.5-3.5 1.41-1.41L10 12.67l5.59-5.59L17 8.5 10 15.5z\" fill=\"#000\"/><defs><linearGradient id=\"mod_grad\" x1=\"4\" y1=\"2\" x2=\"20\" y2=\"22\" gradientUnits=\"userSpaceOnUse\"><stop stop-color=\"#FFE000\"/><stop offset=\"1\" stop-color=\"#799F0C\"/></linearGradient></defs></svg>"
    },
    "pro-player": {
        "id": "120000000980",
        "slug": "pro-player",
        "icon_id": "Icon_FF_ProPlayer_FireText",
        "name": "PRO-PLYER Fire Text",
        "title": "Animated Fire Text Pro Player",
        "category": "Rank & Competitive",
        "file": "",
        "glowColor": "rgba(249, 212, 35, 0.9)",
        "hexGlow": "#F9D423",
        "priority": 1000,
        "defaultSize": 16,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "text_code",
        "description": "Cyberpunk Orbitron typography with blazing molten-fire animated glow for competitive pro players.",
        "textCode": "<span class=\"pro-badge\" style=\"font-family:Orbitron,sans-serif;font-weight:900;font-size:13px;letter-spacing:1px;color:#f9d423;animation:fire_text 2s infinite alternate;text-transform:uppercase;\">PRO</span>"
    },
    "120000000980": {
        "id": "120000000980",
        "slug": "pro-player",
        "icon_id": "Icon_FF_ProPlayer_FireText",
        "name": "PRO-PLYER Fire Text",
        "title": "Animated Fire Text Pro Player",
        "category": "Rank & Competitive",
        "file": "",
        "glowColor": "rgba(249, 212, 35, 0.9)",
        "hexGlow": "#F9D423",
        "priority": 1000,
        "defaultSize": 16,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "text_code",
        "description": "Cyberpunk Orbitron typography with blazing molten-fire animated glow for competitive pro players.",
        "textCode": "<span class=\"pro-badge\" style=\"font-family:Orbitron,sans-serif;font-weight:900;font-size:13px;letter-spacing:1px;color:#f9d423;animation:fire_text 2s infinite alternate;text-transform:uppercase;\">PRO</span>"
    },
    "legendary-diamond": {
        "id": "120000001080",
        "slug": "legendary-diamond",
        "icon_id": "Icon_FF_Legendary_MysticDiamond",
        "name": "Legendary Mystic Diamond",
        "title": "Mystic Purple Aura Diamond",
        "category": "Exclusive Tier",
        "file": "",
        "glowColor": "rgba(168, 85, 247, 0.95)",
        "hexGlow": "#8E2DE2",
        "priority": 95,
        "defaultSize": 24,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "svg_code",
        "description": "Prismatic diamond with rotating gradient and purple cosmic aura reserved for legendary performers.",
        "svgCode": "<svg viewBox=\"0 0 24 24\" style=\"width:100%;height:100%;animation:glow_legend 2.5s infinite ease-in-out;\"><defs><linearGradient id=\"leg_grad\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\"><stop offset=\"0%\" stop-color=\"#8e2de2\"/><stop offset=\"1\" stop-color=\"#4a00e0\"/></linearGradient></defs><path fill=\"url(#leg_grad)\" d=\"M12 .5L4.5 9l7.5 14.5L19.5 9L12 .5zm0 2.31L17.5 9H6.5L12 2.81z\"/></svg>"
    },
    "120000001080": {
        "id": "120000001080",
        "slug": "legendary-diamond",
        "icon_id": "Icon_FF_Legendary_MysticDiamond",
        "name": "Legendary Mystic Diamond",
        "title": "Mystic Purple Aura Diamond",
        "category": "Exclusive Tier",
        "file": "",
        "glowColor": "rgba(168, 85, 247, 0.95)",
        "hexGlow": "#8E2DE2",
        "priority": 95,
        "defaultSize": 24,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "svg_code",
        "description": "Prismatic diamond with rotating gradient and purple cosmic aura reserved for legendary performers.",
        "svgCode": "<svg viewBox=\"0 0 24 24\" style=\"width:100%;height:100%;animation:glow_legend 2.5s infinite ease-in-out;\"><defs><linearGradient id=\"leg_grad\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\"><stop offset=\"0%\" stop-color=\"#8e2de2\"/><stop offset=\"1\" stop-color=\"#4a00e0\"/></linearGradient></defs><path fill=\"url(#leg_grad)\" d=\"M12 .5L4.5 9l7.5 14.5L19.5 9L12 .5zm0 2.31L17.5 9H6.5L12 2.81z\"/></svg>"
    },
    "hud-alive": {
        "id": "120000001180",
        "slug": "hud-alive",
        "icon_id": "Icon_FF_HUD_Survivor_AliveTeal",
        "name": "HUD Alive Survivor",
        "title": "In-game Alive Players HUD Badge",
        "category": "In-game HUD",
        "file": "badge_alive_teal.png",
        "glowColor": "rgba(20, 184, 166, 0.85)",
        "hexGlow": "#14B8A6",
        "priority": 500,
        "defaultSize": 26,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "description": "Free Fire in-game battle interface alive survivor badge icon."
    },
    "120000001180": {
        "id": "120000001180",
        "slug": "hud-alive",
        "icon_id": "Icon_FF_HUD_Survivor_AliveTeal",
        "name": "HUD Alive Survivor",
        "title": "In-game Alive Players HUD Badge",
        "category": "In-game HUD",
        "file": "badge_alive_teal.png",
        "glowColor": "rgba(20, 184, 166, 0.85)",
        "hexGlow": "#14B8A6",
        "priority": 500,
        "defaultSize": 26,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "description": "Free Fire in-game battle interface alive survivor badge icon."
    },
    "hud-kill": {
        "id": "120000001280",
        "slug": "hud-kill",
        "icon_id": "Icon_FF_HUD_Elimination_KillMaroon",
        "name": "HUD Kill Elimination",
        "title": "In-game Elimination HUD Badge",
        "category": "In-game HUD",
        "file": "badge_kill_maroon.png",
        "glowColor": "rgba(225, 29, 72, 0.85)",
        "hexGlow": "#E11D48",
        "priority": 500,
        "defaultSize": 26,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "description": "Free Fire in-game battle interface elimination kill badge icon."
    },
    "120000001280": {
        "id": "120000001280",
        "slug": "hud-kill",
        "icon_id": "Icon_FF_HUD_Elimination_KillMaroon",
        "name": "HUD Kill Elimination",
        "title": "In-game Elimination HUD Badge",
        "category": "In-game HUD",
        "file": "badge_kill_maroon.png",
        "glowColor": "rgba(225, 29, 72, 0.85)",
        "hexGlow": "#E11D48",
        "priority": 500,
        "defaultSize": 26,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "description": "Free Fire in-game battle interface elimination kill badge icon."
    },
    "neon-stream": {
        "id": "120000001380",
        "slug": "neon-stream",
        "icon_id": "Icon_FF_Special_NeonStreamer",
        "name": "Neon Gaming Streamer",
        "title": "Neon Live Stream Gaming Badge",
        "category": "Special Editions",
        "file": "neon_live_stream_badge.png",
        "glowColor": "rgba(139, 92, 246, 0.9)",
        "hexGlow": "#8B5CF6",
        "priority": 450,
        "defaultSize": 38,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "description": "Futuristic neon gaming broadcast badge designed for top live streaming partners."
    },
    "120000001380": {
        "id": "120000001380",
        "slug": "neon-stream",
        "icon_id": "Icon_FF_Special_NeonStreamer",
        "name": "Neon Gaming Streamer",
        "title": "Neon Live Stream Gaming Badge",
        "category": "Special Editions",
        "file": "neon_live_stream_badge.png",
        "glowColor": "rgba(139, 92, 246, 0.9)",
        "hexGlow": "#8B5CF6",
        "priority": 450,
        "defaultSize": 38,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "description": "Futuristic neon gaming broadcast badge designed for top live streaming partners."
    },
    "prime-vbadge": {
        "id": "120000001480",
        "slug": "prime-vbadge",
        "icon_id": "Icon_FF_Prime_VBadge_Aura",
        "name": "Prime Aura V-Badge",
        "title": "Prime Radiant Influencer V-Badge",
        "category": "Special Editions",
        "file": "vbadge_prime.png",
        "glowColor": "rgba(255, 215, 0, 0.9)",
        "hexGlow": "#FFD700",
        "priority": 400,
        "defaultSize": 36,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "description": "Supreme Gold V-Badge surrounded with high-energy radiant lightning corona aura."
    },
    "120000001480": {
        "id": "120000001480",
        "slug": "prime-vbadge",
        "icon_id": "Icon_FF_Prime_VBadge_Aura",
        "name": "Prime Aura V-Badge",
        "title": "Prime Radiant Influencer V-Badge",
        "category": "Special Editions",
        "file": "vbadge_prime.png",
        "glowColor": "rgba(255, 215, 0, 0.9)",
        "hexGlow": "#FFD700",
        "priority": 400,
        "defaultSize": 36,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "description": "Supreme Gold V-Badge surrounded with high-energy radiant lightning corona aura."
    },
    "special-badge-8": {
        "id": "120000001580",
        "slug": "special-badge-8",
        "icon_id": "Icon_FF_Badge_Level8_Special",
        "name": "Special Level 8 Elite",
        "title": "Special Tier Level 8 Emblem",
        "category": "Special Editions",
        "file": "icon_badge_8.png",
        "glowColor": "rgba(234, 179, 8, 0.85)",
        "hexGlow": "#EAB308",
        "priority": 350,
        "defaultSize": 32,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "description": "Special tier level 8 commemorative golden emblem badge."
    },
    "120000001580": {
        "id": "120000001580",
        "slug": "special-badge-8",
        "icon_id": "Icon_FF_Badge_Level8_Special",
        "name": "Special Level 8 Elite",
        "title": "Special Tier Level 8 Emblem",
        "category": "Special Editions",
        "file": "icon_badge_8.png",
        "glowColor": "rgba(234, 179, 8, 0.85)",
        "hexGlow": "#EAB308",
        "priority": 350,
        "defaultSize": 32,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "description": "Special tier level 8 commemorative golden emblem badge."
    },
    "rank-token": {
        "id": "120000001680",
        "slug": "rank-token",
        "icon_id": "Icon_FF_Rank_Token_Exchange",
        "name": "Rank Token",
        "title": "Ranked Season Exchange Token",
        "category": "Exchange Tokens",
        "file": "FF_UI_Badge_ESPORTS01.png",
        "glowColor": "rgba(59, 130, 246, 0.85)",
        "hexGlow": "#3B82F6",
        "priority": 300,
        "defaultSize": 34,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "description": "Official Free Fire ranked match token used to exchange for elite rewards in the Rank Store."
    },
    "120000001680": {
        "id": "120000001680",
        "slug": "rank-token",
        "icon_id": "Icon_FF_Rank_Token_Exchange",
        "name": "Rank Token",
        "title": "Ranked Season Exchange Token",
        "category": "Exchange Tokens",
        "file": "FF_UI_Badge_ESPORTS01.png",
        "glowColor": "rgba(59, 130, 246, 0.85)",
        "hexGlow": "#3B82F6",
        "priority": 300,
        "defaultSize": 34,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "description": "Official Free Fire ranked match token used to exchange for elite rewards in the Rank Store."
    },
    "cs-cup-voucher": {
        "id": "120000001780",
        "slug": "cs-cup-voucher",
        "icon_id": "Icon_FF_CupMatch_cscup",
        "name": "Clash Squad Cup Upgrade Voucher",
        "title": "Clash Squad Cup Advanced Reward Voucher",
        "category": "Misc",
        "file": "FF_UI_Badge_KOL03.png",
        "glowColor": "rgba(168, 85, 247, 0.85)",
        "hexGlow": "#A855F7",
        "priority": 250,
        "defaultSize": 34,
        "rarity": "PURPLE",
        "patch": "OB55",
        "type": "image",
        "description": "Use this voucher to upgrade to the Advanced rewards in the Clash Squad Championship Cup."
    },
    "120000001780": {
        "id": "120000001780",
        "slug": "cs-cup-voucher",
        "icon_id": "Icon_FF_CupMatch_cscup",
        "name": "Clash Squad Cup Upgrade Voucher",
        "title": "Clash Squad Cup Advanced Reward Voucher",
        "category": "Misc",
        "file": "FF_UI_Badge_KOL03.png",
        "glowColor": "rgba(168, 85, 247, 0.85)",
        "hexGlow": "#A855F7",
        "priority": 250,
        "defaultSize": 34,
        "rarity": "PURPLE",
        "patch": "OB55",
        "type": "image",
        "description": "Use this voucher to upgrade to the Advanced rewards in the Clash Squad Championship Cup."
    }
};

    // Public SDK API
    const FFBadges = {
        version: '2.5.0',

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
            // Deduplicate unique badges
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

            const imgUrl = this.getImageUrl(b.file);
            const filterStyle = 'filter:drop-shadow(0 0 6px ' + b.glowColor + ') drop-shadow(0 2px 4px rgba(0,0,0,0.9));';

            return '<span class="ff-shape-badge" style="' + posStyle + 'width:' + size + ';height:' + size + ';' + filterStyle + 'user-select:none;" title="' + b.title + '">
' +
                   '  <img src="' + imgUrl + '" alt="' + b.name + '" style="width:100%;height:100%;object-fit:contain;display:block;background:transparent;border:none;">
' +
                   '  <span class="ff-badge-shine" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;-webkit-mask-image:url(\'' + imgUrl + '\');mask-image:url(\'' + imgUrl + '\');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
' +
                   '    <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;background:linear-gradient(45deg,transparent 35%,rgba(255,255,255,0.2) 44%,rgba(255,255,255,0.95) 50%,rgba(255,255,255,0.2) 56%,transparent 65%);animation:ff_shape_sweep ' + speed + ' infinite linear;mix-blend-mode:screen;display:block;"></span>
' +
                   '  </span>
' +
                   '</span>';
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

    // Web Component Registration <ff-badge>
    if ('customElements' in window && !customElements.get('ff-badge')) {
        class FFBadgeElement extends HTMLElement {
            static get observedAttributes() {
                return ['name', 'id', 'size', 'position', 'speed'];
            }

            connectedCallback() {
                this.renderBadge();
            }

            attributeChangedCallback() {
                this.renderBadge();
            }

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

    // Expose to global scope
    window.FFBadges = FFBadges;

})(typeof window !== 'undefined' ? window : this, typeof document !== 'undefined' ? document : {});