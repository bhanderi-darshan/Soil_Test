<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (isset($_GET['soil'])) { $_SESSION['soil_type'] = $_GET['soil']; header("Location: dashboard.php"); exit(); }
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Soil Profile - SmartSoil Analyzer</title>
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
        .fcard, .rec-card, .tip-card, .soil-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-top: 3px solid var(--accent);
            border-radius: 12px;
            padding: 28px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 15px rgba(44, 62, 45, 0.05);
        }

        .fcard:hover, .rec-card:hover, .tip-card:hover, .soil-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(44, 62, 45, 0.1);
            border-top-color: var(--accent-warm);
        }

        /* Soil Select */
        .soil-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .soil-card { cursor: default; }
        .soil-image { width: 2.8rem; height: auto; margin-bottom: 14px; display: block; }
        .soil-card h3 { font-size: 1.2rem; font-weight: 700; color: var(--primary); margin-bottom: 8px; }
        .soil-card .desc { font-size: 0.9rem; color: var(--secondary); line-height: 1.6; margin-bottom: 16px; }

        .prop-list { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
        .prop-tag {
            font-size: 0.78rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            background: var(--bg-alt);
            color: var(--accent-hover);
            border: 1px solid var(--border-color);
        }

        .sel-btn {
            display: block;
            width: 100%;
            padding: 12px;
            text-align: center;
            background: var(--bg-alt);
            border: 2px solid var(--accent);
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--accent-hover);
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.25s;
            text-decoration: none;
        }
        .sel-btn:hover {
            background: var(--accent);
            color: var(--text-light);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
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
        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .nav-links a:not(.nav-cta):not(.danger) { display: none; }
            .g3, .soil-grid { grid-template-columns: 1fr; padding: 0 20px; }
            .page-head h1 { font-size: 1.8rem; }
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
        <a href="login.php">Login</a>
        <span class="nav-chip" style="background:#fdfbf7; color:#2c3e2d; border-color:#dcedc8;">👤 <?php echo htmlspecialchars($username); ?></span>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php" class="danger">Logout</a>
    </div>
</nav>

<!-- ====== SOIL SELECTION ====== -->
<div class="page-section">
    <div class="container">
        <div class="page-head">
            <span class="section-tag">Step 1 of 1</span>
            <h1>Select Soil Profile</h1>
            <p>Choose your field's dominant soil type for the most accurate AI predictions and fertilizer recommendations.</p>
        </div>

        <div class="soil-grid">
            <div class="soil-card">
                <img src="images/black.jpg" alt="Black Soil" class="soil-image">
                <h3>Black Soil</h3>
                <p class="desc">High moisture retention capabilities, rich in lime and magnesium. Ideal for cotton farming.</p>
                <div class="prop-list">
                    <span class="prop-tag">High Moisture</span>
                    <span class="prop-tag">Lime Rich</span>
                    <span class="prop-tag">Cotton Friendly</span>
                </div>
                <a href="?soil=Black Soil" class="sel-btn">Select Black Soil &rarr;</a>
            </div>

            <div class="soil-card">
                <img src="images/red.jpg" alt="Red Soil" class="soil-image">
                <h3>Red Soil</h3>
                <p class="desc">Iron-rich composition natively low in nitrogen and organic matter. Highly porous and well-drained.</p>
                <div class="prop-list">
                    <span class="prop-tag">High Iron</span>
                    <span class="prop-tag">Low Nitrogen</span>
                    <span class="prop-tag">Porous</span>
                </div>
                <a href="?soil=Red Soil" class="sel-btn">Select Red Soil &rarr;</a>
            </div>

            <div class="soil-card">
                <img src="images/alluvial.jpg" alt="Alluvial Soil" class="soil-image">
                <h3>Alluvial Soil</h3>
                <p class="desc">Highly fertile profile excellent for intensive commercial farming and continuous crop cycles.</p>
                <div class="prop-list">
                    <span class="prop-tag">High Fertility</span>
                    <span class="prop-tag">Silt Base</span>
                    <span class="prop-tag">Water Retentive</span>
                </div>
                <a href="?soil=Alluvial Soil" class="sel-btn">Select Alluvial Soil &rarr;</a>
            </div>

            <div class="soil-card">
                <img src="images/sandy.jpg" alt="Sandy Soil" class="soil-image">
                <h3>Sandy Soil</h3>
                <p class="desc">Fast drainage mechanics requiring consistent nutrient application and regular irrigation routines.</p>
                <div class="prop-list">
                    <span class="prop-tag">High Drainage</span>
                    <span class="prop-tag">Low Nutrients</span>
                    <span class="prop-tag">Dry Climate</span>
                </div>
                <a href="?soil=Sandy Soil" class="sel-btn">Select Sandy Soil &rarr;</a>
            </div>

            <div class="soil-card">
                <img src="images/laterite.jpg" alt="Laterite Soil" class="soil-image">
                <h3>Laterite Soil</h3>
                <p class="desc">Traditionally poor in essential NPK. Requires significant fertilization programs to maximize yields.</p>
                <div class="prop-list">
                    <span class="prop-tag">Low NPK</span>
                    <span class="prop-tag">Acidic</span>
                    <span class="prop-tag">Leached</span>
                </div>
                <a href="?soil=Laterite Soil" class="sel-btn">Select Laterite Soil &rarr;</a>
            </div>
        </div>

        <div style="text-align:center; margin-top:50px;">
            <a href="dashboard.php" class="btn btn-white">Skip to Dashboard &rarr;</a>
        </div>
    </div>
</div>

<footer class="site-footer">
    &copy; 2026 <strong>SmartSoil Analyzer</strong> &mdash; Next-Gen Agricultural Intelligence.
</footer>

</body>
</html>
