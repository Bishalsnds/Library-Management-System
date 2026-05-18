<?php
/*
  Database connection settings for this project:
  host=127.0.0.1, port=3306, user=root, password=root
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "127.0.0.1";
$port = 3306;
$user = "root"; // default XAMPP/MariaDB user
$pass = "root"; // default XAMPP/MariaDB password
$dbname = "library_db";

// Create connection using MySQLi
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n\nPlease run: http://localhost/Xaamp%20htdocs/setup_db.php");
}

// Set charset to UTF8
$conn->set_charset("utf8mb4");
?>