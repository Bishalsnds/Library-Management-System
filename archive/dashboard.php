<?php
include 'db.php';

$searchQuery = $_GET['search'] ?? '';

// Handle Create
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $published_year = intval($_POST['published_year']) ?? 0;
    $available_copies = intval($_POST['available_copies']) ?? 1;

    $sql = "INSERT INTO books (title, author, isbn, published_year, available_copies) VALUES ('$title', '$author', '$isbn', $published_year, $available_copies)";
    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// Handle Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $published_year = intval($_POST['published_year']) ?? 0;
    $available_copies = intval($_POST['available_copies']) ?? 1;

    $sql = "UPDATE books SET title='$title', author='$author', isbn='$isbn', published_year=$published_year, available_copies=$available_copies WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM books WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch all books
$sql = "SELECT * FROM books";
$result = $conn->query($sql);
$books = ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];

if ($searchQuery) {
    $books = array_filter($books, function($book) use ($searchQuery) {
        return stripos($book['title'], $searchQuery) !== false || stripos($book['author'], $searchQuery) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="container">
        <div class="logo-header">
            <img src="img/logo.svg" alt="Site Logo">
            <h1>Library Books Dashboard</h1>
        </div>

        <form class="search-form" method="GET">
            <input type="text" name="search" placeholder="Search by title or author..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search</button>
        </form>

        <h2>Add New Book</h2>
        <form method="POST">
            <input type="hidden" name="create" value="1">
            <label>Title: <input type="text" name="title" required></label>
            <label>Author: <input type="text" name="author" required></label>
            <label>ISBN: <input type="text" name="isbn" required></label>
            <label>Published Year: <input type="number" name="published_year"></label>
            <label>Available Copies: <input type="number" name="available_copies" value="1"></label>
            <button type="submit">Add Book</button>
        </form>

        <h2>Books List</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Year</th>
                <th>Copies</th>
                <th>Actions</th>
            </tr>
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                <tr>
                    <td><?php echo intval($book['id']); ?></td>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                    <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                    <td><?php echo intval($book['published_year']); ?></td>
                    <td><?php echo intval($book['available_copies']); ?></td>
                    <td>
                        <a href="?edit=<?php echo intval($book['id']); ?>">Edit</a> |
                        <a href="?delete=<?php echo intval($book['id']); ?>" onclick="return confirm('Delete this book?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">No books found</td></tr>
            <?php endif; ?>
        </table>

        <?php if (isset($_GET['edit'])): 
            $editId = intval($_GET['edit']);
            $editBook = null;
            foreach ($books as $b) {
                if ($b['id'] == $editId) {
                    $editBook = $b;
                    break;
                }
            }
        ?>
            <?php if ($editBook): ?>
                <h2>Edit Book</h2>
                <form method="POST">
                    <input type="hidden" name="update" value="1">
                    <input type="hidden" name="id" value="<?php echo intval($editBook['id']); ?>">
                    <label>Title: <input type="text" name="title" value="<?php echo htmlspecialchars($editBook['title']); ?>" required></label>
                    <label>Author: <input type="text" name="author" value="<?php echo htmlspecialchars($editBook['author']); ?>" required></label>
                    <label>ISBN: <input type="text" name="isbn" value="<?php echo htmlspecialchars($editBook['isbn']); ?>" required></label>
                    <label>Published Year: <input type="number" name="published_year" value="<?php echo intval($editBook['published_year']); ?>"></label>
                    <label>Available Copies: <input type="number" name="available_copies" value="<?php echo intval($editBook['available_copies']); ?>"></label>
                    <button type="submit">Update Book</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>
