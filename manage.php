<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli("localhost", "root", "", "library_db");
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

$message = "";
$msg_type = "";

// Add new member
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_member"])) {
    $name = trim($_POST["member_name"]);
    $email = trim($_POST["member_email"]);
    $phone = trim($_POST["member_phone"]);

    if ($name) {
        $stmt = $conn->prepare("INSERT INTO members (name, email, phone, active) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $name, $email, $phone);
        if ($stmt->execute()) {
            $message = "✅ Member added successfully!";
            $msg_type = "success";
        } else {
            $message = "❌ Error adding member: " . $stmt->error;
            $msg_type = "error";
        }
        $stmt->close();
    } else {
        $message = "❌ Please enter member name.";
        $msg_type = "error";
    }
}

// Add new book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_book"])) {
    $title = trim($_POST["title"]);
    $author = trim($_POST["author"]);
    $isbn = trim($_POST["isbn"]);
    $quantity = intval($_POST["quantity"]);

    if ($title && $author && $quantity > 0) {
        $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, available, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $title, $author, $isbn, $quantity, $quantity);
        if ($stmt->execute()) {
            $message = "✅ Book added successfully!";
            $msg_type = "success";
        } else {
            $message = "❌ Error adding book: " . $stmt->error;
            $msg_type = "error";
        }
        $stmt->close();
    } else {
        $message = "❌ Please fill all fields correctly.";
        $msg_type = "error";
    }
}

// Delete member
if (isset($_GET["delete_member"])) {
    $member_id = intval($_GET["delete_member"]);
    $conn->query("UPDATE members SET active = 0 WHERE member_id = $member_id");
    $message = "✅ Member deactivated!";
    $msg_type = "success";
}

