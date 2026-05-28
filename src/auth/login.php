<?php
session_start();
require_once '../../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (empty($password)) {
        $error = 'Password is required';
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = 'Invalid email or password';
        } else {
            $user = $result->fetch_assoc();

            if ($user['status'] !== 'active') {
                $error = 'Your account is inactive';
            } elseif (!password_verify($password, $user['password'])) {
                $error = 'Invalid email or password';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];

                $success = 'Login successful!';
                header('Location: ../../index.php');
                exit();
            }
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
    <title>Library Management System - Login</title>
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
                <h1>Login</h1>
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="login-form" id="loginForm" novalidate>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            autocomplete="email"
                        >
                        <span class="field-error" id="email-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                        >
                        <span class="field-error" id="password-error"></span>
                    </div>

                    <div class="form-options">
                        <label>
                            <input type="checkbox" name="rememberMe"> Remember me
                        </label>
                    </div>

                    <button type="submit" class="login-btn">Login</button>
                </form>

                <p class="login-footer">
                    Don't have an account? <a href="signup.php" style="color: #667eea; text-decoration: none;">Sign up here</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function setError(inputId, errorId, message) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            input.classList.add('input-invalid');
            input.classList.remove('input-valid');
            error.textContent = message;
        }

        function setValid(inputId, errorId) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            input.classList.remove('input-invalid');
            input.classList.add('input-valid');
            error.textContent = '';
        }

        function clearState(inputId, errorId) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            input.classList.remove('input-invalid', 'input-valid');
            error.textContent = '';
        }

        function validateEmail() {
            const val = document.getElementById('email').value.trim();
            if (!val) {
                setError('email', 'email-error', 'Email address is required.');
                return false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                setError('email', 'email-error', 'Please enter a valid email address (e.g. user@example.com).');
                return false;
            }
            setValid('email', 'email-error');
            return true;
        }

        function validatePassword() {
            const val = document.getElementById('password').value;
            if (!val) {
                setError('password', 'password-error', 'Password is required.');
                return false;
            }
            setValid('password', 'password-error');
            return true;
        }

        document.getElementById('email').addEventListener('blur', validateEmail);
        document.getElementById('email').addEventListener('input', function() {
            if (this.classList.contains('input-invalid')) validateEmail();
        });

        document.getElementById('password').addEventListener('blur', validatePassword);
        document.getElementById('password').addEventListener('input', function() {
            if (this.classList.contains('input-invalid')) validatePassword();
        });

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const emailOk = validateEmail();
            const passOk = validatePassword();
            if (!emailOk || !passOk) e.preventDefault();
        });
    </script>
</body>
</html>
