<?php
session_start();
include "db.php";
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$soil_name = $_SESSION['soil_type'] ?? 'Unknown Soil';
$username  = $_SESSION['username'] ?? 'Farmer';
$fertilizer = [];
if($soil_name=='Black Soil') {
    $fertilizer = [
        ['icon'=>'🟤','title'=>'MOP Application','desc'=>'Apply MOP (Muriate of Potash) to build up Potassium levels for better plant immunity.'],
        ['icon'=>'🌿','title'=>'Nitrogen Boost','desc'=>'Add Urea (46-0-0) early in the growth cycle for immediate Nitrogen availability.'],
        ['icon'=>'♻️','title'=>'Organic Base','desc'=>'Use FYM (Farm Yard Manure) compost to retain moisture and improve soil structure.'],
    ];
} elseif($soil_name=='Red Soil') {
    $fertilizer = [
        ['icon'=>'🧪','title'=>'NPK Compound','desc'=>'Use NPK 12-32-16 compound fertilizer immediately before planting for balanced nutrition.'],
        ['icon'=>'🪨','title'=>'Acidity Control','desc'=>'Add gypsum explicitly to neutralize natural acidity and prevent soil crusting.'],
        ['icon'=>'🌾','title'=>'Micronutrients','desc'=>'Integrate Zinc and Iron sulfates as Red soil is naturally deficient in trace elements.'],
    ];
} elseif($soil_name=='Sandy Soil') {
    $fertilizer = [
        ['icon'=>'⏳','title'=>'Slow-Release','desc'=>'Use slow-release fertilizers to prevent leaching from fast-drainage sandy structure.'],
        ['icon'=>'⚖️','title'=>'Balanced NPK','desc'=>'Apply NPK 10-26-26 for balanced macro-nutrition across root zones.'],
        ['icon'=>'💧','title'=>'Micronutrient Blend','desc'=>'Apply frequent small doses of micronutrient blend to compensate for nutrient loss.'],
    ];
} elseif($soil_name=='Alluvial Soil') {
    $fertilizer = [
        ['icon'=>'🌊','title'=>'DAP Application','desc'=>'Apply DAP (Di-Ammonium Phosphate) for robust root and early plant establishment.'],
        ['icon'=>'🌿','title'=>'Nitrogen Topdress','desc'=>'Use Urea top-dressing during the vegetative stage for strong shoot growth.'],
        ['icon'=>'⚗️','title'=>'Zinc Addition','desc'=>'Add zinc sulphate micronutrient to prevent deficiencies common in alluvial fields.'],
    ];
} else {
    $fertilizer = [
        ['icon'=>'🌱','title'=>'Organic Start','desc'=>'Apply raw organic compost or poultry manure to raise soil carbon and microbial activity.'],
        ['icon'=>'🧮','title'=>'Balanced NPK','desc'=>'Use a customized NPK 19-19-19 compound fertilizer distributed slowly through the season.'],
        ['icon'=>'🔬','title'=>'Retention Boost','desc'=>'Ensure biochar or green manure covers are preserved to boost water-holding capacity.'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fertilizer Guide - SmartSoil Analyzer</title>
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
        <span class="nav-chip">🌍 <?php echo htmlspecialchars($soil_name); ?></span>
        <span class="nav-chip" style="background:#fdfbf7; color:#2c3e2d; border-color:#dcedc8;">👤 <?php echo htmlspecialchars($username); ?></span>
        <a href="dashboard.php">Dashboard</a>
        <a href="recommendation.php">Recommendations</a>
        <a href="logout.php" class="danger">Logout</a>
    </div>
</nav>

<div class="page-section">
    <div class="container" style="max-width:1000px">
        <div class="page-head">
            <span class="section-tag">🧪 Fertilizer Intelligence</span>
            <h1>Fertilization Strategy</h1>
            <p>Targeted chemical and organic formulations suited specifically for your <strong><?php echo htmlspecialchars($soil_name); ?></strong> field profile.</p>
        </div>

        <div class="tips-grid">
            <?php foreach($fertilizer as $item): ?>
            <div class="tip-card">
                <span class="tip-icon"><?php echo $item['icon']; ?></span>
                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                <p><?php echo htmlspecialchars($item['desc']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="action-row" style="margin-top:40px">
            <a href="dashboard.php" class="btn btn-green">📊 Return to Dashboard</a>
            <a href="crop.php"      class="btn btn-white">🌾 Crop Recommendation</a>
        </div>
    </div>
</div>

<footer class="site-footer">
    &copy; 2026 <strong>SmartSoil Analyzer</strong> &mdash; Next-Gen Agricultural Intelligence.
</footer>

</body>
</html>
