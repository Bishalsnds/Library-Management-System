# Library Management System - Fines & Warnings Module

**Created:** May 11, 2026  
**Status:** UI Complete + Database Ready  
**Currency:** DKK (Danish Krone)  
**Responsive:** Mobile & Desktop Optimized

---

## 📁 Project Files

```
fine and warning/
├── index.php               # Dashboard (session-based)
├── fines.php              # Fine management (session-based)
├── warnings.php           # Warning management (session-based)
├── fines-db.php           # Fine management (DATABASE VERSION)
├── warnings-db.php        # Warning management (DATABASE VERSION)
├── styles.css             # Vibrant purple/orange UI theme
├── config.php             # Database connection settings
├── database.sql           # MySQL schema & sample data
├── DATABASE_SETUP.md      # Database import instructions
└── README.md              # This file
```

---

## 🎨 Features

### ✅ Completed
- **Vibrant UI** with purple, orange, and white color scheme
- **Fully responsive** design (mobile, tablet, desktop)
- **Input validation** on all form fields
- **DKK currency** formatting (Danish Krone - kr)
- **Session-based** storage (fines.php, warnings.php)
- **Database-ready** versions (fines-db.php, warnings-db.php)

### 🔄 Form Validation
- Student name: 2-100 characters, letters/numbers/hyphens only
- Book title: 2-100 characters, letters/numbers/hyphens only
- Fine amount: Positive numbers up to 100,000 DKK
- Warning level: Required dropdown selection
- Supports Danish characters (æ, ø, å)

### 📱 Responsive Breakpoints
- **Desktop** (1024px+): Full layout with grid
- **Tablet** (760px - 1024px): Adjusted spacing
- **Mobile** (480px - 760px): Single column forms, compact buttons
- **Small Mobile** (<480px): Full-width inputs, stacked layout

---

## 🚀 Quick Start

### Option 1: Session-Based (No Database)
Perfect for testing and demo purposes:

1. Open browser: `http://localhost/fine%20and%20warning/index.php`
2. Click "Manage Fines" or "Manage Warnings"
3. Add records using the forms
4. Data stored in PHP session (clears when browser closes)

### Option 2: Database-Enabled (Recommended)
For persistent data storage:

#### Step A: Import Database
1. Start XAMPP (Apache + MySQL)
2. Open `http://localhost/phpmyadmin/`
3. Click **Import** → Select **database.sql** → Click **Go**
4. Verify the `library_management` database is created

#### Step B: Connect to Database
1. Update `config.php` if your MySQL credentials differ from default
2. Open: `http://localhost/fine%20and%20warning/fines-db.php`
3. Or: `http://localhost/fine%20and%20warning/warnings-db.php`
4. Add records - they're now saved to the database!

---

## 📋 Database Tables

### students
- `student_id` (Primary Key)
- `student_name` (VARCHAR 100)
- `email`, `phone`
- `status` (active/inactive)

### books
- `book_id` (Primary Key)
- `book_title` (VARCHAR 150)
- `author`, `isbn`, `category`
- `available_copies`, `total_copies`

### fines
- `fine_id` (Primary Key)
- `student_id` (Foreign Key)
- `book_id` (Foreign Key)
- `fine_amount` (DECIMAL - in DKK)
- `reason`, `status` (unpaid/paid/waived)
- `issued_date`, `due_date`, `payment_date`

### warnings
- `warning_id` (Primary Key)
- `student_id` (Foreign Key)
- `warning_level` (Level 1/2/3)
- `note`, `status` (active/resolved/closed)
- `issued_date`, `resolved_date`

---

## 🎯 How to Use

### Adding a Fine

**Session Version (fines.php):**
1. Enter student name
2. Enter book title
3. Enter fine amount in DKK
4. Add reason (optional, defaults to "Overdue return")
5. Click **Save Fine**

**Database Version (fines-db.php):**
1. Select student from dropdown
2. Select book from dropdown (or enter manually)
3. Enter fine amount in DKK
4. Add reason
5. Click **Save Fine to Database**

### Adding a Warning

**Session Version (warnings.php):**
1. Enter student name
2. Select warning level (1, 2, or 3)
3. Add warning note (optional)
4. Click **Record Warning**

**Database Version (warnings-db.php):**
1. Select student from dropdown
2. Select warning level (1, 2, or 3)
3. Add detailed note
4. Click **Record Warning to Database**

---

## 🎨 Color Scheme

```css
Purple:    #a749ff
Orange:    #ff8a3d
White:     #ffffff
Dark BG:   #130f2a
```

Used throughout the UI for consistent branding.

---

## 📱 Responsive Behavior

- Forms automatically stack on mobile devices
- Buttons become full-width on screens < 480px
- Table text resizes for readability
- Navigation links arrange vertically on mobile
- All interactive elements remain touch-friendly

---

## ✅ Input Validation Rules

| Field | Min | Max | Allowed Characters |
|-------|-----|-----|-------------------|
| Student Name | 2 | 100 | a-z, 0-9, space, hyphen, period, æøå |
| Book Title | 2 | 100 | a-z, 0-9, space, hyphen, period, æøå |
| Fine Amount | 0.01 | 100,000 | Numbers, decimals (DKK) |
| Reason | 0 | 100 | a-z, 0-9, space, hyphen, period, æøå |

---

## 🔧 Configuration

Edit `config.php` to change database settings:

```php
define('DB_HOST', 'localhost');     // MySQL host
define('DB_PORT', 3306);            // MySQL port
define('DB_USER', 'root');          // MySQL username
define('DB_PASS', '');              // MySQL password
define('DB_NAME', 'library_management'); // Database name
```

**Default XAMPP Settings:**
- Host: localhost
- User: root
- Password: (empty)
- Port: 3306

---

## 🚨 Troubleshooting

**Q: "Connection failed: Access denied"**  
A: Check if MySQL is running and verify credentials in `config.php`

**Q: "Table doesn't exist"**  
A: Re-import `database.sql` via phpmyadmin

**Q: Characters (æ, ø, å) showing as ???**  
A: Ensure HTML has `<meta charset="UTF-8">` and database uses utf8mb4

**Q: Forms not responsive on mobile**  
A: Clear browser cache (Ctrl+Shift+Delete) and reload

**Q: Session data disappears after browser closes**  
A: This is normal. Use database version (fines-db.php) for persistent storage

---

## 📊 Sample Data Included

The `database.sql` includes:
- 4 sample students (Ahmed, Emma, Sophia, Liam)
- 5 sample books (The Great Gatsby, 1984, etc.)
- 3 sample fine records
- 3 sample warning records

You can delete and add your own through the web interface.

---

## 🔜 Next Steps

1. **Import database.sql** to XAMPP MySQL
2. **Test the session-based UI** (fines.php, warnings.php)
3. **Switch to database version** (fines-db.php, warnings-db.php)
4. **Add more features** like:
   - Edit/Delete fine records
   - Mark fines as paid
   - Generate reports
   - User authentication
   - Student dashboard
   - Export to PDF/Excel

---

## 📞 Support

For issues or questions about database setup, refer to `DATABASE_SETUP.md`

---

**Last Updated:** May 11, 2026
