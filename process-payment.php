<?php
include 'config.php';

$payment_id = isset($_GET['payment_id']) ? $_GET['payment_id'] : null;
$method = isset($_GET['method']) ? $_GET['method'] : null;
$message = '';
$messageType = '';

// Fetch payment details
if ($payment_id) {
    $result = $conn->query("SELECT p.*, f.fine_id, f.student_name, f.book_title, f.fine_amount 
                           FROM payments p 
                           JOIN fines f ON p.fine_id = f.fine_id 
                           WHERE p.payment_id = '$payment_id'");
    
    if (!$result || $result->num_rows === 0) {
        $message = 'Payment not found.';
        $messageType = 'error';
    } else {
        $payment = $result->fetch_assoc();
    }
}

// Simulate payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'process') {
    $transaction_id = 'TXN_' . uniqid() . '_' . time();
    
    // Update payment status to completed
    $update_payment = $conn->query("UPDATE payments SET status='completed', transaction_id='$transaction_id', updated_at=NOW() 
                                   WHERE payment_id='$payment_id'");
    
    // Update fine status to paid
    if ($update_payment && isset($payment['fine_id'])) {
        $conn->query("UPDATE fines SET status='paid', payment_date=NOW() WHERE fine_id=" . $payment['fine_id']);
        
        $message = '✓ Payment successful! Your fine has been marked as paid.';
        $messageType = 'success';
        
        // Redirect after 3 seconds
        header("Refresh: 3; url=fines-complete.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Process Payment</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    .payment-processing { max-width: 500px; margin: 50px auto; text-align: center; }
    .status-icon { font-size: 4rem; margin: 24px 0; }
    .spinner { display: inline-block; width: 40px; height: 40px; border: 4px solid rgba(167,73,255,0.3); border-top: 4px solid var(--purple); border-radius: 50%; animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .payment-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 24px; margin: 24px 0; }
    .detail-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .detail-row:last-child { border-bottom: none; }
  </style>
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>Process Payment</h1>
        <p><?php echo $method === 'google_pay' ? 'Google Pay' : 'Mobile Pay'; ?> Payment Gateway</p>
      </div>
    </header>

    <?php if ($message !== ''): ?>
      <div class="alert notice" style="border-left: 4px solid <?php echo $messageType === 'success' ? '#4ade80' : '#ff6b6b'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <div class="payment-processing">
      <?php if (isset($payment) && $payment['status'] === 'pending'): ?>
        <div class="status-icon">
          <div class="spinner"></div>
        </div>
        <h2>Processing Payment</h2>
        <p>Please wait while we process your payment...</p>

        <div class="payment-card">
          <div class="detail-row">
            <span>Student:</span>
            <strong><?php echo htmlspecialchars($payment['student_name']); ?></strong>
          </div>
          <div class="detail-row">
            <span>Book:</span>
            <strong><?php echo htmlspecialchars($payment['book_title']); ?></strong>
          </div>
          <div class="detail-row">
            <span>Amount:</span>
            <strong>kr <?php echo number_format($payment['amount'], 2); ?></strong>
          </div>
          <div class="detail-row">
            <span>Method:</span>
            <strong><?php echo $method === 'google_pay' ? 'Google Pay' : 'Mobile Pay'; ?></strong>
          </div>
        </div>

        <form method="POST" action="process-payment.php?payment_id=<?php echo htmlspecialchars($payment_id); ?>&method=<?php echo htmlspecialchars($method); ?>" style="margin-top: 24px;">
          <button type="submit" name="action" value="process" class="btn-primary" style="width: 100%; padding: 14px;">
            Complete Payment
          </button>
        </form>

        <a href="fines-complete.php" class="btn-secondary" style="display: block; margin-top: 12px; padding: 14px;">
          Cancel Payment
        </a>

      <?php elseif (isset($payment) && $payment['status'] === 'completed'): ?>
        <div class="status-icon">✅</div>
        <h2>Payment Successful!</h2>
        <p>Your fine has been paid successfully.</p>

        <div class="payment-card">
          <div class="detail-row">
            <span>Transaction ID:</span>
            <strong><?php echo htmlspecialchars($payment['transaction_id']); ?></strong>
          </div>
          <div class="detail-row">
            <span>Amount Paid:</span>
            <strong>kr <?php echo number_format($payment['amount'], 2); ?></strong>
          </div>
          <div class="detail-row">
            <span>Payment Method:</span>
            <strong><?php echo $method === 'google_pay' ? 'Google Pay' : 'Mobile Pay'; ?></strong>
          </div>
          <div class="detail-row">
            <span>Date & Time:</span>
            <strong><?php echo date('Y-m-d H:i:s', strtotime($payment['updated_at'])); ?></strong>
          </div>
        </div>

        <p style="color: var(--muted); margin-top: 16px;">Redirecting to fines page...</p>
        <a href="fines-complete.php" class="btn-primary" style="display: block; margin-top: 16px; padding: 14px;">
          Back to Fines
        </a>

      <?php else: ?>
        <div class="card">
          <p>Payment information not found or invalid.</p>
          <a href="fines-complete.php" class="btn-primary">Back to Fines</a>
        </div>
      <?php endif; ?>
    </div>

    <footer>Secure payment processing</footer>
  </main>
</body>
</html>
