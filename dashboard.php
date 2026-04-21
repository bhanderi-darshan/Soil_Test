<?php
session_start();
include "db.php";
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$soil_name = $_SESSION['soil_type'] ?? 'Black Soil';
$username  = $_SESSION['username'] ?? 'Farmer';
$sensor_result = mysqli_query($conn, "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1");
$sensor = mysqli_fetch_assoc($sensor_result) ?: ['ec'=>'0.00','moisture'=>'0.00','temperature'=>'0.00'];
$npk_result = mysqli_query($conn, "SELECT * FROM predicted_npk ORDER BY id DESC LIMIT 1");
$npk = mysqli_fetch_assoc($npk_result) ?: ['nitrogen'=>'0','phosphorus'=>'0','potassium'=>'0'];
$soil_data = ['standard_n'=>'50','standard_p'=>'40','standard_k'=>'50'];
$sq = mysqli_query($conn, "SELECT * FROM soil_types WHERE soil_name='".mysqli_real_escape_string($conn,$soil_name)."' LIMIT 1");
if($sq && mysqli_num_rows($sq)>0) $soil_data = mysqli_fetch_assoc($sq);

// Handle Prediction Mode Toggle
if(isset($_POST['toggle_mode'])) {
    $new_mode = $_POST['toggle_mode'] === 'all' ? 'all' : 'regular';
    mysqli_query($conn, "UPDATE farmer_preferences SET prediction_mode='$new_mode' WHERE user_id=".$_SESSION['user_id']);
}

// Fetch Preferences
$pref_result = mysqli_query($conn, "SELECT * FROM farmer_preferences WHERE user_id=".$_SESSION['user_id']);
$prefs = mysqli_fetch_assoc($pref_result) ?: ['prediction_mode'=>'regular', 'preferred_crops'=>''];
$prediction_mode = $prefs['prediction_mode'];

