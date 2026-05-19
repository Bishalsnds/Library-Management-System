<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="container">
        <div class="logo-header">
            <img src="img/logo.svg" alt="Site Logo">
            <h1>Edit Book</h1>
        </div>
    <?php
    include 'db.php';
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM books WHERE id=$id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            echo "Book not found.";
            exit;
        }
    } else {
        echo "No book ID provided.";
        exit;
    }
    ?>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="<?php echo $row['title']; ?>" required><br><br>
        
        <label for="author">Author:</label>
        <input type="text" id="author" name="author" value="<?php echo $row['author']; ?>" required><br><br>
        
        <label for="isbn">ISBN:</label>
        <input type="text" id="isbn" name="isbn" value="<?php echo $row['isbn']; ?>" required><br><br>
        
        <label for="published_year">Published Year:</label>
        <input type="number" id="published_year" name="published_year" value="<?php echo $row['published_year']; ?>"><br><br>
        
        <label for="available_copies">Available Copies:</label>
        <input type="number" id="available_copies" name="available_copies" value="<?php echo $row['available_copies']; ?>"><br><br>
        
        <input type="submit" value="Update Book">
    </form>
    <a href="index.php">Back to List</a>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = intval($_POST['id']);
        $title = $conn->real_escape_string($_POST['title']);
        $author = $conn->real_escape_string($_POST['author']);
        $isbn = $conn->real_escape_string($_POST['isbn']);
        $published_year = intval($_POST['published_year']) ?? 0;
        $available_copies = intval($_POST['available_copies']) ?? 1;
        
        $sql = "UPDATE books SET title='$title', author='$author', isbn='$isbn', published_year=$published_year, available_copies=$available_copies WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            header("Location: index.php");
            exit;
        } else {
            echo "Error: " . $conn->error;
        }
    }
    $conn->close();
    ?>
    </div>
</body>
</html>