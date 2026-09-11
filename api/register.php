<?php
// api/register.php
header('Content-Type: application/json');
require '../config/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Check if email already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$data['email']]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

// Hash the password and insert the new user
$hash = password_hash($data['password'], PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (email, password_hash, full_name, gender, role) VALUES (?, ?, ?, ?, ?)");
$result = $stmt->execute([
    $data['email'],
    $hash,
    $data['full_name'],
    $data['gender'] ?? 'M',
    $data['role'] ?? 'student'
]);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'User registered successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed']);
}
?>