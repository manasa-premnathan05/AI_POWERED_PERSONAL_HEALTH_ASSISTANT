<?php
require_once __DIR__ . '/../vendor/autoload.php';



function getMailer() {
    $config = require __DIR__ . '/../config/mail_config.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['encryption'];
        $mail->Port = $config['smtp']['port'];
        $mail->SMTPDebug = $config['smtp']['debug'];
        
        // Sender info
        $mail->setFrom(
            $config['smtp']['from_email'],
            $config['smtp']['from_name']
        );
        
        return $mail;
    } catch (Exception $e) {
        error_log("Mailer configuration error: " . $e->getMessage());
        throw $e; // Re-throw to handle in calling function
    }
}

/**
 * Send reminder notification email
 * 
 * @param string $email User's email
 * @param array $reminder Reminder data
 * @return bool True if sent successfully
 */
function sendReminderEmail($to, $reminderDetails) {
    try {
        $mail = getMailer();
        $mail->addAddress($to);
        
        $mail->isHTML(true);
        $mail->Subject = "Reminder: " . $reminderDetails['medication'];
        
        // Load template - ensure this path is correct
        $templatePath = __DIR__ . '/../email_templates/reminder.php';
        if (!file_exists($templatePath)) {
            throw new Exception("Email template not found at: $templatePath");
        }
        
        $template = file_get_contents($templatePath);
        $body = str_replace(
            ['{{MEDICATION}}', '{{DOSAGE}}', '{{TIME}}', '{{DATE}}'],
            [
                htmlspecialchars($reminderDetails['medication']),
                htmlspecialchars($reminderDetails['dosage']),
                $reminderDetails['time'],
                date('F j, Y')
            ],
            $template
        );
        
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        if (!$mail->send()) {
            error_log("Email send failed. Error: " . $mail->ErrorInfo);
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send contact form message to admin
 * 
 * @param string $name Sender's name
 * @param string $email Sender's email
 * @param string $subject Email subject
 * @param string $message The message content
 * @return bool True if sent successfully
 */
function sendContactFormEmail($name, $email, $subject, $message) {
    try {
        $mail = getMailer();
        
        // Set sender and recipient
        $mail->addAddress('tm5548793@gmail.com'); // Your admin email
        $mail->addReplyTo($email, $name);
        
        $mail->isHTML(true);
        $mail->Subject = $subject ?: "New contact form submission from $name";
        
        // Load template
        $templatePath = __DIR__ . '/../email_templates/contact_form.php';
        if (!file_exists($templatePath)) {
            throw new Exception("Contact form template not found at: $templatePath");
        }
        
        $template = file_get_contents($templatePath);
        $body = str_replace(
            ['{{NAME}}', '{{EMAIL}}', '{{SUBJECT}}', '{{MESSAGE}}'],
            [
                htmlspecialchars($name),
                htmlspecialchars($email),
                htmlspecialchars($subject),
                nl2br(htmlspecialchars($message))
            ],
            $template
        );
        
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        if (!$mail->send()) {
            error_log("Contact form email failed. Error: " . $mail->ErrorInfo);
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Contact form mail error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email using SMTP
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body Email content (HTML)
 * @param array $attachments Optional file attachments
 * @return bool True if sent successfully
 */
function sendEmail($to, $subject, $body, $attachments = []) {
    try {
        $mail = getMailer();
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        // Add attachments if any
        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment['path'], $attachment['name']);
        }
        
        if (!$mail->send()) {
            error_log("Email send failed. Error: " . $mail->ErrorInfo);
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $e->getMessage());
        return false;
    }
}