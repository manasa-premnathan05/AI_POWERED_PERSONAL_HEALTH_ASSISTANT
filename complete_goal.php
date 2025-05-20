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

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$goal_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Verify the goal belongs to the user
    $stmt = $pdo->prepare("SELECT * FROM health_goals WHERE id = ? AND user_id = ?");
    $stmt->execute([$goal_id, $user_id]);
    $goal = $stmt->fetch();
    
    if (!$goal) {
        $_SESSION['goal_error'] = "Goal not found or doesn't belong to you";
        header('Location: dashboard.php');
        exit();
    }
    
    // Mark as completed (100% progress)
    $stmt = $pdo->prepare("UPDATE health_goals SET progress = 100 WHERE id = ?");
    $stmt->execute([$goal_id]);
    
    // Random celebration message
    $messages = [
        "Well done champion! You've crushed this goal!",
        "Amazing work! Your dedication is inspiring!",
        "Goal accomplished! Time to celebrate this win!",
        "You did it! This is just the beginning of your success!",
        "Fantastic achievement! Your hard work paid off!"
    ];
    $randomMessage = $messages[array_rand($messages)];
    
    $_SESSION['goal_success'] = $randomMessage;
    header('Location: dashboard.php');
    exit();
} catch (PDOException $e) {
    $_SESSION['goal_error'] = "Error completing goal: " . $e->getMessage();
    header('Location: dashboard.php');
    exit();
}
?>