<?php
require_once 'db_connection.php';
require_once 'reminder_functions.php';
require_once 'email_functions.php';

// Get pending notifications
$now = date('Y-m-d H:i:00');
$stmt = $conn->prepare("SELECT n.*, r.medication, r.dosage, u.email, u.username 
                       FROM notifications n
                       JOIN medication_reminders r ON n.reminder_id = r.id
                       JOIN users u ON n.user_id = u.id
                       WHERE n.notification_time <= ? AND n.status = 'pending'");
$stmt->bind_param("s", $now);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($notifications as $notification) {
    // Send email notification
    sendReminderEmail(
        $notification['email'],
        $notification['username'],
        $notification['medication'],
        $notification['dosage']
    );
    
    // Send browser notification (implement this)
    sendBrowserNotification(
        $notification['user_id'],
        "Medication Reminder",
        "Time to take {$notification['medication']} - {$notification['dosage']}"
    );
    
    // Update notification status
    $updateStmt = $conn->prepare("UPDATE notifications SET status = 'sent' WHERE id = ?");
    $updateStmt->bind_param("i", $notification['id']);
    $updateStmt->execute();
}
?>