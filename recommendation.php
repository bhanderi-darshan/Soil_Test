<?php
session_start();
include "db.php";
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$soil_name = $_SESSION['soil_type'] ?? 'Unknown Soil';
$username  = $_SESSION['username'] ?? 'Farmer';
$npk_result = mysqli_query($conn,"SELECT * FROM predicted_npk ORDER BY id DESC LIMIT 1");
$npk = mysqli_fetch_assoc($npk_result) ?: ['nitrogen'=>'0','phosphorus'=>'0','potassium'=>'0'];
$N=$npk['nitrogen']; $P=$npk['phosphorus']; $K=$npk['potassium'];

// Fetch User Location and Preferences
$pref_result = mysqli_query($conn, "SELECT * FROM farmer_preferences WHERE user_id=".$_SESSION['user_id']);
$prefs = mysqli_fetch_assoc($pref_result) ?: ['prediction_mode'=>'all', 'preferred_crops'=>'', 'district'=>'Rajkot', 'taluka'=>'Gondal'];
$prediction_mode = $prefs['prediction_mode'];
$preferred_crops = explode(',', $prefs['preferred_crops']);
$district = $prefs['district'];
$taluka = $prefs['taluka'];

// Determine Current Season
$month = (int)date('m');
$day = (int)date('d');
$season = 'Kharif'; 
if (($month == 3 && $day >= 16) || ($month > 3 && $month < 6) || ($month == 6 && $day <= 14)) $season = 'Summer';
elseif (($month == 6 && $day >= 15) || ($month > 6 && $month < 10) || ($month == 10 && $day <= 15)) $season = 'Kharif';
else $season = 'Rabi';

// Fetch valid crops for this location and season
$sql = "SELECT crop_name FROM location_crop_mapping WHERE district_name='$district' AND taluka_name='$taluka' AND season='$season'";
$mapping_res = mysqli_query($conn, $sql);
$location_supported_crops = [];
while($m = mysqli_fetch_assoc($mapping_res)) {
    $location_supported_crops[] = $m['crop_name'];
}

