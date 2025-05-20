<?php
require_once __DIR__ . '/includes/db_connection.php';
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    header('Location: login.php');
    exit();
}

$user_id = (int)$user_id;

// Verify user exists
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    if (!$stmt->fetch()) {
        // User doesn't exist - destroy session and redirect
        session_destroy();
        header('Location: login-signup.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error checking user: " . $e->getMessage());
    $error = "System error. Please try again later.";
}

// Check if user is logged in (you'll need to implement your auth system)
if (!isset($_SESSION['user_id'])) {
    header('Location: login-signup.php'); // Redirect to login if not authenticated
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic profile info
    $full_name = $_POST['full_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $blood_type = $_POST['blood_type'];
    
    // Health metrics
    $blood_pressure_systolic = $_POST['blood_pressure_systolic'];
    $blood_pressure_diastolic = $_POST['blood_pressure_diastolic'];
    $heart_rate = $_POST['heart_rate'];
    $blood_sugar = $_POST['blood_sugar'];
    $allergies = $_POST['allergies'];
    $chronic_diseases = $_POST['chronic_diseases'];
    
    // Calculate BMI
    $bmi = $weight / (($height/100) * ($height/100));
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Insert into user_profiles
        $stmt = $pdo->prepare("INSERT INTO user_profiles 
                              (user_id, full_name, date_of_birth, gender, height, weight, blood_type, allergies, chronic_diseases) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                              ON DUPLICATE KEY UPDATE 
                              full_name=VALUES(full_name), date_of_birth=VALUES(date_of_birth), 
                              gender=VALUES(gender), height=VALUES(height), weight=VALUES(weight), 
                              blood_type=VALUES(blood_type), allergies=VALUES(allergies), 
                              chronic_diseases=VALUES(chronic_diseases)");
        $stmt->execute([$user_id, $full_name, $date_of_birth, $gender, $height, $weight, 
                        $blood_type, $allergies, $chronic_diseases]);
        
        // Insert into health_metrics
        $stmt = $pdo->prepare("INSERT INTO health_metrics 
                              (user_id, metric_date, weight, blood_pressure_systolic, 
                               blood_pressure_diastolic, heart_rate, blood_sugar, notes) 
                              VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?)");
        $notes = "Initial profile setup. BMI: " . round($bmi, 2);
        $stmt->execute([$user_id, $weight, $blood_pressure_systolic, 
                        $blood_pressure_diastolic, $heart_rate, $blood_sugar, $notes]);
        
        $pdo->commit();
        
        // Redirect to dashboard
        header('Location: dashboard.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error saving profile: " . $e->getMessage();
    }
}

// Check if profile exists
$profile = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();
} catch (PDOException $e) {
    $error = "Error fetching profile: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Profile Setup</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
:root {
    --primary-color: #45a049;
    --primary-light:rgb(86, 235, 178);
    --secondary-color:rgb(18, 172, 44);
    --accent-color: #f72585;
    --light-color: #f8f9fa;
    --dark-color: #212529;
    --success-color: #4cc9f0;
    --warning-color: #f8961e;
    --danger-color: #ef233c;
    --gray-color: #adb5bd;
    --border-radius: 12px;
    --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background-color: #f5f7ff;
    color: var(--dark-color);
    line-height: 1.6;
    padding: 20px;
}

.profile-setup-container {
    max-width: 900px;
    margin: 30px auto;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.profile-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.profile-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
    transform: rotate(30deg);
    animation: shine 8s infinite linear;
}

@keyframes shine {
    0% { transform: rotate(30deg) translate(-10%, -10%); }
    100% { transform: rotate(30deg) translate(10%, 10%); }
}

.profile-header h1 {
    font-size: 2.2rem;
    margin-bottom: 10px;
    position: relative;
    display: inline-block;
}

.profile-header h1 i {
    margin-right: 15px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-15px); }
    60% { transform: translateY(-7px); }
}

.profile-header p {
    font-size: 1rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.profile-form {
    padding: 30px;
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-section h2 {
    font-size: 1.3rem;
    color: var(--primary-color);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.form-section h2 i {
    margin-right: 10px;
    font-size: 1.1em;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--dark-color);
    font-size: 0.95rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #e0e0e0;
    border-radius: var(--border-radius);
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    transition: var(--transition);
    background-color: #f9fafc;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    background-color: white;
}

.form-group textarea {
    min-height: 80px;
    resize: vertical;
}

.unit {
    margin-left: 8px;
    color: var(--gray-color);
    font-size: 0.85rem;
}

.alert {
    padding: 15px;
    margin-bottom: 25px;
    border-radius: var(--border-radius);
    font-size: 0.95rem;
}

.alert-danger {
    background-color: #fee2e2;
    color: #b91c1c;
    border-left: 4px solid #ef4444;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 30px;
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: var(--border-radius);
    font-family: 'Poppins', sans-serif;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.btn i {
    margin-right: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    box-shadow: 0 4px 15px rgba(19, 122, 19, 0.84);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(21, 117, 45, 0.77);
}

.btn-primary:active {
    transform: translateY(0);
}

.btn-primary::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0));
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.btn-primary:hover::after {
    transform: translateX(100%);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .profile-setup-container {
        margin: 15px auto;
    }
    
    .profile-header {
        padding: 20px;
    }
    
    .profile-form {
        padding: 20px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}

/* Animation for form elements */
.form-group {
    animation: fadeInUp 0.5s ease-out;
    animation-fill-mode: both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Delay animations for form groups */
.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }
.form-group:nth-child(3) { animation-delay: 0.3s; }
.form-group:nth-child(4) { animation-delay: 0.4s; }
.form-group:nth-child(5) { animation-delay: 0.5s; }
.form-group:nth-child(6) { animation-delay: 0.6s; }
</style>
</head>
<body>
    <div class="profile-setup-container">
        <div class="profile-header">
            <h1><i class="fas fa-user-plus"></i> Health Profile Setup</h1>
            <p>Please provide your health information to get started with your personalized dashboard.</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form action="profile_setup.php" method="post" class="profile-form">
            <div class="form-section">
                <h2><i class="fas fa-id-card"></i> Basic Information</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo (isset($profile['gender']) && $profile['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($profile['gender']) && $profile['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (isset($profile['gender']) && $profile['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2><i class="fas fa-weight"></i> Body Measurements</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="height">Height (cm)</label>
                        <input type="number" id="height" name="height" step="0.1" min="100" max="250" 
                               value="<?php echo htmlspecialchars($profile['height'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" step="0.1" min="20" max="300" 
                               value="<?php echo htmlspecialchars($profile['weight'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="blood_type">Blood Type</label>
                        <select id="blood_type" name="blood_type" required>
                            <option value="">Select Blood Type</option>
                            <option value="A+" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo (isset($profile['blood_type']) && $profile['blood_type'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2><i class="fas fa-heartbeat"></i> Health Metrics</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="blood_pressure_systolic">Blood Pressure (Systolic)</label>
                        <input type="number" id="blood_pressure_systolic" name="blood_pressure_systolic" 
                               min="70" max="200" value="<?php echo htmlspecialchars($profile['blood_pressure_systolic'] ?? ''); ?>">
                        <span class="unit">mmHg</span>
                    </div>
                    <div class="form-group">
                        <label for="blood_pressure_diastolic">Blood Pressure (Diastolic)</label>
                        <input type="number" id="blood_pressure_diastolic" name="blood_pressure_diastolic" 
                               min="40" max="120" value="<?php echo htmlspecialchars($profile['blood_pressure_diastolic'] ?? ''); ?>">
                        <span class="unit">mmHg</span>
                    </div>
                    <div class="form-group">
                        <label for="heart_rate">Resting Heart Rate</label>
                        <input type="number" id="heart_rate" name="heart_rate" min="30" max="200" 
                               value="<?php echo htmlspecialchars($profile['heart_rate'] ?? ''); ?>">
                        <span class="unit">bpm</span>
                    </div>
                    <div class="form-group">
                        <label for="blood_sugar">Blood Sugar Level</label>
                        <input type="number" id="blood_sugar" name="blood_sugar" step="0.1" min="2" max="20" 
                               value="<?php echo htmlspecialchars($profile['blood_sugar'] ?? ''); ?>">
                        <span class="unit">mmol/L</span>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2><i class="fas fa-allergies"></i> Health Conditions</h2>
                <div class="form-group">
                    <label for="allergies">Allergies</label>
                    <textarea id="allergies" name="allergies" rows="2" placeholder="List any allergies you have..."><?php echo htmlspecialchars($profile['allergies'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="chronic_diseases">Chronic Diseases/Conditions</label>
                    <textarea id="chronic_diseases" name="chronic_diseases" rows="2" placeholder="List any chronic conditions you have..."><?php echo htmlspecialchars($profile['chronic_diseases'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Profile
                </button>
            </div>
        </form>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>