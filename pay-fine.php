<?php
include 'config.php';

$message = '';
$messageType = '';
$fine_id = isset($_GET['fine_id']) ? intval($_GET['fine_id']) : 0;
$fine = null;

// Fetch fine details
if ($fine_id > 0) {
    $result = $conn->query("SELECT * FROM fines WHERE fine_id = $fine_id");
    if ($result && $result->num_rows > 0) {
        $fine = $result->fetch_assoc();
    }
}

if (!$fine) {
    $message = 'Fine not found.';
    $messageType = 'error';
}

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method']; // 'google_pay', 'mobile_pay'
    $amount = floatval($_POST['amount'] ?? 0);
    
    if ($amount != floatval($fine['fine_amount'])) {
        $message = 'Payment amount does not match fine amount.';
        $messageType = 'error';
    } else {
        // Create payment record in database
        $payment_id = 'PAY_' . uniqid() . '_' . time();
        $safe_method = $conn->real_escape_string($payment_method);
        
        $insert = $conn->query("INSERT INTO payments (fine_id, payment_id, amount, method, status, created_at) 
                               VALUES ($fine_id, '$payment_id', $amount, '$safe_method', 'pending', NOW())");
        
        if ($insert) {
            // Redirect to payment gateway based on method
            if ($payment_method === 'google_pay') {
                header("Location: process-payment.php?payment_id=$payment_id&method=google_pay");
            } else {
                header("Location: process-payment.php?payment_id=$payment_id&method=mobile_pay");
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pay Fine</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://js.stripe.com/v3/"></script>
  <style>
    .payment-container { max-width: 600px; margin: 0 auto; }
    .fine-details { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 24px; margin-bottom: 24px; }
    .fine-row { display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .fine-label { color: var(--muted); }
    .fine-value { font-weight: 600; }
    .payment-methods { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin: 24px 0; }
    .payment-btn { padding: 20px; border-radius: 16px; border: 2px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); cursor: pointer; transition: all 0.3s ease; }
    .payment-btn:hover { background: rgba(255,255,255,0.1); border-color: var(--purple); }
    .payment-btn.active { background: var(--purple); border-color: var(--purple); }
    .payment-icon { font-size: 2.5rem; margin-bottom: 12px; }
    .payment-title { font-weight: 600; }
    .payment-desc { font-size: 0.9rem; color: var(--muted); margin-top: 8px; }
    .secure-badge { display: flex; align-items: center; gap: 8px; color: #10b981; font-size: 0.9rem; margin: 16px 0; }
    @media (max-width: 760px) { .payment-methods { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <main class="container">
    <header class="header">
      <div>
        <h1>Pay Fine</h1>
        <p>Secure payment for library fine using Google Pay or Mobile Pay</p>
      </div>
      <div class="page-nav">
        <a class="btn-secondary" href="fines-complete.php">Back to Fines</a>
      </div>
    </header>

    <?php if ($message !== ''): ?>
      <div class="alert notice" style="border-left: 4px solid <?php echo $messageType === 'success' ? '#4ade80' : '#ff6b6b'; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>

    <?php if ($fine): ?>
    <div class="payment-container">
      <section class="fine-details">
        <div class="section-title">Fine Details</div>
        <div class="fine-row">
          <span class="fine-label">Student:</span>
          <span class="fine-value"><?php echo htmlspecialchars($fine['student_name']); ?></span>
        </div>
        <div class="fine-row">
          <span class="fine-label">Book:</span>
          <span class="fine-value"><?php echo htmlspecialchars($fine['book_title']); ?></span>
        </div>
        <div class="fine-row">
          <span class="fine-label">Reason:</span>
          <span class="fine-value"><?php echo htmlspecialchars($fine['reason']); ?></span>
        </div>
        <div class="fine-row">
          <span class="fine-label">Issued Date:</span>
          <span class="fine-value"><?php echo date('Y-m-d', strtotime($fine['issued_date'])); ?></span>
        </div>
        <div class="fine-row" style="border-bottom: none;">
          <span class="fine-label" style="font-size: 1.2rem; font-weight: 600;">Amount Due:</span>
          <span class="fine-value" style="font-size: 1.5rem; color: var(--orange);">kr <?php echo number_format(floatval($fine['fine_amount']), 2); ?></span>
        </div>
      </section>

      <div class="secure-badge">
        🔒 Secure payment powered by Stripe
      </div>

      <form method="POST" action="pay-fine.php" id="paymentForm">
        <input type="hidden" name="amount" value="<?php echo $fine['fine_amount']; ?>">
        
        <div class="section-title">Select Payment Method</div>
        <div class="payment-methods">
          <label class="payment-btn" style="cursor: pointer;">
            <input type="radio" name="payment_method" value="google_pay" style="display: none;" onchange="document.querySelector('.payment-btn.active')?.classList.remove('active'); this.parentElement.classList.add('active');">
            <div class="payment-icon">🔵</div>
            <div class="payment-title">Google Pay</div>
            <div class="payment-desc">Fast & secure payment</div>
          </label>

          <label class="payment-btn" style="cursor: pointer;">
            <input type="radio" name="payment_method" value="mobile_pay" style="display: none;" onchange="document.querySelector('.payment-btn.active')?.classList.remove('active'); this.parentElement.classList.add('active');">
            <div class="payment-icon">📱</div>
            <div class="payment-title">Mobile Pay</div>
            <div class="payment-desc">Mobile payment option</div>
          </label>
        </div>

        <div style="margin-top: 24px;">
          <button type="submit" class="btn-primary" style="width: 100%; padding: 16px;">
            Proceed to Payment
          </button>
          <a href="fines-complete.php" class="btn-secondary" style="width: 100%; padding: 16px; display: inline-block; text-align: center; margin-top: 12px;">
            Cancel
          </a>
        </div>
      </form>
    </div>
    <?php else: ?>
    <div class="card">
      <p>Fine not found. Please go back and select a valid fine to pay.</p>
      <a href="fines-complete.php" class="btn-primary">Back to Fines</a>
    </div>
    <?php endif; ?>

    <footer>All payments are processed securely through Stripe</footer>
  </main>

  <script>
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
      const method = document.querySelector('input[name="payment_method"]:checked');
      if (!method) {
        e.preventDefault();
        alert('Please select a payment method');
      }
    });
  </script>
</body>
</html>
