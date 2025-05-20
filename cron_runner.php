<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../includes/db_connection.php';
require __DIR__ . '/../includes/email_functions.php';
use GO\Scheduler;

// Create a new scheduler
$scheduler = new Scheduler();

// Schedule your reminder script to run every minute
$scheduler->php(__DIR__ . '/send_scheduled_emails.php')
          ->everyMinute()
          ->output(__DIR__ . '/cron.log');

// Run the scheduler
$scheduler->run();
