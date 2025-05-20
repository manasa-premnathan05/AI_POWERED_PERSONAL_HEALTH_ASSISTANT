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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = $_POST['name'];
        $dosage = $_POST['dosage'];
        $frequency = $_POST['frequency'];
        $start_date = $_POST['start_date'];
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $purpose = $_POST['purpose'];
        
        $stmt = $pdo->prepare("INSERT INTO medications 
                              (user_id, name, dosage, frequency, start_date, end_date, purpose) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $dosage, $frequency, $start_date, $end_date, $purpose]);
        
        $_SESSION['success'] = "Medication added successfully!";
        header('Location: dashboard.php#medications');
        exit();
    } catch (PDOException $e) {
        $error = "Error adding medication: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medication</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Add your existing CSS styles here */
        .medication-form-container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Include your sidebar -->
        <div class="sidebar">
            <!-- Your existing sidebar content -->
        </div>
        
        <div class="main-content">
            <div class="header">
                <div class="header-left">
                    <h1>Add Medication</h1>
                </div>
            </div>
            
            <div class="medication-form-container">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form action="add_medication.php" method="post">
                    <div class="form-group">
                        <label for="name">Medication Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="dosage">Dosage *</label>
                        <input type="text" id="dosage" name="dosage" required placeholder="e.g., 500mg, 1 tablet">
                    </div>
                    
                    <div class="form-group">
                        <label for="frequency">Frequency *</label>
                        <input type="text" id="frequency" name="frequency" required placeholder="e.g., Twice daily, Every 6 hours">
                    </div>
                    
                    <div class="form-group">
                        <label for="start_date">Start Date *</label>
                        <input type="date" id="start_date" name="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date (optional)</label>
                        <input type="date" id="end_date" name="end_date">
                    </div>
                    
                    <div class="form-group">
                        <label for="purpose">Purpose *</label>
                        <textarea id="purpose" name="purpose" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <a href="dashboard.php#medications" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Medication</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>