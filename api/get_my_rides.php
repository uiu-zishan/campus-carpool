<?php
// api/get_my_rides.php
header('Content-Type: application/json');
require '../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$type = $data['type'] ?? 'booked'; // 'booked' or 'posted'

if ($type === 'booked') {
    // Passenger's booked rides
    $stmt = $pdo->prepare("
        SELECT b.id AS booking_id, b.status AS booking_status, b.booked_at,
               r.id AS ride_id, r.origin, r.destination, r.departure_time, r.status AS ride_status,
               u.id AS driver_id, u.full_name AS driver_name, u.avg_rating AS driver_rating
        FROM bookings b
        JOIN rides r ON b.ride_id = r.id
        JOIN users u ON r.driver_id = u.id
        WHERE b.passenger_id = ?
        ORDER BY r.departure_time DESC
    ");
    $stmt->execute([$user_id]);
} else {
    // Driver's posted rides
    $stmt = $pdo->prepare("
        SELECT r.id AS ride_id, r.origin, r.destination, r.departure_time, 
               r.status AS ride_status, r.seats_available,
               (SELECT COUNT(*) FROM bookings WHERE ride_id = r.id AND status = 'confirmed') AS confirmed_bookings
        FROM rides r
        WHERE r.driver_id = ?
        ORDER BY r.departure_time DESC
    ");
    $stmt->execute([$user_id]);
}

$rides = $stmt->fetchAll();
echo json_encode(['success' => true, 'rides' => $rides]);
?>