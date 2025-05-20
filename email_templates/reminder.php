<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 15px; text-align: center; }
        .content { padding: 20px; }
        .footer { margin-top: 20px; font-size: 12px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Medication Reminder</h2>
        </div>
        
        <div class="content">
            <p>Hello,</p>
            <p>This is a reminder to take your medication:</p>
            
            <table>
                <tr><td><strong>Medication:</strong></td><td>{{MEDICATION}}</td></tr>
                <tr><td><strong>Dosage:</strong></td><td>{{DOSAGE}}</td></tr>
                <tr><td><strong>Time:</strong></td><td>{{TIME}}</td></tr>
                <tr><td><strong>Date:</strong></td><td>{{DATE}}</td></tr>
            </table>
            
            <p>Thank you for using MedReminder!</p>
        </div>
        
        <div class="footer">
            <p>If you believe you received this email by mistake, please ignore it.</p>
        </div>
    </div>
</body>
</html>