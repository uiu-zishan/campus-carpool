<?php
// api/get_ride_details.php
header('Content-Type: application/json');
require '../config/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$ride_id = $data['ride_id'] ?? null;

if (!$ride_id) {
    echo json_encode(['success' => false, 'message' => 'Ride ID required']);
    exit;
}

// Get ride + driver + vehicle
$stmt = $pdo->prepare("
    SELECT r.*, 
           u.id AS driver_id, u.full_name AS driver_name, 
           u.avg_rating AS driver_rating, u.gender AS driver_gender,
           v.make, v.model, v.color, v.license_plate
    FROM rides r
    JOIN users u ON r.driver_id = u.id
    LEFT JOIN vehicles v ON v.driver_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$ride_id]);
$ride = $stmt->fetch();

if (!$ride) {
    echo json_encode(['success' => false, 'message' => 'Ride not found']);
    exit;
}

// Get checkpoints
$stmt = $pdo->prepare("
    SELECT id, sequence_number, location_name, latitude, longitude
    FROM checkpoints
    WHERE ride_id = ?
    ORDER BY sequence_number ASC
");
$stmt->execute([$ride_id]);
$checkpoints = $stmt->fetchAll();

$ride['checkpoints'] = $checkpoints;

echo json_encode(['success' => true, 'ride' => $ride]);
?>