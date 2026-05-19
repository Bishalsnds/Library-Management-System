<?php
require_once '../../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $first_name = sanitize($_POST['firstName'] ?? '');
    $last_name = sanitize($_POST['lastName'] ?? '');
    $email = sanitize($_POST['signup_email'] ?? '');
    $student_id = sanitize($_POST['studentId'] ?? '');
    $password = $_POST['signup_password'] ?? '';
    $confirm_password = $_POST['confirmPassword'] ?? '';

    // Validation
    if (empty($first_name)) {
        $error = 'First name is required';
    } elseif (empty($last_name)) {
        $error = 'Last name is required';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Valid email is required';
    } elseif (empty($student_id)) {
        $error = 'Student ID is required';
    } elseif (empty($password) || strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param('s', $email);
        $check_email->execute();
        $result = $check_email->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Check if student ID already exists
            $check_id = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
            $check_id->bind_param('s', $student_id);
            $check_id->execute();
            $result = $check_id->get_result();

            if ($result->num_rows > 0) {
                $error = 'Student ID already registered';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert into database
                $insert = $conn->prepare("INSERT INTO users (first_name, last_name, email, student_id, password, role, status) VALUES (?, ?, ?, ?, ?, 'student', 'active')");
                $insert->bind_param('sssss', $first_name, $last_name, $email, $student_id, $hashed_password);

                if ($insert->execute()) {
                    $success = 'Account created successfully! Redirecting to login...';
                    header('refresh:2;url=login.php');
                } else {
                    $error = 'Error creating account: ' . $conn->error;
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
    <title>Sign Up - Library Management System</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-side">
            <div class="side-content">
                <div class="logo-container">
                    <img src="../../public/assets/images/logo.png.jpg" alt="Y.R.A.S.B. Logo" class="logo">
                </div>
                <h1>Library System</h1>
                <p>Create your account to manage books efficiently</p>
                <ul class="features-list">
                    <li>Easy Book Access</li>
                    <li>Track Borrowings</li>
                    <li>User Friendly</li>
                </ul>
            </div>
        </div>

        <div class="login-wrapper">
            <div class="login-form-box">
                <h1>Create Account</h1>

                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="signup.php" class="login-form">
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

                <p class="login-footer">
                    Already have an account? <a href="login.php" style="color: #667eea; text-decoration: none;">Login here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
