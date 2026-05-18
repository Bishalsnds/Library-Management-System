<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once 'db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Determine which action to perform
switch($action) {
    case 'search':
        handleSearch();
        break;
    case 'add':
        handleAdd();
        break;
    case 'update':
        handleUpdate();
        break;
    case 'delete':
        handleDelete();
        break;
    case 'getAll':
        handleGetAll();
        break;
    case 'getCategories':
        handleGetCategories();
        break;
    case 'checkDb':
        handleCheckDb();
        break;
    default:
        echo json_encode(["error" => "Invalid action"]);
}

// SEARCH - GET books by search and category
function handleSearch() {
    global $conn;
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';

    // Debug logging
    error_log("Search params - search: '$search', category: '$category'");

    $query = "SELECT id, title, author, category, available FROM books WHERE 1=1";

    if ($search) {
        $search = $conn->real_escape_string($search);
        $query .= " AND (title LIKE '%$search%' OR author LIKE '%$search%' OR category LIKE '%$search%')";
    }

    if ($category && $category !== '') {
        $category = $conn->real_escape_string($category);
        $query .= " AND category = '$category'";
    }

    $query .= " ORDER BY title ASC";
    
    // Debug logging
    error_log("Final query: $query");
    
    $result = $conn->query($query);

    if (!$result) {
        echo json_encode(["error" => "Query failed: " . $conn->error]);
        return;
    }

    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }

    echo json_encode($books);
}

// ADD - POST new book
function handleAdd() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["error" => "POST method required"]);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!isset($input['title']) || empty(trim($input['title']))) {
        echo json_encode(["error" => "Title is required"]);
        return;
    }
    if (!isset($input['author']) || empty(trim($input['author']))) {
        echo json_encode(["error" => "Author is required"]);
        return;
    }
    if (!isset($input['category']) || empty(trim($input['category']))) {
        echo json_encode(["error" => "Category is required"]);
        return;
    }

    $title = $conn->real_escape_string(trim($input['title']));
    $author = $conn->real_escape_string(trim($input['author']));
    $category = $conn->real_escape_string(trim($input['category']));
    $available = isset($input['available']) ? (int)$input['available'] : 1;

    $query = "INSERT INTO books (title, author, category, available) VALUES ('$title', '$author', '$category', $available)";

    if ($conn->query($query) === TRUE) {
        echo json_encode(["success" => true, "message" => "Book added successfully", "id" => $conn->insert_id]);
    } else {
        echo json_encode(["error" => "Failed to add book: " . $conn->error]);
    }
}

// UPDATE - PUT update book
function handleUpdate() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["error" => "POST method required"]);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || empty($input['id'])) {
        echo json_encode(["error" => "Book ID is required"]);
        return;
    }

    $id = (int)$input['id'];
    $updates = [];

    if (isset($input['title']) && !empty(trim($input['title']))) {
        $title = $conn->real_escape_string(trim($input['title']));
        $updates[] = "title = '$title'";
    }
    if (isset($input['author']) && !empty(trim($input['author']))) {
        $author = $conn->real_escape_string(trim($input['author']));
        $updates[] = "author = '$author'";
    }
    if (isset($input['category']) && !empty(trim($input['category']))) {
        $category = $conn->real_escape_string(trim($input['category']));
        $updates[] = "category = '$category'";
    }
    if (isset($input['available'])) {
        $available = (int)$input['available'];
        $updates[] = "available = $available";
    }

    if (empty($updates)) {
        echo json_encode(["error" => "No fields to update"]);
        return;
    }

    $query = "UPDATE books SET " . implode(", ", $updates) . " WHERE id = $id";

    if ($conn->query($query) === TRUE) {
        echo json_encode(["success" => true, "message" => "Book updated successfully"]);
    } else {
        echo json_encode(["error" => "Failed to update book: " . $conn->error]);
    }
}

// DELETE - DELETE book
function handleDelete() {
    global $conn;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["error" => "POST method required"]);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || empty($input['id'])) {
        echo json_encode(["error" => "Book ID is required"]);
        return;
    }

    $id = (int)$input['id'];
    $query = "DELETE FROM books WHERE id = $id";

    if ($conn->query($query) === TRUE) {
        echo json_encode(["success" => true, "message" => "Book deleted successfully"]);
    } else {
        echo json_encode(["error" => "Failed to delete book: " . $conn->error]);
    }
}

// GET ALL - Retrieve all books
function handleGetAll() {
    global $conn;

    $query = "SELECT id, title, author, category, available FROM books ORDER BY title ASC";
    $result = $conn->query($query);

    if (!$result) {
        echo json_encode(["error" => "Query failed: " . $conn->error]);
        return;
    }

    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }

    echo json_encode($books);
}

// GET CATEGORIES - Get distinct categories
function handleGetCategories() {
    global $conn;

    $query = "SELECT DISTINCT category FROM books ORDER BY category ASC";
    $result = $conn->query($query);

    if (!$result) {
        echo json_encode(["error" => "Query failed: " . $conn->error]);
        return;
    }

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }

    echo json_encode($categories);
}

// CHECK DATABASE - Check if database and table exist with sample data
function handleCheckDb() {
    global $conn;
    
    $info = [
        "connection" => "OK",
        "database" => "library_db",
        "table_exists" => false,
        "book_count" => 0,
        "categories" => []
    ];
    
    // Check if books table exists
    $result = $conn->query("SHOW TABLES LIKE 'books'");
    if ($result && $result->num_rows > 0) {
        $info["table_exists"] = true;
        
        // Get book count
        $countResult = $conn->query("SELECT COUNT(*) as count FROM books");
        if ($countResult) {
            $countRow = $countResult->fetch_assoc();
            $info["book_count"] = (int)$countRow['count'];
        }
        
        // Get available/unavailable counts
        $statusResult = $conn->query("SELECT SUM(CASE WHEN available = 1 THEN 1 ELSE 0 END) AS available_count, SUM(CASE WHEN available = 0 THEN 1 ELSE 0 END) AS unavailable_count FROM books");
        if ($statusResult) {
            $statusRow = $statusResult->fetch_assoc();
            $info["available_count"] = (int)$statusRow['available_count'];
            $info["unavailable_count"] = (int)$statusRow['unavailable_count'];
        }

        // Get categories
        $catResult = $conn->query("SELECT DISTINCT category FROM books ORDER BY category");
        if ($catResult) {
            while ($row = $catResult->fetch_assoc()) {
                $info["categories"][] = $row['category'];
            }
        }
    }
    
    echo json_encode($info);
}

$conn->close();
?>
