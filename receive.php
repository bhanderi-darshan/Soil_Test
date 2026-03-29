<?php

include "db.php";

// check required parameters

if (!isset($_GET['device']) || !isset($_GET['moisture']) || !isset($_GET['ec']) || !isset($_GET['temp'])) {
    die("Missing parameters");
}

// get values safely

$device = mysqli_real_escape_string($conn, $_GET['device']);
$moisture = mysqli_real_escape_string($conn, $_GET['moisture']);
$ec = mysqli_real_escape_string($conn, $_GET['ec']);
$temp = mysqli_real_escape_string($conn, $_GET['temp']);


// check if device exists

$checkDevice = "SELECT * FROM devices WHERE device_id='$device'";
$result = $conn->query($checkDevice);

if ($result->num_rows == 0) {
    die("Invalid device ID");
}


// insert sensor data

$sql = "INSERT INTO sensor_data (device_id, ec, moisture, temperature)
        VALUES ('$device','$ec','$moisture','$temp')";

if ($conn->query($sql) === TRUE) {

    // run python prediction model

    $cmd = escapeshellcmd("python predict.py");

    exec($cmd . " > /dev/null 2>&1 &");

    echo "Sensor Data Saved + Model Executed Successfully";

} else {

    echo "Database Insert Error";

}

?>