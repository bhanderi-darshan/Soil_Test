<?php
session_start();
include "db.php";
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if(!isset($_SESSION['soil_type'])) { header("Location: soil_select.php"); exit(); }
$soil_type = $_SESSION['soil_type'];
$username  = $_SESSION['username'] ?? 'Farmer';
$npk_result = mysqli_query($conn, "SELECT * FROM predicted_npk ORDER BY id DESC LIMIT 1");
$npk = mysqli_fetch_assoc($npk_result) ?: ['nitrogen'=>'0','phosphorus'=>'0','potassium'=>'0'];
$N=$npk['nitrogen']; $P=$npk['phosphorus']; $K=$npk['potassium'];
// Fetch User Location and Preferences for Filtered Prediction
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
    'Rice'      => ['n'=>[80, 120], 'p'=>[40, 60], 'k'=>[40, 60], 'soil'=>'Alluvial Soil'],
    'Wheat'     => ['n'=>[60, 90],  'p'=>[40, 50], 'k'=>[30, 50], 'soil'=>'Black Soil'],
    'Cotton'    => ['n'=>[70, 100], 'p'=>[30, 50], 'k'=>[60, 80], 'soil'=>'Black Soil'],
    'Groundnut' => ['n'=>[20, 40],  'p'=>[40, 60], 'k'=>[30, 50], 'soil'=>'Red Soil'],
    'Bajra'     => ['n'=>[40, 60],  'p'=>[20, 40], 'k'=>[20, 40], 'soil'=>'Sandy Soil'],
    'Maize'     => ['n'=>[90, 120], 'p'=>[40, 60], 'k'=>[40, 60], 'soil'=>'Alluvial Soil'],
    'Castor'    => ['n'=>[60, 80],  'p'=>[50, 70], 'k'=>[50, 70], 'soil'=>'Black Soil'],
    'Cumin'     => ['n'=>[30, 50],  'p'=>[30, 50], 'k'=>[20, 40], 'soil'=>'Sandy Soil'],
    'Onion'     => ['n'=>[100, 120],'p'=>[60, 80], 'k'=>[80, 100],'soil'=>'Alluvial Soil'],
    'Potato'    => ['n'=>[120, 150],'p'=>[80, 100],'k'=>[100, 150],'soil'=>'Black Soil'],
    'Gram'      => ['n'=>[20, 30],  'p'=>[40, 60], 'k'=>[20, 30], 'soil'=>'Black Soil'],
    'Tobacco'   => ['n'=>[100, 120],'p'=>[80, 100],'k'=>[120, 150],'soil'=>'Alluvial Soil'],
    'Mango'     => ['n'=>[70, 90],  'p'=>[30, 50], 'k'=>[50, 80], 'soil'=>'Red Soil'],
    'Banana'    => ['n'=>[180, 220],'p'=>[50, 70], 'k'=>[250, 300],'soil'=>'Alluvial Soil'],
    'Jowar'     => ['n'=>[60, 80],  'p'=>[30, 50], 'k'=>[30, 50], 'soil'=>'Sandy Soil'],
    'Chilli'    => ['n'=>[90, 120], 'p'=>[50, 70], 'k'=>[50, 80], 'soil'=>'Black Soil'],
    'Sugarcane' => ['n'=>[200, 250],'p'=>[70, 90], 'k'=>[100, 140],'soil'=>'Alluvial Soil'],
    'Onion'     => ['n'=>[100, 120],'p'=>[60, 80], 'k'=>[80, 100],'soil'=>'Alluvial Soil'],
];

$best_crop = 'Wheat';
$max_score = -999;

