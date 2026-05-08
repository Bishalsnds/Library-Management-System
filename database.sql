-- Library Management System Database Setup
-- Run this in phpMyAdmin or MySQL command line

-- Create Database
CREATE DATABASE IF NOT EXISTS lms_db;
USE lms_db;

-- Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    student_id VARCHAR(20) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin', 'librarian') DEFAULT 'student',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_student_id (student_id)
);

-- Insert sample users (passwords hashed with DEFAULT: password123)
INSERT INTO users (first_name, last_name, email, student_id, password, role, status) VALUES
('Admin', 'User', 'admin@library.com', 'ADMIN001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('John', 'Doe', 'john@library.com', 'STU001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
('Jane', 'Smith', 'jane@library.com', 'STU002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active');

-- Note: Default password for all sample users is "password123"
