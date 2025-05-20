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

// Fetch user profile
try {
    $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();
    
    if (!$profile) {
        // Redirect to profile setup if profile doesn't exist
        header('Location: profile_setup.php');
        exit();
    }
    
    // Calculate BMI
    $bmi = $profile['weight'] / (($profile['height']/100) * ($profile['height']/100));
    $bmi_category = '';
    
    if ($bmi < 18.5) {
        $bmi_category = 'Underweight';
    } elseif ($bmi >= 18.5 && $bmi < 25) {
        $bmi_category = 'Normal weight';
    } elseif ($bmi >= 25 && $bmi < 30) {
        $bmi_category = 'Overweight';
    } else {
        $bmi_category = 'Obese';
    }
    
    // Fetch latest health metrics
    $stmt = $pdo->prepare("SELECT * FROM health_metrics WHERE user_id = ? ORDER BY metric_date DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $latest_metrics = $stmt->fetch();
    
    // Fetch medications
    $stmt = $pdo->prepare("SELECT * FROM medications WHERE user_id = ? ORDER BY start_date DESC");
    $stmt->execute([$user_id]);
    $medications = $stmt->fetchAll();
    
    // Fetch upcoming appointments
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? AND appointment_date >= NOW() ORDER BY appointment_date ASC LIMIT 3");
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll();
    
    // Fetch health metrics history for chart
    $stmt = $pdo->prepare("SELECT metric_date, weight, blood_pressure_systolic, blood_pressure_diastolic, heart_rate 
                          FROM health_metrics 
                          WHERE user_id = ? 
                          ORDER BY metric_date DESC 
                          LIMIT 30");
    $stmt->execute([$user_id]);
    $metrics_history = $stmt->fetchAll();
    
    // Fetch health goals
    $stmt = $pdo->prepare("SELECT * FROM health_goals WHERE user_id = ? ORDER BY target_date ASC");
    $stmt->execute([$user_id]);
    $health_goals = $stmt->fetchAll();
    
    // Fetch completed tasks count
    $stmt = $pdo->prepare("SELECT COUNT(*) as completed_count FROM health_tasks WHERE user_id = ? AND completed = 1");
    $stmt->execute([$user_id]);
    $completed_tasks = $stmt->fetch()['completed_count'];
    
    // Fetch total tasks count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_count FROM health_tasks WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_tasks = $stmt->fetch()['total_count'];
    
    // Prepare data for charts
    $chart_labels = [];
    $weight_data = [];
    $bp_systolic_data = [];
    $bp_diastolic_data = [];
    $hr_data = [];
    
    foreach (array_reverse($metrics_history) as $metric) {
        $chart_labels[] = date('M j', strtotime($metric['metric_date']));
        $weight_data[] = $metric['weight'];
        $bp_systolic_data[] = $metric['blood_pressure_systolic'];
        $bp_diastolic_data[] = $metric['blood_pressure_diastolic'];
        $hr_data[] = $metric['heart_rate'];
    }
    
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

function getGoalStatusClass($goal) {
    $today = new DateTime();
    $targetDate = new DateTime($goal['target_date']);
    
    if ($goal['progress'] >= 100) return 'bg-success';
    if ($today > $targetDate) return 'bg-danger';
    return 'bg-info';
}

function getGoalStatusText($goal) {
    $today = new DateTime();
    $targetDate = new DateTime($goal['target_date']);
    
    if ($goal['progress'] >= 100) return 'Completed';
    if ($today > $targetDate) return 'Overdue';
    return 'In Progress';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
        <link rel="icon" href="assets/favicon.ico" type="image/x-icon">

    <style>
        :root {
  /* Color Palette */
  --primary: #4EA685;
  --primary-light: #4EA685;
  --secondary: #00cec9;
  --success: #00b894;
  --warning: #fdcb6e;
  --danger: #d63031;
  --dark: #2d3436;
  --light: #f5f6fa;
  --gray: #dfe6e9;
  
  /* Theme Colors */
  --bg-color: #f8f9ff;
  --card-bg: #ffffff;
  --text-color: #2d3436;
  --text-muted: #636e72;
  
  /* Shadows */
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.12);
  --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.16);
  
  /* Border Radius */
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 24px;
  
  /* Transitions */
  --transition-fast: all 0.2s ease;
  --transition-normal: all 0.3s ease;
  --transition-slow: all 0.5s ease;
}

/* Base Styles */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', sans-serif;
  background-color: var(--bg-color);
  color: var(--text-color);
  line-height: 1.6;
  overflow-x: hidden;
}

/* Dashboard Layout */
.dashboard-container {
  display: grid;
  grid-template-columns: 280px 1fr;
  min-height: 100vh;
}

/* Sidebar Styles */
.sidebar {
  background: linear-gradient(135deg, var(--primary), #4EA685);
  color: white;
  padding: 2rem 1.5rem;
  position: relative;
  overflow: hidden;
  box-shadow: var(--shadow-md);
  z-index: 10;
}

.sidebar::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
  transform: rotate(30deg);
  animation: shine 15s infinite linear;
  z-index: -1;
}

@keyframes shine {
  0% { transform: rotate(30deg) translate(-10%, -10%); }
  100% { transform: rotate(30deg) translate(10%, 10%); }
}

.sidebar-header {
  margin-bottom: 2.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.sidebar-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  background: linear-gradient(to right, white, #e0e0e0);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.sidebar-header i {
  font-size: 1.75rem;
  background: linear-gradient(to right, #ffffff, #e0e0e0);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.sidebar-menu {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 2rem;
}

.sidebar-menu a {
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  padding: 0.75rem 1rem;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  gap: 0.75rem;
  transition: var(--transition-fast);
}

.sidebar-menu a:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  transform: translateX(5px);
}

.sidebar-menu a.active {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  font-weight: 500;
}

.sidebar-menu a i {
  width: 24px;
  text-align: center;
}

.sidebar-footer {
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-footer a {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  transition: var(--transition-fast);
}

.sidebar-footer a:hover {
  color: white;
  transform: translateX(5px);
}

/* Main Content Styles */
.main-content {
  padding: 2rem;
  overflow-y: auto;
}

/* Header Styles */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.header-left h1 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
  background: linear-gradient(to right, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  display: inline-block;
}

.header-left p {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: var(--card-bg);
  padding: 0.5rem 1rem;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  transition: var(--transition-fast);
  cursor: pointer;
}

.user-profile:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.user-profile img {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--primary-light);
}

/* Stats Cards */
.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.card {
  background: var(--card-bg);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: var(--shadow-sm);
  transition: var(--transition-normal);
  position: relative;
  overflow: hidden;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}

.card::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
  opacity: 0;
  transition: var(--transition-normal);
}

.card:hover::after {
  opacity: 1;
}

.card-icon {
  width: 56px;
  height: 56px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
}

.bg-primary { background: var(--primary); }
.bg-success { background: var(--success); }
.bg-info { background: var(--secondary); }
.bg-warning { background: var(--warning); }
.bg-danger { background: var(--danger); }

.card-info h3 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.card-info p {
  color: var(--text-muted);
  font-size: 0.85rem;
}

/* BMI Slider Widget */
.bmi-slider-container {
  background: var(--card-bg);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-sm);
  position: relative;
  overflow: hidden;
}

