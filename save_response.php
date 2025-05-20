<?php
// save_response.php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "health_assistant");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!$data || !isset($data['symptoms'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

// Prepare and execute the SQL statement
$stmt = $conn->prepare("INSERT INTO symptom_responses 
                       (age, gender, symptoms, result, created_at) 
                       VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("isss", $data['age'], $data['gender'], $data['symptoms'], $data['result']);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save response']);
}

$stmt->close();
$conn->close();
?>