$status=[];
if($npk['nitrogen']<$soil_data['standard_n'])   $status[]=['label'=>'Nitrogen Deficient','icon'=>'N'];
if($npk['phosphorus']<$soil_data['standard_p']) $status[]=['label'=>'Phosphorus Deficient','icon'=>'P'];
if($npk['potassium']<$soil_data['standard_k'])  $status[]=['label'=>'Potassium Deficient','icon'=>'K'];
$avg = ($npk['nitrogen']+$npk['phosphorus']+$npk['potassium'])/3;
if($avg<30){$fert='Low Fertility';$fcol='#c62828';}
elseif($avg<60){$fert='Medium Fertility';$fcol='#f6a623';}
else{$fert='High Fertility';$fcol='#388e3c';}
function pct($v,$m){return $m>0?min(100,round(($v/$m)*100)):0;}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SmartSoil Analyzer</title>
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
            background: var(--bg-light);
            min-height: 100vh;
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
            color: var(--text-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(0, 161, 155, 0.35);
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

        .nav-links .nav-cta {
            background: linear-gradient(135deg, #0A4A3C 0%, #177864 100%);
            color: var(--text-light) !important;
            padding: 9px 22px;
            border-radius: 6px;
            font-weight: 700;
            box-shadow: 0 3px 12px rgba(10, 74, 60, 0.3);
            border: 1px solid #146150;
        }
        .nav-links .nav-cta:hover {
            background: var(--accent-hover) !important;
            color: var(--text-light) !important;
            transform: translateY(-1px);
        }

        .nav-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(0,161,155,0.1);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--accent-hover);
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
            background: var(--card-bg);
            color: var(--accent-hover);
            border: 2px solid var(--accent);
        }
        .btn-white:hover {
            background: var(--accent);
            color: var(--text-light);
            transform: translateY(-2px);
        }

        /* Dashboard */
        .dash { padding: 40px 0 70px; }

        .dash-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 40px;
            flex-wrap: wrap;
            gap: 16px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 40px;
            margin-bottom: 40px;
        }

        .dash-top h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 6px;
        }

        .dash-top h1::after {
            content: '';
            display: block;
            width: 60px;
            height: 5px;
            background: linear-gradient(135deg, #F2B759 0%, #e09e36 100%);
            margin-top: 8px;
            border-radius: 3px;
        }

        .dash-top p { font-size: 0.95rem; color: var(--secondary); }

        .refresh {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--secondary);
            cursor: pointer;
            transition: all 0.25s;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 2px 10px rgba(44, 62, 45, 0.05);
        }
        .refresh:hover { background: var(--bg-alt); color: var(--accent-hover); border-color: var(--accent); transform: translateY(-2px); }

        .d-section {
            margin-bottom: 40px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 40px;
            margin-bottom: 40px;
        }

        .d-section:nth-child(even) {
            background: linear-gradient(135deg, rgba(242, 183, 89, 0.08) 0%, rgba(10, 74, 60, 0.05) 100%);
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 40px;
            border: 1px solid rgba(242, 183, 89, 0.2);
        }

        .d-head { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }

        .d-icon {
            width: 40px;
            height: 40px;
            background: var(--bg-alt);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            border: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .d-head h2 { font-size: 1.2rem; font-weight: 700; color: var(--primary); }

        .d-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            background: rgba(0,161,155,0.12);
            color: var(--accent-hover);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border: 1px solid rgba(0,161,155,0.3);
        }

        .d-badge-live { animation: blink 2s infinite; }
        @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }

        .g3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

        /* Cards */
        .d-card, .cmp-card, .fert-box {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-top: 3px solid var(--accent);
            border-radius: 12px;
            padding: 28px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 15px rgba(0, 161, 155, 0.07);
        }

        .d-card:hover, .cmp-card:hover, .fert-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 161, 155, 0.15);
            border-top-color: var(--accent-warm);
        }

        .d-lbl {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--secondary);
            margin-bottom: 10px;
        }

        .d-val {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 6px;
            line-height: 1;
            font-family: 'Outfit', sans-serif;
        }

        .d-val su { font-size: 0.9rem; color: var(--secondary); font-weight: 500; font-style: normal; }
        .d-sub { font-size: 0.85rem; color: var(--secondary); margin-top: 4px; }

        .bar-wrap { margin-top: 16px; }
        .bar-labels { display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--secondary); margin-bottom: 6px; font-weight: 600; }
        .bar-bg { height: 8px; background: #c0b8b0; border-radius: 10px; overflow: hidden; }
        .bar-fg { height: 100%; border-radius: 10px; background: linear-gradient(90deg, var(--accent), var(--accent-hover)); transition: width 1.2s ease; }
        .bar-red { background: linear-gradient(90deg, var(--accent-warm), #e53935) !important; }

        .val-red { color: #c62828 !important; }
        .val-green { color: var(--accent-hover) !important; }

        .cmp-row {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 20px;
            padding: 18px 0;
            border-bottom: 1px solid var(--bg-alt);
        }
        .cmp-row:last-child { border-bottom: none; }

        .cmp-mid {
            text-align: center;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 6px 16px;
            border-radius: 20px;
            background: var(--bg-alt);
            color: var(--accent-hover);
            letter-spacing: 0.8px;
            white-space: nowrap;
        }

        .cv { font-size: 1.8rem; font-weight: 800; font-family: 'Outfit', sans-serif; color: var(--primary); }
        .clbl { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--secondary); margin-top: 4px; }

        .fert-ring {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            flex-shrink: 0;
            background: var(--bg-alt);
            border: 3px solid var(--accent);
        }

        .action-row {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 40px;
            flex-wrap: wrap;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
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
            .g3 { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .nav-links a:not(.nav-cta):not(.danger) { display: none; }
            .g3, .d-section { padding: 0 20px; }
            .d-section:nth-child(even) { padding: 20px; }
            .dash-top, .action-row { padding: 0 20px; }
            .dash-top h1 { font-size: 1.8rem; }
        }

        .sv  { color: var(--secondary); }
        .lv  { color: #c62828; }
        .ov  { color: var(--accent-hover); }
    </style>
</head>
<body>

<!-- ====== NAVBAR ====== -->
<nav class="navbar">
    <a href="index.php" class="nav-logo">
        <div class="nav-logo-icon">SS</div>
        Smart<span>Soil</span>
    </a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="dashboard.php">Profile</a>
        <a href="index.php#contact">Contact</a>
        <a href="logout.php" class="danger">Logout</a>
        
        <!-- Toggle Mode -->
        <form method="POST" style="display:inline-flex; margin-left:15px; background:var(--bg-alt); padding:4px; border-radius:30px; border:1px solid var(--border-color);">
            <button type="submit" name="toggle_mode" value="regular" class="nav-chip" style="margin:0; border:none; cursor:pointer; background:<?php echo $prediction_mode==='regular'?'var(--accent)':'transparent'; ?>; color:<?php echo $prediction_mode==='regular'?'white':'var(--secondary)'; ?>; transition:0.3s;">
                Regular Crops
            </button>
            <button type="submit" name="toggle_mode" value="all" class="nav-chip" style="margin:0; border:none; cursor:pointer; background:<?php echo $prediction_mode==='all'?'var(--accent)':'transparent'; ?>; color:<?php echo $prediction_mode==='all'?'white':'var(--secondary)'; ?>; transition:0.3s;">
                All Crops
            </button>
        </form>
    </div>
</nav>

<!-- ====== DASHBOARD CONTENT ====== -->
<div class="dash">

    <div class="dash-top">
        <div>
            <h1>Soil Analytics Dashboard</h1>
            <p>Live sensor data &middot; AI predictions &middot; Soil: <strong style="color:#388e3c"><?php echo htmlspecialchars($soil_name); ?></strong></p>
            <p style="font-size:0.85rem; color:var(--secondary); margin-top:4px;">
                Location: <strong><?php echo htmlspecialchars($prefs['district']); ?> &rsaquo; <?php echo htmlspecialchars($prefs['taluka']); ?></strong> &nbsp; | &nbsp;
                Hardware: <strong style="color:<?php echo !empty($user['hardware_id'])?'var(--accent)':'#777'; ?>"><?php echo !empty($user['hardware_id'])?htmlspecialchars($user['hardware_id']):'Not Linked'; ?></strong>
            </p>
        </div>
        <div class="refresh" onclick="location.reload()">
            <span>&#8635;</span> Synchronize Data
        </div>
    </div>

    <!-- AI RECOMMENDATION STRATEGY -->
    <div class="d-section" style="margin-bottom:20px;">
        <div class="d-card" style="display:flex; justify-content:space-between; align-items:center; border:1px solid var(--border-color); background:rgba(255,255,255,0.7); backdrop-filter:blur(5px);">
            <div>
                <h3 style="font-size:1.1rem; color:var(--primary); font-weight:800;">AI Recommendation Mode</h3>
                <p style="font-size:0.85rem; color:var(--secondary);">How should the system prioritize its suggestions?</p>
            </div>
            <form method="POST">
                <div style="background:#eee; padding:3px; border-radius:30px; display:inline-flex;">
                    <button type="submit" name="toggle_mode" value="regular" style="cursor:pointer; border:none; padding:8px 18px; border-radius:30px; font-weight:700; transition:0.3s; background:<?php echo $prediction_mode==='regular'?'var(--accent)':'transparent'; ?>; color:<?php echo $prediction_mode==='regular'?'white':'#666'; ?>;">
                        Farmer Choice
                    </button>
                    <button type="submit" name="toggle_mode" value="all" style="cursor:pointer; border:none; padding:8px 18px; border-radius:30px; font-weight:700; transition:0.3s; background:<?php echo $prediction_mode==='all'?'var(--accent)':'transparent'; ?>; color:<?php echo $prediction_mode==='all'?'white':'#666'; ?>;">
                        Universal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SENSOR DATA -->
    <div class="d-section">
        <div class="d-head">
            <div class="d-icon">H</div>
            <h2>Hardware Sensor Readings</h2>
            <span class="d-badge d-badge-live">LIVE</span>
        </div>
        <div class="g3">
            <div class="d-card">
                <div class="d-lbl">Electrical Conductivity (EC)</div>
                <div class="d-val"><?php echo htmlspecialchars($sensor['ec']); ?><su>mS/cm</su></div>
                <div class="d-sub">Measures soil salinity level</div>
                <div class="bar-wrap">
                    <div class="bar-labels"><span>0</span><span>3.0 max</span></div>
                    <div class="bar-bg"><div class="bar-fg" style="width:<?php echo pct($sensor['ec'],3); ?>%"></div></div>
                </div>
            </div>
            <div class="d-card">
                <div class="d-lbl">Soil Moisture</div>
                <div class="d-val"><?php echo htmlspecialchars($sensor['moisture']); ?><su>%</su></div>
                <div class="d-sub">Volumetric water content in field</div>
                <div class="bar-wrap">
                    <div class="bar-labels"><span>0%</span><span>100%</span></div>
                    <div class="bar-bg"><div class="bar-fg" style="width:<?php echo pct($sensor['moisture'],100); ?>%"></div></div>
                </div>
            </div>
            <div class="d-card">
                <div class="d-lbl">Soil Temperature</div>
                <div class="d-val"><?php echo htmlspecialchars($sensor['temperature']); ?><su>&deg;C</su></div>
                <div class="d-sub">Surface soil temperature reading</div>
                <div class="bar-wrap">
                    <div class="bar-labels"><span>0&deg;C</span><span>60&deg;C max</span></div>
                    <div class="bar-bg"><div class="bar-fg" style="width:<?php echo pct($sensor['temperature'],60); ?>%"></div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ML NPK -->
    <div class="d-section">
        <div class="d-head">
            <div class="d-icon">AI</div>
            <h2>AI-Predicted NPK Values</h2>
            <span class="d-badge" style="color:#f6a623; border-color:#f6a623; background:#fff8e1;">Precision AI Model</span>
        </div>
        <div class="g3">
            <div class="d-card">
                <div class="d-lbl">Nitrogen (N)</div>
                <div class="d-val <?php echo $npk['nitrogen']<$soil_data['standard_n']?'lv':'ov'; ?>"><?php echo htmlspecialchars($npk['nitrogen']); ?><su>mg/kg</su></div>
                <div class="d-sub">Target: <?php echo $soil_data['standard_n']; ?> mg/kg for <?php echo htmlspecialchars($soil_name); ?></div>
                <div class="bar-wrap">
                    <div class="bar-labels"><span>0</span><span>Target: <?php echo $soil_data['standard_n']; ?></span></div>
                    <div class="bar-bg"><div class="bar-fg <?php echo $npk['nitrogen']<$soil_data['standard_n']?'bar-red':''; ?>" style="width:<?php echo pct($npk['nitrogen'],$soil_data['standard_n']*1.5); ?>%"></div></div>
                </div>
            </div>
            <div class="d-card">
                <div class="d-lbl">Phosphorus (P)</div>
                <div class="d-val <?php echo $npk['phosphorus']<$soil_data['standard_p']?'lv':'ov'; ?>"><?php echo htmlspecialchars($npk['phosphorus']); ?><su>mg/kg</su></div>
                <div class="d-sub">Target: <?php echo $soil_data['standard_p']; ?> mg/kg for <?php echo htmlspecialchars($soil_name); ?></div>
                <div class="bar-wrap">
                    <div class="bar-labels"><span>0</span><span>Target: <?php echo $soil_data['standard_p']; ?></span></div>
                    <div class="bar-bg"><div class="bar-fg <?php echo $npk['phosphorus']<$soil_data['standard_p']?'bar-red':''; ?>" style="width:<?php echo pct($npk['phosphorus'],$soil_data['standard_p']*1.5); ?>%"></div></div>
                </div>
            </div>
            <div class="d-card">
                <div class="d-lbl">Potassium (K)</div>
                <div class="d-val <?php echo $npk['potassium']<$soil_data['standard_k']?'lv':'ov'; ?>"><?php echo htmlspecialchars($npk['potassium']); ?><su>mg/kg</su></div>
                <div class="d-sub">Target: <?php echo $soil_data['standard_k']; ?> mg/kg for <?php echo htmlspecialchars($soil_name); ?></div>
                <div class="bar-wrap">
                    <div class="bar-labels"><span>0</span><span>Target: <?php echo $soil_data['standard_k']; ?></span></div>
                    <div class="bar-bg"><div class="bar-fg <?php echo $npk['potassium']<$soil_data['standard_k']?'bar-red':''; ?>" style="width:<?php echo pct($npk['potassium'],$soil_data['standard_k']*1.5); ?>%"></div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- COMPARISON -->
    <div class="d-section">
        <div class="d-head">
            <div class="d-icon">VS</div>
            <h2>Predicted vs Standard Baseline (<?php echo htmlspecialchars($soil_name); ?>)</h2>
        </div>
        <div class="cmp-card">
            <div class="cmp-row">
                <div style="text-align:right">
                    <div class="cv <?php echo $npk['nitrogen']<$soil_data['standard_n']?'lv':'ov'; ?>"><?php echo $npk['nitrogen']; ?></div>
                    <div class="clbl">Predicted N (mg/kg)</div>
                </div>
                <div class="cmp-mid">NITROGEN</div>
                <div>
                    <div class="cv sv"><?php echo $soil_data['standard_n']; ?></div>
                    <div class="clbl">Standard N (mg/kg)</div>
                </div>
            </div>
            <div class="cmp-row">
                <div style="text-align:right">
                    <div class="cv <?php echo $npk['phosphorus']<$soil_data['standard_p']?'lv':'ov'; ?>"><?php echo $npk['phosphorus']; ?></div>
                    <div class="clbl">Predicted P (mg/kg)</div>
                </div>
                <div class="cmp-mid">PHOSPHORUS</div>
                <div>
                    <div class="cv sv"><?php echo $soil_data['standard_p']; ?></div>
                    <div class="clbl">Standard P (mg/kg)</div>
                </div>
            </div>
            <div class="cmp-row">
                <div style="text-align:right">
                    <div class="cv <?php echo $npk['potassium']<$soil_data['standard_k']?'lv':'ov'; ?>"><?php echo $npk['potassium']; ?></div>
                    <div class="clbl">Predicted K (mg/kg)</div>
                </div>
                <div class="cmp-mid">POTASSIUM</div>
                <div>
                    <div class="cv sv"><?php echo $soil_data['standard_k']; ?></div>
                    <div class="clbl">Standard K (mg/kg)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- HEALTH STATUS -->
    <div class="d-section">
        <div class="d-head">
            <div class="d-icon">SH</div>
            <h2>Soil Health and Fertility Status</h2>
        </div>
        <div class="g3">
            <?php if(empty($status)): ?>
            <div class="d-card" style="border-top-color:#388e3c">
                <div style="font-size:2.5rem; margin-bottom:14px; color:#388e3c; font-weight:900;">OK</div>
                <div class="d-lbl">All Nutrients</div>
                <div class="d-val ov" style="font-size:1.6rem">Balanced</div>
                <div class="d-sub" style="margin-top:8px">No deficiencies detected. Field is healthy.</div>
            </div>
            <?php else: foreach($status as $s): ?>
            <div class="d-card" style="border-top-color:#c62828">
                <div style="font-size:2rem; font-weight:900; color:#c62828; margin-bottom:14px"><?php echo htmlspecialchars($s['icon']); ?></div>
                <div class="d-lbl">Nutrient Alert</div>
                <div class="d-val lv" style="font-size:1.2rem"><?php echo htmlspecialchars($s['label']); ?></div>
                <div class="d-sub" style="margin-top:8px">Below standard level — needs attention.</div>
            </div>
            <?php endforeach; endif; ?>

            <div class="fert-box" style="border-top-color:<?php echo $fcol; ?>">
                <div class="fert-ring" style="border-color:<?php echo $fcol; ?>; color:<?php echo $fcol; ?>;">
                    <?php echo $avg>=60?'H':($avg>=30?'M':'L'); ?>
                </div>
                <div>
                    <h3 style="color:<?php echo $fcol; ?>; font-size:1.3rem; font-weight:800; margin-bottom:5px"><?php echo $fert; ?></h3>
                    <p style="font-size:0.9rem; color:#5d665e">Average NPK: <strong><?php echo round($avg,1); ?> mg/kg</strong><br>
                    <?php echo count($status)===0?'All nutrients are within optimal range.':count($status).' nutrient(s) need immediate attention.'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTIONS -->
    <div class="action-row">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin_panel.php" class="btn btn-green" style="background:#1e293b;">&#9881; Admin Panel</a>
        <?php endif; ?>
        <a href="recommendation.php" class="btn btn-green">Crop &amp; Fertilizer Plan</a>
        <a href="soil_select.php"    class="btn btn-white">Change Soil Type</a>
        <a href="fertilizer.php"     class="btn btn-white">Fertilizer Details</a>
        <a href="crop.php"           class="btn btn-white">Crop Details</a>
        <a href="logout.php"         class="btn btn-white" style="color:#c62828; border-color:#ffcdd2;">Logout</a>
    </div>

</div>

<footer class="site-footer">
    &copy; 2026 <strong>SmartSoil Analyzer</strong> &mdash; Next-Gen Agricultural Intelligence.
</footer>

</body>
</html>
