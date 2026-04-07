<?php
session_start();
include "db.php";
if(isset($_SESSION['user_id'])) { header("Location: soil_select.php"); exit(); }
$login_error = '';
$register_error = '';
$register_success = '';

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: soil_select.php");
        exit();
    } else {
        $login_error = 'Invalid username or password.';
    }
}

if(isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['reg_username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['reg_password']));
    $confirm  = $_POST['reg_confirm_password'];
    if(empty($username)||empty($password)) {
        $register_error = 'Please fill all fields.';
    } elseif($password !== $confirm) {
        $register_error = 'Passwords do not match.';
    } else {
        $check = mysqli_query($conn,"SELECT id FROM users WHERE username='$username'");
        if(mysqli_num_rows($check)>0) {
            $register_error = 'Username already exists.';
        } else {
            if(mysqli_query($conn,"INSERT INTO users (username, password) VALUES ('$username', '$password')")) {
                $register_success = 'Registration successful! Please sign in.';
            } else {
                $register_error = 'Error registering user.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SmartSoil Analyzer</title>
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

        /* Auth Wrap */
        .auth-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: linear-gradient(rgba(20, 40, 20, 0.65), rgba(40, 50, 30, 0.8)), 
                        url('images/hero_bg.png') no-repeat center center / cover;
            background-attachment: fixed;
        }

        .auth-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 950px;
            background: var(--card-bg);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.35);
        }

        .auth-form { padding: 52px 44px; display: flex; flex-direction: column; justify-content: center; }

        .auth-side {
            background: linear-gradient(145deg, var(--primary), #1a251b);
            color: var(--text-light);
            padding: 52px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .auth-side-icon { font-size: 4.5rem; margin-bottom: 24px; display: block; }
        .auth-side h3 { font-size: 1.7rem; font-weight: 800; margin-bottom: 14px; }
        .auth-side p { font-size: 0.95rem; color: rgba(255,255,255,0.8); line-height: 1.7; margin-bottom: 30px; max-width: 260px; }

        .check-list { width: 100%; }
        .check-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.85);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .check-list li:last-child { border-bottom: none; }
        .ck {
            width: 22px; height: 22px;
            border-radius: 50%;
            background: rgba(76, 175, 80, 0.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.65rem;
            color: #a5d6a7;
            flex-shrink: 0;
            border: 1px solid rgba(76,175,80,0.5);
        }

        /* Form styles */
        .form-title { margin-bottom: 28px; }
        .form-title h2 { font-size: 1.7rem; font-weight: 800; color: var(--primary); margin-bottom: 5px; }
        .form-title p { font-size: 0.9rem; color: var(--secondary); }

        .fg { margin-bottom: 18px; }
        .fg label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--accent-hover);
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Outfit', sans-serif;
        }

        .fg input, .fg textarea, .fg select {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            color: var(--primary);
            background: var(--bg-light);
            transition: all 0.25s;
        }

        .fg input:focus, .fg textarea:focus, .fg select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.15);
            background: var(--card-bg);
        }

        .fg textarea { min-height: 110px; resize: vertical; }

        .auth-submit {
            width: 100%;
            padding: 15px;
            background: var(--accent);
            color: var(--text-light);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.25s ease;
            margin-top: 8px;
        }
        .auth-submit:hover { background: var(--accent-hover); box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4); transform: translateY(-2px); }

        .toggle-text { text-align: center; margin-top: 20px; font-size: 0.88rem; color: var(--secondary); }
        .toggle-text a { color: var(--accent-hover); font-weight: 700; cursor: pointer; }

        /* Panel toggle animations */
        .panel { display: none; }
        .panel.active { display: block; animation: fadeUp 0.3s ease; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

        /* Alerts */
        .msg-err {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            color: #c62828;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .msg-ok {
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            color: #2e7d32;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 18px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-box { grid-template-columns: 1fr; }
            .auth-side { display: none; }
        }
    </style>
</head>
<body>

<div class="auth-wrap">
    <div class="auth-box">

        <!-- Left: Forms -->
        <div class="auth-form">

            <!-- Login Panel -->
            <div id="loginSection" class="panel <?php echo isset($_POST['register']) && empty($register_success) ? '' : 'active'; ?>">
                <div class="form-title">
                    <h2>Welcome Back</h2>
                    <p>Sign in to continue to your agricultural dashboard.</p>
                </div>
                <?php if($login_error): ?><div class="msg-err"><?php echo htmlspecialchars($login_error); ?></div><?php endif; ?>
                <?php if($register_success): ?><div class="msg-ok"><?php echo htmlspecialchars($register_success); ?></div><?php endif; ?>
                <form method="POST">
                    <div class="fg">
                        <label>Username</label>
                        <input type="text" name="username" placeholder="e.g. farmer_01" required>
                    </div>
                    <div class="fg">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" name="login" class="auth-submit">Secure Login &rarr;</button>
                </form>
                <p class="toggle-text">Don't have an account? <a onclick="showPanel('register')">Create one</a></p>
            </div>

            <!-- Register Panel -->
            <div id="registerSection" class="panel <?php echo isset($_POST['register']) && empty($register_success) ? 'active' : ''; ?>">
                <div class="form-title">
                    <h2>Create Account</h2>
                    <p>Join us and start monitoring your soil health today.</p>
                </div>
                <?php if($register_error): ?><div class="msg-err"><?php echo htmlspecialchars($register_error); ?></div><?php endif; ?>
                <form method="POST">
                    <div class="fg">
                        <label>Username</label>
                        <input type="text" name="reg_username" placeholder="Choose a username" required>
                    </div>
                    <div class="fg">
                        <label>Password</label>
                        <input type="password" name="reg_password" placeholder="Create password" required>
                    </div>
                    <div class="fg">
                        <label>Confirm Password</label>
                        <input type="password" name="reg_confirm_password" placeholder="Repeat password" required>
                    </div>
                    <button type="submit" name="register" class="auth-submit">Register Now &rarr;</button>
                </form>
                <p class="toggle-text">Already have an account? <a onclick="showPanel('login')">Sign in</a></p>
            </div>

        </div>

        <!-- Right: Info Side -->
        <div class="auth-side">
            <span class="auth-side-icon">🌱</span>
            <h3>Smart Agriculture Engine</h3>
            <p>Monitor Nitrogen, Phosphorus &amp; Potassium levels and receive intelligent real-time crop recommendations tailored to your field.</p>
            <ul class="check-list">
                <li><div class="ck">✓</div> 99% Prediction Accuracy</li>
                <li><div class="ck">✓</div> 24/7 Field Monitoring</li>
                <li><div class="ck">✓</div> NPK AI Predictive Model</li>
                <li><div class="ck">✓</div> Crop &amp; Fertilizer Guidance</li>
            </ul>
        </div>

    </div>
</div>

<script>
function showPanel(target) {
    document.getElementById('loginSection').classList.remove('active');
    document.getElementById('registerSection').classList.remove('active');
    document.getElementById(target + 'Section').classList.add('active');
}
</script>
</body>
</html>
