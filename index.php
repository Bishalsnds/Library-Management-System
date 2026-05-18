<!DOCTYPE html>
<!--
  Run this application from XAMPP using:
  http://localhost/library/index.php

  First time only, initialize the database at:
  http://localhost/library/setup_db.php

  MySQL settings: host=127.0.0.1, port=3306, user=root, password=root
-->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Books</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="container">
        <div class="logo-header">
            <img src="img/logo.svg" alt="Site Logo">
            <h1>Library Books</h1>
        </div>
        <p><a href="dashboard.php">Open Dashboard</a> | <a href="create.php">Add New Book</a></p>
        <table border="1">
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>ISBN</th>
            <th>Published Year</th>
            <th>Available Copies</th>
            <th>Actions</th>
        </tr>
        <?php
        include 'db.php';
        $sql = "SELECT * FROM books";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["author"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["isbn"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["published_year"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["available_copies"]) . "</td>";
                echo "<td><a href='edit.php?id=" . intval($row["id"]) . "'>Edit</a> | <a href='delete.php?id=" . intval($row["id"]) . "'>Delete</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No books found</td></tr>";
        }
        $conn->close();
        ?>
    </table>
    </div>
</body>
</html>