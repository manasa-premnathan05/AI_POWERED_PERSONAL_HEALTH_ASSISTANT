<?php
require_once __DIR__ . '/includes/db_connection.php';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $target_date = $_POST['target_date'];
    $progress = 0; // Start at 0% progress
    
    try {
        $stmt = $pdo->prepare("INSERT INTO health_goals (user_id, title, description, target_date, progress) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $description, $target_date, $progress]);
        
        $_SESSION['goal_success'] = "New health goal added successfully! You've taken the first step towards a healthier you!";
        header('Location: dashboard.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['goal_error'] = "Error adding goal: " . $e->getMessage();
        header('Location: dashboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Health Goal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #6c5ce7;
            --primary-light: #a29bfe;
            --secondary: #00cec9;
            --success: #00b894;
            --warning: #fdcb6e;
            --danger: #d63031;
            --dark: #2d3436;
            --light: #f5f6fa;
            --gray: #dfe6e9;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 2rem;
        }
        
        .goal-form-container {
            perspective: 1000px;
            width: 100%;
            max-width: 600px;
        }
        
        .goal-form-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
            transform-style: preserve-3d;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .goal-form-card:hover {
            transform: translateY(-10px) rotateX(5deg);
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.2);
        }
        
        .goal-form-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(108, 92, 231, 0.1) 0%, rgba(108, 92, 231, 0) 70%);
            transform: rotate(30deg);
            animation: shine 8s infinite linear;
            z-index: 0;
        }
        
        @keyframes shine {
            0% { transform: rotate(30deg) translate(-10%, -10%); }
            100% { transform: rotate(30deg) translate(10%, 10%); }
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
        }
        
        .form-header h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .form-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--gray);
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(108, 92, 231, 0.2);
            transform: translateY(-2px);
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: none;
            outline: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.4);
            flex: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.5);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
            flex: 1;
            margin-right: 1rem;
        }
        
        .btn-outline:hover {
            background: rgba(108, 92, 231, 0.1);
            transform: translateY(-3px);
        }
        
        .motivation-message {
            text-align: center;
            margin-top: 2rem;
            font-style: italic;
            color: var(--primary);
            font-weight: 500;
            opacity: 0.8;
            animation: fadeIn 2s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 0.8; transform: translateY(0); }
        }
        
        .floating-icons {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 0;
        }
        
        .floating-icon {
            position: absolute;
            opacity: 0.1;
            animation: float 6s infinite ease-in-out;
        }
        
        .floating-icon:nth-child(1) {
            top: 10%;
            left: 15%;
            font-size: 2rem;
            animation-delay: 0s;
        }
        
        .floating-icon:nth-child(2) {
            top: 70%;
            left: 80%;
            font-size: 1.5rem;
            animation-delay: 1s;
        }
        
        .floating-icon:nth-child(3) {
            top: 30%;
            left: 85%;
            font-size: 2.5rem;
            animation-delay: 2s;
        }
        
        .floating-icon:nth-child(4) {
            top: 85%;
            left: 10%;
            font-size: 1.8rem;
            animation-delay: 3s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
    </style>
</head>
<body>
    <div class="goal-form-container">
        <div class="goal-form-card">
            <div class="floating-icons">
                <i class="fas fa-heart floating-icon"></i>
                <i class="fas fa-dumbbell floating-icon"></i>
                <i class="fas fa-running floating-icon"></i>
                <i class="fas fa-apple-alt floating-icon"></i>
            </div>
            
            <div class="form-header">
                <h2>Set a New Health Goal</h2>
                <p>Every journey begins with a single step. What's your first step today?</p>
            </div>
            
            <form action="add_goal.php" method="POST">
                <div class="form-group">
                    <label for="title">Goal Title</label>
                    <input type="text" id="title" name="title" required placeholder="e.g., Run 5km, Lose 5kg">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Why is this goal important to you?"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="target_date">Target Date</label>
                    <input type="date" id="target_date" name="target_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Goal
                    </button>
                </div>
            </form>
            
            <div class="motivation-message">
                "You don't have to be great to start, but you have to start to be great." — Zig Ziglar
            </div>
        </div>
    </div>
</body>
</html>
