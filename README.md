# Library Management System - Complete Setup Guide

## Project Structure
```
library mgt/
├── 📄 Frontend Files
│   ├── index.html          - Main search interface for users
│   ├── admin.html          - Admin panel for managing books
│   ├── style.css           - Unified styling for all pages
│   ├── script.js           - User search functionality
│   └── admin.js            - Admin panel functionality
│
├── 🔧 Backend Files
│   ├── api.php             - Main API endpoint (all CRUD operations)
│   ├── db.php              - Database connection
│   └── getBooks.php        - Reference search endpoint (optional)
│
├── 📊 Database
│   └── setup.sql           - Database schema + Nepali book samples
│
└── 📖 Documentation
    ├── API_DOCS.md         - Complete API documentation
    └── README.md           - This file
```

## System Architecture
```
┌─────────────────────────────────────────────────────────┐
│  Frontend (HTML + JavaScript)                           │
│  ├── index.html + script.js (User Search)              │
│  └── admin.html + admin.js (Admin Management)          │
└──────────────────────┬──────────────────────────────────┘
                       │ AJAX Fetch Requests
                       ▼
┌─────────────────────────────────────────────────────────┐
│  Backend API (api.php)                                  │
│  ├── action=search      (GET)    → Search & Filter    │
│  ├── action=getAll      (GET)    → Fetch All Books    │
│  ├── action=getCategories (GET) → Get All Categories  │
│  ├── action=add         (POST)   → Add New Book       │
│  ├── action=update      (POST)   → Update Book       │
│  └── action=delete      (POST)   → Delete Book       │
└──────────────────────┬──────────────────────────────────┘
                       │ SQL Queries
                       ▼
┌─────────────────────────────────────────────────────────┐
│  MySQL Database (library_db)                            │
│  └── books table (id, title, author, category, etc)    │
└─────────────────────────────────────────────────────────┘
```

## Key Features
- 🔍 **Search Engine** - Find books by title, author, or category
- 📚 **Nepali Literature** - Includes classic Nepali books
- ➕ **Add Books** - Add new books to the library
- ✏️ **Edit Books** - Update book details and availability
- 🗑️ **Delete Books** - Remove books from the library
- 📊 **Admin Panel** - Manage entire book collection
- 🛡️ **Secure Backend** - Input validation and SQL injection protection
- 🎨 **Responsive Design** - Clean, unified styling

## Step-by-Step Setup Instructions:

### Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Click "Start" button next to Apache
3. Click "Start" button next to MySQL

### Step 2: Create Database
**Method A: Using phpMyAdmin (Easy) ✓ RECOMMENDED**
1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click on "New" or "Create new database"
3. Enter database name: `library_db`
4. Click "Create"
5. Click on the `library_db` database
6. Go to "Import" tab
7. Click "Choose File" and select `setup.sql` from your project folder
8. Click "Go" to import

**Method B: Using Command Line**
1. Open Command Prompt
2. Navigate to: `cd C:\xampp\mysql\bin`
3. Connect to MySQL: `mysql -u root`
4. Paste the SQL commands from `setup.sql`

### Step 3: Access the Application
- **User Search:** `http://localhost/library%20mgt/index.html`
- **Admin Panel:** `http://localhost/library%20mgt/admin.html`

---

## Usage Guide

### 👤 For Regular Users
**File:** `index.html` + `script.js`

1. Open `http://localhost/library%20mgt/index.html`
2. Categories automatically load from database
3. Search by:
   - **Title** - e.g., "Muna Madan"
   - **Author** - e.g., "Laxmi Prasad Devkota"
   - **Category** - Select from dropdown
4. Click "Search" to see results in table format
5. View ID, Title, Author, Category, and Availability status

### 🔧 For Administrators
**File:** `admin.html` + `admin.js`

1. Open `http://localhost/library%20mgt/admin.html`
2. Three main tabs:

#### Tab 1: Search Books
- Search for specific books
- Filter by category
- View all book details in table format
- Quick Edit/Delete buttons for each book

#### Tab 2: Add Book
- Fill in book details:
  - **Title** (required)
  - **Author** (required)
  - **Category** (required)
  - **Available** (checkbox, default: checked)
- Click "Add Book" to save to database
- Success message shows book ID
- Form automatically clears after successful addition

