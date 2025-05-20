<?php
// proxy.php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
require_once __DIR__ . '/config/config.php';

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    die(json_encode($data));
}

try {
    // Get raw input first
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        jsonResponse(['error' => 'No input received'], 400);
    }

    // Then decode JSON
    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse(['error' => 'Invalid JSON format'], 400);
    }

    // Validate required fields
    $required = ['age', 'gender', 'symptoms'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            jsonResponse(['error' => "Missing required field: $field"], 400);
        }
    }

    // Validate age
    $age = (int)$input['age'];
    if ($age < 1 || $age > 120) {
        jsonResponse(['error' => 'Age must be between 1 and 120'], 400);
    }

    // Validate gender
    $gender = strtolower($input['gender']);
    if (!in_array($gender, ['male', 'female', 'other'])) {
        jsonResponse(['error' => 'Invalid gender specified'], 400);
    }

    // Validate symptoms
    if (strlen(trim($input['symptoms'])) < 10) {
        jsonResponse(['error' => 'Please describe symptoms in more detail (minimum 10 characters)'], 400);
    }

    // Prepare Groq API request
    $apiData = [
        'model' => 'llama3-70b-8192', // Using one of Groq's available models
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a medical assistant. Provide clear, concise information in HTML format.'
            ],
            [
                'role' => 'user',
                'content' => "Analyze symptoms for a {$age}-year-old {$gender}:\n\nSymptoms: {$input['symptoms']}\n\n" .
                             "Provide:\n1. 3 most likely conditions (ranked by probability)\n" .
                             "2. Recommended self-care measures\n3. When to see a doctor immediately\n" .
                             "4. General advice for symptom management\n\n" .
                             "Format response as HTML with headings (h3) and bullet points (ul/li). " .
                             "Include a disclaimer that this is not medical diagnosis."
            ]
        ],
        'temperature' => 0.3,
        'max_tokens' => 2000,
        'top_p' => 0.9
    ];

    // Call Groq API
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.groq.com/openai/v1/chat/completions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY
        ],
        CURLOPT_POSTFIELDS => json_encode($apiData),
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("API connection failed: " . $curlError);
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid API response format");
    }

    if ($httpCode !== 200) {
        throw new Exception($data['error']['message'] ?? "API request failed with status $httpCode");
    }

    // Log to database (optional)
    if (defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME')) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (!$conn->connect_error) {
                $stmt = $conn->prepare("INSERT INTO symptom_checks (age, gender, symptoms, response) VALUES (?, ?, ?, ?)");
                $responseContent = $data['choices'][0]['message']['content'];
                $stmt->bind_param("isss", $age, $input['gender'], $input['symptoms'], $responseContent);
                $stmt->execute();
                $stmt->close();
                $conn->close();
            }
        } catch (Exception $dbError) {
            error_log('Database Error: ' . $dbError->getMessage());
        }
    }

    // Return successful response
    jsonResponse($data);

} catch (Exception $e) {
    error_log('Symptom Checker Error: ' . $e->getMessage());
    jsonResponse(['error' => $e->getMessage()], 500);
}