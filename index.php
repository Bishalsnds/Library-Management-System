<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: src/auth/login.php');
    exit();
}

require_once 'config.php';

// Get statistics
$total_books = $conn->query("SELECT COUNT(*) as count FROM books");
$total_users = $conn->query("SELECT COUNT(*) as count FROM users");
$available_books = $conn->query("SELECT COUNT(*) as count FROM books WHERE available = 1");

$books_count = $total_books ? $total_books->fetch_assoc()['count'] : 0;
$users_count = $total_users ? $total_users->fetch_assoc()['count'] : 0;
$available_count = $available_books ? $available_books->fetch_assoc()['count'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Dashboard</title>
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --accent: #28a745;
            --surface: #ffffff;
            --surface-alt: #f6f8ff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 18px 50px rgba(102, 126, 234, 0.14);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: var(--surface-alt);
            color: var(--primary);
        }
        
        .logout-btn {
            background: var(--primary) !important;
            color: white !important;
        }
        
        .logout-btn:hover {
            background: var(--primary-dark) !important;
        }
        
        .page {
            position: relative;
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px 20px;
        }
        
        .card-wrap {
            background: rgba(255,255,255,0.96);
            border-radius: 28px;
            box-shadow: var(--shadow);
            padding: 32px;
            border: 1px solid rgba(229,231,235,.7);
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        
        .welcome-text {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
            font-size: 1.1em;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .menu-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: white;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .menu-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 35px rgba(0,0,0,0.18);
        }
        
        .menu-card .icon {
            font-size: 3em;
            margin-bottom: 10px;
        }
        
        .menu-card h2 {
            font-size: 1.3em;
            margin-bottom: 10px;
        }
        
        .menu-card p {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .stats {
            background: #f8fafc;
            padding: 30px;
            border-radius: 22px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 22px;
            margin-top: 30px;
            border: 1px solid rgba(229,231,235,.95);
        }
        
        .stat {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #667eea;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-brand">📚 Library Management System</div>
        <div class="navbar-menu">
            <span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= htmlspecialchars($_SESSION['user_role']) ?>)</span>
            <a href="src/auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="page">
        <div class="card-wrap">
            <h1>Dashboard</h1>
            <p class="welcome-text">Welcome to Library Management System</p>

            <div class="menu-grid">
                <a href="checkincheckout.php" class="menu-card">
                    <div class="icon">📖</div>
                    <h2>Check In/Out Books</h2>
                    <p>Manage book borrowings</p>
                </a>

                <a href="public/pages/books.php" class="menu-card">
                    <div class="icon">📕</div>
                    <h2>Browse Books</h2>
                    <p>View all available books</p>
                </a>

                <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'librarian'): ?>
                <a href="manage.php" class="menu-card">
                    <div class="icon">⚙️</div>
                    <h2>Manage Books</h2>
                    <p>Add/Edit/Delete books</p>
                </a>
                <?php endif; ?>

                <a href="src/modules/fines/index.php" class="menu-card">
                    <div class="icon">💸</div>
                    <h2>Fines &amp; Warnings</h2>
                    <p>Manage fines, warnings &amp; payments</p>
                </a>

                <a href="http://localhost/phpmyadmin" class="menu-card">
                    <div class="icon">🗄️</div>
                    <h2>Database</h2>
                    <p>View database records</p>
                </a>
            </div>

            <!-- Statistics -->
            <div class="stats">
                <div class="stat">
                    <div class="stat-number"><?= $books_count ?></div>
                    <div class="stat-label">Total Books</div>
                </div>

                <div class="stat">
                    <div class="stat-number"><?= $available_count ?></div>
                    <div class="stat-label">Available Books</div>
                </div>

                <div class="stat">
                    <div class="stat-number"><?= $users_count ?></div>
                    <div class="stat-label">Users</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>