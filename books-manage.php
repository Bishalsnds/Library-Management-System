<?php
include 'config.php';

$message = '';
$messageType = '';
$editMode = false;
$editBook = null;
$editBookId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

// Load book data if in edit mode
if ($editBookId > 0) {
    $edit_result = $conn->query("SELECT * FROM books WHERE book_id = $editBookId");
    if ($edit_result && $edit_result->num_rows > 0) {
        $editBook = $edit_result->fetch_assoc();
        $editMode = true;
    }
}

// Handle Add/Update Book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add-book' || $_POST['action'] === 'update-book') {
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $isbn = trim($_POST['isbn'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $total_copies = intval($_POST['total_copies'] ?? 1);
        $available_copies = intval($_POST['available_copies'] ?? 1);
        $book_id = intval($_POST['book_id'] ?? 0);

        // Validate inputs
        if ($title === '' || strlen($title) < 2) {
            $message = 'Book title is required (minimum 2 characters).';
            $messageType = 'error';
        }
        elseif ($total_copies <= 0) {
            $message = 'Total copies must be at least 1.';
            $messageType = 'error';
        }
        elseif ($available_copies < 0 || $available_copies > $total_copies) {
            $message = 'Available copies must be between 0 and total copies.';
            $messageType = 'error';
        }
        else {
            $safe_title = $conn->real_escape_string($title);
            $safe_author = $conn->real_escape_string($author);
            $safe_isbn = $conn->real_escape_string($isbn);
            $safe_category = $conn->real_escape_string($category);

            if ($_POST['action'] === 'add-book') {
                $insert_query = "INSERT INTO books (book_title, author, isbn, category, total_copies, available_copies) 
                                VALUES ('$safe_title', '$safe_author', '$safe_isbn', '$safe_category', $total_copies, $available_copies)";
                
                if ($conn->query($insert_query)) {
                    $message = '✓ Book added successfully.';
                    $messageType = 'success';
                    $_POST = []; // Clear form
                } else {
                    $message = 'Error adding book: ' . $conn->error;
                    $messageType = 'error';
                }
            } else {
                // Update book
                $update_query = "UPDATE books SET book_title='$safe_title', author='$safe_author', isbn='$safe_isbn', category='$safe_category', total_copies=$total_copies, available_copies=$available_copies 
                                WHERE book_id=$book_id";
                
                if ($conn->query($update_query)) {
                    $message = '✓ Book updated successfully.';
                    $messageType = 'success';
                    header("Refresh: 2; url=books-manage.php");
                } else {
                    $message = 'Error updating book: ' . $conn->error;
                    $messageType = 'error';
                }
            }
        }
    }
    
    // Handle Delete Book
    elseif ($_POST['action'] === 'delete-book') {
        $book_id = intval($_POST['book_id'] ?? 0);
        if ($book_id > 0) {
            $delete_query = "DELETE FROM books WHERE book_id = $book_id";
            if ($conn->query($delete_query)) {
                $message = '✓ Book deleted successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error deleting book: ' . $conn->error;
                $messageType = 'error';
            }
        }
    }
}

// Fetch all books
$books_query = "SELECT book_id, book_title, author, isbn, category, total_copies, available_copies FROM books ORDER BY book_title ASC";
$books_result = $conn->query($books_query);
$books = [];

