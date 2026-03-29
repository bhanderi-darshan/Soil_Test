<?php

$conn = new mysqli("localhost","root","","soil_project");

if($conn->connect_error){
    die("Connection failed");
}

?>