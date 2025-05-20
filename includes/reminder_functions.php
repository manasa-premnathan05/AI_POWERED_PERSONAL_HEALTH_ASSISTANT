<?php
// includes/reminder_functions.php

// Ensure we have access to the database connection
require_once __DIR__ . '/db_connection.php';
global $pdo;

function createReminder($userId, $medication, $dosage, $frequency, $times, $start_date) {
    global $pdo;
    
    try {
        $scheduleData = json_encode([
            'times' => $times,
            'frequency' => $frequency
        ]);
        
        $stmt = $pdo->prepare("INSERT INTO medication_reminders 
                              (user_id, medication, dosage, schedule_type, schedule_data, start_date) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $userId,
            $medication,
            $dosage,
            $frequency,
            $scheduleData,
            $start_date
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating reminder: " . $e->getMessage());
        return false;
    }
}

function getRemindersForUser($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM medication_reminders WHERE user_id = ? ORDER BY start_date");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching reminders: " . $e->getMessage());
        return [];
    }
}