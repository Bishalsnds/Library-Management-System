<?php
include 'db.php';

$books = [
    ['title' => 'To Kill a Mockingbird', 'author' => 'Harper Lee', 'isbn' => '978-0-06-112008-4', 'published_year' => 1960, 'available_copies' => 5],
    ['title' => '1984', 'author' => 'George Orwell', 'isbn' => '978-0-452-28423-4', 'published_year' => 1949, 'available_copies' => 3],
    ['title' => 'The Great Gatsby', 'author' => 'F. Scott Fitzgerald', 'isbn' => '978-0-7432-7356-5', 'published_year' => 1925, 'available_copies' => 4],
    ['title' => 'Pride and Prejudice', 'author' => 'Jane Austen', 'isbn' => '978-0-14-143951-8', 'published_year' => 1813, 'available_copies' => 6],
    ['title' => 'The Catcher in the Rye', 'author' => 'J.D. Salinger', 'isbn' => '978-0-316-76948-0', 'published_year' => 1951, 'available_copies' => 2],
    ['title' => 'Harry Potter and the Sorcerer\'s Stone', 'author' => 'J.K. Rowling', 'isbn' => '978-0-590-35340-3', 'published_year' => 1997, 'available_copies' => 10],
    ['title' => 'The Lord of the Rings', 'author' => 'J.R.R. Tolkien', 'isbn' => '978-0-544-00203-5', 'published_year' => 1954, 'available_copies' => 7],
    ['title' => 'The Hobbit', 'author' => 'J.R.R. Tolkien', 'isbn' => '978-0-547-92822-7', 'published_year' => 1937, 'available_copies' => 8],
    ['title' => 'Dune', 'author' => 'Frank Herbert', 'isbn' => '978-0-441-17271-9', 'published_year' => 1965, 'available_copies' => 4],
    ['title' => 'Neuromancer', 'author' => 'William Gibson', 'isbn' => '978-0-441-56956-4', 'published_year' => 1984, 'available_copies' => 3],
    ['title' => 'Brave New World', 'author' => 'Aldous Huxley', 'isbn' => '978-0-06-085052-4', 'published_year' => 1932, 'available_copies' => 5],
    ['title' => 'The Hitchhiker\'s Guide to the Galaxy', 'author' => 'Douglas Adams', 'isbn' => '978-0-345-39180-3', 'published_year' => 1979, 'available_copies' => 6],
    ['title' => 'Ender\'s Game', 'author' => 'Orson Scott Card', 'isbn' => '978-0-812-55065-5', 'published_year' => 1985, 'available_copies' => 4],
    ['title' => 'The Name of the Wind', 'author' => 'Patrick Rothfuss', 'isbn' => '978-0-7564-0407-9', 'published_year' => 2007, 'available_copies' => 5],
    ['title' => 'Mistborn: The Final Empire', 'author' => 'Brandon Sanderson', 'isbn' => '978-0-7653-5178-1', 'published_year' => 2006, 'available_copies' => 7],
];

foreach ($books as $book) {
    $title = $conn->real_escape_string($book['title']);
    $author = $conn->real_escape_string($book['author']);
    $isbn = $conn->real_escape_string($book['isbn']);
    $published_year = $book['published_year'];
    $available_copies = $book['available_copies'];

    $sql = "INSERT INTO books (title, author, isbn, published_year, available_copies) VALUES ('$title', '$author', '$isbn', $published_year, $available_copies)";
    $conn->query($sql);
}

$conn->close();

echo 'Seed complete.';
