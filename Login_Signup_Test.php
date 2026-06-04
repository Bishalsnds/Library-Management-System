<?php
session_start();
require_once 'config.php';

// ─── Helper ──────────────────────────────────────────────────────────────────
$results = [];

function run_test($label, $passed, $note = '') {
    global $results;
    $results[] = [
        'label'  => $label,
        'passed' => $passed,
        'note'   => $note
    ];
}

// ─── Validation functions (mirrors login.php / signup.php logic) ─────────────
function validate_signup($first_name, $last_name, $email, $student_id, $password, $confirm_password) {
    if (empty($first_name))                  return 'First name is required';
    if (strlen($first_name) < 2)             return 'First name must be at least 2 characters';
    if (strlen($first_name) > 50)            return 'First name must be less than 50 characters';
    if (empty($last_name))                   return 'Last name is required';
    if (strlen($last_name) < 2)              return 'Last name must be at least 2 characters';
    if (strlen($last_name) > 50)             return 'Last name must be less than 50 characters';
    if (empty($email))                       return 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Invalid email format';
    if (!preg_match('/@gmail\.com$/', $email)) return 'Only @gmail.com addresses are allowed';
    if (strlen($email) > 100)                return 'Email must be less than 100 characters';
    if (empty($student_id))                  return 'Student ID is required';
    if (strlen($student_id) < 2)             return 'Student ID is too short';
    if (empty($password) || strlen($password) < 6) return 'Password must be at least 6 characters';
    if (strlen($password) > 100)             return 'Password must be less than 100 characters';
    if ($password !== $confirm_password)     return 'Passwords do not match';
    return '';
}

function validate_login($email, $password) {
    if (empty($email))                          return 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Invalid email format';
    if (!preg_match('/@gmail\.com$/', $email))  return 'Only @gmail.com addresses are allowed';
    if (empty($password))                       return 'Password is required';
    return '';
}

// ═══════════════════════════════════════════════════════════════════════════
// 1. FIRST NAME BOUNDARY TESTS
// ═══════════════════════════════════════════════════════════════════════════
$err = validate_signup('', 'Smith', 'test@gmail.com', 'S001', 'pass123', 'pass123');
run_test('FirstName – Extreme Min (empty)',        $err !== '', "Error: $err");

$err = validate_signup('A', 'Smith', 'test@gmail.com', 'S001', 'pass123', 'pass123');
run_test('FirstName – Min-1 (1 char)',             $err !== '', "Error: $err");

$err = validate_signup('AB', 'Smith', 'test@gmail.com', 'S001', 'pass123', 'pass123');
run_test('FirstName – Min Boundary (2 chars)',     $err === '', "Result: accepted");

$err = validate_signup('ABC', 'Smith', 'test@gmail.com', 'S001', 'pass123', 'pass123');
run_test('FirstName – Min+1 (3 chars)',            $err === '', "Result: accepted");

$err = validate_signup(str_repeat('A', 50), 'Smith', 'test@gmail.com', 'S001', 'pass123', 'pass123');
run_test('FirstName – Max Boundary (50 chars)',    $err === '', "Result: accepted");

$err = validate_signup(str_repeat('A', 51), 'Smith', 'test@gmail.com', 'S001', 'pass123', 'pass123');
run_test('FirstName – Max+1 (51 chars)',           $err !== '', "Error: $err");

// ═══════════════════════════════════════════════════════════════════════════
// 2. LAST NAME BOUNDARY TESTS
// ═══════════════════════════════════════════════════════════════════════════
$err = validate_signup('John', '', 'test@gmail.com', 'S001', 'pass123', 'pass123');
run_test('LastName – Extreme Min (empty)',         $err !== '', "Error: $err");

$err = validate_signup('John', 'A', 'test@gmail.com', 'S001', 'pass123', 'pass123');
run_test('LastName – Min-1 (1 char)',              $err !== '', "Error: $err");

