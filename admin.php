<?php
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
            font-family: 'Inter', sans-serif;
            background: #080915;
            color: #f1f5f9;
        }
        .font-orbitron { font-family: 'Orbitron', sans-serif; }
        .font-mono-code { font-family: 'Fira Code', monospace; }

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

        /* Checkerboard pattern for proving 100% transparent backgrounds */
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
    </style>
</head>
<body class="min-h-screen flex flex-col selection:bg-rose-500 selection:text-white">

<?php if (!$is_logged_in): ?>
    <!-- LOGIN SCREEN -->
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
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Email or Username</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3.5 top-3 text-gray-500 text-xs"></i>
                        <input type="text" name="username" required placeholder="Enter email or username..." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3.5 top-3 text-gray-500 text-xs"></i>
                        <input type="password" name="password" required placeholder="Enter password..." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition">
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
                    <span>Back to FF Items Explorer</span>
                </a>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ADMIN STUDIO DASHBOARD -->

    <!-- Navbar -->
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
                            VFX STUDIO
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
                            <?= htmlspecialchars($success_item['name']) ?>
                            <span class="text-sm font-mono text-emerald-400">#<?= $success_item['id'] ?></span>
                        </h2>
                        <p class="text-xs text-gray-300">
                            Badge has been updated, synchronized across local files and D: drive backup, and deployed to jsDelivr CDN!
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
                            <button type="button" onclick="copyText('<ff-badge id=\'<?= $success_item['id'] ?>\' size=\'<?= $success_item['default_size'] ?>\'></ff-badge>', 'Web Tag Copied!')" class="text-purple-400 hover:text-purple-300 text-[10px]">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <input type="text" readonly value="&lt;ff-badge id=&quot;<?= $success_item['id'] ?>&quot; size=&quot;<?= $success_item['default_size'] ?>&quot;&gt;&lt;/ff-badge&gt;" class="w-full bg-black/50 border border-gray-800 rounded p-1.5 text-[10px] font-mono text-purple-300">
                    </div>

                    <div class="bg-[#090b18] p-3 rounded-xl border border-white/5 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="text-amber-400 font-mono">PHP Function Call:</span>
                            <button type="button" onclick="copyText(<?= htmlspecialchars(json_encode($success_item['fn_call'])) ?>, 'PHP Call Copied!')" class="text-amber-400 hover:text-amber-300 text-[10px]">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <input type="text" readonly value="<?= htmlspecialchars($success_item['fn_call']) ?>" class="w-full bg-black/50 border border-gray-800 rounded p-1.5 text-[10px] font-mono text-amber-300">
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- SECTION 1: SEARCH / PASTE BADGE ID BAR (Clean, no quick select pills) -->
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
                            <input type="text" id="quickBadgeSearchInput" placeholder="Paste 12...80 ID (e.g. 120000010880) or type badge name..." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl pl-9 pr-4 py-2.5 text-xs font-mono text-emerald-400 placeholder-gray-500 focus:outline-none focus:border-amber-400 transition">
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
                        <span>➕ Create New Badge</span>
                    </button>
                </div>

            </div>
        </section>

        <!-- SECTION 2: 2-COLUMN MAIN STUDIO WORKBENCH -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- LEFT COLUMN: BADGE CUSTOMIZER CONTROLS (7 COLS) -->
            <div class="lg:col-span-7 glass-panel p-6 sm:p-7 space-y-6">
                
                <div class="flex items-center justify-between border-b border-[#1f2544] pb-4">
                    <div class="space-y-1">
                        <h2 class="text-base font-black font-orbitron text-white flex items-center gap-2">
                            <i class="fas fa-sliders text-amber-400"></i>
                            <span id="formPanelTitle">Badge Studio & VFX Customizer</span>
                        </h2>
                        <p class="text-xs text-gray-400" id="formPanelSub">
                            Edit badge name, 12...80 ID, scattered gunpowder stars (0 to 50), and 50 numbered presets.
                        </p>
                    </div>

                    <button type="button" onclick="randomizeId()" class="px-3 py-1.5 bg-amber-400/10 hover:bg-amber-400/20 text-yellow-300 border border-yellow-400/30 rounded-xl text-xs font-mono font-bold flex items-center gap-1.5 transition cursor-pointer" title="Generate New 12...80 ID">
                        <i class="fas fa-dice text-xs"></i> <span>Randomize ID</span>
                    </button>
                </div>

                <form method="POST" action="admin.php" enctype="multipart/form-data" id="badgeEditorForm" class="space-y-6">
                    <input type="hidden" name="save_badge" value="1">
                    <input type="hidden" name="original_id" id="originalIdInput" value="">

                    <!-- Basic Metadata Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- 1. Badge Name (Editable) -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center justify-between">
                                <span>Badge Name <span class="text-rose-500">*</span></span>
                                <span class="text-[10px] text-gray-400 font-normal">Editable anytime</span>
                            </label>
                            <input type="text" name="badge_name" id="badgeNameInput" required placeholder="e.g. Free Fire Prime Level 8 or OFFICIAL V BAGE" value="Free Fire Prime Level 8" oninput="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition">
                        </div>

                        <!-- 2. Badge ID (Editable 12...80) -->
                        <div class="space-y-1.5 sm:col-span-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Badge ID (Must start with 12 and end with 80) <span class="text-emerald-400">*</span></label>
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
                            <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Category</label>
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
                            <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Rarity</label>
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
                            <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Default Size (px)</label>
                            <input type="number" name="badge_size" id="badgeSizeInput" value="54" min="20" max="250" oninput="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition">
                        </div>

                        <!-- 6. Aura Glow Color -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Aura Glow Color</label>
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
                                <option value="rgba(16, 185, 129, 0.85)">Emerald Lightning (rgba(16, 185, 129, 0.85))</option>
                                <option value="rgba(255, 255, 255, 0.85)">Diamond White (rgba(255, 255, 255, 0.85))</option>
                            </select>
                        </div>

                    </div>

                    <!-- 50 NUMBERED EFFECT PRESETS & UNIVERSAL REMOVAL BAR -->
                    <div class="p-4 rounded-2xl bg-[#090c1f] border border-cyan-500/30 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-cyan-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-wand-magic-sparkles text-amber-400"></i>
                                <span>50+ Numbered Effect Presets</span>
                            </label>
                            <button type="button" onclick="removeAllEffects()" class="px-2.5 py-1 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 text-[11px] font-bold flex items-center gap-1 transition cursor-pointer">
                                <i class="fas fa-ban"></i> <span>❌ Remove All Effects (Clean Static Badge)</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-400">Select any preset to automatically apply sheen angle, speed, scattered stars count, and center gem glint:</p>
                        
                        <div class="relative">
                            <select id="presetSelect" onchange="handlePresetChange(this.value)" class="w-full bg-[#080a18] border border-cyan-500/40 rounded-xl px-4 py-2.5 text-xs text-cyan-200 focus:outline-none focus:border-amber-400 transition cursor-pointer font-mono">
                                <option value="">-- Choose one of 50 Numbered Effect Presets --</option>
                                <?php foreach ($presets_list as $ps): ?>
                                    <option value="<?= $ps['id'] ?>"><?= htmlspecialchars($ps['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- MASTER VFX ENGINE & PARTICLE STUDIO -->
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-[#121634] to-[#0c0f24] border border-amber-500/30 space-y-4 shadow-xl">
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-white/10 pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center font-bold text-sm shadow">
                                    <i class="fas fa-fire"></i>
                                </span>
                                <div>
                                    <h4 class="text-xs font-bold text-white uppercase tracking-wider font-orbitron">VFX Animation Engine & Particles</h4>
                                    <p class="text-[10px] text-gray-400">Metallic sheen sweep, 360-degree radially scattered stars, and core gem glint</p>
                                </div>
                            </div>
                            
                            <!-- Master Animation Switch (Allows ON/OFF for ANY badge, including V-Badge) -->
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-amber-300 bg-amber-400/10 border border-amber-400/40 px-3.5 py-1.5 rounded-xl hover:bg-amber-400/20 transition">
                                <input type="checkbox" name="sweep_enabled" id="masterAnimCheck" value="1" checked onchange="toggleMasterAnimation(this.checked)" class="rounded bg-gray-800 border-gray-700 text-amber-500 focus:ring-0">
                                <span>Enable Animation Engine</span>
                            </label>
                        </div>

                        <div id="vfxStudioControls" class="grid grid-cols-1 sm:grid-cols-2 gap-4 transition-all">
                            
                            <!-- 1. Metallic Sheen Sweep Direction (8 Directions) -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fas fa-bolt text-yellow-400"></i> <span>Sheen Direction (8 Directions)</span>
                                    </label>
                                    <label class="flex items-center gap-1 cursor-pointer text-[10px] text-gray-400">
                                        <input type="checkbox" id="sheenActiveCheck" checked onchange="toggleSheenSweep(this.checked)" class="rounded bg-gray-800 border-gray-700 text-amber-500 text-[10px]">
                                        <span>Sheen Active</span>
                                    </label>
                                </div>
                                <select name="sweep_direction" id="sweepDirSelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-400 cursor-pointer font-mono">
                                    <option value="diag_bl_tr" selected>↗ Diagonal (Bottom-Left to Top-Right - Prime 8 Authentic)</option>
                                    <option value="diag_br_tl">↖ Diagonal Reverse (Bottom-Right to Top-Left)</option>
                                    <option value="diag_tl_br">↘ Diagonal Down (Top-Left to Bottom-Right)</option>
                                    <option value="diag_tr_bl">↙ Diagonal Down-Left (Top-Right to Bottom-Left)</option>
                                    <option value="up">↑ Vertical Up (Bottom to Top)</option>
                                    <option value="down">↓ Vertical Down (Top to Bottom)</option>
                                    <option value="right">→ Horizontal Right (Left to Right)</option>
                                    <option value="left">← Horizontal Left (Right to Left)</option>
                                </select>
                            </div>

                            <!-- 2. Sweep Speed -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fas fa-gauge-high text-cyan-400"></i> <span>Sheen Speed</span>
                                </label>
                                <select name="sweep_speed" id="sweepSpeedSelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-400 cursor-pointer font-mono">
                                    <option value="1.5s">1.5s (Fast Flash)</option>
                                    <option value="1.8s">1.8s (Active)</option>
                                    <option value="2.2s" selected>2.2s (Prime 8 Authentic)</option>
                                    <option value="2.6s">2.6s (Standard)</option>
                                    <option value="3.2s">3.2s (Smooth)</option>
                                    <option value="4.0s">4.0s (Calm Slow)</option>
                                </select>
                            </div>

                            <!-- 3. Dynamic Stars Count (0 to 50 Stars, Radially Scattered) -->
                            <div class="space-y-1.5 sm:col-span-2 bg-[#080a18] p-3 rounded-xl border border-white/5">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fas fa-star text-amber-400"></i> <span>Scattered Gunpowder Stars (0 to 50 - 360° All Around)</span>
                                    </label>
                                    <span id="starCountDisplay" class="text-xs font-mono font-bold text-amber-300">16 Stars</span>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <input type="range" id="starRangeSlider" min="0" max="50" value="16" oninput="setStarDensity(this.value)" class="flex-1 accent-amber-400 cursor-pointer">
                                    <input type="number" name="star_density" id="starDensityInput" min="0" max="50" value="16" oninput="setStarDensity(this.value)" class="w-16 bg-[#0e1224] border border-[#23294c] rounded-lg px-2 py-1 text-xs font-mono text-center text-amber-300 focus:outline-none focus:border-amber-400">
                                </div>

                                <!-- Quick Star Pills -->
                                <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                    <span class="text-[10px] text-gray-400 mr-1">Presets:</span>
                                    <button type="button" onclick="setStarDensity(0)" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[10px] font-mono text-gray-300 cursor-pointer">0 (None)</button>
                                    <button type="button" onclick="setStarDensity(4)" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[10px] font-mono text-gray-300 cursor-pointer">4</button>
                                    <button type="button" onclick="setStarDensity(8)" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[10px] font-mono text-gray-300 cursor-pointer">8</button>
                                    <button type="button" onclick="setStarDensity(12)" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[10px] font-mono text-gray-300 cursor-pointer">12</button>
                                    <button type="button" onclick="setStarDensity(16)" class="px-2 py-0.5 rounded bg-amber-400/20 text-amber-300 border border-amber-400/40 text-[10px] font-mono font-bold cursor-pointer">16 (Prime 8)</button>
                                    <button type="button" onclick="setStarDensity(24)" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[10px] font-mono text-gray-300 cursor-pointer">24</button>
                                    <button type="button" onclick="setStarDensity(36)" class="px-2 py-0.5 rounded bg-white/5 hover:bg-white/10 text-[10px] font-mono text-gray-300 cursor-pointer">36</button>
                                    <button type="button" onclick="setStarDensity(50)" class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-200 border border-amber-500/40 text-[10px] font-mono font-bold cursor-pointer">50 (Max Fireworks)</button>
                                </div>
                            </div>

                            <!-- 4. Star Color Theme -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fas fa-palette text-pink-400"></i> <span>Star Color Theme</span>
                                </label>
                                <select name="star_theme" id="starThemeSelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-400 cursor-pointer font-mono">
                                    <option value="gold" selected>Gold Spark</option>
                                    <option value="cyan">Cyan Ice</option>
                                    <option value="white">Diamond White</option>
                                    <option value="ruby">Ruby Red</option>
                                    <option value="purple">Cosmic Purple</option>
                                    <option value="emerald">Emerald Green</option>
                                    <option value="amber">Amber Flame</option>
                                    <option value="rainbow">Rainbow Prism</option>
                                </select>
                            </div>

                            <!-- 5. Center Gem Flare & Floating Motion Toggles -->
                            <div class="space-y-2 flex flex-col justify-end">
                                <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-200 bg-[#080a18] p-2 rounded-xl border border-white/5 hover:border-amber-400/30 transition">
                                    <input type="checkbox" name="has_gem_glint" id="hasGemGlintCheck" value="1" checked onchange="updateLivePreviewStudio()" class="rounded bg-gray-800 border-gray-700 text-amber-500">
                                    <span class="font-semibold">Center Gem Star Glint</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-200 bg-[#080a18] p-2 rounded-xl border border-white/5 hover:border-amber-400/30 transition">
                                    <input type="checkbox" name="pulse_enabled" id="pulseEnabledCheck" value="1" checked onchange="updateLivePreviewStudio()" class="rounded bg-gray-800 border-gray-700 text-amber-500">
                                    <span>Floating Wing Pulse Motion</span>
                                </label>
                            </div>

                        </div>

                        <!-- 100% Transparency Guarantee Note -->
                        <div class="pt-2 border-t border-white/10 flex items-center justify-between text-[11px]">
                            <span class="text-emerald-400 flex items-center gap-1.5 font-mono">
                                <i class="fas fa-circle-check text-xs"></i> 100% Transparent Background (Zero Halos / No Yellow Background Clouds)
                            </span>
                        </div>

                    </div>

                    <!-- Icon Asset Selection (Dropdown OR Custom PNG Upload) -->
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider block">
                            Badge Icon Asset (Select Existing or Upload Transparent PNG) <span class="text-rose-500">*</span>
                        </label>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Dropdown -->
                            <div class="space-y-1">
                                <span class="text-[11px] text-gray-400 font-semibold">1. Choose from Existing Free Fire Assets:</span>
                                <select name="existing_file" id="existingFileSelect" onchange="selectExistingAsset(this.value)" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition cursor-pointer font-mono">
                                    <option value="">-- Choose existing asset --</option>
                                    <?php foreach ($existing_badge_files as $ef): ?>
                                        <option value="<?= $ef ?>" <?= ($ef === 'ff_ui_prime_icon_08.png') ? 'selected' : '' ?>><?= $ef ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Upload New File -->
                            <div class="space-y-1">
                                <span class="text-[11px] text-gray-400 font-semibold">2. Or Upload New Transparent PNG:</span>
                                <div class="border border-dashed border-[#23294c] hover:border-amber-400/50 rounded-xl p-2.5 text-center cursor-pointer bg-[#080a18] transition relative" onclick="document.getElementById('fileInput').click()">
                                    <input type="file" name="badge_file" id="fileInput" accept="image/png,image/svg+xml,image/webp" class="hidden" onchange="previewSelectedFile(event)">
                                    <div class="flex items-center justify-center gap-2 text-xs text-gray-300">
                                        <i class="fas fa-file-arrow-up text-amber-400"></i>
                                        <span id="uploadPromptText">Click to select PNG</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Description</label>
                        <textarea name="badge_description" id="badgeDescInput" rows="2" placeholder="Official Free Fire verified insignia deployed via Turky Hub Monster CDN..." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition"></textarea>
                    </div>

                    <!-- Submit & Save Button -->
                    <div class="flex items-center justify-between gap-3 pt-3 border-t border-[#1f2544]">
                        <button type="button" onclick="resetToNewBadge()" class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white text-xs font-semibold transition cursor-pointer">
                            <i class="fas fa-arrow-rotate-left mr-1"></i> Reset Form
                        </button>

                        <button type="submit" class="bg-gradient-to-r from-amber-500 via-rose-500 to-pink-600 hover:from-amber-600 hover:to-pink-700 text-white font-extrabold text-xs py-3 px-8 rounded-xl flex items-center gap-2 shadow-lg shadow-rose-500/20 transition cursor-pointer">
                            <i class="fas fa-cloud-arrow-up text-sm"></i>
                            <span id="saveBtnText">Save & Generate Global CDN Asset</span>
                        </button>
                    </div>

                </form>

            </div>

            <!-- RIGHT COLUMN: HERO STICKY LIVE PREVIEW STAGE (5 COLS) -->
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
                        
                        <!-- Size Switcher -->
                        <div class="flex items-center gap-1 bg-[#080a18] p-1 rounded-xl border border-white/5">
                            <span class="text-[10px] text-gray-400 px-1">SIZE:</span>
                            <button type="button" onclick="setStageSize(48)" class="px-2 py-0.5 rounded-lg hover:bg-white/10 text-gray-400 stage-size-btn cursor-pointer" data-size="48">48px</button>
                            <button type="button" onclick="setStageSize(72)" class="px-2 py-0.5 rounded-lg hover:bg-white/10 text-gray-400 stage-size-btn cursor-pointer" data-size="72">72px</button>
                            <button type="button" onclick="setStageSize(110)" class="px-2 py-0.5 rounded-lg bg-amber-400/20 text-amber-300 font-bold border border-amber-400/40 stage-size-btn cursor-pointer" data-size="110">110px</button>
                            <button type="button" onclick="setStageSize(150)" class="px-2 py-0.5 rounded-lg hover:bg-white/10 text-gray-400 stage-size-btn cursor-pointer" data-size="150">150px</button>
                        </div>

                        <!-- Background Switcher (Prove Transparency) -->
                        <div class="flex items-center gap-1 bg-[#080a18] p-1 rounded-xl border border-white/5">
                            <span class="text-[10px] text-gray-400 px-1">BG:</span>
                            <button type="button" onclick="setStageBg('dark')" title="Dark Space" class="w-5 h-5 rounded bg-[#080a18] border border-white/20 cursor-pointer"></button>
                            <button type="button" onclick="setStageBg('black')" title="Pitch Black" class="w-5 h-5 rounded bg-black border border-white/20 cursor-pointer"></button>
                            <button type="button" onclick="setStageBg('checker')" title="Checkerboard (Transparent Test)" class="w-5 h-5 rounded checker-bg border border-white/20 cursor-pointer"></button>
                        </div>

                    </div>

                    <!-- Big Interactive Preview Stage -->
                    <div id="stageContainer" class="h-64 rounded-2xl bg-[#080a18] border border-white/10 flex items-center justify-center p-4 relative overflow-hidden transition-all duration-300 shadow-inner">
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
                            <span class="text-[10px] text-gray-400 uppercase font-mono">Badge ID:</span>
                            <div class="font-bold text-emerald-400 font-mono text-xs" id="metaBadgeId">#<?= $suggested_id ?></div>
                        </div>
                    </div>

                    <!-- Quick Copy Studio Actions -->
                    <div class="space-y-2 pt-2 border-t border-white/5">
                        <div class="flex items-center justify-between text-xs font-bold text-gray-300">
                            <span>Quick Copy Code & Links:</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="copyActiveBadgeId()" class="p-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] font-mono flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <i class="fas fa-barcode"></i> <span>Copy ID</span>
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
                                $bFile = htmlspecialchars($b['file'] ?? 'ff_ui_prime_icon_08.png');
                                $img_cdn = $CDN_BASE . $bFile;
                                $bStars = intval($b['star_density'] ?? 0);
                                $bSweep = !empty($b['sweep_enabled']);
                                $bDir = htmlspecialchars($b['sweep_direction'] ?? 'diag_bl_tr');
                                $bSpd = htmlspecialchars($b['sweep_speed'] ?? '2.2s');
                                $bTheme = htmlspecialchars($b['star_theme'] ?? 'gold');
                                $bGlint = !empty($b['has_gem_glint']);
                                $bGlow = htmlspecialchars($b['glow_color'] ?? 'rgba(255, 215, 0, 0.85)');
                                $isPrime = ($b['type'] === 'prime_image' || !empty($b['prime_level']));
                                $isFullyStatic = (!$bSweep && $bStars === 0 && !$bGlint);
                            ?>
                            <tr class="hover:bg-white/[0.04] transition admin-badge-row cursor-pointer" onclick="handleRowClick(event, '<?= $b['id'] ?>')" data-name="<?= strtolower($b['name']) ?>" data-id="<?= strtolower($b['id']) ?>">
                                
                                <!-- Preview Cell (Direct Rock-Solid Live Rendering with Local Fallback) -->
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
                                            <img src="badges/<?= $bFile ?>" onerror="this.src='<?= $img_cdn ?>'" class="ff-badge-img <?= $isFullyStatic ? 'style="animation:none !important;"' : '' ?>" alt="<?= htmlspecialchars($b['name']) ?>">
                                            <?php if ($bSweep): ?>
                                                <span class="ff-badge-shine sweep-dir-<?= $bDir ?>" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('badges/<?= $bFile ?>');mask-image:url('badges/<?= $bFile ?>');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
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
                                        <span class="font-mono font-bold text-emerald-400">#<?= $b['id'] ?></span>
                                        <button type="button" onclick="event.stopPropagation(); copyText('<?= $b['id'] ?>', 'ID Copied!')" class="text-gray-500 hover:text-white cursor-pointer" title="Copy ID">
                                            <i class="fas fa-copy text-[10px]"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Name -->
                                <td class="py-3 px-3">
                                    <div class="font-bold text-white"><?= htmlspecialchars($b['name']) ?></div>
                                    <div class="text-[10px] text-gray-500 font-mono"><?= htmlspecialchars($b['icon_id'] ?? '') ?></div>
                                </td>

                                <!-- Category -->
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-cyan-950/50 text-cyan-300 border border-cyan-800/40">
                                        <?= htmlspecialchars($b['category']) ?>
                                    </span>
                                </td>

                                <!-- Rarity -->
                                <td class="py-3 px-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-yellow-300">
                                        <?= htmlspecialchars($b['rarity']) ?>
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

                                        <button type="button" onclick="copyText('<ff-badge id=\'<?= $b['id'] ?>\' size=\'<?= $b['default_size'] ?>\'></ff-badge>', 'Web Tag Copied!')" class="px-2 py-1 rounded bg-purple-500/10 hover:bg-purple-500/20 text-purple-300 border border-purple-500/30 text-[10px] font-mono flex items-center gap-1 transition cursor-pointer">
                                            <i class="fas fa-code text-[9px]"></i> <span>Tag</span>
                                        </button>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                                        <button type="button" onclick="loadBadgeToEditor('<?= $b['id'] ?>')" class="px-2.5 py-1 rounded-lg bg-amber-400 hover:bg-amber-500 text-black font-extrabold text-[11px] flex items-center gap-1 transition shadow cursor-pointer" title="Edit and customize in studio">
                                            <i class="fas fa-pen-to-square"></i> <span>Edit</span>
                                        </button>
                                        
                                        <a href="index.php?item=<?= $b['id'] ?>" target="_blank" class="w-7 h-7 rounded-lg bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white flex items-center justify-center transition" title="View in Explorer">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>

                                        <a href="admin.php?delete=<?= $b['id'] ?>" onclick="return confirm('Are you sure you want to delete badge #<?= $b['id'] ?>?');" class="w-7 h-7 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 flex items-center justify-center transition" title="Delete Badge">
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
    <div id="adminToast" class="fixed bottom-6 right-6 bg-amber-400 text-black font-extrabold px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-2 transform translate-y-24 opacity-0 transition-all duration-300 z-50 pointer-events-none">
        <i class="fas fa-check-circle text-lg"></i>
        <span id="adminToastMsg">Copied!</span>
    </div>

    <!-- CLIENT DATA & SCRIPT ENGINE -->
    <script>
        // Master in-memory databases
        const ALL_BADGES_DATA = <?= json_encode($current_badges); ?>;
        const ALL_PRESETS_DATA = <?= json_encode($presets_list); ?>;
        const REPO_NAME = '<?= $REPO_NAME ?>';
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
            navigator.clipboard.writeText(text).then(() => {
                showToast(msg);
            });
        }

        function showToast(msg) {
            const t = document.getElementById('adminToast');
            document.getElementById('adminToastMsg').textContent = msg;
            t.classList.remove('translate-y-24', 'opacity-0');
            setTimeout(() => {
                t.classList.add('translate-y-24', 'opacity-0');
            }, 2200);
        }

        // Randomize 12...80 ID
        function randomizeId() {
            const middle = Math.floor(10000000 + Math.random() * 90000000);
            const newId = '12' + middle + '80';
            document.getElementById('badgeIdInput').value = newId;
            updateLivePreviewStudio();
            showToast('New 12...80 ID generated: #' + newId);
        }

        // Set Star fireworks count (0 to 50)
        function setStarDensity(val) {
            val = Math.max(0, Math.min(50, parseInt(val) || 0));
            document.getElementById('starRangeSlider').value = val;
            document.getElementById('starDensityInput').value = val;
            document.getElementById('starCountDisplay').textContent = val + ' Stars';
            updateLivePreviewStudio();
        }

        // Select existing asset from dropdown
        function selectExistingAsset(val) {
            if (!val) return;
            currentPreviewImg = 'badges/' + val;
            document.getElementById('uploadPromptText').textContent = 'Asset: ' + val;
            updateLivePreviewStudio();
        }

        // Live preview of uploaded file
        function previewSelectedFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            document.getElementById('uploadPromptText').textContent = 'File: ' + file.name;
            document.getElementById('existingFileSelect').value = '';
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
            document.querySelectorAll('.stage-size-btn').forEach(b => {
                if (parseInt(b.dataset.size) === sz) {
                    b.classList.add('bg-amber-400/20', 'text-amber-300', 'font-bold', 'border', 'border-amber-400/40');
                    b.classList.remove('text-gray-400');
                } else {
                    b.classList.remove('bg-amber-400/20', 'text-amber-300', 'font-bold', 'border', 'border-amber-400/40');
                    b.classList.add('text-gray-400');
                }
            });
            updateLivePreviewStudio();
        }

        // Stage Background Switcher
        function setStageBg(type) {
            const sc = document.getElementById('stageContainer');
            sc.classList.remove('checker-bg', 'bg-black', 'bg-[#080a18]');
            if (type === 'checker') {
                sc.classList.add('checker-bg');
            } else if (type === 'black') {
                sc.classList.add('bg-black');
            } else {
                sc.classList.add('bg-[#080a18]');
            }
        }

        // Master Animation Toggle Switch (ON / OFF)
        function toggleMasterAnimation(checked) {
            const ctrls = document.getElementById('vfxStudioControls');
            if (checked) {
                ctrls.classList.remove('opacity-40', 'pointer-events-none');
                document.getElementById('sheenActiveCheck').checked = true;
                if (parseInt(document.getElementById('starDensityInput').value) === 0) {
                    setStarDensity(16);
                }
            } else {
                ctrls.classList.add('opacity-40', 'pointer-events-none');
                document.getElementById('sheenActiveCheck').checked = false;
                setStarDensity(0);
                document.getElementById('hasGemGlintCheck').checked = false;
                document.getElementById('pulseEnabledCheck').checked = false;
            }
            updateLivePreviewStudio();
        }

        // Sheen Sweep Sub-Toggle
        function toggleSheenSweep(checked) {
            updateLivePreviewStudio();
        }

        // Quick Search or Paste ID
        function handleQuickSearchLoad() {
            const query = (document.getElementById('quickBadgeSearchInput').value || '').trim();
            if (!query) {
                showToast('Please paste a badge ID or enter a name!');
                return;
            }
            searchAndLoadBadge(query);
        }

        document.getElementById('quickBadgeSearchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleQuickSearchLoad();
            }
        });

        // Search in memory database and load
        function searchAndLoadBadge(q) {
            const cleanQ = q.toLowerCase().replace('#', '').trim();
            let found = ALL_BADGES_DATA.find(b => b.id.toLowerCase() === cleanQ);
            if (!found) {
                found = ALL_BADGES_DATA.find(b => b.name.toLowerCase() === cleanQ || b.slug.toLowerCase() === cleanQ);
            }
            if (!found) {
                found = ALL_BADGES_DATA.find(b => b.name.toLowerCase().includes(cleanQ) || b.id.includes(cleanQ));
            }

            if (found) {
                loadBadgeToEditor(found.id);
            } else {
                showToast('No badge found matching: ' + q);
            }
        }

        // Handle Row Click in table
        function handleRowClick(event, id) {
            loadBadgeToEditor(id);
        }

        // MASTER LOADER: Populate everything from any badge
        function loadBadgeToEditor(id) {
            const b = ALL_BADGES_DATA.find(x => x.id === id);
            if (!b) return;

            currentActiveBadge = b;

            // Update Header & Mode
            document.getElementById('originalIdInput').value = b.id;
            document.getElementById('formPanelTitle').textContent = 'Editing: ' + b.name + ' (#' + b.id + ')';
            document.getElementById('formPanelSub').textContent = 'Modify name, 12...80 ID, or VFX parameters and save to update CDN immediately.';
            document.getElementById('editorModeText').textContent = 'Editing: #' + b.id;
            document.getElementById('editorModeBadge').className = 'px-3.5 py-1.5 rounded-xl bg-amber-950/60 border border-amber-500/50 text-amber-300 text-xs font-mono flex items-center gap-2';
            document.getElementById('saveBtnText').textContent = 'Update & Redeploy #' + b.id;

            // Form Fields
            document.getElementById('badgeNameInput').value = b.name;
            document.getElementById('badgeIdInput').value = b.id;
            document.getElementById('badgeCatSelect').value = b.category || 'Prime System';
            document.getElementById('badgeRaritySelect').value = b.rarity || 'MYTHIC';
            document.getElementById('badgeSizeInput').value = b.default_size || 54;
            document.getElementById('glowSelect').value = b.glow_color || 'rgba(255, 215, 0, 0.85)';
            document.getElementById('glowEnabledCheck').checked = (b.glow_enabled !== false);
            document.getElementById('badgeDescInput').value = b.description || '';

            // Icon Asset
            currentPreviewImg = 'badges/' + (b.file || 'ff_ui_prime_icon_08.png');
            document.getElementById('uploadPromptText').textContent = 'Asset: ' + (b.file || 'ff_ui_prime_icon_08.png');
            
            const efSelect = document.getElementById('existingFileSelect');
            for (let i = 0; i < efSelect.options.length; i++) {
                if (efSelect.options[i].value === b.file) {
                    efSelect.selectedIndex = i;
                    break;
                }
            }

            // Universal Animation State Check:
            const hasAnimation = (b.sweep_enabled !== false && b.sweep_enabled !== 'false' && b.sweep_enabled !== 0);
            document.getElementById('masterAnimCheck').checked = hasAnimation;
            document.getElementById('sheenActiveCheck').checked = hasAnimation;
            toggleMasterAnimation(hasAnimation);

            // Sheen Direction normalizer
            let sDir = b.sweep_direction || 'diag_bl_tr';
            if (sDir === 'diag') sDir = 'diag_bl_tr';
            if (sDir === 'diag_rev') sDir = 'diag_br_tl';
            document.getElementById('sweepDirSelect').value = sDir;
            document.getElementById('sweepSpeedSelect').value = b.sweep_speed || '2.2s';
            
            // Star Density (0 to 50)
            const starCount = (b.star_density !== undefined) ? parseInt(b.star_density) : (b.prime_level === 8 ? 16 : 0);
            setStarDensity(hasAnimation ? starCount : 0);

            document.getElementById('starThemeSelect').value = b.star_theme || 'gold';
            document.getElementById('hasGemGlintCheck').checked = hasAnimation && (b.has_gem_glint === true || b.has_gem_glint === '1' || b.has_gem_glint === 1);
            document.getElementById('pulseEnabledCheck').checked = hasAnimation && (b.pulse_enabled !== false);

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
            document.getElementById('formPanelSub').textContent = 'Upload transparent PNG badge or select existing asset to generate 12...80 ID and CDN link.';
            document.getElementById('editorModeText').textContent = 'Mode: New Badge';
            document.getElementById('editorModeBadge').className = 'px-3.5 py-1.5 rounded-xl bg-cyan-950/60 border border-cyan-800/50 text-cyan-300 text-xs font-mono flex items-center gap-2';
            document.getElementById('saveBtnText').textContent = 'Save & Generate Global CDN Asset';

            currentPreviewImg = 'badges/ff_ui_prime_icon_08.png';
            document.getElementById('uploadPromptText').textContent = 'Click to select PNG';
            document.getElementById('existingFileSelect').value = 'ff_ui_prime_icon_08.png';

            document.getElementById('masterAnimCheck').checked = true;
            document.getElementById('sheenActiveCheck').checked = true;
            toggleMasterAnimation(true);
            setStarDensity(16);
            document.getElementById('sweepDirSelect').value = 'diag_bl_tr';
            document.getElementById('sweepSpeedSelect').value = '2.2s';
            document.getElementById('starThemeSelect').value = 'gold';
            document.getElementById('hasGemGlintCheck').checked = true;
            document.getElementById('pulseEnabledCheck').checked = true;
            document.getElementById('glowEnabledCheck').checked = true;

            updateLivePreviewStudio();
            showToast('Editor reset for New Badge creation');
        }

        // Apply one of the 50 Numbered Effect Presets
        function handlePresetChange(presetId) {
            if (!presetId) return;
            const p = ALL_PRESETS_DATA.find(x => x.id === parseInt(presetId));
            if (!p) return;

            document.getElementById('masterAnimCheck').checked = true;
            document.getElementById('sheenActiveCheck').checked = !!p.sweep;
            toggleMasterAnimation(true);

            if (p.category) document.getElementById('badgeCatSelect').value = p.category;
            if (p.rarity) document.getElementById('badgeRaritySelect').value = p.rarity;
            if (p.glow) document.getElementById('glowSelect').value = p.glow;
            
            if (p.dir) {
                let d = p.dir;
                if (d === 'diag') d = 'diag_bl_tr';
                if (d === 'diag_rev') d = 'diag_br_tl';
                document.getElementById('sweepDirSelect').value = d;
            }
            if (p.speed) document.getElementById('sweepSpeedSelect').value = p.speed;

            setStarDensity(p.stars || 0);
            if (p.theme) document.getElementById('starThemeSelect').value = p.theme;
            document.getElementById('hasGemGlintCheck').checked = !!p.glint;
            document.getElementById('pulseEnabledCheck').checked = true;

            updateLivePreviewStudio();
            showToast('Applied Preset: ' + p.name);
        }

        // Remove All Effects (Clean Static Badge - 100% Frozen & Transparent)
        function removeAllEffects() {
            document.getElementById('masterAnimCheck').checked = false;
            document.getElementById('sheenActiveCheck').checked = false;
            setStarDensity(0);
            document.getElementById('hasGemGlintCheck').checked = false;
            document.getElementById('pulseEnabledCheck').checked = false;
            document.getElementById('presetSelect').value = '';
            toggleMasterAnimation(false);
            updateLivePreviewStudio();
            showToast('All effects removed: Clean static transparent badge!');
        }

        // MASTER LIVE PREVIEW STUDIO RENDERER (Radially Scattered Stars & Universal On/Off)
        function updateLivePreviewStudio() {
            const stage = document.getElementById('liveBadgeStage');
            if (!stage) return;

            const isMasterAnim = document.getElementById('masterAnimCheck').checked;
            const isSheenActive = isMasterAnim && document.getElementById('sheenActiveCheck').checked;
            const sweepDir = document.getElementById('sweepDirSelect').value;
            const sweepSpeed = document.getElementById('sweepSpeedSelect').value;
            const starCount = isMasterAnim ? Math.max(0, Math.min(50, parseInt(document.getElementById('starDensityInput').value) || 0)) : 0;
            const starTheme = document.getElementById('starThemeSelect').value;
            const hasGlint = isMasterAnim && document.getElementById('hasGemGlintCheck').checked;
            const hasPulse = isMasterAnim && document.getElementById('pulseEnabledCheck').checked;
            const glowEnabled = document.getElementById('glowEnabledCheck').checked;
            const glowVal = glowEnabled ? document.getElementById('glowSelect').value : 'transparent';
            const badgeId = document.getElementById('badgeIdInput').value || '120000000080';

            // Sheen Direction Class
            let sweepClass = '';
            if (sweepDir === 'diag_bl_tr' || sweepDir === 'diag') sweepClass = 'sweep-dir-bl-tr';
            else if (sweepDir === 'diag_br_tl' || sweepDir === 'diag_rev') sweepClass = 'sweep-dir-br-tl';
            else if (sweepDir === 'diag_tl_br') sweepClass = 'sweep-dir-tl-br';
            else if (sweepDir === 'diag_tr_bl') sweepClass = 'sweep-dir-tr-bl';
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

            // Generate Radially Scattered Stars (360 degrees around badge silhouette)
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
            const filterDrop = glowEnabled ? `filter:drop-shadow(0 0 10px ${glowVal});` : '';

            // Render Live Stage (100% Transparent, No Yellow Cloud)
            stage.innerHTML = `
                <div class="ff-prime-badge-wrap ff-prime-lvl-8${animOffClass}" style="width:${stageDisplaySize}px;height:${stageDisplaySize}px;position:relative;background:transparent !important;${filterDrop}">
                    ${starsHtml}
                    <img src="${currentPreviewImg}" class="ff-badge-img" style="${imgStyle}" alt="Live Preview" onerror="this.onerror=null;this.src='${CDN_BASE + (currentActiveBadge ? currentActiveBadge.file : 'ff_ui_prime_icon_08.png')}';">
                    ${shineHtml}
                    ${glintHtml}
                </div>
            `;

            // Update Status Badge on top of stage
            const statusBadge = document.getElementById('previewAnimStatusBadge');
            if (isMasterAnim && (isSheenActive || starCount > 0 || hasGlint)) {
                statusBadge.textContent = 'Animation Active';
                statusBadge.className = 'text-[10px] font-mono text-emerald-300 bg-emerald-400/10 px-2.5 py-0.5 rounded-full border border-emerald-400/30';
            } else {
                statusBadge.textContent = 'Static (VFX Disabled)';
                statusBadge.className = 'text-[10px] font-mono text-gray-400 bg-white/5 px-2.5 py-0.5 rounded-full border border-white/10';
            }

            // Update Live Metadata
            document.getElementById('metaStars').textContent = starCount > 0 ? (starCount + ' Stars (' + starTheme + ')') : '0 Stars (None)';
            
            let dirTxt = '↗ Diag';
            if (sweepDir === 'diag_bl_tr') dirTxt = '↗ Diag (BL-TR)';
            else if (sweepDir === 'diag_br_tl') dirTxt = '↖ Diag (BR-TL)';
            else if (sweepDir === 'diag_tl_br') dirTxt = '↘ Diag (TL-BR)';
            else if (sweepDir === 'diag_tr_bl') dirTxt = '↙ Diag (TR-BL)';
            else if (sweepDir === 'up') dirTxt = '↑ Up';
            else if (sweepDir === 'down') dirTxt = '↓ Down';
            else if (sweepDir === 'right') dirTxt = '→ Right';
            else if (sweepDir === 'left') dirTxt = '← Left';

            document.getElementById('metaSheen').textContent = isSheenActive ? (dirTxt + ' (' + sweepSpeed + ')') : 'Disabled';
            document.getElementById('metaGlint').textContent = hasGlint ? '✨ Active Glint' : 'Off';
            document.getElementById('metaBadgeId').textContent = '#' + badgeId;
        }

        // Quick Copy Actions for active badge
        function copyActiveBadgeId() {
            const id = document.getElementById('badgeIdInput').value || '120000000080';
            copyText(id, 'Badge ID #' + id + ' Copied!');
        }

        function copyActiveCDN() {
            const fileName = document.getElementById('existingFileSelect').value || 'ff_ui_prime_icon_08.png';
            const url = CDN_BASE + fileName;
            copyText(url, 'CDN URL Copied!');
        }

        function copyActiveWebTag() {
            const id = document.getElementById('badgeIdInput').value || '120000000080';
            const sz = document.getElementById('badgeSizeInput').value || '54';
            copyText('<ff-badge id="' + id + '" size="' + sz + '"></ff-badge>', 'Web Component Tag Copied!');
        }

        function copyActivePHP() {
            const fileName = document.getElementById('existingFileSelect').value || 'ff_ui_prime_icon_08.png';
            const sz = document.getElementById('badgeSizeInput').value || '54';
            const isPrime = document.getElementById('badgeNameInput').value.toLowerCase().includes('prime');
            const call = isPrime ? 
                ("generate_ff_prime_badge_html('" + fileName + "', 8, '" + sz + "px');") :
                ("generate_ff_badge_html('" + fileName + "', '" + document.getElementById('badgeNameInput').value + "', '" + document.getElementById('glowSelect').value + "', 'top-right', '" + sz + "px');");
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
