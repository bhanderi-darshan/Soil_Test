<?php
session_start();
include "db.php";

// Check if user has selected soil, otherwise redirect
if(!isset($_SESSION['soil_type'])){
    header("Location: soil_select.php");
    exit();
}

// selected soil type
$soil_type = $_SESSION['soil_type'];

// get latest predicted npk
$npk_query = "SELECT * FROM predicted_npk ORDER BY id DESC LIMIT 1";
$npk_result = mysqli_query($conn,$npk_query);
$npk = mysqli_fetch_assoc($npk_result) ?: ['nitrogen'=>'0','phosphorus'=>'0','potassium'=>'0'];

$N = $npk['nitrogen'];
$P = $npk['phosphorus'];
$K = $npk['potassium'];

// call python model securely
$command = "python crop_predict.py " . escapeshellarg($N) . " " . escapeshellarg($P) . " " . escapeshellarg($K) . " " . escapeshellarg($soil_type) . " 2>&1";
$output = shell_exec($command);
$crop = trim((string)$output);
if(empty($crop)){
    $crop = "Model Unreachable";
}

?>
<!DOCTYPE html>
<html>

<head>

<title>Crop Recommendation</title>

<style>

body{
font-family:Arial;
background:#f1f8e9;
text-align:center;
}

.container{

width:60%;
margin:auto;
margin-top:60px;

background:white;
padding:30px;

border-radius:15px;

box-shadow:0px 4px 12px rgba(0,0,0,0.2);

}

h2{
color:#2e7d32;
}

.crop-box{

margin-top:25px;

padding:20px;

background:#e8f5e9;

border-radius:12px;

font-size:22px;

}

</style>

</head>

<body>

<div class="container">

<h2>Recommended Crop 🌽</h2>

<p>Based on soil nutrients and soil type</p>

<div class="crop-box">

Best Crop: <b><?php echo $crop; ?></b>

</div>

</div>

</body>

</html>