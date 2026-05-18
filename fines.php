<?php
session_start();
if (!isset($_SESSION['fines'])) {
    $_SESSION['fines'] = [];
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
    $student = trim($_POST['student'] ?? '');
    $book = trim($_POST['book'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    // Validate student name
    $studentValidation = validateInput($student);
    if (!$studentValidation['valid']) {
        $message = 'Student name error: ' . $studentValidation['error'];
        $messageType = 'error';
    } 
    // Validate book title
    elseif (!($bookValidation = validateInput($book))['valid']) {
        $message = 'Book title error: ' . $bookValidation['error'];
        $messageType = 'error';
    }
    // Validate amount
    elseif ($amount === '' || !is_numeric($amount) || (float)$amount <= 0) {
        $message = 'Please enter a valid positive fine amount (DKK).';
        $messageType = 'error';
    }
    elseif ((float)$amount > 100000) {
        $message = 'Fine amount cannot exceed 100,000 DKK.';
        $messageType = 'error';
    }
    else {
        $_SESSION['fines'][] = [
            'student' => htmlspecialchars($student, ENT_QUOTES, 'UTF-8'),
            'book' => htmlspecialchars($book, ENT_QUOTES, 'UTF-8'),
            'amount' => number_format((float)$amount, 2),
            'reason' => htmlspecialchars($reason ?: 'Overdue return', ENT_QUOTES, 'UTF-8'),
            'date' => date('Y-m-d H:i:s'),
        ];
        $message = '✓ Fine record added successfully.';
        $messageType = 'success';
    }
}
$fines = $_SESSION['fines'];
$totalFines = count($fines);
$balance = array_sum(array_map(fn($item) => floatval($item['amount']), $fines));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fine Management</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>Fine Management</h1>
        <p>Record overdue fines for library users and maintain a current list of active fines in the system.</p>
      </div>
      <div class="page-nav">
        <a class="btn-secondary" href="index.php">Back to dashboard</a>
        <a class="btn-primary" href="warnings.php">Warning Module</a>
      </div>
    </header>

    <?php if ($message !== ''): ?>
      <div class="alert notice" style="border-left: 4px solid <?php echo $messageType === 'success' ? '#4ade80' : '#ff6b6b'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <section class="section form-panel">
      <div class="section-title">Add New Fine</div>
      <form method="post" action="fines.php" novalidate>
        <div class="form-grid">
          <div>
            <label for="student">Student Name *</label>
            <input id="student" name="student" type="text" placeholder="Type student full name" required 
                   pattern="[a-zA-Z0-9\s\-\.æøåÆØÅ]{2,100}"
                   title="Student name: 2-100 characters, letters, numbers, spaces, hyphens only">
          </div>
          <div>
            <label for="book">Book Title *</label>
            <input id="book" name="book" type="text" placeholder="Type book title" required 
                   pattern="[a-zA-Z0-9\s\-\.æøåÆØÅ]{2,100}"
                   title="Book title: 2-100 characters, letters, numbers, spaces, hyphens only">
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
            <button class="btn-primary" type="submit" name="action" value="add-fine">Save Fine</button>
          </div>
        </div>
      </form>
    </section>

    <section class="section table-panel">
      <div class="section-title">Fine Overview</div>
      <p class="notice">Currently <span class="chip"><?php echo $totalFines; ?> fines</span> totaling <span class="chip">kr <?php echo number_format($balance, 2); ?></span>.</p>
      <?php if ($totalFines === 0): ?>
        <p>No fine records have been added yet. Use the form above to create a new fine.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Book</th>
              <th>Amount</th>
              <th>Reason</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($fines as $fine): ?>
            <tr>
              <td><?php echo $fine['student']; ?></td>
              <td><?php echo $fine['book']; ?></td>
              <td>kr <?php echo $fine['amount']; ?></td>
              <td><?php echo $fine['reason']; ?></td>
              <td><?php echo $fine['date']; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <footer>Fine list is currently stored in PHP session for demo. Database integration comes next.</footer>
  </main>
</body>
</html>
