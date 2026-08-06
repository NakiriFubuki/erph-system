<?php
/**
 * One-click reset admin password to admin123 (For forgotten password or first-time setup only)
 * Open in browser: http://localhost/ERPH1/public/reset_admin_password.php
 * After running once, delete or rename this file.
 */
require_once __DIR__ . '/../db.php';

header('Content-Type: text/html; charset=utf-8');

$email = 'admin@erph.com';
$newPassword = 'admin123';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'admin'");
    $stmt->execute([$hash, $email]);
    if ($stmt->rowCount() > 0) {
        echo '<p style="color:green;">Success: Administrator (admin@erph.com) password has been reset to admin123. Please log in with this account.</p>';
    } else {
        echo '<p style="color:orange;">Admin account not found. Please run erph1_fresh.sql to create the default admin.</p>';
    }
} catch (Exception $e) {
    echo '<p style="color:red;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
echo '<p><a href="login_roles.php">Back to login</a></p>';
