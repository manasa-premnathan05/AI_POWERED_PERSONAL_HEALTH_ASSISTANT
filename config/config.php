<?php
// Groq API Key (now using environment variables for security)
define('GROQ_API_KEY', getenv('GROQ_API_KEY'));

// PostgreSQL configuration for Render
define('DB_HOST', getenv('DB_HOST'));  // From Render dashboard
define('DB_USER', getenv('DB_USER'));  // From Render dashboard  
define('DB_PASS', getenv('DB_PASSWORD')); // From Render dashboard
define('DB_NAME', getenv('DB_NAME'));  // Usually same as service name

// Establish connection
try {
    $pdo = new PDO(
        "pgsql:host=".DB_HOST.";dbname=".DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}