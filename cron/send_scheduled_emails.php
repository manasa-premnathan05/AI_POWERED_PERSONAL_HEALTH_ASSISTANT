<?php
// Set correct base path
define('BASE_DIR', dirname(__DIR__));
require_once BASE_DIR . '/includes/db_connection.php';
require_once BASE_DIR . '/includes/reminder_functions.php';
require_once BASE_DIR . '/includes/email_functions.php';

date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', BASE_DIR . '/cron/error.log');

// Verify database connection and data
try {
    $pdo->query("SELECT 1");
    error_log("[" . date('Y-m-d H:i:s') . "] Database connection successful");
    
    // Debug: Log all active reminders with their times
    $stmt = $pdo->query("
        SELECT mr.id, mr.medication, mr.schedule_type, mr.schedule_data, u.email
        FROM medication_reminders mr
        JOIN users u ON mr.user_id = u.id
        WHERE mr.start_date <= CURDATE() 
        AND (mr.end_date IS NULL OR mr.end_date >= CURDATE())
    ");
    
    $allReminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("[" . date('Y-m-d H:i:s') . "] Active reminders: " . print_r($allReminders, true));

} catch (PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Database error: " . $e->getMessage());
    exit;
}

function getDueReminders() {
    global $pdo;
    
    try {
        $currentTime = date('H:i');
        $currentDay = date('l');
        error_log("[" . date('Y-m-d H:i:s') . "] Checking reminders for time: $currentTime, day: $currentDay");
        
        $stmt = $pdo->prepare("
            SELECT mr.*, u.email, u.username 
            FROM medication_reminders mr
            JOIN users u ON mr.user_id = u.id
            WHERE mr.start_date <= CURDATE() 
            AND (mr.end_date IS NULL OR mr.end_date >= CURDATE())
            AND NOT EXISTS (
                SELECT 1 FROM notifications 
                WHERE reminder_id = mr.id 
                AND DATE(notification_time) = CURDATE()
                AND TIME_FORMAT(notification_time, '%H:%i') = :currentTime
            )
        ");
        $stmt->execute([':currentTime' => $currentTime]);
        
        $allReminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $dueReminders = [];
        
        foreach ($allReminders as $reminder) {
            $schedule = json_decode($reminder['schedule_data'], true);
            
            if (!is_array($schedule) || !isset($schedule['times'])) {
                error_log("[" . date('Y-m-d H:i:s') . "] Invalid schedule data for reminder ID: " . $reminder['id']);
                continue;
            }
            
            foreach ($schedule['times'] as $time) {
                $scheduledTime = date('H:i', strtotime($time));
                
                if ($currentTime === $scheduledTime) {
                    if ($reminder['schedule_type'] == 'weekly') {
                        $startDay = date('l', strtotime($reminder['start_date']));
                        if ($currentDay == $startDay) {
                            $dueReminders[] = $reminder;
                            error_log("[" . date('Y-m-d H:i:s') . "] Weekly reminder match: " . $reminder['medication']);
                        }
                    } else {
                        $dueReminders[] = $reminder;
                        error_log("[" . date('Y-m-d H:i:s') . "] Daily reminder match: " . $reminder['medication']);
                    }
                    break;
                }
            }
        }
        
        return $dueReminders;
    } catch (PDOException $e) {
        error_log("[" . date('Y-m-d H:i:s') . "] Database error: " . $e->getMessage());
        return [];
    }
}

// Main execution
error_log("[" . date('Y-m-d H:i:s') . "] CRON JOB STARTED");
$dueReminders = getDueReminders();
error_log("[" . date('Y-m-d H:i:s') . "] Found " . count($dueReminders) . " due reminders");

foreach ($dueReminders as $reminder) {
    $scheduleData = json_decode($reminder['schedule_data'], true);
    
    foreach ($scheduleData['times'] as $time) {
        if (date('H:i') !== date('H:i', strtotime($time))) {
            continue;
        }
        
        $reminderDetails = [
            'medication' => $reminder['medication'],
            'dosage' => $reminder['dosage'],
            'time' => $time,
            'date' => date('F j, Y')
        ];
        
        error_log("[" . date('Y-m-d H:i:s') . "] Processing reminder for: " . $reminder['email']);
        
        try {
            $mail = getMailer();
            $mail->addAddress($reminder['email']);
            $mail->Subject = 'Medication Reminder: ' . $reminder['medication'];
            $mail->Body = "Time to take your medication:<br><br>
                          <strong>Medication:</strong> {$reminder['medication']}<br>
                          <strong>Dosage:</strong> {$reminder['dosage']}<br>
                          <strong>Time:</strong> {$time}";
            
            if ($mail->send()) {
                $stmt = $pdo->prepare("
                    INSERT INTO notifications 
                    (reminder_id, user_id, notification_time, status) 
                    VALUES (?, ?, NOW(), 'sent')
                ");
                $stmt->execute([$reminder['id'], $reminder['user_id']]);
                error_log("[" . date('Y-m-d H:i:s') . "] Successfully sent reminder to " . $reminder['email']);
            } else {
                error_log("[" . date('Y-m-d H:i:s') . "] Failed to send email: " . $mail->ErrorInfo);
            }
        } catch (Exception $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Email error: " . $e->getMessage());
        }
    }
}

error_log("[" . date('Y-m-d H:i:s') . "] CRON JOB COMPLETED\n");
?>