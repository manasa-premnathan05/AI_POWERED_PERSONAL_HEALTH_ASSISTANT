<?php
require_once __DIR__ . '/includes/db_connection.php';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $weight = filter_input(INPUT_POST, 'weight', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $blood_pressure_systolic = filter_input(INPUT_POST, 'blood_pressure_systolic', FILTER_SANITIZE_NUMBER_INT);
        $blood_pressure_diastolic = filter_input(INPUT_POST, 'blood_pressure_diastolic', FILTER_SANITIZE_NUMBER_INT);
        $heart_rate = filter_input(INPUT_POST, 'heart_rate', FILTER_SANITIZE_NUMBER_INT);
        $blood_sugar = filter_input(INPUT_POST, 'blood_sugar', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $metric_date = filter_input(INPUT_POST, 'metric_date', FILTER_SANITIZE_STRING);
        
        // Insert into database
        $stmt = $pdo->prepare("INSERT INTO health_metrics 
                              (user_id, weight, blood_pressure_systolic, blood_pressure_diastolic, 
                               heart_rate, blood_sugar, metric_date) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $weight, $blood_pressure_systolic, $blood_pressure_diastolic, 
                        $heart_rate, $blood_sugar, $metric_date]);
        
        $_SESSION['metric_success'] = "Health metric added successfully!";
        header('Location: dashboard.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['metric_error'] = "Error adding health metric: " . $e->getMessage();
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
    <title>Add Health Metric</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Use the same styles as your dashboard */
        :root {
            --primary: #4EA685;
            --text-muted: #636e72;
            --radius-md: 12px;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9ff;
            color: #2d3436;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }
        h1 {
            color: var(--primary);
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        input, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #dfe6e9;
            border-radius: var(--radius-md);
            font-family: 'Poppins', sans-serif;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
        }
        .btn-secondary {
            background: #636e72;
        }
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-plus-circle"></i> Add Health Metric</h1>
        
        <?php if (isset($_SESSION['metric_error'])): ?>
            <div style="color: red; margin-bottom: 1rem;">
                <?php echo $_SESSION['metric_error']; unset($_SESSION['metric_error']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="weight">Weight (kg)</label>
                <input type="number" step="0.1" id="weight" name="weight" required>
            </div>
            
            <div class="form-group">
                <label for="blood_pressure_systolic">Blood Pressure (Systolic)</label>
                <input type="number" id="blood_pressure_systolic" name="blood_pressure_systolic" required>
            </div>
            
            <div class="form-group">
                <label for="blood_pressure_diastolic">Blood Pressure (Diastolic)</label>
                <input type="number" id="blood_pressure_diastolic" name="blood_pressure_diastolic" required>
            </div>
            
            <div class="form-group">
                <label for="heart_rate">Heart Rate (bpm)</label>
                <input type="number" id="heart_rate" name="heart_rate" required>
            </div>
            
            <div class="form-group">
                <label for="blood_sugar">Blood Sugar (mmol/L)</label>
                <input type="number" step="0.1" id="blood_sugar" name="blood_sugar">
            </div>
            
            <div class="form-group">
                <label for="metric_date">Date</label>
                <input type="date" id="metric_date" name="metric_date" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn">Save Metric</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>