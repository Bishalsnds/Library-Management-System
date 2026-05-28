<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

include 'config.php';

$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Ensure payments table exists before querying
$conn->query("CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id VARCHAR(100) UNIQUE NOT NULL,
    fine_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('google_pay','mobile_pay') NOT NULL,
    status ENUM('pending','completed','failed','cancelled') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fine_id) REFERENCES fines(fine_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Fetch all payments with fine details
$payments_query = "SELECT p.*, f.student_name, f.book_title, f.fine_amount
                   FROM payments p
                   JOIN fines f ON p.fine_id = f.fine_id
                   ORDER BY p.created_at DESC";

$payments_result = $conn->query($payments_query);
$all_payments = [];
$total_amount = 0;
$completed_amount = 0;

if ($payments_result) {
    while ($row = $payments_result->fetch_assoc()) {
        $all_payments[] = $row;
        $total_amount += floatval($row['amount']);
        if ($row['status'] === 'completed') {
            $completed_amount += floatval($row['amount']);
        }
    }
}

// Count per status for filter buttons
$status_counts = ['completed' => 0, 'pending' => 0, 'failed' => 0, 'cancelled' => 0];
foreach ($all_payments as $p) {
    if (isset($status_counts[$p['status']])) {
        $status_counts[$p['status']]++;
    }
}

// Apply filter for display
$payments = ($filter_status === 'all')
    ? $all_payments
    : array_values(array_filter($all_payments, fn($p) => $p['status'] === $filter_status));

$totalPayments = count($all_payments);
$filteredCount = count($payments);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment History</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
    .status-pending   { background: #fef3c7; color: #b45309; }
    .status-completed { background: #dcfce7; color: #16a34a; }
    .status-failed    { background: #fee2e2; color: #dc2626; }
    .status-cancelled { background: #f3e8ff; color: #7c3aed; }

    .filters { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .filter-btn {
      padding: 10px 18px;
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 20px;
      cursor: pointer;
      background: rgba(255,255,255,0.05);
      color: var(--text);
      text-decoration: none;
      font-size: 0.9rem;
      transition: background 0.2s, border-color 0.2s;
    }
    .filter-btn:hover { background: rgba(255,255,255,0.12); }
    .filter-btn.active { background: var(--purple); border-color: var(--purple); color: #fff; }

    .stat-card {
      display: inline-block;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 16px;
      padding: 16px 24px;
      margin-right: 16px;
      margin-bottom: 16px;
    }
    .stat-label { color: var(--muted); font-size: 0.9rem; }
    .stat-value { font-size: 1.8rem; font-weight: 700; color: var(--orange); margin-top: 4px; }

    .empty-state {
      text-align: center;
      padding: 48px 24px;
      color: var(--muted);
    }
    .empty-state p { margin-top: 8px; }
  </style>
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>Payment History</h1>
        <p>Track all fine payments made through Google Pay and Mobile Pay</p>
      </div>
      <div class="page-nav">
        <a class="btn-secondary" href="index.php">Back to dashboard</a>
        <a class="btn-primary" href="fines-complete.php">Fines Module</a>
      </div>
    </header>

    <section class="section">
      <div class="stat-card">
        <div class="stat-label">Total Transactions</div>
        <div class="stat-value"><?php echo $totalPayments; ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Total Amount</div>
        <div class="stat-value">kr <?php echo number_format($total_amount, 2); ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Completed</div>
        <div class="stat-value">kr <?php echo number_format($completed_amount, 2); ?></div>
      </div>
    </section>

    <section class="section table-panel">
      <div class="section-title">All Payments</div>

      <div class="filters">
        <a class="filter-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>" href="?status=all">
          All (<?php echo $totalPayments; ?>)
        </a>
        <a class="filter-btn <?php echo $filter_status === 'completed' ? 'active' : ''; ?>" href="?status=completed">
          Completed (<?php echo $status_counts['completed']; ?>)
        </a>
        <a class="filter-btn <?php echo $filter_status === 'pending' ? 'active' : ''; ?>" href="?status=pending">
          Pending (<?php echo $status_counts['pending']; ?>)
        </a>
        <a class="filter-btn <?php echo $filter_status === 'failed' ? 'active' : ''; ?>" href="?status=failed">
          Failed (<?php echo $status_counts['failed']; ?>)
        </a>
        <a class="filter-btn <?php echo $filter_status === 'cancelled' ? 'active' : ''; ?>" href="?status=cancelled">
          Cancelled (<?php echo $status_counts['cancelled']; ?>)
        </a>
      </div>

      <?php if ($filteredCount === 0): ?>
        <div class="empty-state">
          <strong><?php echo $totalPayments === 0 ? 'No payments recorded yet.' : 'No ' . htmlspecialchars($filter_status) . ' payments found.'; ?></strong>
          <?php if ($totalPayments === 0): ?>
            <p>Payments will appear here once students pay their fines.</p>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Student</th>
              <th>Book</th>
              <th>Amount</th>
              <th>Method</th>
              <th>Status</th>
              <th>Transaction ID</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $payment): ?>
            <tr>
              <td><?php echo htmlspecialchars($payment['student_name']); ?></td>
              <td><?php echo htmlspecialchars($payment['book_title']); ?></td>
              <td>kr <?php echo number_format(floatval($payment['amount']), 2); ?></td>
              <td>
                <span style="font-weight: 600;">
                  <?php echo $payment['method'] === 'google_pay' ? '🔵 Google Pay' : '📱 Mobile Pay'; ?>
                </span>
              </td>
              <td>
                <span class="status-badge status-<?php echo htmlspecialchars($payment['status']); ?>">
                  <?php echo ucfirst($payment['status']); ?>
                </span>
              </td>
              <td>
                <code style="background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">
                  <?php echo htmlspecialchars($payment['transaction_id'] ?: 'N/A'); ?>
                </code>
              </td>
              <td><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <footer>Secure payment tracking for library fines</footer>
  </main>
</body>
</html>