foreach ($crops_data as $name => $c) {
    if (!in_array($name, $location_supported_crops)) continue;
    if ($prediction_mode === 'regular' && !in_array($name, $preferred_crops)) continue;
    
    $score = 0;
    if ($soil_type === $c['soil']) $score += 50;
    
    $target_n = ($c['n'][0] + $c['n'][1]) / 2;
    $target_p = ($c['p'][0] + $c['p'][1]) / 2;
    $target_k = ($c['k'][0] + $c['k'][1]) / 2;
    
    $score -= abs($target_n - $N);
    $score -= abs($target_p - $P);
    $score -= abs($target_k - $K);
    
    if ($score > $max_score) {
        $max_score = $score;
        $best_crop = $name;
    }
}
$crop = $best_crop;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Recommendation - SmartSoil Analyzer</title>
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
        .container { max-width: 1200px; margin: 0 auto; padding: 0 40px; }
        .page-section { padding: 60px 0 80px; }

        .page-head { text-align: center; margin-bottom: 50px; }
        .page-head h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        .page-head h1::after {
            content: '';
            display: block;
            width: 60px;
            height: 5px;
            background: linear-gradient(135deg, #F2B759 0%, #e09e36 100%);
            margin: 14px auto 0;
            border-radius: 3px;
        }
        .page-head p { font-size: 1.05rem; color: var(--secondary); max-width: 600px; margin: 0 auto; }

        .section-tag {
            display: inline-block;
            background: rgba(242, 183, 89, 0.15);
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 6px 16px;
            border-radius: 20px;
            margin-bottom: 12px;
            border: 1px solid var(--accent);
        }

        /* Cards */
        .fcard {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-top: 4px solid var(--primary);
            border-radius: 12px;
            padding: 28px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(10, 74, 60, 0.05);
        }

        .fcard:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(242, 183, 89, 0.2);
            border-top-color: var(--accent);
        }

        .fcard-icon { font-size: 2.2rem; margin-bottom: 16px; }
        .fcard h3 { font-size: 1.05rem; font-weight: 700; color: var(--primary); margin-bottom: 10px; }
        .fcard p { font-size: 0.9rem; color: var(--secondary); line-height: 1.65; }

        /* Features Grid */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
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
            .card-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .nav-links a:not(.nav-cta):not(.danger) { display: none; }
            .card-grid { grid-template-columns: 1fr; padding: 0 20px; }
            .page-head h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="nav-logo">
        <div class="nav-logo-icon">🌱</div>
        Smart<span>Soil</span>
    </a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="index.php#contact">Contact</a>
        <span class="nav-chip">🌍 <?php echo htmlspecialchars($soil_type); ?></span>
        <span class="nav-chip" style="background:#fdfbf7; color:#0A4A3C; border-color:#e6d8bc;">👤 <?php echo htmlspecialchars($username); ?></span>
        <a href="dashboard.php">Dashboard</a>
        <a href="recommendation.php">Recommendations</a>
        <a href="logout.php" class="danger">Logout</a>
    </div>
</nav>

<div class="page-section">
    <div class="container" style="max-width:820px">
        <div class="page-head">
            <span class="section-tag">🌾 Crop Intelligence</span>
            <h1>Crop Recommendation</h1>
            <p>Targeted guidance modeled from live sensor statistics and your selected soil profile.</p>
        </div>

        <div class="rec-card hl" style="text-align:center; padding:55px 40px; border-top-color: var(--accent); background: linear-gradient(135deg, rgba(242, 183, 89, 0.1), rgba(242, 183, 89, 0.02)); box-shadow: 0 8px 30px rgba(242, 183, 89, 0.2); border-radius: 12px; border: 1px solid var(--border-color);">
            <div style="font-size:3.5rem; margin-bottom:20px">🌾</div>
            <h3>AI Recommended Crop Strategy</h3>
            <div class="rv" style="font-size:3rem; font-weight: 800; color: var(--primary); font-family: 'Outfit', sans-serif;"><?php echo htmlspecialchars($crop); ?></div>
            <p style="margin-top:20px; font-size:1.05rem; max-width:420px; margin-left:auto; margin-right:auto">
                Based on <strong><?php echo htmlspecialchars($soil_type); ?></strong> characteristics.<br>
                <span style="color:#0A4A3C; font-weight:800">N: <?php echo $N; ?></span> &nbsp;
                <span style="color:#F2B759; font-weight:800">P: <?php echo $P; ?></span> &nbsp;
                <span style="color:#1f6153; font-weight:800">K: <?php echo $K; ?></span> mg/kg
            </p>
        </div>

        <div class="action-row" style="margin-top:40px">
            <a href="dashboard.php"   class="btn btn-green">📊 Return to Dashboard</a>
            <a href="fertilizer.php"  class="btn btn-white">🧪 Fertilizer Plan</a>
        </div>
    </div>
</div>

<footer class="site-footer">
    &copy; 2026 <strong>SmartSoil Analyzer</strong> &mdash; Next-Gen Agricultural Intelligence.
</footer>

</body>
</html>
