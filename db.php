<?php

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'soil_project';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
if ($conn->connect_error) {
    die('Database server connection failed: ' . $conn->connect_error);
}

if (!$conn->select_db($DB_NAME)) {
    if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")) {
        die('Failed to create database "' . $DB_NAME . '": ' . $conn->error);
    }
    $conn->select_db($DB_NAME);
}

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
)");

$conn->set_charset('utf8mb4');

?>