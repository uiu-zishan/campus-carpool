<?php
// api/forgot_password.php
header('Content-Type: application/json');
require '../config/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? null;

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email required']);
    exit;
}

// Find user
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// SECURITY: Always return success (prevents user enumeration)
if (!$user) {
    echo json_encode(['success' => true, 'message' => 'If that email exists, a reset link has been sent.']);
    exit;
}

// Generate secure token
$token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour

// Insert reset record
$stmt = $pdo->prepare("
    INSERT INTO password_resets (user_id, token, expires_at)
    VALUES (?, ?, ?)
");
$stmt->execute([$user['id'], $token, $expires_at]);

// Build reset link
$reset_link = "http://localhost/campus-carpool/reset-password.html?token=" . $token;

// In production: send via email. For this project: return in response
echo json_encode([
    'success' => true,
    'message' => 'Reset link generated',
    'reset_link' => $reset_link // FOR DEMO ONLY - remove in production
]);
?>