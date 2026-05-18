# Learning Management System (LMS)

## Project Structure

```
Library Management System/
├── public/                     # Web root - entry point for web server
│   ├── index.php              # Main entry point
│   ├── assets/
│   │   ├── css/               # Stylesheets (move style.css here)
│   │   └── images/            # Images and media
│   ├── uploads/               # User uploads (books, profiles, etc.)
│   └── [static files]
│
├── src/                        # Source code
│   ├── auth/            # Handles authentication
│  
├── database/                   # Database related files
│   ├── migrations/             # Database migrations
│   └── database.sql            # Initial schema (move from root)
│
└── README.md                   # Project documentation

```

