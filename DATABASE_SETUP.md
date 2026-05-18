# Library Management System - Database Setup Guide

## Quick Start: Setting up XAMPP MySQL

### Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services

### Step 2: Import the Database Schema
1. Open your browser and go to: **http://localhost/phpmyadmin/**
2. Click on **"Import"** at the top of the page
3. Select the file: **database.sql** from this folder
4. Click **"Go"** to import

**Alternative Method (Using MySQL Command Line):**
```bash
# Open Command Prompt in the project folder and run:
mysql -u root -p < database.sql

# (Press Enter when prompted for password - leave it blank for default XAMPP setup)
```

### Step 3: Verify Database Creation
1. Go to **http://localhost/phpmyadmin/**
2. On the left sidebar, you should see **library_management** database
3. Click on it to see the tables: students, books, fines, warnings

### Step 4: Configure Database Connection
The connection is already configured in `config.php`:
- Host: `localhost`
- Username: `root`
- Password: (empty - default XAMPP)
- Database: `library_management`

If your MySQL credentials are different, edit `config.php` and update the values.

### Step 5: Update PHP Files to Use Database
Once the database is set up, the PHP files can be updated to:
- Save fine records to the database instead of sessions
- Save warning records to the database
- Display data from the actual database tables
- Add edit/delete/update functionality

---

## Database Tables Overview

### 1. **students** - Stores student information
- student_id (Primary Key)
- student_name
- email, phone
- status (active/inactive)

### 2. **books** - Stores library book information
- book_id (Primary Key)
- book_title, author, isbn
- category
- available_copies, total_copies

### 3. **fines** - Stores student fines
- fine_id (Primary Key)
- student_id (Foreign Key)
- book_id (Foreign Key)
- fine_amount (in DKK)
- reason, status (unpaid/paid/waived)
- issued_date, due_date, payment_date

### 4. **warnings** - Stores student warnings
- warning_id (Primary Key)
- student_id (Foreign Key)
- warning_level (Level 1/2/3)
- note, status (active/resolved/closed)
- issued_date, resolved_date

---

## Sample Data
The database.sql file includes sample data:
- 4 sample students
- 5 sample books
- 3 sample fines
- 3 sample warnings

You can delete this data and add your own through the web interface.

---

## Next Steps
Once the database is imported, let me know and I can create updated PHP files that:
1. Read/Write from the actual database
2. Support adding, editing, deleting fines and warnings
3. Generate reports and summaries
4. Add user authentication if needed

---

## Troubleshooting

**Error: "Connection failed: Access denied"**
- Check if MySQL is running in XAMPP Control Panel
- Verify username and password in config.php

**Error: "Unknown database 'library_management'"**
- Make sure you imported database.sql successfully
- Check phpmyadmin to confirm the database exists

**Error: "Table doesn't exist"**
- Re-import the database.sql file
- Make sure the import completed without errors

**Danish characters (æ, ø, å) not displaying correctly**
- The database uses utf8mb4 encoding
- Make sure your HTML files have `<meta charset="UTF-8">`
- Check that your browser is set to UTF-8 encoding
