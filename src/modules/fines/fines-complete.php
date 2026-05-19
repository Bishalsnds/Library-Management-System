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
$editMode = false;
$editFine = null;
$editFineId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

// Load fine data if in edit mode
if ($editFineId > 0) {
    $edit_result = $conn->query("SELECT * FROM fines WHERE fine_id = $editFineId");
    if ($edit_result && $edit_result->num_rows > 0) {
        $editFine = $edit_result->fetch_assoc();
        $editMode = true;
    }
}

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

// Handle Add/Update Fine
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add-fine' || $_POST['action'] === 'update-fine') {
        $student_id = intval($_POST['student_id'] ?? 0);
        $book_id = intval($_POST['book_id'] ?? 0);
        $amount = trim($_POST['amount'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $fine_id = intval($_POST['fine_id'] ?? 0);

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
            $safe_student_name = $conn->real_escape_string($student_name);
            $safe_book_title = $conn->real_escape_string($book_title);
            $safe_reason = $conn->real_escape_string($reason ?: 'Overdue return');
            $fine_amount = floatval($amount);

            if ($_POST['action'] === 'add-fine') {
                $insert_query = "INSERT INTO fines (student_id, book_id, student_name, book_title, fine_amount, reason, status) 
                                VALUES ($student_id, " . ($book_id > 0 ? $book_id : 'NULL') . ", '$safe_student_name', '$safe_book_title', $fine_amount, '$safe_reason', 'unpaid')";
                
                if ($conn->query($insert_query)) {
                    $message = '✓ Fine record added successfully.';
                    $messageType = 'success';
                    $_POST = []; // Clear form
                } else {
                    $message = 'Error adding fine: ' . $conn->error;
                    $messageType = 'error';
                }
            } else {
                // Update fine
                $update_query = "UPDATE fines SET student_id=$student_id, book_id=" . ($book_id > 0 ? $book_id : 'NULL') . 
                               ", student_name='$safe_student_name', book_title='$safe_book_title', fine_amount=$fine_amount, reason='$safe_reason' 
                               WHERE fine_id=$fine_id";
                
                if ($conn->query($update_query)) {
                    $message = '✓ Fine record updated successfully.';
                    $messageType = 'success';
                    header("Refresh: 2; url=fines-complete.php");
                } else {
                    $message = 'Error updating fine: ' . $conn->error;
                    $messageType = 'error';
                }
            }
        }
    }
    
    // Handle Delete Fine
    elseif ($_POST['action'] === 'delete-fine') {
        $fine_id = intval($_POST['fine_id'] ?? 0);
        if ($fine_id > 0) {
            $delete_query = "DELETE FROM fines WHERE fine_id = $fine_id";
            if ($conn->query($delete_query)) {
                $message = '✓ Fine record deleted successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error deleting fine: ' . $conn->error;
                $messageType = 'error';
            }
        }
    }
    
    // Handle Mark as Paid
    elseif ($_POST['action'] === 'mark-paid') {
        $fine_id = intval($_POST['fine_id'] ?? 0);
        if ($fine_id > 0) {
            $paid_query = "UPDATE fines SET status='paid', payment_date=NOW() WHERE fine_id = $fine_id";
            if ($conn->query($paid_query)) {
                $message = '✓ Fine marked as paid.';
                $messageType = 'success';
            } else {
                $message = 'Error marking fine as paid: ' . $conn->error;
                $messageType = 'error';
            }
        }
    }
    
    // Handle Cancel/Waive Fine
    elseif ($_POST['action'] === 'cancel-fine') {
        $fine_id = intval($_POST['fine_id'] ?? 0);
        if ($fine_id > 0) {
            $cancel_query = "UPDATE fines SET status='waived' WHERE fine_id = $fine_id";
            if ($conn->query($cancel_query)) {
                $message = '✓ Fine has been cancelled/waived.';
                $messageType = 'success';
            } else {
                $message = 'Error cancelling fine: ' . $conn->error;
                $messageType = 'error';
            }
        }
    }
}

// Fetch all fines with filtering
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$filter_query = "SELECT fine_id, student_id, student_name, book_title, fine_amount, reason, issued_date, status FROM fines";

if ($filter_status !== 'all') {
    $filter_query .= " WHERE status='" . $conn->real_escape_string($filter_status) . "'";
}
$filter_query .= " ORDER BY issued_date DESC";

$fines_result = $conn->query($filter_query);
$fines = [];
$total_fine_amount = 0;
$unpaid_amount = 0;

