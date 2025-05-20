<?php
require_once 'db_connection.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['userId'], $data['subscription'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid data']));
}

$userId = (int)$data['userId'];
$subscription = $data['subscription'];

// Delete old subscription if exists
$stmt = $conn->prepare("DELETE FROM push_subscriptions WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();

// Insert new subscription
$stmt = $conn->prepare("INSERT INTO push_subscriptions 
                      (user_id, endpoint, p256dh, auth) 
                      VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", 
    $userId,
    $subscription['endpoint'],
    $subscription['keys']['p256dh'],
    $subscription['keys']['auth']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>