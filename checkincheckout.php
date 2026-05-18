<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: src/auth/login.php');
    exit();
}

require_once 'config.php';

$message = "";
$msg_type = "";

// Issue book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["issue"])) {
    $book_id = intval($_POST["book_id"] ?? 0);
    $user_id = intval($_POST["user_id"] ?? 0);
    $due_date = $_POST["due_date"] ?? '';

    if ($book_id > 0 && $user_id > 0) {
        // Check if book is available
        $check = $conn->query("SELECT available FROM books WHERE id = $book_id");
        if ($check && $row = $check->fetch_assoc()) {
            if ($row["available"]) {
                $stmt = $conn->prepare("INSERT INTO transactions (user_id, book_id, transaction_type, due_date) VALUES (?, ?, 'checkout', ?)");
                $stmt->bind_param("iis", $user_id, $book_id, $due_date);
                if ($stmt->execute()) {
                    $conn->query("UPDATE books SET available = 0 WHERE id = $book_id");
                    $message = "✅ Book issued successfully!";
                    $msg_type = "success";
                } else {
                    $message = "❌ Error: " . $stmt->error;
                    $msg_type = "error";
                }
                $stmt->close();
            } else {
                $message = "❌ Book not available.";
                $msg_type = "error";
            }
        } else {
            $message = "❌ Book not found.";
            $msg_type = "error";
        }
    } else {
        $message = "❌ Please select valid book and user.";
        $msg_type = "error";
    }
}

// Return book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["return"])) {
    $transaction_id = intval($_POST["transaction_id"] ?? 0);

    if ($transaction_id > 0) {
        $trans = $conn->query("SELECT book_id FROM transactions WHERE id = $transaction_id AND transaction_type = 'checkout'");
        if ($trans && $row = $trans->fetch_assoc()) {
            $return_date = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("UPDATE transactions SET return_date = ?, transaction_type = 'checkin' WHERE id = ?");
            $stmt->bind_param("si", $return_date, $transaction_id);
            $stmt->execute();
            $stmt->close();
            
            $conn->query("UPDATE books SET available = 1 WHERE id = {$row["book_id"]}");
            $message = "✅ Book returned successfully!";
            $msg_type = "success";
        } else {
            $message = "❌ Transaction not found.";
            $msg_type = "error";
        }
    } else {
        $message = "❌ Please select a valid transaction.";
        $msg_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check In/Out - Library Management System</title>
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 2px 8px rgba(0,0,0,0.1);
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
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        
        .card h2 {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.3em;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text);
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }
        
        input:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        button {
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            width: 100%;
            transition: background 0.3s;
        }
        
        button:hover {
            background: var(--primary-dark);
        }
        
        .back-btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: var(--primary-dark);
            transform: translateX(-3px);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-brand">📚 Library Management System</div>
        <div class="navbar-menu">
            <a href="index.php">Back to Dashboard</a>
            <span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="src/auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <a href="index.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="page-title">
            <h1>📖 Check In/Out Books</h1>
            <p>Manage book borrowings and returns</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= $msg_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <div class="cards-grid">
            <!-- Issue Book Form -->
            <div class="card">
                <h2>📘 Issue Book</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="book_id">Select Book:</label>
                        <select id="book_id" name="book_id" required>
                            <option value="">-- Select a Book --</option>
                            <?php
                            $books = $conn->query("SELECT id, title, author FROM books WHERE available = 1 ORDER BY title");
                            if ($books) {
                                while ($b = $books->fetch_assoc()) {
                                    echo "<option value='{$b['id']}'>{$b['title']} by {$b['author']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user_id">Select User:</label>
                        <select id="user_id" name="user_id" required>
                            <option value="">-- Select User --</option>
                            <?php
                            $users = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE status = 'active' ORDER BY first_name");
                            if ($users) {
                                while ($u = $users->fetch_assoc()) {
                                    echo "<option value='{$u['id']}'>{$u['name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="due_date">Due Date:</label>
                        <input type="date" id="due_date" name="due_date" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
                    </div>
                    <button type="submit" name="issue">Issue Book</button>
                </form>
            </div>

            <!-- Return Book Form -->
            <div class="card">
                <h2>📙 Return Book</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="transaction_id">Select Issued Book:</label>
                        <select id="transaction_id" name="transaction_id" required>
                            <option value="">-- Select Book to Return --</option>
                            <?php
                            $issued = $conn->query("SELECT t.id, b.title, b.author, u.first_name, u.last_name
                                                   FROM transactions t
                                                   JOIN books b ON t.book_id = b.id
                                                   JOIN users u ON t.user_id = u.id
                                                   WHERE t.transaction_type = 'checkout'
                                                   ORDER BY t.transaction_date DESC");
                            if ($issued) {
                                while ($i = $issued->fetch_assoc()) {
                                    echo "<option value='{$i['id']}'>{$i['title']} by {$i['author']} - Issued to: {$i['first_name']} {$i['last_name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="return">Return Book</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
