<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: src/auth/login.php');
    exit();
}

if (!in_array($_SESSION['user_role'], ['admin', 'librarian'])) {
    header('Location: index.php');
    exit();
}

require_once 'config.php';

$message = "";
$msg_type = "";
$editMode = false;
$editBook = null;

// Load book for editing
$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
if ($editId > 0) {
    $res = $conn->query("SELECT * FROM books WHERE id = $editId");
    if ($res && $res->num_rows > 0) {
        $editBook = $res->fetch_assoc();
        $editMode = true;
    }
}

// Add book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title    = sanitize($_POST['title']    ?? '');
    $author   = sanitize($_POST['author']   ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $available = isset($_POST['available']) ? 1 : 0;

    if ($title && $author && $category) {
        $stmt = $conn->prepare("INSERT INTO books (title, author, category, available) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $author, $category, $available);
        if ($stmt->execute()) {
            $message  = "Book added successfully!";
            $msg_type = "success";
        } else {
            $message  = "Error adding book: " . $stmt->error;
            $msg_type = "error";
        }
        $stmt->close();
    } else {
        $message  = "Please fill in all required fields (Title, Author, Category).";
        $msg_type = "error";
    }
}

// Update book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    $book_id  = intval($_POST['book_id']    ?? 0);
    $title    = sanitize($_POST['title']    ?? '');
    $author   = sanitize($_POST['author']   ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $available = isset($_POST['available']) ? 1 : 0;

    if ($book_id > 0 && $title && $author && $category) {
        $stmt = $conn->prepare("UPDATE books SET title=?, author=?, category=?, available=? WHERE id=?");
        $stmt->bind_param("sssii", $title, $author, $category, $available, $book_id);
        if ($stmt->execute()) {
            $message  = "Book updated successfully!";
            $msg_type = "success";
            $editMode = false;
            $editBook = null;
            header("Refresh: 1; url=manage.php");
        } else {
            $message  = "Error updating book: " . $stmt->error;
            $msg_type = "error";
        }
        $stmt->close();
    } else {
        $message  = "Please fill in all required fields.";
        $msg_type = "error";
    }
}

// Delete book
if (isset($_GET['delete_book'])) {
    $book_id = intval($_GET['delete_book']);
    if ($book_id > 0) {
        if ($conn->query("DELETE FROM books WHERE id = $book_id")) {
            $message  = "Book deleted successfully!";
            $msg_type = "success";
        } else {
            $message  = "Error deleting book: " . $conn->error;
            $msg_type = "error";
        }
    }
}

// Toggle user active/inactive
if (isset($_GET['toggle_user'])) {
    $user_id = intval($_GET['toggle_user']);
    if ($user_id > 0 && $user_id !== (int)$_SESSION['user_id']) {
        $u = $conn->query("SELECT status FROM users WHERE id = $user_id");
        if ($u && $u->num_rows > 0) {
            $cur = $u->fetch_assoc()['status'];
            $new = $cur === 'active' ? 'inactive' : 'active';
            $conn->query("UPDATE users SET status='$new' WHERE id=$user_id");
            $message  = "User status changed to $new.";
            $msg_type = "success";
        }
    }
}

// Stats
$stat_total     = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'] ?? 0;
$stat_avail     = $conn->query("SELECT COUNT(*) as c FROM books WHERE available = 1")->fetch_assoc()['c'] ?? 0;
$stat_users     = $conn->query("SELECT COUNT(*) as c FROM users WHERE status = 'active'")->fetch_assoc()['c'] ?? 0;
$co_res         = $conn->query("SELECT COUNT(*) as c FROM transactions WHERE transaction_type = 'checkout'");
$stat_checked   = $co_res ? $co_res->fetch_assoc()['c'] : 0;

// Fetch all books for the list
$books_res = $conn->query("SELECT * FROM books ORDER BY id DESC");
$books = [];
if ($books_res) {
    while ($row = $books_res->fetch_assoc()) $books[] = $row;
}

// Fetch all users
$users_res = $conn->query("SELECT id, first_name, last_name, email, student_id, role, status, created_at FROM users ORDER BY id DESC");
$users = [];
if ($users_res) {
    while ($row = $users_res->fetch_assoc()) $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books – Library Management System</title>
    <style>
        :root {
            --primary: #a749ff;
            --primary-dark: #8a3ad9;
            --accent: #ff8a3d;
            --accent-dark: #e6712a;
            --surface: #ffffff;
            --surface-alt: #faf3ff;
            --text: #1f1230;
            --muted: #6b5c80;
            --border: #e9def8;
            --shadow: 0 18px 50px rgba(167,73,255,0.18);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(167,73,255,0.30), transparent 30%),
                radial-gradient(circle at bottom right, rgba(255,138,61,0.28), transparent 30%),
                linear-gradient(180deg, #1a1030 0%, #130e28 100%);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text);
        }

        /* Navbar */
        .navbar {
            background: rgba(255,255,255,0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 16px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-brand { font-size: 1.4em; font-weight: bold; color: var(--primary); }
        .navbar-menu { display: flex; gap: 16px; align-items: center; }
        .navbar-menu a {
            text-decoration: none;
            color: var(--text);
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.2s;
            font-size: 14px;
        }
        .navbar-menu a:hover { background: var(--surface-alt); color: var(--primary); }
        .logout-btn { background: var(--primary) !important; color: white !important; }
        .logout-btn:hover { background: var(--primary-dark) !important; }

        /* Layout */
        .page { max-width: 1200px; margin: 30px auto; padding: 0 20px 60px; }

        .back-btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.2s;
        }
        .back-btn:hover { background: var(--primary-dark); transform: translateX(-3px); }

        /* Alert */
        .alert {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-error   { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: rgba(255,255,255,0.96);
            padding: 22px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-align: center;
            border-left: 4px solid var(--primary);
        }
        .stat-card.accent { border-left-color: var(--accent); }
        .stat-number { font-size: 2.2em; font-weight: bold; color: var(--primary); }
        .stat-card.accent .stat-number { color: var(--accent); }
        .stat-label  { color: var(--muted); font-size: 0.88em; margin-top: 4px; }

        /* Section cards */
        .card {
            background: rgba(255,255,255,0.97);
            padding: 26px;
            margin-bottom: 24px;
            border-radius: 14px;
            box-shadow: var(--shadow);
        }
        .card h2 { color: var(--primary); margin-bottom: 18px; font-size: 1.25em; }

        /* Forms */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--text); }
        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--border);
            border-radius: 6px;
            font-size: 14px;
            color: var(--text);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        .form-group input[type="text"]:focus,
        .form-group select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(167,73,255,0.12);
        }
        .checkbox-group { display: flex; align-items: center; gap: 8px; margin-top: 6px; }
        .checkbox-group input { width: 16px; height: 16px; cursor: pointer; }
        .checkbox-group label { margin: 0; font-size: 14px; font-weight: 500; cursor: pointer; }

        .btn { padding: 11px 20px; border: none; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-primary  { background: var(--primary); color: white; }
        .btn-primary:hover  { background: var(--primary-dark); }
        .btn-accent   { background: var(--accent); color: white; }
        .btn-accent:hover   { background: var(--accent-dark); }
        .btn-secondary { background: #f0eff5; color: var(--text); }
        .btn-secondary:hover { background: var(--border); }
        .btn-group { display: flex; gap: 10px; margin-top: 6px; flex-wrap: wrap; }

        /* Table */
        .search-row { display: flex; gap: 10px; margin-bottom: 14px; align-items: center; }
        .search-row input {
            flex: 1;
            padding: 9px 12px;
            border: 1.5px solid var(--border);
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-row input:focus { border-color: var(--primary); }
        .search-row button { padding: 9px 16px; background: var(--accent); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }
        .search-row button:hover { background: var(--accent-dark); }

        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        thead th {
            background: #f7f3ff;
            padding: 12px 14px;
            text-align: left;
            font-weight: 700;
            color: var(--text);
            border-bottom: 2px solid var(--border);
        }
        tbody td { padding: 12px 14px; border-bottom: 1px solid #f0ebfa; vertical-align: middle; }
        tbody tr:hover { background: #faf7ff; }
        tbody tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-available { background: #d4edda; color: #155724; }
        .badge-unavailable { background: #f8d7da; color: #721c24; }
        .badge-active   { background: #d4edda; color: #155724; }
        .badge-inactive { background: #f8d7da; color: #721c24; }
        .badge-admin    { background: #e8d5ff; color: #6d28d9; }
        .badge-librarian { background: #dbeafe; color: #1d4ed8; }
        .badge-student  { background: #f3f4f6; color: #374151; }

        .action-link {
            display: inline-block;
            padding: 5px 11px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            margin: 2px;
            transition: opacity 0.2s;
        }
        .action-link:hover { opacity: 0.82; }
        .action-edit   { background: var(--accent);   color: white; }
        .action-delete { background: #dc3545;          color: white; }
        .action-toggle { background: #6b7280;          color: white; }

        .empty-row td { text-align: center; color: var(--muted); padding: 30px; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="navbar-brand">📚 Library Management System</div>
    <div class="navbar-menu">
        <span style="font-size:13px;color:var(--muted)">
            <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= htmlspecialchars($_SESSION['user_role']) ?>)
        </span>
        <a href="index.php">Dashboard</a>
        <a href="src/auth/logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="page">
    <a href="index.php" class="back-btn">← Back to Dashboard</a>

    <?php if ($message): ?>
        <div class="alert alert-<?= $msg_type === 'success' ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $stat_total ?></div>
            <div class="stat-label">Total Books</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stat_avail ?></div>
            <div class="stat-label">Available Books</div>
        </div>
        <div class="stat-card accent">
            <div class="stat-number"><?= $stat_checked ?></div>
            <div class="stat-label">Checked Out</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stat_users ?></div>
            <div class="stat-label">Active Users</div>
        </div>
    </div>

    <!-- Add / Edit Book Form -->
    <div class="card">
        <h2><?= $editMode ? '✏️ Edit Book' : '📕 Add New Book' ?></h2>
        <form method="POST" action="manage.php<?= $editMode ? '?edit=' . $editBook['id'] : '' ?>">
            <?php if ($editMode): ?>
                <input type="hidden" name="book_id" value="<?= $editBook['id'] ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="title">Title <span style="color:#dc3545">*</span></label>
                    <input type="text" id="title" name="title" placeholder="Book title"
                           value="<?= htmlspecialchars($editBook['title'] ?? ($_POST['title'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="author">Author <span style="color:#dc3545">*</span></label>
                    <input type="text" id="author" name="author" placeholder="Author name"
                           value="<?= htmlspecialchars($editBook['author'] ?? ($_POST['author'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="category">Category <span style="color:#dc3545">*</span></label>
                    <input type="text" id="category" name="category" placeholder="e.g. Fiction, Programming, Science"
                           value="<?= htmlspecialchars($editBook['category'] ?? ($_POST['category'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label>Availability</label>
                    <div class="checkbox-group">
                        <input type="checkbox" id="available" name="available"
                               <?= (!$editMode || ($editBook['available'] ?? true)) ? 'checked' : '' ?>>
                        <label for="available">Mark as Available</label>
                    </div>
                </div>
            </div>

            <div class="btn-group">
                <?php if ($editMode): ?>
                    <button type="submit" name="update_book" class="btn btn-primary">Save Changes</button>
                    <a href="manage.php" class="btn btn-secondary">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_book" class="btn btn-primary">Add Book</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Books List -->
    <div class="card">
        <h2>📚 All Books</h2>
        <div class="search-row">
            <input type="text" id="book-search" placeholder="Search by title, author, or category…" oninput="filterTable('book-search','books-table')">
            <button onclick="clearSearch('book-search','books-table')">Clear</button>
        </div>
        <table id="books-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($books) === 0): ?>
                    <tr class="empty-row"><td colspan="7">No books in the library yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?= $book['id'] ?></td>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['category']) ?></td>
                        <td>
                            <?php if ($book['available']): ?>
                                <span class="badge badge-available">✓ Available</span>
                            <?php else: ?>
                                <span class="badge badge-unavailable">✗ Checked Out</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('Y-m-d', strtotime($book['created_at'])) ?></td>
                        <td>
                            <a href="manage.php?edit=<?= $book['id'] ?>" class="action-link action-edit">Edit</a>
                            <a href="manage.php?delete_book=<?= $book['id'] ?>" class="action-link action-delete"
                               onclick="return confirm('Delete \'<?= htmlspecialchars(addslashes($book['title'])) ?>\'? This cannot be undone.')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Users List -->
    <div class="card">
        <h2>👥 Registered Users</h2>
        <div class="search-row">
            <input type="text" id="user-search" placeholder="Search by name, email, or student ID…" oninput="filterTable('user-search','users-table')">
            <button onclick="clearSearch('user-search','users-table')">Clear</button>
        </div>
        <table id="users-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Student ID</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) === 0): ?>
                    <tr class="empty-row"><td colspan="7">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['student_id'] ?? '—') ?></td>
                        <td><span class="badge badge-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></td>
                        <td>
                            <span class="badge badge-<?= $user['status'] ?>"><?= ucfirst($user['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="manage.php?toggle_user=<?= $user['id'] ?>" class="action-link action-toggle"
                               onclick="return confirm('Toggle status for <?= htmlspecialchars(addslashes($user['first_name'])) ?>?')">
                                <?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                            </a>
                            <?php else: ?>
                                <span style="color:var(--muted);font-size:12px">(you)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
function filterTable(inputId, tableId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    const rows = document.querySelectorAll('#' + tableId + ' tbody tr:not(.empty-row)');
    let visible = 0;
    rows.forEach(row => {
        const text = Array.from(row.querySelectorAll('td')).slice(0, -1).map(td => td.textContent).join(' ').toLowerCase();
        const show = text.includes(filter);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
}

function clearSearch(inputId, tableId) {
    document.getElementById(inputId).value = '';
    filterTable(inputId, tableId);
}
</script>
</body>
</html>
<?php $conn->close(); ?>
