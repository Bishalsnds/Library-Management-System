<?php
session_start();
require_once '../../config.php';
require_once '../lib/EmailHelper.php';

$message = '';
$message_type = 'error';

if (isset($_GET['token'])) {
    $token = sanitize($_GET['token']);
    
    // Verify token exists and hasn't expired
    $verify_token = $conn->prepare("SELECT id, user_id, email FROM email_verifications WHERE verification_token = ? AND token_expires_at > NOW() AND verified_at IS NULL");
    $verify_token->bind_param('s', $token);
    $verify_token->execute();
    $result = $verify_token->get_result();
    
    if ($result->num_rows > 0) {
        $verification = $result->fetch_assoc();
        $user_id = $verification['user_id'];
        $email = $verification['email'];
        
        // Update user status to active
        $update_user = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $update_user->bind_param('i', $user_id);
        
        if ($update_user->execute()) {
            // Mark verification as completed
            $mark_verified = $conn->prepare("UPDATE email_verifications SET verified_at = NOW() WHERE verification_token = ?");
            $mark_verified->bind_param('s', $token);
            $mark_verified->execute();
            
            // Get user info for welcome email
            $get_user = $conn->prepare("SELECT first_name FROM users WHERE id = ?");
            $get_user->bind_param('i', $user_id);
            $get_user->execute();
            $user_result = $get_user->get_result();
            $user = $user_result->fetch_assoc();
            
            // Send welcome email
            EmailHelper::sendWelcomeEmail($email, $user['first_name']);
            
            $message = '✓ Email verified successfully! Your account is now active. You can login now.';
            $message_type = 'success';
            
            $get_user->close();
        } else {
            $message = '✗ Error activating account. Please try again or contact support.';
        }
        
        $update_user->close();
    } else {
        $message = '✗ Invalid or expired verification link. Please register again or contact support.';
    }
    
    $verify_token->close();
} else {
    $message = '✗ No verification token provided.';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Library Management System</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        .verification-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: #faf9fb;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .verification-header {
            background: linear-gradient(135deg, #845Ec2 0%, #4B4453 100%);
            color: white;
            padding: 30px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .verification-header h1 {
            margin: 0;
            font-size: 28px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            font-size: 16px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #f5c6cb;
            font-size: 16px;
        }

        .login-link {
            display: inline-block;
            background: linear-gradient(135deg, #C34A36 0%, #FF8066 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 20px;
        }

        .login-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(195, 74, 54, 0.3);
        }

        .contact-support {
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-header">
            <h1>Email Verification</h1>
        </div>

        <?php if ($message_type === 'success'): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <p>Redirecting to login page in 3 seconds...</p>
            <a href="../../index.php" class="login-link">Go to Login Now</a>
            <script>
                setTimeout(function() {
                    window.location.href = '../../index.php';
                }, 3000);
            </script>
        <?php else: ?>
            <div class="error-message">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <a href="signup.php" class="login-link">Back to Signup</a>
            <div class="contact-support">
                <p>Having issues? <a href="mailto:support@lms-system.com">Contact Support</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
