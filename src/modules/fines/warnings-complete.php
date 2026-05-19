<?php
include 'config.php';

// Fetch all students
$students_result = $conn->query("SELECT student_id, student_name FROM students WHERE status='active' ORDER BY student_name ASC");
$students = [];
if ($students_result) {
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
}

$message = '';
$messageType = '';
$editMode = false;
$editWarning = null;
$editWarningId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

// Load warning data if in edit mode
if ($editWarningId > 0) {
    $edit_result = $conn->query("SELECT * FROM warnings WHERE warning_id = $editWarningId");
    if ($edit_result && $edit_result->num_rows > 0) {
        $editWarning = $edit_result->fetch_assoc();
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

// Handle Add/Update Warning
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add-warning' || $_POST['action'] === 'update-warning') {
        $student_id = intval($_POST['student_id'] ?? 0);
        $level = trim($_POST['level'] ?? '');
        $note = trim($_POST['note'] ?? '');
        $warning_id = intval($_POST['warning_id'] ?? 0);

        // Get student name from ID
        $student_result = $conn->query("SELECT student_name FROM students WHERE student_id=$student_id");
        $student_data = $student_result->fetch_assoc();
        $student_name = $student_data['student_name'] ?? '';

        // Validate inputs
        if ($student_id <= 0 || $student_name === '') {
            $message = 'Please select a valid student.';
            $messageType = 'error';
        } 
        elseif ($level === '' || !in_array($level, ['Level 1', 'Level 2', 'Level 3'])) {
            $message = 'Please select a valid warning level.';
            $messageType = 'error';
        }
        else {
            $safe_student_name = $conn->real_escape_string($student_name);
            $safe_note = $conn->real_escape_string($note ?: 'Follow policy and return items on time.');
            $safe_level = $conn->real_escape_string($level);

            if ($_POST['action'] === 'add-warning') {
                $insert_query = "INSERT INTO warnings (student_id, student_name, warning_level, note, status) 
                                VALUES ($student_id, '$safe_student_name', '$safe_level', '$safe_note', 'active')";
                
                if ($conn->query($insert_query)) {
                    $message = '✓ Warning has been recorded successfully.';
                    $messageType = 'success';
                    $_POST = []; // Clear form
                } else {
                    $message = 'Error adding warning: ' . $conn->error;
                    $messageType = 'error';
                }
            } else {
                // Update warning
                $update_query = "UPDATE warnings SET student_id=$student_id, student_name='$safe_student_name', warning_level='$safe_level', note='$safe_note' 
                                WHERE warning_id=$warning_id";
                
                if ($conn->query($update_query)) {
                    $message = '✓ Warning updated successfully.';
                    $messageType = 'success';
                    header("Refresh: 2; url=warnings-complete.php");
                } else {
                    $message = 'Error updating warning: ' . $conn->error;
                    $messageType = 'error';
                }
            }
        }
    }
    
    // Handle Delete Warning
    elseif ($_POST['action'] === 'delete-warning') {
        $warning_id = intval($_POST['warning_id'] ?? 0);
        if ($warning_id > 0) {
            $delete_query = "DELETE FROM warnings WHERE warning_id = $warning_id";
            if ($conn->query($delete_query)) {
                $message = '✓ Warning deleted successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error deleting warning: ' . $conn->error;
                $messageType = 'error';
            }
        }
    }
    
    // Handle Resolve Warning
    elseif ($_POST['action'] === 'resolve-warning') {
        $warning_id = intval($_POST['warning_id'] ?? 0);
        if ($warning_id > 0) {
            $resolve_query = "UPDATE warnings SET status='resolved', resolved_date=NOW() WHERE warning_id = $warning_id";
            if ($conn->query($resolve_query)) {
                $message = '✓ Warning marked as resolved.';
                $messageType = 'success';
            } else {
                $message = 'Error resolving warning: ' . $conn->error;
                $messageType = 'error';
            }
        }
    }
    
    // Handle Close Warning
    elseif ($_POST['action'] === 'close-warning') {
        $warning_id = intval($_POST['warning_id'] ?? 0);
        if ($warning_id > 0) {
            $close_query = "UPDATE warnings SET status='closed' WHERE warning_id = $warning_id";
            if ($conn->query($close_query)) {
                $message = '✓ Warning closed.';
                $messageType = 'success';
            } else {
                $message = 'Error closing warning: ' . $conn->error;
                $messageType = 'error';
            }
        }
    }
}

// Fetch all warnings with filtering
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$filter_query = "SELECT warning_id, student_id, student_name, warning_level, note, issued_date, status FROM warnings";

if ($filter_status !== 'all') {
    $filter_query .= " WHERE status='" . $conn->real_escape_string($filter_status) . "'";
}
$filter_query .= " ORDER BY issued_date DESC";

$warnings_result = $conn->query($filter_query);
$warnings = [];