.bmi-slider-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.bmi-slider-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.bmi-slider-header h3 i {
  color: var(--primary);
}

.bmi-slider {
  width: 100%;
  height: 12px;
  background: linear-gradient(to right, #4EA685, #2ecc71, #f1c40f, #e74c3c);
  border-radius: 6px;
  margin: 1.5rem 0;
  position: relative;
}

.bmi-slider-marker {
  position: absolute;
  width: 2px;
  height: 20px;
  background: rgba(0, 0, 0, 0.2);
  top: -4px;
}

.bmi-slider-marker:nth-child(1) { left: 18.5%; }
.bmi-slider-marker:nth-child(2) { left: 45%; }
.bmi-slider-marker:nth-child(3) { left: 71.5%; }

.bmi-slider-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: var(--text-muted);
  margin-bottom: 0.5rem;
}

.bmi-slider-value {
  position: absolute;
  top: -40px;
  transform: translateX(-50%);
  background: var(--primary);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: var(--radius-sm);
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: var(--shadow-sm);
}

.bmi-slider-value::after {
  content: '';
  position: absolute;
  bottom: -6px;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 0;
  border-left: 6px solid transparent;
  border-right: 6px solid transparent;
  border-top: 6px solid var(--primary);
}

.bmi-categories {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
}

.bmi-category {
  text-align: center;
  padding: 0.5rem;
  border-radius: var(--radius-sm);
  font-size: 0.8rem;
  font-weight: 500;
  flex: 1;
  margin: 0 0.25rem;
  transition: var(--transition-fast);
}

.bmi-category:hover {
  transform: translateY(-3px);
  box-shadow: var(--shadow-sm);
}

.bmi-category.underweight { background: rgba(52, 152, 219, 0.1); color: #4EA685; }
.bmi-category.normal { background: rgba(46, 204, 113, 0.1); color: #27ae60; }
.bmi-category.overweight { background: rgba(241, 196, 15, 0.1); color: #f39c12; }
.bmi-category.obese { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }

.bmi-category.active {
  background: var(--primary);
  color: white;
  font-weight: 600;
}

/* Section Styles */
.section {
  background: var(--card-bg);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-sm);
  transition: var(--transition-normal);
  position: relative;
  overflow: hidden;
}

.section:hover {
  box-shadow: var(--shadow-md);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-header h2 i {
  color: var(--primary);
}
/* Health Goals Section Enhancements */
.goals-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.goal-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    position: relative;
    overflow: hidden;
    border-left: 6px solid var(--primary);
    transform: translateY(0);
}

.goal-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

.goal-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(108, 92, 231, 0.05) 0%, rgba(108, 92, 231, 0) 100%);
    z-index: 0;
}

.goal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

.goal-title {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--primary);
    margin: 0;
}

.goal-desc {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    position: relative;
    z-index: 1;
}

.goal-progress-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 1.5rem 0;
    position: relative;
    z-index: 1;
}

.goal-progress {
    flex: 1;
    height: 8px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    width: 0%;
    transition: width 1s ease;
    position: relative;
}

.progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, 
        rgba(255,255,255,0.8) 0%, 
        rgba(255,255,255,0) 50%, 
        rgba(255,255,255,0.8) 100%);
    animation: progressShine 2s infinite linear;
}

@keyframes progressShine {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.progress-percent {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--primary);
}

.goal-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    font-size: 0.85rem;
    position: relative;
    z-index: 1;
}

.goal-date {
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.goal-actions {
    display: flex;
    gap: 0.5rem;
}

.goal-complete-btn-container {
    margin-top: 1.5rem;
    position: relative;
    z-index: 1;
    text-align: center;
}

.goal-complete-btn {
    width: 100%;
    padding: 0.75rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.goal-complete-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 184, 148, 0.3);
}

.goal-complete-btn::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 70%);
    transform: rotate(30deg);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.goal-complete-btn:hover::after {
    opacity: 1;
    animation: shine 1.5s infinite;
}

/* Celebration Effects */
.celebration-container {
    position: relative;
    overflow: hidden;
    padding: 1rem;
}

.confetti {
    position: absolute;
    width: 10px;
    height: 10px;
    background-color: var(--primary);
    opacity: 0.7;
    animation: confettiFall 5s linear infinite;
}

.confetti:nth-child(1) {
    left: 10%;
    animation-delay: 0s;
    background-color: #4EA685;;
}

.confetti:nth-child(2) {
    left: 20%;
    animation-delay: 0.5s;
    background-color: #00cec9;
}

.confetti:nth-child(3) {
    left: 30%;
    animation-delay: 1s;
    background-color: #00b894;
}

.confetti:nth-child(4) {
    left: 40%;
    animation-delay: 1.5s;
    background-color: #fdcb6e;
}

.confetti:nth-child(5) {
    left: 50%;
    animation-delay: 2s;
    background-color: #e84393;
}

.confetti:nth-child(6) {
    left: 60%;
    animation-delay: 2.5s;
    background-color: #4EA685;;
}

.confetti:nth-child(7) {
    left: 70%;
    animation-delay: 3s;
    background-color: #00cec9;
}

.confetti:nth-child(8) {
    left: 80%;
    animation-delay: 3.5s;
    background-color: #00b894;
}

.confetti:nth-child(9) {
    left: 90%;
    animation-delay: 4s;
    background-color: #fdcb6e;
}

.confetti:nth-child(10) {
    left: 100%;
    animation-delay: 4.5s;
    background-color: #e84393;
}

@keyframes confettiFall {
    0% {
        transform: translateY(-100px) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}

/* Empty State */
.empty-goals {
    text-align: center;
    padding: 3rem 2rem;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 16px;
    border: 2px dashed rgba(108, 92, 231, 0.3);
    grid-column: 1 / -1;
}

.empty-goals i {
    font-size: 3rem;
    color: var(--primary);
    margin-bottom: 1rem;
    opacity: 0.7;
}

.empty-goals h3 {
    color: var(--dark);
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
}

.empty-goals p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

/* 3D Card Effect */
.goal-card {
    transform-style: preserve-3d;
    transition: all 0.5s ease;
}

.goal-card:hover {
    transform: translateY(-5px) rotateX(2deg) rotateY(2deg);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

/* Add Goal Button Effect */
#add-goal-btn {
    position: relative;
    overflow: hidden;
}

#add-goal-btn::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 70%);
    transform: rotate(30deg);
    animation: shine 3s infinite linear;
    opacity: 0.5;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .goals-container {
        grid-template-columns: 1fr;
    }
    
    .goal-card {
        padding: 1.25rem;
    }
}

/* Button Styles */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 1.25rem;
  border-radius: var(--radius-sm);
  font-weight: 500;
  font-size: 0.85rem;
  cursor: pointer;
  transition: var(--transition-fast);
  border: none;
  outline: none;
  position: relative;
  overflow: hidden;
}

.btn i {
  font-size: 0.9rem;
}

.btn-sm {
  padding: 0.4rem 1rem;
  font-size: 0.8rem;
}

.btn-primary {
  background: var(--primary);
  color: white;
  box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3);
}

.btn-primary:hover {
  background: #5d4ae3;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(108, 92, 231, 0.4);
}

.btn-primary:active {
  transform: translateY(0);
}

.btn-outline-primary {
  background: transparent;
  color: var(--primary);
  border: 1px solid var(--primary);
}

.btn-outline-primary:hover {
  background: rgba(108, 92, 231, 0.1);
  transform: translateY(-2px);
}

.btn-outline-secondary {
  background: transparent;
  color: var(--text-muted);
  border: 1px solid var(--gray);
}

.btn-outline-secondary:hover {
  background: rgba(223, 230, 233, 0.3);
  transform: translateY(-2px);
}

/* Badge Styles */
.badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.bg-info { background: rgba(52, 152, 219, 0.1); color: #3498db; }
.bg-success { background: rgba(46, 204, 113, 0.1); color: #27ae60; }
.bg-warning { background: rgba(241, 196, 15, 0.1); color: #f39c12; }
.bg-danger { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }

/* Metrics Grid */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.metric-card {
  background: var(--card-bg);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
  transition: var(--transition-normal);
  border-top: 3px solid var(--primary);
}

.metric-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}

.metric-card h3 {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--text-muted);
}

.metric-value {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
}

.bp-value {
  font-size: 1.5rem;
}

.bp-separator {
  font-size: 1rem;
  font-weight: 400;
  color: var(--text-muted);
}

.unit {
  font-size: 1rem;
  font-weight: 400;
  color: var(--text-muted);
}

.text-muted {
  color: var(--text-muted);
}

.metric-status {
  display: flex;
  justify-content: flex-end;
}

/* Charts Row */
.charts-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-top: 2rem;
}

