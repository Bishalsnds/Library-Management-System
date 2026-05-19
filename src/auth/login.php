<?php
session_start();
require_once '../../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email)) {
        $error = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (empty($password)) {
        $error = 'Password is required';
    } else {
        // Fetch user from database
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = 'Invalid email or password';
        } else {
            $user = $result->fetch_assoc();

            // Check if account is active
            if ($user['status'] !== 'active') {
                $error = 'Your account is inactive';
            } elseif (!password_verify($password, $user['password'])) {
                $error = 'Invalid email or password';
            } else {
                // Login successful - create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                
                $success = 'Login successful!';
                
                // Redirect to dashboard
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

                <form method="POST" action="login.php" class="login-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <div class="form-options">
                        <label>
                            <input type="checkbox" name="rememberMe"> Remember me
                        </label>
                    </div>

                    <button type="submit" class="login-btn">Login</button>
                </form>

                <p class="test-creds">
                    <strong>Test Credentials:</strong><br>
                    Student: <strong>john@gmail.com</strong> / <strong>password123</strong><br>
                    Admin: <strong>admin@gmail.com</strong> / <strong>password123</strong>
                </p>

                <p class="login-footer">
                    Don't have an account? <a href="signup.php" style="color: #667eea; text-decoration: none;">Sign up here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
