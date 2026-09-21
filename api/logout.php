<?php
// api/logout.php
// Properly destroys the PHP session on the server side

header('Content-Type: application/json');
session_start();

// Log the logout action (optional audit trail)
if (isset($_SESSION['user_id'])) {
    require '../config/db_connect.php';
    try {
        $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, 'logout', 'User logged out', ?)");
        $stmt->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Exception $e) {
        // Fail silently — logging shouldn't block logout
    }
}

// Clear all session data
$_SESSION = [];

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>