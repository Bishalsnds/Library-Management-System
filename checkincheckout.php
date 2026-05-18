<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "library_db");
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

$message = "";
$msg_type = "";

// Issue book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["issue"])) {
    $book_id = intval($_POST["book_id"]);
    $member_id = intval($_POST["member_id"]);
    $issue_date = $_POST["issue_date"];
    $due_date = $_POST["due_date"];

    if ($book_id > 0 && $member_id > 0) {
        $check = $conn->query("SELECT available FROM books WHERE book_id = $book_id");
        if ($check && $row = $check->fetch_assoc()) {
            if ($row["available"] > 0) {
                $stmt = $conn->prepare("INSERT INTO transactions (book_id, member_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, 'issued')");
                $stmt->bind_param("iiss", $book_id, $member_id, $issue_date, $due_date);
                if ($stmt->execute()) {
                    $conn->query("UPDATE books SET available = available - 1 WHERE book_id = $book_id");
                    $message = "✅ Book issued successfully!";
                    $msg_type = "success";
                } else {
                    $message = "❌ Issue error: " . $stmt->error;
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
        $message = "❌ Please select valid book and member.";
        $msg_type = "error";
    }
}

// Return book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["return"])) {
    $transaction_id = intval($_POST["transaction_id"]);
    $return_date = $_POST["return_date"];

    if ($transaction_id > 0) {
        $trans = $conn->query("SELECT book_id, due_date FROM transactions WHERE trans_id = $transaction_id AND status = 'issued'");
        if ($trans && $row = $trans->fetch_assoc()) {
            $fine = 0;
            if (strtotime($return_date) > strtotime($row["due_date"])) {
                $days = ceil((strtotime($return_date) - strtotime($row["due_date"])) / 86400);
                $fine = $days * 5; // $5 per day fine
            }
            $conn->query("UPDATE transactions SET return_date = '$return_date', status = 'returned', fine = $fine WHERE trans_id = $transaction_id");
            $conn->query("UPDATE books SET available = available + 1 WHERE book_id = {$row["book_id"]}");
            $message = "✅ Book returned successfully! " . ($fine > 0 ? "Fine: $fine" : "No fine.");
            $msg_type = "success";
        } else {
            $message = "❌ Transaction not found or already returned.";
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
    <title>Library Management System</title>
    <style>
        :root {
            --primary: #28a745;
            --primary-soft: #ecfdf5;
            --surface: #ffffff;
            --surface-alt: #f8fafc;
            --text: #1f2937;
            --muted: #4b5563;
            --border: #e5e7eb;
            --shadow: 0 18px 50px rgba(15,23,42,0.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #eef2ff; min-height: 100vh; color: var(--text); padding: 0; }
        .page { max-width: 1200px; margin: 0 auto; padding: 28px 20px 60px; }
        .card-wrap { background: rgba(255,255,255,0.98); border-radius: 28px; box-shadow: var(--shadow); padding: 32px; border: 1px solid rgba(229,231,235,.9); }
        .logo {
            display: block;
            margin: 0 auto 20px;
            max-width: 170px;
            height: auto;
        }
        h1 { color: #333; margin-bottom: 20px; text-align: center; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h2 { color: #28a745; margin-bottom: 15px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 5px; color: #333; font-weight: 500; }
        input[type="text"], input[type="number"], input[type="date"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="text"]:focus, input[type="number"]:focus, input[type="date"]:focus, select:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.3);
        }
        .row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
        @media (max-width: 900px) { .row { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 600px) { .row { grid-template-columns: 1fr; } }
        button { 
            background: #28a745; 
            color: white; 
            padding: 12px 20px; 
            border: none; 
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            width: 100%;
            transition: background 0.3s;
        }
        button:hover { background: #218838; }
        button:active { background: #1e7e34; }
        .books-list { margin-top: 20px; }
        .book-item {
            background: #f9f9f9;
            padding: 12px;
            margin-bottom: 10px;
            border-left: 4px solid #28a745;
            border-radius: 4px;
        }
        .book-item strong { color: #28a745; }
        .book-info { font-size: 13px; color: #666; margin-top: 5px; }
        .issued-books { margin-top: 15px; }
        .issue-item {
            background: #f9f9f9;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 4px;
            border-left: 4px solid #28a745;
        }
        .issue-item.overdue { border-left-color: #dc3545; background: #fff5f5; }
        .issue-item.due-soon { border-left-color: #ffc107; background: #fffbf0; }
        .issue-item strong { color: #28a745; }
        .issue-info { font-size: 12px; color: #666; margin-top: 5px; }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .search-container button {
            padding: 10px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-container button:hover { background: #0056b3; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #28a745;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 2em;
            color: #28a745;
        }
        .stat-card p {
            margin: 5px 0 0 0;
            color: #666;
            font-weight: 500;
        }
        .overdue-alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="page">
    <div class="card-wrap">
    <img src="logo.jpg" alt="Library Logo" class="logo">
    <h1>📚 Library Management System</h1>
    
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="checkincheckout.php">📖 Check In/Out</a>
        <a href="manage.php">👥 Manage Members & Books</a>
    </div>
    
    <?php if ($message): ?>
        <div class="message <?= $msg_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <?php
        $total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
        $available_books = $conn->query("SELECT SUM(available) as count FROM books")->fetch_assoc()['count'];
        $issued_books = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE status = 'issued'")->fetch_assoc()['count'];
        $overdue_books = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE status = 'issued' AND due_date < CURDATE()")->fetch_assoc()['count'];
        ?>
        <div class="stat-card">
            <h3><?= $total_books ?></h3>
            <p>Total Books</p>
        </div>
        <div class="stat-card">
            <h3><?= $available_books ?></h3>
            <p>Available Books</p>
        </div>
        <div class="stat-card">
            <h3><?= $issued_books ?></h3>
            <p>Books Issued</p>
        </div>
        <div class="stat-card" style="border-left-color: #dc3545;">
            <h3 style="color: #dc3545;"><?= $overdue_books ?></h3>
            <p>Overdue Books</p>
        </div>
    </div>

    <?php if ($overdue_books > 0): ?>
        <div class="overdue-alert">
            ⚠️ <strong>Alert:</strong> There are <?= $overdue_books ?> overdue book(s). Please check the "Currently Issued Books" section below.
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Issue Book Form -->
        <div class="card">
            <h2>📘 Issue Book</h2>
            <form method="post">
                <div class="form-group">
                    <label for="issue_book">Select Book:</label>
                    <select id="issue_book" name="book_id" required>
                        <option value="">-- Select a Book --</option>
                        <?php
                        $books = $conn->query("SELECT book_id, title, author, available FROM books WHERE available > 0 ORDER BY title");
                        if ($books) {
                            while ($b = $books->fetch_assoc()) {
                                echo "<option value='{$b['book_id']}'>{$b['title']} by {$b['author']} (Available: {$b['available']})</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="issue_member">Select Member:</label>
                    <select id="issue_member" name="member_id" required>
                        <option value="">-- Select Member --</option>
                        <?php
                        $members = $conn->query("SELECT member_id, name FROM members WHERE active = 1 ORDER BY name");
                        if ($members) {
                            while ($m = $members->fetch_assoc()) {
                                echo "<option value='{$m['member_id']}'>{$m['name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="issue_date">Issue Date:</label>
                    <input type="date" id="issue_date" name="issue_date" value="<?= date('Y-m-d') ?>" required>
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
            <form method="post">
                <div class="form-group">
                    <label for="return_transaction">Select Issued Book:</label>
                    <select id="return_transaction" name="transaction_id" required>
                        <option value="">-- Select Book to Return --</option>
                        <?php
                        $issued = $conn->query("SELECT t.trans_id, b.title, b.author, m.name, t.issue_date, t.due_date
                                               FROM transactions t
                                               JOIN books b ON t.book_id = b.book_id
                                               JOIN members m ON t.member_id = m.member_id
                                               WHERE t.status = 'issued'
                                               ORDER BY t.issue_date DESC");
                        if ($issued) {
                            while ($i = $issued->fetch_assoc()) {
                                $due_status = (strtotime($i['due_date']) < time()) ? ' (OVERDUE)' : '';
                                echo "<option value='{$i['trans_id']}'>{$i['title']} by {$i['author']} - Issued to: {$i['name']}{$due_status}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="return_date">Return Date:</label>
                    <input type="date" id="return_date" name="return_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <button type="submit" name="return">Return Book</button>
            </form>
        </div>

        <!-- Current Issued Books -->
        <div class="card">
            <h2>📚 Currently Issued Books</h2>
            <div class="issued-books">
                <?php
                $current_issued = $conn->query("SELECT b.title, b.author, m.name, t.issue_date, t.due_date,
                                               DATEDIFF(t.due_date, CURDATE()) as days_left
                                               FROM transactions t
                                               JOIN books b ON t.book_id = b.book_id
                                               JOIN members m ON t.member_id = m.member_id
                                               WHERE t.status = 'issued'
                                               ORDER BY t.due_date ASC");

                if ($current_issued && $current_issued->num_rows > 0) {
                    while ($issue = $current_issued->fetch_assoc()) {
                        $status_class = $issue['days_left'] < 0 ? 'overdue' : ($issue['days_left'] <= 3 ? 'due-soon' : 'normal');
                        $status_text = $issue['days_left'] < 0 ? 'OVERDUE' : ($issue['days_left'] . ' days left');

                        echo "<div class='issue-item {$status_class}'>
                            <strong>{$issue['title']}</strong> by {$issue['author']}
                            <div class='issue-info'>
                                Issued to: {$issue['name']} | Due: {$issue['due_date']} | {$status_text}
                            </div>
                        </div>";
                    }
                } else {
                    echo "<p style='color: #999; text-align: center;'>No books currently issued.</p>";
                }
                ?>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>