$err = validate_signup('John', 'AB', 'test@gmail.com', 'S001', 'pass123', 'pass123');
run_test('LastName – Min Boundary (2 chars)',      $err === '', "Result: accepted");

$err = validate_signup('John', str_repeat('B', 50), 'test@gmail.com', 'S001', 'pass123', 'pass123');
run_test('LastName – Max Boundary (50 chars)',     $err === '', "Result: accepted");

$err = validate_signup('John', str_repeat('B', 51), 'test@gmail.com', 'S001', 'pass123', 'pass123');
run_test('LastName – Max+1 (51 chars)',            $err !== '', "Error: $err");

// ═══════════════════════════════════════════════════════════════════════════
// 3. EMAIL BOUNDARY TESTS
// ═══════════════════════════════════════════════════════════════════════════
$err = validate_signup('John', 'Smith', '', 'S001', 'pass123', 'pass123');
run_test('Email – Extreme Min (empty)',            $err !== '', "Error: $err");

$err = validate_signup('John', 'Smith', 'notanemail', 'S001', 'pass123', 'pass123');
run_test('Email – Invalid format (no @ or .)',     $err !== '', "Error: $err");

$err = validate_signup('John', 'Smith', 'test@yahoo.com', 'S001', 'pass123', 'pass123');
run_test('Email – Non-gmail domain',              $err !== '', "Error: $err");

$err = validate_signup('John', 'Smith', 'a@gmail.com', 'S001', 'pass123', 'pass123');
run_test('Email – Min Boundary (a@gmail.com)',    $err === '', "Result: accepted");

// RFC 5321 limits local part (before @) to max 64 chars
// So max valid gmail = 64 chars + '@gmail.com' (10) = 74 chars total
$valid_email = str_repeat('a', 64) . '@gmail.com'; // 74 chars — max RFC-valid gmail
$err = validate_signup('John', 'Smith', $valid_email, 'S001', 'pass123', 'pass123');
run_test('Email – Max Boundary (74 chars, RFC limit)', $err === '', "Result: accepted");

// To hit the 100-char DB limit we'd need a local part > 64, which filter_var rejects first
// So we test that oversized emails are correctly blocked (by format check)
$too_long_email = str_repeat('a', 91) . '@gmail.com'; // 101 chars, local part > 64
$err = validate_signup('John', 'Smith', $too_long_email, 'S001', 'pass123', 'pass123');
run_test('Email – Max+1 (101 chars, rejected by RFC format)', $err !== '', "Error: $err");

// ═══════════════════════════════════════════════════════════════════════════
// 4. STUDENT ID BOUNDARY TESTS
// ═══════════════════════════════════════════════════════════════════════════
$err = validate_signup('John', 'Smith', 'test@gmail.com', '', 'pass123', 'pass123');
run_test('StudentId – Extreme Min (empty)',        $err !== '', "Error: $err");

$err = validate_signup('John', 'Smith', 'test@gmail.com', 'A', 'pass123', 'pass123');
run_test('StudentId – Min-1 (1 char)',             $err !== '', "Error: $err");

$err = validate_signup('John', 'Smith', 'test@gmail.com', 'AB', 'pass123', 'pass123');
run_test('StudentId – Min Boundary (2 chars)',     $err === '', "Result: accepted");

$err = validate_signup('John', 'Smith', 'test@gmail.com', 'P12345678', 'pass123', 'pass123');
run_test('StudentId – Valid format (P12345678)',   $err === '', "Result: accepted");

// ═══════════════════════════════════════════════════════════════════════════
// 5. PASSWORD BOUNDARY TESTS
// ═══════════════════════════════════════════════════════════════════════════
$err = validate_signup('John', 'Smith', 'test@gmail.com', 'S001', '', '');
run_test('Password – Extreme Min (empty)',         $err !== '', "Error: $err");

