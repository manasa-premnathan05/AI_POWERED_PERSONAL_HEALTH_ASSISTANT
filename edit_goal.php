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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goal_id = $_POST['goal_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $target_date = $_POST['target_date'];
    $progress = $_POST['progress'];
    
    try {
        // Verify the goal belongs to the user before updating
        $stmt = $pdo->prepare("SELECT user_id FROM health_goals WHERE id = ?");
        $stmt->execute([$goal_id]);
        $goal = $stmt->fetch();
        
        if ($goal && $goal['user_id'] == $user_id) {
            $stmt = $pdo->prepare("UPDATE health_goals SET title = ?, description = ?, target_date = ?, progress = ? WHERE id = ?");
            $stmt->execute([$title, $description, $target_date, $progress, $goal_id]);
            
            $_SESSION['goal_success'] = "Goal updated successfully!";
        } else {
            $_SESSION['goal_error'] = "Goal not found or you don't have permission to edit it.";
        }
    } catch (PDOException $e) {
        $_SESSION['goal_error'] = "Error updating goal: " . $e->getMessage();
    }
}

header('Location: dashboard.php#health-goals');
exit();
?>