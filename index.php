<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Soil Analyzer</title>
    <!-- Modern, professional typography -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
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

        .nav-links .nav-cta {
            background: var(--accent);
            color: var(--text-light) !important;
            padding: 9px 22px;
            border-radius: 6px;
            font-weight: 700;
            box-shadow: 0 3px 12px rgba(76, 175, 80, 0.3);
        }
        .nav-links .nav-cta:hover {
            background: var(--accent-hover) !important;
            color: var(--text-light) !important;
            transform: translateY(-1px);
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(20, 40, 20, 0.4), rgba(40, 50, 30, 0.7)), 
                        url('images/hero_bg.png') no-repeat center center/cover;
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
            background-color: var(--accent);
            color: var(--text-light);
            padding: 18px 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.3s ease;
            border: none;
            border-radius: 4px;
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }

        .hero .btn:hover {
            background-color: var(--accent-hover);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(76, 175, 80, 0.6);
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
            background-color: var(--accent);
            display: block;
            margin: 15px auto 0;
            border-radius: 2px;
        }

        /* Product Section */
        .product-section {
            background-color: var(--bg-light);
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
            background-color: var(--bg-alt);
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
            border-radius: 8px;
            border-top: 4px solid var(--accent);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(44, 62, 45, 0.08);
            border-top-color: var(--accent-warm);
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
            background-color: var(--bg-light);
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
            box-shadow: 0 15px 35px rgba(44, 62, 45, 0.05);
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
            box-shadow: 0 10px 30px rgba(44, 62, 45, 0.05);
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
            border: 1px solid #ced4da;
            background-color: #fafbfc;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
            border-radius: 4px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
            background-color: #ffffff;
        }

        textarea.form-control {
            resize: vertical;
            height: 160px;
        }

        .submit-btn {
            background-color: var(--accent-warm);
            color: var(--primary);
            border: none;
            padding: 18px 30px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            border-radius: 4px;
            font-family: 'Outfit', sans-serif;
        }

        .submit-btn:hover {
            background-color: #ffa000;
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            background-color: var(--primary);
            color: #a3b8a5;
            text-align: center;
            padding: 40px 20px;
            font-size: 0.95rem;
        }

        footer strong {
            color: var(--text-light);
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
            .contact-details, .contact-form-wrapper {
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
            <a href="#home">Home</a>
            <a href="#contact">Contact</a>
            <a href="login.php">Login</a>
            <a href="dashboard.php">Profile</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Precision Agriculture Intelligence</h1>
            <p>A sophisticated field monitoring system. Monitor your soil health seamlessly and optimize your yields with real-time sensor data and professional guidance.</p>
            <a href="#product" class="btn">Discover the Hardware</a>
        </div>
    </section>

    <!-- Product Details Section -->
    <section id="product" class="product-section">
        <div class="product-container">
            <div class="product-text">
                <h2>Product Overview</h2>
                <p>The Smart Soil Analyzer is a state-of-the-art IoT device designed to provide farmers and commercial agriculture teams with comprehensive, real-time insights into field conditions.</p>
                <p>Equipped with advanced multi-parameter sensors, the device continuously monitors moisture levels, electrical conductivity (EC), and ambient temperature, automatically transmitting robust datasets to our cloud infrastructure for deep analysis.</p>
                <p><strong>Industrial Durability:</strong> Engineered to withstand harsh agricultural environments, ensuring reliable performance across all seasons.</p>
            </div>
            <div class="product-image">
                <!-- Using the existing product.png from the images directory -->
                <img src="images/product.png" alt="Smart Soil Analyzer Hardware" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
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
                <p>High-precision physical sensors measure EC, moisture, and temperature directly from your soil 24/7 with virtually zero latency.</p>
            </div>
            <div class="feature-card">
                <h3>AI NPK Prediction</h3>
                <p>Instantly predict Nitrogen, Phosphorus, and Potassium levels through highly sophisticated machine learning models based on local data.</p>
            </div>
            <div class="feature-card">
                <h3>Autonomous Guidance</h3>
                <p>Receive immediate, highly-tailored fertilizer dosages and crop cycle recommendations optimized specifically for your exact environment.</p>
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
                <p><strong>📍 Address:</strong><br>123 Agriculture Tech Park,<br>Smart City, 10001</p>
                <p><strong>📞 Phone:</strong><br>+1 (555) 123-4567</p>
                <p><strong>✉️ Email:</strong><br>support@smartsoil.com</p>
                <p><strong>🕒 Business Hours:</strong><br>Mon - Fri, 9:00 AM - 6:00 PM</p>
            </div>

            <!-- Right Side: Contact Form -->
            <div class="contact-form-wrapper">
                <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Your query has been successfully submitted. We will contact you soon.');">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" class="form-control" required placeholder="Jane Doe">
                    </div>
                    <div class="form-group">
                        <label for="email">Business Email</label>
                        <input type="email" id="email" class="form-control" required placeholder="jane@farm-tech.com">
                    </div>
                    <div class="form-group">
                        <label for="query">Detailed Query</label>
                        <textarea id="query" class="form-control" placeholder="How can our hardware solutions elevate your agricultural output?" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Submit Request</button>
                </form>
            </div>
            
        </div>
    </section>

    <!-- Footer -->
    <footer>
        &copy; 2026 <strong>Smart Soil Analyzer</strong> | Next-Gen Agricultural Solutions. All Rights Reserved.
    </footer>

</body>
</html>