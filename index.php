<?php
$conn = new mysqli("localhost", "root", "", "library_db");
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Check-in/Check-out System</title>
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
            padding: 0;
            color: var(--text);
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, rgba(102,126,234,0.12), rgba(118,75,162,0.16));
            pointer-events: none;
        }
        .page {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px 60px;
        }
        .card-wrap {
            background: rgba(255,255,255,0.96);
            border-radius: 28px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            padding: 32px;
            border: 1px solid rgba(229,231,235,.7);
        }
        .logo {
            display: block;
            margin: 0 auto 20px;
            max-width: 170px;
            height: auto;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .subtitle {
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
<div class="page">
    <div class="card-wrap">
        <img src="logo.jpg" alt="Library Logo" class="logo">
        <h1>📚 Library System</h1>
    <p class="subtitle">Manage Books & Members Efficiently</p>

    <div class="menu-grid">
        <a href="manage.php" class="menu-card">
            <div class="icon">👥</div>
            <h2>Manage Members & Books</h2>
            <p>Add members and books to library</p>
        </a>

        <a href="checkincheckout.php" class="menu-card">
            <div class="icon">📖</div>
            <h2>Issue & Return Books</h2>
            <p>Check-in and check-out books</p>
        </a>

        <a href="http://localhost/phpmyadmin" class="menu-card">
            <div class="icon">🗄️</div>
            <h2>Database Management</h2>
            <p>View detailed database records</p>
        </a>
    </div>

    <!-- Statistics -->
    <div class="stats">
        <?php
        $total_books = $conn->query("SELECT COUNT(*) as count FROM books");
        $total_members = $conn->query("SELECT COUNT(*) as count FROM members WHERE active = 1");
        $issued_books = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE status = 'issued'");

        $books_count = $total_books ? $total_books->fetch_assoc()['count'] : 0;
        $members_count = $total_members ? $total_members->fetch_assoc()['count'] : 0;
        $issued_count = $issued_books ? $issued_books->fetch_assoc()['count'] : 0;
        ?>

        <div class="stat">
            <div class="stat-number"><?= $books_count ?></div>
            <div class="stat-label">Total Books</div>
        </div>

        <div class="stat">
            <div class="stat-number"><?= $members_count ?></div>
            <div class="stat-label">Active Members</div>
        </div>

        <div class="stat">
            <div class="stat-number"><?= $issued_count ?></div>
            <div class="stat-label">Books Issued</div>
        </div>
    </div>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>