<?php
// api/login.php
header('Content-Type: application/json');
require '../config/db_connect.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Missing email or password']);
    exit;
}

// Fetch the user by email
$stmt = $pdo->prepare("SELECT id, email, password_hash, full_name, role FROM users WHERE email = ?");
$stmt->execute([$data['email']]);
$user = $stmt->fetch();

// Verify the password
if ($user && password_verify($data['password'], $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
}
?>