#### Tab 3: Manage Books
- Load and view ALL books in database
- Table shows: ID, Title, Author, Category, Status
- Edit or Delete any book using action buttons
- Click buttons to modify books
- Changes reflect immediately

---

## Code Organization

### Frontend Consistency
All frontend files use consistent naming and patterns:
- **searchBooks()** - Main search function (used in both user & admin pages)
- **clearSearch()** - Clear search fields
- **loadCategories()** - Dynamically load categories from database
- **Unified CSS** - Single `style.css` for all pages

### Backend Consolidation
All API operations are centralized in `api.php`:
- No duplicate endpoints
- Single point of maintenance
- Consistent error handling
- All CRUD operations in one file

### Database Integration
- **Single Source of Truth** - All data stored in MySQL
- **Dynamic Categories** - Categories pulled from database, not hardcoded
- **Sample Data** - setup.sql includes Nepali book samples
- **Real-time Updates** - UI updates immediately after DB changes

---

## API Backend

### All API Requests
```
GET/POST /api.php?action=ACTION_NAME
```

| Action | Method | Purpose |
|--------|--------|---------|
| `search` | GET | Search books by title/author/category |
| `getAll` | GET | Fetch all books from database |
| `getCategories` | GET | Get all unique categories |
| `add` | POST | Add new book |
| `update` | POST | Update existing book |
| `delete` | POST | Delete book |

**See API_DOCS.md for detailed endpoint documentation with examples**

---

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

### Sample Data
The database includes:
- Classic English books (Fantasy, Programming, Education)
- Nepali classic literature (Muna Madan, Ramayana, etc.)
- Modern Nepali novels
- Total: 15 books across multiple categories

---

## Technical Details

### Technologies Used
- **Frontend:** HTML5, CSS3, Vanilla JavaScript (ES6)
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Server:** Apache (XAMPP)

### Security Features
1. **SQL Injection Protection** - Using `mysqli` real_escape_string()
2. **Input Validation** - Required fields checked on backend
3. **Type Casting** - Numeric values cast to prevent issues
4. **Error Handling** - Errors logged, not exposed to frontend
5. **JSON API** - Consistent response format

### Browser Compatibility
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- IE 11+ (basic support)

---

## Troubleshooting

### "No books displayed"
- Check if MySQL is running
- Verify database `library_db` exists
- Check if `books` table has data (import setup.sql)
- Open browser console (F12) for error messages

### "Connection refused"
- Start MySQL in XAMPP Control Panel
- Verify credentials in db.php (username: `root`, password: empty)

### "Categories not loading"
- Ensure database is populated with setup.sql
- Check browser console for fetch errors
- Verify api.php is accessible

### "Add/Edit/Delete not working"
- Check browser console (F12) for error messages
- Verify POST data format matches API docs
- Ensure MySQL connection is active
- Check db.php database name matches

---

## File Dependencies

```
index.html
├── style.css
└── script.js
    └── api.php
        └── db.php

admin.html
├── style.css
└── admin.js
    └── api.php
        └── db.php

setup.sql → MySQL (library_db)
```

---

## Development Notes

### Adding New Features
1. Add database fields to `setup.sql`
2. Add API handler function in `api.php`
3. Add frontend form fields in HTML
4. Add JavaScript functions in respective .js file
5. Update API_DOCS.md with new endpoint

### Modifying Database
- Edit `setup.sql` and re-import through phpMyAdmin
- Or use admin panel to manually add/edit books

### Styling Changes
- All styles in single `style.css` file
- Modify classes used in HTML/JS
- Classes are descriptive (e.g., `.btn-save`, `.status-badge`)

---

## File Sizes Reference
- setup.sql: ~2 KB (with sample data)
- api.php: ~5 KB
- admin.js: ~7 KB
- script.js: ~2 KB
- style.css: ~5 KB
- HTML files: ~3 KB each

**Total Project Size: < 30 KB (highly optimized)**
```
User/Admin → JavaScript (search/admin.js) → api.php → MySQL Database
```

### Available Endpoints
All API calls go through `api.php?action=ACTION_NAME`

1. **Search:** `api.php?action=search&search=term&category=Fiction`
2. **Add:** `api.php?action=add` (POST)
3. **Update:** `api.php?action=update` (POST)
4. **Delete:** `api.php?action=delete` (POST)
5. **Get All:** `api.php?action=getAll`
6. **Categories:** `api.php?action=getCategories`