$err = validate_signup('John', 'Smith', 'test@gmail.com', 'S001', '12345', '12345');
run_test('Password – Min-1 (5 chars)',             $err !== '', "Error: $err");

$err = validate_signup('John', 'Smith', 'test@gmail.com', 'S001', '123456', '123456');
run_test('Password – Min Boundary (6 chars)',      $err === '', "Result: accepted");

$err = validate_signup('John', 'Smith', 'test@gmail.com', 'S001', '1234567', '1234567');
run_test('Password – Min+1 (7 chars)',             $err === '', "Result: accepted");

$err = validate_signup('John', 'Smith', 'test@gmail.com', 'S001', str_repeat('p', 100), str_repeat('p', 100));
run_test('Password – Max Boundary (100 chars)',    $err === '', "Result: accepted");

$err = validate_signup('John', 'Smith', 'test@gmail.com', 'S001', str_repeat('p', 101), str_repeat('p', 101));
run_test('Password – Max+1 (101 chars)',           $err !== '', "Error: $err");

$err = validate_signup('John', 'Smith', 'test@gmail.com', 'S001', 'pass123', 'different');
run_test('Password – Confirm mismatch',            $err !== '', "Error: $err");

// ═══════════════════════════════════════════════════════════════════════════
// 6. SECURITY TESTS
// ═══════════════════════════════════════════════════════════════════════════
$sql_input    = "' OR '1'='1";
$sanitized    = sanitize($sql_input);
$neutralized  = ($sanitized !== $sql_input);
run_test('Security – SQL Injection attempt', $neutralized,
    "Original: $sql_input | Sanitized: $sanitized");

$xss_input   = "<script>alert('XSS')</script>";
$sanitized   = sanitize($xss_input);
$neutralized = (strpos($sanitized, '<script>') === false);
run_test('Security – XSS Script injection', $neutralized,
    "Original: $xss_input | Sanitized: $sanitized");

$drop_input  = "'; DROP TABLE users; --";
$sanitized   = sanitize($drop_input);
$neutralized = ($sanitized !== $drop_input);
run_test('Security – SQL DROP TABLE injection', $neutralized,
    "Original: $drop_input | Sanitized: $sanitized");

// ═══════════════════════════════════════════════════════════════════════════
// 7. DATABASE CRUD TESTS (register + login)
// ═══════════════════════════════════════════════════════════════════════════
$test_email    = 'testuser_autotest@gmail.com';
$test_password = password_hash('TestPass123', PASSWORD_BCRYPT);
$test_sid      = 'TEST001';

// Clean up any previous test run
$conn->query("DELETE FROM users WHERE email = '$test_email'");

// CREATE
$insert = $conn->prepare("INSERT INTO users (first_name, last_name, email, student_id, password, role, status) VALUES (?, ?, ?, ?, ?, 'student', 'active')");
$insert->bind_param('sssss', ...['TestFirst', 'TestLast', $test_email, $test_sid, $test_password]);
run_test('DB – Create (Register new user)', $insert->execute(), "Inserted test user: $test_email");
$insert->close();

// READ
$read = $conn->prepare("SELECT id, first_name, email FROM users WHERE email = ?");
$read->bind_param('s', $test_email);
$read->execute();
$res = $read->get_result();
$found = $res->num_rows === 1;
$row   = $found ? $res->fetch_assoc() : null;
run_test('DB – Read (Find user by email)', $found,
    $found ? "Found: ID={$row['id']}, Name={$row['first_name']}" : "Not found");
$read->close();

// AUTHENTICATE (Login)
$auth = $conn->prepare("SELECT password, status FROM users WHERE email = ?");
$auth->bind_param('s', $test_email);
$auth->execute();
$auth_res  = $auth->get_result();
$auth_row  = $auth_res->fetch_assoc();
$login_ok  = $auth_row && $auth_row['status'] === 'active' && password_verify('TestPass123', $auth_row['password']);
run_test('DB – Authenticate (Login with correct password)', $login_ok, "Status: active, Password: verified");
$auth->close();