if ($fines_result) {
    while ($row = $fines_result->fetch_assoc()) {
        $fines[] = $row;
        $total_fine_amount += floatval($row['fine_amount']);
        if ($row['status'] === 'unpaid') {
            $unpaid_amount += floatval($row['fine_amount']);
        }
    }
}
$totalFines = count($fines);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fine Management - Complete</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
    .status-unpaid { background: #fee2e2; color: #dc2626; }
    .status-paid { background: #dcfce7; color: #16a34a; }
    .status-waived { background: #f3e8ff; color: #7c3aed; }
    .action-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-small { padding: 8px 12px; font-size: 0.9rem; }
    .btn-edit { background: #a749ff; color: white; }
    .btn-paid { background: #10b981; color: white; }
    .btn-cancel { background: #ef4444; color: white; }
    .btn-delete { background: #6b7280; color: white; }
    .filters { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .filter-btn { padding: 10px 16px; border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; cursor: pointer; background: rgba(255,255,255,0.05); color: var(--text); }
    .filter-btn.active { background: var(--purple); border-color: var(--purple); }
  </style>
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>Fine Management</h1>
        <p>Complete fine management system with edit, update, delete, and payment tracking (Database Connected).</p>
      </div>
      <div class="page-nav">
        <a class="btn-secondary" href="index.php">Back to dashboard</a>
        <a class="btn-primary" href="warnings-complete.php">Warning Module</a>
        <a class="btn-primary" href="books-manage.php">Manage Books</a>
      </div>
    </header>

    <?php if ($message !== ''): ?>
      <div class="alert notice" style="border-left: 4px solid <?php echo $messageType === 'success' ? '#4ade80' : '#ff6b6b'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <?php if (!$editMode): ?>
    <section class="section form-panel">
      <div class="section-title">Add New Fine</div>
      <form method="post" action="fines-complete.php" novalidate>
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
            <input id="reason" name="reason" type="text" placeholder="e.g. overdue return" maxlength="100">
          </div>
          <div class="full">
            <button class="btn-primary" type="submit" name="action" value="add-fine">Add Fine</button>
          </div>
        </div>
      </form>
    </section>
    <?php else: ?>
    <section class="section form-panel">
      <div class="section-title">Edit Fine</div>
      <form method="post" action="fines-complete.php" novalidate>
        <input type="hidden" name="fine_id" value="<?php echo $editFine['fine_id']; ?>">
        <div class="form-grid">
          <div>
            <label for="student_id">Student *</label>
            <select id="student_id" name="student_id" required>
              <option value="">Select a student</option>
              <?php foreach ($students as $student): ?>
                <option value="<?php echo $student['student_id']; ?>" <?php echo $student['student_id'] == $editFine['student_id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($student['student_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="book_id">Book *</label>
            <select id="book_id" name="book_id">
              <option value="">Select a book or enter manually</option>
              <?php foreach ($books as $book): ?>
                <option value="<?php echo $book['book_id']; ?>" <?php echo $book['book_title'] == $editFine['book_title'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($book['book_title']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="book_title">Or Book Title *</label>
            <input id="book_title" name="book_title" type="text" value="<?php echo htmlspecialchars($editFine['book_title']); ?>">
          </div>
          <div>
            <label for="amount">Fine Amount (DKK) *</label>
            <input id="amount" name="amount" type="number" step="0.01" min="0.01" max="100000" value="<?php echo $editFine['fine_amount']; ?>" required>
          </div>
          <div>
            <label for="reason">Reason</label>
            <input id="reason" name="reason" type="text" value="<?php echo htmlspecialchars($editFine['reason']); ?>" maxlength="100">
          </div>
          <div class="full">
            <button class="btn-primary" type="submit" name="action" value="update-fine">Update Fine</button>
            <a class="btn-secondary" href="fines-complete.php">Cancel</a>
          </div>
        </div>
      </form>
    </section>
    <?php endif; ?>

    <section class="section table-panel">
      <div class="section-title">Fine Records</div>
      
      <div class="filters">
        <a class="filter-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>" href="?status=all">All (<?php echo $totalFines; ?>)</a>
        <a class="filter-btn <?php echo $filter_status === 'unpaid' ? 'active' : ''; ?>" href="?status=unpaid">Unpaid</a>
        <a class="filter-btn <?php echo $filter_status === 'paid' ? 'active' : ''; ?>" href="?status=paid">Paid</a>
        <a class="filter-btn <?php echo $filter_status === 'waived' ? 'active' : ''; ?>" href="?status=waived">Cancelled</a>
      </div>

      <p class="notice">Total: <span class="chip">kr <?php echo number_format($total_fine_amount, 2); ?></span> | Unpaid: <span class="chip">kr <?php echo number_format($unpaid_amount, 2); ?></span></p>
      
      <?php if ($totalFines === 0): ?>
        <p>No fines recorded. Use the form above to add a new fine.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Book</th>
              <th>Amount</th>
              <th>Reason</th>
              <th>Date</th>
              <th>Status</th>
              <th>Actions</th>
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
              <td>
                <span class="status-badge status-<?php echo $fine['status']; ?>">
                  <?php echo ucfirst($fine['status']); ?>
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <a href="?edit=<?php echo $fine['fine_id']; ?>" class="btn-small btn-edit">Edit</a>
                  <?php if ($fine['status'] === 'unpaid'): ?>
                    <a href="pay-fine.php?fine_id=<?php echo $fine['fine_id']; ?>" class="btn-small" style="background: #10b981; color: white;">Pay</a>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="fine_id" value="<?php echo $fine['fine_id']; ?>">
                      <button type="submit" name="action" value="mark-paid" class="btn-small btn-paid" onclick="return confirm('Mark as paid?')">Paid</button>
                    </form>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="fine_id" value="<?php echo $fine['fine_id']; ?>">
                      <button type="submit" name="action" value="cancel-fine" class="btn-small btn-cancel" onclick="return confirm('Cancel/Waive this fine?')">Cancel</button>
                    </form>
                  <?php endif; ?>
                  <form method="post" style="display:inline;">
                    <input type="hidden" name="fine_id" value="<?php echo $fine['fine_id']; ?>">
                    <button type="submit" name="action" value="delete-fine" class="btn-small btn-delete" onclick="return confirm('Delete this fine?')">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <footer>All fine records are stored in XAMPP MySQL database.</footer>
  </main>
</body>
</html>