@media (max-width: 768px) {
  .charts-row {
    grid-template-columns: 1fr;
  }
}

.chart-container {
  background: var(--card-bg);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
}

/* Tables */
.table-responsive {
  overflow-x: auto;
}

.medications-table {
  width: 100%;
  border-collapse: collapse;
}

.medications-table th, 
.medications-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.medications-table th {
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-muted);
  background: rgba(0, 0, 0, 0.02);
}

.medications-table tr:hover td {
  background: rgba(108, 92, 231, 0.03);
}
/* Action Buttons for Medication Table */
.action-buttons {
    display: flex;
    gap: 0.5rem;
}

/* Alert Messages */
.alert {
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.9rem;
}

.alert-success {
    background-color: rgba(0, 184, 148, 0.1);
    color: #00b894;
    border-left: 4px solid #00b894;
}

.alert-danger {
    background-color: rgba(214, 48, 49, 0.1);
    color: #d63031;
    border-left: 4px solid #d63031;
}

/* Appointments Grid */
.appointments-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

.appointment-card {
  background: var(--card-bg);
  border-radius: var(--radius-md);
  padding: 1.25rem;
  display: flex;
  gap: 1rem;
  box-shadow: var(--shadow-sm);
  transition: var(--transition-normal);
  border-left: 4px solid var(--primary);
}

.appointment-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}

.appointment-date {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(108, 92, 231, 0.1);
  border-radius: var(--radius-sm);
  padding: 0.75rem;
  min-width: 60px;
}

.date-day {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary);
}

.date-month {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--primary);
}

.appointment-details {
  flex: 1;
}

.appointment-details h3 {
  font-size: 1.1rem;
  margin-bottom: 0.25rem;
}

.specialty {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-bottom: 0.5rem;
}

.time, .location {
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.25rem;
}

.time i, .location i {
  color: var(--primary);
}

.appointment-actions {
  display: flex;
  align-items: flex-start;
}
/* ===== Appointments Section ===== */
.appointments-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
}

.appointment-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    transition: all 0.3s ease;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.18);
}

.appointment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(31, 38, 135, 0.2);
}

.appointment-date {
    background: linear-gradient(135deg, #4EA685;, #a29bfe);
    border-radius: 12px;
    padding: 1rem;
    color: white;
    text-align: center;
    min-width: 80px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    margin-right: 1.5rem;
}
/* ===== Profile Section ===== */
.profile-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

.profile-card {
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.18);
}

.profile-card h3 {
    color: #4EA685;;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.25rem;
}

.profile-field {
    display: flex;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.field-label {
    font-weight: 600;
    color: #636e72;
    min-width: 120px;
}

.field-value {
    color: #2d3436;
}

.blood-type {
    background: #ff7675;
    color: white;
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
}

/* Glassmorphism Effect */
.glass-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.18);
}

.date-day {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
}

.date-month {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 16px;
    border: 2px dashed rgba(108, 92, 231, 0.3);
}

.empty-icon {
    font-size: 3rem;
    color: #4EA685;;
    margin-bottom: 1rem;
    opacity: 0.7;
}

.empty-state h3 {
    color: #2d3436;
    margin-bottom: 1.5rem;
}

/* Pulse Animation for CTA Button */
.pulse-button {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(108, 92, 231, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(108, 92, 231, 0); }
    100% { box-shadow: 0 0 0 0 rgba(108, 92, 231, 0); }
}

/* Profile Grid */
.profile-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
}

.profile-card {
  background: var(--card-bg);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
}

.profile-card h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.profile-detail {
  display: flex;
  margin-bottom: 0.75rem;
}

.detail-label {
  font-weight: 500;
  width: 150px;
  color: var(--text-muted);
}

.detail-value {
  flex: 1;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 3rem 2rem;
  background: rgba(0, 0, 0, 0.02);
  border-radius: var(--radius-md);
  border: 1px dashed rgba(0, 0, 0, 0.1);
}

.empty-state i {
  font-size: 3rem;
  color: var(--primary-light);
  margin-bottom: 1rem;
  opacity: 0.5;
}

.empty-state p {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
}

/* Animations */
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.floating {
  animation: float 3s ease-in-out infinite;
}

/* Responsive Adjustments */
@media (max-width: 1024px) {
  .dashboard-container {
    grid-template-columns: 240px 1fr;
  }
}

@media (max-width: 768px) {
  .dashboard-container {
    grid-template-columns: 1fr;
  }
  
  .sidebar {
    position: fixed;
    width: 260px;
    height: 100vh;
    left: -260px;
    transition: var(--transition-normal);
  }
  
  .sidebar.active {
    left: 0;
  }
  
  .main-content {
    margin-left: 0;
  }
}

/* Stickers (Decorative Elements) */
.sticker {
  position: absolute;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  opacity: 0.1;
  z-index: -1;
}

