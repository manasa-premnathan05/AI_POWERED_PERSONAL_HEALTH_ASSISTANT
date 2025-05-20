<?php
// includes/db_connection.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get credentials from environment variables (set in Render)
define('DB_HOST', getenv('DB_HOST') ?: 'dpg-cvs0bivgi27c73aef8s0-a');
define('DB_USER', getenv('DB_USER') ?: 'health_assistant_user');
define('DB_PASS', getenv('DB_PASSWORD') ?: '5ucYlALe4AGYB7NwoFKjoGTXrWtcfec9');
define('DB_NAME', getenv('DB_NAME') ?: 'health_assistant');

try {
    $pdo = new PDO(
        "pgsql:host=".DB_HOST.";dbname=".DB_NAME, 
        DB_USER, 
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Test connection
    $pdo->query("SELECT 1 FROM pg_tables LIMIT 1");
    
    // Create tables if they don't exist (PostgreSQL syntax)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS medication_reminders (
        id SERIAL PRIMARY KEY,
        user_id INT NOT NULL,
        medication VARCHAR(100) NOT NULL,
        dosage VARCHAR(50) NOT NULL,
        schedule_type VARCHAR(10) CHECK (schedule_type IN ('daily', 'weekly', 'custom')) NOT NULL,
        schedule_data TEXT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE,
        notes TEXT,
        last_sent TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id SERIAL PRIMARY KEY,
        reminder_id INT NOT NULL,
        user_id INT NOT NULL,
        notification_time TIMESTAMP NOT NULL,
        status VARCHAR(10) CHECK (status IN ('pending', 'sent', 'dismissed', 'completed')) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (reminder_id) REFERENCES medication_reminders(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // Create index separately (PostgreSQL syntax)
    $pdo->exec("CREATE INDEX IF NOT EXISTS notifications_time_status_idx ON notifications (notification_time, status)");
    
    // Add column if not exists (PostgreSQL syntax)
    $columnExists = $pdo->query("SELECT 1 FROM information_schema.columns 
                               WHERE table_name = 'medication_reminders' 
                               AND column_name = 'last_sent'")->fetch();
    if (!$columnExists) {
        $pdo->exec("ALTER TABLE medication_reminders ADD COLUMN last_sent TIMESTAMP NULL");
    }
    
    return $pdo;
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}