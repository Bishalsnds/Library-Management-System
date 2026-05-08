<?php
session_start();
require_once 'config.php';

$login_error = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['login_email'] ?? '');
    $password = $_POST['login_password'] ?? '';

    if (empty($email)) {
        $login_error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_error = 'Invalid email format';
    } elseif (empty($password)) {
        $login_error = 'Password is required';
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $login_error = 'Invalid email or password';
        } else {
            $user = $result->fetch_assoc();

            if ($user['status'] !== 'active') {
                $login_error = 'Your account is inactive';
            } elseif (!password_verify($password, $user['password'])) {
                $login_error = 'Invalid email or password';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                
                header('Location: dashboard.php');
                exit();
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Login</title>
    <link rel="stylesheet" href="public/assets/css/style.css">
    <style>
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #f5c6cb;
        }

        .login-footer {
            font-size: 13px;
            color: #999;
            text-align: center;
            margin-top: 15px;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side -->
        <div class="login-side">
            <div class="side-content">
                <div class="logo-container">
                    <img src="public/assets/images/logo.png.jpg" alt="Y.R.A.S.B. Logo" class="logo">
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

        <!-- Right Side -->
        <div class="login-wrapper">
            <div class="login-form-box">
                <h1>Login</h1>

                <?php if (!empty($login_error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>

                <form method="POST" action="index.php" class="login-form">
                    <div class="form-group">
                        <label for="login_email">Email Address</label>
                        <input 
                            type="email" 
                            id="login_email" 
                            name="login_email" 
                            placeholder="Enter your email"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="login_password">Password</label>
                        <input 
                            type="password" 
                            id="login_password" 
                            name="login_password" 
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <div class="form-options">
                        <label class="remember-checkbox">
                            <input type="checkbox" name="rememberMe">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="login-btn">Login</button>
                </form>

                <p class="test-creds">
                    <strong>Test Credentials:</strong><br>
                    Email: <strong>admin@library.com</strong><br>
                    Password: <strong>password123</strong>
                </p>

                <p class="login-footer">
                    Don't have an account? <a href="src/auth/signup.php">Sign up here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
