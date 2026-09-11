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
// SECURITY: Restrict registration to university email domains only
$allowed_domains = ['bscse.uiu.ac.bd', 'uiu.ac.bd'];
$email_parts = explode('@', $data['email']);

// Check that email has exactly one @ symbol
if (count($email_parts) !== 2) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

$email_domain = strtolower($email_parts[1]); // Case-insensitive

if (!in_array($email_domain, $allowed_domains)) {
    echo json_encode(['success' => false, 'message' => 'Please use your university email (e.g., @bscse.uiu.ac.bd)']);
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