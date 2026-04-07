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
$crop = 'Wheat';
if($N<30||$P<30||$K<30) { $crop='Sugarcane'; }
elseif($soil_type==='Sandy Soil') { $crop='Millet'; }
elseif($soil_type==='Alluvial Soil') { $crop='Rice'; }
elseif($soil_type==='Black Soil') { $crop='Cotton'; }
elseif($soil_type==='Red Soil') { $crop='Groundnut'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Recommendation - SmartSoil Analyzer</title>
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
            width: 50px;
            height: 4px;
            background: var(--accent-warm);
            margin: 14px auto 0;
            border-radius: 2px;
        }
        .page-head p { font-size: 1.05rem; color: var(--secondary); max-width: 600px; margin: 0 auto; }

        .section-tag {
            display: inline-block;
            background: #e8f5e9;
            color: var(--accent-hover);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 12px;
        }

        /* Cards */
        .fcard {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-top: 3px solid var(--accent);
            border-radius: 12px;
            padding: 28px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 15px rgba(44, 62, 45, 0.05);
        }

        .fcard:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(44, 62, 45, 0.1);
            border-top-color: var(--accent-warm);
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
        <span class="nav-chip" style="background:#fdfbf7; color:#2c3e2d; border-color:#dcedc8;">👤 <?php echo htmlspecialchars($username); ?></span>
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

        <div class="rec-card hl" style="text-align:center; padding:55px 40px">
            <div style="font-size:3.5rem; margin-bottom:20px">🌾</div>
            <h3>AI Recommended Crop Strategy</h3>
            <div class="rv" style="font-size:3rem"><?php echo htmlspecialchars($crop); ?></div>
            <p style="margin-top:20px; font-size:1.05rem; max-width:420px; margin-left:auto; margin-right:auto">
                Based on <strong><?php echo htmlspecialchars($soil_type); ?></strong> characteristics.<br>
                <span style="color:#388e3c; font-weight:700">N: <?php echo $N; ?></span> &nbsp;
                <span style="color:#f6a623; font-weight:700">P: <?php echo $P; ?></span> &nbsp;
                <span style="color:#2c3e2d; font-weight:700">K: <?php echo $K; ?></span> mg/kg
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
