<?php
// config/mail_config.php

return [
    'smtp' => [
        'host' => 'smtp.gmail.com',      // e.g., smtp.gmail.com
        'username' => 'tm5548793@gmail.com',  // SMTP username
        'password' => 'tezb uwkt kotq ycby',     // App password for security
        'port' => 587,                          // 587 for TLS, 465 for SSL
        'encryption' => 'tls',                  // 'tls' or 'ssl'
        'from_email' => 'tm5548793@gmail.com',   // Sender email
        'from_name' => 'MedReminder System',    // Sender name
        'debug' => 2                         // 0=off, 1=client, 2=client+server
    ]
];