// Delete book
if (isset($_GET["delete_book"])) {
    $book_id = intval($_GET["delete_book"]);
    $conn->query("DELETE FROM books WHERE book_id = $book_id");
    $message = "✅ Book deleted!";
    $msg_type = "success";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library - Manage Members & Books</title>
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
        .nav-links { text-align: center; margin-bottom: 20px; }
        .nav-links a { 
            display: inline-block;
            margin: 0 10px;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .nav-links a:hover { background: #218838; }
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
        .message { padding: 12px; margin-bottom: 20px; border-radius: 5px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h2 { color: #28a745; margin-bottom: 15px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 5px; color: #333; font-weight: 500; }
        input[type="text"], input[type="email"], input[type="tel"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="tel"]:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.3);
        }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 768px) { .row { grid-template-columns: 1fr; } }
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
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th { background: #f0f0f0; padding: 12px; text-align: left; border: 1px solid #ddd; font-weight: bold; }
        table td { padding: 12px; border: 1px solid #ddd; }
        table tr:hover { background: #f9f9f9; }
        .action-btn { 
            display: inline-block;
            padding: 6px 12px;
            margin: 2px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
            border: none;
        }
        .edit-btn { background: #007bff; color: white; }
        .delete-btn { background: #dc3545; color: white; }
        .edit-btn:hover { background: #0056b3; }
        .delete-btn:hover { background: #c82333; }
        .status-active { color: green; font-weight: bold; }
        .status-inactive { color: red; font-weight: bold; }
    </style>
</head>
<body>
<div class="page">
    <div class="card-wrap">
    <img src="logo.jpg" alt="Library Logo" class="logo">
    <h1>📚 Library Management</h1>
    
    <div class="nav-links">
        <a href="index.php">🏠 Home</a>
        <a href="checkincheckout.php">📖 Check In/Out</a>
        <a href="manage.php">👥 Manage Members & Books</a>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <?php
        $total_members = $conn->query("SELECT COUNT(*) as count FROM members WHERE active = 1")->fetch_assoc()['count'];
        $total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
        $available_books = $conn->query("SELECT SUM(available) as count FROM books")->fetch_assoc()['count'];
        $issued_books = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE status = 'issued'")->fetch_assoc()['count'];
        ?>
        <div class="stat-card">
            <h3><?= $total_members ?></h3>
            <p>Active Members</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_books ?></h3>
            <p>Total Books</p>
        </div>
        <div class="stat-card">
            <h3><?= $available_books ?></h3>
            <p>Available Books</p>
        </div>
        <div class="stat-card" style="border-left-color: #ffc107;">
            <h3 style="color: #ffc107;"><?= $issued_books ?></h3>
            <p>Books Issued</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message <?= $msg_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Add Member Form -->
        <div class="card">
            <h2>➕ Add New Member</h2>
            <form method="post">
                <div class="form-group">
                    <label for="member_name">Member Name:</label>
                    <input type="text" id="member_name" name="member_name" required placeholder="Enter full name">
                </div>
                <div class="form-group">
                    <label for="member_email">Email:</label>
                    <input type="email" id="member_email" name="member_email" placeholder="Enter email (optional)">
                </div>
                <div class="form-group">
                    <label for="member_phone">Phone:</label>
                    <input type="tel" id="member_phone" name="member_phone" placeholder="Enter phone (optional)">
                </div>
                <button type="submit" name="add_member">Add Member</button>
            </form>
        </div>

        <!-- Add Book Form -->
        <div class="card">
            <h2>📕 Add New Book</h2>
            <form method="post">
                <div class="form-group">
                    <label for="title">Book Title:</label>
                    <input type="text" id="title" name="title" required placeholder="Enter book title">
                </div>
                <div class="form-group">
                    <label for="author">Author:</label>
                    <input type="text" id="author" name="author" required placeholder="Enter author name">
                </div>
                <div class="form-group">
                    <label for="isbn">ISBN:</label>
                    <input type="text" id="isbn" name="isbn" placeholder="Enter ISBN (optional)">
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="1" value="1" required>
                </div>
                <button type="submit" name="add_book">Add Book</button>
            </form>
        </div>
    </div>

    <!-- Members List -->
    <div class="card">
        <h2>👥 All Members</h2>
        <div class="search-container">
            <input type="text" id="member-search" placeholder="Search members by name, email, or phone..." onkeyup="filterMembers()">
            <button onclick="clearMemberSearch()">Clear</button>
        </div>
        <table id="members-table">
            <thead>
                <tr>
                    <th>Member ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $members = $conn->query("SELECT * FROM members ORDER BY member_id DESC");
                if ($members && $members->num_rows > 0) {
                    while ($member = $members->fetch_assoc()) {
                        $status = $member['active'] == 1 ? '<span class="status-active">✓ Active</span>' : '<span class="status-inactive">✗ Inactive</span>';
                        echo "<tr>
                            <td>#{$member['member_id']}</td>
                            <td>{$member['name']}</td>
                            <td>{$member['email']}</td>
                            <td>{$member['phone']}</td>
                            <td>$status</td>
                            <td>
                                <a href='?delete_member={$member['member_id']}' class='action-btn delete-btn' onclick='return confirm(\"Deactivate this member?\")'>Deactivate</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center; color: #999;'>No members found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Books List -->
    <div class="card">
        <h2>📚 All Books</h2>
        <div class="search-container">
            <input type="text" id="book-search" placeholder="Search books by title or author..." onkeyup="filterBooks()">
            <button onclick="clearBookSearch()">Clear</button>
        </div>
        <table id="books-table">
            <thead>
                <tr>
                    <th>Book ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Available</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $books = $conn->query("SELECT * FROM books ORDER BY book_id DESC");
                if ($books && $books->num_rows > 0) {
                    while ($book = $books->fetch_assoc()) {
                        $color = $book['available'] > 0 ? 'green' : 'red';
                        echo "<tr>
                            <td>#{$book['book_id']}</td>
                            <td>{$book['title']}</td>
                            <td>{$book['author']}</td>
                            <td>{$book['isbn']}</td>
                            <td><span style='color: {$color}; font-weight: bold;'>{$book['available']}</span></td>
                            <td>{$book['total']}</td>
                            <td>
                                <a href='?delete_book={$book['book_id']}' class='action-btn delete-btn' onclick='return confirm(\"Delete this book?\")'>Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center; color: #999;'>No books found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    </div>

<script>
// Search functionality for members
function filterMembers() {
    const input = document.getElementById('member-search');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('members-table');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;

        for (let j = 0; j < cells.length - 1; j++) { // Exclude actions column
            if (cells[j] && cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                match = true;
                break;
            }
        }

        rows[i].style.display = match ? '' : 'none';
    }
}

function clearMemberSearch() {
    document.getElementById('member-search').value = '';
    filterMembers();
}

// Search functionality for books
function filterBooks() {
    const input = document.getElementById('book-search');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('books-table');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;

        for (let j = 0; j < cells.length - 1; j++) { // Exclude actions column
            if (cells[j] && cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                match = true;
                break;
            }
        }

        rows[i].style.display = match ? '' : 'none';
    }
}

function clearBookSearch() {
    document.getElementById('book-search').value = '';
    filterBooks();
}
</script>
</body>
</html>
<?php $conn->close(); ?>
