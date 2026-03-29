<?php
session_start();

if(isset($_GET['soil'])){
    $_SESSION['soil_type'] = $_GET['soil'];
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Select Soil Type</title>
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
    max-width: 1100px;
    background: rgba(255, 255, 255, 0.15); /* Transparent glass */
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 40px;
    border-radius: 25px;
    box-shadow: 0px 10px 40px rgba(0,0,0,0.2);
    text-align: center;
    box-sizing: border-box;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 30px;
    transition: transform 0.3s ease, background 0.3s ease, box-shadow 0.3s ease;
}

.container:hover {
    background: rgba(255, 255, 255, 0.22);
    transform: translateY(-3px);
    box-shadow: 0px 15px 50px rgba(0,0,0,0.3);
}

.card {
    background: #F4FBF3;
    width: 220px;
    padding: 18px;
    border-radius: 18px;
    border: 2px solid #E2F0E0;
    box-shadow: 0px 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(74, 139, 65, 0.25);
}

.card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.card h3 {
    margin: 0 0 20px 0;
    color: #2A8139;
    font-size: 20px;
    font-weight: 800;
}

.card a {
    text-decoration: none;
    margin-top: auto;
}

button {
    width: 100%;
    padding: 12px;
    background: #3E7A2C;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}

button:hover {
    background: #2e5c20;
}

</style>
</head>
<body>

<div class="header-text">
    <h1>Select Soil Type 🌱</h1>
    <p style="color: #A3D39B; font-size: 17px; margin: 0; font-weight: 500;">Choose your field's soil profile to calibrate the predictive model</p>
</div>

<div class="container">

<!-- BLACK SOIL -->
<div class="card">
    <img src="images/black.jpg" alt="Black Soil">
    <h3>Black Soil</h3>
    <a href="?soil=Black Soil"><button>Select</button></a>
</div>

<!-- RED SOIL -->
<div class="card">
    <img src="images/red.jpg" alt="Red Soil">
    <h3>Red Soil</h3>
    <a href="?soil=Red Soil"><button>Select</button></a>
</div>

<!-- ALLUVIAL SOIL -->
<div class="card">
    <img src="images/alluvial.jpg" alt="Alluvial Soil">
    <h3>Alluvial Soil</h3>
    <a href="?soil=Alluvial Soil"><button>Select</button></a>
</div>

<!-- SANDY SOIL -->
<div class="card">
    <img src="images/sandy.jpg" alt="Sandy Soil">
    <h3>Sandy Soil</h3>
    <a href="?soil=Sandy Soil"><button>Select</button></a>
</div>

<!-- LATERITE SOIL -->
<div class="card">
    <img src="images/laterite.jpg" alt="Laterite Soil">
    <h3>Laterite Soil</h3>
    <a href="?soil=Laterite Soil"><button>Select</button></a>
</div>

</div>

</body>
</html>