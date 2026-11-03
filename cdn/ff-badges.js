/**
 * Free Fire Badges & V-Badges Master Client SDK & Web Component v3.2.0
 * Ultra-fast CDN delivery, interactive Web Component, Authentic Prime System (Lvl 1-8 VFX).
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

    const BADGES_DATABASE = {
    "prime-level-1": {
        "id": "120000010180",
        "slug": "prime-level-1",
        "icon_id": "Icon_FF_Prime_Level_01",
        "name": "Free Fire Prime Level 1",
        "title": "Prime Level 1 (Bronze Recruit Insignia)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_01.png",
        "glowColor": "rgba(205, 127, 50, 0.65)",
        "hexGlow": "#CD7F32",
        "priority": 810000,
        "defaultSize": 44,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 1,
        "description": "Free Fire Prime Level 1 bronze insignia. Clean authentic vector crest representing entry into the Garena Free Fire Prime System.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_01.png', 1, '44px');"
    },
    "120000010180": {
        "id": "120000010180",
        "slug": "prime-level-1",
        "icon_id": "Icon_FF_Prime_Level_01",
        "name": "Free Fire Prime Level 1",
        "title": "Prime Level 1 (Bronze Recruit Insignia)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_01.png",
        "glowColor": "rgba(205, 127, 50, 0.65)",
        "hexGlow": "#CD7F32",
        "priority": 810000,
        "defaultSize": 44,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 1,
        "description": "Free Fire Prime Level 1 bronze insignia. Clean authentic vector crest representing entry into the Garena Free Fire Prime System.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_01.png', 1, '44px');"
    },
    "prime-level-2": {
        "id": "120000010280",
        "slug": "prime-level-2",
        "icon_id": "Icon_FF_Prime_Level_02",
        "name": "Free Fire Prime Level 2",
        "title": "Prime Level 2 (Silver Scout Crest)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_02.png",
        "glowColor": "rgba(192, 192, 192, 0.7)",
        "hexGlow": "#C0C0C0",
        "priority": 820000,
        "defaultSize": 44,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 2,
        "description": "Free Fire Prime Level 2 silver rank insignia for seasoned battle survivors and verified account holders.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_02.png', 2, '44px');"
    },
    "120000010280": {
        "id": "120000010280",
        "slug": "prime-level-2",
        "icon_id": "Icon_FF_Prime_Level_02",
        "name": "Free Fire Prime Level 2",
        "title": "Prime Level 2 (Silver Scout Crest)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_02.png",
        "glowColor": "rgba(192, 192, 192, 0.7)",
        "hexGlow": "#C0C0C0",
        "priority": 820000,
        "defaultSize": 44,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 2,
        "description": "Free Fire Prime Level 2 silver rank insignia for seasoned battle survivors and verified account holders.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_02.png', 2, '44px');"
    },
    "prime-level-3": {
        "id": "120000010380",
        "slug": "prime-level-3",
        "icon_id": "Icon_FF_Prime_Level_03",
        "name": "Free Fire Prime Level 3",
        "title": "Prime Level 3 (Gold Vanguard)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_03.png",
        "glowColor": "rgba(234, 179, 8, 0.75)",
        "hexGlow": "#EAB308",
        "priority": 830000,
        "defaultSize": 44,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 3,
        "description": "Free Fire Prime Level 3 gold vanguard crest awarded for consistent battle performance, tier upgrades, and platform patronage.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_03.png', 3, '44px');"
    },
    "120000010380": {
        "id": "120000010380",
        "slug": "prime-level-3",
        "icon_id": "Icon_FF_Prime_Level_03",
        "name": "Free Fire Prime Level 3",
        "title": "Prime Level 3 (Gold Vanguard)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_03.png",
        "glowColor": "rgba(234, 179, 8, 0.75)",
        "hexGlow": "#EAB308",
        "priority": 830000,
        "defaultSize": 44,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 3,
        "description": "Free Fire Prime Level 3 gold vanguard crest awarded for consistent battle performance, tier upgrades, and platform patronage.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_03.png', 3, '44px');"
    },
    "prime-level-4": {
        "id": "120000010480",
        "slug": "prime-level-4",
        "icon_id": "Icon_FF_Prime_Level_04",
        "name": "Free Fire Prime Level 4",
        "title": "Prime Level 4 (Cyan Crystal Aura)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_04.png",
        "glowColor": "rgba(6, 182, 212, 0.85)",
        "hexGlow": "#06B6D4",
        "priority": 840000,
        "defaultSize": 46,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 4,
        "description": "Free Fire Prime Level 4 with cyan breathing aura glow and transparent drop-shadow contours emitting directly from the badge.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_04.png', 4, '46px');"
    },
    "120000010480": {
        "id": "120000010480",
        "slug": "prime-level-4",
        "icon_id": "Icon_FF_Prime_Level_04",
        "name": "Free Fire Prime Level 4",
        "title": "Prime Level 4 (Cyan Crystal Aura)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_04.png",
        "glowColor": "rgba(6, 182, 212, 0.85)",
        "hexGlow": "#06B6D4",
        "priority": 840000,
        "defaultSize": 46,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 4,
        "description": "Free Fire Prime Level 4 with cyan breathing aura glow and transparent drop-shadow contours emitting directly from the badge.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_04.png', 4, '46px');"
    },
    "prime-level-5": {
        "id": "120000010580",
        "slug": "prime-level-5",
        "icon_id": "Icon_FF_Prime_Level_05",
        "name": "Free Fire Prime Level 5",
        "title": "Prime Level 5 (Radiant Gold Halo)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_05.png",
        "glowColor": "rgba(245, 158, 11, 0.85)",
        "hexGlow": "#F59E0B",
        "priority": 850000,
        "defaultSize": 46,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 5,
        "description": "Free Fire Prime Level 5 with animated radiant halo pulse, breathing core aura, and soft wing expansion.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_05.png', 5, '46px');"
    },
    "120000010580": {
        "id": "120000010580",
        "slug": "prime-level-5",
        "icon_id": "Icon_FF_Prime_Level_05",
        "name": "Free Fire Prime Level 5",
        "title": "Prime Level 5 (Radiant Gold Halo)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_05.png",
        "glowColor": "rgba(245, 158, 11, 0.85)",
        "hexGlow": "#F59E0B",
        "priority": 850000,
        "defaultSize": 46,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 5,
        "description": "Free Fire Prime Level 5 with animated radiant halo pulse, breathing core aura, and soft wing expansion.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_05.png', 5, '46px');"
    },
    "prime-level-6": {
        "id": "120000010680",
        "slug": "prime-level-6",
        "icon_id": "Icon_FF_Prime_Level_06",
        "name": "Free Fire Prime Level 6",
        "title": "Prime Level 6 (Amber Flame Wings)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_06.png",
        "glowColor": "rgba(249, 115, 22, 0.9)",
        "hexGlow": "#F97316",
        "priority": 860000,
        "defaultSize": 48,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 6,
        "description": "Free Fire Prime Level 6 with amber flame contour aura, dual-frequency breathing, and energetic wing pulses.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_06.png', 6, '48px');"
    },
    "120000010680": {
        "id": "120000010680",
        "slug": "prime-level-6",
        "icon_id": "Icon_FF_Prime_Level_06",
        "name": "Free Fire Prime Level 6",
        "title": "Prime Level 6 (Amber Flame Wings)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_06.png",
        "glowColor": "rgba(249, 115, 22, 0.9)",
        "hexGlow": "#F97316",
        "priority": 860000,
        "defaultSize": 48,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 6,
        "description": "Free Fire Prime Level 6 with amber flame contour aura, dual-frequency breathing, and energetic wing pulses.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_06.png', 6, '48px');"
    },
    "prime-level-7": {
        "id": "120000010780",
        "slug": "prime-level-7",
        "icon_id": "Icon_FF_Prime_Level_07",
        "name": "Free Fire Prime Level 7",
        "title": "Prime Level 7 (Grandmaster Mythic Sparkle)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_07.png",
        "glowColor": "rgba(234, 179, 8, 0.95)",
        "hexGlow": "#EAB308",
        "priority": 870000,
        "defaultSize": 50,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 7,
        "description": "Free Fire Prime Level 7 Grandmaster crest with organic center gem glint twinkle, mythic golden wings, and celestial breathing glow.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_07.png', 7, '50px');"
    },
    "120000010780": {
        "id": "120000010780",
        "slug": "prime-level-7",
        "icon_id": "Icon_FF_Prime_Level_07",
        "name": "Free Fire Prime Level 7",
        "title": "Prime Level 7 (Grandmaster Mythic Sparkle)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_07.png",
        "glowColor": "rgba(234, 179, 8, 0.95)",
        "hexGlow": "#EAB308",
        "priority": 870000,
        "defaultSize": 50,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 7,
        "description": "Free Fire Prime Level 7 Grandmaster crest with organic center gem glint twinkle, mythic golden wings, and celestial breathing glow.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_07.png', 7, '50px');"
    },
    "prime-level-8": {
        "id": "120000010880",
        "slug": "prime-level-8",
        "icon_id": "Icon_FF_Prime_Level_08",
        "name": "Free Fire Prime Level 8",
        "title": "Prime Level 8 (Supreme God Tier Ascending Energy)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_08.png",
        "glowColor": "rgba(255, 215, 0, 1)",
        "hexGlow": "#FFD700",
        "priority": 880000,
        "defaultSize": 54,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 8,
        "description": "Free Fire Prime Level 8 Supreme pinnacle insignia. Features authentic right-to-upward ascending golden energy beam, periodic gem glint sparkle, and multi-tier drop-shadow aura.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_08.png', 8, '54px');"
    },
    "120000010880": {
        "id": "120000010880",
        "slug": "prime-level-8",
        "icon_id": "Icon_FF_Prime_Level_08",
        "name": "Free Fire Prime Level 8",
        "title": "Prime Level 8 (Supreme God Tier Ascending Energy)",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_prime_icon_08.png",
        "glowColor": "rgba(255, 215, 0, 1)",
        "hexGlow": "#FFD700",
        "priority": 880000,
        "defaultSize": 54,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "prime_image",
        "prime_level": 8,
        "description": "Free Fire Prime Level 8 Supreme pinnacle insignia. Features authentic right-to-upward ascending golden energy beam, periodic gem glint sparkle, and multi-tier drop-shadow aura.",
        "fn_call": "generate_ff_prime_badge_html('ff_ui_prime_icon_08.png', 8, '54px');"
    },
    "prime-profile-title": {
        "id": "120000010980",
        "slug": "prime-profile-title",
        "icon_id": "Icon_FF_Prime_ProfileTitle_Gold",
        "name": "Prime Profile Title Emblem",
        "title": "Official Prime Profile Title Header Tag",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_profile_title_prime.png",
        "glowColor": "rgba(255, 215, 0, 0.9)",
        "hexGlow": "#FFD700",
        "priority": 805000,
        "defaultSize": 48,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Special golden Prime title emblem displayed prominently on top profile cards of verified Free Fire Prime players.",
        "fn_call": "generate_ff_badge_html('ff_ui_profile_title_prime.png', 'Prime Profile Title Emblem', 'rgba(255, 215, 0, 0.9)', 'top-right', '48px');"
    },
    "120000010980": {
        "id": "120000010980",
        "slug": "prime-profile-title",
        "icon_id": "Icon_FF_Prime_ProfileTitle_Gold",
        "name": "Prime Profile Title Emblem",
        "title": "Official Prime Profile Title Header Tag",
        "category": "Prime System",
        "category_slug": "prime",
        "file": "ff_ui_profile_title_prime.png",
        "glowColor": "rgba(255, 215, 0, 0.9)",
        "hexGlow": "#FFD700",
        "priority": 805000,
        "defaultSize": 48,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Special golden Prime title emblem displayed prominently on top profile cards of verified Free Fire Prime players.",
        "fn_call": "generate_ff_badge_html('ff_ui_profile_title_prime.png', 'Prime Profile Title Emblem', 'rgba(255, 215, 0, 0.9)', 'top-right', '48px');"
    },
    "official-v": {
        "id": "120000000080",
        "slug": "official-v",
        "icon_id": "Icon_FF_KOL01_VBadge_Gold",
        "name": "OFFICIAL V BAGE",
        "title": "Official Influencer V-Badge",
        "category": "Official Partner (KOL)",
        "category_slug": "official",
        "file": "FF_UI_Badge_KOL01.png",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 2000000,
        "defaultSize": 34,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "The authentic Free Fire partner influencer verification badge with gold light-sweep beam.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_KOL01.png', 'OFFICIAL V BAGE', 'rgba(255, 215, 0, 0.85)', 'top-right', '34px');"
    },
    "120000000080": {
        "id": "120000000080",
        "slug": "official-v",
        "icon_id": "Icon_FF_KOL01_VBadge_Gold",
        "name": "OFFICIAL V BAGE",
        "title": "Official Influencer V-Badge",
        "category": "Official Partner (KOL)",
        "category_slug": "official",
        "file": "FF_UI_Badge_KOL01.png",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 2000000,
        "defaultSize": 34,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "The authentic Free Fire partner influencer verification badge with gold light-sweep beam.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_KOL01.png', 'OFFICIAL V BAGE', 'rgba(255, 215, 0, 0.85)', 'top-right', '34px');"
    },
    "diamond-partner": {
        "id": "120000000180",
        "slug": "diamond-partner",
        "icon_id": "Icon_FF_KOL03_Diamond_VIP",
        "name": "FF Diamond Partner",
        "title": "Diamond Partner / Elite VIP",
        "category": "Crystal VIP Partner",
        "category_slug": "vip",
        "file": "FF_UI_Badge_KOL03.png",
        "glowColor": "rgba(168, 85, 247, 0.85)",
        "hexGlow": "#A855F7",
        "priority": 1500000,
        "defaultSize": 34,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Prestigious violet diamond partner badge for elite server patrons and top-tier partners.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_KOL03.png', 'FF Diamond Partner', 'rgba(168, 85, 247, 0.85)', 'top-right', '34px');"
    },
    "120000000180": {
        "id": "120000000180",
        "slug": "diamond-partner",
        "icon_id": "Icon_FF_KOL03_Diamond_VIP",
        "name": "FF Diamond Partner",
        "title": "Diamond Partner / Elite VIP",
        "category": "Crystal VIP Partner",
        "category_slug": "vip",
        "file": "FF_UI_Badge_KOL03.png",
        "glowColor": "rgba(168, 85, 247, 0.85)",
        "hexGlow": "#A855F7",
        "priority": 1500000,
        "defaultSize": 34,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Prestigious violet diamond partner badge for elite server patrons and top-tier partners.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_KOL03.png', 'FF Diamond Partner', 'rgba(168, 85, 247, 0.85)', 'top-right', '34px');"
    },
    "esports-crown": {
        "id": "120000000280",
        "slug": "esports-crown",
        "icon_id": "Icon_FF_Esports_Crown_Gold",
        "name": "FF Esports PRO Crown",
        "title": "Verified Competitor & Tournament Champion Crown",
        "category": "Competitive & Esports",
        "category_slug": "esports",
        "file": "FF_UI_Badge_ESPORTS01.png",
        "glowColor": "rgba(251, 191, 36, 0.85)",
        "hexGlow": "#FBBF24",
        "priority": 95000,
        "defaultSize": 34,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Official crown badge designated for esports champions, tournament MVPs, and verified pro contenders.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_ESPORTS01.png', 'FF Esports PRO Crown', 'rgba(251, 191, 36, 0.85)', 'top-right', '34px');"
    },
    "120000000280": {
        "id": "120000000280",
        "slug": "esports-crown",
        "icon_id": "Icon_FF_Esports_Crown_Gold",
        "name": "FF Esports PRO Crown",
        "title": "Verified Competitor & Tournament Champion Crown",
        "category": "Competitive & Esports",
        "category_slug": "esports",
        "file": "FF_UI_Badge_ESPORTS01.png",
        "glowColor": "rgba(251, 191, 36, 0.85)",
        "hexGlow": "#FBBF24",
        "priority": 95000,
        "defaultSize": 34,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Official crown badge designated for esports champions, tournament MVPs, and verified pro contenders.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_ESPORTS01.png', 'FF Esports PRO Crown', 'rgba(251, 191, 36, 0.85)', 'top-right', '34px');"
    },
    "gm-staff": {
        "id": "120000000380",
        "slug": "gm-staff",
        "icon_id": "Icon_FF_GM_Staff_Crimson",
        "name": "FF Official Staff / GM",
        "title": "Garena Official Staff, Admin & Server Moderator",
        "category": "Administration & Authority",
        "category_slug": "staff",
        "file": "FF_UI_Badge_GM01.png",
        "glowColor": "rgba(239, 68, 68, 0.85)",
        "hexGlow": "#EF4444",
        "priority": 90000,
        "defaultSize": 34,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Crimson Garena server moderator and administrator badge symbolizing platform authority.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_GM01.png', 'FF Official Staff / GM', 'rgba(239, 68, 68, 0.85)', 'top-right', '34px');"
    },
    "120000000380": {
        "id": "120000000380",
        "slug": "gm-staff",
        "icon_id": "Icon_FF_GM_Staff_Crimson",
        "name": "FF Official Staff / GM",
        "title": "Garena Official Staff, Admin & Server Moderator",
        "category": "Administration & Authority",
        "category_slug": "staff",
        "file": "FF_UI_Badge_GM01.png",
        "glowColor": "rgba(239, 68, 68, 0.85)",
        "hexGlow": "#EF4444",
        "priority": 90000,
        "defaultSize": 34,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Crimson Garena server moderator and administrator badge symbolizing platform authority.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_GM01.png', 'FF Official Staff / GM', 'rgba(239, 68, 68, 0.85)', 'top-right', '34px');"
    },
    "craftland-creator": {
        "id": "120000000480",
        "slug": "craftland-creator",
        "icon_id": "Icon_FF_Craftland_Architect",
        "name": "FF Craftland Creator",
        "title": "Craftland Map Architect & Certified Creator",
        "category": "Creators & Architects",
        "category_slug": "creator",
        "file": "craftland.png",
        "glowColor": "rgba(6, 182, 212, 0.85)",
        "hexGlow": "#06B6D4",
        "priority": 85000,
        "defaultSize": 34,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Cyan cube badge awarded to certified Craftland map designers, builders, and creative architects.",
        "fn_call": "generate_ff_badge_html('craftland.png', 'FF Craftland Creator', 'rgba(6, 182, 212, 0.85)', 'top-right', '34px');"
    },
    "120000000480": {
        "id": "120000000480",
        "slug": "craftland-creator",
        "icon_id": "Icon_FF_Craftland_Architect",
        "name": "FF Craftland Creator",
        "title": "Craftland Map Architect & Certified Creator",
        "category": "Creators & Architects",
        "category_slug": "creator",
        "file": "craftland.png",
        "glowColor": "rgba(6, 182, 212, 0.85)",
        "hexGlow": "#06B6D4",
        "priority": 85000,
        "defaultSize": 34,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Cyan cube badge awarded to certified Craftland map designers, builders, and creative architects.",
        "fn_call": "generate_ff_badge_html('craftland.png', 'FF Craftland Creator', 'rgba(6, 182, 212, 0.85)', 'top-right', '34px');"
    },
    "emerging-streamer": {
        "id": "120000000580",
        "slug": "emerging-streamer",
        "icon_id": "Icon_FF_KOL02_Emerging_Streamer",
        "name": "FF Emerging Creator",
        "title": "Rising Streamer & Tier-2 Partner Badge",
        "category": "Rising Creators",
        "category_slug": "creator",
        "file": "FF_UI_Badge_KOL02.png",
        "glowColor": "rgba(16, 185, 129, 0.85)",
        "hexGlow": "#10B981",
        "priority": 80000,
        "defaultSize": 34,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Emerald lightning badge for verified up-and-coming gaming streamers and content creators.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_KOL02.png', 'FF Emerging Creator', 'rgba(16, 185, 129, 0.85)', 'top-right', '34px');"
    },
    "120000000580": {
        "id": "120000000580",
        "slug": "emerging-streamer",
        "icon_id": "Icon_FF_KOL02_Emerging_Streamer",
        "name": "FF Emerging Creator",
        "title": "Rising Streamer & Tier-2 Partner Badge",
        "category": "Rising Creators",
        "category_slug": "creator",
        "file": "FF_UI_Badge_KOL02.png",
        "glowColor": "rgba(16, 185, 129, 0.85)",
        "hexGlow": "#10B981",
        "priority": 80000,
        "defaultSize": 34,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Emerald lightning badge for verified up-and-coming gaming streamers and content creators.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_KOL02.png', 'FF Emerging Creator', 'rgba(16, 185, 129, 0.85)', 'top-right', '34px');"
    },
    "vbadge-classic": {
        "id": "120000000680",
        "slug": "vbadge-classic",
        "icon_id": "Icon_FF_Classic_Gold_VBadge",
        "name": "Classic Gold V-Badge",
        "title": "Classic Free Fire Verified V-Badge",
        "category": "Legacy & Classics",
        "category_slug": "legacy",
        "file": "vbadge.png",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 75000,
        "defaultSize": 32,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "The timeless golden V-Badge that started influencer verification across the Free Fire ecosystem.",
        "fn_call": "generate_ff_badge_html('vbadge.png', 'Classic Gold V-Badge', 'rgba(255, 215, 0, 0.85)', 'in-line', '32px');"
    },
    "120000000680": {
        "id": "120000000680",
        "slug": "vbadge-classic",
        "icon_id": "Icon_FF_Classic_Gold_VBadge",
        "name": "Classic Gold V-Badge",
        "title": "Classic Free Fire Verified V-Badge",
        "category": "Legacy & Classics",
        "category_slug": "legacy",
        "file": "vbadge.png",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 75000,
        "defaultSize": 32,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "The timeless golden V-Badge that started influencer verification across the Free Fire ecosystem.",
        "fn_call": "generate_ff_badge_html('vbadge.png', 'Classic Gold V-Badge', 'rgba(255, 215, 0, 0.85)', 'in-line', '32px');"
    },
    "vbadge-vector": {
        "id": "120000000780",
        "slug": "vbadge-vector",
        "icon_id": "Icon_FF_Vector_SVG_VBadge",
        "name": "Vector SVG V-Badge",
        "title": "Ultra High Definition Vector V-Badge",
        "category": "Vector & Scalable",
        "category_slug": "vector",
        "file": "vbadge.svg",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 70000,
        "defaultSize": 30,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Infinitely scalable SVG vector V-badge engineered for high-DPI displays and responsive banners.",
        "fn_call": "generate_ff_badge_html('vbadge.svg', 'Vector SVG V-Badge', 'rgba(255, 215, 0, 0.85)', 'in-line', '30px');"
    },
    "120000000780": {
        "id": "120000000780",
        "slug": "vbadge-vector",
        "icon_id": "Icon_FF_Vector_SVG_VBadge",
        "name": "Vector SVG V-Badge",
        "title": "Ultra High Definition Vector V-Badge",
        "category": "Vector & Scalable",
        "category_slug": "vector",
        "file": "vbadge.svg",
        "glowColor": "rgba(255, 215, 0, 0.85)",
        "hexGlow": "#FFD700",
        "priority": 70000,
        "defaultSize": 30,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Infinitely scalable SVG vector V-badge engineered for high-DPI displays and responsive banners.",
        "fn_call": "generate_ff_badge_html('vbadge.svg', 'Vector SVG V-Badge', 'rgba(255, 215, 0, 0.85)', 'in-line', '30px');"
    },
    "moderator-shield": {
        "id": "120000000880",
        "slug": "moderator-shield",
        "icon_id": "Icon_FF_Moderator_Shield_Gold",
        "name": "MODERETOR Shield",
        "title": "Verified Community Guardian Shield",
        "category": "Security & Moderation",
        "category_slug": "security",
        "file": "verified-badge.svg",
        "glowColor": "rgba(255, 215, 0, 0.9)",
        "hexGlow": "#FFE000",
        "priority": 10000,
        "defaultSize": 22,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "svg_code",
        "prime_level": null,
        "description": "Golden shield with verify checkmark for community moderators, tournament referees, and arbiters.",
        "fn_call": "render_verified_badge([$badge], $settings, 5, 'header');",
        "svgCode": "<svg viewBox=\"0 0 24 24\" fill=\"none\" style=\"width:100%;height:100%;animation:pulse_gold 2s infinite ease-in-out;\"><path d=\"M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3z\" fill=\"url(#mod_grad)\"/><path d=\"M10 15.5l-3.5-3.5 1.41-1.41L10 12.67l5.59-5.59L17 8.5 10 15.5z\" fill=\"#000\"/><defs><linearGradient id=\"mod_grad\" x1=\"4\" y1=\"2\" x2=\"20\" y2=\"22\" gradientUnits=\"userSpaceOnUse\"><stop stop-color=\"#FFE000\"/><stop offset=\"1\" stop-color=\"#799F0C\"/></linearGradient></defs></svg>"
    },
    "120000000880": {
        "id": "120000000880",
        "slug": "moderator-shield",
        "icon_id": "Icon_FF_Moderator_Shield_Gold",
        "name": "MODERETOR Shield",
        "title": "Verified Community Guardian Shield",
        "category": "Security & Moderation",
        "category_slug": "security",
        "file": "verified-badge.svg",
        "glowColor": "rgba(255, 215, 0, 0.9)",
        "hexGlow": "#FFE000",
        "priority": 10000,
        "defaultSize": 22,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "svg_code",
        "prime_level": null,
        "description": "Golden shield with verify checkmark for community moderators, tournament referees, and arbiters.",
        "fn_call": "render_verified_badge([$badge], $settings, 5, 'header');",
        "svgCode": "<svg viewBox=\"0 0 24 24\" fill=\"none\" style=\"width:100%;height:100%;animation:pulse_gold 2s infinite ease-in-out;\"><path d=\"M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3z\" fill=\"url(#mod_grad)\"/><path d=\"M10 15.5l-3.5-3.5 1.41-1.41L10 12.67l5.59-5.59L17 8.5 10 15.5z\" fill=\"#000\"/><defs><linearGradient id=\"mod_grad\" x1=\"4\" y1=\"2\" x2=\"20\" y2=\"22\" gradientUnits=\"userSpaceOnUse\"><stop stop-color=\"#FFE000\"/><stop offset=\"1\" stop-color=\"#799F0C\"/></linearGradient></defs></svg>"
    },
    "pro-player": {
        "id": "120000000980",
        "slug": "pro-player",
        "icon_id": "Icon_FF_ProPlayer_FireText",
        "name": "PRO-PLYER Fire Text",
        "title": "Animated Fire Text Pro Player",
        "category": "Rank & Competitive",
        "category_slug": "rank",
        "file": "",
        "glowColor": "rgba(249, 212, 35, 0.9)",
        "hexGlow": "#F9D423",
        "priority": 1000,
        "defaultSize": 16,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "text_code",
        "prime_level": null,
        "description": "Cyberpunk Orbitron typography with blazing molten-fire animated glow for competitive pro players.",
        "fn_call": "render_verified_badge([$badge], $settings, 4, 'inline');",
        "textCode": "<span class=\"pro-badge\" style=\"font-family:Orbitron,sans-serif;font-weight:900;font-size:13px;letter-spacing:1px;color:#f9d423;animation:fire_text 2s infinite alternate;text-transform:uppercase;\">PRO</span>"
    },
    "120000000980": {
        "id": "120000000980",
        "slug": "pro-player",
        "icon_id": "Icon_FF_ProPlayer_FireText",
        "name": "PRO-PLYER Fire Text",
        "title": "Animated Fire Text Pro Player",
        "category": "Rank & Competitive",
        "category_slug": "rank",
        "file": "",
        "glowColor": "rgba(249, 212, 35, 0.9)",
        "hexGlow": "#F9D423",
        "priority": 1000,
        "defaultSize": 16,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "text_code",
        "prime_level": null,
        "description": "Cyberpunk Orbitron typography with blazing molten-fire animated glow for competitive pro players.",
        "fn_call": "render_verified_badge([$badge], $settings, 4, 'inline');",
        "textCode": "<span class=\"pro-badge\" style=\"font-family:Orbitron,sans-serif;font-weight:900;font-size:13px;letter-spacing:1px;color:#f9d423;animation:fire_text 2s infinite alternate;text-transform:uppercase;\">PRO</span>"
    },
    "legendary-diamond": {
        "id": "120000001080",
        "slug": "legendary-diamond",
        "icon_id": "Icon_FF_Legendary_MysticDiamond",
        "name": "Legendary Mystic Diamond",
        "title": "Mystic Purple Aura Diamond",
        "category": "Exclusive Tier",
        "category_slug": "exclusive",
        "file": "",
        "glowColor": "rgba(168, 85, 247, 0.95)",
        "hexGlow": "#8E2DE2",
        "priority": 95,
        "defaultSize": 24,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "svg_code",
        "prime_level": null,
        "description": "Prismatic diamond with rotating gradient and purple cosmic aura reserved for legendary performers.",
        "fn_call": "render_verified_badge([$badge], $settings, 3, 'header');",
        "svgCode": "<svg viewBox=\"0 0 24 24\" style=\"width:100%;height:100%;animation:glow_legend 2.5s infinite ease-in-out;\"><defs><linearGradient id=\"leg_grad\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\"><stop offset=\"0%\" stop-color=\"#8e2de2\"/><stop offset=\"1\" stop-color=\"#4a00e0\"/></linearGradient></defs><path fill=\"url(#leg_grad)\" d=\"M12 .5L4.5 9l7.5 14.5L19.5 9L12 .5zm0 2.31L17.5 9H6.5L12 2.81z\"/></svg>"
    },
    "120000001080": {
        "id": "120000001080",
        "slug": "legendary-diamond",
        "icon_id": "Icon_FF_Legendary_MysticDiamond",
        "name": "Legendary Mystic Diamond",
        "title": "Mystic Purple Aura Diamond",
        "category": "Exclusive Tier",
        "category_slug": "exclusive",
        "file": "",
        "glowColor": "rgba(168, 85, 247, 0.95)",
        "hexGlow": "#8E2DE2",
        "priority": 95,
        "defaultSize": 24,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "svg_code",
        "prime_level": null,
        "description": "Prismatic diamond with rotating gradient and purple cosmic aura reserved for legendary performers.",
        "fn_call": "render_verified_badge([$badge], $settings, 3, 'header');",
        "svgCode": "<svg viewBox=\"0 0 24 24\" style=\"width:100%;height:100%;animation:glow_legend 2.5s infinite ease-in-out;\"><defs><linearGradient id=\"leg_grad\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\"><stop offset=\"0%\" stop-color=\"#8e2de2\"/><stop offset=\"1\" stop-color=\"#4a00e0\"/></linearGradient></defs><path fill=\"url(#leg_grad)\" d=\"M12 .5L4.5 9l7.5 14.5L19.5 9L12 .5zm0 2.31L17.5 9H6.5L12 2.81z\"/></svg>"
    },
    "hud-alive": {
        "id": "120000001180",
        "slug": "hud-alive",
        "icon_id": "Icon_FF_HUD_Survivor_AliveTeal",
        "name": "HUD Alive Survivor",
        "title": "In-game Alive Players HUD Badge",
        "category": "In-game HUD",
        "category_slug": "hud",
        "file": "badge_alive_teal.png",
        "glowColor": "rgba(20, 184, 166, 0.85)",
        "hexGlow": "#14B8A6",
        "priority": 500,
        "defaultSize": 26,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Free Fire in-game battle interface alive survivor badge icon.",
        "fn_call": "generate_ff_badge_html('badge_alive_teal.png', 'HUD Alive Survivor', 'rgba(20, 184, 166, 0.85)', 'in-line', '26px');"
    },
    "120000001180": {
        "id": "120000001180",
        "slug": "hud-alive",
        "icon_id": "Icon_FF_HUD_Survivor_AliveTeal",
        "name": "HUD Alive Survivor",
        "title": "In-game Alive Players HUD Badge",
        "category": "In-game HUD",
        "category_slug": "hud",
        "file": "badge_alive_teal.png",
        "glowColor": "rgba(20, 184, 166, 0.85)",
        "hexGlow": "#14B8A6",
        "priority": 500,
        "defaultSize": 26,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Free Fire in-game battle interface alive survivor badge icon.",
        "fn_call": "generate_ff_badge_html('badge_alive_teal.png', 'HUD Alive Survivor', 'rgba(20, 184, 166, 0.85)', 'in-line', '26px');"
    },
    "hud-kill": {
        "id": "120000001280",
        "slug": "hud-kill",
        "icon_id": "Icon_FF_HUD_Elimination_KillMaroon",
        "name": "HUD Kill Elimination",
        "title": "In-game Elimination HUD Badge",
        "category": "In-game HUD",
        "category_slug": "hud",
        "file": "badge_kill_maroon.png",
        "glowColor": "rgba(225, 29, 72, 0.85)",
        "hexGlow": "#E11D48",
        "priority": 500,
        "defaultSize": 26,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Free Fire in-game battle interface elimination kill badge icon.",
        "fn_call": "generate_ff_badge_html('badge_kill_maroon.png', 'HUD Kill Elimination', 'rgba(225, 29, 72, 0.85)', 'in-line', '26px');"
    },
    "120000001280": {
        "id": "120000001280",
        "slug": "hud-kill",
        "icon_id": "Icon_FF_HUD_Elimination_KillMaroon",
        "name": "HUD Kill Elimination",
        "title": "In-game Elimination HUD Badge",
        "category": "In-game HUD",
        "category_slug": "hud",
        "file": "badge_kill_maroon.png",
        "glowColor": "rgba(225, 29, 72, 0.85)",
        "hexGlow": "#E11D48",
        "priority": 500,
        "defaultSize": 26,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Free Fire in-game battle interface elimination kill badge icon.",
        "fn_call": "generate_ff_badge_html('badge_kill_maroon.png', 'HUD Kill Elimination', 'rgba(225, 29, 72, 0.85)', 'in-line', '26px');"
    },
    "neon-stream": {
        "id": "120000001380",
        "slug": "neon-stream",
        "icon_id": "Icon_FF_Special_NeonStreamer",
        "name": "Neon Gaming Streamer",
        "title": "Neon Live Stream Gaming Badge",
        "category": "Special Editions",
        "category_slug": "special",
        "file": "neon_live_stream_badge.png",
        "glowColor": "rgba(139, 92, 246, 0.9)",
        "hexGlow": "#8B5CF6",
        "priority": 450,
        "defaultSize": 38,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Futuristic neon gaming broadcast badge designed for top live streaming partners.",
        "fn_call": "generate_ff_badge_html('neon_live_stream_badge.png', 'Neon Gaming Streamer', 'rgba(139, 92, 246, 0.9)', 'top-right', '38px');"
    },
    "120000001380": {
        "id": "120000001380",
        "slug": "neon-stream",
        "icon_id": "Icon_FF_Special_NeonStreamer",
        "name": "Neon Gaming Streamer",
        "title": "Neon Live Stream Gaming Badge",
        "category": "Special Editions",
        "category_slug": "special",
        "file": "neon_live_stream_badge.png",
        "glowColor": "rgba(139, 92, 246, 0.9)",
        "hexGlow": "#8B5CF6",
        "priority": 450,
        "defaultSize": 38,
        "rarity": "LEGENDARY",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Futuristic neon gaming broadcast badge designed for top live streaming partners.",
        "fn_call": "generate_ff_badge_html('neon_live_stream_badge.png', 'Neon Gaming Streamer', 'rgba(139, 92, 246, 0.9)', 'top-right', '38px');"
    },
    "prime-vbadge": {
        "id": "120000001480",
        "slug": "prime-vbadge",
        "icon_id": "Icon_FF_Prime_VBadge_Aura",
        "name": "Prime Aura V-Badge",
        "title": "Prime Radiant Influencer V-Badge",
        "category": "Special Editions",
        "category_slug": "special",
        "file": "vbadge_prime.png",
        "glowColor": "rgba(255, 215, 0, 0.9)",
        "hexGlow": "#FFD700",
        "priority": 400,
        "defaultSize": 36,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Supreme Gold V-Badge surrounded with high-energy radiant lightning corona aura.",
        "fn_call": "generate_ff_badge_html('vbadge_prime.png', 'Prime Aura V-Badge', 'rgba(255, 215, 0, 0.9)', 'top-right', '36px');"
    },
    "120000001480": {
        "id": "120000001480",
        "slug": "prime-vbadge",
        "icon_id": "Icon_FF_Prime_VBadge_Aura",
        "name": "Prime Aura V-Badge",
        "title": "Prime Radiant Influencer V-Badge",
        "category": "Special Editions",
        "category_slug": "special",
        "file": "vbadge_prime.png",
        "glowColor": "rgba(255, 215, 0, 0.9)",
        "hexGlow": "#FFD700",
        "priority": 400,
        "defaultSize": 36,
        "rarity": "MYTHIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Supreme Gold V-Badge surrounded with high-energy radiant lightning corona aura.",
        "fn_call": "generate_ff_badge_html('vbadge_prime.png', 'Prime Aura V-Badge', 'rgba(255, 215, 0, 0.9)', 'top-right', '36px');"
    },
    "special-badge-8": {
        "id": "120000001580",
        "slug": "special-badge-8",
        "icon_id": "Icon_FF_Badge_Level8_Special",
        "name": "Special Level 8 Elite",
        "title": "Special Tier Level 8 Emblem",
        "category": "Special Editions",
        "category_slug": "special",
        "file": "icon_badge_8.png",
        "glowColor": "rgba(234, 179, 8, 0.85)",
        "hexGlow": "#EAB308",
        "priority": 350,
        "defaultSize": 32,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Special tier level 8 commemorative golden emblem badge.",
        "fn_call": "generate_ff_badge_html('icon_badge_8.png', 'Special Level 8 Elite', 'rgba(234, 179, 8, 0.85)', 'in-line', '32px');"
    },
    "120000001580": {
        "id": "120000001580",
        "slug": "special-badge-8",
        "icon_id": "Icon_FF_Badge_Level8_Special",
        "name": "Special Level 8 Elite",
        "title": "Special Tier Level 8 Emblem",
        "category": "Special Editions",
        "category_slug": "special",
        "file": "icon_badge_8.png",
        "glowColor": "rgba(234, 179, 8, 0.85)",
        "hexGlow": "#EAB308",
        "priority": 350,
        "defaultSize": 32,
        "rarity": "EPIC",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Special tier level 8 commemorative golden emblem badge.",
        "fn_call": "generate_ff_badge_html('icon_badge_8.png', 'Special Level 8 Elite', 'rgba(234, 179, 8, 0.85)', 'in-line', '32px');"
    },
    "rank-token": {
        "id": "120000001680",
        "slug": "rank-token",
        "icon_id": "Icon_FF_Rank_Token_Exchange",
        "name": "Rank Token",
        "title": "Ranked Season Exchange Token",
        "category": "Exchange Tokens",
        "category_slug": "tokens",
        "file": "FF_UI_Badge_ESPORTS01.png",
        "glowColor": "rgba(59, 130, 246, 0.85)",
        "hexGlow": "#3B82F6",
        "priority": 300,
        "defaultSize": 34,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Official Free Fire ranked match token used to exchange for elite rewards in the Rank Store.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_ESPORTS01.png', 'Rank Token', 'rgba(59, 130, 246, 0.85)', 'in-line', '34px');"
    },
    "120000001680": {
        "id": "120000001680",
        "slug": "rank-token",
        "icon_id": "Icon_FF_Rank_Token_Exchange",
        "name": "Rank Token",
        "title": "Ranked Season Exchange Token",
        "category": "Exchange Tokens",
        "category_slug": "tokens",
        "file": "FF_UI_Badge_ESPORTS01.png",
        "glowColor": "rgba(59, 130, 246, 0.85)",
        "hexGlow": "#3B82F6",
        "priority": 300,
        "defaultSize": 34,
        "rarity": "RARE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Official Free Fire ranked match token used to exchange for elite rewards in the Rank Store.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_ESPORTS01.png', 'Rank Token', 'rgba(59, 130, 246, 0.85)', 'in-line', '34px');"
    },
    "cs-cup-voucher": {
        "id": "120000001780",
        "slug": "cs-cup-voucher",
        "icon_id": "Icon_FF_CupMatch_cscup",
        "name": "Clash Squad Cup Upgrade Voucher",
        "title": "Clash Squad Cup Advanced Reward Voucher",
        "category": "Misc",
        "category_slug": "misc",
        "file": "FF_UI_Badge_KOL03.png",
        "glowColor": "rgba(168, 85, 247, 0.85)",
        "hexGlow": "#A855F7",
        "priority": 250,
        "defaultSize": 34,
        "rarity": "PURPLE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Use this voucher to upgrade to the Advanced rewards in the Clash Squad Championship Cup.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_KOL03.png', 'Clash Squad Cup Upgrade Voucher', 'rgba(168, 85, 247, 0.85)', 'in-line', '34px');"
    },
    "120000001780": {
        "id": "120000001780",
        "slug": "cs-cup-voucher",
        "icon_id": "Icon_FF_CupMatch_cscup",
        "name": "Clash Squad Cup Upgrade Voucher",
        "title": "Clash Squad Cup Advanced Reward Voucher",
        "category": "Misc",
        "category_slug": "misc",
        "file": "FF_UI_Badge_KOL03.png",
        "glowColor": "rgba(168, 85, 247, 0.85)",
        "hexGlow": "#A855F7",
        "priority": 250,
        "defaultSize": 34,
        "rarity": "PURPLE",
        "patch": "OB55",
        "type": "image",
        "prime_level": null,
        "description": "Use this voucher to upgrade to the Advanced rewards in the Clash Squad Championship Cup.",
        "fn_call": "generate_ff_badge_html('FF_UI_Badge_KOL03.png', 'Clash Squad Cup Upgrade Voucher', 'rgba(168, 85, 247, 0.85)', 'in-line', '34px');"
    }
};

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

            // Authentic Free Fire Prime System Renderer
            if (b.type === 'prime_image' || b.prime_level) {
                const lvl = b.prime_level || 1;
                const imgUrl = this.getImageUrl(b.file);
                const hasGlint = (lvl >= 7);
                const hasShine = (lvl >= 4);
                const hasStars = (lvl >= 4);
                const hasWingFlares = (lvl >= 4);

                let wingFlaresHtml = '';
                if (hasWingFlares) {
                    wingFlaresHtml = '  <div class="ff-side-wing-flare left"></div>
' +
                                     '  <div class="ff-side-wing-flare right"></div>
';
                }

                let starsHtml = '';
                if (hasStars) {
                    let starList = [];
                    if (lvl === 8) {
                        starList = ['star-pos-1', 'star-pos-2', 'star-pos-3', 'star-pos-4', 'star-pos-5', 'star-pos-6', 'star-pos-7', 'star-pos-8'];
                    } else if (lvl === 7) {
                        starList = ['star-pos-1', 'star-pos-2', 'star-pos-3', 'star-pos-4', 'star-pos-6', 'star-pos-7'];
                    } else if (lvl === 6) {
                        starList = ['star-pos-1', 'star-pos-2', 'star-pos-4', 'star-pos-5', 'star-pos-6'];
                    } else if (lvl === 5) {
                        starList = ['star-pos-1', 'star-pos-2', 'star-pos-6', 'star-pos-7'];
                    } else if (lvl === 4) {
                        starList = ['star-pos-1', 'star-pos-2'];
                    }
                    const starDots = starList.map(pos => '<svg class="ff-pinpoint-star ' + pos + '" viewBox="0 0 24 24"><path d="M12 0 L14.2 9.2 L24 12 L14.2 14.8 L12 24 L9.8 14.8 L0 12 L9.8 9.2 Z" fill="#FFF8B0"/><circle cx="12" cy="12" r="2.5" fill="#FFFFFF"/></svg>').join('');
                    starsHtml = '  <div class="ff-prime-stars-layer">' + starDots + '</div>
';
                }

                let shineHtml = '';
                if (hasShine) {
                    shineHtml = '  <span class="ff-badge-shine" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url(\'' + imgUrl + '\');mask-image:url(\'' + imgUrl + '\');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
' +
                                '    <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;background:linear-gradient(45deg,transparent 35%,rgba(255,255,255,0.22) 44%,rgba(255,255,255,0.98) 50%,rgba(255,255,255,0.22) 56%,transparent 65%);animation:ff_shape_sweep ' + speed + ' infinite linear;mix-blend-mode:screen;display:block;"></span>
' +
                                '  </span>
';
                }

                return '<div class="ff-prime-badge-wrap ff-prime-lvl-' + lvl + '" style="' + posStyle + 'width:' + size + ';height:' + size + ';user-select:none;" title="' + b.title + '">
' +
                       wingFlaresHtml +
                       starsHtml +
                       '  <img src="' + imgUrl + '" class="ff-badge-img" alt="' + b.name + '">
' +
                       shineHtml +
                       (hasGlint ? '  <div class="ff-gem-glint"></div>
' : '') +
                       '</div>';
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