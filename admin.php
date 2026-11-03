<?php
/**
 * TURKY HUB - Badges Master Admin Studio
 * GitHub Repository: https://github.com/tamimhasan1280/ff-bages1280
 * Dedicated Authentication & CDN Asset Generator
 */

session_start();

$ADMIN_USER = 'tamim1280';
$ADMIN_PASS = '123@#TAmi';
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

    if ($u === $ADMIN_USER && $p === $ADMIN_PASS) {
        $_SESSION['ff_admin_logged'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $login_error = 'Invalid username or password! Please check your credentials.';
    }
}

$is_logged_in = !empty($_SESSION['ff_admin_logged']);

// Helper: Generate valid 12...80 ID
function generate_ff_id() {
    $middle = str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    return '12' . $middle . '80';
}

$success_item = null;
$error_msg = '';

// Handle Badge Creation / Upload
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_badge'])) {
    $name = trim($_POST['badge_name'] ?? '');
    $id = trim($_POST['badge_id'] ?? '');
    $category = trim($_POST['badge_category'] ?? 'Custom VIP');
    $rarity = trim($_POST['badge_rarity'] ?? 'MYTHIC');
    $description = trim($_POST['badge_description'] ?? '');
    $priority = intval($_POST['badge_priority'] ?? 500000);
    $glow_color = trim($_POST['badge_glow'] ?? 'rgba(255, 215, 0, 0.85)');
    $hex_glow = trim($_POST['badge_hex_glow'] ?? '#FFD700');
    $size = intval($_POST['badge_size'] ?? 54);
    $is_prime = !empty($_POST['is_prime']);
    $prime_lvl = intval($_POST['prime_level'] ?? 1);
    $sweep_enabled = !empty($_POST['sweep_enabled']);
    $sweep_direction = trim($_POST['sweep_direction'] ?? 'diag');
    $sweep_speed = trim($_POST['sweep_speed'] ?? '2.2s');
    $star_type = trim($_POST['star_type'] ?? 'gunpowder');
    $star_density = intval($_POST['star_density'] ?? 16);
    $star_theme = trim($_POST['star_theme'] ?? 'gold');
    $has_gem_glint = !empty($_POST['has_gem_glint']);

    // Validate ID: MUST start with 12 and end with 80
    if (!preg_match('/^12\d{6,10}80$/', $id)) {
        // Auto-fix if invalid
        $clean_middle = preg_replace('/[^0-9]/', '', $id);
        if (strlen($clean_middle) < 8) {
            $id = generate_ff_id();
        } else {
            $middle = substr($clean_middle, 2, -2);
            if (empty($middle)) $middle = str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
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

        if (empty($filename)) {
            $filename = 'vbadge.png'; // Fallback
        }

        $slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', trim($name)));
        $cat_slug = strtolower(preg_replace('/[^a-z0-9-]+/', '-', $category));

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
            'priority' => $priority,
            'default_size' => $size,
            'type' => $is_prime ? 'prime_image' : 'image',
            'prime_level' => $is_prime ? $prime_lvl : null,
            'sweep_enabled' => $sweep_enabled,
            'sweep_direction' => $sweep_direction,
            'sweep_speed' => $sweep_speed,
            'star_type' => $star_type,
            'star_density' => $star_density,
            'star_theme' => $star_theme,
            'has_gem_glint' => $has_gem_glint,
            'description' => $description ?: ($is_prime ? "Free Fire Prime Level {$prime_lvl} authentic insignia deployed via Turky Hub Monster CDN." : "Official Free Fire verified asset deployed via Turky Hub Monster CDN."),
            'fn_call' => $is_prime ? "generate_ff_prime_badge_html('{$filename}', {$prime_lvl}, '{$size}px');" : "generate_ff_badge_html('{$filename}', '{$name}', '{$glow_color}', 'top-right', '{$size}px');",
            'cdn_urls' => [
                'jsdelivr' => $CDN_BASE . $filename,
                'github_raw' => $RAW_BASE . $filename,
                'statically' => "https://cdn.statically.io/gh/{$REPO_NAME}/main/badges/" . $filename
            ],
            'direct_url' => $CDN_BASE . $filename
        ];

        // Update JSON files
        $json_file = __DIR__ . '/api/badges.json';
        $v1_json = __DIR__ . '/api/v1/badges.json';
        $data = json_decode(file_get_contents($json_file), true);
        
        // Remove duplicate if exists
        $badges_list = array_filter($data['badges'] ?? [], function($b) use ($id) {
            return $b['id'] !== $id;
        });

        array_unshift($badges_list, $new_badge);
        $data['badges'] = array_values($badges_list);
        $data['total_badges'] = count($data['badges']);

        $encoded_json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($json_file, $encoded_json);
        file_put_contents($v1_json, $encoded_json);

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
        shell_exec('git -C "' . __DIR__ . '" add . && git -C "' . __DIR__ . '" commit -m "feat(admin): added new badge ' . addslashes($name) . ' (' . $id . ')" >nul 2>&1');
        shell_exec('git -C "' . __DIR__ . '" push origin main >nul 2>&1');

        $success_item = $new_badge;
    }
}

