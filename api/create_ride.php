<?php
// api/create_ride.php
header('Content-Type: application/json');
require '../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$driver_id = $_SESSION['user_id'];
// BUSINESS RULE: Driver must have a registered vehicle
$stmt = $pdo->prepare("SELECT id, capacity FROM vehicles WHERE driver_id = ?");
$stmt->execute([$driver_id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    echo json_encode(['success' => false, 'message' => 'Please register your vehicle before posting a ride']);
    exit;
}
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
$required = ['origin', 'destination', 'departure_time', 'seats_available', 'checkpoints'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
        exit;
    }
}

// Validate seats against vehicle capacity
if ($data['seats_available'] > $vehicle['capacity']) {
    echo json_encode(['success' => false, 'message' => 'Seats exceed your vehicle capacity of ' . $vehicle['capacity']]);
    exit;
}

// Validate checkpoints array
if (!is_array($data['checkpoints']) || count($data['checkpoints']) < 2) {
    echo json_encode(['success' => false, 'message' => 'At least 2 checkpoints required']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insert the ride
    $stmt = $pdo->prepare("
        INSERT INTO rides (driver_id, origin, destination, departure_time, seats_available, is_female_only, status)
        VALUES (?, ?, ?, ?, ?, ?, 'scheduled')
    ");
    $stmt->execute([
        $driver_id,
        $data['origin'],
        $data['destination'],
        $data['departure_time'],
        $data['seats_available'],
        !empty($data['is_female_only']) ? 1 : 0
    ]);
    $ride_id = $pdo->lastInsertId();

    // 2. Insert all checkpoints
    $stmt = $pdo->prepare("
        INSERT INTO checkpoints (ride_id, sequence_number, location_name, latitude, longitude)
        VALUES (?, ?, ?, ?, ?)
    ");
    $seq = 1;
    foreach ($data['checkpoints'] as $cp) {
        $stmt->execute([
            $ride_id,
            $seq++,
            $cp['location_name'] ?? 'Unknown',
            $cp['latitude'] ?? 0,
            $cp['longitude'] ?? 0
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Ride posted successfully',
        'ride_id' => $ride_id
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Failed to create ride: ' . $e->getMessage()]);
}
?>