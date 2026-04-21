<?php
session_start();
include "db.php";
if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start();
}
$login_error = '';
$register_error = '';
$register_success = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Save role in session

        if ($user['role'] === 'admin') {
            header("Location: admin_panel.php");
        } else {
            // Check if user has preferences set
            $prefCheck = mysqli_query($conn, "SELECT id FROM farmer_preferences WHERE user_id=".$user['id']);
            if ($prefCheck && mysqli_num_rows($prefCheck) > 0) {
                header("Location: dashboard.php");
            } else {
                header("Location: crop_selection.php");
            }
        }
        exit();
    } else {
        $login_error = 'Invalid username or password.';
    }
}

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, trim($_POST['reg_username']));
    $password = mysqli_real_escape_string($conn, trim($_POST['reg_password']));
    $confirm = $_POST['reg_confirm_password'];
    if (empty($username) || empty($password)) {
        $register_error = 'Please fill all fields.';
    } elseif ($password !== $confirm) {
        $register_error = 'Passwords do not match.';
    } else {
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            $register_error = 'Username already exists.';
        } else {
            if (mysqli_query($conn, "INSERT INTO users (username, password) VALUES ('$username', '$password')")) {
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
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
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
            background: linear-gradient(135deg, rgba(10, 74, 60, 0.85) 0%, rgba(242, 183, 89, 0.8) 100%), url('images/farm_bg.png') no-repeat center center fixed;
            background-size: cover;
            background-attachment: fixed;
        }

        .auth-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            max-width: 950px;
            background: rgba(255, 255, 255, 0.15); /* Transparency */
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .auth-form {
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.85); /* Semi-transparent white */
        }

        .auth-side {
            background: linear-gradient(145deg, rgba(10, 74, 60, 0.75), rgba(23, 120, 100, 0.75));
            color: var(--text-light);
            padding: 52px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .auth-side-icon {
            font-size: 4.5rem;
            margin-bottom: 24px;
            display: block;
        }

        .auth-side h3 {
            font-size: 1.7rem;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .auth-side p {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.7;
            margin-bottom: 30px;
            max-width: 260px;
        }

        .check-list {
            width: 100%;
        }

        .check-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.85);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .check-list li:last-child {
            border-bottom: none;
        }

        .ck {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(228, 221, 211, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            color: #E4DDD3;
            flex-shrink: 0;
            border: 1px solid rgba(228, 221, 211, 0.5);
        }

        /* Form styles */
        .form-title {
            margin-bottom: 28px;
        }

        .form-title h2 {
            font-size: 1.7rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .form-title p {
            font-size: 0.9rem;
            color: var(--secondary);
        }

        .fg {
            margin-bottom: 18px;
        }

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

        .fg input,
        .fg textarea,
        .fg select {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            color: var(--primary);
            background: rgba(255, 255, 255, 0.6);
            transition: all 0.25s;
        }

        .fg input:focus,
        .fg textarea:focus,
        .fg select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(242, 183, 89, 0.25);
            background: #ffffff;
        }

        .fg textarea {
            min-height: 110px;
            resize: vertical;
        }

        .auth-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #0A4A3C, #177864);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.25s ease;
            margin-top: 8px;
            box-shadow: 0 6px 15px rgba(10, 74, 60, 0.3);
        }

        .auth-submit:hover {
            background: linear-gradient(135deg, #0d5e4d, #0A4A3C);
            box-shadow: 0 10px 25px rgba(10, 74, 60, 0.4);
            transform: translateY(-2px);
        }

        .toggle-text {
            text-align: center;
            margin-top: 20px;
            font-size: 0.88rem;
            color: var(--secondary);
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .toggle-text a {
            color: var(--accent-hover);
            font-weight: 700;
            cursor: pointer;
        }
        
        .admin-link {
            color: var(--primary) !important;
            text-decoration: underline;
        }

        /* Panel toggle animations */
        .panel {
            display: none;
        }

        .panel.active {
            display: block;
            animation: fadeUp 0.3s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

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
            background: rgba(242, 183, 89, 0.15);
            border: 1px solid rgba(242, 183, 89, 0.4);
            color: var(--primary);
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 18px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-box {
                grid-template-columns: 1fr;
            }

            .auth-side {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="auth-wrap">
        <div class="auth-box">

            <!-- Left: Forms -->
            <div class="auth-form">

                <!-- Login Panel -->
                <div id="loginSection"
                    class="panel <?php echo isset($_POST['register']) && empty($register_success) ? '' : 'active'; ?>">
                    <div class="form-title">
                        <h2>Welcome Back</h2>
                        <p>Sign in to continue to your agricultural dashboard.</p>
                    </div>
                    <?php if ($login_error): ?>
                        <div class="msg-err"><?php echo htmlspecialchars($login_error); ?></div><?php endif; ?>
                    <?php if ($register_success): ?>
                        <div class="msg-ok"><?php echo htmlspecialchars($register_success); ?></div><?php endif; ?>
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
                    <div class="toggle-text">
                        <span>Don't have an account? <a onclick="showPanel('register')">Create one</a></span>
                        <span><a onclick="showPanel('admin')" class="admin-link">Switch to Admin Login</a></span>
                    </div>
                </div>

                <!-- Register Panel -->
                <div id="registerSection"
                    class="panel <?php echo isset($_POST['register']) && empty($register_success) ? 'active' : ''; ?>">
                    <div class="form-title">
                        <h2>Create Account</h2>
                        <p>Join us and start monitoring your soil health today.</p>
                    </div>
                    <?php if ($register_error): ?>
                        <div class="msg-err"><?php echo htmlspecialchars($register_error); ?></div><?php endif; ?>
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
                    <div class="toggle-text">
                        <span>Already have an account? <a onclick="showPanel('login')">Sign in</a></span>
                    </div>
                </div>

                <!-- Admin Login Panel -->
                <div id="adminSection" class="panel">
                    <div class="form-title">
                        <h2>Admin Portal</h2>
                        <p>Authorized access only.</p>
                    </div>
                    <?php if ($login_error): ?>
                        <div class="msg-err"><?php echo htmlspecialchars($login_error); ?></div><?php endif; ?>
                    <form method="POST">
                        <div class="fg">
                            <label>Admin Username</label>
                            <input type="text" name="username" placeholder="admin" required>
                        </div>
                        <div class="fg">
                            <label>Admin Password</label>
                            <input type="password" name="password" placeholder="••••••••" required>
                        </div>
                        <button type="submit" name="login" class="auth-submit" style="background: linear-gradient(135deg, #1f6153, #0d5e4d);">Admin Access &rarr;</button>
                    </form>
                    <div class="toggle-text">
                        <span><a onclick="showPanel('login')">&larr; Back to Farmer Login</a></span>
                    </div>
                </div>

            </div>

            <!-- Right: Info Side -->
            <div class="auth-side">
                <span class="auth-side-icon">SS</span>
                <h3>Smart Agriculture Engine</h3>
                <p>Monitor Nitrogen, Phosphorus &amp; Potassium levels and receive intelligent real-time crop
                    recommendations tailored to your field.</p>
                <ul class="check-list">

                    <li>
                        <div class="ck">✓</div> 24/7 Field Monitoring
                    </li>
                    <li>
                        <div class="ck">✓</div> NPK AI Predictive Model
                    </li>
                    <li>
                        <div class="ck">✓</div> Crop &amp; Fertilizer Guidance
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <script>
        function showPanel(target) {
            document.getElementById('loginSection').classList.remove('active');
            document.getElementById('registerSection').classList.remove('active');
            if(document.getElementById('adminSection')) {
                document.getElementById('adminSection').classList.remove('active');
            }
            document.getElementById(target + 'Section').classList.add('active');
        }
    </script>
</body>

</html>