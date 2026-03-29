<?php
include "db.php";

// get latest sensor data
$sensor_query = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1";
$sensor_result = mysqli_query($conn, $sensor_query);
$sensor = mysqli_fetch_assoc($sensor_result) ?: ['ec'=>'0','moisture'=>'0','temperature'=>'0'];

// get latest predicted npk
$npk_query = "SELECT * FROM predicted_npk ORDER BY id DESC LIMIT 1";
$npk_result = mysqli_query($conn, $npk_query);
$npk = mysqli_fetch_assoc($npk_result) ?: ['nitrogen'=>'0','phosphorus'=>'0','potassium'=>'0'];

// default selected soil (temporary)
$soil_query = "SELECT * FROM soil_types WHERE soil_name='Black Soil'";
$soil_result = mysqli_query($conn, $soil_query);
$soil = mysqli_fetch_assoc($soil_result) ?: ['standard_n'=>'0','standard_p'=>'0','standard_k'=>'0'];

?>

<!DOCTYPE html>
<html>
<head>
<title>Soil Dashboard</title>

<style>

body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: linear-gradient(rgba(35, 90, 40, 0.75), rgba(20, 70, 25, 0.85)), 
                url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed;
    position: relative;
    padding: 50px 20px;
    box-sizing: border-box;
}

.header-text {
    text-align: center;
    margin-bottom: 25px;
    z-index: 2;
    color: white;
}

.header-text h1 {
    font-size: 42px;
    margin: 0 0 10px 0;
    color: #B5E8A4;
    font-weight: 800;
}

.container {
    width: 95%;
    max-width: 1000px; /* Made it longer/wider */
    background: rgba(255, 255, 255, 0.15); /* Transparent */
    backdrop-filter: blur(12px); /* Glassmorphism effect */
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.3); /* Glass edge */
    padding: 40px;
    border-radius: 25px;
    box-shadow: 0px 10px 40px rgba(0,0,0,0.2);
    text-align: center;
    box-sizing: border-box;
    transition: transform 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
}

.container:hover {
    background: rgba(255, 255, 255, 0.22); /* Interactive hover */
    transform: translateY(-3px);
    box-shadow: 0px 15px 50px rgba(0,0,0,0.3);
}

h3 {
    text-align: left;
    color: #fff; /* White to contrast transparent background */
    font-size: 22px;
    font-weight: 800;
    margin-top: 35px;
    margin-bottom: 15px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    padding-bottom: 10px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5); /* Increase readability */
}

h3:first-child {
    margin-top: 0;
}

.grid-row {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
}

.card {
    flex: 1;
    min-width: 200px;
    padding: 22px;
    background: #F4FBF3; /* Lightest pastoral green */
    border-radius: 15px;
    border: 2px solid #E2F0E0;
    color: #4A8B41;
    text-align: left;
    box-sizing: border-box;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(74, 139, 65, 0.12);
}

.card-label {
    display: block;
    font-weight: 700;
    color: #556254;
    font-size: 13px;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    text-transform: uppercase;
}

.card b {
    font-size: 32px;
    color: #3E7A2C;
}

</style>
</head>
<body>

<div class="header-text">
    <h1>Smart Soil Dashboard 🌱</h1>
    <p style="color: #A3D39B; font-size: 17px; margin: 0; font-weight: 500;">Live sensor tracking, AI predictions, and local soil standards</p>
</div>

<div class="container">

    <h3>Sensor Data</h3>
    <div class="grid-row">
        <div class="card">
            <span class="card-label">EC</span>
            <b><?php echo $sensor['ec']; ?></b>
        </div>
        <div class="card">
            <span class="card-label">Moisture</span>
            <b><?php echo $sensor['moisture']; ?></b>
        </div>
        <div class="card">
            <span class="card-label">Temperature</span>
            <b><?php echo $sensor['temperature']; ?>°C</b>
        </div>
    </div>

    <h3>Predicted NPK Values</h3>
    <div class="grid-row">
        <div class="card">
            <span class="card-label">Nitrogen</span>
            <b><?php echo $npk['nitrogen']; ?></b>
        </div>
        <div class="card">
            <span class="card-label">Phosphorus</span>
            <b><?php echo $npk['phosphorus']; ?></b>
        </div>
        <div class="card">
            <span class="card-label">Potassium</span>
            <b><?php echo $npk['potassium']; ?></b>
        </div>
    </div>

    <h3>Standard Soil Values (Black Soil)</h3>
    <div class="grid-row">
        <div class="card" style="background: #fafafa; border-color: #eee;">
            <span class="card-label">Standard N</span>
            <b style="color: #555;"><?php echo $soil['standard_n']; ?></b>
        </div>
        <div class="card" style="background: #fafafa; border-color: #eee;">
            <span class="card-label">Standard P</span>
            <b style="color: #555;"><?php echo $soil['standard_p']; ?></b>
        </div>
        <div class="card" style="background: #fafafa; border-color: #eee;">
            <span class="card-label">Standard K</span>
            <b style="color: #555;"><?php echo $soil['standard_k']; ?></b>
        </div>
    </div>

</div>

</body>
</html>