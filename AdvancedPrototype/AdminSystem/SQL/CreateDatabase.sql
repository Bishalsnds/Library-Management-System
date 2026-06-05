-- Library Management System - Database Setup
-- Run this script in SQL Server Management Studio

CREATE DATABASE LibraryDB;
GO

USE LibraryDB;
GO

CREATE TABLE users (
    id         INT IDENTITY(1,1) PRIMARY KEY,
    first_name NVARCHAR(50)  NOT NULL,
    last_name  NVARCHAR(50)  NOT NULL,
    email      NVARCHAR(100) NOT NULL UNIQUE,
    student_id NVARCHAR(20)  NOT NULL UNIQUE,
    password   NVARCHAR(255) NOT NULL,
    role       NVARCHAR(20)  NOT NULL DEFAULT 'student',
    status     NVARCHAR(20)  NOT NULL DEFAULT 'active',
    created_at DATETIME      NOT NULL DEFAULT GETDATE()
);
GO

-- Sample data
INSERT INTO users (first_name, last_name, email, student_id, password, role, status)
VALUES ('Admin', 'User', 'admin@gmail.com', 'ADMIN001',
        'admin123', 'admin', 'active');
GO
