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
$medication_id = $_GET['id'] ?? null;

if (!$medication_id) {
    header('Location: dashboard.php#medications');
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM medications WHERE id = ? AND user_id = ?");
    $stmt->execute([$medication_id, $user_id]);
    
    $_SESSION['success'] = "Medication deleted successfully!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting medication: " . $e->getMessage();
}

header('Location: dashboard.php#medications');
exit();
?>