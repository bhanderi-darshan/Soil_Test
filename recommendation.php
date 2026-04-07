<?php
session_start();
include "db.php";
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$soil_name = $_SESSION['soil_type'] ?? 'Unknown Soil';
$username  = $_SESSION['username'] ?? 'Farmer';
$npk_result = mysqli_query($conn,"SELECT * FROM predicted_npk ORDER BY id DESC LIMIT 1");
$npk = mysqli_fetch_assoc($npk_result) ?: ['nitrogen'=>'0','phosphorus'=>'0','potassium'=>'0'];
$N=$npk['nitrogen']; $P=$npk['phosphorus']; $K=$npk['potassium'];

if($N<30||$P<30||$K<30) { $crop='Sugarcane'; $crop_reason='Selected due to low NPK levels. Sugarcane tolerates lower nutrient concentrations.'; }
elseif($soil_name=='Sandy Soil') { $crop='Millet (Bajra)'; $crop_reason='Millet grows well in sandy, drought-prone soils with lower water requirements.'; }
elseif($soil_name=='Alluvial Soil') { $crop='Rice (Paddy)'; $crop_reason='Alluvial soil is ideal for paddy cultivation with its high water retention capacity.'; }
elseif($soil_name=='Black Soil') { $crop='Cotton'; $crop_reason='Black soil is best known for cotton due to excellent moisture and nutrient retention.'; }
elseif($soil_name=='Red Soil') { $crop='Groundnut'; $crop_reason='Red soil with good drainage is perfect for groundnut and oilseed crops.'; }
else { $crop='Wheat'; $crop_reason='Wheat thrives under balanced NPK conditions in your current soil type.'; }

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
        /* Colorful Agriculture-Themed Professional Variables */
        :root {
            --primary: #2c3e2d;         /* Deep earthy forest green for primary text */
            --secondary: #5d665e;       /* Earthy gray for paragraphs */
            --bg-light: #fdfbf7;        /* Very soft cream/warm white for background */
            --bg-alt: #f0f5f1;          /* Soft pale green for alternating sections */
            --card-bg: #f0f5f1;         /* Changed to avoid white boxes */
            --text-light: #ffffff;
            --border-color: #dcedc8;    /* Very soft green border */
            
            --accent: #4caf50;          /* Vibrant leaf green */
            --accent-hover: #388e3c;    /* Deep leaf green */
            --accent-warm: #f6a623;     /* Warm sun/harvest yellow/orange accent */
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
            background: var(--bg-light);
            border-bottom: 2px solid var(--border-color);
            box-shadow: 0 2px 20px rgba(44, 62, 45, 0.08);
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
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
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

        .nav-links a:hover { color: var(--accent-hover); background: var(--bg-alt); }
        .nav-links a.danger { color: #c62828; }
        .nav-links a.danger:hover { background: #ffebee; color: #b71c1c; }

        .nav-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            background: var(--bg-alt);
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
            background: var(--accent);
            color: var(--text-light);
            box-shadow: 0 4px 14px rgba(76, 175, 80, 0.3);
        }
        .btn-green:hover {
            background: var(--accent-hover);
            color: var(--text-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
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

        /* Layout */
        .rec-page { max-width: 1100px; margin: 0 auto; padding: 60px 40px 80px; }

        .rec-head { text-align: center; margin-bottom: 50px; }
        .rec-head h1 { font-size: 2.4rem; font-weight: 800; color: var(--primary); margin-bottom: 12px; }
        .rec-head h1::after {
            content: '';
            display: block;
            width: 50px;
            height: 4px;
            background: var(--accent-warm);
            margin: 14px auto 0;
            border-radius: 2px;
        }
        .rec-head p { color: var(--secondary); font-size: 1.05rem; }

        .npk-bar {
            background: linear-gradient(135deg, var(--primary), #1a251b);
            color: var(--text-light);
            border-radius: 12px;
            padding: 32px 40px;
            display: flex;
            gap: 30px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-top: 4px solid var(--accent-warm);
        }

        .npk-bar-label { flex: 1; min-width: 200px; }
        .npk-bar-label .t {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--accent-warm);
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
            border-top: 3px solid var(--accent);
            border-radius: 12px;
            padding: 28px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 15px rgba(44, 62, 45, 0.05);
        }

        .rec-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(44, 62, 45, 0.1);
            border-top-color: var(--accent-warm);
        }

        .rec-card h3 {
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--accent-warm);
            margin-bottom: 14px;
            font-family: 'Inter', sans-serif;
        }
        .rec-card .rv { font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: 15px; font-family: 'Outfit', sans-serif; }
        .rec-card p { font-size: 0.95rem; color: var(--secondary); line-height: 1.65; }
        .rec-card.hl { border-top-color: var(--accent-warm); background: #fffdf5; box-shadow: 0 8px 30px rgba(246, 166, 35, 0.1); }

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
            width: 8px;
            height: 8px;
            background: var(--accent-warm);
            border-radius: 50%;
            margin-top: 7px;
            flex-shrink: 0;
            box-shadow: 0 0 8px rgba(246, 166, 35, 0.4);
        }

        .tips-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }

        .tip-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-top: 3px solid #8d6e63;
            border-radius: 12px;
            padding: 28px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 15px rgba(44, 62, 45, 0.05);
        }

        .tip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(44, 62, 45, 0.1);
            border-top-color: var(--accent-warm);
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
            background: var(--primary);
            color: #a3b8a5;
            text-align: center;
            padding: 28px 40px;
            font-size: 0.9rem;
            border-top: 3px solid var(--accent-warm);
        }
        footer.site-footer strong { color: var(--text-light); }

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
        <span class="nav-chip" style="background:#fdfbf7; color:#2c3e2d; border-color:#dcedc8;">👤 <?php echo htmlspecialchars($username); ?></span>
        <a href="dashboard.php">Dashboard</a>
        <a href="soil_select.php">Change Soil</a>
        <a href="logout.php" class="danger">Logout</a>
    </div>
</nav>

<!-- ====== RECOMMENDATION CONTENT ====== -->
<div class="rec-page">

    <div class="rec-head">
        <div style="font-size:0.78rem; font-weight:700; color:#f6a623; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:10px;">🤖 AI-Powered Analysis</div>
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
