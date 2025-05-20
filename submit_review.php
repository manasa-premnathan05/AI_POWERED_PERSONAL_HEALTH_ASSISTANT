<?php
session_start();
require_once __DIR__ . '/includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login-signup.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $review_text = trim($_POST['review_text']);
    $rating = (int)$_POST['rating'];
    
    // Validate inputs
    $errors = [];
    if (empty($review_text)) $errors[] = 'Review text is required';
    if ($rating < 1 || $rating > 5) $errors[] = 'Invalid rating';
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_reviews (user_id, username, review_text, rating) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $username, $review_text, $rating]);
            
            $_SESSION['review_success'] = 'Thank you for your review!';
        } catch (PDOException $e) {
            $_SESSION['review_error'] = 'Error submitting review. Please try again.';
        }
    } else {
        $_SESSION['review_error'] = implode('<br>', $errors);
    }
}

header("Location: index.php#reviews");
exit();
?>