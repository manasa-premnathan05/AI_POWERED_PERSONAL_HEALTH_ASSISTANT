<?php
require_once __DIR__ . '/includes/db_connection.php';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$appointment_id = $_GET['id'] ?? null;

if (!$appointment_id) {
    header('Location: dashboard.php#appointments');
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->execute([$appointment_id, $user_id]);
    
    $_SESSION['success'] = "Appointment deleted successfully!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting appointment: " . $e->getMessage();
}

header('Location: dashboard.php#appointments');
exit();
?>