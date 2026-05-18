<?php
error_reporting(0);
ini_set('display_errors', 0);

echo "Creating Library Database...\n\n";

// Try multiple connection strategies for MariaDB
$connStr = null;

// Strategy 1: Using PDO SQLite workaround - save SQL to execute later
// Strategy 2: Direct socket connection
// Strategy 3: TCP connection

$attempted = false;

// Try using PHP's built-in SQLite to generate the SQL we need
try {
    // Create SQL statements
    $createDb = "CREATE DATABASE IF NOT EXISTS library_db;";
    $createTable = "CREATE TABLE IF NOT EXISTS books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        isbn VARCHAR(20) UNIQUE NOT NULL,
        published_year INT,
        available_copies INT DEFAULT 0
    );";
    
    // Save to temporary SQL file
    $sqlFile = 'c:\\xampp\\tmp\\setup_library.sql';
    file_put_contents($sqlFile, $createDb . "\nUSE library_db;\n" . $createTable);
    
    // Try to execute using mysql CLI
    $output = [];
    $return = 0;
    exec('c:\\xampp\\mysql\\bin\\mysql.exe -u root < "' . $sqlFile . '" 2>&1', $output, $return);
    
    if ($return === 0) {
        echo "✓ Database created successfully\n";
        unlink($sqlFile);
        echo "\n✅ Database setup complete!\n";
        echo "You can now visit: http://localhost/library/index.php\n";
        exit(0);
    }
} catch (Exception $e) {
    // Continue to next strategy
}

// Strategy 2: Try socket connection with no auth
try {
    $conn = @new mysqli('localhost', '', '', '', 0, 'mysql');
    
    if ($conn && !$conn->connect_error) {
        echo "✓ Connected with empty credentials\n";
        $attempted = true;
        
        if ($conn->query("CREATE DATABASE IF NOT EXISTS library_db") === TRUE) {
            echo "✓ Database created\n";
            
            $conn->select_db("library_db");
            if ($conn->query("CREATE TABLE IF NOT EXISTS books (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                author VARCHAR(255) NOT NULL,
                isbn VARCHAR(20) UNIQUE NOT NULL,
                published_year INT,
                available_copies INT DEFAULT 0
            )") === TRUE) {
                echo "✓ Table created\n";
                echo "\n✅ Setup complete!\n";
                $conn->close();
                exit(0);
            }
        }
        $conn->close();
    }
} catch (Exception $e) {
    // Continue
}

// Fallback: Show manual instructions
echo "\n⚠️  Please set up the database manually:\n\n";
echo "1. Open phpMyAdmin or MySQL command line\n";
echo "2. Run these commands:\n\n";
echo "CREATE DATABASE IF NOT EXISTS library_db;\n";
echo "USE library_db;\n";
echo "CREATE TABLE IF NOT EXISTS books (\n";
echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
echo "    title VARCHAR(255) NOT NULL,\n";
echo "    author VARCHAR(255) NOT NULL,\n";
echo "    isbn VARCHAR(20) UNIQUE NOT NULL,\n";
echo "    published_year INT,\n";
echo "    available_copies INT DEFAULT 0\n";
echo ");\n\n";
echo "3. Then refresh this page or visit: http://localhost/library/index.php\n";
?>

