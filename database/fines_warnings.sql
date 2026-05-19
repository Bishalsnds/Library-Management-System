-- Library Management System: Fines and Warnings Database Schema
-- Compatible with XAMPP MySQL / MariaDB
-- Created: 2026-05-11

-- Create Database
CREATE DATABASE IF NOT EXISTS library_management;
USE library_management;

-- ============================================
-- STUDENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS students (
  student_id INT AUTO_INCREMENT PRIMARY KEY,
  student_name VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(20),
  enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_student_name (student_name),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BOOKS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS books (
  book_id INT AUTO_INCREMENT PRIMARY KEY,
  book_title VARCHAR(150) NOT NULL,
  author VARCHAR(100),
  isbn VARCHAR(20),
  category VARCHAR(50),
  available_copies INT DEFAULT 1,
  total_copies INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_book_title (book_title),
  INDEX idx_isbn (isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- FINES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS fines (
  fine_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  book_id INT,
  student_name VARCHAR(100) NOT NULL,
  book_title VARCHAR(150) NOT NULL,
  fine_amount DECIMAL(10, 2) NOT NULL,
  reason VARCHAR(100) DEFAULT 'Overdue return',
  status ENUM('unpaid', 'paid', 'waived') DEFAULT 'unpaid',
  payment_date DATETIME,
  issued_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  due_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE SET NULL,
  INDEX idx_student_id (student_id),
  INDEX idx_status (status),
  INDEX idx_issued_date (issued_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WARNINGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS warnings (
  warning_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  student_name VARCHAR(100) NOT NULL,
  warning_level ENUM('Level 1', 'Level 2', 'Level 3') NOT NULL DEFAULT 'Level 1',
  note LONGTEXT,
  status ENUM('active', 'resolved', 'closed') DEFAULT 'active',
  issued_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  resolved_date DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  INDEX idx_student_id (student_id),
  INDEX idx_warning_level (warning_level),
  INDEX idx_status (status),
  INDEX idx_issued_date (issued_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA (Optional)
-- ============================================

-- Insert sample students
INSERT INTO students (student_name, email, phone, status) VALUES
('Ahmed Hassan', 'ahmed@example.com', '+45-40-123456', 'active'),
('Emma Nielsen', 'emma@example.com', '+45-40-234567', 'active'),
('Sophia Andersen', 'sophia@example.com', '+45-40-345678', 'active'),
('Liam Pedersen', 'liam@example.com', '+45-40-456789', 'active');

-- Insert sample books
INSERT INTO books (book_title, author, isbn, category, available_copies, total_copies) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0-7432-7356-5', 'Fiction', 2, 3),
('To Kill a Mockingbird', 'Harper Lee', '978-0-06-112008-4', 'Fiction', 1, 2),
('1984', 'George Orwell', '978-0-451-52493-2', 'Fiction', 0, 2),
('Database Design', 'C.J. Date', '978-0-596-52902-4', 'Technology', 1, 1),
('The Clean Coder', 'Robert C. Martin', '978-0-13-595705-9', 'Technology', 2, 2);

-- Insert sample fines
INSERT INTO fines (student_id, book_id, student_name, book_title, fine_amount, reason, status, issued_date) VALUES
(1, 1, 'Ahmed Hassan', 'The Great Gatsby', 50.00, 'Overdue return', 'unpaid', NOW() - INTERVAL 10 DAY),
(2, 3, 'Emma Nielsen', '1984', 75.00, 'Overdue return', 'unpaid', NOW() - INTERVAL 5 DAY),
(3, 2, 'Sophia Andersen', 'To Kill a Mockingbird', 25.00, 'Overdue return', 'paid', NOW() - INTERVAL 15 DAY);

-- Insert sample warnings
INSERT INTO warnings (student_id, student_name, warning_level, note, status, issued_date) VALUES
(1, 'Ahmed Hassan', 'Level 1', 'First warning for overdue items. Please return books on time.', 'active', NOW() - INTERVAL 8 DAY),
(2, 'Emma Nielsen', 'Level 2', 'Second warning. Student has multiple overdue items. Final notice before account suspension.', 'active', NOW() - INTERVAL 3 DAY),
(4, 'Liam Pedersen', 'Level 1', 'Damage to returned book cover. Please handle books carefully.', 'resolved', NOW() - INTERVAL 20 DAY);

-- ============================================
-- VIEWS (Optional for reporting)
-- ============================================

-- View: Total fines by student
CREATE VIEW IF NOT EXISTS student_fine_summary AS
SELECT 
  s.student_id,
  s.student_name,
  s.email,
  COUNT(f.fine_id) as total_fines,
  SUM(CASE WHEN f.status = 'unpaid' THEN f.fine_amount ELSE 0 END) as unpaid_amount,
  SUM(CASE WHEN f.status = 'paid' THEN f.fine_amount ELSE 0 END) as paid_amount,
  MAX(f.issued_date) as last_fine_date
FROM students s
LEFT JOIN fines f ON s.student_id = f.student_id
GROUP BY s.student_id, s.student_name, s.email;

-- View: Active warnings by student
CREATE VIEW IF NOT EXISTS active_warnings_summary AS
SELECT 
  s.student_id,
  s.student_name,
  s.email,
  COUNT(w.warning_id) as total_warnings,
  COUNT(CASE WHEN w.status = 'active' THEN 1 END) as active_warnings,
  MAX(w.warning_level) as highest_level,
  MAX(w.issued_date) as last_warning_date
FROM students s
LEFT JOIN warnings w ON s.student_id = w.student_id
GROUP BY s.student_id, s.student_name, s.email;

-- ============================================
-- END OF SCHEMA
-- ============================================
