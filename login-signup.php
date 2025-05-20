<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Debugging - check if session is working
error_log("Session ID: " . session_id()); 

// Database connection - using the same connection as other files
require_once __DIR__ . '/includes/db_connection.php';
global $pdo;

// Redirect to homepage if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
error_log("Database connection: " . ($pdo ? "Success" : "Failed"));



if (isset($_POST['signup'])) {
    error_log("Signup form submitted");
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm-password']);

    // Debug received data
    error_log("Signup data - Username: $username, Email: $email");

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error = "Password must be at least 8 characters with uppercase, lowercase, number, and special character";
    } else {
        try {
            // Check if username/email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                $error = "Username or email already exists";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
                
                if ($stmt->execute([$username, $email, $hashed_password])) {
                    // Get the new user's ID
                    $userId = $pdo->lastInsertId();
                    error_log("New user created with ID: $userId");
                    
                    // Set session variables
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    
                    // Redirect to index
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                    error_log("Registration failed: " . implode(" ", $stmt->errorInfo()));
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            error_log("Database error during signup: " . $e->getMessage());
        }
    }
}

// Handle Sign In
if (isset($_POST['signin'])) {
    error_log("Signin form submitted");
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            error_log("User found: " . $user['username']);
            if (password_verify($password, $user['password'])) {
                // Set session and redirect
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid username or password.";
                error_log("Password verification failed");
            }
        } else {
            $error = "Invalid username or password.";
            error_log("User not found: $username");
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        error_log("Database error during signin: " . $e->getMessage());
    }
}


