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

// Fetch current profile data
try {
    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();
    
    if (!$profile) {
        header('Location: profile_setup.php');
        exit();
    }
} catch (PDOException $e) {
    die("Error fetching profile: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $blood_type = $_POST['blood_type'];
    $allergies = $_POST['allergies'];
    $chronic_diseases = $_POST['chronic_diseases'];
    
    try {
        $stmt = $pdo->prepare("UPDATE user_profiles SET 
                             full_name = ?, date_of_birth = ?, gender = ?, 
                             height = ?, weight = ?, blood_type = ?,
                             allergies = ?, chronic_diseases = ?
                             WHERE user_id = ?");
        $stmt->execute([$full_name, $date_of_birth, $gender, $height, 
                       $weight, $blood_type, $allergies, $chronic_diseases, $user_id]);
        
        $_SESSION['success'] = "Profile updated successfully!";
        header('Location: dashboard.php#profile');
        exit();
    } catch (PDOException $e) {
        $error = "Error updating profile: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Use the same styles as your profile_setup.php */
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
            margin: 0;
            padding: 2rem;
        }
        
        .profile-form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .form-header h1 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
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
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: #5d4ae3;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--gray);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 8px;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="profile-form-container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-header">
            <h1><i class="fas fa-user-edit"></i> Edit Profile</h1>
            <p>Update your personal and health information</p>
        </div>
        
        <form action="edit_profile.php" method="post">
            <div class="form-grid">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($profile['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" 
                           value="<?php echo htmlspecialchars($profile['date_of_birth']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="Male" <?php echo ($profile['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($profile['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($profile['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="height">Height (cm)</label>
                    <input type="number" id="height" name="height" step="0.1" min="100" max="250" 
                           value="<?php echo htmlspecialchars($profile['height']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="weight">Weight (kg)</label>
                    <input type="number" id="weight" name="weight" step="0.1" min="20" max="300" 
                           value="<?php echo htmlspecialchars($profile['weight']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="blood_type">Blood Type</label>
                    <select id="blood_type" name="blood_type" required>
                        <option value="A+" <?php echo ($profile['blood_type'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                        <option value="A-" <?php echo ($profile['blood_type'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                        <option value="B+" <?php echo ($profile['blood_type'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                        <option value="B-" <?php echo ($profile['blood_type'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                        <option value="AB+" <?php echo ($profile['blood_type'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                        <option value="AB-" <?php echo ($profile['blood_type'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                        <option value="O+" <?php echo ($profile['blood_type'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                        <option value="O-" <?php echo ($profile['blood_type'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="allergies">Allergies</label>
                <textarea id="allergies" name="allergies" rows="2"><?php echo htmlspecialchars($profile['allergies']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="chronic_diseases">Chronic Diseases/Conditions</label>
                <textarea id="chronic_diseases" name="chronic_diseases" rows="2"><?php echo htmlspecialchars($profile['chronic_diseases']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <a href="dashboard.php#profile" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</body>
</html>