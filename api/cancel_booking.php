<?php
// api/cancel_booking.php
header('Content-Type: application/json');
require '../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$booking_id = $data['booking_id'] ?? null;

if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'Booking ID required']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Lock and fetch the booking
    $stmt = $pdo->prepare("
        SELECT b.id, b.ride_id, b.passenger_id, b.status, r.departure_time
        FROM bookings b
        JOIN rides r ON b.ride_id = r.id
        WHERE b.id = ?
        FOR UPDATE
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    if (!$booking) throw new Exception('Booking not found');
    if ($booking['passenger_id'] != $user_id) throw new Exception('Not your booking');
    if ($booking['status'] === 'cancelled') throw new Exception('Already cancelled');
    if ($booking['status'] === 'completed') throw new Exception('Cannot cancel completed ride');

    // Determine penalty: if cancelled less than 2 hours before departure
    $departure = strtotime($booking['departure_time']);
    $hours_until = ($departure - time()) / 3600;
    $penalty = $hours_until < 2 && $hours_until > 0;

    // Update booking status
    $stmt = $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=?");
    $stmt->execute([$booking_id]);

    // Insert cancellation record
    $stmt = $pdo->prepare("
        INSERT INTO cancellations (booking_id, cancelled_by, penalty_applied)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$booking_id, $user_id, $penalty ? 1 : 0]);

    // Note: seats_available is auto-incremented by the trigger
    
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => $penalty ? 'Booking cancelled (penalty applied)' : 'Booking cancelled',
        'penalty_applied' => $penalty
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>