<?php
session_start();
require_once '../../config.php';

$error = '';
$success = '';
$step = 1; // Step 1: Enter email, Step 2: Verify reset token

// Check if user is trying to reset with a token
if (isset($_GET['token'])) {
    $token = sanitize($_GET['token'] ?? '');
    $step = 2;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset-password') {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($password)) {
            $error = 'Password is required';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            // Update password in database
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            // For this implementation, we'll use a simple token-based approach stored in session
            // In production, you'd want to store tokens in the database with expiration times
            if (isset($_SESSION['reset_email'])) {
                $email = $_SESSION['reset_email'];
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bind_param('ss', $hashedPassword, $email);
                
                if ($stmt->execute()) {
                    $success = 'Password has been reset successfully! You can now login with your new password.';
                    unset($_SESSION['reset_email']);
                } else {
                    $error = 'Failed to reset password. Please try again.';
                }
                $stmt->close();
            } else {
                $error = 'Invalid reset token. Please request a new password reset.';
            }
        }
    }
}
// Step 1: User enters email to request password reset
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request-reset') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Check if email exists in database
        $stmt = $conn->prepare("SELECT id, first_name FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = 'Email address not found in our system';
        } else {
            $user = $result->fetch_assoc();
            
            // Generate a simple token (in production, store this with expiration in DB)
            $token = bin2hex(random_bytes(32));
            
            // Store email in session for verification
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_token'] = $token;
            $_SESSION['reset_token_time'] = time();
            
            $success = 'Please check your email for further instructions. For this demo, you can use the link below to reset your password.';
            $step = 2;
            
            // In production, send email with reset link:
            // $resetLink = 'https://yourdomain.com/src/auth/forgot-password.php?token=' . $token;
            // sendEmail($email, 'Password Reset Request', $resetLink);
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Library Management System</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        .field-error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
            min-height: 18px;
        }
        .input-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.12) !important;
        }
        .input-valid {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.10) !important;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-side">
            <div class="side-content">
                <div class="logo-section">
                    <img src="../../public/assets/images/logo.png.jpg" alt="Library Management System Logo" class="login-logo">
                </div>
                <h1>Library System</h1>
                <p>Manage books efficiently</p>
                <ul class="features-list">
                    <li>Easy Book Access</li>
                    <li>Track Borrowings</li>
                    <li>User Friendly</li>
                </ul>
            </div>
        </div>

        <div class="login-wrapper">
            <div class="login-form-box">
                <?php if ($step === 1): ?>
                    <h1>Forgot Password?</h1>
                    <p style="color: #666; margin-bottom: 20px; font-size: 14px;">Enter your email address and we'll help you reset your password.</p>
                    
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="forgot-password.php" class="login-form" id="resetForm" novalidate>
                        <input type="hidden" name="action" value="request-reset">
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                autocomplete="email"
                                required
                            >
                            <span class="field-error" id="email-error"></span>
                        </div>

                        <button type="submit" class="login-btn">Request Password Reset</button>
                    </form>

                    <p class="login-footer">
                        Remember your password? <a href="login.php" style="color: #667eea; text-decoration: none;">Back to Login</a>
                    </p>

                <?php else: ?>
                    <h1>Reset Your Password</h1>
                    
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($success && !$error): ?>
                        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                        <p class="login-footer" style="margin-top: 20px; text-align: center;">
                            <a href="login.php" style="color: #667eea; text-decoration: none;">Return to Login</a>
                        </p>
                    <?php else: ?>
                        <p style="color: #666; margin-bottom: 20px; font-size: 14px;">Enter your new password below.</p>

                        <form method="POST" action="forgot-password.php<?php echo isset($_GET['token']) ? '?token=' . htmlspecialchars($_GET['token']) : ''; ?>" class="login-form" id="newPasswordForm" novalidate>
                            <input type="hidden" name="action" value="reset-password">
                            
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Enter new password"
                                    required
                                >
                                <span class="field-error" id="password-error"></span>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    placeholder="Confirm your password"
                                    required
                                >
                                <span class="field-error" id="confirm-password-error"></span>
                            </div>

                            <button type="submit" class="login-btn">Reset Password</button>
                        </form>
                    <?php endif; ?>

                    <p class="login-footer">
                        <a href="login.php" style="color: #667eea; text-decoration: none;">Back to Login</a>
                    </p>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function setError(inputId, errorId, message) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            if (input) {
                input.classList.add('input-invalid');
                input.classList.remove('input-valid');
            }
            if (error) error.textContent = message;
        }

        function setValid(inputId, errorId) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            if (input) {
                input.classList.remove('input-invalid');
                input.classList.add('input-valid');
            }
            if (error) error.textContent = '';
        }

        function validateEmail() {
            const val = document.getElementById('email')?.value.trim();
            if (!val) {
                setError('email', 'email-error', 'Email address is required.');
                return false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                setError('email', 'email-error', 'Please enter a valid email address.');
                return false;
            }
            setValid('email', 'email-error');
            return true;
        }

        function validatePassword() {
            const val = document.getElementById('password')?.value;
            if (!val) {
                setError('password', 'password-error', 'Password is required.');
                return false;
            }
            if (val.length < 6) {
                setError('password', 'password-error', 'Password must be at least 6 characters.');
                return false;
            }
            setValid('password', 'password-error');
            return true;
        }

        function validateConfirmPassword() {
            const password = document.getElementById('password')?.value;
            const confirmPassword = document.getElementById('confirm_password')?.value;
            if (!confirmPassword) {
                setError('confirm_password', 'confirm-password-error', 'Please confirm your password.');
                return false;
            }
            if (password !== confirmPassword) {
                setError('confirm_password', 'confirm-password-error', 'Passwords do not match.');
                return false;
            }
            setValid('confirm_password', 'confirm-password-error');
            return true;
        }

        // Attach event listeners if elements exist
        if (document.getElementById('email')) {
            document.getElementById('email').addEventListener('blur', validateEmail);
            document.getElementById('email').addEventListener('input', function() {
                if (this.classList.contains('input-invalid')) validateEmail();
            });
        }

        if (document.getElementById('resetForm')) {
            document.getElementById('resetForm').addEventListener('submit', function(e) {
                if (!validateEmail()) e.preventDefault();
            });
        }

        if (document.getElementById('password')) {
            document.getElementById('password').addEventListener('blur', validatePassword);
            document.getElementById('password').addEventListener('input', function() {
                if (this.classList.contains('input-invalid')) validatePassword();
            });
        }

        if (document.getElementById('confirm_password')) {
            document.getElementById('confirm_password').addEventListener('blur', validateConfirmPassword);
            document.getElementById('confirm_password').addEventListener('input', function() {
                if (this.classList.contains('input-invalid')) validateConfirmPassword();
            });
        }

        if (document.getElementById('newPasswordForm')) {
            document.getElementById('newPasswordForm').addEventListener('submit', function(e) {
                const passOk = validatePassword();
                const confirmOk = validateConfirmPassword();
                if (!passOk || !confirmOk) e.preventDefault();
            });
        }
    </script>
</body>
</html>
