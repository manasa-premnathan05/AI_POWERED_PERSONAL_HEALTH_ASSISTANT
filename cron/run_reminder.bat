@echo off
set PHP_BIN="C:\xampp\php\php.exe"
set PROJECT_DIR="C:\xampp\htdocs\AI_POWERED_PERSONAL_HEALTH_ASSISTANT"

%PHP_BIN% -f %PROJECT_DIR%\cron\send_scheduled_emails.php >> %PROJECT_DIR%\cron\cron.log 2>&1