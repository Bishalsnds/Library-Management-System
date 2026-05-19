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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add-warning') {
    $student_id = intval($_POST['student_id'] ?? 0);
    $level = trim($_POST['level'] ?? '');
    $note = trim($_POST['note'] ?? '');

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
        // Insert into database
        $safe_student_name = $conn->real_escape_string($student_name);
        $safe_note = $conn->real_escape_string($note ?: 'Follow policy and return items on time.');
        $safe_level = $conn->real_escape_string($level);

        $insert_query = "INSERT INTO warnings (student_id, student_name, warning_level, note, status) 
                        VALUES ($student_id, '$safe_student_name', '$safe_level', '$safe_note', 'active')";
        
        if ($conn->query($insert_query)) {
            $message = '✓ Warning has been recorded successfully.';
            $messageType = 'success';
        } else {
            $message = 'Error adding warning: ' . $conn->error;
            $messageType = 'error';
        }
    }
}

// Fetch all active warnings
$warnings_query = "SELECT warning_id, student_name, warning_level, note, issued_date, status FROM warnings WHERE status='active' ORDER BY issued_date DESC";
$warnings_result = $conn->query($warnings_query);
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
  <title>Warning Management - Database Version</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>Warning Management</h1>
        <p>Issue library warnings for students and keep track of their warning history (Database Enabled).</p>
      </div>
      <div class="page-nav">
        <a class="btn-secondary" href="index.php">Back to dashboard</a>
        <a class="btn-primary" href="fines-db.php">Fine Module</a>
      </div>
    </header>

    <?php if ($message !== ''): ?>
      <div class="alert notice" style="border-left: 4px solid <?php echo $messageType === 'success' ? '#4ade80' : '#ff6b6b'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <section class="section form-panel">
      <div class="section-title">Add New Warning</div>
      <form method="post" action="warnings-db.php" novalidate>
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
            <button class="btn-primary" type="submit" name="action" value="add-warning">Record Warning to Database</button>
          </div>
        </div>
      </form>
    </section>

    <section class="section table-panel">
      <div class="section-title">Active Warnings</div>
      <p class="notice">There are <span class="chip"><?php echo $totalWarnings; ?> active warnings</span> in the database.</p>
      <?php if ($totalWarnings === 0): ?>
        <p>No active warnings recorded. Use the form above to add a new warning.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Level</th>
              <th>Note</th>
              <th>Date Issued</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($warnings as $warning): ?>
            <tr>
              <td><?php echo htmlspecialchars($warning['student_name']); ?></td>
              <td><?php echo htmlspecialchars($warning['warning_level']); ?></td>
              <td><?php echo htmlspecialchars($warning['note']); ?></td>
              <td><?php echo date('Y-m-d', strtotime($warning['issued_date'])); ?></td>
              <td><?php echo htmlspecialchars($warning['status']); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <footer>Warning records are now stored in XAMPP MySQL database. Data persists even after closing the browser.</footer>
  </main>
</body>
</html>
