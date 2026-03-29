<?php

include "db.php";

$ec = mysqli_real_escape_string($conn, $_GET['ec'] ?? '0');
$moisture = mysqli_real_escape_string($conn, $_GET['moisture'] ?? '0');
$temp = mysqli_real_escape_string($conn, $_GET['temp'] ?? '0');

// store sensor values

$sql = "INSERT INTO sensor_data (ec, moisture, temperature) VALUES ('$ec','$moisture','$temp')";
$conn->query($sql);


// run python model automatically
$cmd = escapeshellcmd("python predict.py");
exec($cmd . " > /dev/null 2>&1 &"); // Run in background to prevent hanging PHP

echo "Sensor Data Saved + Model Executed";

?>