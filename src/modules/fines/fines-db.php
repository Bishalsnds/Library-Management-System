<?php
include 'config.php';

// Fetch all students for dropdown
$students_result = $conn->query("SELECT student_id, student_name FROM students WHERE status='active' ORDER BY student_name ASC");
$students = [];
if ($students_result) {
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch all books for dropdown
$books_result = $conn->query("SELECT book_id, book_title FROM books ORDER BY book_title ASC");
$books = [];
if ($books_result) {
    while ($row = $books_result->fetch_assoc()) {
        $books[] = $row;
    }
}

$message = '';
$messageType = '';

// Validation function
function validateInput($input) {
    $input = trim($input);
    if (strlen($input) < 2) {
        return ['valid' => false, 'error' => 'Input must be at least 2 characters long.'];
    }
    if (strlen($input) > 100) {
        return ['valid' => false, 'error' => 'Input must not exceed 100 characters.'];
    }
    if (!preg_match('/^[a-zA-Z0-9\s\-\.æøåÆØÅ]+$/u', $input)) {
        return ['valid' => false, 'error' => 'Input contains invalid characters.'];
    }
    return ['valid' => true];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add-fine') {
    $student_id = intval($_POST['student_id'] ?? 0);
    $book_id = intval($_POST['book_id'] ?? 0);
    $amount = trim($_POST['amount'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    // Get student name from ID
    $student_result = $conn->query("SELECT student_name FROM students WHERE student_id=$student_id");
    $student_data = $student_result->fetch_assoc();
    $student_name = $student_data['student_name'] ?? '';

    // Get book title from ID if provided
    $book_title = '';
    if ($book_id > 0) {
        $book_result = $conn->query("SELECT book_title FROM books WHERE book_id=$book_id");
        $book_data = $book_result->fetch_assoc();
        $book_title = $book_data['book_title'] ?? '';
    } else {
        $book_title = trim($_POST['book_title'] ?? '');
    }

    // Validate inputs
    if ($student_id <= 0 || $student_name === '') {
        $message = 'Please select a valid student.';
        $messageType = 'error';
    } 
    elseif ($book_title === '') {
        $message = 'Please select or enter a book title.';
        $messageType = 'error';
    }
    elseif ($amount === '' || !is_numeric($amount) || (float)$amount <= 0) {
        $message = 'Please enter a valid positive fine amount (DKK).';
        $messageType = 'error';
    }
    elseif ((float)$amount > 100000) {
        $message = 'Fine amount cannot exceed 100,000 DKK.';
        $messageType = 'error';
    }
    else {
        // Insert into database
        $safe_student_name = $conn->real_escape_string($student_name);
        $safe_book_title = $conn->real_escape_string($book_title);
        $safe_reason = $conn->real_escape_string($reason ?: 'Overdue return');
        $fine_amount = floatval($amount);

        $insert_query = "INSERT INTO fines (student_id, book_id, student_name, book_title, fine_amount, reason, status) 
                        VALUES ($student_id, " . ($book_id > 0 ? $book_id : 'NULL') . ", '$safe_student_name', '$safe_book_title', $fine_amount, '$safe_reason', 'unpaid')";
        
        if ($conn->query($insert_query)) {
            $message = '✓ Fine record added successfully.';
            $messageType = 'success';
        } else {
            $message = 'Error adding fine: ' . $conn->error;
            $messageType = 'error';
        }
    }
}

// Fetch all unpaid fines
$fines_query = "SELECT fine_id, student_name, book_title, fine_amount, reason, issued_date, status FROM fines WHERE status='unpaid' ORDER BY issued_date DESC";
$fines_result = $conn->query($fines_query);
$fines = [];
$total_fine_amount = 0;

if ($fines_result) {
    while ($row = $fines_result->fetch_assoc()) {
        $fines[] = $row;
        $total_fine_amount += floatval($row['fine_amount']);
    }
}
$totalFines = count($fines);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fine Management - Database Version</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>Fine Management</h1>
        <p>Record overdue fines for library users and maintain a current list of active fines in the system (Database Enabled).</p>
      </div>
      <div class="page-nav">
        <a class="btn-secondary" href="index.php">Back to dashboard</a>
        <a class="btn-primary" href="warnings-db.php">Warning Module</a>
      </div>
    </header>

    <?php if ($message !== ''): ?>
      <div class="alert notice" style="border-left: 4px solid <?php echo $messageType === 'success' ? '#4ade80' : '#ff6b6b'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <section class="section form-panel">
      <div class="section-title">Add New Fine</div>
      <form method="post" action="fines-db.php" novalidate>
        <div class="form-grid">
          <div>
            <label for="student_id">Student *</label>
            <select id="student_id" name="student_id" required>
              <option value="">Select a student</option>
              <?php foreach ($students as $student): ?>
                <option value="<?php echo $student['student_id']; ?>"><?php echo htmlspecialchars($student['student_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="book_id">Book *</label>
            <select id="book_id" name="book_id">
              <option value="">Select a book or enter manually</option>
              <?php foreach ($books as $book): ?>
                <option value="<?php echo $book['book_id']; ?>"><?php echo htmlspecialchars($book['book_title']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="book_title">Or Book Title *</label>
            <input id="book_title" name="book_title" type="text" placeholder="Enter book title manually if not in list">
          </div>
          <div>
            <label for="amount">Fine Amount (DKK) *</label>
            <input id="amount" name="amount" type="number" step="0.01" min="0.01" max="100000" placeholder="0.00" required>
          </div>
          <div>
            <label for="reason">Reason</label>
            <input id="reason" name="reason" type="text" placeholder="e.g. overdue return" 
                   pattern="[a-zA-Z0-9\s\-\.æøåÆØÅ]{0,100}"
                   title="Reason: max 100 characters">
          </div>
          <div class="full">
            <button class="btn-primary" type="submit" name="action" value="add-fine">Save Fine to Database</button>
          </div>
        </div>
      </form>
    </section>

    <section class="section table-panel">
      <div class="section-title">Unpaid Fines</div>
      <p class="notice">Currently <span class="chip"><?php echo $totalFines; ?> unpaid fines</span> totaling <span class="chip">kr <?php echo number_format($total_fine_amount, 2); ?></span>.</p>
      <?php if ($totalFines === 0): ?>
        <p>No unpaid fines recorded. Use the form above to add a new fine.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Book</th>
              <th>Amount</th>
              <th>Reason</th>
              <th>Date Issued</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($fines as $fine): ?>
            <tr>
              <td><?php echo htmlspecialchars($fine['student_name']); ?></td>
              <td><?php echo htmlspecialchars($fine['book_title']); ?></td>
              <td>kr <?php echo number_format(floatval($fine['fine_amount']), 2); ?></td>
              <td><?php echo htmlspecialchars($fine['reason']); ?></td>
              <td><?php echo date('Y-m-d', strtotime($fine['issued_date'])); ?></td>
              <td><?php echo htmlspecialchars($fine['status']); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <footer>Fine records are now stored in XAMPP MySQL database. Data persists even after closing the browser.</footer>
  </main>
</body>
</html>
