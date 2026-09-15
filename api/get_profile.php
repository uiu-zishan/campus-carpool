<?php
// api/get_profile.php
header('Content-Type: application/json');
require '../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, email, full_name, gender, role, avg_rating, penalty_points FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

echo json_encode(['success' => true, 'user' => $user]);
?>