<?php
// Database Configuration
$servername = "localhost";
$username = "root";  // XAMPP default username
$password = "";      // XAMPP default password (empty)
$database = "library_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Set charset to UTF-8
$conn->set_charset("utf8");

?>