$crops_data = [
    'Rice'      => ['n'=>[80, 120], 'p'=>[40, 60], 'k'=>[40, 60], 'soil'=>'Alluvial Soil', 'reason'=>'High yield in water-rich alluvial soil with balanced NPK.'],
    'Wheat'     => ['n'=>[60, 90],  'p'=>[40, 50], 'k'=>[30, 50], 'soil'=>'Black Soil',    'reason'=>'Thrives in black soil with moderate temperature and balanced NPK.'],
    'Cotton'    => ['n'=>[70, 100], 'p'=>[30, 50], 'k'=>[60, 80], 'soil'=>'Black Soil',    'reason'=>'Black soil provides excellent retention for cotton nutrient needs.'],
    'Groundnut' => ['n'=>[20, 40],  'p'=>[40, 60], 'k'=>[30, 50], 'soil'=>'Red Soil',      'reason'=>'Perfect for red soil; low nitrogen need due to nitrogen fixation.'],
    'Bajra'     => ['n'=>[40, 60],  'p'=>[20, 40], 'k'=>[20, 40], 'soil'=>'Sandy Soil',    'reason'=>'Hardy crop for sandy soil with low nutrient requirements.'],
    'Maize'     => ['n'=>[90, 120], 'p'=>[40, 60], 'k'=>[40, 60], 'soil'=>'Alluvial Soil', 'reason'=>'Favorable in deep alluvial soil with high nitrogen availability.'],
    'Castor'    => ['n'=>[60, 80],  'p'=>[50, 70], 'k'=>[50, 70], 'soil'=>'Black Soil',    'reason'=>'Strategic choice for black soil with high P/K requirements.'],
    'Cumin'     => ['n'=>[30, 50],  'p'=>[30, 50], 'k'=>[20, 40], 'soil'=>'Sandy Soil',    'reason'=>'Grows well in Gujarat conditions with minimal mineral needs.'],
    'Onion'     => ['n'=>[100, 120],'p'=>[60, 80], 'k'=>[80, 100],'soil'=>'Alluvial Soil', 'reason'=>'High nutrient feeder, best suited for fertile alluvial tracts.'],
    'Potato'    => ['n'=>[120, 150],'p'=>[80, 100],'k'=>[100, 150],'soil'=>'Black Soil',   'reason'=>'Potatoes require rich nutrients and crumbly black soil structure.'],
    'Gram'      => ['n'=>[20, 30],  'p'=>[40, 60], 'k'=>[20, 30], 'soil'=>'Black Soil',    'reason'=>'Pulse crop ideal for Rabi season with low moisture needs.'],
    'Tobacco'   => ['n'=>[100, 120],'p'=>[80, 100],'k'=>[120, 150],'soil'=>'Alluvial Soil', 'reason'=>'Tobacco thrives in well-irrigated alluvial soil with high potash.'],
    'Mango'     => ['n'=>[70, 90],  'p'=>[30, 50], 'k'=>[50, 80], 'soil'=>'Red Soil',      'reason'=>'Perennial mango trees prefer well-drained red or mixed soils.'],
    'Banana'    => ['n'=>[180, 220],'p'=>[50, 70], 'k'=>[250, 300],'soil'=>'Alluvial Soil', 'reason'=>'Banana is a heavy feeder requiring high nitrogen and potassium.'],
    'Jowar'     => ['n'=>[60, 80],  'p'=>[30, 50], 'k'=>[30, 50], 'soil'=>'Sandy Soil',    'reason'=>'Hardy millet variety suitable for dry sandy soils.'],
    'Chilli'    => ['n'=>[90, 120], 'p'=>[50, 70], 'k'=>[50, 80], 'soil'=>'Black Soil',    'reason'=>'Chilli crops benefit from nutrient-rich black cotton soil.'],
    'Sugarcane' => ['n'=>[200, 250],'p'=>[70, 90], 'k'=>[100, 140],'soil'=>'Alluvial Soil', 'reason'=>'Sugarcane requires intensive nitrogen and steady irrigation.'],
    'Soybean'   => ['n'=>[20, 45],  'p'=>[60, 80], 'k'=>[40, 60], 'soil'=>'Black Soil',    'reason'=>'Soybean prefers well-drained loamy soil with high phosphorus.'],
    'Mustard'   => ['n'=>[60, 90],  'p'=>[30, 50], 'k'=>[30, 50], 'soil'=>'Sandy Soil',    'reason'=>'Mustard thrives in cool Rabi weather with light sandy soil.'],
    'Isabgol'   => ['n'=>[20, 30],  'p'=>[15, 25], 'k'=>[15, 20], 'soil'=>'Sandy Soil',    'reason'=>'Highly drought-resistant crop suitable for desert margins.'],
    'Papaya'    => ['n'=>[50, 70],  'p'=>[50, 70], 'k'=>[100, 150],'soil'=>'Alluvial Soil', 'reason'=>'Papaya needs fertile soil and high potassium for fruit quality.'],
    'Chikoo'    => ['n'=>[60, 80],  'p'=>[20, 30], 'k'=>[50, 70], 'soil'=>'Red Soil',      'reason'=>'Sapota (Chikoo) is hardy and performs well in varied soil types.'],
    'Pomegranate' => ['n'=>[60, 90], 'p'=>[30, 50], 'k'=>[60, 80], 'soil'=>'Red Soil',      'reason'=>'Ideal for semi-arid regions with well-drained loamy soil.'],
    'Tomato'    => ['n'=>[100, 150],'p'=>[60, 100],'k'=>[80, 120],'soil'=>'Alluvial Soil', 'reason'=>'Heavy feeder vegetable requiring frequent fertilization.'],
    'Garlic'    => ['n'=>[80, 100], 'p'=>[50, 70], 'k'=>[50, 80], 'soil'=>'Black Soil',    'reason'=>'Garlic requires rich soil and consistent moisture during winter.'],
    'Sesamum'   => ['n'=>[40, 60],  'p'=>[20, 40], 'k'=>[20, 30], 'soil'=>'Sandy Soil',    'reason'=>'Sesamum is an oilseed crop that performs well in sandy loam.'],
    'Guava'     => ['n'=>[60, 80],  'p'=>[30, 50], 'k'=>[50, 70], 'soil'=>'Alluvial Soil', 'reason'=>'Guava is extremely adaptable and thrives in fertile alluvial plains.'],
    'Coriander' => ['n'=>[40, 60],  'p'=>[30, 50], 'k'=>[10, 20], 'soil'=>'Black Soil',    'reason'=>'Grows best in heavy black soils with cool winter climate.'],
    'Coconut'   => ['n'=>[50, 70],  'p'=>[30, 50], 'k'=>[120, 180],'soil'=>'Sandy Soil',    'reason'=>'Coconut prefers coastal sandy soil with high potassium and water.'],
    'Okra'      => ['n'=>[80, 100], 'p'=>[40, 60], 'k'=>[40, 60], 'soil'=>'Alluvial Soil', 'reason'=>'Bhendi/Okra yields well in light-textured fertile soils.'],
];

