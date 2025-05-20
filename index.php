<?php
session_start();


// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login-signup.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareSphere</title>
    <!-- Add Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="assets/js/notification.js"></script>
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="assets/css/notifications.css">
    <link rel="stylesheet" href="style.css">

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
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
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

        .hero {
            background-image: url('banner.jpg');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
        }

        .hero-content {
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 10px;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4EA685;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background: #45a049;
        }

        .features {
            padding: 40px 20px;
            text-align: center;
        }

        .features h2 {
            margin-bottom: 20px;
        }

        .feature-cards {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .card {
            background: #f4f4f4;
            padding: 20px;
            border-radius: 10px;
            width: 30%;
            margin: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .news-grid {
            padding: 40px 20px;
            text-align: center;
        }

        .news-grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .news-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .news-card:hover {
            transform: translateY(-5px);
        }

        .news-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .news-card h3 {
            margin-bottom: 10px;
            color: #4EA685;
        }

        .news-card p {
            font-size: 0.9rem;
            color: #555;
        }

        .news-card a {
            color: #4EA685;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }

        .news-card a:hover {
            text-decoration: underline;
        }

        .testimonials {
            padding: 40px 20px;
            text-align: center;
        }

        .testimonial-cards {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .testimonial {
            background: #f4f4f4;
            padding: 20px;
            border-radius: 10px;
            width: 45%;
            margin: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .cta {
            background: #4EA685;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .cta .btn {
            background: white;
            color: #4EA685;
        }

        .cta .btn:hover {
            background: #f1f1f1;
        }

        .newsletter {
            padding: 40px 20px;
            text-align: center;
            background: #f4f4f4;
        }

        .newsletter input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
        }

        footer {
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

            .feature-cards,
            .testimonial-cards {
                flex-direction: column;
            }

            .card,
            .testimonial {
                width: 100%;
                margin: 10px 0;
            }

            .hero {
                height: 300px;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .newsletter input {
                width: 100%;
                margin-right: 0;
                margin-bottom: 10px;
            }
        }

        /* Testimonials Section */
        .testimonials {
            padding: 60px 20px;
            background: #f9fbfd;
            position: relative;
            overflow: hidden;
        }

        .testimonials::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%234EA685" fill-opacity="0.05" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: cover;
            background-position: bottom;
            z-index: 0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: #4EA685;
            border-radius: 3px;
        }

        .section-subtitle {
            color: #7f8c8d;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Review Form */
        .review-form-container {
            max-width: 800px;
            margin: 0 auto 50px;
            position: relative;
            z-index: 1;
        }

        .review-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .review-form:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        .review-form h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 25px;
            position: relative;
        }

        .review-form h3::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 2px;
            background: #4EA685;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid #e0e6ed;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s;
            min-height: 120px;
            resize: vertical;
        }

        .form-control:focus {
            border-color: #4EA685;
            box-shadow: 0 0 0 3px rgba(78, 166, 133, 0.2);
            outline: none;
        }

        .rating-label {
            display: block;
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 500;
        }

        .rating-stars {
            direction: rtl;
            unicode-bidi: bidi-override;
            display: inline-block;
        }

        .rating-stars input {
            display: none;
        }

        .rating-stars label {
            font-size: 28px;
            color: #ddd;
            cursor: pointer;
            padding: 0 3px;
            transition: all 0.2s;
        }

        .rating-stars input:checked~label,
        .rating-stars label:hover,
        .rating-stars label:hover~label {
            color: #ffc107;
            transform: scale(1.1);
        }

        .rating-stars input:checked+label {
            color: #ffc107;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            background: #4EA685;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 15px;
        }

        .btn-submit i {
            margin-right: 8px;
        }

        .btn-submit:hover {
            background: #3d8b6f;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 166, 133, 0.3);
        }

        /* Testimonial Cards */
        .testimonial-carousel {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin: 0 auto;
            max-width: 1200px;
            position: relative;
            z-index: 1;
        }

        .testimonial-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }

        .testimonial-card::before {
            content: "" ";
 position: absolute;
            top: 20px;
            left: 20px;
            font-size: 80px;
            color: rgba(78, 166, 133, 0.1);
            font-family: Georgia, serif;
            line-height: 1;
        }

        .testimonial-card::after {
            content: "" ";
 position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 80px;
            color: rgba(78, 166, 133, 0.1);
            font-family: Georgia, serif;
            line-height: 1;
            transform: rotate(180deg);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: #4EA685;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .user-info h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }

        .review-rating {
            color: #ffc107;
            font-size: 1rem;
            letter-spacing: 2px;
        }

        .review-text {
            color: #555;
            line-height: 1.6;
            margin: 0 0 15px 0;
            font-style: italic;
            position: relative;
            z-index: 1;
        }

        .card-footer {
            display: flex;
            justify-content: flex-end;
        }

        .review-date {
            color: #95a5a6;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }

        .review-date i {
            margin-right: 5px;
            font-size: 0.9rem;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .testimonial-carousel {
                grid-template-columns: 1fr;
            }

            .review-form {
                padding: 20px;
            }

            .section-header h2 {
                font-size: 2rem;
            }
        }

        /* Nearby Hospitals Section - Free Version */
        .nearby-hospitals {
            padding: 60px 20px;
            background: #f5f9fc;
        }

        .map-container {
            height: 500px;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: relative;
        }

        #hospitalMap {
            height: 100%;
            width: 100%;
        }

        .map-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
        }

        .map-btn {
            background: #4EA685;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .hospital-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .hospital-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .loading-text {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        /* Add to your CSS */
        .location-error {
            padding: 10px;
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            max-width: 300px;
        }

        .location-error i {
            margin-right: 5px;
            color: #dc3545;
        }

        .location-error ul {
            padding-left: 20px;
            margin: 10px 0;
        }

        .location-error p {
            margin: 5px 0;
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
                        <p>Welcome,
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </p>
                        <a href="dashboard.php"><i class="fas fa-user"></i> My Profile</a>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </li>
            </ul>
            <audio id="notification-sound" src="assets/sounds/notification.mp3" preload="auto"></audio>

            <script>
                function playNotificationSound() {
                    document.getElementById('notification-sound').play();
                }

                // When receiving a notification
                function showNotification(title, message) {
                    playNotificationSound();

                    // Show visual notification
                    if (Notification.permission === 'granted') {
                        new Notification(title, { body: message });
                    } else if (Notification.permission !== 'denied') {
                        Notification.requestPermission().then(permission => {
                            if (permission === 'granted') {
                                new Notification(title, { body: message });
                            }
                        });
                    }

                    // Add to notification center
                    addToNotificationCenter(title, message);
                }
            </script>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Welcome to Your AI Health Assistant</h1>
                <p>Your personal guide to better health and wellness.</p>
                <a href="symptom-checker.php" class="btn">Check Symptoms</a>
                <a href="recommendations.php" class="btn">Get Recommendations</a>
            </div>
        </section>

        <!-- News/Blog Grid Section -->
        <section class="news-grid">
            <h2>Health News & Blogs</h2>
            <div class="news-grid-container">
                <!-- News cards will be dynamically inserted here -->
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials">
            <div class="section-header">
                <h2>What Our Users Say</h2>
                <p class="section-subtitle">Hear from our community of health-conscious users</p>
            </div>

            <!-- Review Form -->
            <div class="review-form-container">
                <div class="review-form">
                    <h3>Share Your Experience</h3>
                    <form id="reviewForm" method="POST" action="submit_review.php">
                        <div class="form-group">
                            <textarea id="reviewText" name="review_text" class="form-control"
                                placeholder="Tell us about your experience..." required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="rating-label">Rate your experience:</label>
                            <div class="rating-stars">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                                <label for="star<?= $i ?>" title="<?= $i ?> star">★</label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Submit Review
                        </button>
                    </form>
                </div>
            </div>

            <!-- Display Reviews -->
            <div class="testimonial-carousel" id="reviewsContainer">
                <?php
        require_once __DIR__ . '/includes/db_connection.php';
        $stmt = $pdo->query("SELECT username, review_text, rating, created_at FROM user_reviews ORDER BY created_at DESC LIMIT 6");
        while ($review = $stmt->fetch()):
        ?>
                <div class="testimonial-card">
                    <div class="card-header">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                        </div>
                        <div class="user-info">
                            <h4>
                                <?php echo htmlspecialchars($review['username']); ?>
                            </h4>
                            <div class="review-rating">
                                <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                ★
                                <?php endfor; ?>
                                <?php for ($i = $review['rating']; $i < 5; $i++): ?>
                                ☆
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="review-text">"
                            <?php echo htmlspecialchars($review['review_text']); ?>"
                        </p>
                    </div>
                    <div class="card-footer">
                        <small class="review-date">
                            <i class="far fa-calendar-alt"></i>
                            <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                        </small>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
        <!-- Nearby Hospitals Section -->
        <section class="nearby-hospitals">
            <div class="section-header">
                <h2>Nearby Healthcare Facilities</h2>
                <p class="section-subtitle">Find hospitals near your location</p>
            </div>

            <div class="map-container">
                <!-- Map will load here -->
                <div id="hospitalMap"></div>

                <div class="map-controls">
                    <button id="locateMeBtn" class="map-btn">
                        <i class="fas fa-location-arrow"></i> Use My Location
                    </button>
                </div>
            </div>

            <div class="hospital-list" id="hospitalList">
                <!-- Hospitals will appear here -->
                <div class="loading-text">Loading hospitals...</div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta">
            <h2>Start Your Health Journey Today</h2>
            <p>Join thousands of users who are improving their health with our AI-powered assistant.</p>
            <a href="chatbot.php" class="btn">Get Started</a>
        </section>

        <!-- Newsletter Section -->
        <section class="newsletter">
            <h2>Subscribe to Our Newsletter</h2>
            <form id="newsletter-form">
                <input type="email" placeholder="Enter your email" required>
                <button type="submit" class="btn">Subscribe</button>
            </form>
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
    <script src="https://cdn.botpress.cloud/webchat/v2.2/inject.js"></script>
    <script>
        // Dropdown functionality
        function toggleDropdown() {
            document.getElementById("userDropdown").classList.toggle("show");
        }

        // Close dropdown if clicked outside
        window.onclick = function (e) {
            if (!e.target.matches('.user-icon') && !e.target.matches('.user-icon *')) {
                var dropdown = document.getElementById("userDropdown");
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }

        // News API functionality
        const apiKey = 'a9cfd1f1ce6544409b3a51efe9efa888';
        const newsContainer = document.querySelector('.news-grid-container');

        function fetchHealthNews() {
            fetch(`https://newsapi.org/v2/top-headlines?category=health&apiKey=${apiKey}`)
                .then(response => response.json())
                .then(data => {
                    if (data.articles) {
                        const articlesWithImages = data.articles.filter(article => article.urlToImage);
                        displayNews(articlesWithImages);
                    }
                })
                .catch(error => console.error('Error fetching news:', error));
        }

        function displayNews(articles) {
            newsContainer.innerHTML = '';
            articles.slice(0, 8).forEach(article => {
                const newsCard = document.createElement('div');
                newsCard.classList.add('news-card');
                newsCard.innerHTML = `
                    <img src="${article.urlToImage}" alt="${article.title}">
                    <h3>${article.title}</h3>
                    <p>${article.description || 'No description available.'}</p>
                    <a href="${article.url}" target="_blank">Read More</a>
                `;
                newsContainer.appendChild(newsCard);
            });
        }

        // Fetch news when the page loads
        fetchHealthNews();

        // Refresh news every 1 minute (60,000 milliseconds)
        setInterval(fetchHealthNews, 60000);

        // Newsletter form submission
        document.getElementById('newsletter-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const email = this.querySelector('input').value;
            alert(`Thank you for subscribing with ${email}! You'll receive our newsletter soon.`);
            this.reset();
        });

        // AJAX form submission for reviews
        document.getElementById('reviewForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('submit_review.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });

        // Display success/error messages
document.addEventListener('DOMContentLoaded', function() {
    <?php if(isset($_SESSION['review_success'])): ?>
        alert('<?php echo addslashes($_SESSION['review_success']); ?>');
        <?php unset($_SESSION['review_success']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['review_error'])): ?>
        alert('<?php echo addslashes($_SESSION['review_error']); ?>');
        <?php unset($_SESSION['review_error']); ?>
    <?php endif; ?>
});




        ///
        // Load Leaflet.js from CDN (free open-source library)

        // Add this at the start of your JavaScript
        document.addEventListener('DOMContentLoaded', function () {
            if (!navigator.geolocation) {
                console.warn("Geolocation is not supported by this browser");
                // Show a permanent notice in the UI
                document.getElementById('locateMeBtn').style.display = 'none';
                document.querySelector('.map-controls').insertAdjacentHTML('beforeend',
                    '<div class="geo-warning">Geolocation not supported in your browser</div>'
                );
            }
        });
        const leafletCSS = document.createElement('link');
        leafletCSS.rel = 'stylesheet';
        leafletCSS.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(leafletCSS);

        const leafletJS = document.createElement('script');
        leafletJS.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        leafletJS.onload = initHospitalMap;
        document.head.appendChild(leafletJS);

        // Main map function
        function initHospitalMap() {
            // Create map (centered on a default location)
            const map = L.map('hospitalMap').setView([19.0760, 72.8777], 13); // Default to Mumbai

            // Use OpenStreetMap tiles (free)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Custom hospital icon
            const hospitalIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34]
            });

            // Locate button functionality
            document.getElementById('locateMeBtn').addEventListener('click', function () {
                map.locate({ setView: true, maxZoom: 14 });
            });

            // When location found
            map.on('locationfound', function (e) {
                findNearbyHospitals(e.latlng, map);
            });

            // If location error
            map.on('locationerror', function (e) {
                console.error("Geolocation error:", e.message);

                // Show a more user-friendly message
                const userFriendlyMessage = `
        <div class="location-error">
            <p><i class="fas fa-exclamation-triangle"></i> Couldn't access your location.</p>
            <p>Possible reasons:</p>
            <ul>
                <li>Location permissions are blocked</li>
                <li>Your device/browser doesn't support geolocation</li>
                <li>You're using a VPN or private browsing mode</li>
            </ul>
            <p>Showing default area instead.</p>
        </div>
    `;

                // Create a custom popup instead of alert()
                L.popup()
                    .setLatLng(map.getCenter())
                    .setContent(userFriendlyMessage)
                    .openOn(map);

                // Still load hospitals for default area
                findNearbyHospitals(map.getCenter(), map);
            });
        }

        // Find hospitals using Overpass API (free)
        function findNearbyHospitals(latlng, map) {
            const radius = 5000; // 5km radius
            const hospitalList = document.getElementById('hospitalList');
            hospitalList.innerHTML = '<div class="loading-text">Finding nearby hospitals...</div>';

            // Clear existing markers
            map.eachLayer(layer => {
                if (layer instanceof L.Marker) {
                    map.removeLayer(layer);
                }
            });

            // Query Overpass API for hospitals
            fetch(`https://overpass-api.de/api/interpreter?data=[out:json];node[amenity=hospital](around:${radius},${latlng.lat},${latlng.lng});out;`)
                .then(response => response.json())
                .then(data => {
                    hospitalList.innerHTML = '';

                    if (data.elements.length === 0) {
                        hospitalList.innerHTML = '<div class="loading-text">No hospitals found in this area.</div>';
                        return;
                    }

                    // Process results
                    data.elements.forEach(hospital => {
                        if (!hospital.tags || !hospital.tags.name) return;

                        // Add marker to map
                        const marker = L.marker([hospital.lat, hospital.lon], { icon: hospitalIcon })
                            .addTo(map)
                            .bindPopup(`<b>${hospital.tags.name}</b><br>${hospital.tags['addr:street'] || ''}`);

                        // Add to list
                        const card = document.createElement('div');
                        card.className = 'hospital-card';
                        card.innerHTML = `
                    <h3>${hospital.tags.name}</h3>
                    ${hospital.tags['addr:street'] ? `<p>${hospital.tags['addr:street']}</p>` : ''}
                    <a href="https://www.openstreetmap.org/node/${hospital.id}" target="_blank" class="map-btn" style="display: inline-block; margin-top: 10px;">
                        <i class="fas fa-map-marker-alt"></i> View on Map
                    </a>
                `;
                        hospitalList.appendChild(card);
                    });
                })
                .catch(error => {
                    hospitalList.innerHTML = '<div class="loading-text">Error loading hospitals. Please try again.</div>';
                    console.error('Error:', error);
                });
        }


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