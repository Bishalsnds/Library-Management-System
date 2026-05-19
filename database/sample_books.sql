-- Sample Books Data for LMS Library System
-- Run this in phpMyAdmin to add books to your library

USE `LMS fines and warning`;

-- Add sample books to the books table
INSERT INTO books (book_title, author, isbn, category, available_copies, total_copies) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0-7432-7356-5', 'Fiction', 2, 3),
('To Kill a Mockingbird', 'Harper Lee', '978-0-06-112008-4', 'Fiction', 1, 2),
('1984', 'George Orwell', '978-0-451-52493-2', 'Fiction', 0, 2),
('Pride and Prejudice', 'Jane Austen', '978-0-14-143951-8', 'Fiction', 1, 1),
('The Catcher in the Rye', 'J.D. Salinger', '978-0-316-76948-0', 'Fiction', 2, 2),
('Wuthering Heights', 'Emily Brontë', '978-0-14-018495-7', 'Fiction', 1, 1),
('Jane Eyre', 'Charlotte Brontë', '978-0-14-144144-3', 'Fiction', 1, 1),
('The Hobbit', 'J.R.R. Tolkien', '978-0-547-92822-8', 'Fantasy', 3, 3),
('The Lord of the Rings', 'J.R.R. Tolkien', '978-0-544-00901-1', 'Fantasy', 2, 2),
('Harry Potter and the Philosopher''s Stone', 'J.K. Rowling', '978-0-747-53269-9', 'Fantasy', 3, 3),
('A Brief History of Time', 'Stephen Hawking', '978-0-553-38016-3', 'Science', 1, 1),
('Cosmos', 'Carl Sagan', '978-0-345-53438-0', 'Science', 2, 2),
('The Selfish Gene', 'Richard Dawkins', '978-0-19-288860-8', 'Science', 1, 1),
('Database Design', 'C.J. Date', '978-0-596-52902-4', 'Technology', 1, 1),
('The Clean Coder', 'Robert C. Martin', '978-0-13-595705-9', 'Technology', 2, 2),
('Code Complete', 'Steve McConnell', '978-0-735-61967-8', 'Technology', 1, 1),
('The Pragmatic Programmer', 'David Thomas & Andrew Hunt', '978-0-20161-992-7', 'Technology', 1, 1),
('Introduction to Algorithms', 'Cormen, Leiserson, Rivest, Stein', '978-0-262-03384-8', 'Technology', 0, 1),
('The Hobbit (Audiobook)', 'J.R.R. Tolkien', '978-0-393-25819-5', 'Audiobook', 1, 1),
('Sapiens', 'Yuval Noah Harari', '978-0-06-231609-7', 'History', 2, 2);

-- Verify books were added
SELECT COUNT(*) as total_books FROM books;
