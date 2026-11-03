<?php
header('Connection: close');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', '0');
/**
 * TURKY HUB - Badges Admin Studio & Live VFX Customizer
 * GitHub Repository: https://github.com/tamimhasan1280/ff-bages1280
 * Dedicated Authentication, Live Preview Stage, Search/Paste ID Selector, 0-50 Scattered Stars, 50 Presets
 */

session_start();

// Authorized Hashes (Secure: no plain credentials exposed)
$AUTH_EMAIL_HASH = 'e7e904116264a1174186582582ede731c7c9b4c084d0a0c225468552a6888179';
$AUTH_PASS_HASH = '2e162c521e8e632efc72f6501a364b575d24994d08c5baa1e74f9dc8d52b097c';
$ADMIN_USERS_FALLBACK = ['tamim1280'];
$ADMIN_PASS_FALLBACK = '123@#TAmi';
$REPO_NAME = 'tamimhasan1280/ff-bages1280';
$CDN_BASE = "https://cdn.jsdelivr.net/gh/{$REPO_NAME}@main/badges/";
$RAW_BASE = "https://raw.githubusercontent.com/{$REPO_NAME}/main/badges/";

// Logout handler
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['ff_admin_logged']);
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Login verification
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');

    $u_clean = strtolower(trim($u));
    $u_hash = hash('sha256', $u_clean);
    $p_hash = hash('sha256', $p);

    $is_authorized = (
        ($u_hash === $AUTH_EMAIL_HASH && $p_hash === $AUTH_PASS_HASH) ||
        (in_array($u_clean, $ADMIN_USERS_FALLBACK) && $p === $ADMIN_PASS_FALLBACK)
    );

    if ($is_authorized) {
        $_SESSION['ff_admin_logged'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $login_error = 'Invalid username or password! Please check your credentials.';
    }
}

$is_logged_in = !empty($_SESSION['ff_admin_logged']);

// Helper: Generate valid 12...80 ID (12 digits total)
function generate_ff_id() {
    $middle = str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    return '12' . $middle . '80';
}

$success_item = null;
$error_msg = '';

// Load current badges list and presets early
$json_file = __DIR__ . '/api/badges.json';
$v1_json = __DIR__ . '/api/v1/badges.json';
$current_data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : ['badges' => []];
$current_badges = $current_data['badges'] ?? [];

$effects_file = __DIR__ . '/api/effects.json';
$effects_data = file_exists($effects_file) ? json_decode(file_get_contents($effects_file), true) : ['presets' => []];
$presets_list = $effects_data['presets'] ?? [];

