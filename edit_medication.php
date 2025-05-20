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
$medication_id = $_GET['id'] ?? null;

if (!$medication_id) {
    header('Location: dashboard.php#medications');
    exit();
}

// Fetch medication details
try {
    $stmt = $pdo->prepare("SELECT * FROM medications WHERE id = ? AND user_id = ?");
    $stmt->execute([$medication_id, $user_id]);
    $medication = $stmt->fetch();
    
    if (!$medication) {
        header('Location: dashboard.php#medications');
        exit();
    }
} catch (PDOException $e) {
    die("Error fetching medication: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = $_POST['name'];
        $dosage = $_POST['dosage'];
        $frequency = $_POST['frequency'];
        $start_date = $_POST['start_date'];
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $purpose = $_POST['purpose'];
        
        $stmt = $pdo->prepare("UPDATE medications SET 
                              name = ?, dosage = ?, frequency = ?, 
                              start_date = ?, end_date = ?, purpose = ?
                              WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $dosage, $frequency, $start_date, $end_date, $purpose, $medication_id, $user_id]);
        
        $_SESSION['success'] = "Medication updated successfully!";
        header('Location: dashboard.php#medications');
        exit();
    } catch (PDOException $e) {
        $error = "Error updating medication: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Medication</title>
    <!-- Include your CSS and other head elements -->
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
                    <h1>Edit Medication</h1>
                </div>
            </div>
            
            <div class="medication-form-container">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form action="edit_medication.php?id=<?php echo $medication_id; ?>" method="post">
                    <div class="form-group">
                        <label for="name">Medication Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($medication['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="dosage">Dosage *</label>
                        <input type="text" id="dosage" name="dosage" value="<?php echo htmlspecialchars($medication['dosage']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="frequency">Frequency *</label>
                        <input type="text" id="frequency" name="frequency" value="<?php echo htmlspecialchars($medication['frequency']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_date">Start Date *</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($medication['start_date']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date (optional)</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($medication['end_date']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="purpose">Purpose *</label>
                        <textarea id="purpose" name="purpose" rows="3" required><?php echo htmlspecialchars($medication['purpose']); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <a href="dashboard.php#medications" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Medication</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>