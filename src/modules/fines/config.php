<?php
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_management');

date_default_timezone_set('Europe/Copenhagen');

// Connect without selecting a database first so we can create it if missing
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);

if ($conn->connect_error) {
    die("MySQL connection failed: " . $conn->connect_error . ". Make sure XAMPP is running.");
}

$conn->set_charset("utf8mb4");

// Create the database if it doesn't exist
$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db(DB_NAME);

// Create all required tables if they don't exist
$conn->query("CREATE TABLE IF NOT EXISTS students (
    student_id   INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    email        VARCHAR(100),
    phone        VARCHAR(20),
    status       ENUM('active','inactive') DEFAULT 'active',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_student_name (student_name),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS books (
    book_id          INT AUTO_INCREMENT PRIMARY KEY,
    book_title       VARCHAR(150) NOT NULL,
    author           VARCHAR(100),
    isbn             VARCHAR(20),
    category         VARCHAR(50),
    available_copies INT DEFAULT 1,
    total_copies     INT DEFAULT 1,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_book_title (book_title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS fines (
    fine_id      INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT NOT NULL,
    book_id      INT,
    student_name VARCHAR(100) NOT NULL,
    book_title   VARCHAR(150) NOT NULL,
    fine_amount  DECIMAL(10,2) NOT NULL,
    reason       VARCHAR(100) DEFAULT 'Overdue return',
    status       ENUM('unpaid','paid','waived') DEFAULT 'unpaid',
    payment_date DATETIME,
    issued_date  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date     DATE,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS warnings (
    warning_id    INT AUTO_INCREMENT PRIMARY KEY,
    student_id    INT NOT NULL,
    student_name  VARCHAR(100) NOT NULL,
    warning_level ENUM('Level 1','Level 2','Level 3') NOT NULL DEFAULT 'Level 1',
    note          LONGTEXT,
    status        ENUM('active','resolved','closed') DEFAULT 'active',
    issued_date   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_date DATETIME,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS payments (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    payment_id     VARCHAR(100) UNIQUE NOT NULL,
    fine_id        INT NOT NULL,
    amount         DECIMAL(10,2) NOT NULL,
    method         ENUM('google_pay','mobile_pay') NOT NULL,
    status         ENUM('pending','completed','failed','cancelled') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fine_id) REFERENCES fines(fine_id) ON DELETE CASCADE,
    INDEX idx_fine_id (fine_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
?>