$best_crop = 'Wheat';
$max_score = -999;
$crop_reason = 'Default choice based on current location and season.';

foreach ($crops_data as $name => $c) {
    // 1. Filter by Location & Season Availability
    if (!in_array($name, $location_supported_crops)) continue;

    // 2. If 'regular' mode, further filter by Farmer's Preferences
    if ($prediction_mode === 'regular' && !in_array($name, $preferred_crops)) continue;
    
    $score = 0;
    if ($soil_name === $c['soil']) $score += 50;
    
    $target_n = ($c['n'][0] + $c['n'][1]) / 2;
    $target_p = ($c['p'][0] + $c['p'][1]) / 2;
    $target_k = ($c['k'][0] + $c['k'][1]) / 2;
    
    $score -= abs($target_n - $N);
    $score -= abs($target_p - $P);
    $score -= abs($target_k - $K);
    
    if ($score > $max_score) {
        $max_score = $score;
        $best_crop = $name;
        $crop_reason = $c['reason'];
    }
}
$crop = $best_crop;

$fertilizer=[];
if($soil_name=='Black Soil') { $fertilizer[]='Apply MOP (Muriate of Potash) for potassium'; $fertilizer[]='Add Urea (46-0-0) for nitrogen boost'; $fertilizer[]='Use compost to improve organic matter'; }
elseif($soil_name=='Red Soil') { $fertilizer[]='Apply NPK 12-32-16 compound fertilizer'; $fertilizer[]='Add gypsum to correct pH imbalance'; $fertilizer[]='Use FYM (Farm Yard Manure) regularly'; }
elseif($soil_name=='Sandy Soil') { $fertilizer[]='Use slow-release fertilizers to prevent leaching'; $fertilizer[]='Apply NPK 10-26-26 for balanced nutrition'; $fertilizer[]='Frequent small doses of micronutrient blend'; }
elseif($soil_name=='Alluvial Soil') { $fertilizer[]='Apply DAP (Di-Ammonium Phosphate) for phosphorus'; $fertilizer[]='Use Urea for nitrogen top-dressing'; $fertilizer[]='Add zinc sulphate as micronutrient'; }
else { $fertilizer[]='Use balanced NPK 19-19-19 compound fertilizer'; $fertilizer[]='Apply organic compost for long-term fertility'; $fertilizer[]='Add micronutrient blend for trace elements'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommendations - SmartSoil Analyzer</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* SmartSoil – Colorful #F2B759 & #0A4A3C Palette */
        :root {
            --primary: #0A4A3C;
            --secondary: #1f6153;
            --bg-light: #fdfbf7;
            --bg-alt: #f1ebd8;
            --card-bg: #ffffff;
            --text-light: #ffffff;
            --border-color: #e6d8bc;
            --accent: #F2B759;
            --accent-hover: #e09e36;
            --accent-warm: #f5c77e;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        h1, h2, h3, h4, .logo, .btn {
            font-family: 'Outfit', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            color: var(--primary);
            line-height: 1.7;
            background-color: var(--bg-light);
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            width: 100%;
            background: var(--card-bg);
            border-bottom: 2px solid var(--accent);
            box-shadow: 0 4px 20px rgba(10, 74, 60, 0.15);
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 50px;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--primary);
            text-decoration: none;
            letter-spacing: 0.5px;
        }

        .nav-logo span { color: var(--accent); }

        .nav-logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(242, 183, 89, 0.4);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-links a {
            padding: 8px 18px;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--secondary);
            border-radius: 6px;
            transition: all 0.25s ease;
            font-family: 'Inter', sans-serif;
            text-decoration: none;
        }

        .nav-links a:hover { color: var(--primary); background: rgba(242, 183, 89, 0.15); }
        .nav-links a.danger { color: #c62828; }
        .nav-links a.danger:hover { background: #ffebee; color: #b71c1c; }

        .nav-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(242, 183, 89, 0.15);
            border: 1px solid var(--accent);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--primary);
            white-space: nowrap;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px 28px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.5px;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease;
            text-decoration: none;
        }

        .btn-green {
            background: linear-gradient(135deg, #0A4A3C 0%, #177864 100%);
            color: var(--text-light);
            box-shadow: 0 4px 14px rgba(10, 74, 60, 0.35);
        }
        .btn-green:hover {
            background: linear-gradient(135deg, #0d5e4d, #0A4A3C);
            color: var(--text-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(10, 74, 60, 0.5);
        }

        .btn-white {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: var(--primary);
            box-shadow: 0 4px 14px rgba(242, 183, 89, 0.35);
        }
        .btn-white:hover {
            background: linear-gradient(135deg, #e09e36, #cc8c29);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(242, 183, 89, 0.5);
        }

        /* Layout */
        .rec-page { max-width: 1100px; margin: 0 auto; padding: 60px 40px 80px; }

        .rec-head { text-align: center; margin-bottom: 50px; }
        .rec-head h1 { font-size: 2.4rem; font-weight: 800; color: var(--primary); margin-bottom: 12px; }
        .rec-head h1::after {
            content: '';
            display: block;
            width: 60px;
            height: 5px;
            background: linear-gradient(135deg, #F2B759 0%, #e09e36 100%);
            margin: 14px auto 0;
            border-radius: 3px;
        }
        .rec-head p { color: var(--secondary); font-size: 1.05rem; }

        .npk-bar {
            background: linear-gradient(135deg, #0A4A3C 0%, #177864 100%);
            color: var(--text-light);
            border-radius: 12px;
            padding: 32px 40px;
            display: flex;
            gap: 30px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(10, 74, 60, 0.3);
            border-top: 4px solid var(--accent);
            border-bottom: 4px solid var(--accent);
        }

        .npk-bar-label { flex: 1; min-width: 200px; }
        .npk-bar-label .t {
            font-size: 0.8rem;
            font-weight: 800;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 6px;
        }
        .npk-bar-label .s { font-size: 0.95rem; color: rgba(255,255,255,0.85); }

        .npk-bar-item {
            text-align: center;
            background: rgba(255,255,255,0.1);
            padding: 16px 28px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.15);
            min-width: 100px;
        }
        .npk-bar-item .v { font-size: 2rem; font-weight: 800; color: var(--text-light); font-family: 'Outfit', sans-serif; }
        .npk-bar-item .l { font-size: 0.72rem; font-weight: 600; color: rgba(255,255,255,0.65); text-transform: uppercase; letter-spacing: 0.8px; margin-top: 4px; }

        .rec-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 40px; }

        .rec-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-top: 4px solid var(--primary);
            border-radius: 12px;
            padding: 28px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(10, 74, 60, 0.05);
        }

        .rec-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(242, 183, 89, 0.2);
            border-top-color: var(--accent);
        }

        .rec-card h3 {
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--primary);
            margin-bottom: 14px;
            font-family: 'Inter', sans-serif;
        }
        .rec-card .rv { font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: 15px; font-family: 'Outfit', sans-serif; }
        .rec-card p { font-size: 0.95rem; color: var(--secondary); line-height: 1.65; }
        .rec-card.hl { border-top-color: var(--accent); background: linear-gradient(135deg, rgba(242, 183, 89, 0.1), rgba(242, 183, 89, 0.02)); box-shadow: 0 8px 30px rgba(242, 183, 89, 0.2); }

        .fl { text-align: left; margin-top: 10px; }
        .fl li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--bg-alt);
            font-size: 0.95rem;
            color: var(--secondary);
        }
        .fl li:last-child { border-bottom: none; }
        .fl .dot {
            width: 10px;
            height: 10px;
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            border-radius: 50%;
            margin-top: 7px;
            flex-shrink: 0;
            box-shadow: 0 0 8px rgba(242, 183, 89, 0.5);
        }

        .tips-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }

        .tip-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-top: 4px solid var(--primary);
            border-radius: 12px;
            padding: 28px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(10, 74, 60, 0.05);
        }

        .tip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(242, 183, 89, 0.2);
            border-top-color: var(--accent);
        }

        .tip-card .tip-icon { font-size: 1.8rem; margin-bottom: 14px; display: block; }
        .tip-card h4 { font-size: 1rem; font-weight: 700; color: var(--primary); margin-bottom: 8px; }
        .tip-card p { font-size: 0.88rem; color: var(--secondary); line-height: 1.6; }

        .rec-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 40px;
        }

        /* Footer */
        footer.site-footer {
            background: linear-gradient(135deg, #0A4A3C, #177864);
            color: rgba(255,255,255,0.8);
            text-align: center;
            padding: 30px 40px;
            font-size: 0.95rem;
            border-top: 5px solid var(--accent);
        }
        footer.site-footer strong { color: var(--accent); font-weight: 800; }

        /* Responsive */
        @media (max-width: 1024px) {
            .rec-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .nav-links a:not(.nav-cta):not(.danger) { display: none; }
            .rec-page { padding: 30px 20px; }
            .npk-bar { flex-direction: column; text-align: center; }
            .tips-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<!-- ====== NAVBAR ====== -->
<nav class="navbar">
    <a href="index.php" class="nav-logo">
        <div class="nav-logo-icon">🌱</div>
        Smart<span>Soil</span>
    </a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="index.php#contact">Contact</a>
        <span class="nav-chip">🌍 <?php echo htmlspecialchars($soil_name); ?></span>
        <span class="nav-chip" style="background:#fdfbf7; color:#0A4A3C; border-color:#e6d8bc;">👤 <?php echo htmlspecialchars($username); ?></span>
        <a href="dashboard.php">Dashboard</a>
        <a href="soil_select.php">Change Soil</a>
        <a href="logout.php" class="danger">Logout</a>
    </div>
</nav>

<!-- ====== RECOMMENDATION CONTENT ====== -->
<div class="rec-page">

    <div class="rec-head">
        <div style="font-size:0.78rem; font-weight:800; color:var(--accent); text-transform:uppercase; letter-spacing:1.5px; margin-bottom:10px;">🤖 AI-Powered Analysis</div>
        <h1>Crop &amp; Fertilizer Planning</h1>
        <p>Curated intelligently based on your field's live sensor analytics and <strong><?php echo htmlspecialchars($soil_name); ?></strong> soil profile.</p>
    </div>

    <!-- NPK Summary Bar -->
    <div class="npk-bar">
        <div class="npk-bar-label">
            <div class="t">Predicted NPK Matrix</div>
            <div class="s">Data powering this strategic recommendation</div>
        </div>
        <div class="npk-bar-item">
            <div class="v"><?php echo htmlspecialchars($N); ?></div>
            <div class="l">Nitrogen (N)</div>
        </div>
        <div class="npk-bar-item">
            <div class="v"><?php echo htmlspecialchars($P); ?></div>
            <div class="l">Phosphorus (P)</div>
        </div>
        <div class="npk-bar-item">
            <div class="v"><?php echo htmlspecialchars($K); ?></div>
            <div class="l">Potassium (K)</div>
        </div>
    </div>

    <!-- Recommendation Cards -->
    <div class="rec-grid">
        <div class="rec-card hl">
            <div style="font-size:3rem; margin-bottom:16px;">🌾</div>
            <h3>Recommended Crop</h3>
            <div class="rv"><?php echo htmlspecialchars($crop); ?></div>
            <p><?php echo htmlspecialchars($crop_reason); ?></p>
        </div>

        <div class="rec-card">
            <div style="font-size:3rem; margin-bottom:16px;">🧪</div>
            <h3>Recommended Fertilizers</h3>
            <ul class="fl">
                <?php foreach($fertilizer as $f): ?>
                <li><span class="dot"></span><?php echo htmlspecialchars($f); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Tips Grid -->
    <div class="tips-grid">
        <div class="tip-card">
            <span class="tip-icon">📈</span>
            <h4>Application Timing</h4>
            <p>Apply basal dose before sowing. Top-dress nitrogen in 2–3 splits during crop growth stages for best assimilation.</p>
        </div>
        <div class="tip-card">
            <span class="tip-icon">💧</span>
            <h4>Irrigation Advice</h4>
            <p>Ensure adequate moisture especially during critical pre-harvest growth stages based on live sensor readings.</p>
        </div>
        <div class="tip-card">
            <span class="tip-icon">⚙️</span>
            <h4>Next Steps</h4>
            <p>Re-test soil after fertilizer application and one full crop cycle to systematically track improvement progress.</p>
        </div>
    </div>

    <div class="rec-actions">
        <a href="dashboard.php"   class="btn btn-green">📊 Return to Dashboard</a>
        <a href="soil_select.php" class="btn btn-white">&#8635; Change Soil</a>
        <a href="fertilizer.php"  class="btn btn-white">🧪 Fertilizer Guide</a>
        <a href="crop.php"        class="btn btn-white">🌾 Crop Guide</a>
    </div>

</div>

<footer class="site-footer">
    &copy; 2026 <strong>SmartSoil Analyzer</strong> &mdash; Next-Gen Agricultural Intelligence.
</footer>

</body>
</html>
