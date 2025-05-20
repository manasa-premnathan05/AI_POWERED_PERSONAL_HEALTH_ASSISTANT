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
        $doctor_name = $_POST['doctor_name'];
        $specialty = $_POST['specialty'];
        $appointment_date = $_POST['appointment_date'];
        $location = $_POST['location'];
        $notes = $_POST['notes'] ?? null;
        
        $stmt = $pdo->prepare("INSERT INTO appointments 
                              (user_id, doctor_name, specialty, appointment_date, location, notes) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $doctor_name, $specialty, $appointment_date, $location, $notes]);
        
        $_SESSION['success'] = "Appointment scheduled successfully!";
        header('Location: dashboard.php#appointments');
        exit();
    } catch (PDOException $e) {
        $error = "Error scheduling appointment: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Appointment</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
/* ===== Base Styles ===== */
:root {
    --primary: #6c5ce7;
    --primary-light: #a29bfe;
    --secondary: #00cec9;
    --success: #00b894;
    --danger: #ff7675;
    --light: #f8f9fa;
    --dark: #2d3436;
    --gray: #dfe6e9;
    --card-bg: rgba(255, 255, 255, 0.96);
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f5f7ff;
}

/* ===== Form Container ===== */
.appointment-form-container {
    max-width: 700px;
    margin: 2rem auto;
    background: var(--card-bg);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 5px 20px rgba(108, 92, 231, 0.1);
    border: 1px solid rgba(108, 92, 231, 0.1);
}

.form-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.form-header h1 {
    color: var(--primary);
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.form-header p {
    color: var(--dark);
    opacity: 0.7;
}

/* ===== Form Elements ===== */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark);
    font-size: 0.95rem;
}

.form-control {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 1px solid var(--gray);
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

/* ===== Button Styles ===== */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
}

.btn {
    padding: 0.8rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
}

.btn-primary {
    background: var(--primary);
    color: white;
    border: none;
}

.btn-primary:hover {
    background: #5d4ae3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 92, 231, 0.2);
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--gray);
    color: var(--dark);
}

.btn-outline:hover {
    background: rgba(0, 0, 0, 0.02);
    border-color: var(--primary-light);
}

/* ===== Responsive Design ===== */
@media (max-width: 768px) {
    .appointment-form-container {
        padding: 1.5rem;
        margin: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
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
            <div class="appointment-form-container">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="form-header">
                    <h1><i class="fas fa-calendar-plus"></i> New Appointment</h1>
                    <p>Schedule your medical visit</p>
                </div>
                
                <form action="add_appointment.php" method="post">
                    <div class="form-group float-label">
                        <input type="text" id="doctor_name" name="doctor_name" class="form-control" placeholder=" " required>
                        <label for="doctor_name">Doctor's Name</label>
                    </div>
                    
                    <div class="form-group float-label">
                        <input type="text" id="specialty" name="specialty" class="form-control" placeholder=" " required>
                        <label for="specialty">Specialty</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="appointment_date">Date & Time</label>
                        <input type="datetime-local" id="appointment_date" name="appointment_date" class="form-control" required>
                    </div>
                    
                    <div class="form-group float-label">
                        <input type="text" id="location" name="location" class="form-control" placeholder=" " required>
                        <label for="location">Location</label>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes (Optional)</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <a href="dashboard.php#appointments" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i> Schedule Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Floating label enhancement
        document.querySelectorAll('.float-label input').forEach(input => {
            input.addEventListener('focus', function() {
                this.nextElementSibling.style.color = '#6c5ce7';
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.nextElementSibling.style.color = '';
                }
            });
        });
    </script>
</body>
</html>