# Library Management System - Backend API Documentation

## Overview
Complete backend API for managing a library book database with MySQL and XAMPP.

## Files Created
- **api.php** - Main API endpoint for all operations
- **admin.html** - Admin panel UI for managing books
- **admin.js** - Frontend logic for admin panel
- **db.php** - Database connection file
- **setup.sql** - Database schema and sample data

## API Endpoints

All requests go through: `api.php?action=ACTION_NAME`

### 1. Search Books
**Endpoint:** `api.php?action=search`  
**Method:** GET  
**Parameters:**
- `search` (optional) - Search term for title or author
- `category` (optional) - Filter by category

**Example:**
```
GET /api.php?action=search&search=Harry&category=Fantasy
```

**Response:**
```json
[
    {
        "id": 1,
        "title": "Harry Potter",
        "author": "J.K. Rowling",
        "category": "Fantasy",
        "available": 1
    }
]
```

---

### 2. Add Book
**Endpoint:** `api.php?action=add`  
**Method:** POST  
**Content-Type:** application/json  
**Body Parameters:**
- `title` (required) - Book title
- `author` (required) - Author name
- `category` (required) - Book category
- `available` (optional, default=1) - 1 for available, 0 for not available

**Example:**
```javascript
fetch('api.php?action=add', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        title: 'New Book',
        author: 'Author Name',
        category: 'Fiction',
        available: 1
    })
})
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Book added successfully",
    "id": 8
}
```

**Response (Error):**
```json
{
    "error": "Title is required"
}
```

---

### 3. Update Book
**Endpoint:** `api.php?action=update`  
**Method:** POST  
**Content-Type:** application/json  
**Body Parameters:**
- `id` (required) - Book ID
- `title` (optional) - New title
- `author` (optional) - New author
- `category` (optional) - New category
- `available` (optional) - 1 or 0

**Example:**
```javascript
fetch('api.php?action=update', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        id: 5,
        available: 0,  // Mark as not available
        title: "Updated Title"
    })
})
```

**Response:**
```json
{
    "success": true,
    "message": "Book updated successfully"
}
```

---

### 4. Delete Book
**Endpoint:** `api.php?action=delete`  
**Method:** POST  
**Content-Type:** application/json  
**Body Parameters:**
- `id` (required) - Book ID to delete

**Example:**
```javascript
fetch('api.php?action=delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: 5 })
})
```

**Response:**
```json
{
    "success": true,
    "message": "Book deleted successfully"
}
```

---

### 5. Get All Books
**Endpoint:** `api.php?action=getAll`  
**Method:** GET  
**Parameters:** None

**Example:**
```
GET /api.php?action=getAll
```

**Response:**
```json
[
    {
        "id": 1,
        "title": "Harry Potter",
        "author": "J.K. Rowling",
        "category": "Fantasy",
        "available": 1
    },
    {
        "id": 2,
        "title": "The Hobbit",
        "author": "J.R.R. Tolkien",
        "category": "Fantasy",
        "available": 0
    }
]
```

---

### 6. Get Categories
**Endpoint:** `api.php?action=getCategories`  
**Method:** GET  
**Parameters:** None

**Example:**
```
GET /api.php?action=getCategories
```

**Response:**
```json
[
    "Fantasy",
    "Programming",
    "Education"
]
```

---

## Usage Guide

### For Users - Book Search
1. Open `http://localhost/library%20mgt/index.html`
2. Search by title, author, or category
3. See available books from the database

### For Admins - Manage Books
1. Open `http://localhost/library%20mgt/admin.html`
2. **Search Tab:** Search and filter books from database
3. **Add Tab:** Add new books to the database
4. **Manage Tab:** View all books, edit, or delete

## Database Structure

```sql
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Error Handling

All endpoints return JSON responses. Check for `error` key:

```json
{
    "error": "Error message here"
}
```

Common errors:
- `"Connection failed"` - MySQL not running
- `"Database not found"` - library_db doesn't exist
- `"Title is required"` - Missing required field

## Security Notes

1. **SQL Injection Protection:** Using `real_escape_string()` for all inputs
2. **Input Validation:** Required fields are validated
3. **Type Casting:** Numeric values are cast to prevent issues
4. **Error Reporting:** Disabled display of errors in responses (logged only)

## Example Usage in JavaScript

### Search and Display
```javascript
fetch('api.php?action=search&search=Harry&category=Fantasy')
    .then(response => response.json())
    .then(books => {
        books.forEach(book => {
            console.log(book.title); // Harry Potter
        });
    });
```

### Add a Book
```javascript
fetch('api.php?action=add', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        title: 'The Lord of the Rings',
        author: 'J.R.R. Tolkien',
        category: 'Fantasy',
        available: 1
    })
})
.then(response => response.json())
.then(data => alert('Book added with ID: ' + data.id));
```

### Update Availability
```javascript
fetch('api.php?action=update', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        id: 1,
        available: 0  // Mark as borrowed
    })
})
.then(response => response.json())
.then(data => console.log(data.message));
```

## Troubleshooting

### "No books displayed"
- Check if MySQL is running
- Verify database `library_db` exists
- Check if `books` table has data
- Open browser console (F12) for error messages

### "Connection refused"
- Start MySQL in XAMPP Control Panel
- Verify credentials in db.php (username: root, password: empty)

### "404 api.php not found"
- Ensure api.php is in: `C:\xampp\htdocs\library mgt\`
- URL should be: `http://localhost/library%20mgt/api.php`