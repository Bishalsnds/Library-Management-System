-- Library Management System - Complete Database Setup
-- Drop existing databases to start fresh
DROP DATABASE IF EXISTS library_db;

-- Create Database
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

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

-- Create Books Table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Book Transactions Table (for check-in/check-out)
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    transaction_type ENUM('checkout', 'checkin') NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATETIME,
    return_date DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_book_id (book_id)
);

-- Insert Sample Users (password: password123)
INSERT INTO users (first_name, last_name, email, student_id, password, role, status) VALUES
('Admin', 'User', 'admin@gmail.com', 'ADM001', '$2y$10$FRChCacoThqNnpu7114SBuCv3nuKWJwbYq9KI1rwnwKmzwmV/D4Su', 'admin', 'active'),
('John', 'Doe', 'john@gmail.com', 'STU001', '$2y$10$FRChCacoThqNnpu7114SBuCv3nuKWJwbYq9KI1rwnwKmzwmV/D4Su', 'student', 'active'),
('Jane', 'Smith', 'jane@gmail.com', 'STU002', '$2y$10$FRChCacoThqNnpu7114SBuCv3nuKWJwbYq9KI1rwnwKmzwmV/D4Su', 'student', 'active'),
('Librarian', 'Staff', 'librarian@gmail.com', 'LIB001', '$2y$10$FRChCacoThqNnpu7114SBuCv3nuKWJwbYq9KI1rwnwKmzwmV/D4Su', 'librarian', 'active');

-- Insert Sample Books
INSERT INTO books (title, author, category, available) VALUES
('Harry Potter', 'J.K. Rowling', 'Fantasy', 1),
('The Hobbit', 'J.R.R. Tolkien', 'Fantasy', 0),
('Clean Code', 'Robert Martin', 'Programming', 1),
('Data Structures', 'Mark Allen', 'Education', 1),
('The Great Gatsby', 'F. Scott Fitzgerald', 'Fantasy', 1),
('Python Crash Course', 'Eric Matthes', 'Programming', 1),
('Design Patterns', 'Gang of Four', 'Programming', 0),
('Muna Madan', 'Laxmi Prasad Devkota', 'Nepali Literature', 1),
('Amar Nepalese', 'Dharanidhar Koirala', 'Nepali Classic', 1),
('Matasya', 'Rammohan Adhikari', 'Nepali Literature', 0),
('Shyama Swapna', 'Jaya Bhagwati', 'Nepali Literature', 1),
('Karnali Blues', 'Buddhisagar', 'Nepali Novel', 1),
('Ramayana (Nepali)', 'Traditional', 'Nepali Classic', 1),
('The Midnight Jasmine', 'Samrat Upadhyay', 'Nepali Literature', 0),
('Haruf Sahitya Sangrah', 'Various Authors', 'Nepali Classic', 1),
('To Kill a Mockingbird', 'Harper Lee', 'Classic Literature', 1),
('1984', 'George Orwell', 'Dystopian Fiction', 1),
('Pride and Prejudice', 'Jane Austen', 'Romance', 1),
('The Catcher in the Rye', 'J.D. Salinger', 'Coming of Age', 0),
('The Lord of the Rings', 'J.R.R. Tolkien', 'Fantasy', 1),
('JavaScript: The Good Parts', 'Douglas Crockford', 'Programming', 1),
('Introduction to Algorithms', 'Cormen et al.', 'Computer Science', 1),
('The Pragmatic Programmer', 'Hunt and Thomas', 'Programming', 0),
('Code Complete', 'Steve McConnell', 'Software Engineering', 1),
('Head First Design Patterns', 'Freeman and Robson', 'Programming', 1);