$auth2 = $conn->prepare("SELECT password FROM users WHERE email = ?");
$auth2->bind_param('s', $test_email);
$auth2->execute();
$auth2_res = $auth2->get_result();
$auth2_row = $auth2_res->fetch_assoc();
$bad_login = $auth2_row && !password_verify('WrongPassword', $auth2_row['password']);
run_test('DB – Authenticate (Login with wrong password)', $bad_login, "Correctly rejected wrong password");
$auth2->close();

// UPDATE (change password)
$new_hash = password_hash('NewPass456', PASSWORD_BCRYPT);
$upd = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$upd->bind_param('ss', $new_hash, $test_email);
run_test('DB – Update (Reset password)', $upd->execute() && $upd->affected_rows === 1, "Password updated");
$upd->close();

// DELETE
$del = $conn->prepare("DELETE FROM users WHERE email = ?");
$del->bind_param('s', $test_email);
run_test('DB – Delete (Remove test user)', $del->execute() && $del->affected_rows === 1, "Test user removed");
$del->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login & Sign Up – Test Results</title>
<style>
    body { font-family: Arial, sans-serif; margin: 30px; background: #f5f5f5; }
    h1   { font-size: 22px; margin-bottom: 6px; }
    h2   { font-size: 16px; color: #333; margin: 28px 0 8px; border-bottom: 2px solid #ccc; padding-bottom: 4px; }
    .summary { font-size: 14px; color: #555; margin-bottom: 20px; }
    .test { margin: 6px 0; font-size: 14px; }
    .label { font-weight: bold; }
    .pass { color: #1a7a1a; }
    .fail { color: #cc0000; }
    .note { color: #555; font-size: 13px; margin-left: 8px; }
    .badge { display: inline-block; padding: 1px 8px; border-radius: 3px; font-size: 12px;
             font-weight: bold; margin-left: 6px; }
    .badge-pass { background: #d4edda; color: #155724; }
    .badge-fail { background: #f8d7da; color: #721c24; }
    .section { background: #fff; border-radius: 6px; padding: 16px 20px;
               margin-bottom: 16px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
</style>
</head>
<body>

<h1>Login &amp; Sign Up – Test Results</h1>
<?php
$total  = count($results);
$passed = count(array_filter($results, fn($r) => $r['passed']));
$failed = $total - $passed;
?>
<p class="summary">
    Total: <strong><?= $total ?></strong> &nbsp;|&nbsp;
    <span class="pass">Passed: <strong><?= $passed ?></strong></span> &nbsp;|&nbsp;
    <span class="fail">Failed: <strong><?= $failed ?></strong></span>
</p>

<?php
$sections = [
    'FirstName Boundary Tests' => 'FirstName',
    'LastName Boundary Tests'  => 'LastName',
    'Email Boundary Tests'     => 'Email',
    'StudentId Boundary Tests' => 'StudentId',
    'Password Boundary Tests'  => 'Password',
    'Security Tests'           => 'Security',
    'Database CRUD Tests'      => 'DB',
];

foreach ($sections as $title => $prefix):
    $section_tests = array_filter($results, fn($r) => str_starts_with($r['label'], $prefix));
    if (empty($section_tests)) continue;
?>
<div class="section">
    <h2><?= $title ?></h2>
    <?php foreach ($section_tests as $t): ?>
    <div class="test">
        <span class="label"><?= htmlspecialchars($t['label']) ?></span>
        <span class="badge <?= $t['passed'] ? 'badge-pass' : 'badge-fail' ?>">
            <?= $t['passed'] ? 'PASS' : 'FAIL' ?>
        </span>
        <?php if ($t['note']): ?>
        <span class="note"><?= htmlspecialchars($t['note']) ?></span>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

</body>
</html>