.sticker-1 {
  top: 20px;
  right: 20px;
  background: var(--primary);
  animation: float 4s ease-in-out infinite;
}

.sticker-2 {
  bottom: 40px;
  left: 30px;
  background: var(--secondary);
  animation: float 5s ease-in-out infinite 1s;
}

.sticker-3 {
  top: 50%;
  right: 10%;
  background: var(--danger);
  animation: float 6s ease-in-out infinite 0.5s;
}

/* Add the new styles for tiles and goals section */
.dashboard-tiles {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.tile {
    background: var(--card-bg);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow-sm);
    transition: var(--transition-normal);
    position: relative;
    overflow: hidden;
}

.tile:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}
.horizontal-sections {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (max-width: 1200px) {
    .horizontal-sections {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .horizontal-sections {
        grid-template-columns: 1fr;
    }
}

.tile-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    flex-shrink: 0;
}

.medication-tile .tile-icon { background: var(--primary); }
.appointments-tile .tile-icon { background: var(--secondary); }
.tasks-tile .tile-icon { background: var(--warning); }
.completed-tile .tile-icon { background: var(--success); }

.tile-content h3 {
    font-size: 1rem;
    color: var(--text-muted);
    margin-bottom: 0.25rem;
}

.tile-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-color);
}

/* Health Goals Section */
.goals-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.goal-card {
    background: var(--card-bg);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    border-left: 4px solid var(--primary);
    transition: var(--transition-normal);
    position: relative;
}

.goal-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.goal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.goal-title {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--primary);
}

.goal-desc {
    color: var(--text-muted);
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.goal-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    font-size: 0.85rem;
}

.goal-date {
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.goal-actions {
    display: flex;
    gap: 0.5rem;
}

.goal-progress {
    height: 6px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
    margin-top: 1rem;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: var(--primary);
    width: 0%;
    transition: width 0.5s ease;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: var(--card-bg);
    border-radius: var(--radius-md);
    padding: 2rem;
    width: 100%;
    max-width: 500px;
    box-shadow: var(--shadow-lg);
    position: relative;
}

.close-modal {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
    transition: var(--transition-fast);
}

.close-modal:hover {
    color: var(--danger);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-color);
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--gray);
    border-radius: var(--radius-sm);
    font-family: 'Poppins', sans-serif;
    transition: var(--transition-fast);
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

/* Sparkle button effect */
.sparkle-btn {
    position: relative;
    overflow: hidden;
}

.sparkle-btn::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 70%);
    transform: rotate(30deg);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sparkle-btn:hover::after {
    opacity: 1;
    animation: shine 1.5s infinite;
}

@keyframes shine {
    0% { transform: rotate(30deg) translate(-10%, -10%); }
    100% { transform: rotate(30deg) translate(10%, 10%); }
}

/* Empty state for goals */
.empty-goals {
    text-align: center;
    padding: 3rem 2rem;
    background: rgba(0, 0, 0, 0.02);
    border-radius: var(--radius-md);
    border: 1px dashed rgba(0, 0, 0, 0.1);
    grid-column: 1 / -1;
}