if ($books_result) {
    while ($row = $books_result->fetch_assoc()) {
        $books[] = $row;
    }
}
$totalBooks = count($books);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Management</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .availability { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
    .available { background: #dcfce7; color: #16a34a; }
    .unavailable { background: #fee2e2; color: #dc2626; }
    .action-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-small { padding: 8px 12px; font-size: 0.9rem; }
    .btn-edit { background: #a749ff; color: white; }
    .btn-delete { background: #ef4444; color: white; }
  </style>
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>Book Management</h1>
        <p>Manage library books, track inventory, and update book details.</p>
      </div>
      <div class="page-nav">
        <a class="btn-secondary" href="index.php">Back to dashboard</a>
        <a class="btn-primary" href="fines-complete.php">Fines Module</a>
        <a class="btn-primary" href="warnings-complete.php">Warnings Module</a>
      </div>
    </header>

    <?php if ($message !== ''): ?>
      <div class="alert notice" style="border-left: 4px solid <?php echo $messageType === 'success' ? '#4ade80' : '#ff6b6b'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <?php if (!$editMode): ?>
    <section class="section form-panel">
      <div class="section-title">Add New Book</div>
      <form method="post" action="books-manage.php" novalidate>
        <div class="form-grid">
          <div class="full">
            <label for="title">Book Title *</label>
            <input id="title" name="title" type="text" placeholder="Enter book title" required>
          </div>
          <div>
            <label for="author">Author</label>
            <input id="author" name="author" type="text" placeholder="Enter author name">
          </div>
          <div>
            <label for="isbn">ISBN</label>
            <input id="isbn" name="isbn" type="text" placeholder="Enter ISBN">
          </div>
          <div>
            <label for="category">Category</label>
            <input id="category" name="category" type="text" placeholder="e.g., Fiction, Science, Technology">
          </div>
          <div>
            <label for="total_copies">Total Copies *</label>
            <input id="total_copies" name="total_copies" type="number" min="1" value="1" required>
          </div>
          <div>
            <label for="available_copies">Available Copies *</label>
            <input id="available_copies" name="available_copies" type="number" min="0" value="1" required>
          </div>
          <div class="full">
            <button class="btn-primary" type="submit" name="action" value="add-book">Add Book</button>
          </div>
        </div>
      </form>
    </section>
    <?php else: ?>
    <section class="section form-panel">
      <div class="section-title">Edit Book</div>
      <form method="post" action="books-manage.php" novalidate>
        <input type="hidden" name="book_id" value="<?php echo $editBook['book_id']; ?>">
        <div class="form-grid">
          <div class="full">
            <label for="title">Book Title *</label>
            <input id="title" name="title" type="text" value="<?php echo htmlspecialchars($editBook['book_title']); ?>" required>
          </div>
          <div>
            <label for="author">Author</label>
            <input id="author" name="author" type="text" value="<?php echo htmlspecialchars($editBook['author']); ?>">
          </div>
          <div>
            <label for="isbn">ISBN</label>
            <input id="isbn" name="isbn" type="text" value="<?php echo htmlspecialchars($editBook['isbn']); ?>">
          </div>
          <div>
            <label for="category">Category</label>
            <input id="category" name="category" type="text" value="<?php echo htmlspecialchars($editBook['category']); ?>">
          </div>
          <div>
            <label for="total_copies">Total Copies *</label>
            <input id="total_copies" name="total_copies" type="number" min="1" value="<?php echo $editBook['total_copies']; ?>" required>
          </div>
          <div>
            <label for="available_copies">Available Copies *</label>
            <input id="available_copies" name="available_copies" type="number" min="0" value="<?php echo $editBook['available_copies']; ?>" required>
          </div>
          <div class="full">
            <button class="btn-primary" type="submit" name="action" value="update-book">Update Book</button>
            <a class="btn-secondary" href="books-manage.php">Cancel</a>
          </div>
        </div>
      </form>
    </section>
    <?php endif; ?>

    <section class="section table-panel">
      <div class="section-title">Library Books</div>
      <p class="notice">Total books in library: <span class="chip"><?php echo $totalBooks; ?></span></p>
      
      <?php if ($totalBooks === 0): ?>
        <p>No books in library. Use the form above to add the first book.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Title</th>
              <th>Author</th>
              <th>ISBN</th>
              <th>Category</th>
              <th>Total</th>
              <th>Available</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($books as $book): ?>
            <tr>
              <td><?php echo htmlspecialchars($book['book_title']); ?></td>
              <td><?php echo htmlspecialchars($book['author'] ?: '—'); ?></td>
              <td><?php echo htmlspecialchars($book['isbn'] ?: '—'); ?></td>
              <td><?php echo htmlspecialchars($book['category'] ?: '—'); ?></td>
              <td><?php echo $book['total_copies']; ?></td>
              <td><?php echo $book['available_copies']; ?></td>
              <td>
                <span class="availability <?php echo $book['available_copies'] > 0 ? 'available' : 'unavailable'; ?>">
                  <?php echo $book['available_copies'] > 0 ? 'Available' : 'Out'; ?>
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <a href="?edit=<?php echo $book['book_id']; ?>" class="btn-small btn-edit">Edit</a>
                  <form method="post" style="display:inline;">
                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                    <button type="submit" name="action" value="delete-book" class="btn-small btn-delete" onclick="return confirm('Delete this book?')">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <footer>All book records are stored in the XAMPP MySQL database.</footer>
  </main>
</body>
</html>
