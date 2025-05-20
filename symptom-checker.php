<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Symptom Checker - AI Health Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="notification-system.js"></script>
<link rel="stylesheet" href="notification-styles.css">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">

    <style>
        /* General Styles */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: #333;
        }
        main {
    padding-bottom: 80px; /* Adjust based on your footer height */
}

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

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4EA685;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }

        .btn:hover {
            background: #45a049;
        }

        .symptom-checker {
            padding: 40px 20px;
            text-align: center;
        }

        .symptom-checker h1 {
            margin-bottom: 20px;
        }

        .symptom-checker form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .symptom-checker label {
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .symptom-checker input,
        .symptom-checker select,
        .symptom-checker textarea {
            width: 100%;
            max-width: 400px;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .symptom-checker button {
            cursor: pointer;
        }

        #result {
            margin-top: 20px;
            font-size: 1.1rem;
            text-align: left;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .loading {
            display: none;
            margin-top: 20px;
            font-size: 1.2rem;
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

       footer {
    bottom: 0;
    left: 0;
    right: 0;
    background: #333;
    color: white;
    text-align: center;
    padding: 20px 0;
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
        
        #result h3 {
            color: #4EA685;
            margin-top: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
        }

        #result ul {
            padding-left: 20px;
            margin-bottom: 15px;
        }

        #result li {
            margin-bottom: 8px;
        }

        .warning {
            color: #d9534f;
            font-weight: bold;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        /* Responsive styles */
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
                        <p>Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></p>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="dashboard.php"><i class="fas fa-user"></i> My Profile</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        <?php else: ?>
                            <a href="login-signup.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <?php endif; ?>
                    </div>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="symptom-checker">
            <h1>Symptom Checker</h1>
            <form id="symptom-form">
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" required min="1" max="120">

                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>

                <label for="symptoms">Describe Your Symptoms:</label>
                <textarea id="symptoms" name="symptoms" rows="4" required minlength="10"></textarea>

                <button type="submit" class="btn">Check Symptoms</button>
            </form>
            <div class="loading" id="loading">Analyzing symptoms... <i class="fas fa-spinner fa-spin"></i></div>
            <div id="result"></div>
        </section>
    </main>

  

    <footer>
        <p>&copy; 2023 AI Health Assistant. All rights reserved.</p>
        <div class="social-links">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
    </footer>

    <script>
        // Dropdown functionality
        function toggleDropdown() {
            document.getElementById("userDropdown").classList.toggle("show");
        }
        
        // Close dropdown if clicked outside
        window.onclick = function(e) {
            if (!e.target.matches('.user-icon') && !e.target.matches('.user-icon *')) {
                var dropdown = document.getElementById("userDropdown");
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }

        document.getElementById('symptom-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const age = document.getElementById('age').value;
            const gender = document.getElementById('gender').value;
            const symptoms = document.getElementById('symptoms').value;
            
            // UI updates
            document.getElementById('loading').style.display = 'block';
            document.getElementById('result').innerHTML = '';
            
            try {
                const response = await fetch('proxy.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        age: parseInt(age),
                        gender: gender,
                        symptoms: symptoms
                    })
                });
                
                // First check if response is OK
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server Error:', errorText);
                    throw new Error(`Server error (HTTP ${response.status})`);
                }
                
                // Try to parse JSON
                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    console.error('JSON Parse Error:', e);
                    throw new Error('Invalid server response format');
                }
                
                // Check if we got valid data
                if (data.choices?.[0]?.message?.content) {
                    document.getElementById('result').innerHTML = data.choices[0].message.content;
                    
                    // Save to database (optional)
                    try {
                        await fetch('save_response.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                age: age,
                                gender: gender,
                                symptoms: symptoms,
                                result: data.choices[0].message.content
                            })
                        });
                    } catch (saveError) {
                        console.error('Save error:', saveError);
                    }
                } else {
                    throw new Error('Unexpected response structure');
                }
                
            } catch (error) {
                console.error('Full Error:', error);
                showError(error.message);
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        });

        function showError(message) {
            const errorHtml = `
                <div class="error">
                    <h3>Error</h3>
                    <p>${message.replace(/<[^>]*>/g, '').substring(0, 500)}</p>
                    <p>Please try again later.</p>
                </div>`;
            document.getElementById('result').innerHTML = errorHtml;
        }
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