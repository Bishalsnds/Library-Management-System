<?php
session_start();
if (!isset($_SESSION['warnings'])) {
    $_SESSION['warnings'] = [];
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
    $student = trim($_POST['student'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $note = trim($_POST['note'] ?? '');

    // Validate student name
    $studentValidation = validateInput($student);
    if (!$studentValidation['valid']) {
        $message = 'Student name error: ' . $studentValidation['error'];
        $messageType = 'error';
    } 
    // Validate level
    elseif ($level === '' || !in_array($level, ['Level 1', 'Level 2', 'Level 3'])) {
        $message = 'Please select a valid warning level.';
        $messageType = 'error';
    }
    else {
        $_SESSION['warnings'][] = [
            'student' => htmlspecialchars($student, ENT_QUOTES, 'UTF-8'),
            'level' => htmlspecialchars($level, ENT_QUOTES, 'UTF-8'),
            'note' => htmlspecialchars($note ?: 'Follow policy and return items on time.', ENT_QUOTES, 'UTF-8'),
            'date' => date('Y-m-d H:i:s'),
        ];
        $message = '✓ Warning has been recorded successfully.';
        $messageType = 'success';
    }
}
$warningsession = $_SESSION['warnings'];
$totalWarnings = count($warningsession);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Warning Management</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>Warning Management</h1>
        <p>Issue library warnings for students and keep track of their warning history in a modern color-coded interface.</p>
      </div>
      <div class="page-nav">
        <a class="btn-secondary" href="index.php">Back to dashboard</a>
        <a class="btn-primary" href="fines.php">Fine Module</a>
      </div>
    </header>

    <?php if ($message !== ''): ?>
      <div class="alert notice" style="border-left: 4px solid <?php echo $messageType === 'success' ? '#4ade80' : '#ff6b6b'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <section class="section form-panel">
      <div class="section-title">Add New Warning</div>
      <form method="post" action="warnings.php" novalidate>
        <div class="form-grid">
          <div>
            <label for="student">Student Name *</label>
            <input id="student" name="student" type="text" placeholder="Enter student name" required 
                   pattern="[a-zA-Z0-9\s\-\.æøåÆØÅ]{2,100}"
                   title="Student name: 2-100 characters, letters, numbers, spaces, hyphens only">
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
            <textarea id="note" name="note" placeholder="Write a short warning note" maxlength="500"></textarea>
          </div>
          <div class="full">
            <button class="btn-primary" type="submit" name="action" value="add-warning">Record Warning</button>
          </div>
        </div>
      </form>
    </section>

    <section class="section table-panel">
      <div class="section-title">Warnings Overview</div>
      <p class="notice">There are <span class="chip"><?php echo $totalWarnings; ?> warnings</span> recorded in this session.</p>
      <?php if ($totalWarnings === 0): ?>
        <p>No warnings have been recorded yet. Use the form above to add your first warning.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Level</th>
              <th>Note</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($warningsession as $warning): ?>
            <tr>
              <td><?php echo $warning['student']; ?></td>
              <td><?php echo $warning['level']; ?></td>
              <td><?php echo $warning['note']; ?></td>
              <td><?php echo $warning['date']; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <footer>Warning records are temporarily stored in PHP session for the UI demo. Database support can be added next.</footer>
  </main>
</body>
</html>
