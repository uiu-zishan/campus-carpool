<?php
// api/favorite_driver.php
header('Content-Type: application/json');
require '../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$driver_id = $data['driver_id'] ?? null;

if (!$driver_id) {
    echo json_encode(['success' => false, 'message' => 'Driver ID required']);
    exit;
}
if ($driver_id == $user_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot favorite yourself']);
    exit;
}

// Toggle: if exists → delete, else → insert
$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND driver_id = ?");
$stmt->execute([$user_id, $driver_id]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
    $stmt->execute([$existing['id']]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, driver_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $driver_id]);
    echo json_encode(['success' => true, 'action' => 'added']);
}
?>