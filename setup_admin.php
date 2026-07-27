<?php
/**
 * setup_admin.php
 * ------------------------------------------------------------
 * Run this ONCE in your browser (e.g. http://localhost/student-registration/setup_admin.php)
 * to create your admin login. It generates a proper bcrypt hash
 * via PHP's password_hash() — never store plain-text passwords.
 *
 * IMPORTANT: Delete this file after creating your admin account.
 */

require_once 'config.php';

$message = '';
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || strlen($password) < 8) {
        $message = 'Please provide a username and a password of at least 8 characters.';
    } else {
        $check = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $check->bind_param('s', $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = 'That admin username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
            $stmt->bind_param('ss', $username, $hash);
            $stmt->execute();
            $stmt->close();
            $done = true;
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Admin Account</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-login-shell">
    <div class="admin-login-card">
        <div class="seal">SR</div>

        <?php if ($done): ?>
            <h1>Admin account created</h1>
            <p class="sub">You can now log in to the admin panel. For security, delete <code>setup_admin.php</code> from your project folder now.</p>
            <a href="admin/login.php" class="btn-secondary" style="display:block;text-align:center;">Go to admin login</a>
        <?php else: ?>
            <h1>Create Admin Account</h1>
            <p class="sub">One-time setup. Run this once, then delete this file.</p>

            <?php if ($message): ?>
                <div class="alert-banner error"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label for="username">Admin username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="field">
                    <label for="password">Admin password</label>
                    <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
                </div>
                <button type="submit" class="btn-submit"><span class="btn-text">Create Account</span></button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
