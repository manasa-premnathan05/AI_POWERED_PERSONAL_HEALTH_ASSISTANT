<!DOCTYPE html>
<html>
<head>
    <title>New Contact Form Submission</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4EA685; color: white; padding: 10px; text-align: center; }
        .content { padding: 20px; border: 1px solid #ddd; }
        .footer { margin-top: 20px; font-size: 0.8em; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Contact Form Submission</h2>
        </div>
        
        <div class="content">
            <p><strong>From:</strong> {{NAME}} ({{EMAIL}})</p>
            <p><strong>Subject:</strong> {{SUBJECT}}</p>
            <p><strong>Message:</strong></p>
            <p>{{MESSAGE}}</p>
        </div>
        
        <div class="footer">
            <p>This message was sent from the contact form on CareSphere.</p>
        </div>
    </div>
</body>
</html>