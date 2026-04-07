<?php
session_start();
include "db.php";

if(isset($_SESSION['user_id'])) {
    header("Location: soil_select.php");
    exit();
}

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Agriculture System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-panel">
        <div class="container login-box">
            <div class="header-text">
                <h1>Smart Soil Login</h1>
                <p>Sign in to choose soil type and view dashboard.</p>
            </div>
            <?php if(!empty($login_error)): ?>
                <div class="card" style="border: 1px solid #d36a6a; color: #942d2d; background:#ffeaea; margin-bottom:15px;"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <label>Username</label>
                <input type="text" name="username" placeholder="farmer_01" required>

                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>

                <button type="submit" name="login" class="btn">Login</button>
            </form>
            <p style="margin-top: 15px; color:#355930;">No account? Add a user in the DB table `users` and then login.</p>
        </div>
    </div>
</body>
</html>
