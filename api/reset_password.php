<?php
// api/reset_password.php
header('Content-Type: application/json');
require '../config/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? null;
$new_password = $data['password'] ?? null;

if (!$token || !$new_password) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

if (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
    echo json_encode(['success' => false, 'message' => 'Password must be 8+ chars with an uppercase letter and a number']);
    exit;
}

// Look up the token
$stmt = $pdo->prepare("
    SELECT id, user_id, expires_at, used 
    FROM password_resets 
    WHERE token = ?
");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset || $reset['used'] || strtotime($reset['expires_at']) < time()) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired reset link']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Update password
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$hash, $reset['user_id']]);

    // Mark token as used
    $stmt = $pdo->prepare("UPDATE password_resets SET used = TRUE WHERE id = ?");
    $stmt->execute([$reset['id']]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Password reset successfully']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Reset failed']);
}
?>