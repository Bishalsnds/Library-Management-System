-- Create Database
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- Create Books Table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Sample Data
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

-- Display all books
SELECT * FROM books;