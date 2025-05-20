<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About CareSphere | AI-Powered Health Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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

        /* Main Content Styles */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #4EA685, #2c8c6e);
            color: white;
            padding: 80px 20px;
            text-align: center;
        }

        .hero h1 {
            font-size: 2.8rem;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 30px;
        }

        /* Section Styling */
        .section {
            margin-bottom: 60px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.2rem;
            color: #2c3e50;
        }

        /* About Section */
        .about-content {
            display: flex;
            gap: 40px;
            align-items: center;
            margin-bottom: 50px;
        }

        .about-text {
            flex: 1;
        }

        .about-image {
            flex: 1;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .about-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s;
        }

        .about-image:hover img {
            transform: scale(1.05);
        }

        /* Features Section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: #4EA685;
            margin-bottom: 20px;
        }

        /* Team Section */
        .team-members {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .team-member {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s;
        }

        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .member-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            border: 4px solid #4EA685;
        }

        .member-name {
            font-size: 1.3rem;
            margin: 10px 0 5px;
            color: #2c3e50;
        }

        .member-role {
            color: #666;
            margin-bottom: 15px;
        }

        .member-social {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .member-social a {
            color: #4EA685;
            font-size: 1.2rem;
            transition: color 0.3s;
        }

        .member-social a:hover {
            color: #3d8b6f;
        }

        /* Contact Section */
        .contact-container {
            display: flex;
            gap: 40px;
            margin-top: 40px;
        }

        .contact-info, .contact-form {
            flex: 1;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .contact-info h3 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .info-icon {
            font-size: 1.3rem;
            color: #4EA685;
            margin-right: 15px;
            margin-top: 3px;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #4EA685;
            color: white;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: #3d8b6f;
            transform: translateY(-3px);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border 0.3s;
        }

        .form-control:focus {
            border-color: #4EA685;
            outline: none;
            box-shadow: 0 0 0 3px rgba(78, 166, 133, 0.2);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background: #4EA685;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .submit-btn:hover {
            background: #3d8b6f;
        }

        /* Footer Styles */
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 2rem;
        }
        .social-links{
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
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

        /* Responsive styles */
        @media (max-width: 992px) {
            .about-content, .contact-container {
                flex-direction: column;
            }
            
            .about-image {
                order: -1;
                margin-bottom: 30px;
                max-width: 500px;
            }
        }

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
            
            .section-title {
                font-size: 1.8rem;
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
                <li><a href="about.php" class="active">About Us</a></li>
                <li class="user-menu">
                    <div class="user-icon" onclick="toggleDropdown()">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="dropdown-content" id="userDropdown">
                        <p>Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></p>
                        <a href="dashboard.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </li>
            </ul>
            <audio id="notification-sound" src="assets/sounds/notification.mp3" preload="auto"></audio>
        </nav>
    </header>

    <section class="hero">
        <h1>About CareSphere</h1>
        <p>Your AI-powered health companion designed to provide personalized healthcare solutions and medication management</p>
    </section>

    <div class="container">
        <!-- About Section -->
        <section class="section">
            <h2 class="section-title">Our Story</h2>
            <div class="about-content">
                <div class="about-text">
                    <h3>Revolutionizing Healthcare with AI</h3>
                    <p>CareSphere was born as a college project with a vision to bridge the gap between technology and healthcare. Our platform combines artificial intelligence with medical expertise to provide accessible health management tools for everyone.</p>
                    
                    <h3>Our Mission</h3>
                    <p>To empower individuals with intelligent health monitoring, personalized medication management, and reliable symptom analysis, making healthcare more proactive and preventive.</p>
                    
                    <h3>Project Highlights</h3>
                    <p>Developed as part of our academic curriculum, CareSphere demonstrates the practical application of web technologies, AI integration, and database management in solving real-world healthcare challenges.</p>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" alt="AI Health Technology">
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="section">
            <h2 class="section-title">Key Features</h2>
            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Smart Reminders</h3>
                    <p>Never miss a medication dose with our intelligent reminder system that sends timely notifications via email and browser alerts.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h3>Symptom Analysis</h3>
                    <p>Our AI-powered symptom checker provides preliminary assessments based on your reported symptoms and medical history.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Health Tracking</h3>
                    <p>Monitor your health trends over time with our comprehensive dashboard and visual analytics.</p>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="section">
            <h2 class="section-title">Our Team</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 40px;">Meet the talented students behind CareSphere - a group of passionate developers and healthcare enthusiasts from our college.</p>
            
            <div class="team-members">
                <div class="team-member">
                    <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Amal Varghese" class="member-image">
                    <h4 class="member-name">Amal Varghese</h4>
                    <p class="member-role">Project Lead & Backend Developer</p>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="team-member">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Anaswara Sureshbabu" class="member-image">
                    <h4 class="member-name">Anaswara Sureshbabu</h4>
                    <p class="member-role">Frontend Developer</p>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="team-member">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Vyas Angre" class="member-image">
                    <h4 class="member-name">Vyas Angre</h4>
                    <p class="member-role">UI/UX Designer</p>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="team-member">
                    <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Arjun Manoj" class="member-image">
                    <h4 class="member-name">Arjun Manoj</h4>
                    <p class="member-role">Database Specialist</p>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                </div>
                <div class="team-member">
                    <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Manasa Premnathan" class="member-image">
                    <h4 class="member-name">Manasa Premnathan</h4>
                    <p class="member-role">Content & Research</p>
                    <div class="member-social">
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="section">
            <h2 class="section-title">Get In Touch</h2>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    <p>Have questions about our project or interested in collaboration? Reach out to our team!</p>
                    
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt info-icon"></i>
                        <div>
                            <h4>Location</h4>
                            <p>Department of Computer Science<br>Pillai College of Engineering<br>New Panvel, Navi Mumbai</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-envelope info-icon"></i>
                        <div>
                            <h4>Email</h4>
                            <p>tm5548793@gmail.com</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-phone info-icon"></i>
                        <div>
                            <h4>Phone</h4>
                            <p>+91 xxxxx xxxxx</p>
                        </div>
                    </div>
                    
                    <h4 style="margin-top: 30px;">Follow Our Project</h4>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                
                <div class="contact-form">
    <h3>Send Us a Message</h3>
     <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
            <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>
    <form id="contactForm" method="POST" action="contact_form_handler.php">
        <div class="form-group">
            <label for="name">Your Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" class="form-control">
        </div>
        <div class="form-group">
            <label for="message">Your Message</label>
            <textarea id="message" name="message" class="form-control" required></textarea>
        </div>
        <button type="submit" class="submit-btn" name="send_message">
            <i class="fas fa-paper-plane"></i> Send Message
        </button>
    </form>
</div>
            </div>
        </section>
    </div>



    <footer>
        <p>&copy; 2023 CareSphere. All rights reserved.</p>
        <div class="social-links">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
    </footer>

<script>
    // Toggle user dropdown
    function toggleDropdown() {
        document.getElementById("userDropdown").classList.toggle("show");
    }

    // Close dropdown if clicked outside
    window.onclick = function(event) {
        if (!event.target.matches('.user-icon') && !event.target.matches('.user-icon *')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }

    // Remove the form submit handler since we're using PHP processing
    // The form will submit to contact_form_handler.php
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