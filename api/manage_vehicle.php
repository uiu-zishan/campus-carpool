<?php
// api/manage_vehicle.php
header('Content-Type: application/json');
require '../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

// GET: fetch existing vehicle
if (isset($data['action']) && $data['action'] === 'get') {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE driver_id = ?");
    $stmt->execute([$user_id]);
    $vehicle = $stmt->fetch();
    echo json_encode(['success' => true, 'vehicle' => $vehicle ?: null]);
    exit;
}

// CREATE or UPDATE
$required = ['make', 'model', 'color', 'license_plate', 'capacity'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing: $field"]);
        exit;
    }
}

if ($data['capacity'] < 1 || $data['capacity'] > 10) {
    echo json_encode(['success' => false, 'message' => 'Capacity must be 1-10']);
    exit;
}

try {
    // Check if vehicle exists
    $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE driver_id = ?");
    $stmt->execute([$user_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // UPDATE
        $stmt = $pdo->prepare("
            UPDATE vehicles 
            SET make=?, model=?, color=?, license_plate=?, capacity=?, year=?
            WHERE driver_id=?
        ");
        $stmt->execute([
            $data['make'], $data['model'], $data['color'],
            $data['license_plate'], $data['capacity'],
            $data['year'] ?? null, $user_id
        ]);
        $message = 'Vehicle updated';
    } else {
        // INSERT
        $stmt = $pdo->prepare("
            INSERT INTO vehicles (driver_id, make, model, color, license_plate, capacity, year)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id, $data['make'], $data['model'], $data['color'],
            $data['license_plate'], $data['capacity'], $data['year'] ?? null
        ]);
        $message = 'Vehicle added';
    }

    echo json_encode(['success' => true, 'message' => $message]);

} catch (PDOException $e) {
    // Handle duplicate license plate
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'License plate already registered']);
    } else {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}
?>