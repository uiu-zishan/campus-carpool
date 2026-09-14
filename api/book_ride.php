<?php
// api/book_ride.php
// THE MOST CRITICAL ENDPOINT - Uses row-level locking to prevent overbooking
header('Content-Type: application/json');
require '../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

$ride_id = $data['ride_id'] ?? null;
$pickup_checkpoint_id = $data['pickup_checkpoint_id'] ?? null;
$dropoff_checkpoint_id = $data['dropoff_checkpoint_id'] ?? null;

if (!$ride_id || !$pickup_checkpoint_id || !$dropoff_checkpoint_id) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. LOCK the ride row to prevent overbooking
    $stmt = $pdo->prepare("
        SELECT id, driver_id, seats_available, status 
        FROM rides 
        WHERE id = ? 
        FOR UPDATE
    ");
    $stmt->execute([$ride_id]);
    $ride = $stmt->fetch();

    if (!$ride) {
        throw new Exception('Ride not found');
    }

    // 2. Validate business rules
    if ($ride['driver_id'] == $user_id) {
        throw new Exception('You cannot book your own ride');
    }
    if ($ride['status'] !== 'scheduled') {
        throw new Exception('This ride is no longer available');
    }
    if ($ride['seats_available'] < 1) {
        throw new Exception('No seats available on this ride');
    }

    // 3. Verify pickup comes before dropoff
    $stmt = $pdo->prepare("
        SELECT id, sequence_number FROM checkpoints 
        WHERE id IN (?, ?) AND ride_id = ?
    ");
    $stmt->execute([$pickup_checkpoint_id, $dropoff_checkpoint_id, $ride_id]);
    $checkpoints = $stmt->fetchAll();

    if (count($checkpoints) !== 2) {
        throw new Exception('Invalid checkpoint selection');
    }

    $pickup_seq = null;
    $dropoff_seq = null;
    foreach ($checkpoints as $cp) {
        if ($cp['id'] == $pickup_checkpoint_id) $pickup_seq = $cp['sequence_number'];
        if ($cp['id'] == $dropoff_checkpoint_id) $dropoff_seq = $cp['sequence_number'];
    }

    if ($pickup_seq >= $dropoff_seq) {
        throw new Exception('Pickup must come before dropoff');
    }

    // 4. Check if user already booked this ride
    $stmt = $pdo->prepare("
        SELECT id FROM bookings 
        WHERE ride_id = ? AND passenger_id = ? AND status IN ('pending', 'confirmed')
    ");
    $stmt->execute([$ride_id, $user_id]);
    if ($stmt->fetch()) {
        throw new Exception('You already booked this ride');
    }

    // 5. Insert the booking
    $stmt = $pdo->prepare("
        INSERT INTO bookings 
        (ride_id, passenger_id, pickup_checkpoint_id, dropoff_checkpoint_id, 
         pickup_sequence, dropoff_sequence, status)
        VALUES (?, ?, ?, ?, ?, ?, 'confirmed')
    ");
    $stmt->execute([
        $ride_id, $user_id, 
        $pickup_checkpoint_id, $dropoff_checkpoint_id,
        $pickup_seq, $dropoff_seq
    ]);

    // Note: seats_available is auto-decremented by the trigger we created in schema_v2.sql

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking confirmed',
        'booking_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>