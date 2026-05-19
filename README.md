# Library Management System

A PHP + MySQL library management system with login, book management, check-in/check-out, and a fines & warnings module.

---

## Quick Start (after `git clone`)

### 1. Install XAMPP

Download from [apachefriends.org](https://www.apachefriends.org/) and install. Start **Apache** and **MySQL** from the XAMPP control panel.

### 2. Drop the project into XAMPP's `htdocs/`

```bash
# macOS
mv Library-Management-System /Applications/XAMPP/xamppfiles/htdocs/

# Windows
move Library-Management-System C:\xampp\htdocs\
```

### 3. Run the setup script (one click)

Open this URL in your browser:

```
http://localhost/Library-Management-System/setup.php
```

It will:
- Create both databases (`library_db` and `library_management`)
- Import the schemas from `database/`
- Seed a working admin user

When you see "Setup complete!", click "Open the app".

### 4. Log in

| Role | Email | Password |
|------|-------|----------|
| Student | `john@gmail.com` | `password123` |
| Student | `jane@gmail.com` | `password123` |
| Librarian | `librarian@gmail.com` | `password123` |
| Admin | `admin@gmail.com` | `password123` |

---

## Troubleshooting

**"Requested URL was not found"** — You probably typed the wrong path. The URL is based on the folder name inside `htdocs/`. If you cloned to `htdocs/Library-Management-System`, open `http://localhost/Library-Management-System/`, not just `http://localhost/library/`.

**Database connection failed** — XAMPP MySQL isn't running, or the MySQL `root` user has a password set. Default XAMPP has no password. If yours does, edit `config.php` and `src/modules/fines/config.php` to add it.

**Login fails** — Re-run `setup.php`. It's safe to run multiple times and will reset the test passwords.

---

## Project structure

```
Library-Management-System/
├── index.php                  # Entry point — redirects to login or dashboard
├── setup.php                  # One-shot setup for fresh clones
├── config.php                 # Main database (library_db) connection
├── db.php                     # Legacy DB helper used by api.php
├── api.php                    # JSON API (legacy, used by archived AJAX UI)
├── manage.php                 # Admin: manage members & books
├── checkincheckout.php        # Issue / return books
├── .htaccess                  # Apache config
│
├── src/
│   ├── auth/                  # Login, signup, logout, email verification
│   └── modules/
│       └── fines/             # Fines, warnings, payments module (its own DB)
│
├── public/
│   ├── pages/books.php        # Browse books page
│   └── assets/
│       ├── css/style.css      # Auth pages stylesheet
│       ├── css/theme.css      # Shared color tokens
│       └── images/logo.png.jpg
│
├── database/
│   ├── complete_setup.sql     # library_db schema + seed users + sample books
│   ├── fines_warnings.sql     # library_management schema for fines module
│   └── sample_books.sql
│
├── docs/                      # Module-specific documentation
├── archive/                   # Older / unused PHP files kept for reference
├── scripts/                   # Helper scripts (e.g. push_to_github.bat)
└── README.md                  # This file
```

---

## Features

- **Authentication** — Login, signup, role-based access (admin / librarian / student)
- **Book management** — Add, edit, delete books and members (admin only)
- **Check in/out** — Issue books to members, return books, track due dates
- **Browse books** — All users can view the catalog
- **Fines & warnings module** — Record fines (DKK), track warnings by level, accept payments via Google Pay / Mobile Pay
- **Unified theme** — Purple `#a749ff` + orange `#ff8a3d` on dark navy across all pages

## Tech stack

- PHP 8.x + MySQL (XAMPP)
- Vanilla HTML / CSS / JavaScript — no frameworks
- bcrypt for password hashing
- Session-based authentication
