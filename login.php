<?php
session_start();
include "db.php";

if(isset($_POST['login'])){

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

$query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
$result = mysqli_query($conn,$query);

if(mysqli_num_rows($result)>0){

$_SESSION['username']=$username;
header("Location: soil_select.php");

}else{

echo "<script>alert('Invalid Username or Password')</script>";

}

}
?>


<!DOCTYPE html>
<html>

<head>

<title>Login - Agriculture System</title>

<style>

body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    /* Beautiful green farm image with a dark green semi-transparent overlay for contrast */
    background: linear-gradient(rgba(35, 90, 40, 0.75), rgba(20, 70, 25, 0.85)), 
                url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed;
    position: relative;
    overflow: hidden;
}

/* HEADER TEXT ABOVE */
.header-text {
    text-align: center;
    margin-bottom: 25px;
    z-index: 2;
    max-width: 480px;
    padding: 0 20px;
}

.header-text h1 {
    font-size: 48px;
    margin: 0 0 10px 0;
    color: #B5E8A4; /* Light green header */
    font-weight: 800;
}

.header-text p {
    font-size: 17px;
    color: #A3D39B;
    line-height: 1.5;
    margin: 0;
    font-weight: 500;
}

/* MAIN BOX */
.login-box {
    background: white;
    padding: 35px 40px;
    width: 100%;
    max-width: 400px;
    border-radius: 25px;
    box-shadow: 0px 10px 30px rgba(0,0,0,0.08); /* Very soft shadow */
    text-align: left; /* Left align everything */
    z-index: 2;
    box-sizing: border-box;
    margin: 0 20px;
}

.login-box h2 {
    font-size: 26px;
    color: #111;
    margin: 0;
    font-weight: 800;
}

.subtitle {
    color: #9AB499;
    font-size: 16px;
    margin-top: 6px;
    margin-bottom: 25px;
    font-weight: 500;
}

/* INPUT LABEL */
label {
    display: block;
    margin-top: 18px;
    font-weight: 700;
    color: #556254;
    font-size: 12px;
    letter-spacing: 0.5px;
}

/* INPUT BOX */
input {
    width: 100%;
    box-sizing: border-box;
    padding: 16px 18px;
    margin-top: 8px;
    border-radius: 12px;
    border: 2px solid #E2F0E0;
    background: #F4FBF3;
    font-size: 16px;
    color: #4A8B41;
    outline: none;
    transition: 0.3s;
}

input::placeholder {
    color: #9EBD9B;
}

input:focus {
    border-color: #B2D8AF;
}

/* BUTTON */
button {
    margin-top: 25px;
    width: 100%;
    padding: 18px;
    background: #3E7A2C;
    color: white;
    font-weight: bold;
    border: none;
    border-radius: 12px;
    font-size: 18px;
    cursor: pointer;
    transition: background 0.3s;
}

button:hover {
    background: #316422;
}

</style>
</head>
<body>

<div class="header-text">
    <h1>Agriculture</h1>
    <p>Test your soil, predict NPK values, and get fertilizer & crop recommendations — all in one place.</p>
</div>

<div class="login-box">
    <h2>Sign In</h2>
    <p class="subtitle">Enter your farmer account details</p>

    <form method="POST">
        <label>USERNAME / DEVICE ID</label>
        <input type="text" name="username" placeholder="e.g. farmer_01 or ESP32-A4B2" required>
        
        <label>PASSWORD</label>
        <input type="password" name="password" placeholder="••••••••" required>
        
        <button name="login">Get Started &rarr;</button>
    </form>
</div>

</body>
</html>