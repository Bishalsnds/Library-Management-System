<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once 'db.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$query = "SELECT id, title, author, category, available FROM books WHERE 1=1";

if ($search) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (title LIKE '%$search%' OR author LIKE '%$search%' OR category LIKE '%$search%')";
}

if ($category) {
    $category = $conn->real_escape_string($category);
    $query .= " AND category = '$category'";
}

$result = $conn->query($query);

if (!$result) {
    die(json_encode(["error" => "Query failed: " . $conn->error]));
}

$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

echo json_encode($books);
$conn->close();
?>
