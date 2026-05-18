<?php
session_start();
require_once 'config.php';

$login_error = '';
$signup_error = '';
$signup_success = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_email'])) {
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

// Handle Signup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup_email'])) {
    $first_name = sanitize($_POST['firstName'] ?? '');
    $last_name = sanitize($_POST['lastName'] ?? '');
    $email = sanitize($_POST['signup_email'] ?? '');
    $student_id = sanitize($_POST['studentId'] ?? '');
    $password = $_POST['signup_password'] ?? '';
    $confirm_password = $_POST['confirmPassword'] ?? '';

    if (empty($first_name)) {
        $signup_error = 'First name is required';
    } elseif (empty($last_name)) {
        $signup_error = 'Last name is required';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signup_error = 'Valid email is required';
    } elseif (empty($student_id)) {
        $signup_error = 'Student ID is required';
    } elseif (empty($password) || strlen($password) < 6) {
        $signup_error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $signup_error = 'Passwords do not match';
    } else {
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param('s', $email);
        $check_email->execute();
        $result = $check_email->get_result();

        if ($result->num_rows > 0) {
            $signup_error = 'Email already registered';
        } else {
            $check_id = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
            $check_id->bind_param('s', $student_id);
            $check_id->execute();
            $result = $check_id->get_result();

            if ($result->num_rows > 0) {
                $signup_error = 'Student ID already registered';
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $insert = $conn->prepare("INSERT INTO users (first_name, last_name, email, student_id, password, role, status) VALUES (?, ?, ?, ?, ?, 'student', 'active')");
                $insert->bind_param('sssss', $first_name, $last_name, $email, $student_id, $hashed_password);

                if ($insert->execute()) {
                    $signup_success = 'Account created successfully! You can now login.';
                } else {
                    $signup_error = 'Error creating account: ' . $conn->error;
                }
                $insert->close();
            }
            $check_id->close();
        }
        $check_email->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System - Login & Signup</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #999;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-button.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #f5c6cb;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side -->
        <div class="login-side">
            <div class="side-content">
                <div class="logo-container">
                    <img src="logo.png.jpg" alt="Y.R.A.S.B. Logo" class="logo">
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
                <!-- Tabs -->
                <div class="tabs">
                    <button class="tab-button active" id="login-tab">Login</button>
                    <button class="tab-button" id="signup-tab">Sign Up</button>
                </div>

                <!-- LOGIN FORM -->
                <div class="form-section active" id="login-form-section">
                    <?php if (!empty($login_error)): ?>
                        <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="auth.php" class="login-form">
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
                </div>

                <!-- SIGNUP FORM -->
                <div class="form-section" id="signup-form-section">
                    <?php if (!empty($signup_error)): ?>
                        <div class="error-message"><?php echo htmlspecialchars($signup_error); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($signup_success)): ?>
                        <div class="success-message"><?php echo htmlspecialchars($signup_success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="auth.php" class="login-form">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" name="firstName" placeholder="Enter your first name" required>
                        </div>

                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="lastName" placeholder="Enter your last name" required>
                        </div>

                        <div class="form-group">
                            <label for="signup_email">Email Address</label>
                            <input type="email" id="signup_email" name="signup_email" placeholder="Enter your email" required>
                        </div>

                        <div class="form-group">
                            <label for="studentId">Student ID</label>
                            <input type="text" id="studentId" name="studentId" placeholder="Enter your student ID" required>
                        </div>

                        <div class="form-group">
                            <label for="signup_password">Password</label>
                            <input type="password" id="signup_password" name="signup_password" placeholder="At least 6 characters" required>
                        </div>

                        <div class="form-group">
                            <label for="confirmPassword">Confirm Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                        </div>

                        <button type="submit" class="login-btn">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Switching Script (Pure HTML/CSS Alternative) -->
    <script>
        const loginTab = document.getElementById('login-tab');
        const signupTab = document.getElementById('signup-tab');
        const loginForm = document.getElementById('login-form-section');
        const signupForm = document.getElementById('signup-form-section');

        loginTab.addEventListener('click', function() {
            loginTab.classList.add('active');
            signupTab.classList.remove('active');
            loginForm.classList.add('active');
            signupForm.classList.remove('active');
        });

        signupTab.addEventListener('click', function() {
            signupTab.classList.add('active');
            loginTab.classList.remove('active');
            signupForm.classList.add('active');
            loginForm.classList.remove('active');
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
