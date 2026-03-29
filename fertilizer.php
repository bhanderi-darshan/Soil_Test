<?php
session_start();

$soil_name = $_SESSION['soil_type'] ?? 'Unknown Soil';
$fertilizer = array();

// Example fertilizer logic
if($soil_name == "Black Soil") {
    $fertilizer[] = "Apply MOP (Potassium deficiency)";
} else {
    $fertilizer[] = "Basic Compost";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Fertilizer Recommendation</title>
<style>
body{font-family:Arial;background:#f1f8e9;text-align:center;}
.container{width:60%;margin:auto;margin-top:50px;background:white;padding:30px;border-radius:15px;box-shadow:0px 4px 12px rgba(0,0,0,0.2);}
h2{color:#2e7d32;}
.card{margin:15px;padding:15px;background:#e8f5e9;border-radius:10px;font-size:18px;}
button{margin-top:25px;padding:12px 25px;background:#2e7d32;color:white;border:none;border-radius:8px;cursor:pointer;}
button:hover{background:#1b5e20;}
</style>
</head>
<body>
<div class="container">
<h2>Fertilizer Recommendation 🌾</h2>
<h3>Selected Soil Type:</h3>
<p><b><?php echo htmlspecialchars($soil_name); ?></b></p>

<h3>Recommended Fertilizers:</h3>
<?php
if(empty($fertilizer)){
    echo "<div class='card'>Soil nutrients are sufficient ✅</div>";
}else{
    foreach($fertilizer as $f){
        echo "<div class='card'>".htmlspecialchars($f)."</div>";
    }
}
?>
<br>
<a href="crop.php"><button>Next → Crop Recommendation</button></a>
</div>
</body>
</html>