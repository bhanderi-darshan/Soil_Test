<?php

$conn = new mysqli("localhost", "root", "", "soil_project");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (
    isset($_GET['device']) &&
    isset($_GET['moisture']) &&
    isset($_GET['ec']) &&
    isset($_GET['temp'])
) {

    $device = $_GET['device'];
    $moisture = $_GET['moisture'];
    $ec = $_GET['ec'];
    $temp = $_GET['temp'];

    $sql = "INSERT INTO sensor_data (device_id, moisture, ec, temperature)
            VALUES ('$device', '$moisture', '$ec', '$temp')";

    if ($conn->query($sql) === TRUE) {
        echo "SUCCESS";
    } else {
        echo "ERROR: " . $conn->error;
    }

} else {

    echo "Missing parameters";

}

$conn->close();

?>