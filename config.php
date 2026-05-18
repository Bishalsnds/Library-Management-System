<?php
// Database Configuration for Library Management System
// XAMPP MySQL Connection Settings

// Database credentials
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');  // Default XAMPP MySQL username
define('DB_PASS', '');      // Default XAMPP MySQL password (empty)
define('DB_NAME', 'lms fines&warnings');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for proper character support (including Danish characters æ, ø, å)
$conn->set_charset("utf8mb4");

// Optional: Set timezone
date_default_timezone_set('Europe/Copenhagen');

?>
