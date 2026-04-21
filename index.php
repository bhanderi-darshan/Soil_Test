<?php
session_start();
include "db.php";
$query_msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_query'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $query_text = mysqli_real_escape_string($conn, trim($_POST['query']));
    if (!empty($name) && !empty($email) && !empty($query_text)) {
        if (mysqli_query($conn, "INSERT INTO queries (name, email, message) VALUES ('$name', '$email', '$query_text')")) {
            $query_msg = "Your query has been successfully submitted. We will contact you soon.";
        } else {
            $query_msg = "Error submitting query. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Soil Analyzer</title>
    <!-- Modern, professional typography -->
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@400;500&display=swap"
        rel="stylesheet">
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

        h1,
        h2,
        h3,
        h4,
        .logo,
        .btn {
            font-family: 'Outfit', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            color: var(--primary);
            line-height: 1.7;
            background: var(--bg-light);
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

        .nav-logo span {
            color: var(--accent);
        }

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

        .nav-links a:hover {
            color: var(--primary);
            background: rgba(242, 183, 89, 0.15);
        }

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
            background: linear-gradient(135deg, #0d5e4d 0%, #146150 100%) !important;
            color: var(--text-light) !important;
            transform: translateY(-1px);
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            background: linear-gradient(135deg, rgba(10, 74, 60, 0.85) 0%, rgba(31, 97, 83, 0.75) 100%),
                url('images/farm_bg.png') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--text-light);
            padding: 0 20px;
        }

        .hero-content {
            max-width: 900px;
            margin-top: 50px;
        }

        .hero h1 {
            font-size: 4.5rem;
            font-weight: 700;
            margin-bottom: 25px;
            letter-spacing: -1px;
            line-height: 1.1;
            text-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }

        .hero p {
            font-size: 1.35rem;
            font-weight: 400;
            margin-bottom: 50px;
            color: #f1f8e9;
            max-width: 750px;
            margin-left: auto;
            margin-right: auto;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);
        }

        .hero .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: var(--primary);
            padding: 18px 50px;
            font-size: 1.1rem;
            font-weight: 800;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.3s ease;
            border: none;
            border-radius: 4px;
            box-shadow: 0 8px 25px rgba(242, 183, 89, 0.4);
        }

        .hero .btn:hover {
            background: linear-gradient(135deg, #e09e36, #cc8c29);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(242, 183, 89, 0.6);
        }

        /* Common Layout */
        section {
            padding: 110px 50px;
        }

        .section-title {
            font-size: 2.6rem;
            font-weight: 700;
            margin-bottom: 60px;
            text-align: center;
            color: var(--primary);
            letter-spacing: -0.5px;
            position: relative;
        }

        .section-title::after {
            content: '';
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--accent-warm));
            display: block;
            margin: 15px auto 0;
            border-radius: 2px;
        }

        /* Product Section */
        .product-section {
            background: var(--bg-light);
        }

        .product-container {
            max-width: 1250px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            gap: 80px;
        }

        .product-text {
            flex: 1;
        }

        .product-text h2 {
            font-size: 2.4rem;
            margin-bottom: 25px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: var(--primary);
            position: relative;
        }

        .product-text h2::after {
            content: '';
            width: 50px;
            height: 4px;
            background-color: var(--accent-warm);
            display: block;
            margin-top: 15px;
            border-radius: 2px;
        }

        .product-text p {
            margin-bottom: 20px;
            color: var(--secondary);
            font-size: 1.15rem;
        }

        .product-image {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: transparent;
        }

        .product-image img {
            max-width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            filter: drop-shadow(0 15px 30px rgba(44, 62, 45, 0.1));
        }

        .product-placeholder {
            width: 100%;
            height: 400px;
            background-color: #f1f8e9;
            border: 2px dashed #81c784;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #388e3c;
            font-weight: 500;
            text-align: center;
            padding: 20px;
            border-radius: 8px;
        }

        /* Features Section */
        .features-section {
            background: linear-gradient(135deg, rgba(242, 183, 89, 0.08) 0%, rgba(10, 74, 60, 0.05) 100%);
        }

        .features-grid {
            max-width: 1250px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
        }

        .feature-card {
            background: var(--card-bg);
            padding: 50px 40px;
            border: 1px solid var(--border-color);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 12px;
            border-top: 4px solid var(--primary);
            box-shadow: 0 5px 20px rgba(10, 74, 60, 0.05);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(242, 183, 89, 0.2);
            border-top-color: var(--accent);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary);
            font-weight: 600;
        }

        .feature-card p {
            color: var(--secondary);
            font-size: 1.05rem;
        }

        /* Contact Section */
        .contact-section {
            background: var(--bg-light);
        }

        .contact-container {
            max-width: 1250px;
            margin: 0 auto;
            display: flex;
            gap: 60px;
            align-items: flex-start;
        }

        .contact-details {
            flex: 1;
            background: var(--card-bg);
            color: var(--primary);
            padding: 50px;
            border-radius: 8px;
            box-shadow: 0 15px 35px rgba(0, 161, 155, 0.08);
            border: 1px solid var(--border-color);
            border-top: 4px solid var(--accent);
        }

        .contact-details h3 {
            font-size: 1.8rem;
            margin-bottom: 30px;
            color: var(--primary);
            font-weight: 700;
            position: relative;
        }

        .contact-details h3::after {
            content: '';
            width: 40px;
            height: 3px;
            background-color: var(--accent-warm);
            display: block;
            margin-top: 15px;
            border-radius: 2px;
        }

        .contact-details p {
            font-size: 1.15rem;
            color: var(--secondary);
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            position: relative;
        }

        .contact-details p strong {
            color: var(--primary);
            min-width: 140px;
        }

        .contact-form-wrapper {
            flex: 1;
            width: 100%;
            background: var(--card-bg);
            padding: 50px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 161, 155, 0.08);
            border-top: 4px solid var(--accent);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px;
            border: 2px solid var(--border-color);
            background-color: var(--bg-light);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
            border-radius: 8px;
            font-weight: 500;
            color: var(--primary);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(242, 183, 89, 0.2);
            background-color: #ffffff;
        }

        textarea.form-control {
            resize: vertical;
            height: 160px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #0A4A3C, #177864);
            color: var(--text-light);
            border: none;
            padding: 18px 30px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border-radius: 8px;
            font-family: 'Outfit', sans-serif;
            box-shadow: 0 6px 15px rgba(10, 74, 60, 0.3);
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #0d5e4d, #0A4A3C);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(10, 74, 60, 0.4);
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, #0A4A3C, #177864);
            color: rgba(255,255,255,0.8);
            text-align: center;
            padding: 40px 20px;
            font-size: 0.95rem;
            border-top: 5px solid var(--accent);
        }

        footer strong {
            color: var(--accent);
            font-weight: 800;
        }

        /* Responsive */
        @media (max-width: 1000px) {
            .product-container {
                flex-direction: column;
                text-align: center;
            }

            .product-text h2::after {
                margin: 15px auto 0;
            }

            .contact-container {
                flex-direction: column;
            }

            .contact-details,
            .contact-form-wrapper {
                width: 100%;
            }

            .contact-details p {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .contact-details p strong {
                min-width: auto;
            }

            .navbar {
                flex-direction: column;
                gap: 20px;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <!-- Header Navigation Section -->
    <nav class="navbar">
        <a href="index.php" class="nav-logo">
            <div class="nav-logo-icon">🌱</div>
            Smart<span>Soil</span>
        </a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="index.php#contact">Contact</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Precision Agriculture Intelligence</h1>
            <p>A sophisticated field monitoring system. Monitor your soil health seamlessly and optimize your yields
                with real-time sensor data and professional guidance.</p>
            <a href="#product" class="btn">Discover the Hardware</a>
        </div>
    </section>

    <!-- Product Details Section -->
    <section id="product" class="product-section">
        <div class="product-container">
            <div class="product-text">
                <h2>Product Overview</h2>
                <p>The Smart Soil Analyzer is a state-of-the-art IoT device designed to provide farmers and commercial
                    agriculture teams with comprehensive, real-time insights into field conditions.</p>
                <p>Equipped with advanced multi-parameter sensors, the device continuously monitors moisture levels,
                    electrical conductivity (EC), and ambient temperature, automatically transmitting robust datasets to
                    our cloud infrastructure for deep analysis.</p>
                <p><strong>Industrial Durability:</strong> Engineered to withstand harsh agricultural environments,
                    ensuring reliable performance across all seasons.</p>
            </div>
            <div class="product-image">
                <!-- Using the existing product.png from the images directory -->
                <img src="images/product.png" alt="Smart Soil Analyzer Hardware"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="product-placeholder" style="display: none;">
                    [ Insert Your Product Photo Here ]
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <h2 class="section-title">Core Capabilities</h2>
        <div class="features-grid">
            <div class="feature-card">
                <h3>Hardware Integration</h3>
                <p>High-precision physical sensors measure EC, moisture, and temperature directly from your soil 24/7
                    with virtually zero latency.</p>
            </div>
            <div class="feature-card">
                <h3>AI NPK Prediction</h3>
                <p>Instantly predict Nitrogen, Phosphorus, and Potassium levels through highly sophisticated machine
                    learning models based on local data.</p>
            </div>
            <div class="feature-card">
                <h3>Autonomous Guidance</h3>
                <p>Receive immediate, highly-tailored fertilizer dosages and crop cycle recommendations optimized
                    specifically for your exact environment.</p>
            </div>
        </div>
    </section>

    <!-- Contact & Query Section (Split Layout) -->
    <section id="contact" class="contact-section">
        <h2 class="section-title">Inquiries & Support</h2>
        <div class="contact-container">

            <!-- Left Side: Contact Details -->
            <div class="contact-details">
                <h3>Get in Touch</h3>
                <p><strong>📍 Address:</strong><br>Smart Soil Production ,<br>Rajkot , 360001</p>
                <p><strong>📞 Phone:</strong><br>+91 9104219230</p>
                <p><strong>✉️ Email:</strong><br>darshanbhanderi005@gmail.com</p>
                <p><strong>🕒 Business Hours:</strong><br>Mon - Fri, 9:00 AM - 6:00 PM</p>
            </div>

            <div class="contact-form-wrapper">
                <?php if ($query_msg): ?>
                    <div
                        style="background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:600; border:1px solid #a5d6a7;">
                        ✓ <?php echo htmlspecialchars($query_msg); ?>
                    </div>
                <?php endif; ?>
                <form action="#contact" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label for="email">Business Email</label>
                        <input type="email" id="email" name="email" class="form-control" required
                            placeholder="support@smartsoi.com">
                    </div>
                    <div class="form-group">
                        <label for="query">Detailed Query</label>
                        <textarea id="query" name="query" class="form-control"
                            placeholder="How can our hardware solutions elevate your agricultural output?"
                            required></textarea>
                    </div>
                    <button type="submit" name="submit_query" class="submit-btn">Submit Request</button>
                </form>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer>
        &copy; 2026 <strong>Smart Soil Tester </strong> |Smart Soil Agricultural Solutions. All Rights Reserved.
    </footer>

</body>

</html>