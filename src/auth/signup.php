<?php
require_once '../../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize($_POST['firstName'] ?? '');
    $last_name = sanitize($_POST['lastName'] ?? '');
    $email = sanitize($_POST['signup_email'] ?? '');
    $student_id = sanitize($_POST['studentId'] ?? '');
    $password = $_POST['signup_password'] ?? '';
    $confirm_password = $_POST['confirmPassword'] ?? '';

    if (empty($first_name)) {
        $error = 'First name is required';
    } elseif (strlen($first_name) < 2) {
        $error = 'First name must be at least 2 characters';
    } elseif (empty($last_name)) {
        $error = 'Last name is required';
    } elseif (strlen($last_name) < 2) {
        $error = 'Last name must be at least 2 characters';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Valid email is required';
    } elseif (empty($student_id)) {
        $error = 'Student ID is required';
    } elseif (empty($password) || strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param('s', $email);
        $check_email->execute();
        $result = $check_email->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            $check_id = $conn->prepare("SELECT id FROM users WHERE student_id = ?");
            $check_id->bind_param('s', $student_id);
            $check_id->execute();
            $result = $check_id->get_result();

            if ($result->num_rows > 0) {
                $error = 'Student ID already registered';
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

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
        .password-strength {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .strength-weak   { color: #dc3545; }
        .strength-fair   { color: #fd7e14; }
        .strength-good   { color: #28a745; }
    </style>
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

                <form method="POST" action="signup.php" class="login-form" id="signupForm" novalidate>
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName"
                               placeholder="Enter your first name"
                               value="<?php echo htmlspecialchars($_POST['firstName'] ?? ''); ?>"
                               autocomplete="given-name">
                        <span class="field-error" id="firstName-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName"
                               placeholder="Enter your last name"
                               value="<?php echo htmlspecialchars($_POST['lastName'] ?? ''); ?>"
                               autocomplete="family-name">
                        <span class="field-error" id="lastName-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="signup_email">Email Address</label>
                        <input type="email" id="signup_email" name="signup_email"
                               placeholder="Enter your email"
                               value="<?php echo htmlspecialchars($_POST['signup_email'] ?? ''); ?>"
                               autocomplete="email">
                        <span class="field-error" id="email-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="studentId">Student ID</label>
                        <input type="text" id="studentId" name="studentId"
                               placeholder="Enter your student ID"
                               value="<?php echo htmlspecialchars($_POST['studentId'] ?? ''); ?>"
                               autocomplete="off">
                        <span class="field-error" id="studentId-error"></span>
                    </div>

                    <div class="form-group">
                        <label for="signup_password">Password</label>
                        <input type="password" id="signup_password" name="signup_password"
                               placeholder="At least 6 characters"
                               autocomplete="new-password">
                        <span class="field-error" id="password-error"></span>
                        <span class="password-strength" id="password-strength"></span>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword"
                               placeholder="Re-enter your password"
                               autocomplete="new-password">
                        <span class="field-error" id="confirm-error"></span>
                    </div>

                    <button type="submit" class="login-btn">Create Account</button>
                </form>

                <p class="login-footer">
                    Already have an account? <a href="login.php" style="color: #667eea; text-decoration: none;">Login here</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function setError(id, errorId, msg) {
            const el = document.getElementById(id);
            el.classList.add('input-invalid');
            el.classList.remove('input-valid');
            document.getElementById(errorId).textContent = msg;
        }

        function setValid(id, errorId) {
            const el = document.getElementById(id);
            el.classList.remove('input-invalid');
            el.classList.add('input-valid');
            document.getElementById(errorId).textContent = '';
        }

        function validateFirstName() {
            const val = document.getElementById('firstName').value.trim();
            if (!val) { setError('firstName', 'firstName-error', 'First name is required.'); return false; }
            if (val.length < 2) { setError('firstName', 'firstName-error', 'First name must be at least 2 characters.'); return false; }
            if (!/^[A-Za-z\s\-']+$/.test(val)) { setError('firstName', 'firstName-error', 'First name can only contain letters, spaces, hyphens, or apostrophes.'); return false; }
            setValid('firstName', 'firstName-error');
            return true;
        }

        function validateLastName() {
            const val = document.getElementById('lastName').value.trim();
            if (!val) { setError('lastName', 'lastName-error', 'Last name is required.'); return false; }
            if (val.length < 2) { setError('lastName', 'lastName-error', 'Last name must be at least 2 characters.'); return false; }
            if (!/^[A-Za-z\s\-']+$/.test(val)) { setError('lastName', 'lastName-error', 'Last name can only contain letters, spaces, hyphens, or apostrophes.'); return false; }
            setValid('lastName', 'lastName-error');
            return true;
        }

        function validateEmail() {
            const val = document.getElementById('signup_email').value.trim();
            if (!val) { setError('signup_email', 'email-error', 'Email address is required.'); return false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { setError('signup_email', 'email-error', 'Please enter a valid email address (e.g. user@example.com).'); return false; }
            setValid('signup_email', 'email-error');
            return true;
        }

        function validateStudentId() {
            const val = document.getElementById('studentId').value.trim();
            if (!val) { setError('studentId', 'studentId-error', 'Student ID is required.'); return false; }
            if (val.length < 2) { setError('studentId', 'studentId-error', 'Student ID is too short.'); return false; }
            setValid('studentId', 'studentId-error');
            return true;
        }

        function getPasswordStrength(pw) {
            if (pw.length < 6) return { level: 'weak', label: 'Weak — at least 6 characters required' };
            let score = 0;
            if (pw.length >= 8) score++;
            if (/[A-Z]/.test(pw)) score++;
            if (/[0-9]/.test(pw)) score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;
            if (score <= 1) return { level: 'fair', label: 'Fair — add uppercase, numbers, or symbols for a stronger password' };
            return { level: 'good', label: 'Strong password' };
        }

        function validatePassword() {
            const val = document.getElementById('signup_password').value;
            const strengthEl = document.getElementById('password-strength');
            if (!val) {
                setError('signup_password', 'password-error', 'Password is required.');
                strengthEl.textContent = '';
                return false;
            }
            if (val.length < 6) {
                setError('signup_password', 'password-error', 'Password must be at least 6 characters.');
                const s = getPasswordStrength(val);
                strengthEl.textContent = s.label;
                strengthEl.className = 'password-strength strength-' + s.level;
                return false;
            }
            const s = getPasswordStrength(val);
            strengthEl.textContent = s.label;
            strengthEl.className = 'password-strength strength-' + s.level;
            setValid('signup_password', 'password-error');
            // Re-validate confirm if it has a value
            if (document.getElementById('confirmPassword').value) validateConfirm();
            return true;
        }

        function validateConfirm() {
            const pw = document.getElementById('signup_password').value;
            const confirm = document.getElementById('confirmPassword').value;
            if (!confirm) { setError('confirmPassword', 'confirm-error', 'Please confirm your password.'); return false; }
            if (pw !== confirm) { setError('confirmPassword', 'confirm-error', 'Passwords do not match.'); return false; }
            setValid('confirmPassword', 'confirm-error');
            return true;
        }

        // Attach blur validators
        const fields = [
            ['firstName', validateFirstName],
            ['lastName', validateLastName],
            ['signup_email', validateEmail],
            ['studentId', validateStudentId],
            ['signup_password', validatePassword],
            ['confirmPassword', validateConfirm],
        ];

        fields.forEach(([id, fn]) => {
            const el = document.getElementById(id);
            el.addEventListener('blur', fn);
            el.addEventListener('input', function() {
                if (this.classList.contains('input-invalid')) fn();
            });
        });

        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const results = fields.map(([, fn]) => fn());
            if (results.some(r => !r)) e.preventDefault();
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>
