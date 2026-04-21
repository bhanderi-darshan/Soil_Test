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

$conn->query("CREATE TABLE IF NOT EXISTS queries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Ensure columns exist (for existing tables)
$conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'user'");
$conn->query("ALTER TABLE users ADD COLUMN hardware_id VARCHAR(100) DEFAULT NULL");

// If no admin exists, create a default admin
$adminCheck = $conn->query("SELECT id FROM users WHERE role='admin'");
if ($adminCheck && $adminCheck->num_rows == 0) {
    // default admin username: admin, password: admin_password
    $conn->query("INSERT INTO users (username, password, role) VALUES ('admin', 'admin', 'admin')");
}

$conn->query("CREATE TABLE IF NOT EXISTS farmer_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preferred_crops TEXT NOT NULL,
    prediction_mode VARCHAR(20) DEFAULT 'regular',
    district VARCHAR(100) DEFAULT NULL,
    taluka VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE IF NOT EXISTS location_crop_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    district_name VARCHAR(100),
    taluka_name VARCHAR(100),
    season VARCHAR(20),
    crop_name VARCHAR(100)
)");

// Tailored Taluka Crop Data
$checkCount = $conn->query("SELECT COUNT(*) as total FROM location_crop_mapping")->fetch_assoc()['total'];
if ($checkCount < 100) { 
    $conn->query("TRUNCATE TABLE location_crop_mapping"); // Clear partial/baseline
    
    $fullData = [
        ['Ahmedabad', 'Ahmedabad City', ['Cotton', 'Bajra', 'Groundnut', 'Maize'], ['Wheat', 'Gram', 'Mustard', 'Vegetables'], ['Watermelon', 'Cucumber', 'Fodder'], ['Vegetables', 'Flowers']],
        ['Ahmedabad', 'Daskroi', ['Cotton', 'Bajra', 'Groundnut', 'Maize', 'Sesame'], ['Wheat', 'Gram', 'Mustard', 'Cumin'], ['Watermelon', 'Muskmelon', 'Fodder'], ['Vegetables', 'Banana', 'Papaya']],
        ['Ahmedabad', 'Dholka', ['Rice', 'Cotton', 'Bajra', 'Groundnut'], ['Wheat', 'Gram', 'Mustard', 'Potato'], ['Watermelon', 'Fodder'], ['Vegetables', 'Mango']],
        ['Ahmedabad', 'Dhandhuka', ['Cotton', 'Groundnut', 'Bajra', 'Sesame'], ['Wheat', 'Gram', 'Cumin', 'Mustard'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Ahmedabad', 'Sanand', ['Cotton', 'Bajra', 'Groundnut', 'Maize'], ['Wheat', 'Gram', 'Mustard', 'Fennel'], ['Watermelon', 'Cucumber'], ['Vegetables', 'Flowers']],
        ['Ahmedabad', 'Viramgam', ['Cotton', 'Bajra', 'Groundnut', 'Castor'], ['Wheat', 'Gram', 'Cumin', 'Mustard'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Ahmedabad', 'Bavla', ['Cotton', 'Groundnut', 'Bajra'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Ahmedabad', 'Mandal', ['Bajra', 'Groundnut', 'Castor', 'Cotton'], ['Wheat', 'Gram', 'Cumin'], ['Fodder', 'Watermelon'], ['Vegetables']],
        ['Ahmedabad', 'Detroj-Rampura', ['Bajra', 'Cotton', 'Groundnut'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Vegetables']],
        
        ['Amreli', 'Amreli', ['Groundnut', 'Cotton', 'Bajra', 'Sesame'], ['Wheat', 'Gram', 'Cumin', 'Coriander'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        ['Amreli', 'Babra', ['Groundnut', 'Cotton', 'Bajra', 'Jowar'], ['Wheat', 'Cumin', 'Gram'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Amreli', 'Bagasara', ['Groundnut', 'Bajra', 'Sesame', 'Cotton'], ['Wheat', 'Cumin', 'Gram', 'Coriander'], ['Fodder', 'Watermelon'], ['Vegetables']],
        ['Amreli', 'Dhari', ['Groundnut', 'Cotton', 'Bajra', 'Sesame'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        ['Amreli', 'Jafarabad', ['Groundnut', 'Bajra', 'Cotton'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon'], ['Coconut', 'Vegetables']],
        ['Amreli', 'Khamba', ['Groundnut', 'Bajra', 'Jowar', 'Sesame'], ['Wheat', 'Cumin', 'Gram'], ['Fodder', 'Watermelon'], ['Vegetables']],
        ['Amreli', 'Kunkavav-Vadia', ['Groundnut', 'Cotton', 'Bajra'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Amreli', 'Lathi', ['Groundnut', 'Bajra', 'Cotton', 'Sesame'], ['Wheat', 'Cumin', 'Gram'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Amreli', 'Lilia', ['Groundnut', 'Bajra', 'Sesame', 'Jowar'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Amreli', 'Rajula', ['Groundnut', 'Bajra', 'Cotton', 'Sesame'], ['Wheat', 'Cumin', 'Gram', 'Coriander'], ['Watermelon'], ['Coconut', 'Vegetables']],
        ['Amreli', 'Savarkundla', ['Groundnut', 'Cotton', 'Bajra', 'Castor'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        
        ['Anand', 'Anand', ['Rice', 'Cotton', 'Tobacco', 'Groundnut', 'Maize'], ['Wheat', 'Potato', 'Mustard', 'Gram', 'Vegetables'], ['Watermelon', 'Cucumber', 'Fodder'], ['Banana', 'Papaya', 'Vegetables', 'Flowers']],
        ['Anand', 'Anklav', ['Rice', 'Cotton', 'Tobacco', 'Maize'], ['Wheat', 'Potato', 'Mustard', 'Gram'], ['Watermelon', 'Fodder'], ['Banana', 'Vegetables']],
        ['Anand', 'Borsad', ['Rice', 'Tobacco', 'Cotton', 'Groundnut'], ['Wheat', 'Potato', 'Mustard'], ['Watermelon', 'Cucumber'], ['Banana', 'Papaya', 'Vegetables']],
        ['Anand', 'Khambhat', ['Cotton', 'Groundnut', 'Bajra', 'Sesame'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        ['Anand', 'Petlad', ['Rice', 'Tobacco', 'Cotton', 'Maize'], ['Wheat', 'Potato', 'Mustard', 'Gram'], ['Watermelon', 'Cucumber'], ['Banana', 'Vegetables']],
        ['Anand', 'Sojitra', ['Rice', 'Cotton', 'Tobacco'], ['Wheat', 'Potato', 'Mustard'], ['Watermelon', 'Fodder'], ['Banana', 'Vegetables']],
        ['Anand', 'Tarapur', ['Rice', 'Cotton', 'Groundnut'], ['Wheat', 'Mustard', 'Gram'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Anand', 'Umreth', ['Rice', 'Cotton', 'Tobacco', 'Maize'], ['Wheat', 'Potato', 'Mustard'], ['Watermelon', 'Cucumber'], ['Banana', 'Vegetables']],
        
        ['Banaskantha', 'Amirgadh', ['Bajra', 'Castor', 'Cotton', 'Groundnut', 'Jowar'], ['Wheat', 'Gram', 'Mustard', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Banaskantha', 'Bhabhar', ['Bajra', 'Castor', 'Cotton', 'Jowar'], ['Cumin', 'Mustard', 'Gram', 'Wheat'], ['Fodder', 'Watermelon'], ['Vegetables']],
        ['Banaskantha', 'Danta', ['Maize', 'Bajra', 'Groundnut', 'Jowar'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Banaskantha', 'Dantiwada', ['Bajra', 'Cotton', 'Castor', 'Groundnut'], ['Wheat', 'Cumin', 'Mustard', 'Gram', 'Fennel'], ['Watermelon', 'Fodder'], ['Vegetables', 'Date Palm']],
        ['Banaskantha', 'Deesa', ['Bajra', 'Cotton', 'Castor', 'Groundnut'], ['Potato', 'Cumin', 'Wheat', 'Mustard', 'Fennel', 'Onion'], ['Watermelon', 'Muskmelon'], ['Vegetables', 'Banana']],
        ['Banaskantha', 'Deodar', ['Bajra', 'Cotton', 'Castor', 'Groundnut', 'Jowar'], ['Wheat', 'Cumin', 'Mustard', 'Gram'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Banaskantha', 'Kankrej', ['Bajra', 'Castor', 'Cotton', 'Groundnut'], ['Cumin', 'Wheat', 'Mustard', 'Gram'], ['Watermelon', 'Fodder'], ['Vegetables', 'Date Palm']],
        ['Banaskantha', 'Lakhani', ['Bajra', 'Castor', 'Cotton'], ['Cumin', 'Mustard', 'Wheat'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Banaskantha', 'Palanpur', ['Bajra', 'Cotton', 'Castor', 'Groundnut', 'Maize'], ['Wheat', 'Cumin', 'Fennel', 'Mustard', 'Gram'], ['Watermelon', 'Fodder'], ['Vegetables', 'Banana']],
        ['Banaskantha', 'Suigam', ['Bajra', 'Castor', 'Cotton'], ['Cumin', 'Mustard', 'Gram'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Banaskantha', 'Tharad', ['Bajra', 'Castor', 'Cotton', 'Groundnut'], ['Cumin', 'Mustard', 'Wheat', 'Gram', 'Fennel'], ['Watermelon', 'Fodder'], ['Vegetables', 'Date Palm']],
        ['Banaskantha', 'Vadgam', ['Bajra', 'Cotton', 'Castor', 'Groundnut'], ['Wheat', 'Cumin', 'Mustard', 'Gram'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Banaskantha', 'Vav', ['Bajra', 'Castor', 'Cotton', 'Groundnut'], ['Cumin', 'Wheat', 'Mustard', 'Gram'], ['Watermelon', 'Fodder'], ['Vegetables']],

        ['Bharuch', 'Amod', ['Cotton', 'Groundnut', 'Rice', 'Bajra'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        ['Bharuch', 'Ankleshwar', ['Cotton', 'Groundnut', 'Rice', 'Bajra', 'Sesamum'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        ['Bharuch', 'Bharuch', ['Cotton', 'Groundnut', 'Rice', 'Jowar'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Mango', 'Banana', 'Vegetables']],
        ['Bharuch', 'Hansot', ['Cotton', 'Rice', 'Groundnut', 'Sesamum'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        ['Bharuch', 'Jambusar', ['Cotton', 'Groundnut', 'Rice', 'Bajra', 'Sugarcane'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Mango', 'Banana', 'Vegetables']],
        ['Bharuch', 'Jhagadia', ['Cotton', 'Rice', 'Groundnut', 'Jowar', 'Castor'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        ['Bharuch', 'Netrang', ['Cotton', 'Rice', 'Groundnut', 'Jowar'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        ['Bharuch', 'Vagra', ['Cotton', 'Groundnut', 'Rice', 'Sesamum'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        ['Bharuch', 'Valia', ['Cotton', 'Rice', 'Groundnut', 'Castor'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],

        ['Bhavnagar', 'Bhavnagar', ['Cotton', 'Groundnut', 'Bajra', 'Sesamum'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables', 'Mango']],
        ['Bhavnagar', 'Gariadhar', ['Groundnut', 'Cotton', 'Bajra', 'Sesamum'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Bhavnagar', 'Ghogha', ['Cotton', 'Groundnut', 'Bajra'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon'], ['Vegetables']],
        ['Bhavnagar', 'Jesar', ['Groundnut', 'Cotton', 'Bajra', 'Sesamum'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Bhavnagar', 'Mahuva', ['Groundnut', 'Cotton', 'Bajra', 'Sesamum'], ['Wheat', 'Cumin', 'Coriander', 'Gram'], ['Watermelon', 'Fodder'], ['Mango', 'Chikoo', 'Vegetables']],
        ['Bhavnagar', 'Palitana', ['Groundnut', 'Cotton', 'Bajra'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables', 'Mango']],
        ['Bhavnagar', 'Shihor', ['Groundnut', 'Cotton', 'Bajra', 'Sesamum'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Bhavnagar', 'Sihor', ['Cotton', 'Groundnut', 'Bajra', 'Jowar'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        ['Bhavnagar', 'Talaja', ['Groundnut', 'Cotton', 'Bajra', 'Sesamum'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Mango', 'Chikoo', 'Vegetables']],
        ['Bhavnagar', 'Umrala', ['Groundnut', 'Cotton', 'Bajra'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables']],
        ['Bhavnagar', 'Vallabhipur', ['Cotton', 'Groundnut', 'Bajra', 'Sesamum'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables']],

        ['Ahmedabad', 'Ahmedabad City', ['Cotton', 'Bajra', 'Groundnut', 'Maize', 'Rice', 'Soybean', 'Chilli'], ['Wheat', 'Gram', 'Cumin', 'Mustard', 'Onion', 'Garlic'], ['Moong', 'Sesamum', 'Vegetables', 'Fodder'], ['Mango', 'Banana', 'Papaya', 'Lemon', 'Tomato', 'Okra']],
        ['Ahmedabad', 'Daskroi', ['Cotton', 'Bajra', 'Groundnut', 'Rice', 'Sesamum'], ['Wheat', 'Gram', 'Mustard', 'Cumin', 'Garlic'], ['Moong', 'Summer Bajra', 'Vegetables'], ['Banana', 'Papaya', 'Mango', 'Chikoo']],
        ['Anand', 'Anand', ['Rice', 'Tobacco', 'Maize', 'Soybean'], ['Wheat', 'Potato', 'Mustard', 'Garlic'], ['Moong', 'Summer Paddy', 'Fodder'], ['Banana', 'Mango', 'Papaya', 'Lemon']],
        ['Kheda', 'Nadiad', ['Rice', 'Tobacco', 'Cotton', 'Maize'], ['Wheat', 'Potato', 'Mustard', 'Garlic'], ['Vegetables', 'Fodder', 'Moong'], ['Banana', 'Papaya', 'Sugarcane']],
        ['Banaskantha', 'Deesa', ['Bajra', 'Castor', 'Groundnut'], ['Potato', 'Mustard', 'Cumin', 'Fennel', 'Isabgol'], ['Summer Bajra', 'Watermelon'], ['Mango', 'Date Palm', 'Pomegranate']],
        ['Mehsana', 'Unjha', ['Bajra', 'Castor', 'Cotton'], ['Cumin', 'Fennel', 'Mustard', 'Ajwain', 'Isabgol'], ['Moong', 'Vegetables'], ['Mango', 'Lemon']],
        ['Rajkot', 'Gondal', ['Groundnut', 'Cotton', 'Bajra', 'Chilli'], ['Wheat', 'Gram', 'Coriander', 'Garlic', 'Onion'], ['Summer Groundnut', 'Sesamum'], ['Mango', 'Guava', 'Chikoo']],
        ['Junagadh', 'Junagadh', ['Groundnut', 'Cotton', 'Bajra', 'Soybean'], ['Wheat', 'Gram', 'Coriander', 'Cumin'], ['Summer Groundnut', 'Mango'], ['Kesar Mango', 'Chikoo', 'Coconut']],
        ['Surat', 'Bardoli', ['Rice', 'Sugarcane', 'Cotton'], ['Wheat', 'Gram', 'Tur'], ['Fodder', 'Vegetables'], ['Banana', 'Mango', 'Chikoo', 'Papaya']],
        ['Navsari', 'Gandevi', ['Rice', 'Sugarcane', 'Cotton'], ['Wheat', 'Val'], ['Fodder', 'Vegetables'], ['Chikoo', 'Mango', 'Banana', 'Coconut', 'Sugarcane']],
        ['Surat', 'Surat City', ['Rice', 'Sugarcane', 'Cotton'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Cucumber'], ['Chikoo', 'Banana', 'Vegetables', 'Flowers']],
        ['Surat', 'Bardoli', ['Rice', 'Sugarcane', 'Cotton', 'Groundnut'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Chikoo', 'Mango', 'Banana', 'Vegetables']],

        ['Valsad', 'Valsad', ['Rice', 'Sugarcane', 'Cotton', 'Groundnut'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Chikoo', 'Mango', 'Coconut', 'Banana', 'Vegetables']],
        ['Valsad', 'Dharampur', ['Rice', 'Jowar', 'Maize', 'Groundnut'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Mango', 'Chikoo', 'Cashew', 'Vegetables']],
        
        ['Tapi', 'Vyara', ['Rice', 'Sugarcane', 'Cotton', 'Groundnut', 'Jowar'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Chikoo', 'Mango', 'Banana', 'Vegetables']],
        ['Dang', 'Ahwa', ['Rice', 'Jowar', 'Maize', 'Millets', 'Ragi'], ['Wheat', 'Gram'], ['Fodder'], ['Cashew', 'Mango', 'Bamboo shoots']],
        
        ['Aravalli', 'Modasa', ['Maize', 'Cotton', 'Groundnut', 'Castor', 'Sesamum'], ['Wheat', 'Gram', 'Mustard', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables', 'Mango']],
        ['Aravalli', 'Bayad', ['Maize', 'Cotton', 'Groundnut', 'Castor', 'Jowar'], ['Wheat', 'Gram', 'Mustard'], ['Fodder', 'Watermelon'], ['Vegetables']],

        ['Gandhinagar', 'Gandhinagar', ['Cotton', 'Bajra', 'Groundnut'], ['Wheat', 'Gram', 'Mustard'], ['Watermelon', 'Fodder'], ['Vegetables', 'Flowers']],
        ['Gandhinagar', 'Kalol', ['Cotton', 'Bajra', 'Groundnut', 'Maize'], ['Wheat', 'Gram', 'Mustard', 'Cumin'], ['Watermelon', 'Fodder'], ['Vegetables']],

        ['Gir Somnath', 'Kodinar', ['Groundnut', 'Cotton', 'Bajra', 'Sesamum'], ['Wheat', 'Cumin', 'Coriander', 'Gram'], ['Watermelon', 'Fodder'], ['Mango', 'Chikoo', 'Kesar Mango', 'Vegetables']],
        ['Gir Somnath', 'Veraval', ['Groundnut', 'Bajra', 'Sesamum', 'Cotton'], ['Wheat', 'Cumin', 'Gram'], ['Watermelon'], ['Kesar Mango', 'Chikoo', 'Coconut', 'Vegetables']],

        ['Rajkot', 'Rajkot', ['Groundnut', 'Cotton', 'Bajra', 'Sesamum'], ['Wheat', 'Gram', 'Cumin', 'Mustard'], ['Watermelon', 'Cucumber'], ['Vegetables', 'Mango']],
        ['Rajkot', 'Gondal', ['Groundnut', 'Cotton', 'Bajra', 'Sesamum'], ['Wheat', 'Gram', 'Cumin', 'Coriander'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],
        ['Rajkot', 'Jetpur', ['Groundnut', 'Cotton', 'Bajra', 'Sesamum'], ['Wheat', 'Gram', 'Cumin'], ['Watermelon', 'Fodder'], ['Mango', 'Vegetables']],

        ['Vadodara', 'Vadodara', ['Cotton', 'Rice', 'Tobacco', 'Maize', 'Sugarcane'], ['Wheat', 'Potato', 'Mustard', 'Gram'], ['Watermelon', 'Cucumber'], ['Banana', 'Mango', 'Vegetables', 'Flowers']],
        ['Vadodara', 'Padra', ['Cotton', 'Rice', 'Tobacco', 'Groundnut', 'Maize'], ['Wheat', 'Potato', 'Gram', 'Mustard'], ['Watermelon', 'Cucumber'], ['Banana', 'Mango', 'Vegetables']],
    ];

    foreach ($fullData as $row) {
        $dist = $row[0]; $tal = $row[1];
        foreach ($row[2] as $c) $conn->query("INSERT INTO location_crop_mapping (district_name, taluka_name, season, crop_name) VALUES ('$dist', '$tal', 'Kharif', '$c')");
        foreach ($row[3] as $c) $conn->query("INSERT INTO location_crop_mapping (district_name, taluka_name, season, crop_name) VALUES ('$dist', '$tal', 'Rabi', '$c')");
        foreach ($row[4] as $c) $conn->query("INSERT INTO location_crop_mapping (district_name, taluka_name, season, crop_name) VALUES ('$dist', '$tal', 'Summer', '$c')");
        foreach ($row[5] as $c) {
            $conn->query("INSERT INTO location_crop_mapping (district_name, taluka_name, season, crop_name) VALUES ('$dist', '$tal', 'Kharif', '$c')");
            $conn->query("INSERT INTO location_crop_mapping (district_name, taluka_name, season, crop_name) VALUES ('$dist', '$tal', 'Rabi', '$c')");
            $conn->query("INSERT INTO location_crop_mapping (district_name, taluka_name, season, crop_name) VALUES ('$dist', '$tal', 'Summer', '$c')");
        }
    }
}

$conn->set_charset('utf8mb4');

?>