// Handle Delete Badge
if ($is_logged_in && isset($_GET['delete'])) {
    $del_id = trim($_GET['delete']);
    $json_file = __DIR__ . '/api/badges.json';
    $v1_json = __DIR__ . '/api/v1/badges.json';
    $data = json_decode(file_get_contents($json_file), true);
    
    $data['badges'] = array_values(array_filter($data['badges'] ?? [], function($b) use ($del_id) {
        return $b['id'] !== $del_id;
    }));
    $data['total_badges'] = count($data['badges']);

    $encoded_json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents($json_file, $encoded_json);
    file_put_contents($v1_json, $encoded_json);

    // Regenerate
    if (file_exists(__DIR__ . '/generate_all.php')) {
        shell_exec('php "' . __DIR__ . '/generate_all.php" 2>&1');
    }

    header('Location: admin.php?deleted=1');
    exit;
}

// Load current badges list
$current_badges = json_decode(file_get_contents(__DIR__ . '/api/badges.json'), true)['badges'] ?? [];
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
    <title>TURKY HUB - Badges Admin Studio & CDN Generator</title>
    <link rel="icon" type="image/png" href="badges/FF_UI_Badge_KOL01.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800;900&family=Rajdhani:wght@600;700&family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cdn/prime-badge.css">
    <script src="cdn/ff-badges.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #080915;
            color: #f1f5f9;
        }
        .font-orbitron { font-family: 'Orbitron', sans-serif; }
        .font-rajdhani { font-family: 'Rajdhani', sans-serif; }
        .font-mono-code { font-family: 'Fira Code', monospace; }

        .glass-panel {
            background: #0e1224;
            border: 1px solid #1f2544;
            border-radius: 20px;
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
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Username</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3.5 top-3 text-gray-500 text-xs"></i>
                        <input type="text" name="username" required placeholder="Enter username..." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3.5 top-3 text-gray-500 text-xs"></i>
                        <input type="password" name="password" required placeholder="Enter password..." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition">
                    </div>
                </div>

                <button type="submit" class="w-full mt-2 bg-gradient-to-r from-amber-500 to-rose-600 hover:from-amber-600 hover:to-rose-700 text-white font-bold text-xs py-3 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-rose-500/20 transition">
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
        <div class="max-w-[1720px] mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between gap-4">
            
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-rose-600 flex items-center justify-center shadow-lg shadow-amber-500/20">
                    <i class="fas fa-fire-flame-curved text-white text-base"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold font-orbitron text-white text-base tracking-wider">TURKY HUB</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-400 text-black font-extrabold font-mono uppercase tracking-wider">
                            ADMIN STUDIO
                        </span>
                    </div>
                    <p class="text-[11px] text-gray-400 font-mono">CDN & 12...80 ID Generator Hub</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
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

    <main class="flex-1 max-w-[1720px] mx-auto px-4 sm:px-6 py-6 space-y-6 w-full">

        <!-- SUCCESS ALERT: SHOW INSTANT CDN LINKS FOR DEPLOYED BADGE -->
        <?php if ($success_item): ?>
            <section class="glass-panel p-6 border-emerald-500/40 relative overflow-hidden bg-gradient-to-r from-emerald-950/30 via-[#0e1224] to-[#0e1224] animate-in fade-in duration-300">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                    <div class="space-y-1.5 max-w-2xl">
                        <span class="text-[10px] font-mono px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-bold uppercase tracking-wider">
                            <i class="fas fa-check-circle mr-1"></i> Asset Created & Deployed to CDN
                        </span>
                        <h2 class="text-xl font-black text-white font-orbitron flex items-center gap-2">
                            <?= htmlspecialchars($success_item['name']) ?>
                            <span class="text-sm font-mono text-emerald-400">#<?= $success_item['id'] ?></span>
                        </h2>
                        <p class="text-xs text-gray-300">
                            ব্যাজটি সফলভাবে সেভ হয়েছে, লোকাল ফাইল ও ব্যাকআপ আপডেট হয়েছে, এবং স্বয়ংক্রিয়ভাবে গিটহাব ও jsDelivr CDN-এ লিংক জেনারেট হয়ে গেছে!
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
                    
                    <!-- 1. jsDelivr CDN Link -->
                    <div class="bg-[#090b18] p-3 rounded-xl border border-white/5 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="text-cyan-400 font-mono">jsDelivr Global CDN URL:</span>
                            <button type="button" onclick="copyText('<?= $success_item['direct_url'] ?>', 'CDN URL Copied!')" class="text-cyan-400 hover:text-cyan-300 text-[10px]">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <input type="text" readonly value="<?= $success_item['direct_url'] ?>" class="w-full bg-black/50 border border-gray-800 rounded p-1.5 text-[10px] font-mono text-cyan-300 selection:bg-cyan-500">
                    </div>

                    <!-- 2. Item ID -->
                    <div class="bg-[#090b18] p-3 rounded-xl border border-white/5 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="text-emerald-400 font-mono">Item ID (12...80):</span>
                            <button type="button" onclick="copyText('<?= $success_item['id'] ?>', 'ID Copied!')" class="text-emerald-400 hover:text-emerald-300 text-[10px]">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <input type="text" readonly value="<?= $success_item['id'] ?>" class="w-full bg-black/50 border border-gray-800 rounded p-1.5 text-[10px] font-mono text-emerald-300 selection:bg-emerald-500">
                    </div>

                    <!-- 3. Drop-in HTML Tag -->
                    <div class="bg-[#090b18] p-3 rounded-xl border border-white/5 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="text-purple-400 font-mono">Web Tag &lt;ff-badge&gt;:</span>
                            <button type="button" onclick="copyText('<ff-badge id=\'<?= $success_item['id'] ?>\' size=\'<?= $success_item['default_size'] ?>\'></ff-badge>', 'Web Tag Copied!')" class="text-purple-400 hover:text-purple-300 text-[10px]">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <input type="text" readonly value="&lt;ff-badge id=&quot;<?= $success_item['id'] ?>&quot; size=&quot;<?= $success_item['default_size'] ?>&quot;&gt;&lt;/ff-badge&gt;" class="w-full bg-black/50 border border-gray-800 rounded p-1.5 text-[10px] font-mono text-purple-300">
                    </div>

                    <!-- 4. PHP Function Call -->
                    <div class="bg-[#090b18] p-3 rounded-xl border border-white/5 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold">
                            <span class="text-amber-400 font-mono">PHP Logic Call:</span>
                            <button type="button" onclick="copyText(<?= htmlspecialchars(json_encode($success_item['fn_call'])) ?>, 'PHP Call Copied!')" class="text-amber-400 hover:text-amber-300 text-[10px]">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <input type="text" readonly value="<?= htmlspecialchars($success_item['fn_call']) ?>" class="w-full bg-black/50 border border-gray-800 rounded p-1.5 text-[10px] font-mono text-amber-300">
                    </div>

                </div>
            </section>
        <?php endif; ?>

        <!-- SECTION 1: ADD & DEPLOY NEW BADGE / PRIME ICON FORM -->
        <section class="glass-panel p-6 sm:p-7">
            <div class="flex items-center justify-between border-b border-[#1f2544] pb-4 mb-6">
                <div class="space-y-1">
                    <h2 class="text-lg font-black font-orbitron text-white flex items-center gap-2">
                        <i class="fas fa-cloud-arrow-up text-amber-400"></i>
                        <span>নতুন ব্যাজ বা প্রাইম আইকন আপলোড ও সিডিএন জেনারেটর</span>
                    </h2>
                    <p class="text-xs text-gray-400">
                        এখানে স্বচ্ছ ব্যাকগ্রাউন্ডের (Transparent PNG) ব্যাজ আপলোড করলে স্বয়ংক্রিয়ভাবে ১২ দিয়ে শুরু ও ৮০ দিয়ে শেষ হওয়া আইডি এবং সিডিএন লিংক তৈরি হবে।
                    </p>
                </div>
                <button type="button" onclick="randomizeId()" class="px-3 py-1.5 bg-amber-400/10 hover:bg-amber-400/20 text-yellow-300 border border-yellow-400/30 rounded-xl text-xs font-mono font-bold flex items-center gap-1.5 transition">
                    <i class="fas fa-dice text-xs"></i> <span>Generate New 12...80 ID</span>
                </button>
            </div>

            <form method="POST" action="admin.php" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="save_badge" value="1">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    
                    <!-- 1. Badge Name -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Badge Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="badge_name" id="badgeNameInput" required placeholder="e.g. Free Fire Prime Level 8" value="Free Fire Prime Level 8" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition">
                    </div>

                    <!-- 2. Auto 12...80 ID -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Item ID (Must start with 12 & end with 80) <span class="text-emerald-400">*</span></label>
                            <span class="text-[10px] text-emerald-400 font-mono font-bold">12 Digits Secure</span>
                        </div>
                        <div class="relative flex items-center">
                            <input type="text" name="badge_id" id="badgeIdInput" value="<?= $suggested_id ?>" pattern="^12[0-9]{6,10}80$" required class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs font-mono font-bold text-emerald-400 tracking-wider focus:outline-none focus:border-emerald-400 transition">
                            <button type="button" onclick="randomizeId()" class="absolute right-2 px-2.5 py-1 bg-white/5 hover:bg-white/10 text-gray-300 text-[11px] rounded-lg font-mono transition" title="Randomize">
                                <i class="fas fa-rotate"></i>
                            </button>
                        </div>
                    </div>

                    <!-- 3. Category -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Category</label>
                        <select name="badge_category" id="badgeCatSelect" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition cursor-pointer">
                            <option value="Prime System" selected>Prime System (Free Fire Prime)</option>
                            <option value="Official Partner (KOL)">Official Partner (KOL)</option>
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
                            <option value="LEGENDARY">LEGENDARY (Crimson / Flame)</option>
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
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Aura Glow Color</label>
                        <select name="badge_glow" id="glowSelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition cursor-pointer">
                            <option value="rgba(255, 215, 0, 0.85)" selected>Gold Aura (rgba(255, 215, 0, 0.85))</option>
                            <option value="rgba(168, 85, 247, 0.85)">Purple Cosmic (rgba(168, 85, 247, 0.85))</option>
                            <option value="rgba(6, 182, 212, 0.85)">Cyan Crystal (rgba(6, 182, 212, 0.85))</option>
                            <option value="rgba(239, 68, 68, 0.85)">Crimson Authority (rgba(239, 68, 68, 0.85))</option>
                            <option value="rgba(249, 115, 22, 0.85)">Amber Flame (rgba(249, 115, 22, 0.85))</option>
                            <option value="rgba(16, 185, 129, 0.85)">Emerald Lightning (rgba(16, 185, 129, 0.85))</option>
                        </select>
                    </div>

                </div>

                <!-- VFX & PRIME STUDIO PANEL -->
                <div class="p-5 rounded-2xl bg-gradient-to-br from-[#121634] to-[#0c0f24] border border-amber-500/30 space-y-4 shadow-xl">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-white/10 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center font-bold text-sm shadow">
                                <i class="fas fa-wand-magic-sparkles"></i>
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-white uppercase tracking-wider font-orbitron">VFX Studio & FX Customizer (ইফেক্ট স্টুডিও)</h4>
                                <p class="text-[10px] text-gray-400">ধাতব সোয়াইপ এঙ্গেল, বারুদ স্ফুলিঙ্গ, রত্ন ফ্ল্যাশ ও তারার এনিমেশন কাস্টমাইজ করুন</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-amber-300 bg-amber-400/10 border border-amber-400/40 px-3 py-1.5 rounded-xl hover:bg-amber-400/20 transition">
                            <input type="checkbox" name="is_prime" id="isPrimeCheck" value="1" checked onchange="togglePrimeStudio(this.checked)" class="rounded bg-gray-800 border-gray-700 text-amber-500 focus:ring-0">
                            <span>Enable Prime VFX Engine</span>
                        </label>
                    </div>

                    <div id="vfxStudioControls" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 transition-all">
                        <!-- Prime Level Preset -->
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-crown text-amber-400"></i> <span>Prime Level Preset</span>
                            </label>
                            <select name="prime_level" id="primeLevelSelect" onchange="applyPrimePreset(this.value)" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-400 cursor-pointer">
                                <option value="8" selected>Level 8 (Supreme God Tier - 16 Stars & Glint)</option>
                                <option value="7">Level 7 (Grandmaster Mythic - 12 Stars & Glint)</option>
                                <option value="6">Level 6 (Amber Flame Wings - 8 Stars)</option>
                                <option value="5">Level 5 (Radiant Gold Contour - 6 Stars)</option>
                                <option value="4">Level 4 (Cyan Crystal - 4 Stars)</option>
                                <option value="3">Level 3 (Gold Vanguard Static)</option>
                                <option value="2">Level 2 (Silver Scout Static)</option>
                                <option value="1">Level 1 (Bronze Recruit Static)</option>
                            </select>
                        </div>

                        <!-- Metallic Sheen Sweep Direction -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fas fa-bolt text-yellow-400"></i> <span>Sheen Direction</span>
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer text-[10px] text-gray-400">
                                    <input type="checkbox" name="sweep_enabled" id="sweepEnabledCheck" value="1" checked onchange="updateLivePreviewStudio()" class="rounded bg-gray-800 border-gray-700 text-amber-500 text-[10px]">
                                    <span>Active</span>
                                </label>
                            </div>
                            <select name="sweep_direction" id="sweepDirSelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-400 cursor-pointer">
                                <option value="diag" selected>↗ Diagonal (Bottom-Left to Top-Right)</option>
                                <option value="diag_rev">↖ Diagonal Reverse (Bottom-Right to Top-Left)</option>
                                <option value="up">↑ Vertical (Bottom to Top)</option>
                                <option value="down">↓ Vertical (Top to Bottom)</option>
                                <option value="right">→ Horizontal (Left to Right)</option>
                                <option value="left">← Horizontal (Right to Left)</option>
                            </select>
                        </div>

                        <!-- Sweep Speed -->
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-gauge-high text-cyan-400"></i> <span>Sheen Speed</span>
                            </label>
                            <select name="sweep_speed" id="sweepSpeedSelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-amber-400 cursor-pointer">
                                <option value="1.8s">Fast (1.8s - দ্রুত ঝলকানি)</option>
                                <option value="2.2s" selected>Prime 8 Special (2.2s)</option>
                                <option value="2.6s">Standard (2.6s - মানানসই)</option>
                                <option value="3.4s">Smooth (3.4s - ধীর ও মসৃণ)</option>
                            </select>
                        </div>

                        <!-- Gunpowder Stars Count & Theme -->
                        <div class="space-y-1.5">
                            <label class="text-[11px] font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fas fa-star text-amber-400"></i> <span>Gunpowder Stars (বারুদ স্টার)</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <select name="star_density" id="starDensitySelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-2.5 py-2 text-xs text-white focus:outline-none focus:border-amber-400 cursor-pointer">
                                    <option value="16" selected>16 Stars (Supreme)</option>
                                    <option value="12">12 Stars (High)</option>
                                    <option value="8">8 Stars (Medium)</option>
                                    <option value="6">6 Stars (Compact)</option>
                                    <option value="4">4 Stars (Minimal)</option>
                                    <option value="0">0 Stars (None)</option>
                                </select>

                                <select name="star_theme" id="starThemeSelect" onchange="updateLivePreviewStudio()" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-2.5 py-2 text-xs text-white focus:outline-none focus:border-amber-400 cursor-pointer">
                                    <option value="gold" selected>Gold Star</option>
                                    <option value="cyan">Cyan Ice</option>
                                    <option value="white">Diamond White</option>
                                    <option value="ruby">Ruby Red</option>
                                    <option value="purple">Cosmic Purple</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Center Gem Glint & Transparency Guarantee -->
                    <div id="vfxStudioToggles" class="flex flex-wrap items-center justify-between gap-4 pt-2 border-t border-white/10 transition-all">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-300">
                            <input type="checkbox" name="has_gem_glint" id="hasGemGlintCheck" value="1" checked onchange="updateLivePreviewStudio()" class="rounded bg-gray-800 border-gray-700 text-amber-500">
                            <span>Center Gem Glint Flare (মাঝের রত্ন থেকে তারার মতো আলো ফুটবে)</span>
                        </label>
                        <span class="text-[11px] text-emerald-400 font-mono flex items-center gap-1">
                            <i class="fas fa-circle-check text-xs"></i> 100% Transparent Background (Zero Halos / No Yellow Side Clouds)
                        </span>
                    </div>
                </div>

                <!-- Asset Selection & Live Studio Preview Zone -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 pt-1">
                    
                    <!-- Left: Asset Picker (Select Existing OR Upload New) -->
                    <div class="lg:col-span-2 space-y-3">
                        <label class="text-xs font-bold text-gray-300 uppercase tracking-wider block">Badge Icon Asset (Select Existing or Upload PNG) <span class="text-rose-500">*</span></label>
                        
                        <!-- Pick Existing Asset Dropdown -->
                        <div class="space-y-1">
                            <span class="text-[11px] text-gray-400 font-semibold">1. Choose from Existing Free Fire Assets:</span>
                            <select name="existing_file" id="existingFileSelect" onchange="selectExistingAsset(this.value)" class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-400 transition cursor-pointer font-mono">
                                <option value="">-- Or upload a custom file below --</option>
                                <?php foreach ($existing_badge_files as $ef): ?>
                                    <option value="<?= $ef ?>" <?= ($ef === 'ff_ui_prime_icon_08.png') ? 'selected' : '' ?>><?= $ef ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- OR Upload New File -->
                        <div class="space-y-1">
                            <span class="text-[11px] text-gray-400 font-semibold">2. Or Upload New Transparent PNG / SVG:</span>
                            <div class="border-2 border-dashed border-[#23294c] hover:border-amber-400/50 rounded-2xl p-4 text-center cursor-pointer bg-[#080a18] transition relative" onclick="document.getElementById('fileInput').click()">
                                <input type="file" name="badge_file" id="fileInput" accept="image/png,image/svg+xml,image/webp" class="hidden" onchange="previewSelectedFile(event)">
                                <div class="space-y-1 pointer-events-none">
                                    <div class="w-9 h-9 mx-auto rounded-full bg-white/5 flex items-center justify-center text-amber-400 text-base">
                                        <i class="fas fa-file-arrow-up"></i>
                                    </div>
                                    <div class="text-xs text-gray-300 font-semibold" id="uploadPromptText">Click to select new Transparent PNG file</div>
                                    <p class="text-[10px] text-gray-500 font-mono">100% transparent PNG with no background is recommended</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Live Studio Preview Box (100% Transparent, No Halos) -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Live VFX Stage Preview</label>
                            <span class="text-[10px] text-amber-400 font-mono font-bold animate-pulse">● LIVE FX ACTIVE</span>
                        </div>
                        <div class="h-52 rounded-2xl bg-[#080a18] border border-white/10 flex items-center justify-center p-3 relative overflow-hidden shadow-inner">
                            <div id="liveBadgeStage" class="relative z-10 flex items-center justify-center w-full h-full">
                                <!-- Populated dynamically by updateLivePreviewStudio() -->
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-gray-300 uppercase tracking-wider">Description</label>
                    <textarea name="badge_description" rows="2" placeholder="Exclusive Free Fire partner or prime level verification asset..." class="w-full bg-[#080a18] border border-[#23294c] rounded-xl px-4 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="bg-gradient-to-r from-amber-500 via-rose-500 to-pink-600 hover:from-amber-600 hover:to-pink-700 text-white font-extrabold text-xs py-3 px-8 rounded-xl flex items-center gap-2 shadow-lg shadow-rose-500/20 transition">
                        <i class="fas fa-rocket"></i>
                        <span>Save & Generate Global CDN Asset</span>
                    </button>
                </div>

            </form>
        </section>

        <!-- SECTION 2: BROWSE & MANAGE ALL BADGES (TOTAL: <?= count($current_badges) ?>) -->
        <section class="glass-panel p-6 sm:p-7 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1f2544] pb-4">
                <div class="space-y-0.5">
                    <h3 class="text-base font-black font-orbitron text-white flex items-center gap-2">
                        <i class="fas fa-layer-group text-cyan-400"></i>
                        <span>Existing Badges & Prime Assets (<?= count($current_badges) ?> Total)</span>
                    </h3>
                    <p class="text-xs text-gray-400">প্রতিটি ব্যাজের সরাসরি CDN লিংক, PHP কল এবং ID কপি করার জন্য বাটন দেওয়া আছে।</p>
                </div>

                <!-- Filter & Search -->
                <div class="flex items-center gap-3">
                    <input type="text" id="adminSearchInput" oninput="filterAdminBadges(this.value)" placeholder="Search name or 12...80 ID..." class="bg-[#080a18] border border-[#23294c] rounded-xl px-3.5 py-1.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-amber-400 transition w-56">
                </div>
            </div>

            <!-- Table of Badges -->
            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-[#1f2544] text-[11px] font-mono uppercase text-gray-400">
                            <th class="py-3 px-3">Preview</th>
                            <th class="py-3 px-3">Item ID (12...80)</th>
                            <th class="py-3 px-3">Badge Name</th>
                            <th class="py-3 px-3">Category</th>
                            <th class="py-3 px-3">Rarity</th>
                            <th class="py-3 px-3">CDN Link & Snippets</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminBadgesTbody" class="divide-y divide-[#151934]">
                        <?php foreach ($current_badges as $b): ?>
                            <?php
                                $img_cdn = $CDN_BASE . ($b['file'] ?? '');
                                $local_file = 'badges/' . ($b['file'] ?? '');
                                $isPrime = ($b['type'] === 'prime_image' || !empty($b['prime_level']));
                            ?>
                            <tr class="hover:bg-white/[0.02] transition admin-badge-row" data-name="<?= strtolower($b['name']) ?>" data-id="<?= strtolower($b['id']) ?>">
                                
                                <!-- Preview (No Background, 100% Transparent Live Badge) -->
                                <td class="py-3 px-3">
                                    <div class="w-12 h-12 rounded-xl bg-[#090b18] border border-white/5 flex items-center justify-center p-1 relative overflow-hidden">
                                        <ff-badge id="<?= $b['id'] ?>" size="36"></ff-badge>
                                    </div>
                                </td>

                                <!-- 12...80 ID -->
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-emerald-400">#<?= $b['id'] ?></span>
                                        <button type="button" onclick="copyText('<?= $b['id'] ?>', 'ID Copied!')" class="text-gray-500 hover:text-white" title="Copy ID">
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

                                <!-- CDN Link & Snippets -->
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" onclick="copyText('<?= $img_cdn ?>', 'CDN URL Copied!')" class="px-2 py-1 rounded bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-[10px] font-mono flex items-center gap-1 transition">
                                            <i class="fas fa-link text-[9px]"></i> <span>CDN</span>
                                        </button>

                                        <button type="button" onclick="copyText(<?= htmlspecialchars(json_encode($b['fn_call'] ?? '')) ?>, 'PHP Call Copied!')" class="px-2 py-1 rounded bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-mono flex items-center gap-1 transition">
                                            <i class="fab fa-php text-[10px]"></i> <span>PHP</span>
                                        </button>

                                        <button type="button" onclick="copyText('<ff-badge id=\'<?= $b['id'] ?>\' size=\'<?= $b['default_size'] ?>\'></ff-badge>', 'Web Tag Copied!')" class="px-2 py-1 rounded bg-purple-500/10 hover:bg-purple-500/20 text-purple-300 border border-purple-500/30 text-[10px] font-mono flex items-center gap-1 transition">
                                            <i class="fas fa-code text-[9px]"></i> <span>Tag</span>
                                        </button>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
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
    <div id="adminToast" class="fixed bottom-6 right-6 bg-amber-400 text-black font-extrabold px-4 py-2.5 rounded-2xl shadow-2xl flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 z-50 pointer-events-none">
        <i class="fas fa-check-circle text-base"></i>
        <span id="adminToastMsg">Copied!</span>
    </div>

    <script>
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
            t.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => {
                t.classList.add('translate-y-20', 'opacity-0');
            }, 2000);
        }

        // Randomize 12...80 ID
        function randomizeId() {
            const middle = Math.floor(10000000 + Math.random() * 90000000);
            const newId = '12' + middle + '80';
            document.getElementById('badgeIdInput').value = newId;
            showToast('New 12...80 ID generated: #' + newId);
        }

        // Active preview image source
        let currentPreviewImg = 'badges/ff_ui_prime_icon_08.png';

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

            document.getElementById('uploadPromptText').textContent = 'Selected: ' + file.name;
            document.getElementById('existingFileSelect').value = '';
            const reader = new FileReader();
            reader.onload = function(e) {
                currentPreviewImg = e.target.result;
                updateLivePreviewStudio();
            };
            reader.readAsDataURL(file);
        }

        // Toggle Prime Studio Enable/Disable
        function togglePrimeStudio(checked) {
            const ctrls = document.getElementById('vfxStudioControls');
            const toggles = document.getElementById('vfxStudioToggles');
            if (checked) {
                ctrls.classList.remove('opacity-50', 'pointer-events-none');
                toggles.classList.remove('opacity-50', 'pointer-events-none');
                document.getElementById('badgeCatSelect').value = 'Prime System';
            } else {
                ctrls.classList.add('opacity-50', 'pointer-events-none');
                toggles.classList.add('opacity-50', 'pointer-events-none');
            }
            updateLivePreviewStudio();
        }

        // Apply recommended preset when level changes
        function applyPrimePreset(lvl) {
            lvl = parseInt(lvl) || 8;
            const nameInput = document.getElementById('badgeNameInput');
            if (!nameInput.value || nameInput.value.startsWith('Free Fire Prime Level')) {
                nameInput.value = 'Free Fire Prime Level ' + lvl;
            }

            // Auto select matching icon if in dropdown
            const efSelect = document.getElementById('existingFileSelect');
            const targetIcon = 'ff_ui_prime_icon_0' + lvl + '.png';
            for (let i = 0; i < efSelect.options.length; i++) {
                if (efSelect.options[i].value === targetIcon) {
                    efSelect.selectedIndex = i;
                    currentPreviewImg = 'badges/' + targetIcon;
                    break;
                }
            }

            if (lvl === 8) {
                document.getElementById('sweepEnabledCheck').checked = true;
                document.getElementById('sweepDirSelect').value = 'diag';
                document.getElementById('sweepSpeedSelect').value = '2.2s';
                document.getElementById('starDensitySelect').value = '16';
                document.getElementById('starThemeSelect').value = 'gold';
                document.getElementById('hasGemGlintCheck').checked = true;
                document.getElementById('badgeRaritySelect').value = 'MYTHIC';
            } else if (lvl === 7) {
                document.getElementById('sweepEnabledCheck').checked = true;
                document.getElementById('sweepDirSelect').value = 'diag';
                document.getElementById('sweepSpeedSelect').value = '2.4s';
                document.getElementById('starDensitySelect').value = '12';
                document.getElementById('starThemeSelect').value = 'gold';
                document.getElementById('hasGemGlintCheck').checked = true;
                document.getElementById('badgeRaritySelect').value = 'MYTHIC';
            } else if (lvl === 6) {
                document.getElementById('sweepEnabledCheck').checked = true;
                document.getElementById('sweepDirSelect').value = 'diag';
                document.getElementById('sweepSpeedSelect').value = '2.4s';
                document.getElementById('starDensitySelect').value = '8';
                document.getElementById('starThemeSelect').value = 'ruby';
                document.getElementById('hasGemGlintCheck').checked = false;
                document.getElementById('badgeRaritySelect').value = 'LEGENDARY';
            } else if (lvl === 5) {
                document.getElementById('sweepEnabledCheck').checked = true;
                document.getElementById('sweepDirSelect').value = 'diag';
                document.getElementById('sweepSpeedSelect').value = '2.6s';
                document.getElementById('starDensitySelect').value = '6';
                document.getElementById('starThemeSelect').value = 'gold';
                document.getElementById('hasGemGlintCheck').checked = false;
                document.getElementById('badgeRaritySelect').value = 'LEGENDARY';
            } else if (lvl === 4) {
                document.getElementById('sweepEnabledCheck').checked = true;
                document.getElementById('sweepDirSelect').value = 'diag';
                document.getElementById('sweepSpeedSelect').value = '2.6s';
                document.getElementById('starDensitySelect').value = '4';
                document.getElementById('starThemeSelect').value = 'cyan';
                document.getElementById('hasGemGlintCheck').checked = false;
                document.getElementById('badgeRaritySelect').value = 'EPIC';
            } else {
                document.getElementById('sweepEnabledCheck').checked = false;
                document.getElementById('starDensitySelect').value = '0';
                document.getElementById('hasGemGlintCheck').checked = false;
                document.getElementById('badgeRaritySelect').value = 'RARE';
            }

            updateLivePreviewStudio();
        }

        // Master Live Preview Studio Updater
        function updateLivePreviewStudio() {
            const stage = document.getElementById('liveBadgeStage');
            if (!stage) return;

            const isPrime = document.getElementById('isPrimeCheck').checked;
            const lvl = parseInt(document.getElementById('primeLevelSelect').value) || 8;
            const sweepEnabled = document.getElementById('sweepEnabledCheck').checked;
            const sweepDir = document.getElementById('sweepDirSelect').value;
            const sweepSpeed = document.getElementById('sweepSpeedSelect').value;
            const density = parseInt(document.getElementById('starDensitySelect').value) || 0;
            const starTheme = document.getElementById('starThemeSelect').value;
            const hasGlint = document.getElementById('hasGemGlintCheck').checked;
            const size = parseInt(document.getElementById('badgeSizeInput').value) || 54;
            const glowVal = document.getElementById('glowSelect').value;

            const sweepClass = (sweepDir !== 'diag') ? ('sweep-dir-' + sweepDir) : '';

            if (isPrime) {
                // Gunpowder Star Layer (up to 16 positions)
                const allPositions = ['star-pos-1', 'star-pos-2', 'star-pos-3', 'star-pos-4', 'star-pos-5', 'star-pos-6', 'star-pos-7', 'star-pos-8', 'star-pos-9', 'star-pos-10', 'star-pos-11', 'star-pos-12', 'star-pos-13', 'star-pos-14', 'star-pos-15', 'star-pos-16'];
                const starList = allPositions.slice(0, Math.min(density, 16));
                const starColor = (starTheme === 'cyan') ? '#00f0ff' : (starTheme === 'ruby' ? '#ef4444' : (starTheme === 'purple' ? '#c084fc' : '#FFFCE0'));
                const themeClass = (starTheme !== 'gold') ? ('star-theme-' + starTheme) : '';

                const starsHtml = (density > 0) ? `
                    <div class="ff-prime-stars-layer ${themeClass}">
                        ${starList.map(pos => `<svg class="ff-pinpoint-star ${pos}" viewBox="0 0 24 24"><path d="M12 0 L13.5 10.5 L24 12 L13.5 13.5 L12 24 L10.5 13.5 L0 12 L10.5 10.5 Z" fill="${starColor}"/><circle cx="12" cy="12" r="2" fill="#FFFFFF"/></svg>`).join('')}
                    </div>` : '';

                // Metallic Sheen Mask Sweep
                const shineHtml = sweepEnabled ? `
                    <span class="ff-badge-shine ${sweepClass}" style="position:absolute;inset:0;pointer-events:none;overflow:hidden;z-index:7;-webkit-mask-image:url('${currentPreviewImg}');mask-image:url('${currentPreviewImg}');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                        <span class="ff-badge-shine-beam" style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;animation-duration:${sweepSpeed};mix-blend-mode:screen;display:block;"></span>
                    </span>` : '';

                stage.innerHTML = `
                    <div class="ff-prime-badge-wrap ff-prime-lvl-${lvl}" style="width:72px;height:72px;position:relative;background:transparent !important;">
                        ${starsHtml}
                        <img src="${currentPreviewImg}" class="ff-badge-img" alt="Live Preview">
                        ${shineHtml}
                        ${hasGlint ? '<div class="ff-gem-glint"></div>' : ''}
                    </div>
                `;
            } else {
                stage.innerHTML = `
                    <span class="ff-shape-badge" style="width:54px;height:54px;filter:drop-shadow(0 0 6px ${glowVal});display:inline-block;position:relative;">
                        <img src="${currentPreviewImg}" alt="Preview" style="width:100%;height:100%;object-fit:contain;display:block;">
                        <span style="position:absolute;inset:0;pointer-events:none;overflow:hidden;-webkit-mask-image:url('${currentPreviewImg}');mask-image:url('${currentPreviewImg}');-webkit-mask-size:contain;mask-size:contain;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;">
                            <span style="position:absolute;top:-100%;left:-100%;width:300%;height:300%;background:linear-gradient(45deg,transparent 35%,rgba(255,255,255,0.2) 44%,rgba(255,255,255,0.95) 50%,rgba(255,255,255,0.2) 56%,transparent 65%);animation:ff_shape_sweep 2.6s infinite linear;mix-blend-mode:screen;display:block;"></span>
                        </span>
                    </span>
                `;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateLivePreviewStudio();
        });

        // Filter Table
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
    </script>

<?php endif; ?>

</body>
</html>
