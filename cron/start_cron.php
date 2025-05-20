<?php
// Absolute path to your project root
define('PROJECT_ROOT', __DIR__);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log startup
file_put_contents(PROJECT_ROOT.'/cron.log', 
    date('Y-m-d H:i:s')." - Cron service started\n", FILE_APPEND);

// Main loop
while(true) {
    try {
        require PROJECT_ROOT . '/cron_runner.php';
    } catch(Exception $e) {
        file_put_contents(PROJECT_ROOT.'/cron.log', 
            date('Y-m-d H:i:s')." - ERROR: ".$e->getMessage()."\n", FILE_APPEND);
    }
    
    // Wait 60 seconds before next run
    sleep(60);
}