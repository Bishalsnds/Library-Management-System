<?php
include 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $published_year = intval($_POST['published_year']) ?? 0;
    $available_copies = intval($_POST['available_copies']) ?? 1;

    $sql = "INSERT INTO books (title, author, isbn, published_year, available_copies)
            VALUES ('$title', '$author', '$isbn', $published_year, $available_copies)";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Book</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="container">
        <div class="logo-header">
            <img src="img/logo.svg" alt="Site Logo">
            <h1>Add New Book</h1>
        </div>
        <form method="POST" action="">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required><br><br>

        <label for="author">Author:</label>
        <input type="text" id="author" name="author" required><br><br>

        <label for="isbn">ISBN:</label>
        <input type="text" id="isbn" name="isbn" required><br><br>

        <label for="published_year">Published Year:</label>
        <input type="number" id="published_year" name="published_year"><br><br>

        <label for="available_copies">Available Copies:</label>
        <input type="number" id="available_copies" name="available_copies" value="1"><br><br>

        <input type="submit" value="Add Book">
        </form>
        <a href="index.php">Back to List</a>
        <?php if ($error): ?><p style="color: red;"><?php echo $error; ?></p><?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>
