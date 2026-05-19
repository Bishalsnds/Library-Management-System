<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../src/auth/login.php');
    exit();
}

require_once '../../config.php';

// Get all books
$result = $conn->query("SELECT * FROM books ORDER BY title");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - Library Management System</title>
    <style>
        :root {
            --primary: #a749ff;
            --primary-dark: #8a3ad9;
            --accent: #ff8a3d;
            --text: #1f1230;
            --muted: #6b5c80;
            --border: #e9def8;
            --shadow: 0 2px 8px rgba(10,8,24,0.18);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(167, 73, 255, 0.30), transparent 30%),
                radial-gradient(circle at bottom right, rgba(255, 138, 61, 0.28), transparent 30%),
                linear-gradient(180deg, #1a1030 0%, #130e28 100%);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text);
        }
        
        .navbar {
            background: rgba(255,255,255,0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            font-size: 1.5em;
            font-weight: bold;
            color: var(--primary);
        }
        
        .navbar-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .navbar-menu a {
            text-decoration: none;
            color: var(--text);
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .navbar-menu a:hover {
            background: #f0f0f0;
            color: var(--primary);
        }
        
        .logout-btn {
            background: var(--primary) !important;
            color: white !important;
        }
        
        .logout-btn:hover {
            background: var(--primary-dark) !important;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-title {
            background: rgba(255,255,255,0.96);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }
        
        .page-title h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .book-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--shadow);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        
        .book-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .book-info {
            margin: 8px 0;
            color: var(--muted);
            font-size: 0.95em;
        }
        
        .book-info strong {
            color: var(--text);
        }
        
        .availability {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.9em;
            font-weight: bold;
            margin-top: 12px;
            text-align: center;
        }
        
        .available {
            background: #d4edda;
            color: #155724;
        }
        
        .unavailable {
            background: #f8d7da;
            color: #721c24;
        }
        
        .back-btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        
        .back-btn:hover {
            background: var(--primary-dark);
            transform: translateX(-3px);
        }
        
        .no-books {
            background: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-brand">📚 Library Management System</div>
        <div class="navbar-menu">
            <a href="../../index.php">Back to Dashboard</a>
            <span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="../../src/auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <a href="../../index.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="page-title">
            <h1>📖 Browse Library Books</h1>
            <p>Explore all books available in our library</p>
        </div>
        
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="books-grid">
            <?php while ($book = $result->fetch_assoc()): ?>
            <div class="book-card">
                <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                <div class="book-info">
                    <strong>Author:</strong> <?= htmlspecialchars($book['author']) ?>
                </div>
                <div class="book-info">
                    <strong>Category:</strong> <?= htmlspecialchars($book['category']) ?>
                </div>
                <div class="availability <?= $book['available'] ? 'available' : 'unavailable' ?>">
                    <?= $book['available'] ? '✓ Available' : '✗ Not Available' ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="no-books">
            <h2>No books found</h2>
            <p>There are currently no books in the library.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>
