<?php
require_once __DIR__ . '/includes/db_connection.php';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$goal_id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("DELETE FROM health_goals WHERE id = ? AND user_id = ?");
    $stmt->execute([$goal_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['goal_success'] = "Goal deleted successfully!";
    } else {
        $_SESSION['goal_error'] = "Goal not found or you don't have permission to delete it";
    }
} catch (PDOException $e) {
    $_SESSION['goal_error'] = "Error deleting goal: " . $e->getMessage();
}

header('Location: dashboard.php');
exit();
?>