<?php
// Start output buffering to prevent header errors
ob_start();
session_start();

require_once __DIR__ . '/includes/email_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Validate inputs
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (empty($errors)) {
        // Disable SMTP debug output before sending
        $mail = getMailer();
        $mail->SMTPDebug = 0; // Turn off debugging output
        
        // Send email
        $emailSent = sendContactFormEmail($name, $email, $subject, $message);
        
        if ($emailSent) {
            $_SESSION['message'] = 'Thank you! Your message has been sent successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'There was an error sending your message. Please try again later.';
            $_SESSION['message_type'] = 'error';
        }
    } else {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'error';
    }
    
    // Clear output buffer and redirect
    ob_end_clean();
    header('Location: about.php#contact');
    exit();
}