.empty-goals i {
    font-size: 3rem;
    color: var(--primary-light);
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-goals p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .dashboard-tiles {
        grid-template-columns: 1fr 1fr;
    }
    
    .goals-container {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .dashboard-tiles {
        grid-template-columns: 1fr;
    }
}

/* Existing styles from your dashboard... */
:root {
  /* Color Palette */
  --primary: #4EA685;;
  --primary-light: #a29bfe;
  --secondary: #00cec9;
  --success: #00b894;
  --warning: #fdcb6e;
  --danger: #d63031;
  --dark: #2d3436;
  --light: #f5f6fa;
  --gray: #dfe6e9;
  
  /* Theme Colors */
  --bg-color: #f8f9ff;
  --card-bg: #ffffff;
  --text-color: #2d3436;
  --text-muted: #636e72;
  
  /* Shadows */
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
  --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.12);
  --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.16);
  
  /* Border Radius */
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 24px;
  
  /* Transitions */
  --transition-fast: all 0.2s ease;
  --transition-normal: all 0.3s ease;
  --transition-slow: all 0.5s ease;
}
        /* Your existing CSS styles here */
        /* ... */
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header" >
           

                <h2><a href="index.php"> <i class="fas fa-heartbeat"></i> </a> CareSphere</h2>
            </div>
            <div class="sidebar-menu">
                <a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="#profile"><i class="fas fa-user"></i> Profile</a>
                <a href="#health-metrics"><i class="fas fa-chart-line"></i> Health Metrics</a>
                <a href="#medications"><i class="fas fa-pills"></i> Medications</a>
                <a href="#appointments"><i class="fas fa-calendar-alt"></i> Appointments</a>
                <a href="#health-goals"><i class="fas fa-bullseye"></i> Health Goals</a>
            
            </div>
            <div class="sidebar-footer">
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="header-left">
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($profile['full_name']); ?></p>
                </div>
                <div class="header-right">
                    <div class="user-profile">
                        <img src="assets/images/default-avatar.jpg" alt="User Avatar">
                        <span><?php echo htmlspecialchars($profile['full_name']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Status Tiles -->
            <div class="dashboard-tiles">
                <div class="tile medication-tile">
                    <div class="tile-icon"><i class="fas fa-pills"></i></div>
                    <div class="tile-content">
                        <h3>Medications</h3>
                        <span class="tile-value"><?= count($medications) ?></span>
                    </div>
                </div>
                
                <div class="tile appointments-tile">
                    <div class="tile-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="tile-content">
                        <h3>Upcoming</h3>
                        <span class="tile-value"><?= count($appointments) ?></span>
                    </div>
                </div>
                
                <div class="tile tasks-tile">
                    <div class="tile-icon"><i class="fas fa-tasks"></i></div>
                    <div class="tile-content">
                        <h3>Total Tasks</h3>
                        <span class="tile-value" id="total-tasks"><?= $total_tasks ?></span>
                    </div>
                </div>
                
                <div class="tile completed-tile">
                    <div class="tile-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="tile-content">
                        <h3>Completed</h3>
                        <span class="tile-value" id="completed-tasks"><?= $completed_tasks ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Health Stats -->
            <div class="stats-cards">
                <div class="card">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-weight"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($profile['weight']); ?> kg</h3>
                        <p>Weight</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon bg-success">
                        <i class="fas fa-ruler-vertical"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($profile['height']); ?> cm</h3>
                        <p>Height</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon bg-info">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo round($bmi, 1); ?></h3>
                        <p>BMI (<?php echo $bmi_category; ?>)</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($profile['blood_type']); ?></h3>
                        <p>Blood Type</p>
                    </div>
                </div>
            </div>
            
            <!-- Health Metrics Section -->
            <div class="section" id="health-metrics">
                <div class="section-header">
                    <h2><i class="fas fa-chart-line"></i> Health Metrics</h2>
                    <a href="add_metric.php" class="btn btn-sm btn-outline-primary">Add New</a>
                </div>
                
                <div class="metrics-grid">
                    <div class="metric-card">
                        <h3>Blood Pressure</h3>
                        <div class="metric-value">
                            <?php if ($latest_metrics && $latest_metrics['blood_pressure_systolic']): ?>
                                <span class="bp-value"><?php echo htmlspecialchars($latest_metrics['blood_pressure_systolic']); ?></span>
                                <span class="bp-separator">/</span>
                                <span class="bp-value"><?php echo htmlspecialchars($latest_metrics['blood_pressure_diastolic']); ?></span>
                                <span class="bp-unit">mmHg</span>
                            <?php else: ?>
                                <span class="text-muted">No data</span>
                            <?php endif; ?>
                        </div>
                        <div class="metric-status">
                            <?php 
                            if ($latest_metrics && $latest_metrics['blood_pressure_systolic']) {
                                $sys = $latest_metrics['blood_pressure_systolic'];
                                $dia = $latest_metrics['blood_pressure_diastolic'];
                                
                                if ($sys < 90 || $dia < 60) {
                                    echo '<span class="badge bg-info">Low</span>';
                                } elseif ($sys >= 90 && $sys < 120 && $dia >= 60 && $dia < 80) {
                                    echo '<span class="badge bg-success">Normal</span>';
                                } elseif ($sys >= 120 && $sys < 140 || $dia >= 80 && $dia < 90) {
                                    echo '<span class="badge bg-warning">Elevated</span>';
                                } else {
                                    echo '<span class="badge bg-danger">High</span>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Heart Rate</h3>
                        <div class="metric-value">
                            <?php if ($latest_metrics && $latest_metrics['heart_rate']): ?>
                                <span><?php echo htmlspecialchars($latest_metrics['heart_rate']); ?></span>
                                <span class="unit">bpm</span>
                            <?php else: ?>
                                <span class="text-muted">No data</span>
                            <?php endif; ?>
                        </div>
                        <div class="metric-status">
                            <?php 
                            if ($latest_metrics && $latest_metrics['heart_rate']) {
                                $hr = $latest_metrics['heart_rate'];
                                
                                if ($hr < 60) {
                                    echo '<span class="badge bg-info">Low</span>';
                                } elseif ($hr >= 60 && $hr < 100) {
                                    echo '<span class="badge bg-success">Normal</span>';
                                } else {
                                    echo '<span class="badge bg-danger">High</span>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Blood Sugar</h3>
                        <div class="metric-value">
                            <?php if ($latest_metrics && $latest_metrics['blood_sugar']): ?>
                                <span><?php echo htmlspecialchars($latest_metrics['blood_sugar']); ?></span>
                                <span class="unit">mmol/L</span>
                            <?php else: ?>
                                <span class="text-muted">No data</span>
                            <?php endif; ?>
                        </div>
                        <div class="metric-status">
                            <?php 
                            if ($latest_metrics && $latest_metrics['blood_sugar']) {
                                $bs = $latest_metrics['blood_sugar'];
                                
                                if ($bs < 4) {
                                    echo '<span class="badge bg-info">Low</span>';
                                } elseif ($bs >= 4 && $bs < 7.8) {
                                    echo '<span class="badge bg-success">Normal</span>';
                                } else {
                                    echo '<span class="badge bg-danger">High</span>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="charts-row">
                    <div class="chart-container">
                        <canvas id="weightChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <canvas id="bpChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Medications Section -->
            <div class="section" id="medications">
                <div class="section-header">
                    <h2><i class="fas fa-pills"></i> Medications</h2>
                    <a href="add_medication.php" class="btn btn-sm btn-outline-primary">Add New</a>
                </div>
                
                <?php if (isset($_SESSION['medication_success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['medication_success']; unset($_SESSION['medication_success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['medication_error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['medication_error']; unset($_SESSION['medication_error']); ?></div>
                <?php endif; ?>
                
                <?php if (count($medications) > 0): ?>
                    <div class="table-responsive">
                        <table class="medications-table">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Dosage</th>
                                    <th>Frequency</th>
                                    <th>Purpose</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medications as $med): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($med['name']); ?></td>
                                        <td><?php echo htmlspecialchars($med['dosage']); ?></td>
                                        <td><?php echo htmlspecialchars($med['frequency']); ?></td>
                                        <td><?php echo htmlspecialchars($med['purpose']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_medication.php?id=<?php echo $med['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <a href="delete_medication.php?id=<?php echo $med['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this medication?')">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-pills"></i>
                        <p>No medications recorded</p>
                        <a href="add_medication.php" class="btn btn-primary">Add Your First Medication</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Appointments Section -->
            <div class="section" id="appointments">
                <div class="section-header">
                    <h2><i class="fas fa-calendar-alt"></i> Upcoming Appointments</h2>
                    <a href="add_appointment.php" class="btn btn-sm btn-primary pulse-button">
                        <i class="fas fa-plus"></i> New Appointment
                    </a>
                </div>
                
                <?php if (isset($_SESSION['appointment_success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['appointment_success']; unset($_SESSION['appointment_success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['appointment_error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['appointment_error']; unset($_SESSION['appointment_error']); ?></div>
                <?php endif; ?>
                
                <?php if (count($appointments) > 0): ?>
                    <div class="appointments-grid">
                        <?php foreach ($appointments as $appt): ?>
                            <div class="appointment-card glass-card">
                                <div class="appointment-date">
                                    <div class="date-day"><?= date('d', strtotime($appt['appointment_date'])) ?></div>
                                    <div class="date-month"><?= date('M', strtotime($appt['appointment_date'])) ?></div>
                                </div>
                                <div class="appointment-details">
                                    <h3><?= htmlspecialchars($appt['doctor_name']) ?></h3>
                                    <p class="specialty"><?= htmlspecialchars($appt['specialty']) ?></p>
                                    <p class="time">
                                        <i class="fas fa-clock"></i> 
                                        <?= date('h:i A', strtotime($appt['appointment_date'])) ?>
                                    </p>
                                    <p class="location">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?= htmlspecialchars($appt['location']) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h3>No upcoming appointments</h3>
                        <a href="add_appointment.php" class="btn btn-primary gradient-btn">
                            Schedule an Appointment
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
<!-- Health Goals Section -->
<div class="section" id="health-goals">
    <div class="section-header">
        <h2><i class="fas fa-bullseye"></i> Health Goals</h2>
        <a href="add_goal.php" class="btn btn-primary sparkle-btn" id="add-goal-btn">
            <i class="fas fa-plus"></i> Add Goal
        </a>
    </div>
    
    <?php if (isset($_SESSION['goal_success'])): ?>
        <div class="alert alert-success animate__animated animate__bounceIn">
            <div class="celebration-container">
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <div class="confetti"></div>
                <?php echo $_SESSION['goal_success']; unset($_SESSION['goal_success']); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['goal_error'])): ?>
        <div class="alert alert-danger animate__animated animate__headShake">
            <?php echo $_SESSION['goal_error']; unset($_SESSION['goal_error']); ?>
        </div>
    <?php endif; ?>
    
    <div class="goals-container">
        <?php if (empty($health_goals)) : ?>
            <div class="empty-goals animate__animated animate__fadeIn">
                <i class="fas fa-bullseye fa-4x"></i>
                <h3>No health goals set yet</h3>
                <p>Start your health journey by setting your first goal</p>
                <a href="add_goal.php" class="btn btn-primary pulse-button">
                    <i class="fas fa-plus"></i> Add Your First Goal
                </a>
            </div>
        <?php else : ?>
            <?php foreach ($health_goals as $goal) : ?>
                <div class="goal-card animate__animated animate__fadeInUp" 
                     style="animation-delay: <?php echo $index * 0.1; ?>s">
                    <div class="goal-header">
                        <h3 class="goal-title"><?php echo htmlspecialchars($goal['title']); ?></h3>
                        <div class="goal-actions">
                            <a href="edit_goal.php?id=<?php echo $goal['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete_goal.php?id=<?php echo $goal['id']; ?>" class="btn btn-sm btn-outline-danger delete-goal-btn"
                               data-goal-title="<?php echo htmlspecialchars($goal['title']); ?>">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    
                    <?php if (!empty($goal['description'])) : ?>
                        <p class="goal-desc"><?php echo htmlspecialchars($goal['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="goal-progress-container">
                        <div class="goal-progress">
                            <div class="progress-bar" style="width: <?php echo $goal['progress']; ?>%"></div>
                        </div>
                        <span class="progress-percent"><?php echo $goal['progress']; ?>%</span>
                    </div>
                    
                    <div class="goal-meta">
                        <span class="goal-date">
                            <i class="far fa-calendar-alt"></i> 
                            <?php echo date('M j, Y', strtotime($goal['target_date'])); ?>
                        </span>
                        <span class="badge <?php echo getGoalStatusClass($goal); ?>">
                            <?php echo getGoalStatusText($goal); ?>
                        </span>
                    </div>
                    
                    <?php if ($goal['progress'] < 100) : ?>
                        <div class="goal-complete-btn-container">
                            <a href="complete_goal.php?id=<?php echo $goal['id']; ?>" 
                               class="btn btn-sm btn-success goal-complete-btn">
                                <i class="fas fa-check"></i> Mark Complete
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
            
            <!-- Profile Section -->
            <div class="section" id="profile">
                <div class="section-header">
                    <h2><i class="fas fa-user-circle"></i> Profile</h2>
                    <a href="edit_profile.php" class="btn btn-sm btn-outline">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
                
                <div class="profile-grid">
                    <!-- Personal Info Card -->
                    <div class="profile-card glass-card">
                        <h3><i class="fas fa-user-tag"></i> Personal Information</h3>
                        <div class="profile-field">
                            <span class="field-label">Full Name:</span>
                            <span class="field-value"><?= htmlspecialchars($profile['full_name']) ?></span>
                        </div>
                        <div class="profile-field">
                            <span class="field-label">Date of Birth:</span>
                            <span class="field-value"><?= date('F j, Y', strtotime($profile['date_of_birth'])) ?></span>
                        </div>
                        <div class="profile-field">
                            <span class="field-label">Age:</span>
                            <span class="field-value">
                                <?php 
                                $dob = new DateTime($profile['date_of_birth']);
                                $now = new DateTime();
                                echo $dob->diff($now)->y;
                                ?> years
                            </span>
                        </div>
                        <div class="profile-field">
                            <span class="field-label">Gender:</span>
                            <span class="field-value"><?= htmlspecialchars($profile['gender']) ?></span>
                        </div>
                    </div>
                    
                    <!-- Health Info Card -->
                    <div class="profile-card glass-card">
                        <h3><i class="fas fa-heartbeat"></i> Health Information</h3>
                        <div class="profile-field">
                            <span class="field-label">Blood Type:</span>
                            <span class="field-value blood-type"><?= htmlspecialchars($profile['blood_type']) ?></span>
                        </div>
                        <div class="profile-field">
                            <span class="field-label">Allergies:</span>
                            <span class="field-value"><?= $profile['allergies'] ? htmlspecialchars($profile['allergies']) : 'None recorded' ?></span>
                        </div>
                        <div class="profile-field">
                            <span class="field-label">Conditions:</span>
                            <span class="field-value"><?= $profile['chronic_diseases'] ? htmlspecialchars($profile['chronic_diseases']) : 'None recorded' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Weight Chart
        const weightCtx = document.getElementById('weightChart').getContext('2d');
        const weightChart = new Chart(weightCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Weight (kg)',
                    data: <?php echo json_encode($weight_data); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Weight Trend'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
        
        // Blood Pressure Chart
        const bpCtx = document.getElementById('bpChart').getContext('2d');
        const bpChart = new Chart(bpCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [
                    {
                        label: 'Systolic',
                        data: <?php echo json_encode($bp_systolic_data); ?>,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1
                    },
                    {
                        label: 'Diastolic',
                        data: <?php echo json_encode($bp_diastolic_data); ?>,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Blood Pressure Trend'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
        
        // BMI Slider Calculation
        document.addEventListener('DOMContentLoaded', function() {
            const bmi = <?php echo round($bmi, 1); ?>;
            const bmiCategory = '<?php echo $bmi_category; ?>'.toLowerCase().replace(' ', '');
            
            // Calculate position (BMI range: 15 to 40)
            let position = ((bmi - 15) / (40 - 15)) * 100;
            position = Math.max(0, Math.min(100, position)); // Clamp between 0-100
            
            // Create slider value indicator
            const slider = document.createElement('div');
            slider.className = 'bmi-slider-value';
            slider.style.left = `${position}%`;
            slider.textContent = bmi;
            
            // Highlight current category
            document.querySelector(`.bmi-category.${bmiCategory}`).classList.add('active');
            
            // Insert into DOM
            document.querySelector('.bmi-slider').appendChild(slider);
            
            // Set task counts
            document.getElementById('total-tasks').textContent = <?php echo $total_tasks; ?>;
            document.getElementById('completed-tasks').textContent = <?php echo $completed_tasks; ?>;
            
            // Delete confirmation for medications
            const deleteButtons = document.querySelectorAll('.btn-outline-danger');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this item?')) {
                        e.preventDefault();
                    }
                });
            });
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
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete goal confirmation
    document.querySelectorAll('.delete-goal-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const goalTitle = this.getAttribute('data-goal-title');
            if (!confirm(`Are you sure you want to delete the goal "${goalTitle}"?`)) {
                e.preventDefault();
            }
        });
    });
    
    // Animate progress bars
    document.querySelectorAll('.progress-bar').forEach(bar => {
        const targetWidth = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = targetWidth;
        }, 100);
    });
});
</script>
</body>
</html>