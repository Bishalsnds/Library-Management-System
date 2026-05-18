<?php
/*
  Run this once to create the database and tables:
  http://localhost/library/setup_db.php

  MySQL settings: host=127.0.0.1, port=3306, user=root, password=root
*/
$host = "127.0.0.1";
$port = 3306;
$user = "root";
$pass = "";

// Connect without database
$conn = new mysqli($host, $user, $pass, '', $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS library_db";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

// Select database
$conn->select_db("library_db");

// Create table
$table_sql = "CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) UNIQUE NOT NULL,
    published_year INT,
    available_copies INT DEFAULT 0
)";

if ($conn->query($table_sql) === TRUE) {
    echo "Table created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>