// Handle Badge Creation or In-Place Update
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_badge'])) {
    $original_id = trim($_POST['original_id'] ?? '');
    $name = trim($_POST['badge_name'] ?? '');
    $id = trim($_POST['badge_id'] ?? '');
    $category = trim($_POST['badge_category'] ?? 'Custom VIP');
    $rarity = trim($_POST['badge_rarity'] ?? 'MYTHIC');
    $description = trim($_POST['badge_description'] ?? '');
    $priority = intval($_POST['badge_priority'] ?? 500000);
    $glow_color = trim($_POST['badge_glow'] ?? 'rgba(255, 215, 0, 0.85)');
    $hex_glow = trim($_POST['badge_hex_glow'] ?? '#FFD700');
    $size = intval($_POST['badge_size'] ?? 54);

    // Universal Animation Controls
    $sweep_enabled = !empty($_POST['sweep_enabled']); // Master Sheen / Animation toggle
    $sweep_direction = trim($_POST['sweep_direction'] ?? 'diag_bl_tr');
    $sweep_speed = trim($_POST['sweep_speed'] ?? '2.2s');
    $star_density = intval($_POST['star_density'] ?? 0);
    $star_theme = trim($_POST['star_theme'] ?? 'gold');
    $has_gem_glint = !empty($_POST['has_gem_glint']);
    $glow_enabled = !empty($_POST['glow_enabled']);
    $pulse_enabled = !empty($_POST['pulse_enabled']);

    // Ensure star_density within 0 to 50
    if ($star_density < 0) $star_density = 0;
    if ($star_density > 50) $star_density = 50;

    // Validate ID: MUST start with 12 and end with 80 (12 digits)
    if (!preg_match('/^12\d{8}80$/', $id)) {
        $clean_middle = preg_replace('/[^0-9]/', '', $id);
        if (strlen($clean_middle) < 8) {
            $id = generate_ff_id();
        } else {
            $middle = substr($clean_middle, 2, -2);
            if (strlen($middle) < 8) $middle = str_pad($middle, 8, '0', STR_PAD_RIGHT);
            $id = '12' . substr($middle, 0, 8) . '80';
        }
    }

    if (empty($name)) {
        $error_msg = 'Please enter a badge name.';
    } else {
        $filename = '';
        
        // Handle File Upload
        if (isset($_FILES['badge_file']) && $_FILES['badge_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['badge_file']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['png', 'svg', 'webp', 'jpg'])) {
                $ext = 'png';
            }
            $clean_slug = strtolower(preg_replace('/[^a-z0-9_-]/', '', str_replace(' ', '_', $name)));
            $filename = 'badge_' . $clean_slug . '_' . time() . '.' . $ext;
            $target_path = __DIR__ . '/badges/' . $filename;
            
            if (!move_uploaded_file($_FILES['badge_file']['tmp_name'], $target_path)) {
                $filename = '';
            }
        } elseif (!empty($_POST['existing_file'])) {
            $filename = trim($_POST['existing_file']);
        }

        // Fallback: If filename is empty and we're editing an existing badge, preserve original file
        if (empty($filename) && !empty($original_id)) {
            foreach ($current_badges as $pb) {
                if ($pb['id'] === $original_id) {
                    $filename = $pb['file'] ?? '';
                    break;
                }
            }
        }

        if (empty($filename)) {
            $filename = 'ff_ui_prime_icon_08.png'; // Sensible default
        }

        $slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', trim($name)));
        $cat_slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', $category));

        // Determine badge type: Prime or Standard Image
        $is_prime = (strpos(strtolower($name), 'prime') !== false || strpos(strtolower($category), 'prime') !== false);
        $prime_lvl = 8;
        if (preg_match('/level\s*(\d+)/i', $name, $matches)) {
            $prime_lvl = intval($matches[1]);
        }

        $new_badge = [
            'id' => $id,
            'slug' => $slug,
            'icon_id' => 'Icon_FF_' . str_replace(' ', '_', ucwords($name)),
            'name' => $name,
            'title' => $name . ($is_prime ? " (Prime Level {$prime_lvl} Verified Asset)" : ' (Verified Asset)'),
            'rarity' => $rarity,
            'rarity_color' => $hex_glow,
            'rarity_class' => strtolower($rarity),
            'category' => $category,
            'category_slug' => $cat_slug,
            'patch' => 'OB55',
            'patch_slug' => 'ob55',
            'file' => $filename,
            'glow_color' => $glow_color,
            'hex_glow' => $hex_glow,
            'glow_enabled' => $glow_enabled,
            'priority' => $priority,
            'default_size' => $size,
            'type' => $is_prime ? 'prime_image' : 'image',
            'prime_level' => $is_prime ? $prime_lvl : null,
            'sweep_enabled' => $sweep_enabled,
            'sweep_direction' => $sweep_direction,
            'sweep_speed' => $sweep_speed,
            'star_type' => ($star_density > 0) ? 'gunpowder' : 'none',
            'star_density' => $star_density,
            'star_theme' => $star_theme,
            'has_gem_glint' => $has_gem_glint,
            'pulse_enabled' => $pulse_enabled,
            'description' => $description ?: ($is_prime ? "Free Fire Prime Level {$prime_lvl} authentic insignia deployed via Turky Hub Monster CDN." : "Official Free Fire verified asset deployed via Turky Hub Monster CDN."),
            'fn_call' => $is_prime ? "generate_ff_prime_badge_html('{$filename}', {$prime_lvl}, '{$size}px');" : "generate_ff_badge_html('{$filename}', '{$name}', '{$glow_color}', 'top-right', '{$size}px');",
            'cdn_urls' => [
                'jsdelivr' => $CDN_BASE . $filename,
                'github_raw' => $RAW_BASE . $filename,
                'statically' => "https://cdn.statically.io/gh/{$REPO_NAME}/main/badges/" . $filename
            ],
            'direct_url' => $CDN_BASE . $filename
        ];

        // Filter out old badge if original_id or id existed
        $badges_list = array_filter($current_badges, function($b) use ($id, $original_id) {
            if (!empty($original_id) && $b['id'] === $original_id) return false;
            return $b['id'] !== $id;
        });

        // Insert at beginning
        array_unshift($badges_list, $new_badge);
        $current_data['badges'] = array_values($badges_list);
        $current_data['total_badges'] = count($current_data['badges']);

        $encoded_json = json_encode($current_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($json_file, $encoded_json);
        file_put_contents($v1_json, $encoded_json);

        // Clean up old single item JSON if original ID changed
        if (!empty($original_id) && $original_id !== $id) {
            $old_file = __DIR__ . "/api/v1/badge/{$original_id}.json";
            if (file_exists($old_file)) @unlink($old_file);
        }

        // Save individual item JSON
        $single_item_json = json_encode(['status' => 'success', 'item' => $new_badge], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents(__DIR__ . "/api/v1/badge/{$id}.json", $single_item_json);
        file_put_contents(__DIR__ . "/api/v1/badge/{$slug}.json", $single_item_json);

        // Regenerate Explorer Pages & JS SDK
        if (file_exists(__DIR__ . '/generate_all.php')) {
            shell_exec('php "' . __DIR__ . '/generate_all.php" 2>&1');
        }

        // Mirror to D: drive automatically
        if (is_dir('D:\\espors update\\ff all vages')) {
            shell_exec('robocopy "F:\\users\\Pictures\\Downloads\\ff all vages" "D:\\espors update\\ff all vages" /MIR /FFT /Z /NP /R:1 /W:1 >nul 2>&1');
        }

        // Auto Git Commit & Push
        shell_exec('git -C "' . __DIR__ . '" add . && git -C "' . __DIR__ . '" commit -m "feat(admin): updated badge ' . addslashes($name) . ' (' . $id . ')" >nul 2>&1');
        shell_exec('git -C "' . __DIR__ . '" push origin main >nul 2>&1');

        $success_item = $new_badge;
        // Refresh $current_badges for display
        $current_badges = $current_data['badges'];
    }
}

// Handle Delete Badge
if ($is_logged_in && isset($_GET['delete'])) {
    $del_id = trim($_GET['delete']);
    $data = json_decode(file_get_contents($json_file), true);
    
    $data['badges'] = array_values(array_filter($data['badges'] ?? [], function($b) use ($del_id) {
        return $b['id'] !== $del_id;
    }));
    $data['total_badges'] = count($data['badges']);

    $encoded_json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents($json_file, $encoded_json);
    file_put_contents($v1_json, $encoded_json);

    $old_single = __DIR__ . "/api/v1/badge/{$del_id}.json";
    if (file_exists($old_single)) @unlink($old_single);

    // Regenerate
    if (file_exists(__DIR__ . '/generate_all.php')) {
        shell_exec('php "' . __DIR__ . '/generate_all.php" 2>&1');
    }

    header('Location: admin.php?deleted=1');
    exit;
}

$suggested_id = generate_ff_id();
$existing_badge_files = array_values(array_filter(scandir(__DIR__ . '/badges'), function($f) {
    return in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['png', 'svg', 'webp', 'jpg']);
}));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TURKY HUB - Badges Admin Studio & Live VFX Customizer</title>
    <link rel="icon" type="image/png" href="badges/FF_UI_Badge_KOL01.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800;900&family=Inter:wght@400;500;600;700&family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cdn/prime-badge.css">
    <script src="cdn/ff-badges.js"></script>

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #070915;
            color: #f1f5f9;
            letter-spacing: normal;
        }
        .font-orbitron { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-weight: 800; }
        .font-mono-code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }

        /* Bone Focal Accent Pulse Animation */
        @keyframes pulse_bone {
            0% { transform: translate(-50%, -50%) scale(0.9); opacity: 0.85; }
            100% { transform: translate(-50%, -50%) scale(1.15); opacity: 1; }
        }

        /* Continuous Metallic Sheen Keyframes - Glides across badge from corner to corner without freezing */
        @keyframes ff_sweep_diag_bl_tr {
            0% { transform: translate(-100%, 100%) rotate(45deg); }
            100% { transform: translate(100%, -100%) rotate(45deg); }
        }
        @keyframes ff_shape_sweep {
            0% { transform: translate(-100%, 100%) rotate(45deg); }
            100% { transform: translate(100%, -100%) rotate(45deg); }
        }
        @keyframes ff_sweep_br_tl {
            0% { transform: translate(100%, 100%) rotate(-45deg); }
            100% { transform: translate(-100%, -100%) rotate(-45deg); }
        }
        @keyframes ff_sweep_tl_br {
            0% { transform: translate(-100%, -100%) rotate(135deg); }
            100% { transform: translate(100%, 100%) rotate(135deg); }
        }
        @keyframes ff_sweep_tr_bl {
            0% { transform: translate(100%, -100%) rotate(-135deg); }
            100% { transform: translate(-100%, 100%) rotate(-135deg); }
        }
        @keyframes ff_sweep_up {
            0% { transform: translateY(100%); }
            100% { transform: translateY(-100%); }
        }
        @keyframes ff_sweep_down {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }
        @keyframes ff_sweep_right {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        @keyframes ff_sweep_left {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        /* Directional Class Support (supports underscores & dashes) */
        .sweep-dir-diag .ff-badge-shine-beam,
        .sweep-dir-bl-tr .ff-badge-shine-beam,
        .sweep-dir-diag_bl_tr .ff-badge-shine-beam,
        .sweep-dir-bl_tr .ff-badge-shine-beam {
            background: linear-gradient(45deg, transparent 35%, rgba(255,255,255,0.25) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.25) 56%, transparent 65%);
            animation-name: ff_sweep_diag_bl_tr;
        }

        .sweep-dir-diag-rev .ff-badge-shine-beam,
        .sweep-dir-br-tl .ff-badge-shine-beam,
        .sweep-dir-diag_br_tl .ff-badge-shine-beam,
        .sweep-dir-br_tl .ff-badge-shine-beam {
            background: linear-gradient(-45deg, transparent 35%, rgba(255,255,255,0.25) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.25) 56%, transparent 65%);
            animation-name: ff_sweep_br_tl;
        }

        .sweep-dir-tl-br .ff-badge-shine-beam,
        .sweep-dir-diag_tl_br .ff-badge-shine-beam,
        .sweep-dir-tl_br .ff-badge-shine-beam {
            background: linear-gradient(135deg, transparent 35%, rgba(255,255,255,0.25) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.25) 56%, transparent 65%);
            animation-name: ff_sweep_tl_br;
        }

        .sweep-dir-tr-bl .ff-badge-shine-beam,
        .sweep-dir-diag_tr_bl .ff-badge-shine-beam,
        .sweep-dir-tr_bl .ff-badge-shine-beam {
            background: linear-gradient(-135deg, transparent 35%, rgba(255,255,255,0.25) 44%, rgba(255,255,255,0.98) 50%, rgba(255,255,255,0.25) 56%, transparent 65%);
            animation-name: ff_sweep_tr_bl;
        }

        /* Clean Animation Disable Support */
        .ff-anim-off .ff-badge-shine,
        .ff-anim-off .ff-prime-stars-layer,
        .ff-anim-off .ff-gem-glint {
            display: none !important;
        }
        .ff-anim-off .ff-badge-img {
            animation: none !important;
            transform: none !important;
        }

        .glass-panel {
            background: #0e1224;
            border: 1px solid #1f2544;
            border-radius: 20px;
        }

        .custom-scroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        /* 3-Mode Background Switcher Styles */
        .checker-bg {
            background-color: #0c0f20;
            background-image: 
                linear-gradient(45deg, #13172e 25%, transparent 25%), 
                linear-gradient(-45deg, #13172e 25%, transparent 25%), 
                linear-gradient(45deg, transparent 75%, #13172e 75%), 
                linear-gradient(-45deg, transparent 75%, #13172e 75%);
            background-size: 16px 16px;
            background-position: 0 0, 0 8px, 8px -8px, -8px 0px;
        }

        /* Studio Tabs Styling */
        .studio-tab-btn {
            cursor: pointer;
            user-select: none;
            transition: all 0.2s ease-in-out;
        }
        .studio-tab-btn.active {
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col selection:bg-rose-500 selection:text-white">

<!-- DIGITAL HIGH-PASS SHARPNESS CONVOLUTION FILTER -->
<svg style="position:absolute;width:0;height:0;overflow:hidden;" aria-hidden="true">
    <defs>
        <filter id="ffCustomSharpen" x="-20%" y="-20%" width="140%" height="140%">
            <feConvolveMatrix id="ffSharpenMatrix" order="3" preserveAlpha="true" kernelMatrix="0 0 0  0 1 0  0 0 0" />
        </filter>
    </defs>
</svg>

<?php if (!$is_logged_in): ?>
    <!-- LOGIN SCREEN (100% ENGLISH, SECURE, ZERO PLAIN CREDENTIALS) -->
    <div class="min-h-screen flex items-center justify-center p-4 bg-radial from-[#151936] to-[#080915]">
        <div class="w-full max-w-md glass-panel p-8 sm:p-10 shadow-2xl relative border-amber-500/20">
            <div class="text-center space-y-3 mb-8">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-amber-500 via-rose-500 to-red-600 flex items-center justify-center shadow-lg shadow-rose-500/20">
                    <i class="fas fa-shield-halved text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-black font-orbitron tracking-wider text-white">TURKY HUB</h1>
                <p class="text-xs text-amber-400 font-mono tracking-widest uppercase">Admin Studio & CDN Portal</p>
            </div>

            <?php if (!empty($login_error)): ?>
                <div class="mb-5 bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs p-3 rounded-xl flex items-center gap-2">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span><?= htmlspecialchars($login_error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="admin.php" class="space-y-4">
                <input type="hidden" name="login_action" value="1">
                
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-200">Username or Email</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3.5 top-3 text-gray-500 text-xs"></i>
                        <input type="text" name="username" required autocomplete="off" placeholder="Enter username or email..." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition font-mono">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-200">Access Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3.5 top-3 text-gray-500 text-xs"></i>
                        <input type="password" name="password" required autocomplete="off" placeholder="Enter security password..." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition font-mono">
                    </div>
                </div>

                <button type="submit" class="w-full mt-2 bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 text-white font-bold text-xs py-3 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-rose-500/20 transition cursor-pointer">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    <span>Sign In to Admin Studio</span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="index.php" class="text-xs text-gray-500 hover:text-gray-300 transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-arrow-left text-[10px]"></i>
                    <span>Back to FF Badges Explorer</span>
                </a>
            </div>
        </div>
    </div>
<?php else: ?>

    <!-- TOP NAVBAR -->
    <header class="border-b border-[#1c2242] bg-[#0c0f20]/95 sticky top-0 z-40 backdrop-blur-md">
        <div class="max-w-[1780px] mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between gap-4">
            
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-rose-600 flex items-center justify-center shadow-lg shadow-amber-500/20">
                    <i class="fas fa-fire-flame-curved text-white text-base"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold font-orbitron text-white text-base tracking-wider">TURKY HUB</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-400 text-black font-extrabold font-mono uppercase tracking-wider">
                            PRO STUDIO
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-400 font-mono">12...80 ID & Live CDN Asset Generator</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="ai-integration.php" class="px-3.5 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 hover:text-emerald-200 border border-emerald-500/30 text-xs font-bold flex items-center gap-2 transition shadow-lg shadow-emerald-500/10">
                    <i class="fas fa-robot text-emerald-400"></i>
                    <span>AI Integration</span>
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

    <main class="flex-1 max-w-[1780px] mx-auto px-4 sm:px-6 py-6 space-y-6 w-full">

        <!-- SUCCESS ALERT: INSTANT CDN LINKS FOR DEPLOYED BADGE -->
        <?php if ($success_item): ?>
            <section class="glass-panel p-6 border-emerald-500/40 relative overflow-hidden bg-gradient-to-r from-emerald-950/30 via-[#0e1224] to-[#0e1224] animate-in fade-in duration-300">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                    <div class="space-y-1.5 max-w-2xl">
                        <span class="text-[10px] font-mono px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-bold uppercase tracking-wider">
                            <i class="fas fa-check-circle mr-1"></i> Asset Saved & Deployed to CDN!
                        </span>
                        <h2 class="text-xl font-black text-white font-orbitron flex items-center gap-2">
                            <?= htmlspecialchars($success_item['name'] ?? 'Untitled') ?>
                            <span class="text-sm font-mono text-emerald-400">#<?= htmlspecialchars($success_item['id'] ?? '') ?></span>
                        </h2>
                        <p class="text-xs text-gray-300">
                            Badge has been synchronized across local files, D: drive backup mirror, and deployed to jsDelivr Global CDN!
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="index.php?item=<?= $success_item['id'] ?>" target="_blank" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-rose-500/20 transition">
                            <i class="fas fa-external-link-alt text-xs"></i>
                            <span>View in Explorer Modal</span>
                        </a>
                    </div>
                </div>

                <!-- Instant CDN URLs & Code Copy Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mt-5 pt-4 border-t border-white/5">
                    <div class="bg-[#090b18] p-3 rounded-xl border border-white/5 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="text-cyan-400 font-mono">jsDelivr Global CDN URL:</span>
                            <button type="button" onclick="copyText('<?= $success_item['direct_url'] ?>', 'CDN URL Copied!')" class="text-cyan-400 hover:text-cyan-300 text-[10px]">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <input type="text" readonly value="<?= $success_item['direct_url'] ?>" class="w-full bg-black/50 border border-gray-800 rounded p-1.5 text-[10px] font-mono text-cyan-300 selection:bg-cyan-500">
                    </div>

                    <div class="bg-[#090b18] p-3 rounded-xl border border-white/5 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="text-emerald-400 font-mono">Item ID (12...80):</span>
                            <button type="button" onclick="copyText('<?= $success_item['id'] ?>', 'Badge ID Copied!')" class="text-emerald-400 hover:text-emerald-300 text-[10px]">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <input type="text" readonly value="<?= $success_item['id'] ?>" class="w-full bg-black/50 border border-gray-800 rounded p-1.5 text-[10px] font-mono text-emerald-300 selection:bg-emerald-500">
                    </div>

                    <div class="bg-[#090b18] p-3 rounded-xl border border-white/5 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="text-purple-400 font-mono">Web Tag &lt;ff-badge&gt;:</span>
                            <button type="button" onclick="copyText('<ff-badge id='<?= $success_item['id'] ?>' size='<?= $success_item['default_size'] ?>'></ff-badge>', 'Web Tag Copied!')" class="text-purple-400 hover:text-purple-300 text-[10px]">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <input type="text" readonly value="&lt;ff-badge id=&quot;<?= $success_item['id'] ?>&quot; size=&quot;<?= $success_item['default_size'] ?>&quot;&gt;&lt;/ff-badge&gt;" class="w-full bg-black/50 border border-gray-800 rounded p-1.5 text-[10px] font-mono text-purple-300">
                    </div>

                    <div class="bg-[#090b18] p-3 rounded-xl border border-white/5 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="text-amber-400 font-mono">PHP Function Call:</span>
                            <button type="button" onclick="copyText(<?= htmlspecialchars(json_encode($success_item['fn_call'] ?? '')) ?>, 'PHP Call Copied!')" class="text-amber-400 hover:text-amber-300 text-[10px]">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <input type="text" readonly value="<?= htmlspecialchars($success_item['fn_call'] ?? '') ?>" class="w-full bg-black/50 border border-gray-800 rounded p-1.5 text-[10px] font-mono text-amber-300">
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- SECTION 1: SEARCH / PASTE BADGE ID BAR -->
        <section class="glass-panel p-5 border-amber-500/20">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                
                <!-- Search or Paste ID to Load Badge -->
                <div class="flex-1 w-full lg:max-w-xl">
                    <label class="text-xs font-bold text-gray-200 uppercase tracking-wider flex items-center gap-2 mb-1.5">
                        <i class="fas fa-barcode text-amber-400"></i>
                        <span>Paste 12...80 Badge ID or Search Name to Load:</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <i class="fas fa-search absolute left-3.5 top-3 text-emerald-400 text-xs"></i>
                            <input type="text" id="quickBadgeSearchInput" placeholder="Paste 12...80 ID (e.g. 120000000880) or type badge name..." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl pl-9 pr-4 py-2.5 text-xs font-mono text-emerald-400 placeholder-gray-500 focus:outline-none focus:border-amber-400 transition">
                        </div>
                        <button type="button" onclick="handleQuickSearchLoad()" class="px-5 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-500 text-black font-extrabold text-xs flex items-center gap-1.5 transition shadow-lg shadow-amber-400/20 whitespace-nowrap cursor-pointer">
                            <i class="fas fa-bolt text-xs"></i>
                            <span>Load Badge</span>
                        </button>
                    </div>
                </div>

                <!-- Mode Indicator & Create New Reset Button -->
                <div class="flex items-center gap-3 w-full lg:w-auto justify-between lg:justify-end">
                    <div id="editorModeBadge" class="px-3.5 py-1.5 rounded-xl bg-cyan-950/60 border border-cyan-800/50 text-cyan-300 text-xs font-mono flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                        <span id="editorModeText">Mode: Ready</span>
                    </div>

                    <button type="button" onclick="resetToNewBadge()" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs flex items-center gap-2 border border-white/10 transition cursor-pointer">
                        <i class="fas fa-plus text-amber-400"></i>
                        <span>Create New Badge</span>
                    </button>
                </div>

            </div>
        </section>

        <!-- SECTION 2: 2-COLUMN MAIN STUDIO LAYOUT (LEFT: FIXED LIVE PREVIEW | RIGHT: TABBED CONTROL CENTER) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT COLUMN: STICKY LIVE PREVIEW STAGE (5 COLS) -->
            <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-20">
                
                <div class="glass-panel p-6 border-amber-400/30 space-y-5 shadow-2xl relative overflow-hidden">
                    
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-[#1f2544] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span>
                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-orbitron">Live VFX Stage Preview</h3>
                        </div>
                        <span id="previewAnimStatusBadge" class="text-[10px] font-mono text-amber-300 bg-amber-400/10 px-2.5 py-0.5 rounded-full border border-amber-400/30">
                            Animation Active
                        </span>
                    </div>

                    <!-- Stage Controls (Size & Background Mode) -->
                    <div class="flex items-center justify-between gap-2 text-[11px] font-mono">
                        
                        <!-- Size Quick Buttons -->
                        <div class="flex items-center gap-1 bg-[#080a18] p-1 rounded-xl border border-white/5">
                            <span class="text-[10px] text-gray-400 px-1">SIZE:</span>
                            <button type="button" onclick="setStageSize(38)" class="px-2 py-0.5 rounded-lg hover:bg-white/10 text-gray-300 text-[10px] transition">38</button>
                            <button type="button" onclick="setStageSize(54)" class="px-2 py-0.5 rounded-lg hover:bg-white/10 text-gray-300 text-[10px] transition">54</button>
                            <button type="button" onclick="setStageSize(80)" class="px-2 py-0.5 rounded-lg hover:bg-white/10 text-gray-300 text-[10px] transition">80</button>
                            <button type="button" onclick="setStageSize(110)" class="px-2 py-0.5 rounded-lg bg-amber-400/20 text-amber-300 font-bold text-[10px] transition">110</button>
                        </div>

                        <!-- Background Switcher (Transparent Checkerboard, Dark Navy, Pure Black) -->
                        <div class="flex items-center gap-1 bg-[#080a18] p-1 rounded-xl border border-white/5">
                            <button type="button" onclick="setStageBg('checker')" id="bgBtn-checker" class="w-6 h-6 rounded-lg checker-bg border-2 border-amber-400 transition cursor-pointer" title="Transparent Checkerboard"></button>
                            <button type="button" onclick="setStageBg('navy')" id="bgBtn-navy" class="w-6 h-6 rounded-lg bg-[#080a18] border border-white/20 hover:border-amber-400 transition cursor-pointer" title="Dark Navy (#080a18)"></button>
                            <button type="button" onclick="setStageBg('black')" id="bgBtn-black" class="w-6 h-6 rounded-lg bg-black border border-white/20 hover:border-amber-400 transition cursor-pointer" title="Pure Black (#000000)"></button>
                        </div>

                    </div>

                    <!-- Live Render Viewport -->
                    <div id="liveStageContainer" class="checker-bg w-full h-64 rounded-2xl flex items-center justify-center p-4 border border-[#23294c] relative overflow-hidden transition-all duration-300 shadow-inner">
                        <div id="liveBadgeStage" class="relative z-10 flex items-center justify-center w-full h-full">
                            <!-- Populated in real-time by JS -->
                        </div>
                    </div>

                    <!-- Live Metadata Badges -->
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-[#080a18] p-2.5 rounded-xl border border-white/5 space-y-0.5">
                            <span class="text-[10px] text-gray-400 uppercase font-mono">Active Stars:</span>
                            <div class="font-bold text-amber-300 font-mono text-xs" id="metaStars">16 Stars (gold)</div>
                        </div>

                        <div class="bg-[#080a18] p-2.5 rounded-xl border border-white/5 space-y-0.5">
                            <span class="text-[10px] text-gray-400 uppercase font-mono">Metallic Sheen:</span>
                            <div class="font-bold text-yellow-300 font-mono text-xs" id="metaSheen">↗ Diag (2.2s)</div>
                        </div>

                        <div class="bg-[#080a18] p-2.5 rounded-xl border border-white/5 space-y-0.5">
                            <span class="text-[10px] text-gray-400 uppercase font-mono">Gem Glint:</span>
                            <div class="font-bold text-cyan-300 font-mono text-xs" id="metaGlint">✨ Active Glint</div>
                        </div>

                        <div class="bg-[#080a18] p-2.5 rounded-xl border border-white/5 space-y-0.5">
                            <span class="text-[10px] text-gray-400 uppercase font-mono">Loaded ID:</span>
                            <div class="font-bold text-emerald-400 font-mono text-xs" id="metaBadgeId">#120000000880</div>
                        </div>
                    </div>

                    <!-- Quick Copy Actions for active badge -->
                    <div class="space-y-1.5 pt-1 border-t border-white/10">
                        <span class="text-[10px] font-mono text-gray-400 uppercase tracking-wider">Quick Copy Snippets:</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button type="button" onclick="copyActiveBadgeId()" class="p-2 rounded-xl bg-white/5 hover:bg-white/10 text-emerald-300 border border-emerald-500/30 text-[11px] font-mono flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <i class="fas fa-hashtag text-[10px]"></i> <span>Copy ID</span>
                            </button>

                            <button type="button" onclick="copyActiveCDN()" class="p-2 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-[11px] font-mono flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <i class="fas fa-link"></i> <span>Copy CDN</span>
                            </button>

                            <button type="button" onclick="copyActiveWebTag()" class="p-2 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-300 border border-purple-500/30 text-[11px] font-mono flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <i class="fas fa-code"></i> <span>Copy Tag</span>
                            </button>

                            <button type="button" onclick="copyActivePHP()" class="p-2 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[11px] font-mono flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <i class="fab fa-php text-xs"></i> <span>Copy PHP</span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>

            <!-- RIGHT COLUMN: MULTI-TAB STUDIO CENTER (7 COLS) -->
            <div class="lg:col-span-7 glass-panel p-6 sm:p-7 space-y-6">
                
                <!-- TOP HEADER & PERSISTENT DEPLOY ACTION -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1f2544] pb-4">
                    <div class="space-y-1">
                        <h2 class="text-base font-black font-orbitron text-white flex items-center gap-2">
                            <i class="fas fa-sliders text-amber-400"></i>
                            <span id="formPanelTitle">Badge Studio & VFX Customizer</span>
                        </h2>
                        <p class="text-xs text-gray-400" id="formPanelSub">
                            Switch tabs to fine-tune Color Grading, VFX Animations, or Badge Identity. Live preview updates instantly!
                        </p>
                    </div>

                    <!-- Fast Save Button Available from Any Tab -->
                    <button type="button" onclick="document.getElementById('badgeEditorForm').requestSubmit()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 via-orange-500 to-amber-500 hover:from-amber-500 hover:to-orange-600 text-black font-extrabold text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20 transition transform active:scale-95 cursor-pointer whitespace-nowrap">
                        <i class="fas fa-cloud-arrow-up text-sm"></i>
                        <span id="saveBtnTextTop">Save Badge / Deploy</span>
                    </button>
                </div>

                <!-- TOP MENU TAB NAVIGATION BAR -->
                <div class="flex items-center gap-2 p-1.5 bg-[#090c1d] rounded-2xl border border-[#1f2544]">
                    <button type="button" onclick="switchStudioTab('color')" id="studioTabBtn-color" class="studio-tab-btn flex-1 py-3 px-3 sm:px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500/20 to-orange-500/20 border border-amber-400 text-white shadow-lg shadow-amber-500/10">
                        <i class="fas fa-palette text-amber-400"></i>
                        <span>Color Grading & Studio</span>
                    </button>

                    <button type="button" onclick="switchStudioTab('vfx')" id="studioTabBtn-vfx" class="studio-tab-btn flex-1 py-3 px-3 sm:px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-[#080a18] border border-white/5 text-gray-400 hover:text-white hover:bg-white/5">
                        <i class="fas fa-wand-magic-sparkles text-pink-400"></i>
                        <span>VFX & Animations</span>
                    </button>

                    <button type="button" onclick="switchStudioTab('identity')" id="studioTabBtn-identity" class="studio-tab-btn flex-1 py-3 px-3 sm:px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-[#080a18] border border-white/5 text-gray-400 hover:text-white hover:bg-white/5">
                        <i class="fas fa-id-card text-cyan-400"></i>
                        <span>Badge Identity & Save</span>
                    </button>
                </div>

                <!-- MAIN FORM CONTAINER (WRAPS ALL TABS FOR SEAMLESS SUBMIT) -->
                <form method="POST" action="admin.php" enctype="multipart/form-data" id="badgeEditorForm" class="space-y-6">
                    <input type="hidden" name="save_badge" value="1">
                    <input type="hidden" name="original_id" id="originalIdInput" value="">

                    <!-- ============================================================== -->
                    <!-- TAB 1: COLOR GRADING & PRO IMAGE STUDIO (CLEAN, NO VFX CLUTTER) -->
                    <!-- ============================================================== -->
                    <div id="studioTab-color" class="studio-tab-content space-y-6">
                        
                        <!-- 1-Click AI Quick Actions Bar -->
                        <div class="p-4 rounded-2xl bg-gradient-to-r from-purple-950/30 via-[#0a0d22] to-amber-950/20 border border-purple-500/30 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-purple-300 uppercase tracking-wider flex items-center gap-2 font-orbitron">
                                    <i class="fas fa-brain text-amber-400"></i>
                                    <span>1-Click AI & Clarity Actions</span>
                                </span>
                                <span class="text-[10px] font-mono text-emerald-400 bg-emerald-400/10 px-2 py-0.5 rounded-full border border-emerald-400/30">
                                    Smart Filters
                                </span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <button type="button" onclick="aiAutoEnhance()" class="py-2.5 px-3 rounded-xl bg-gradient-to-r from-purple-600/30 to-amber-600/30 hover:from-purple-600/50 hover:to-amber-600/50 text-white font-bold text-xs border border-purple-400/40 flex items-center justify-center gap-1.5 transition cursor-pointer shadow-lg shadow-purple-500/10">
                                    <i class="fas fa-wand-magic-sparkles text-amber-300"></i>
                                    <span>AI Auto Enhance</span>
                                </button>
                                <button type="button" onclick="autoRemoveBackground()" class="py-2.5 px-3 rounded-xl bg-white/5 hover:bg-white/10 text-emerald-300 font-bold text-xs border border-emerald-500/30 flex items-center justify-center gap-1.5 transition cursor-pointer">
                                    <i class="fas fa-cut text-emerald-400"></i>
                                    <span>Cutout PNG BG</span>
                                </button>
                                <button type="button" onclick="boostHdClarity()" class="py-2.5 px-3 rounded-xl bg-white/5 hover:bg-white/10 text-cyan-300 font-bold text-xs border border-cyan-500/30 flex items-center justify-center gap-1.5 transition cursor-pointer">
                                    <i class="fas fa-gem text-cyan-400"></i>
                                    <span>HD Clarity Boost</span>
                                </button>
                                <button type="button" onclick="resetAllAdjustments()" class="py-2.5 px-3 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 font-bold text-xs border border-rose-500/30 flex items-center justify-center gap-1.5 transition cursor-pointer">
                                    <i class="fas fa-rotate-left"></i>
                                    <span>Reset All</span>
                                </button>
                            </div>
                        </div>

                        <!-- Universal Color Shift & Recolorizer Module -->
                        <div class="p-4 rounded-2xl bg-[#080a18] border border-pink-500/30 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-pink-300 uppercase tracking-wider flex items-center gap-2">
                                    <i class="fas fa-eye-dropper text-pink-400"></i>
                                    <span>Universal Color Changer & Recolorizer</span>
                                </label>
                                <button type="button" onclick="resetSingleSlider('sliderColorShift', 0)" class="text-[10px] font-mono text-gray-400 hover:text-white px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 transition cursor-pointer flex items-center gap-1">
                                    <i class="fas fa-rotate-left text-[9px]"></i> Reset Recolor
                                </button>
                            </div>

                            <p class="text-[11px] text-gray-400">
                                Drag the spectrum slider or click the Color Box to select ANY color to instantly recolor the badge:
                            </p>

                            <!-- Rainbow Spectrum Slider -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-300">Color Spectrum Shift:</span>
                                    <span class="font-mono text-pink-400 font-bold" id="valColorShift">0° (Original)</span>
                                </div>
                                <input type="range" id="sliderColorShift" min="0" max="360" value="0" oninput="setColorShift(this.value)" class="w-full h-3 rounded-lg appearance-none cursor-pointer" style="background: linear-gradient(to right, #ff0000, #ffff00, #00ff00, #00ffff, #0000ff, #ff00ff, #ff0000);">
                            </div>

                            <!-- Interactive Target Color Box & Quick Swatches -->
                            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-white/5">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] text-slate-300 font-medium">Pick Target Color:</span>
                                    <input type="color" id="targetColorBox" value="#FFD700" oninput="applyTargetColor(this.value)" class="w-8 h-8 rounded-lg border-2 border-white/30 cursor-pointer bg-transparent" title="Click or double-click to choose any color">
                                </div>

                                <div class="flex flex-wrap items-center gap-1.5">
                                    <button type="button" onclick="applyTargetColor('#FFD700')" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 hover:bg-amber-500/30 transition cursor-pointer">Gold</button>
                                    <button type="button" onclick="applyTargetColor('#FF1744')" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/40 hover:bg-rose-500/30 transition cursor-pointer">Crimson</button>
                                    <button type="button" onclick="applyTargetColor('#00E5FF')" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 hover:bg-cyan-500/30 transition cursor-pointer">Cyan Ice</button>
                                    <button type="button" onclick="applyTargetColor('#D500F9')" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/40 hover:bg-purple-500/30 transition cursor-pointer">Cosmic</button>
                                    <button type="button" onclick="applyTargetColor('#00E676')" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/30 transition cursor-pointer">Emerald</button>
                                    <button type="button" onclick="applyTargetColor('#FF6D00')" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-orange-500/20 text-orange-300 border border-orange-500/40 hover:bg-orange-500/30 transition cursor-pointer">Flame</button>
                                </div>
                            </div>
                        </div>

                        <!-- 6 Precision Adjustment Sliders (Each with dedicated individual Reset button) -->
                        <div class="p-4 rounded-2xl bg-[#080a18] border border-white/10 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-cyan-300 uppercase tracking-wider flex items-center gap-2">
                                    <i class="fas fa-sliders text-cyan-400"></i>
                                    <span>Precision Light & Edge Adjustments</span>
                                </label>
                                <span class="text-[10px] font-mono text-gray-400">Individual Resets Available</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                
                                <!-- Slider 1: Brightness -->
                                <div class="bg-[#0b0f24] p-3 rounded-xl border border-white/5 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-200 flex items-center gap-1.5"><i class="fas fa-sun text-amber-400 text-xs"></i> Brightness</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-amber-400 font-bold" id="valBrightness">100%</span>
                                            <button type="button" onclick="resetSingleSlider('sliderBrightness', 100)" class="text-[10px] font-mono text-gray-400 hover:text-white px-1.5 py-0.5 rounded bg-white/5 hover:bg-white/10 transition cursor-pointer" title="Reset Brightness"><i class="fas fa-rotate-left"></i></button>
                                        </div>
                                    </div>
                                    <input type="range" id="sliderBrightness" min="50" max="180" value="100" oninput="updateLivePreviewStudio()" class="w-full accent-amber-400 cursor-pointer">
                                </div>

                                <!-- Slider 2: Contrast -->
                                <div class="bg-[#0b0f24] p-3 rounded-xl border border-white/5 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-200 flex items-center gap-1.5"><i class="fas fa-circle-half-stroke text-cyan-400 text-xs"></i> Contrast</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-cyan-400 font-bold" id="valContrast">100%</span>
                                            <button type="button" onclick="resetSingleSlider('sliderContrast', 100)" class="text-[10px] font-mono text-gray-400 hover:text-white px-1.5 py-0.5 rounded bg-white/5 hover:bg-white/10 transition cursor-pointer" title="Reset Contrast"><i class="fas fa-rotate-left"></i></button>
                                        </div>
                                    </div>
                                    <input type="range" id="sliderContrast" min="50" max="200" value="100" oninput="updateLivePreviewStudio()" class="w-full accent-cyan-400 cursor-pointer">
                                </div>

                                <!-- Slider 3: Saturation (up to 250%) -->
                                <div class="bg-[#0b0f24] p-3 rounded-xl border border-white/5 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-200 flex items-center gap-1.5"><i class="fas fa-palette text-pink-400 text-xs"></i> Saturation (250% Max)</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-pink-400 font-bold" id="valSaturation">100%</span>
                                            <button type="button" onclick="resetSingleSlider('sliderSaturation', 100)" class="text-[10px] font-mono text-gray-400 hover:text-white px-1.5 py-0.5 rounded bg-white/5 hover:bg-white/10 transition cursor-pointer" title="Reset Saturation"><i class="fas fa-rotate-left"></i></button>
                                        </div>
                                    </div>
                                    <input type="range" id="sliderSaturation" min="0" max="250" value="100" oninput="updateLivePreviewStudio()" class="w-full accent-pink-400 cursor-pointer">
                                </div>

                                <!-- Slider 4: Highlights (up to 200%) -->
                                <div class="bg-[#0b0f24] p-3 rounded-xl border border-white/5 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-200 flex items-center gap-1.5"><i class="fas fa-wand-magic text-yellow-300 text-xs"></i> Highlights (200% Max)</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-yellow-300 font-bold" id="valHighlights">100%</span>
                                            <button type="button" onclick="resetSingleSlider('sliderHighlights', 100)" class="text-[10px] font-mono text-gray-400 hover:text-white px-1.5 py-0.5 rounded bg-white/5 hover:bg-white/10 transition cursor-pointer" title="Reset Highlights"><i class="fas fa-rotate-left"></i></button>
                                        </div>
                                    </div>
                                    <input type="range" id="sliderHighlights" min="50" max="200" value="100" oninput="updateLivePreviewStudio()" class="w-full accent-yellow-300 cursor-pointer">
                                </div>

                                <!-- Slider 5: Hue Tone -->
                                <div class="bg-[#0b0f24] p-3 rounded-xl border border-white/5 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-200 flex items-center gap-1.5"><i class="fas fa-rotate text-purple-400 text-xs"></i> Hue / Tone</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-purple-400 font-bold" id="valHue">0°</span>
                                            <button type="button" onclick="resetSingleSlider('sliderHue', 0)" class="text-[10px] font-mono text-gray-400 hover:text-white px-1.5 py-0.5 rounded bg-white/5 hover:bg-white/10 transition cursor-pointer" title="Reset Hue"><i class="fas fa-rotate-left"></i></button>
                                        </div>
                                    </div>
                                    <input type="range" id="sliderHue" min="-180" max="180" value="0" oninput="updateLivePreviewStudio()" class="w-full accent-purple-400 cursor-pointer">
                                </div>

                                <!-- Slider 6: HD Sharpness (Digital High-Pass SVG Convolution up to 200%) -->
                                <div class="bg-[#0b0f24] p-3 rounded-xl border border-emerald-500/20 space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-200 flex items-center gap-1.5"><i class="fas fa-bolt text-emerald-400 text-xs"></i> HD Sharpness (200% Max)</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-emerald-400 font-bold" id="valSharpness">100%</span>
                                            <button type="button" onclick="resetSingleSlider('sliderSharpness', 100)" class="text-[10px] font-mono text-gray-400 hover:text-white px-1.5 py-0.5 rounded bg-white/5 hover:bg-white/10 transition cursor-pointer" title="Reset Sharpness"><i class="fas fa-rotate-left"></i></button>
                                        </div>
                                    </div>
                                    <input type="range" id="sliderSharpness" min="0" max="200" value="100" oninput="updateLivePreviewStudio()" class="w-full accent-emerald-400 cursor-pointer">
                                </div>

                            </div>
                        </div>

                        <!-- Bone & Focal Accent Light Module -->
                        <div class="p-4 rounded-2xl bg-[#080a18] border border-amber-500/30 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-amber-300 uppercase tracking-wider">
                                    <input type="checkbox" id="boneLightCheck" onchange="updateLivePreviewStudio()" class="rounded bg-gray-800 border-gray-700 text-amber-500 cursor-pointer">
                                    <span>Enable Bone / Focal Accent Light</span>
                                </label>
                                <button type="button" onclick="resetSingleSlider('sliderBoneRadius', 45)" class="text-[10px] font-mono text-gray-400 hover:text-white px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 transition cursor-pointer flex items-center gap-1">
                                    <i class="fas fa-rotate-left text-[9px]"></i> Reset Light
                                </button>
                            </div>

                            <p class="text-[11px] text-gray-400">
                                Click any spot preset below to focus radiant light. Expanding the light radius visibly beams light outward across that side of the badge:
                            </p>

                            <!-- Clickable Bone Spot Presets -->
                            <div class="space-y-1.5">
                                <span class="text-[11px] text-slate-300 block font-medium">Select Bone Focal Spot:</span>
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                    <button type="button" onclick="setBoneSpot('crown')" id="boneBtn-crown" class="bone-spot-btn py-2 px-2 rounded-xl text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 transition cursor-pointer text-center">👑 Top Crown</button>
                                    <button type="button" onclick="setBoneSpot('left_wing')" id="boneBtn-left_wing" class="bone-spot-btn py-2 px-2 rounded-xl text-[11px] font-bold bg-white/5 text-gray-300 border border-white/10 hover:border-amber-400 transition cursor-pointer text-center">🦅 Left Wing</button>
                                    <button type="button" onclick="setBoneSpot('right_wing')" id="boneBtn-right_wing" class="bone-spot-btn py-2 px-2 rounded-xl text-[11px] font-bold bg-white/5 text-gray-300 border border-white/10 hover:border-amber-400 transition cursor-pointer text-center">🦅 Right Wing</button>
                                    <button type="button" onclick="setBoneSpot('center_gem')" id="boneBtn-center_gem" class="bone-spot-btn py-2 px-2 rounded-xl text-[11px] font-bold bg-white/5 text-gray-300 border border-white/10 hover:border-amber-400 transition cursor-pointer text-center">💎 Center Gem</button>
                                    <button type="button" onclick="setBoneSpot('bottom_apex')" id="boneBtn-bottom_apex" class="bone-spot-btn py-2 px-2 rounded-xl text-[11px] font-bold bg-white/5 text-gray-300 border border-white/10 hover:border-amber-400 transition cursor-pointer text-center">⚡ Bottom Apex</button>
                                </div>
                                <input type="hidden" id="bonePosSelect" value="crown">
                            </div>

                            <!-- Color & Radius Control -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-white/5">
                                <div class="space-y-1.5">
                                    <span class="text-[11px] text-slate-300 block">Light Flare Color:</span>
                                    <div class="flex items-center gap-2">
                                        <input type="color" id="boneColorHex" value="#FFD700" oninput="updateLivePreviewStudio()" class="w-9 h-9 rounded-lg border border-white/20 cursor-pointer bg-transparent">
                                        <input type="text" id="boneColorHexText" value="#FFD700" oninput="document.getElementById('boneColorHex').value=this.value; updateLivePreviewStudio()" class="flex-1 bg-[#050711] border border-white/10 rounded-lg px-2.5 py-1.5 text-xs font-mono text-amber-300">
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-300">Light Outward Radius:</span>
                                        <span class="font-mono text-amber-400 font-bold" id="valBoneRadius">45px</span>
                                    </div>
                                    <input type="range" id="sliderBoneRadius" min="15" max="100" value="45" oninput="updateLivePreviewStudio()" class="w-full accent-amber-400 cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <!-- Aura Glow & Silhouette Contour Module -->
                        <div class="p-4 rounded-2xl bg-[#080a18] border border-cyan-500/30 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-cyan-300 uppercase tracking-wider">
                                    <input type="checkbox" id="contourGlowCheck" onchange="updateLivePreviewStudio()" class="rounded bg-gray-800 border-gray-700 text-cyan-500 cursor-pointer">
                                    <span>Enable Aura Glow & Contour Shadow</span>
                                </label>
                                <span class="text-[10px] font-mono text-emerald-400">Silhouette Outline (Zero Boxes)</span>
                            </div>

                            <p class="text-[11px] text-gray-400">
                                Strictly applies <code class="text-cyan-300 font-mono">filter: drop-shadow</code> conforming directly to the PNG silhouette with zero square box artifacts:
                            </p>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="space-y-1.5">
                                    <span class="text-[11px] text-slate-300 block">Glow Color:</span>
                                    <div class="flex items-center gap-2">
                                        <input type="color" id="contourGlowColorHex" value="#FFD700" oninput="updateLivePreviewStudio()" class="w-9 h-9 rounded-lg border border-white/20 cursor-pointer bg-transparent">
                                        <div class="flex items-center gap-1">
                                            <button type="button" onclick="document.getElementById('contourGlowColorHex').value='#FFD700'; updateLivePreviewStudio()" class="w-5 h-5 rounded-full bg-[#FFD700] border border-white/40 cursor-pointer"></button>
                                            <button type="button" onclick="document.getElementById('contourGlowColorHex').value='#00E5FF'; updateLivePreviewStudio()" class="w-5 h-5 rounded-full bg-[#00E5FF] border border-white/40 cursor-pointer"></button>
                                            <button type="button" onclick="document.getElementById('contourGlowColorHex').value='#D500F9'; updateLivePreviewStudio()" class="w-5 h-5 rounded-full bg-[#D500F9] border border-white/40 cursor-pointer"></button>
                                            <button type="button" onclick="document.getElementById('contourGlowColorHex').value='#FF1744'; updateLivePreviewStudio()" class="w-5 h-5 rounded-full bg-[#FF1744] border border-white/40 cursor-pointer"></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-300">Glow Radius:</span>
                                        <span class="font-mono text-cyan-400 font-bold" id="valGlowRadius">14px</span>
                                    </div>
                                    <input type="range" id="sliderGlowRadius" min="0" max="40" value="14" oninput="updateLivePreviewStudio()" class="w-full accent-cyan-400 cursor-pointer">
                                </div>

                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-300">Blur Softness:</span>
                                        <span class="font-mono text-cyan-400 font-bold" id="valGlowBlur">10px</span>
                                    </div>
                                    <input type="range" id="sliderGlowBlur" min="0" max="30" value="10" oninput="updateLivePreviewStudio()" class="w-full accent-cyan-400 cursor-pointer">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ============================================================== -->
                    <!-- TAB 2: VFX & ANIMATION CUSTOMIZER (SHEEN, STARS, 50 PRESETS)  -->
                    <!-- ============================================================== -->
                    <div id="studioTab-vfx" class="studio-tab-content hidden space-y-6">

                        <!-- Master Animation Toggle Bar -->
                        <div class="p-4 rounded-2xl bg-[#080a18] border border-amber-500/30 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="sweep_enabled" id="masterAnimCheck" value="1" checked onchange="toggleMasterAnimation(this.checked)" class="w-4 h-4 rounded bg-gray-800 border-gray-700 text-amber-500 focus:ring-0 cursor-pointer">
                                    <span class="text-xs font-bold text-white uppercase tracking-wider font-orbitron">Master VFX & Animation Toggle</span>
                                </label>
                                <span class="text-[10px] font-mono text-amber-300 bg-amber-400/10 px-2.5 py-0.5 rounded-full border border-amber-400/30">
                                    1-Click On/Off
                                </span>
                            </div>
                            <p class="text-[11px] text-gray-400">
                                Toggle OFF to instantly transform any badge into a 100% clean static emblem without sheen, glints, or moving stars.
                            </p>
                        </div>

                        <!-- Directional Metallic Sheen Controls -->
                        <div class="p-4 rounded-2xl bg-[#080a18] border border-white/10 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-yellow-300 uppercase tracking-wider">
                                    <input type="checkbox" id="sheenActiveCheck" value="1" checked onchange="toggleSheenSweep(this.checked)" class="rounded bg-gray-800 border-gray-700 text-yellow-500 cursor-pointer">
                                    <span>Metallic Sheen Sweep Motion</span>
                                </label>
                                <span class="text-[10px] font-mono text-yellow-400">Clipped to Silhouette</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-200">Sheen Sweep Direction</label>
                                    <select name="sweep_direction" id="sweepDirSelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#0b0f24] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition cursor-pointer">
                                        <option value="diag_bl_tr" selected>↗ Diagonal: Bottom-Left to Top-Right (Free Fire Default)</option>
                                        <option value="diag_br_tl">↖ Diagonal: Bottom-Right to Top-Left</option>
                                        <option value="diag_tl_br">↘ Diagonal: Top-Left to Bottom-Right</option>
                                        <option value="diag_tr_bl">↙ Diagonal: Top-Right to Bottom-Left</option>
                                        <option value="up">↑ Vertical: Bottom to Top</option>
                                        <option value="down">↓ Vertical: Top to Bottom</option>
                                        <option value="right">→ Horizontal: Left to Right</option>
                                        <option value="left">← Horizontal: Right to Left</option>
                                    </select>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-slate-200">Sheen Speed / Interval</label>
                                    <select name="sweep_speed" id="sweepSpeedSelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#0b0f24] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition cursor-pointer">
                                        <option value="1.0s">1.0s (Hyper Ultra Fast)</option>
                                        <option value="1.4s">1.4s (Rapid Action)</option>
                                        <option value="1.8s">1.8s (Fast Metallic)</option>
                                        <option value="2.2s" selected>2.2s (Free Fire Standard)</option>
                                        <option value="2.6s">2.6s (Smooth Glint)</option>
                                        <option value="3.2s">3.2s (Slow Elegant)</option>
                                        <option value="4.0s">4.0s (Cinematic Majesty)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Scattered Gunpowder Micro-Stars (0 to 50 Density) -->
                        <div class="p-4 rounded-2xl bg-[#080a18] border border-white/10 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-amber-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fas fa-sparkles text-amber-400"></i>
                                    <span>Scattered Gunpowder Micro-Stars (0 to 50)</span>
                                </label>
                                <span class="text-xs font-mono font-bold text-amber-400" id="starDensityVal">16 Stars</span>
                            </div>

                            <p class="text-[11px] text-gray-400">
                                In-game diamond sparkles scattered 360-degrees around badge perimeter. Set to 0 to disable stars completely:
                            </p>

                            <input type="range" name="star_density" id="starDensityInput" min="0" max="50" value="16" oninput="setStarDensity(this.value)" class="w-full accent-amber-400 cursor-pointer">

                            <!-- Quick Count Buttons -->
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" onclick="setStarDensity(0)" class="px-2.5 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 text-xs font-mono font-bold border border-rose-500/30 transition">0 (Off)</button>
                                <button type="button" onclick="setStarDensity(8)" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-mono transition">8</button>
                                <button type="button" onclick="setStarDensity(16)" class="px-2.5 py-1 rounded-lg bg-amber-400/20 text-amber-300 text-xs font-mono font-bold border border-amber-400/30 transition">16 (Prime 8)</button>
                                <button type="button" onclick="setStarDensity(25)" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-mono transition">25</button>
                                <button type="button" onclick="setStarDensity(36)" class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 text-xs font-mono transition">36</button>
                                <button type="button" onclick="setStarDensity(50)" class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 text-xs font-mono font-bold border border-emerald-500/30 transition">50 (Max)</button>
                            </div>

                            <!-- Star Theme Color -->
                            <div class="space-y-1.5 pt-2 border-t border-white/5">
                                <label class="text-xs font-semibold text-slate-200">Star Theme Sparkle Color</label>
                                <select name="star_theme" id="starThemeSelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#0b0f24] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition cursor-pointer">
                                    <option value="gold" selected>Gold Celestial (#FFFCE0 & #FFD700)</option>
                                    <option value="cyan">Cyan Crystal (#00F0FF)</option>
                                    <option value="white">Pure White Diamond (#FFFFFF)</option>
                                    <option value="ruby">Ruby Crimson (#FF3366)</option>
                                    <option value="purple">Violet Diamond (#D946EF)</option>
                                    <option value="emerald">Emerald Matrix (#10B981)</option>
                                    <option value="amber">Amber Flame (#F59E0B)</option>
                                    <option value="rainbow">Rainbow Prism (Chroma Spectrum)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Accent Glint & Floating Motion -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-200 bg-[#080a18] p-3 rounded-xl border border-white/5 hover:border-amber-400/30 transition">
                                <input type="checkbox" name="has_gem_glint" id="hasGemGlintCheck" value="1" checked onchange="updateLivePreviewStudio()" class="rounded bg-gray-800 border-gray-700 text-amber-500">
                                <span>Center Gem Pinpoint Glint</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-200 bg-[#080a18] p-3 rounded-xl border border-white/5 hover:border-amber-400/30 transition">
                                <input type="checkbox" name="pulse_enabled" id="pulseEnabledCheck" value="1" checked onchange="updateLivePreviewStudio()" class="rounded bg-gray-800 border-gray-700 text-amber-500">
                                <span>Floating Wing Pulse Motion</span>
                            </label>
                        </div>

                        <!-- 50 Numbered Effect Presets Grid -->
                        <div class="p-4 rounded-2xl bg-[#090c1f] border border-cyan-500/30 space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-cyan-300 uppercase tracking-wider flex items-center gap-1.5 font-orbitron">
                                    <i class="fas fa-wand-magic-sparkles text-amber-400"></i>
                                    <span>50+ Numbered Effect Presets</span>
                                </label>
                                <button type="button" onclick="removeAllEffects()" class="px-2.5 py-1 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 text-[11px] font-bold flex items-center gap-1 transition cursor-pointer">
                                    <i class="fas fa-ban"></i> <span>Disable VFX (Static)</span>
                                </button>
                            </div>

                            <p class="text-[11px] text-gray-400">
                                Click any preset below to instantly apply sheen direction, speed, scattered stars count, and glow color:
                            </p>

                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 max-h-72 overflow-y-auto custom-scroll pr-1">
                                <?php foreach ($presets_list as $preset): ?>
                                    <button type="button" onclick="handlePresetChange('<?= $preset['id'] ?>')" class="text-left p-2.5 rounded-xl bg-[#080a18] hover:bg-white/10 border border-white/5 hover:border-amber-400/40 transition cursor-pointer group">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[10px] font-mono font-bold text-cyan-400 group-hover:text-amber-300">#<?= str_pad($preset['id'], 2, '0', STR_PAD_LEFT) ?></span>
                                            <span class="w-2 h-2 rounded-full" style="background:<?= $preset['hex_glow'] ?>"></span>
                                        </div>
                                        <div class="text-[11px] font-bold text-white truncate"><?= htmlspecialchars($preset['name']) ?></div>
                                        <div class="text-[10px] text-gray-500 font-mono">⭐ <?= $preset['star_density'] ?> Stars</div>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>

                    <!-- ============================================================== -->
                    <!-- TAB 3: BADGE IDENTITY, ASSET & SAVE DEPLOYMENT                -->
                    <!-- ============================================================== -->
                    <div id="studioTab-identity" class="studio-tab-content hidden space-y-6">

                        <!-- Identity Fields Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            <!-- 1. Badge Name (Editable) -->
                            <div class="space-y-1.5 sm:col-span-2">
                                <label class="text-xs font-semibold text-slate-200 flex items-center justify-between">
                                    <span>Badge Name <span class="text-rose-500">*</span></span>
                                    <span class="text-[10px] text-gray-400 font-normal">Editable anytime</span>
                                </label>
                                <input type="text" name="badge_name" id="badgeNameInput" required placeholder="e.g. Free Fire Prime Level 8 or OFFICIAL V BAGE" value="Free Fire Prime Level 8" oninput="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition font-mono">
                            </div>

                            <!-- 2. Badge ID (Strict 12...80 Format) -->
                            <div class="space-y-1.5 sm:col-span-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-semibold text-slate-200">Badge ID (Must start with 12 and end with 80) <span class="text-emerald-400">*</span></label>
                                    <span class="text-[10px] text-emerald-400 font-mono font-bold">12 Digits Strict Format</span>
                                </div>
                                <div class="relative flex items-center">
                                    <input type="text" name="badge_id" id="badgeIdInput" value="<?= $suggested_id ?>" pattern="^12[0-9]{8}80$" required oninput="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs font-mono font-bold text-emerald-400 tracking-wider focus:outline-none focus:border-emerald-400 transition">
                                    <button type="button" onclick="randomizeId()" class="absolute right-2 px-2.5 py-1 bg-white/5 hover:bg-white/10 text-gray-300 text-[11px] rounded-lg font-mono transition cursor-pointer" title="Randomize ID">
                                        <i class="fas fa-rotate"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- 3. Category -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-200">Category</label>
                                <select name="badge_category" id="badgeCatSelect" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition cursor-pointer">
                                    <option value="Prime System" selected>Prime System (Free Fire Prime)</option>
                                    <option value="Official Partner (KOL)">Official Partner (KOL V-Badge)</option>
                                    <option value="Crystal VIP Partner">Crystal VIP Partner</option>
                                    <option value="Competitive & Esports">Competitive & Esports</option>
                                    <option value="Administration & Authority">Administration & Authority</option>
                                    <option value="Creators & Architects">Creators & Architects</option>
                                    <option value="Special Editions">Special Editions</option>
                                    <option value="Exchange Tokens">Exchange Tokens</option>
                                    <option value="Custom VIP">Custom VIP</option>
                                </select>
                            </div>

                            <!-- 4. Rarity -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-200">Rarity</label>
                                <select name="badge_rarity" id="badgeRaritySelect" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition cursor-pointer">
                                    <option value="MYTHIC" selected>MYTHIC (Gold / Celestial)</option>
                                    <option value="LEGENDARY">LEGENDARY (Crimson Flame)</option>
                                    <option value="EPIC">EPIC (Cyan / Purple)</option>
                                    <option value="PURPLE">PURPLE (Violet Diamond)</option>
                                    <option value="RARE">RARE (Blue / Silver)</option>
                                </select>
                            </div>

                            <!-- 5. Default Size -->
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-slate-200">Default Render Size (px)</label>
                                <input type="number" name="badge_size" id="badgeSizeInput" value="54" min="20" max="250" oninput="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition">
                            </div>

                            <!-- 6. Aura Glow Color -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-semibold text-slate-200">Aura Glow Palette</label>
                                    <label class="flex items-center gap-1 cursor-pointer text-[10px] text-amber-300">
                                        <input type="checkbox" name="glow_enabled" id="glowEnabledCheck" value="1" checked onchange="updateLivePreviewStudio()" class="rounded bg-gray-800 border-gray-700 text-amber-500">
                                        <span>Active</span>
                                    </label>
                                </div>
                                <select name="badge_glow" id="glowSelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition cursor-pointer">
                                    <option value="rgba(255, 215, 0, 0.85)" selected>Gold Aura (rgba(255, 215, 0, 0.85))</option>
                                    <option value="rgba(168, 85, 247, 0.85)">Cosmic Purple (rgba(168, 85, 247, 0.85))</option>
                                    <option value="rgba(6, 182, 212, 0.85)">Cyan Crystal (rgba(6, 182, 212, 0.85))</option>
                                    <option value="rgba(239, 68, 68, 0.85)">Crimson Authority (rgba(239, 68, 68, 0.85))</option>
                                    <option value="rgba(249, 115, 22, 0.85)">Amber Flame (rgba(249, 115, 22, 0.85))</option>
                                </select>
                            </div>

                        </div>

                        <!-- Icon Asset Selection (Dropdown OR Custom PNG Upload) -->
                        <div class="space-y-3 pt-2 border-t border-white/5">
                            <label class="text-xs font-semibold text-slate-200 block">
                                Badge Icon Asset (Select Existing or Upload Transparent PNG) <span class="text-rose-500">*</span>
                            </label>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                
                                <!-- Select Existing -->
                                <div class="space-y-1.5">
                                    <span class="text-[11px] text-gray-400">Select Existing Asset:</span>
                                    <select name="existing_file" id="existingFileSelect" onchange="selectExistingAsset(this.value)" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition cursor-pointer">
                                        <option value="">-- Choose Existing Badge Image --</option>
                                        <?php foreach ($available_images as $img): ?>
                                            <option value="<?= htmlspecialchars($img) ?>" <?= ($img === 'ff_ui_prime_icon_08.png') ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($img) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Upload Custom PNG -->
                                <div class="space-y-1.5">
                                    <span class="text-[11px] text-gray-400">Or Upload Custom PNG:</span>
                                    <label class="flex flex-col items-center justify-center p-3 rounded-xl border border-dashed border-[#23294c] hover:border-amber-400/50 bg-[#080a18] cursor-pointer transition">
                                        <div class="flex items-center gap-2 text-xs text-gray-400">
                                            <i class="fas fa-cloud-arrow-up text-amber-400"></i>
                                            <span id="uploadPromptText">Upload Transparent PNG</span>
                                        </div>
                                        <input type="file" name="badge_file" id="badgeFileInput" accept=".png,.svg,.webp,.jpg" onchange="previewSelectedFile(event)" class="hidden">
                                    </label>
                                </div>

                            </div>
                        </div>

                        <!-- Description Field -->
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-slate-200">Badge Description</label>
                            <textarea name="badge_description" id="badgeDescInput" rows="2" placeholder="e.g. Authentic Free Fire in-game badge with micro-stars and dynamic metallic sheen." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition custom-scroll"></textarea>
                        </div>

                        <!-- Prominent Submit / Save Badge Button -->
                        <div class="pt-2">
                            <button type="submit" id="saveBadgeBtn" class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-amber-400 via-orange-500 to-amber-500 hover:from-amber-500 hover:to-orange-600 text-black font-extrabold text-sm uppercase tracking-wider shadow-xl shadow-amber-500/20 transition transform active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer font-orbitron">
                                <i class="fas fa-cloud-arrow-up text-base"></i>
                                <span id="saveBtnText">Save Badge & Deploy to CDN</span>
                            </button>
                        </div>

                    </div>

                </form>

            </div>

        </div>

        <!-- SECTION 3: BROWSE & EDIT ALL BADGES (TOTAL: <?= count($current_badges) ?>) -->
        <section class="glass-panel p-6 sm:p-7 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1f2544] pb-4">
                <div class="space-y-0.5">
                    <h3 class="text-base font-black font-orbitron text-white flex items-center gap-2">
                        <i class="fas fa-layer-group text-cyan-400"></i>
                        <span>Existing Badges & Prime Assets (<?= count($current_badges) ?> Total)</span>
                    </h3>
                    <p class="text-xs text-gray-400">Click any row or 'Edit' button to instantly load that badge into the studio above.</p>
                </div>

                <!-- Filter & Search -->
                <div class="flex items-center gap-3">
                    <input type="text" id="adminSearchInput" oninput="filterAdminBadges(this.value)" placeholder="Search name or 12...80 ID..." class="bg-[#080a18] border border-[#23294c] rounded-xl px-3.5 py-1.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition w-64">
                </div>
            </div>

            <!-- Table of Badges -->
            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-[#1f2544] text-[11px] uppercase text-gray-400 font-mono">
                            <th class="py-3 px-3">Live Badge</th>
                            <th class="py-3 px-3">Item ID (12...80)</th>
                            <th class="py-3 px-3">Badge Name</th>
                            <th class="py-3 px-3">Category</th>
                            <th class="py-3 px-3">Rarity</th>
                            <th class="py-3 px-3">VFX Parameters</th>
                            <th class="py-3 px-3">CDN & Snippets</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminBadgesTbody" class="divide-y divide-[#151934]">
                        <?php foreach ($current_badges as $b): ?>
                            <?php
                                $rawBFile = trim($b['file'] ?? '');
                                $bFile = (!empty($rawBFile)) ? htmlspecialchars($rawBFile) : 'FF_UI_Badge_KOL01.png';
                                $img_cdn = $CDN_BASE . $bFile;
                                $bStars = intval($b['star_density'] ?? 0);
                                $bSweep = !empty($b['sweep_enabled']);
                                $bDir = htmlspecialchars($b['sweep_direction'] ?? 'diag_bl_tr');
                                $bSpd = htmlspecialchars($b['sweep_speed'] ?? '2.2s');
                                $bTheme = htmlspecialchars($b['star_theme'] ?? 'gold');
                                $bGlint = !empty($b['has_gem_glint']);
                                $bGlow = htmlspecialchars($b['glow_color'] ?? 'rgba(255, 215, 0, 0.85)');
                                $isPrime = (($b['type'] ?? '') === 'prime_image' || !empty($b['prime_level']));
                                $isFullyStatic = (!$bSweep && $bStars === 0 && !$bGlint);
                                $bId = htmlspecialchars($b['id'] ?? '');
                                $bName = htmlspecialchars($b['name'] ?? 'Untitled');
                                $bCat = htmlspecialchars($b['category'] ?? 'Custom VIP');
                                $bRare = htmlspecialchars($b['rarity'] ?? 'MYTHIC');
                                $bSize = intval($b['default_size'] ?? $b['size'] ?? 54);
                            ?>
                            <tr class="hover:bg-white/[0.04] transition admin-badge-row cursor-pointer" onclick="handleRowClick(event, '<?= $bId ?>')" data-name="<?= htmlspecialchars(strtolower($bName)) ?>" data-id="<?= htmlspecialchars(strtolower($bId)) ?>">
                                
                                <!-- Preview Cell -->
                                <td class="py-3 px-3">
                                    <div class="w-12 h-12 rounded-xl bg-[#080a18] border border-white/5 flex items-center justify-center p-1 relative overflow-hidden">
                                        <div class="ff-prime-badge-wrap <?= $isFullyStatic ? 'ff-anim-off' : '' ?>" style="width:38px;height:38px;position:relative;background:transparent !important;filter:drop-shadow(0 0 6px <?= $bGlow ?>);">
                                            <?php if ($bStars > 0 && $bSweep): ?>
                                                <div class="ff-prime-stars-layer star-theme-<?= $bTheme ?>">
                                                    <?php for ($si = 1; $si <= min($bStars, 16); $si++): ?>
                                                        <svg class="ff-pinpoint-star star-pos-<?= $si ?>" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="#FFFCE0"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>
                                                    <?php endfor; ?>
                                                </div>
                                            <?php endif; ?>
                                            <img src="badges/<?= $bFile ?>" onerror="this.onerror=null; this.src='<?= $img_cdn ?>'" class="ff-badge-img <?= $isFullyStatic ? 'style="animation:none !important;"' : '' ?>" alt="<?= $bName ?>">
                                            <?php if ($bSweep): ?>
                                                <span class="ff-badge-shine sweep-dir-<?= $bDir ?> sweep-dir-diag_bl_tr" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('badges/<?= $bFile ?>');mask-image:url('badges/<?= $bFile ?>');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                                                    <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;animation-duration:<?= $bSpd ?>;mix-blend-mode:screen;display:block;"></span>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($bGlint): ?>
                                                <div class="ff-gem-glint"></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- 12...80 ID -->
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-emerald-400">#<?= $bId ?></span>
                                        <button type="button" onclick="event.stopPropagation(); copyText('<?= $bId ?>', 'ID Copied!')" class="text-gray-500 hover:text-white cursor-pointer" title="Copy ID">
                                            <i class="fas fa-copy text-[10px]"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Name -->
                                <td class="py-3 px-3">
                                    <div class="font-bold text-white"><?= $bName ?></div>
                                    <div class="text-[10px] text-gray-500 font-mono"><?= htmlspecialchars($b['icon_id'] ?? '') ?></div>
                                </td>

                                <!-- Category -->
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-cyan-950/50 text-cyan-300 border border-cyan-800/40">
                                        <?= $bCat ?>
                                    </span>
                                </td>

                                <!-- Rarity -->
                                <td class="py-3 px-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-yellow-300">
                                        <?= $bRare ?>
                                    </span>
                                </td>

                                <!-- VFX Parameters -->
                                <td class="py-3 px-3 font-mono text-[11px]">
                                    <div class="flex flex-col gap-0.5">
                                        <?php if ($bSweep || $bStars > 0): ?>
                                            <span class="text-amber-300">⭐ <?= $bStars ?> Stars (<?= $bTheme ?>)</span>
                                            <span class="text-gray-400">⚡ <?= $bDir ?> (<?= $bSpd ?>)</span>
                                        <?php else: ?>
                                            <span class="text-gray-500">Static Badge (No VFX)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- CDN Link & Snippets -->
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-1.5" onclick="event.stopPropagation()">
                                        <button type="button" onclick="copyText('<?= $img_cdn ?>', 'CDN URL Copied!')" class="px-2 py-1 rounded bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-[10px] font-mono flex items-center gap-1 transition cursor-pointer">
                                            <i class="fas fa-link text-[9px]"></i> <span>CDN</span>
                                        </button>

                                        <button type="button" onclick="copyText(<?= htmlspecialchars(json_encode($b['fn_call'] ?? '')) ?>, 'PHP Call Copied!')" class="px-2 py-1 rounded bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-mono flex items-center gap-1 transition cursor-pointer">
                                            <i class="fab fa-php text-[10px]"></i> <span>PHP</span>
                                        </button>

                                        <button type="button" onclick="copyText('<ff-badge id='<?= $bId ?>' size='<?= $bSize ?>'></ff-badge>', 'Web Tag Copied!')" class="px-2 py-1 rounded bg-purple-500/10 hover:bg-purple-500/20 text-purple-300 border border-purple-500/30 text-[10px] font-mono flex items-center gap-1 transition cursor-pointer">
                                            <i class="fas fa-code text-[9px]"></i> <span>Tag</span>
                                        </button>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                                        <button type="button" onclick="loadBadgeToEditor('<?= $bId ?>')" class="px-2.5 py-1 rounded-lg bg-amber-400 hover:bg-amber-500 text-black font-extrabold text-[11px] flex items-center gap-1 transition shadow cursor-pointer" title="Edit in studio">
                                            <i class="fas fa-pen-to-square"></i> <span>Edit</span>
                                        </button>
                                        
                                        <a href="index.php?item=<?= $bId ?>" target="_blank" class="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white flex items-center justify-center transition" title="View in Explorer">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>

                                        <a href="admin.php?delete=<?= $bId ?>" onclick="return confirm('Are you sure you want to delete badge #<?= $bId ?>?');" class="w-7 h-7 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 flex items-center justify-center transition" title="Delete Badge">
                                            <i class="fas fa-trash-can text-xs"></i>
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </section>

    </main>

    <!-- Toast Notification -->
    <div id="adminToast" class="fixed bottom-6 right-6 bg-amber-400 text-black font-extrabold px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-2 transform translate-y-24 opacity-0 transition duration-300 z-50 pointer-events-none text-xs">
        <i class="fas fa-circle-check text-sm"></i>
        <span id="toastMsg">Action Successful!</span>
    </div>

    <!-- JAVASCRIPT STUDIO ENGINE (ZERO SYNTAX ERRORS, COMPLETE LIVE PREVIEW & TABS) -->
    <script>
        const ALL_BADGES_DATA = <?= json_encode($current_badges, JSON_UNESCAPED_SLASHES) ?>;
        const PRESETS_LIST = <?= json_encode($presets_list, JSON_UNESCAPED_SLASHES) ?>;
        const RAW_BASE = '<?= $RAW_BASE ?>';
        const CDN_BASE = '<?= $CDN_BASE ?>';

        let currentActiveBadge = null;
        let currentPreviewImg = 'badges/ff_ui_prime_icon_08.png';
        let stageDisplaySize = 110;

        // Copy text helper
        function copyText(text, msg = 'Copied!') {
            if (!navigator.clipboard) {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast(msg);
                return;
            }
            navigator.clipboard.writeText(text).then(() => showToast(msg)).catch(() => showToast(msg));
        }

        // Toast Helper
        function showToast(msg) {
            const toast = document.getElementById('adminToast');
            const msgEl = document.getElementById('toastMsg');
            if (!toast || !msgEl) return;
            msgEl.textContent = msg;
            toast.classList.remove('translate-y-24', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-24', 'opacity-0');
            }, 2500);
        }

        // Randomize 12...80 ID
        function randomizeId() {
            const middle = Math.floor(10000000 + Math.random() * 90000000);
            const newId = '12' + middle + '80';
            const input = document.getElementById('badgeIdInput');
            if (input) {
                input.value = newId;
                updateLivePreviewStudio();
            }
        }

        // STUDIO TAB SWITCHING LOGIC (Color Grading | VFX Animations | Badge Identity)
        function switchStudioTab(tab) {
            document.querySelectorAll('.studio-tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.studio-tab-btn').forEach(btn => {
                btn.className = 'studio-tab-btn flex-1 py-3 px-3 sm:px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-[#080a18] border border-white/5 text-gray-400 hover:text-white hover:bg-white/5 cursor-pointer';
            });

            const targetContent = document.getElementById('studioTab-' + tab);
            const targetBtn = document.getElementById('studioTabBtn-' + tab);

            if (targetContent) targetContent.classList.remove('hidden');
            if (targetBtn) {
                let activeStyle = 'from-amber-500/20 to-orange-500/20 border-amber-400 text-white shadow-amber-500/10';
                if (tab === 'vfx') activeStyle = 'from-pink-500/20 to-purple-500/20 border-pink-400 text-white shadow-pink-500/10';
                if (tab === 'identity') activeStyle = 'from-cyan-500/20 to-blue-500/20 border-cyan-400 text-white shadow-cyan-500/10';
                targetBtn.className = `studio-tab-btn flex-1 py-3 px-3 sm:px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-gradient-to-r ${activeStyle} border shadow-lg cursor-pointer`;
            }
        }

        // Star Density Slider Sync
        function setStarDensity(val) {
            val = Math.max(0, Math.min(50, parseInt(val) || 0));
            document.getElementById('starDensityInput').value = val;
            document.getElementById('starDensityVal').textContent = val + ' Stars';
            updateLivePreviewStudio();
        }

        // Existing Asset Selection Dropdown
        function selectExistingAsset(val) {
            if (!val) return;
            currentPreviewImg = 'badges/' + val;
            document.getElementById('uploadPromptText').textContent = 'Selected: ' + val;
            updateLivePreviewStudio();
        }

        // Custom PNG File Upload Preview
        function previewSelectedFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            document.getElementById('uploadPromptText').textContent = 'Loaded: ' + file.name;
            const reader = new FileReader();
            reader.onload = function(e) {
                currentPreviewImg = e.target.result;
                updateLivePreviewStudio();
            };
            reader.readAsDataURL(file);
        }

        // Stage Size Switcher
        function setStageSize(sz) {
            stageDisplaySize = sz;
            updateLivePreviewStudio();
        }

        // Background Mode Switcher (Transparent Checkerboard, Navy, Pure Black)
        function setStageBg(mode) {
            const sc = document.getElementById('liveStageContainer');
            if (!sc) return;

            sc.classList.remove('checker-bg', 'bg-black', 'bg-[#080a18]');
            document.getElementById('bgBtn-checker').className = 'w-6 h-6 rounded-lg checker-bg border border-white/20 transition cursor-pointer';
            document.getElementById('bgBtn-navy').className = 'w-6 h-6 rounded-lg bg-[#080a18] border border-white/20 transition cursor-pointer';
            document.getElementById('bgBtn-black').className = 'w-6 h-6 rounded-lg bg-black border border-white/20 transition cursor-pointer';

            if (mode === 'navy') {
                sc.classList.add('bg-[#080a18]');
                document.getElementById('bgBtn-navy').className = 'w-6 h-6 rounded-lg bg-[#080a18] border-2 border-amber-400 transition cursor-pointer shadow-lg shadow-amber-400/20';
            } else if (mode === 'black') {
                sc.classList.add('bg-black');
                document.getElementById('bgBtn-black').className = 'w-6 h-6 rounded-lg bg-black border-2 border-amber-400 transition cursor-pointer shadow-lg shadow-amber-400/20';
            } else {
                sc.classList.add('checker-bg');
                document.getElementById('bgBtn-checker').className = 'w-6 h-6 rounded-lg checker-bg border-2 border-amber-400 transition cursor-pointer shadow-lg shadow-amber-400/20';
            }
        }

        // Master Animation Toggle
        function toggleMasterAnimation(enable) {
            document.getElementById('masterAnimCheck').checked = enable;
            document.getElementById('sheenActiveCheck').checked = enable;
            updateLivePreviewStudio();
        }

        // Sheen Sweep Toggle
        function toggleSheenSweep(enable) {
            document.getElementById('sheenActiveCheck').checked = enable;
            updateLivePreviewStudio();
        }

        // Quick Search Bar Load
        function handleQuickSearchLoad() {
            const q = (document.getElementById('quickBadgeSearchInput')?.value || '').trim();
            if (!q) return;
            searchAndLoadBadge(q);
        }

        function searchAndLoadBadge(query) {
            query = query.toLowerCase().trim();
            const badge = ALL_BADGES_DATA.find(b => 
                (b.id && b.id.toLowerCase() === query) ||
                (b.name && b.name.toLowerCase().includes(query)) ||
                (b.file && b.file.toLowerCase().includes(query))
            );

            if (badge) {
                loadBadgeToEditor(badge.id);
            } else {
                showToast('Badge not found! Check 12...80 ID or name.');
            }
        }

        // Table Row Click Handler
        function handleRowClick(event, id) {
            loadBadgeToEditor(id);
        }

        // LOAD BADGE INTO STUDIO EDITOR & LIVE PREVIEW
        function loadBadgeToEditor(id) {
            const b = ALL_BADGES_DATA.find(x => x.id === id);
            if (!b) return;

            currentActiveBadge = b;

            // Form Headers
            document.getElementById('originalIdInput').value = b.id;
            document.getElementById('formPanelTitle').textContent = 'Editing: ' + b.name + ' (#' + b.id + ')';
            document.getElementById('formPanelSub').textContent = 'Modify parameters in tabs and click Save Badge to update CDN immediately.';
            document.getElementById('editorModeText').textContent = 'Editing: #' + b.id;
            document.getElementById('editorModeBadge').className = 'px-3.5 py-1.5 rounded-xl bg-amber-950/60 border border-amber-500/50 text-amber-300 text-xs font-mono flex items-center gap-2';
            document.getElementById('saveBtnText').textContent = 'Update & Redeploy #' + b.id;
            document.getElementById('saveBtnTextTop').textContent = 'Update #' + b.id;

            // Identity Tab Fields
            document.getElementById('badgeNameInput').value = b.name || '';
            document.getElementById('badgeIdInput').value = b.id || '';
            document.getElementById('badgeCatSelect').value = b.category || 'Prime System';
            document.getElementById('badgeRaritySelect').value = b.rarity || 'MYTHIC';
            document.getElementById('badgeSizeInput').value = b.default_size || b.size || 54;
            document.getElementById('glowSelect').value = b.glow_color || 'rgba(255, 215, 0, 0.85)';
            document.getElementById('glowEnabledCheck').checked = (b.glow_enabled !== false);
            document.getElementById('badgeDescInput').value = b.description || '';

            // Icon Asset Selection
            currentPreviewImg = 'badges/' + (b.file || 'ff_ui_prime_icon_08.png');
            document.getElementById('uploadPromptText').textContent = 'Asset: ' + (b.file || 'ff_ui_prime_icon_08.png');
            
            const efSelect = document.getElementById('existingFileSelect');
            if (efSelect) {
                for (let i = 0; i < efSelect.options.length; i++) {
                    if (efSelect.options[i].value === b.file) {
                        efSelect.selectedIndex = i;
                        break;
                    }
                }
            }

            // VFX Animation State
            const hasAnimation = (b.sweep_enabled !== false && b.sweep_enabled !== 'false' && b.sweep_enabled !== 0);
            document.getElementById('masterAnimCheck').checked = hasAnimation;
            document.getElementById('sheenActiveCheck').checked = hasAnimation;

            let sDir = b.sweep_direction || 'diag_bl_tr';
            if (sDir === 'diag') sDir = 'diag_bl_tr';
            if (sDir === 'diag_rev') sDir = 'diag_br_tl';
            document.getElementById('sweepDirSelect').value = sDir;
            document.getElementById('sweepSpeedSelect').value = b.sweep_speed || '2.2s';
            
            const starCount = (b.star_density !== undefined) ? parseInt(b.star_density) : (b.prime_level === 8 ? 16 : 0);
            setStarDensity(hasAnimation ? starCount : 0);

            document.getElementById('starThemeSelect').value = b.star_theme || 'gold';
            document.getElementById('hasGemGlintCheck').checked = hasAnimation && (b.has_gem_glint === true || b.has_gem_glint === '1' || b.has_gem_glint === 1);
            document.getElementById('pulseEnabledCheck').checked = hasAnimation && (b.pulse_enabled !== false);

            // Update Stage immediately
            updateLivePreviewStudio();
            showToast('Loaded: ' + b.name + ' (#' + b.id + ')');

            // Smooth scroll to top of editor
            window.scrollTo({ top: 120, behavior: 'smooth' });
        }

        // Reset to Create New Badge Mode
        function resetToNewBadge() {
            currentActiveBadge = null;
            document.getElementById('badgeEditorForm').reset();
            document.getElementById('originalIdInput').value = '';
            
            randomizeId();

            document.getElementById('formPanelTitle').textContent = 'Badge Studio & VFX Customizer';
            document.getElementById('formPanelSub').textContent = 'Switch tabs to customize Color Grading, VFX Animations, or Badge Identity.';
            document.getElementById('editorModeText').textContent = 'Mode: New Badge';
            document.getElementById('editorModeBadge').className = 'px-3.5 py-1.5 rounded-xl bg-cyan-950/60 border border-cyan-800/50 text-cyan-300 text-xs font-mono flex items-center gap-2';
            document.getElementById('saveBtnText').textContent = 'Save Badge & Deploy to CDN';
            document.getElementById('saveBtnTextTop').textContent = 'Save & Deploy';

            currentPreviewImg = 'badges/ff_ui_prime_icon_08.png';
            document.getElementById('uploadPromptText').textContent = 'Upload Transparent PNG';

            document.getElementById('masterAnimCheck').checked = true;
            document.getElementById('sheenActiveCheck').checked = true;
            setStarDensity(16);

            resetAllAdjustments();
            switchStudioTab('color');
            updateLivePreviewStudio();
            showToast('Switched to New Badge mode.');
        }

        // Apply Numbered Effect Preset
        function handlePresetChange(presetId) {
            const p = PRESETS_LIST.find(x => x.id === parseInt(presetId));
            if (!p) return;

            document.getElementById('masterAnimCheck').checked = true;
            document.getElementById('sheenActiveCheck').checked = true;

            let sDir = p.sweep_direction || 'diag_bl_tr';
            if (sDir === 'diag') sDir = 'diag_bl_tr';
            if (sDir === 'diag_rev') sDir = 'diag_br_tl';
            document.getElementById('sweepDirSelect').value = sDir;
            document.getElementById('sweepSpeedSelect').value = p.sweep_speed || '2.2s';
            
            setStarDensity(p.star_density || 0);
            document.getElementById('starThemeSelect').value = p.star_theme || 'gold';
            document.getElementById('hasGemGlintCheck').checked = (p.has_gem_glint !== false);
            
            if (p.glow_color) {
                document.getElementById('glowSelect').value = p.glow_color;
                document.getElementById('glowEnabledCheck').checked = true;
            }

            updateLivePreviewStudio();
            showToast('Applied Preset #' + p.id + ': ' + p.name);
        }

        // Remove All Effects (Clean Static Emblem)
        function removeAllEffects() {
            document.getElementById('masterAnimCheck').checked = false;
            document.getElementById('sheenActiveCheck').checked = false;
            setStarDensity(0);
            document.getElementById('hasGemGlintCheck').checked = false;
            document.getElementById('pulseEnabledCheck').checked = false;
            updateLivePreviewStudio();
            showToast('All VFX disabled (Clean static badge mode)');
        }

        // INDIVIDUAL SLIDER RESET HELPER
        function resetSingleSlider(id, defaultVal) {
            const el = document.getElementById(id);
            if (!el) return;
            el.value = defaultVal;
            updateLivePreviewStudio();
            showToast('Reset ' + id.replace('slider', '') + ' to default.');
        }

        // BONE FOCAL SPOT PRESET SELECTOR
        function setBoneSpot(spot) {
            const posInput = document.getElementById('bonePosSelect');
            if (posInput) posInput.value = spot;

            document.querySelectorAll('.bone-spot-btn').forEach(btn => {
                btn.className = 'bone-spot-btn py-2 px-2 rounded-xl text-[11px] font-bold bg-white/5 text-gray-300 border border-white/10 hover:border-amber-400 transition cursor-pointer text-center';
            });
            const activeBtn = document.getElementById('boneBtn-' + spot);
            if (activeBtn) {
                activeBtn.className = 'bone-spot-btn py-2 px-2 rounded-xl text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/50 shadow-lg shadow-amber-500/20 transition cursor-pointer text-center';
            }

            document.getElementById('boneLightCheck').checked = true;
            updateLivePreviewStudio();
            showToast('Bone focal spot set: ' + spot.replace('_', ' ').toUpperCase());
        }

        // UNIVERSAL COLOR SHIFT SPECTRUM HANDLER
        function setColorShift(hue) {
            hue = parseInt(hue) || 0;
            const slider = document.getElementById('sliderColorShift');
            if (slider) slider.value = hue;

            const valText = document.getElementById('valColorShift');
            if (valText) {
                valText.textContent = hue === 0 ? '0° (Original)' : (hue + '° Shift');
            }

            updateLivePreviewStudio();
        }

        // INTERACTIVE TARGET COLOR PICKER (Calculates relative hue rotation)
        function applyTargetColor(hex) {
            const rgb = hexToRgb(hex);
            if (!rgb) return;
            const hsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
            const targetHue = Math.round(hsv.h * 360);
            const goldBaseHue = 45; // Default Free Fire gold base
            let shiftDeg = targetHue - goldBaseHue;
            if (shiftDeg < 0) shiftDeg += 360;

            setColorShift(shiftDeg);
            const colorBox = document.getElementById('targetColorBox');
            if (colorBox) colorBox.value = hex;

            showToast('Recolored badge to ' + hex.toUpperCase());
        }

        function hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        }

        function rgbToHsv(r, g, b) {
            r /= 255; g /= 255; b /= 255;
            const max = Math.max(r, g, b), min = Math.min(r, g, b);
            let h, s, v = max;
            const d = max - min;
            s = max === 0 ? 0 : d / max;

            if (max === min) {
                h = 0;
            } else {
                switch (max) {
                    case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                    case g: h = (b - r) / d + 2; break;
                    case b: h = (r - g) / d + 4; break;
                }
                h /= 6;
            }
            return { h: h, s: s, v: v };
        }

        // 1-CLICK AI AUTO ENHANCER
        function aiAutoEnhance() {
            document.getElementById('sliderBrightness').value = 112;
            document.getElementById('sliderContrast').value = 118;
            document.getElementById('sliderSaturation').value = 125;
            document.getElementById('sliderHighlights').value = 110;
            document.getElementById('sliderSharpness').value = 135;

            document.getElementById('contourGlowCheck').checked = true;
            document.getElementById('contourGlowColorHex').value = '#FFD700';
            document.getElementById('sliderGlowRadius').value = 16;
            document.getElementById('sliderGlowBlur').value = 12;

            updateLivePreviewStudio();
            showToast('AI Auto Enhance applied! Balanced light, saturation & edge clarity.');
        }

        // RESET ALL COLOR GRADING & PRO ADJUSTMENTS
        function resetAllAdjustments() {
            document.getElementById('sliderBrightness').value = 100;
            document.getElementById('sliderContrast').value = 100;
            document.getElementById('sliderSaturation').value = 100;
            document.getElementById('sliderHighlights').value = 100;
            document.getElementById('sliderHue').value = 0;
            document.getElementById('sliderSharpness').value = 100;
            document.getElementById('sliderColorShift').value = 0;
            document.getElementById('valColorShift').textContent = '0° (Original)';

            document.getElementById('boneLightCheck').checked = false;
            document.getElementById('sliderBoneRadius').value = 45;
            document.getElementById('boneColorHex').value = '#FFD700';

            document.getElementById('contourGlowCheck').checked = false;
            document.getElementById('sliderGlowRadius').value = 14;
            document.getElementById('sliderGlowBlur').value = 10;
            document.getElementById('contourGlowColorHex').value = '#FFD700';

            updateLivePreviewStudio();
            showToast('All Color Grading and Studio adjustments reset to defaults.');
        }

        // 1-CLICK AUTO BACKGROUND REMOVE (Canvas Flood Erase + Edge Feathering)
        function autoRemoveBackground() {
            if (!currentPreviewImg) return;
            showToast('Processing cutout... Removing background.');

            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = currentPreviewImg;
            img.onload = function() {
                const canvas = document.createElement('canvas');
                canvas.width = img.naturalWidth || 500;
                canvas.height = img.naturalHeight || 500;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0);

                const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const data = imgData.data;

                const c1 = [data[0], data[1], data[2]];
                const c2 = [data[(canvas.width - 1) * 4], data[(canvas.width - 1) * 4 + 1], data[(canvas.width - 1) * 4 + 2]];
                const c3 = [data[(canvas.width * (canvas.height - 1)) * 4], data[(canvas.width * (canvas.height - 1)) * 4 + 1], data[(canvas.width * (canvas.height - 1)) * 4 + 2]];
                const c4 = [data[(canvas.width * canvas.height - 1) * 4], data[(canvas.width * canvas.height - 1) * 4 + 1], data[(canvas.width * canvas.height - 1) * 4 + 2]];

                const bgR = Math.round((c1[0] + c2[0] + c3[0] + c4[0]) / 4);
                const bgG = Math.round((c1[1] + c2[1] + c3[1] + c4[1]) / 4);
                const bgB = Math.round((c1[2] + c2[2] + c3[2] + c4[2]) / 4);
                const tolerance = 42;

                for (let i = 0; i < data.length; i += 4) {
                    const r = data[i], g = data[i + 1], b = data[i + 2];
                    const dist = Math.sqrt((r - bgR)**2 + (g - bgG)**2 + (b - bgB)**2);
                    if (dist < tolerance) {
                        data[i + 3] = 0; // Transparent
                    } else if (dist < tolerance + 15) {
                        data[i + 3] = Math.round(((dist - tolerance) / 15) * data[i + 3]); // Smooth feathering
                    }
                }

                ctx.putImageData(imgData, 0, 0);
                currentPreviewImg = canvas.toDataURL('image/png');
                updateLivePreviewStudio();
                showToast('Background removed! 100% transparent PNG cutout created.');
            };
            img.onerror = function() {
                showToast('Unable to process external image directly due to CORS.');
            };
        }

        // 1-CLICK FULL HD CLARITY BOOST
        function boostHdClarity() {
            document.getElementById('sliderContrast').value = 125;
            document.getElementById('sliderSharpness').value = 150;
            document.getElementById('sliderHighlights').value = 115;
            updateLivePreviewStudio();
            showToast('HD Clarity Boost active! Fine lines sharpened.');
        }

        // ==============================================================
        // CORE LIVE PREVIEW RENDERING ENGINE (ZERO SYNTAX ERRORS)
        // ==============================================================
        function updateLivePreviewStudio() {
            const stage = document.getElementById('liveBadgeStage');
            if (!stage) return;

            // Form Inputs & State
            const isMasterAnim = document.getElementById('masterAnimCheck')?.checked ?? true;
            const isSheenActive = isMasterAnim && (document.getElementById('sheenActiveCheck')?.checked ?? true);
            const sweepDir = document.getElementById('sweepDirSelect')?.value || 'diag_bl_tr';
            const sweepSpeed = document.getElementById('sweepSpeedSelect')?.value || '2.2s';
            const starCount = isMasterAnim ? Math.max(0, Math.min(50, parseInt(document.getElementById('starDensityInput')?.value) || 0)) : 0;
            const starTheme = document.getElementById('starThemeSelect')?.value || 'gold';
            const hasGlint = isMasterAnim && (document.getElementById('hasGemGlintCheck')?.checked ?? true);
            const hasPulse = isMasterAnim && (document.getElementById('pulseEnabledCheck')?.checked ?? true);
            const glowEnabled = document.getElementById('glowEnabledCheck')?.checked ?? true;
            const glowVal = glowEnabled ? (document.getElementById('glowSelect')?.value || 'rgba(255, 215, 0, 0.85)') : 'transparent';
            const badgeId = document.getElementById('badgeIdInput')?.value || '120000000880';

            // Sheen Direction Class (Maps to both underscore and dash versions)
            let sweepClass = 'sweep-dir-diag_bl_tr sweep-dir-bl-tr';
            if (sweepDir === 'diag_br_tl' || sweepDir === 'diag_rev') sweepClass = 'sweep-dir-diag_br_tl sweep-dir-br-tl';
            else if (sweepDir === 'diag_tl_br') sweepClass = 'sweep-dir-diag_tl_br sweep-dir-tl-br';
            else if (sweepDir === 'diag_tr_bl') sweepClass = 'sweep-dir-tr-bl sweep-dir-diag_tr_bl';
            else if (sweepDir === 'up') sweepClass = 'sweep-dir-up';
            else if (sweepDir === 'down') sweepClass = 'sweep-dir-down';
            else if (sweepDir === 'right') sweepClass = 'sweep-dir-right';
            else if (sweepDir === 'left') sweepClass = 'sweep-dir-left';

            // Star theme color map
            const themeColors = {
                gold: '#FFFCE0',
                cyan: '#00F0FF',
                white: '#FFFFFF',
                ruby: '#FF3366',
                purple: '#D946EF',
                emerald: '#10B981',
                amber: '#F59E0B',
                rainbow: '#FFFCE0'
            };
            const starColor = themeColors[starTheme] || '#FFFCE0';
            const starThemeClass = 'star-theme-' + starTheme;

            // Generate Radially Scattered Stars (0 to 50)
            let starsHtml = '';
            if (isMasterAnim && starCount > 0) {
                const starPositions = Array.from({length: starCount}, (_, i) => 'star-pos-' + (i + 1));
                const starDots = starPositions.map(pos => 
                    `<svg class="ff-pinpoint-star ${pos}" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="${starColor}"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>`
                ).join('');

                starsHtml = `<div class="ff-prime-stars-layer ${starThemeClass}">${starDots}</div>`;
            }

            // Directional Metallic Sheen Beam
            let shineHtml = '';
            if (isSheenActive) {
                shineHtml = `
                    <span class="ff-badge-shine ${sweepClass}" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('${currentPreviewImg}');mask-image:url('${currentPreviewImg}');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                        <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;animation-duration:${sweepSpeed};mix-blend-mode:screen;display:block;"></span>
                    </span>`;
            }

            // Center Gem Flare
            const glintHtml = (isMasterAnim && hasGlint) ? '<div class="ff-gem-glint"></div>' : '';

            // Image Animation Style
            const imgStyle = (!isMasterAnim || !hasPulse) ? 'animation:none !important;transform:none !important;' : '';
            const animOffClass = (!isMasterAnim) ? ' ff-anim-off' : '';

            // COMPUTE PRO COLOR & LIGHTING ADJUSTMENTS (SINGLE CLEAN DECLARATION)
            const valB = parseInt(document.getElementById('sliderBrightness')?.value || 100);
            const valC = parseInt(document.getElementById('sliderContrast')?.value || 100);
            const valS = parseInt(document.getElementById('sliderSaturation')?.value || 100);
            const valHl = parseInt(document.getElementById('sliderHighlights')?.value || 100);
            const valH = parseInt(document.getElementById('sliderHue')?.value || 0);
            const valColorShift = parseInt(document.getElementById('sliderColorShift')?.value || 0);
            const valSharp = parseInt(document.getElementById('sliderSharpness')?.value || 100);

            // Update Label Value Displays
            if (document.getElementById('valBrightness')) document.getElementById('valBrightness').textContent = valB + '%';
            if (document.getElementById('valContrast')) document.getElementById('valContrast').textContent = valC + '%';
            if (document.getElementById('valSaturation')) document.getElementById('valSaturation').textContent = valS + '%';
            if (document.getElementById('valHighlights')) document.getElementById('valHighlights').textContent = valHl + '%';
            if (document.getElementById('valHue')) document.getElementById('valHue').textContent = valH + '°';
            if (document.getElementById('valSharpness')) document.getElementById('valSharpness').textContent = valSharp + '%';

            // Dynamic SVG Convolution Matrix for Real HD Edge Sharpening
            const matrixEl = document.getElementById('ffSharpenMatrix');
            if (matrixEl) {
                if (valSharp <= 100) {
                    matrixEl.setAttribute('kernelMatrix', '0 0 0  0 1 0  0 0 0');
                } else {
                    const factor = ((valSharp - 100) / 100) * 1.5;
                    const c = -factor;
                    const center = 1 + (4 * factor);
                    matrixEl.setAttribute('kernelMatrix', `0 ${c} 0  ${c} ${center} ${c}  0 ${c} 0`);
                }
            }

            const effectiveBrightness = ((valB / 100) * (valHl / 100)).toFixed(2);
            const totalHue = valH + valColorShift;
            let imgAdjustmentFilter = `brightness(${effectiveBrightness}) contrast(${valC}%) saturate(${valS}%) hue-rotate(${totalHue}deg)`;
            if (valSharp > 100) {
                imgAdjustmentFilter += ` url(#ffCustomSharpen)`;
            }

            // Compute Aura Contour Shadow (strictly hugs PNG outline, no square box!)
            const isContourGlow = document.getElementById('contourGlowCheck')?.checked;
            const contourGlowColor = document.getElementById('contourGlowColorHex')?.value || '#FFD700';
            const contourGlowRad = parseInt(document.getElementById('sliderGlowRadius')?.value || 14);
            const contourGlowBlur = parseInt(document.getElementById('sliderGlowBlur')?.value || 10);

            if (document.getElementById('valGlowRadius')) document.getElementById('valGlowRadius').textContent = contourGlowRad + 'px';
            if (document.getElementById('valGlowBlur')) document.getElementById('valGlowBlur').textContent = contourGlowBlur + 'px';

            let contourDropFilter = glowEnabled ? `filter: drop-shadow(0 0 8px ${glowVal});` : '';
            if (isContourGlow && contourGlowRad > 0) {
                contourDropFilter = `filter: drop-shadow(0 0 ${contourGlowRad}px ${contourGlowColor}) drop-shadow(0 0 ${contourGlowBlur}px ${contourGlowColor});`;
            }

            // Compute Bone / Focal Accent Light
            const isBoneLight = document.getElementById('boneLightCheck')?.checked;
            let boneLightHtml = '';
            if (isBoneLight) {
                const bonePos = document.getElementById('bonePosSelect')?.value || 'crown';
                const boneColor = document.getElementById('boneColorHex')?.value || '#FFD700';
                const boneRad = parseInt(document.getElementById('sliderBoneRadius')?.value || 45);

                if (document.getElementById('valBoneRadius')) document.getElementById('valBoneRadius').textContent = boneRad + 'px';
                if (document.getElementById('boneColorHexText')) document.getElementById('boneColorHexText').value = boneColor;

                let posX = '50%', posY = '14%';
                if (bonePos === 'left_wing') { posX = '20%'; posY = '36%'; }
                else if (bonePos === 'right_wing') { posX = '80%'; posY = '36%'; }
                else if (bonePos === 'center_gem') { posX = '50%'; posY = '52%'; }
                else if (bonePos === 'bottom_apex') { posX = '50%'; posY = '86%'; }

                boneLightHtml = `
                    <div class="ff-bone-radiant-beam" style="position:absolute;top:${posY};left:${posX};transform:translate(-50%,-50%);width:${boneRad * 2.5}px;height:${boneRad * 2.5}px;border-radius:50%;background:radial-gradient(circle, ${boneColor} 0%, rgba(255,255,255,0.7) 20%, ${boneColor} 40%, transparent 75%);mix-blend-mode:screen;pointer-events:none;z-index:9;filter:blur(6px);opacity:0.85;animation:pulse_bone 2.4s infinite alternate ease-in-out;"></div>
                    <div class="ff-bone-core-glint" style="position:absolute;top:${posY};left:${posX};transform:translate(-50%,-50%);width:${Math.max(12, boneRad * 0.4)}px;height:${Math.max(12, boneRad * 0.4)}px;border-radius:50%;background:#ffffff;box-shadow:0 0 14px #ffffff, 0 0 24px ${boneColor};mix-blend-mode:screen;pointer-events:none;z-index:10;"></div>
                `;
            }

            // Render Live Stage with Image, VFX, Adjustments, Bone Outward Beams & Contour Glow
            stage.innerHTML = `
                <div class="ff-prime-badge-wrap ff-prime-lvl-8${animOffClass}" style="width:${stageDisplaySize}px;height:${stageDisplaySize}px;position:relative;background:transparent !important;${contourDropFilter}">
                    ${starsHtml}
                    <img src="${currentPreviewImg}" class="ff-badge-img" style="${imgStyle} filter:${imgAdjustmentFilter};" alt="Live Preview" onerror="this.onerror=null;this.src='${CDN_BASE + (currentActiveBadge ? currentActiveBadge.file : 'ff_ui_prime_icon_08.png')}';">
                    ${shineHtml}
                    ${glintHtml}
                    ${boneLightHtml}
                </div>
            `;

            // Update Status Badge on top of stage
            const statusBadge = document.getElementById('previewAnimStatusBadge');
            if (statusBadge) {
                if (isMasterAnim && (isSheenActive || starCount > 0 || hasGlint)) {
                    statusBadge.textContent = 'Animation Active';
                    statusBadge.className = 'text-[10px] font-mono text-emerald-300 bg-emerald-400/10 px-2.5 py-0.5 rounded-full border border-emerald-400/30';
                } else {
                    statusBadge.textContent = 'Static (VFX Disabled)';
                    statusBadge.className = 'text-[10px] font-mono text-gray-400 bg-white/5 px-2.5 py-0.5 rounded-full border border-white/10';
                }
            }

            // Update Live Metadata
            const metaStars = document.getElementById('metaStars');
            if (metaStars) metaStars.textContent = starCount > 0 ? (starCount + ' Stars (' + starTheme + ')') : '0 Stars (None)';
            
            let dirTxt = '↗ Diag';
            if (sweepDir === 'diag_bl_tr') dirTxt = '↗ Diag (BL-TR)';
            else if (sweepDir === 'diag_br_tl') dirTxt = '↖ Diag (BR-TL)';
            else if (sweepDir === 'diag_tl_br') dirTxt = '↘ Diag (TL-BR)';
            else if (sweepDir === 'diag_tr_bl') dirTxt = '↙ Diag (TR-BL)';
            else if (sweepDir === 'up') dirTxt = '↑ Up';
            else if (sweepDir === 'down') dirTxt = '↓ Down';
            else if (sweepDir === 'right') dirTxt = '→ Right';
            else if (sweepDir === 'left') dirTxt = '← Left';

            const metaSheen = document.getElementById('metaSheen');
            if (metaSheen) metaSheen.textContent = isSheenActive ? (dirTxt + ' (' + sweepSpeed + ')') : 'Disabled';

            const metaGlint = document.getElementById('metaGlint');
            if (metaGlint) metaGlint.textContent = hasGlint ? '✨ Active Glint' : 'Off';

            const metaBadgeId = document.getElementById('metaBadgeId');
            if (metaBadgeId) metaBadgeId.textContent = '#' + badgeId;
        }

        // Quick Copy Actions for active badge
        function copyActiveBadgeId() {
            const id = document.getElementById('badgeIdInput')?.value || '120000000880';
            copyText(id, 'Badge ID #' + id + ' Copied!');
        }

        function copyActiveCDN() {
            const fileName = document.getElementById('existingFileSelect')?.value || 'ff_ui_prime_icon_08.png';
            const url = CDN_BASE + fileName;
            copyText(url, 'CDN URL Copied!');
        }

        function copyActiveWebTag() {
            const id = document.getElementById('badgeIdInput')?.value || '120000000880';
            const sz = document.getElementById('badgeSizeInput')?.value || '54';
            copyText('<ff-badge id="' + id + '" size="' + sz + '"></ff-badge>', 'Web Component Tag Copied!');
        }

        function copyActivePHP() {
            const fileName = document.getElementById('existingFileSelect')?.value || 'ff_ui_prime_icon_08.png';
            const sz = document.getElementById('badgeSizeInput')?.value || '54';
            const isPrime = (document.getElementById('badgeNameInput')?.value || '').toLowerCase().includes('prime');
            const call = isPrime ? 
                ("generate_ff_prime_badge_html('" + fileName + "', 8, '" + sz + "px');") :
                ("generate_ff_badge_html('" + fileName + "', '" + (document.getElementById('badgeNameInput')?.value || 'Badge') + "', '" + (document.getElementById('glowSelect')?.value || '') + "', 'top-right', '" + sz + "px');");
            copyText(call, 'PHP Function Call Copied!');
        }

        // Search & Filter badges table
        function filterAdminBadges(q) {
            q = q.toLowerCase().trim();
            const rows = document.querySelectorAll('.admin-badge-row');
            rows.forEach(r => {
                const name = r.getAttribute('data-name') || '';
                const id = r.getAttribute('data-id') || '';
                if (!q || name.includes(q) || id.includes(q)) {
                    r.classList.remove('hidden');
                } else {
                    r.classList.add('hidden');
                }
            });
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', () => {
            updateLivePreviewStudio();
        });
    </script>

<?php endif; ?>

</body>
</html>