// Debug the error state
if (!empty($error)) {
    error_log("Error to display: $error");
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('popup-message').textContent = '".addslashes($error)."';
            document.getElementById('popup').style.display = 'flex';
        });
    </script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In / Sign Up Form</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary-color: #4EA685;
            --secondary-color: #57B894;
            --black: #000000;
            --white: #ffffff;
            --gray: #efefef;
            --gray-2: #757575;

            --facebook-color: #4267B2;
            --google-color: #DB4437;
            --twitter-color: #1DA1F2;
            --insta-color: #E1306C;
        }

        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100vh;
            overflow: hidden;
        }

        .container {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            height: 100vh;
        }

        .col {
            width: 50%;
        }

        .align-items-center {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .form-wrapper {
            width: 100%;
            max-width: 28rem;
        }

        .form {
            padding: 1rem;
            background-color: var(--white);
            border-radius: 1.5rem;
            width: 100%;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            transform: scale(0);
            transition: .5s ease-in-out;
            transition-delay: 1s;
        }

        .input-group {
            position: relative;
            width: 100%;
            margin: 1rem 0;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            font-size: 1.4rem;
            color: var(--gray-2);
        }

        .input-group input {
            width: 100%;
            padding: 1rem 3rem;
            font-size: 1rem;
            background-color: var(--gray);
            border-radius: .5rem;
            border: 0.125rem solid var(--white);
            outline: none;
        }

        .input-group input:focus {
            border: 0.125rem solid var(--primary-color);
        }

        .form button {
            cursor: pointer;
            width: 100%;
            padding: .6rem 0;
            border-radius: .5rem;
            border: none;
            background-color: var(--primary-color);
            color: var(--white);
            font-size: 1.2rem;
            outline: none;
        }

        .form p {
            margin: 1rem 0;
            font-size: .7rem;
        }

        .flex-col {
            flex-direction: column;
        }

        .social-list {
            margin: 2rem 0;
            padding: 1rem;
            border-radius: 1.5rem;
            width: 100%;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            transform: scale(0);
            transition: .5s ease-in-out;
            transition-delay: 1.2s;
        }

        .social-list>div {
            color: var(--white);
            margin: 0 .5rem;
            padding: .7rem;
            cursor: pointer;
            border-radius: .5rem;
            cursor: pointer;
            transform: scale(0);
            transition: .5s ease-in-out;
        }

        .social-list>div:nth-child(1) {
            transition-delay: 1.4s;
        }

        .social-list>div:nth-child(2) {
            transition-delay: 1.6s;
        }

        .social-list>div:nth-child(3) {
            transition-delay: 1.8s;
        }

        .social-list>div:nth-child(4) {
            transition-delay: 2s;
        }

        .social-list>div>i {
            font-size: 1.5rem;
            transition: .4s ease-in-out;
        }

        .social-list>div:hover i {
            transform: scale(1.5);
        }

        .facebook-bg {
            background-color: var(--facebook-color);
        }

        .google-bg {
            background-color: var(--google-color);
        }

        .twitter-bg {
            background-color: var(--twitter-color);
        }

        .insta-bg {
            background-color: var(--insta-color);
        }

        .pointer {
            cursor: pointer;
        }

        .container.sign-in .form.sign-in,
        .container.sign-in .social-list.sign-in,
        .container.sign-in .social-list.sign-in>div,
        .container.sign-up .form.sign-up,
        .container.sign-up .social-list.sign-up,
        .container.sign-up .social-list.sign-up>div {
            transform: scale(1);
        }

        .content-row {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 6;
            width: 100%;
        }

        .text {
            margin: 4rem;
            color: var(--white);
        }

        .text h2 {
            font-size: 3.5rem;
            font-weight: 800;
            margin: 2rem 0;
            transition: 1s ease-in-out;
        }

        .text p {
            font-weight: 600;
            transition: 1s ease-in-out;
            transition-delay: .2s;
        }

        .img img {
            width: 30vw;
            transition: 1s ease-in-out;
            transition-delay: .4s;
        }

        .text.sign-in h2,
        .text.sign-in p,
        .img.sign-in img {
            transform: translateX(-250%);
        }

        .text.sign-up h2,
        .text.sign-up p,
        .img.sign-up img {
            transform: translateX(250%);
        }

        .container.sign-in .text.sign-in h2,
        .container.sign-in .text.sign-in p,
        .container.sign-in .img.sign-in img,
        .container.sign-up .text.sign-up h2,
        .container.sign-up .text.sign-up p,
        .container.sign-up .img.sign-up img {
            transform: translateX(0);
        }

        /* BACKGROUND */
        .container::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            height: 100vh;
            width: 300vw;
            transform: translate(35%, 0);
            background-image: linear-gradient(-45deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            transition: 1s ease-in-out;
            z-index: 6;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            border-bottom-right-radius: max(50vw, 50vh);
            border-top-left-radius: max(50vw, 50vh);
        }

        .container.sign-in::before {
            transform: translate(0, 0);
            right: 50%;
        }

        .container.sign-up::before {
            transform: translate(100%, 0);
            right: 50%;
        }

        /* RESPONSIVE */
        @media only screen and (max-width: 425px) {
            .container::before,
            .container.sign-in::before,
            .container.sign-up::before {
                height: 100vh;
                border-bottom-right-radius: 0;
                border-top-left-radius: 0;
                z-index: 0;
                transform: none;
                right: 0;
            }

            .container.sign-in .col.sign-in,
            .container.sign-up .col.sign-up {
                transform: translateY(0);
            }

            .content-row {
                align-items: flex-start !important;
            }

            .content-row .col {
                transform: translateY(0);
                background-color: unset;
            }

            .col {
                width: 100%;
                position: absolute;
                padding: 2rem;
                background-color: var(--white);
                border-top-left-radius: 2rem;
                border-top-right-radius: 2rem;
                transform: translateY(100%);
                transition: 1s ease-in-out;
            }

            .row {
                align-items: flex-end;
                justify-content: flex-end;
            }

            .form,
            .social-list {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .text {
                margin: 0;
            }

            .text p {
                display: none;
            }

            .text h2 {
                margin: .5rem;
                font-size: 2rem;
            }
        }

        /* Pop-up Warning Styles */
        .popup {
            display: <?php echo !empty($error) ? 'flex' : 'none'; ?>;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: flex-start;
            z-index: 1000;
        }

        .popup-content {
            background-color: var(--white);
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            margin-top: 1rem;
        }

        .popup-content span {
            font-size: 0.9rem;
            color: var(--black);
        }

        .popup-content button {
            margin-top: 0.5rem;
            padding: 0.3rem 0.8rem;
            background-color: transparent;
            color: red;
            border: 1px solid red;
            border-radius: 0.3rem;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .popup-content button:hover {
            background-color: red;
            color: var(--white);
        }
    </style>
</head>
<body>
    <div id="container" class="container">
        <!-- FORM SECTION -->
        <div class="row">
            <!-- SIGN UP -->
            <div class="col align-items-center flex-col sign-up">
                <div class="form-wrapper align-items-center">
                    <form class="form sign-up" method="POST" action="">
                        <div class="input-group">
                            <i class='bx bxs-user'></i>
                            <input type="text" name="username" placeholder="Username" required minlength="3" maxlength="20">
                        </div>
                        <div class="input-group">
                            <i class='bx bx-mail-send'></i>
                            <input type="email" name="email" placeholder="Email" required>
                        </div>
                        <div class="input-group">
                            <i class='bx bxs-lock-alt'></i>
                            <input type="password" name="password" placeholder="Password" required
                                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                title="Password must be at least 8 characters with uppercase, lowercase, number, and special character">
                        </div>
                        <div class="input-group">
                            <i class='bx bxs-lock-alt'></i>
                            <input type="password" name="confirm-password" placeholder="Confirm password" required>
                        </div>
                        <button type="submit" name="signup">
                            Sign up
                        </button>
                        <p>
                            <span>Already have an account?</span>
                            <b onclick="toggle()" class="pointer">Sign in here</b>
                        </p>
                    </form>
                </div>
            </div>
            <!-- END SIGN UP -->
            
            <!-- SIGN IN -->
            <div class="col align-items-center flex-col sign-in">
                <div class="form-wrapper align-items-center">
                    <form class="form sign-in" method="POST" action="">
                        <div class="input-group">
                            <i class='bx bxs-user'></i>
                            <input type="text" name="username" placeholder="Username" required>
                        </div>
                        <div class="input-group">
                            <i class='bx bxs-lock-alt'></i>
                            <input type="password" name="password" placeholder="Password" required>
                        </div>
                        <button type="submit" name="signin">
                            Sign in
                        </button>
                        <p>
                            <b>Forgot password?</b>
                        </p>
                        <p>
                            <span>Don't have an account?</span>
                            <b onclick="toggle()" class="pointer">Sign up here</b>
                        </p>
                    </form>
                </div>
            </div>
            <!-- END SIGN IN -->
        </div>
        <!-- END FORM SECTION -->
        
        <!-- CONTENT SECTION -->
        <div class="row content-row">
            <!-- SIGN IN CONTENT -->
            <div class="col align-items-center flex-col">
                <div class="text sign-in">
                    <h2>Welcome</h2>
                </div>
            </div>
            <!-- END SIGN IN CONTENT -->
            
            <!-- SIGN UP CONTENT -->
            <div class="col align-items-center flex-col">
                <div class="text sign-up">
                    <h2>Join with us</h2>
                </div>
            </div>
            <!-- END SIGN UP CONTENT -->
        </div>
        <!-- END CONTENT SECTION -->
    </div>

    <!-- Pop-up Warning -->
    <div id="popup" class="popup">
        <div class="popup-content">
            <span id="popup-message"><?php echo htmlspecialchars($error); ?></span>
            <button onclick="closePopup()">OK</button>
        </div>
    </div>

 <script>
    let container = document.getElementById('container');

    function toggle() {
        container.classList.toggle('sign-in');
        container.classList.toggle('sign-up');
    }

    // Initialize to sign-in by default
    setTimeout(() => {
        if (!container.classList.contains('sign-in') && !container.classList.contains('sign-up')) {
            container.classList.add('sign-in');
        }
    }, 200);

    function closePopup() {
        document.getElementById('popup').style.display = 'none';
    }
    
    // Prevent form submission if there are errors
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[required]');
            let valid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.focus();
                    return;
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill all required fields');
            }
        });
    });

    // Show popup if there's an error
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($error)): ?>
            document.getElementById('popup').style.display = 'flex';
        <?php endif; ?>
    });
</script>
</body>
</html>