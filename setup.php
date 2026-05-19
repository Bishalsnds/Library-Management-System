<?php
/**
 * Library Management System — One-shot setup script.
 *
 * Run this once after cloning into XAMPP htdocs. It will:
 *   1. Create both databases (library_db and library_management)
 *   2. Import the SQL schemas from /database/
 *   3. Seed a working admin user (admin@gmail.com / password123)
 *
 * Safe to re-run; uses CREATE DATABASE IF NOT EXISTS and ON DUPLICATE KEY.
 * Delete this file after setup if you want to avoid exposing it.
 */

header('Content-Type: text/html; charset=utf-8');

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';

$steps  = [];
$errors = [];

function step($label, $ok, $detail = '') {
    global $steps;
    $steps[] = ['label' => $label, 'ok' => (bool)$ok, 'detail' => $detail];
}

// 1. Connect to MySQL server (no DB selected)
$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS);
if ($mysqli->connect_error) {
    $errors[] = "Cannot connect to MySQL at $DB_HOST as '$DB_USER'. Make sure XAMPP MySQL is running. Error: " . $mysqli->connect_error;
} else {
    step('Connected to MySQL', true, "$DB_HOST as $DB_USER");

    // 2. Create databases
    foreach (['library_db', 'library_management'] as $db) {
        $sql = "CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        step("Create database `$db`", $mysqli->query($sql), $mysqli->error);
    }

    // 3. Import schemas
    $imports = [
        'library_db'         => __DIR__ . '/database/complete_setup.sql',
        'library_management' => __DIR__ . '/database/fines_warnings.sql',
    ];
    foreach ($imports as $db => $file) {
        if (!file_exists($file)) {
            step("Import $file into `$db`", false, 'File not found');
            continue;
        }
        $sql = file_get_contents($file);
        // The SQL files contain their own CREATE/USE statements — multi_query handles that
        if ($mysqli->multi_query($sql)) {
            // Drain all result sets so the next query works
            do {
                if ($res = $mysqli->store_result()) {
                    $res->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
            step("Import schema into `$db`", !$mysqli->errno, $mysqli->error);
        } else {
            step("Import schema into `$db`", false, $mysqli->error);
        }
    }

    // 4. Seed a working admin user in library_db
    if ($mysqli->select_db('library_db')) {
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("
            INSERT INTO users (first_name, last_name, email, student_id, password, role, status)
            VALUES ('Admin', 'User', 'admin@gmail.com', 'ADM001', ?, 'admin', 'active')
            ON DUPLICATE KEY UPDATE password = VALUES(password), role = 'admin', status = 'active'
        ");
        if ($stmt) {
            $stmt->bind_param('s', $hash);
            step('Seed admin@gmail.com / password123', $stmt->execute(), $stmt->error);
            $stmt->close();

            // Also set the same password for other seeded accounts and align emails to @gmail.com
            $mysqli->query("UPDATE users SET email = REPLACE(email, '@library.com', '@gmail.com')");
            $update = $mysqli->prepare("
                UPDATE users SET password = ?
                WHERE email IN ('john@gmail.com','jane@gmail.com','librarian@gmail.com')
            ");
            if ($update) {
                $update->bind_param('s', $hash);
                $update->execute();
                $update->close();
            }
        } else {
            step('Seed admin user', false, $mysqli->error);
        }
    }

    $mysqli->close();
}

$any_failure = count($errors) > 0 || count(array_filter($steps, fn($s) => !$s['ok'])) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LMS Setup</title>
    <style>
        :root {
            --primary: #a749ff;
            --accent:  #ff8a3d;
            --ok:      #2ecc71;
            --bad:     #e74c3c;
            --text:    #1f1230;
            --muted:   #6b5c80;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(167, 73, 255, 0.30), transparent 30%),
                radial-gradient(circle at bottom right, rgba(255, 138, 61, 0.28), transparent 30%),
                linear-gradient(180deg, #1a1030 0%, #130e28 100%);
            padding: 32px;
        }
        .card {
            background: white;
            max-width: 720px;
            width: 100%;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 24px 60px rgba(10, 8, 24, 0.35);
        }
        h1 { margin: 0 0 4px; color: var(--primary); }
        p.sub { margin: 0 0 24px; color: var(--muted); }
        ul.steps { list-style: none; padding: 0; margin: 0 0 24px; }
        ul.steps li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 6px;
            background: #faf6ff;
        }
        ul.steps li .icon { font-weight: bold; }
        ul.steps li.ok .icon  { color: var(--ok);  }
        ul.steps li.bad .icon { color: var(--bad); }
        ul.steps li .detail { color: var(--muted); font-size: 0.9em; }
        .next {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-top: 16px;
        }
        .next a { color: white; font-weight: bold; }
        .err {
            background: #fff0f0;
            border-left: 4px solid var(--bad);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        code { background: #f0e7ff; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Library Management System — Setup</h1>
        <p class="sub">Bootstrap databases, schemas, and a test admin user.</p>

        <?php foreach ($errors as $e): ?>
            <div class="err"><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>

        <ul class="steps">
            <?php foreach ($steps as $s): ?>
                <li class="<?= $s['ok'] ? 'ok' : 'bad' ?>">
                    <span class="icon"><?= $s['ok'] ? '✓' : '✗' ?></span>
                    <div>
                        <div><?= htmlspecialchars($s['label']) ?></div>
                        <?php if (!$s['ok'] && $s['detail']): ?>
                            <div class="detail"><?= htmlspecialchars($s['detail']) ?></div>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (!$any_failure): ?>
            <div class="next">
                <strong>Setup complete!</strong> You can now log in.<br><br>
                <strong>Test credentials:</strong><br>
                Student: <code>john@gmail.com</code> / <code>password123</code><br>
                Admin:   <code>admin@gmail.com</code> / <code>password123</code><br><br>
                <a href="index.php">→ Open the app</a>
            </div>
        <?php else: ?>
            <div class="err">
                One or more setup steps failed. Make sure XAMPP MySQL is running and the
                <code>root</code> user has no password (default XAMPP). Then refresh this page.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
