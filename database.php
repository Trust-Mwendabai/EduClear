<?php
$servername = "localhost";
$username = "root"; // Default username for XAMPP
$password = ""; // Default password for XAMPP
$dbname = "EduClear"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}

// Select the database
$conn->select_db($dbname);

// SQL to create tables
$sqlUsers = "CREATE TABLE IF NOT EXISTS Users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('student', 'admin') NOT NULL
)";

$sqlPayments = "CREATE TABLE IF NOT EXISTS Payments (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATETIME NOT NULL,
    status ENUM('pending', 'completed', 'failed') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(id)
)";

$sqlClearanceForms = "CREATE TABLE IF NOT EXISTS ClearanceForms (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    form_data TEXT NOT NULL,
    generated_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(id)
)";

$sqlNotifications = "CREATE TABLE IF NOT EXISTS Notifications (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    message TEXT NOT NULL,
    sent_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(id)
)";

// Execute table creation queries
$conn->query($sqlUsers);
$conn->query($sqlPayments);
$conn->query($sqlClearanceForms);
$conn->query($sqlNotifications);

echo "Tables created successfully";

$conn->close();
?>
