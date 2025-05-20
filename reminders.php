<?php
require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/reminder_functions.php';
require_once __DIR__ . '/includes/email_functions.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login-signup.php");
    exit();
}

/**
 * Get user email from database
 */
function getUserEmail($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user && !empty($user['email'])) {
            return $user['email'];
        }
        return null;
    } catch (PDOException $e) {
        error_log("Database error in getUserEmail(): " . $e->getMessage());
        return null;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $medication = filter_input(INPUT_POST, 'medication', FILTER_SANITIZE_STRING);
        $dosage = filter_input(INPUT_POST, 'dosage', FILTER_SANITIZE_STRING);
        $frequency = filter_input(INPUT_POST, 'frequency', FILTER_SANITIZE_STRING);
        $times = $_POST['times'] ?? [];
        $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
        
        if (empty($medication) || empty($dosage) || empty($times)) {
            throw new Exception("All fields are required");
        }
        
        $reminderId = createReminder(
            $_SESSION['user_id'],
            $medication,
            $dosage,
            $frequency,
            $times,
            $start_date
        );
        
        // Only proceed if reminder was created successfully
        if ($reminderId) {
             $_SESSION['success'] = "Reminder created successfully!";
        } else {
            throw new Exception("Failed to create reminder");
        }
        
        header("Location: reminders.php");
        exit();
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get user's reminders
try {
    $reminders = getRemindersForUser($_SESSION['user_id']);
} catch (Exception $e) {
    $error = "Failed to load reminders: " . $e->getMessage();
    $reminders = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareSphere - Medication Reminders</title>
    <!-- Add Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="style.css">
        <link rel="icon" href="assets/favicon.ico" type="image/x-icon">

    <style>
        /* General Styles */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }

        /* Header Styles */
        header {
            background: #4EA685;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
        }

        header nav ul li {
            margin: 0 15px;
            position: relative;
        }

        header nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        header nav ul li a:hover {
            text-decoration: underline;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        /* User dropdown styles */
        .user-menu {
            position: relative;
            display: inline-block;
        }
        
        .user-icon {
            cursor: pointer;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            color: white;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 5px;
            padding: 10px;
        }
        
        .dropdown-content p {
            color: #333;
            padding: 5px;
            margin: 0;
            font-weight: bold;
            border-bottom: 1px solid #eee;
        }
        
        .dropdown-content a {
            color: #333;
            padding: 8px 5px;
            display: block;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .dropdown-content a:hover {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .show {
            display: block;
        }

        /* Main Content Styles */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        /* Form Styles */
        .reminder-form {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #4EA685;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #4EA685;
            outline: none;
            box-shadow: 0 0 0 3px rgba(78, 166, 133, 0.2);
        }

        /* Time Inputs */
        #times-container {
            margin-bottom: 1rem;
        }

        .time-input {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .time-input .form-control {
            flex: 1;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background-color: #4EA685;
            color: white;
        }

        .btn-primary:hover {
            background-color: #3d8a6d;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        /* Reminders List */
        .reminders-list {
            margin-top: 2rem;
        }

        .reminder-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid #4EA685;
        }

        .reminder-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .reminder-card h5 {
            color: #4EA685;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: 500;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Footer Styles */
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 2rem;
        }

        .social-links a {
            color: white;
            margin: 0 10px;
            text-decoration: none;
            font-size: 1.2rem;
        }

        .social-links a:hover {
            color: #4EA685;
        }

        /* Floating Chatbot Icon */
        .chatbot-icon {
            position: fixed;
            bottom: 30%;
            right: 20px;
            background: #4EA685;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.3s;
        }

        .chatbot-icon:hover {
            background: #45a049;
            transform: scale(1.1);
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                padding: 10px;
            }
            
            header nav ul {
                flex-wrap: wrap;
                justify-content: center;
                margin-top: 10px;
            }
            
            header nav ul li {
                margin: 5px 10px;
            }
            
            .container {
                padding: 0 15px;
            }
            
            .reminder-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
        <header>
        <div class="logo">CareSphere</div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="symptom-checker.php">Symptom Checker</a></li>
                <li><a href="reminders.php">Reminders</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="about.php">About Us</a></li>
                <li class="user-menu">
                    <div class="user-icon" onclick="toggleDropdown()">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="dropdown-content" id="userDropdown">
                        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <a href="dashboard.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </li>
            </ul>
            <audio id="notification-sound" src="assets/sounds/notification.mp3" preload="auto"></audio>
        </nav>
    </header>

<div class="container">
    <h1>Medication Reminders</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <form method="POST" id="reminder-form">
        <div class="form-group">
            <label>Medication Name</label>
            <input type="text" name="medication" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Dosage</label>
            <input type="text" name="dosage" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Frequency</label>
            <select name="frequency" class="form-control" required>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Times</label>
            <div id="times-container">
                <div class="time-input mb-2">
                    <input type="time" name="times[]" class="form-control" required>
                    <button type="button" class="btn btn-danger remove-time">-</button>
                </div>
            </div>
            <button type="button" id="add-time" class="btn btn-secondary">Add Time</button>
        </div>
        
        <div class="form-group">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Save Reminder</button>
    </form>
    
    <h2 class="mt-4">Your Reminders</h2>
    <div id="reminders-list">
        <?php foreach ($reminders as $reminder): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($reminder['medication']) ?></h5>
                    <p class="card-text">
                        <strong>Dosage:</strong> <?= htmlspecialchars($reminder['dosage']) ?><br>
                        <strong>Schedule:</strong> <?= ucfirst($reminder['schedule_type']) ?>
                    </p>
                    <button class="btn btn-danger delete-reminder" 
                            data-id="<?= $reminder['id'] ?>">Delete</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
    </main>


    <footer>
        <p>&copy; 2023 CareSphere. All rights reserved.</p>
        <div class="social-links">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
    </footer>

<script>
// JavaScript for dynamic time inputs
document.getElementById('add-time').addEventListener('click', function() {
    const container = document.getElementById('times-container');
    const div = document.createElement('div');
    div.className = 'time-input mb-2';
    div.innerHTML = `
        <input type="time" name="times[]" class="form-control" required>
        <button type="button" class="btn btn-danger remove-time">-</button>
    `;
    container.appendChild(div);
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-time')) {
        e.target.parentNode.remove();
    }
});
</script>

    </script>
        <script src="https://cdn.botpress.cloud/webchat/v2.2/inject.js"></script>
<script src="https://files.bpcontent.cloud/2025/03/31/19/20250331190550-DL98FARD.js"></script>
    
    <script>
        window.botpressWebChat.init({
            botId: "YOUR_BOT_ID", // Replace with your actual bot ID
            hostUrl: "https://cdn.botpress.cloud/webchat/v2.2",
            clientId: "YOUR_CLIENT_ID", // Replace with your client ID
            container: "#carebot-webchat",
            botName: "CareBot",
            avatarUrl: "images/carebot-avatar.png", // Create this image in your images folder
            composerPlaceholder: "Type your health question here...",
            
            // User session data (if available)
            userId: "<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>",
            extraProperties: {
                username: "<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; ?>"
            },
            
            // Healthcare quick actions
            persistentMenu: [
                { label: "Medication Help", payload: "MEDICATION_HELP" },
                { label: "Symptom Checker", payload: "SYMPTOM_CHECKER_INFO" },
                { label: "Emergency Resources", payload: "EMERGENCY_INFO" }
            ]
        });
    </script>

</body>
</html>