<?php
// api/submit_rating.php
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
$ratee_id = $data['ratee_id'] ?? null;
$score = (int)($data['score'] ?? 0);
$comment = $data['comment'] ?? '';

if (!$ride_id || !$ratee_id || $score < 1 || $score > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

try {
    // Verify the rater was part of this ride (either as driver or passenger)
    $stmt = $pdo->prepare("
        SELECT id FROM rides WHERE id = ? AND driver_id = ?
        UNION
        SELECT id FROM bookings WHERE ride_id = ? AND passenger_id = ? AND status IN ('confirmed', 'completed')
    ");
    $stmt->execute([$ride_id, $user_id, $ride_id, $user_id]);
    if (!$stmt->fetch()) {
        throw new Exception('You were not part of this ride');
    }

    // Insert rating (UNIQUE constraint prevents duplicates)
    $stmt = $pdo->prepare("
        INSERT INTO ratings (ride_id, rater_id, ratee_id, score, comment)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$ride_id, $user_id, $ratee_id, $score, $comment]);

    // Note: avg_rating trigger auto-updates users.avg_rating
    
    echo json_encode(['success' => true, 'message' => 'Rating submitted']);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'You already rated this person for this ride']);
    } else {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>