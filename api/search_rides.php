<?php
// api/search_rides.php
header('Content-Type: application/json');
require '../config/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$origin = $data['origin'] ?? null;
$destination = $data['destination'] ?? null;
$date = $data['date'] ?? null;
$female_only = !empty($data['female_only']);

// Build dynamic query
$sql = "
    SELECT r.id, r.origin, r.destination, r.departure_time, 
           r.seats_available, r.is_female_only,
           u.full_name AS driver_name, u.avg_rating AS driver_rating,
           v.make, v.model, v.color
    FROM rides r
    JOIN users u ON r.driver_id = u.id
    LEFT JOIN vehicles v ON v.driver_id = u.id
    WHERE r.status = 'scheduled'
      AND r.seats_available > 0
";

$params = [];

if ($origin) {
    $sql .= " AND r.origin LIKE ?";
    $params[] = "%$origin%";
}
if ($destination) {
    $sql .= " AND r.destination LIKE ?";
    $params[] = "%$destination%";
}
if ($date) {
    $sql .= " AND DATE(r.departure_time) = ?";
    $params[] = $date;
}
if ($female_only) {
    $sql .= " AND r.is_female_only = 1";
}

$sql .= " ORDER BY r.departure_time ASC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rides = $stmt->fetchAll();

echo json_encode(['success' => true, 'rides' => $rides]);
?>