if ($warnings_result) {
    while ($row = $warnings_result->fetch_assoc()) {
        $warnings[] = $row;
    }
}
$totalWarnings = count($warnings);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Warning Management - Complete</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
    .status-active { background: #fecaca; color: #dc2626; }
    .status-resolved { background: #bfdbfe; color: #1e40af; }
    .status-closed { background: #d1d5db; color: #374151; }
    .action-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-small { padding: 8px 12px; font-size: 0.9rem; }
    .btn-edit { background: #a749ff; color: white; }
    .btn-resolve { background: #3b82f6; color: white; }
    .btn-close { background: #f59e0b; color: white; }
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
        <h1>Warning Management</h1>
        <p>Complete warning management system with edit, update, delete, resolve, and close (Database Connected).</p>
      </div>
      <div class="page-nav">
        <a class="btn-secondary" href="index.php">Back to dashboard</a>
        <a class="btn-primary" href="fines-complete.php">Fine Module</a>
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
      <div class="section-title">Add New Warning</div>
      <form method="post" action="warnings-complete.php" novalidate>
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
            <label for="level">Warning Level *</label>
            <select id="level" name="level" required>
              <option value="">Select a level</option>
              <option value="Level 1">Level 1 - First Notice</option>
              <option value="Level 2">Level 2 - Second Notice</option>
              <option value="Level 3">Level 3 - Final Notice</option>
            </select>
          </div>
          <div class="full">
            <label for="note">Warning Note</label>
            <textarea id="note" name="note" placeholder="Write a detailed warning note" maxlength="1000"></textarea>
          </div>
          <div class="full">
            <button class="btn-primary" type="submit" name="action" value="add-warning">Add Warning</button>
          </div>
        </div>
      </form>
    </section>
    <?php else: ?>
    <section class="section form-panel">
      <div class="section-title">Edit Warning</div>
      <form method="post" action="warnings-complete.php" novalidate>
        <input type="hidden" name="warning_id" value="<?php echo $editWarning['warning_id']; ?>">
        <div class="form-grid">
          <div>
            <label for="student_id">Student *</label>
            <select id="student_id" name="student_id" required>
              <option value="">Select a student</option>
              <?php foreach ($students as $student): ?>
                <option value="<?php echo $student['student_id']; ?>" <?php echo $student['student_id'] == $editWarning['student_id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($student['student_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="level">Warning Level *</label>
            <select id="level" name="level" required>
              <option value="">Select a level</option>
              <option value="Level 1" <?php echo $editWarning['warning_level'] === 'Level 1' ? 'selected' : ''; ?>>Level 1 - First Notice</option>
              <option value="Level 2" <?php echo $editWarning['warning_level'] === 'Level 2' ? 'selected' : ''; ?>>Level 2 - Second Notice</option>
              <option value="Level 3" <?php echo $editWarning['warning_level'] === 'Level 3' ? 'selected' : ''; ?>>Level 3 - Final Notice</option>
            </select>
          </div>
          <div class="full">
            <label for="note">Warning Note</label>
            <textarea id="note" name="note" placeholder="Write a detailed warning note" maxlength="1000"><?php echo htmlspecialchars($editWarning['note']); ?></textarea>
          </div>
          <div class="full">
            <button class="btn-primary" type="submit" name="action" value="update-warning">Update Warning</button>
            <a class="btn-secondary" href="warnings-complete.php">Cancel</a>
          </div>
        </div>
      </form>
    </section>
    <?php endif; ?>

    <section class="section table-panel">
      <div class="section-title">Warning Records</div>
      
      <div class="filters">
        <a class="filter-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>" href="?status=all">All (<?php echo $totalWarnings; ?>)</a>
        <a class="filter-btn <?php echo $filter_status === 'active' ? 'active' : ''; ?>" href="?status=active">Active</a>
        <a class="filter-btn <?php echo $filter_status === 'resolved' ? 'active' : ''; ?>" href="?status=resolved">Resolved</a>
        <a class="filter-btn <?php echo $filter_status === 'closed' ? 'active' : ''; ?>" href="?status=closed">Closed</a>
      </div>

      <p class="notice">Total warnings: <span class="chip"><?php echo $totalWarnings; ?></span></p>
      
      <?php if ($totalWarnings === 0): ?>
        <p>No warnings recorded. Use the form above to add a new warning.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Level</th>
              <th>Note</th>
              <th>Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($warnings as $warning): ?>
            <tr>
              <td><?php echo htmlspecialchars($warning['student_name']); ?></td>
              <td><?php echo htmlspecialchars($warning['warning_level']); ?></td>
              <td><?php echo htmlspecialchars(substr($warning['note'], 0, 50)) . (strlen($warning['note']) > 50 ? '...' : ''); ?></td>
              <td><?php echo date('Y-m-d', strtotime($warning['issued_date'])); ?></td>
              <td>
                <span class="status-badge status-<?php echo $warning['status']; ?>">
                  <?php echo ucfirst($warning['status']); ?>
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <a href="?edit=<?php echo $warning['warning_id']; ?>" class="btn-small btn-edit">Edit</a>
                  <?php if ($warning['status'] === 'active'): ?>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="warning_id" value="<?php echo $warning['warning_id']; ?>">
                      <button type="submit" name="action" value="resolve-warning" class="btn-small btn-resolve" onclick="return confirm('Mark as resolved?')">Resolve</button>
                    </form>
                  <?php endif; ?>
                  <?php if ($warning['status'] !== 'closed'): ?>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="warning_id" value="<?php echo $warning['warning_id']; ?>">
                      <button type="submit" name="action" value="close-warning" class="btn-small btn-close" onclick="return confirm('Close this warning?')">Close</button>
                    </form>
                  <?php endif; ?>
                  <form method="post" style="display:inline;">
                    <input type="hidden" name="warning_id" value="<?php echo $warning['warning_id']; ?>">
                    <button type="submit" name="action" value="delete-warning" class="btn-small btn-delete" onclick="return confirm('Delete this warning?')">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <footer>All warning records are stored in XAMPP MySQL database.</footer>
  </main>
</body>
</html>