📖 **Full API Documentation:** See `API_DOCS.md`

---

## Database Structure

### Books Table
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

### Sample Data
The `setup.sql` includes these sample books:
- Harry Potter (Fantasy) - Available
- The Hobbit (Fantasy) - Not Available
- Clean Code (Programming) - Available
- Data Structures (Education) - Available
- The Great Gatsby (Fantasy) - Available
- Python Crash Course (Programming) - Available
- Design Patterns (Programming) - Not Available

---

## Troubleshooting

### ❌ "Connection failed" error
**Solution:**
- Verify MySQL is running (check XAMPP Control Panel)
- Check database username/password in `db.php`
- Default: username=`root`, password=`` (empty)

### ❌ No books displayed when searching
**Solution:**
- Open browser developer tools (F12)
- Check Console tab for error messages
- Verify database exists: `http://localhost/phpmyadmin`
- Ensure data was imported from `setup.sql`

### ❌ "Cannot find api.php" or 404 error
**Solution:**
- Check all files are in: `C:\xampp\htdocs\library mgt\`
- Use correct URL: `http://localhost/library%20mgt/index.html`
- Note: URL uses %20 for space in "library mgt"

### ❌ Adding book but nothing happens
**Solution:**
- Fill in ALL required fields (Title, Author, Category)
- Check browser console for JavaScript errors
- Verify MySQL is running

### ❌ Edit/Delete buttons don't work
**Solution:**
- The prompts might be behind your browser window
- Look for prompt dialogs on screen
- In newer versions, we'll add a better modal interface

---

## Adding More Books via Database

### Via Admin Panel
1. Go to `http://localhost/library%20mgt/admin.html`
2. Click "Add Book" tab
3. Fill in details
4. Click "Add Book"

### Via phpMyAdmin
1. Go to `http://localhost/phpmyadmin`
2. Select `library_db` → `books` table
3. Click "Insert" tab
4. Fill in book details
5. Click "Go"

### Via SQL Command
```sql
INSERT INTO books (title, author, category, available) 
VALUES ('Book Title', 'Author Name', 'Category', 1);
```

---

## File Descriptions

| File | Purpose |
|------|---------|
| **index.html** | Main user interface for searching books |
| **admin.html** | Admin panel for managing all books |
| **script.js** | Search functionality for index.html |
| **admin.js** | Admin panel functionality for admin.html |
| **style.css** | CSS styling for both interfaces |
| **api.php** | Backend API - handles all database operations |
| **db.php** | Database connection configuration |
| **setup.sql** | Database schema and sample data |
| **API_DOCS.md** | Complete API documentation for developers |
| **README.md** | This file |

---

## API Response Examples

### Search Books
```javascript
GET /api.php?action=search&search=Harry

Response:
[
    {
        "id": 1,
        "title": "Harry Potter",
        "author": "J.K. Rowling",
        "category": "Fantasy",
        "available": "1"
    }
]
```

### Add Book
```javascript
POST /api.php?action=add
Body: { title, author, category, available }

Response:
{
    "success": true,
    "message": "Book added successfully",
    "id": 8
}
```

---

## Performance & Scalability

- Database indexes on commonly searched fields (title, author)
- Efficient SQL queries with proper filtering
- Responsive admin interface for large book collections
- Can handle thousands of books efficiently

---

## Next Steps

1. ✅ Set up database with `setup.sql`
2. ✅ Test user search at `index.html`
3. ✅ Access admin panel at `admin.html`
4. ✅ Add/edit/delete books
5. ✅ Read `API_DOCS.md` for advanced usage

---

## Support

For issues:
1. Check browser console (F12) for error messages
2. Verify MySQL and Apache are running
3. Check file permissions
4. Review this README and API_DOCS.md
5. Verify all files are in `C:\xampp\htdocs\library mgt\`

---

## Features Included

✅ Search by title, author, category  
✅ Add new books  
✅ Edit book details  
✅ Update availability status  
✅ Delete books  
✅ View all books  
✅ Responsive design  
✅ Error handling  
✅ Input validation  
✅ SQL injection protection  
✅ Admin panel interface  
✅ Complete API backend  

Enjoy your Library Management System! 📚