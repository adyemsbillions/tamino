<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamino ETV - Professional Podcasting</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: #1A1A1A;
            color: #FFFFFF;
        }

        /* Error/Success Message */
        .message {
            display: <?php echo isset($_GET['error']) || isset($_GET['success']) ? 'block' : 'none';
                        ?>;
            background: <?php echo isset($_GET['error']) ? '#FF6200' : '#FFC107';
                        ?>;
            color: #FFFFFF;
            padding: 10px;
            text-align: center;
            font-size: 16px;
            position: fixed;
            width: 100%;
            top: 60px;
            z-index: 1000;
        }

        /* Navigation */
        .navbar {
            background: #1A1A1A;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo h1 {
            color: #FFC107;
            font-size: 28px;
            font-weight: 700;
        }

        .nav-menu {
            display: flex;
            gap: 20px;
        }

        .nav-link {
            color: #FFFFFF;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #FF6200;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .hamburger span {
            background: #FFC107;
            height: 3px;
            width: 25px;
            margin: 4px 0;
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .nav-menu {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 60px;
                left: 0;
                width: 100%;
                background: #1A1A1A;
                padding: 20px;
            }

            .nav-menu.active {
                display: flex;
            }

            .hamburger {
                display: flex;
            }
        }

        /* Hero Section */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #1A1A1A;
            /* Fallback background */
        }

        .hero-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero-title span {
            color: #FF6200;
        }

        .hero-subtitle {
            font-size: 18px;
            color: #FFFFFF;
            max-width: 600px;
            margin: 0 auto 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FF6200, #FFC107);
            color: #FFFFFF;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(255, 98, 0, 0.4);
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid #FF6200;
            color: #FF6200;
        }

        .btn-secondary:hover {
            background: #FF6200;
            color: #FFFFFF;
            transform: translateY(-3px);
        }

        /* Services Section */
        .services {
            padding: 80px 20px;
            background: #FFFFFF;
            color: #1A1A1A;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-header h2 {
            font-size: 36px;
            font-weight: 700;
            color: #1A1A1A;
        }

        .section-header p {
            font-size: 16px;
            color: #666666;
            margin-top: 10px;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .service-card {
            background: #F9F9F9;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .service-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .service-content {
            padding: 20px;
        }

        .service-content h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #FF6200;
        }

        .service-content p {
            font-size: 14px;
            color: #666666;
        }

        /* Academy Section */
        .academy {
            padding: 80px 20px;
            background: #1A1A1A;
        }

        .academy-grid {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .academy-content {
            flex: 1;
        }

        .academy-content h2 {
            font-size: 36px;
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 20px;
        }

        .academy-description {
            font-size: 16px;
            color: #CCCCCC;
            margin-bottom: 20px;
        }

        .academy-features {
            list-style: none;
            margin-bottom: 20px;
        }

        .academy-features li {
            font-size: 16px;
            color: #FFFFFF;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .academy-features .dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .dot.cyan {
            background: #FF6200;
        }

        .dot.pink {
            background: #FFC107;
        }

        .btn-pink {
            background: #FF6200;
            color: #FFFFFF;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
        }

        .btn-pink:hover {
            background: #FFC107;
            transform: translateY(-3px);
        }

        .academy-image {
            flex: 1;
        }

        .academy-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .academy-grid {
                flex-direction: column;
            }

            .academy-image {
                order: -1;
            }
        }

        /* Studio Section */
        .studio {
            padding: 80px 20px;
            background: #FFFFFF;
            color: #1A1A1A;
        }

        .studio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .studio-card {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
        }

        .studio-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .studio-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .studio-card:hover .studio-overlay {
            opacity: 1;
        }

        .studio-info {
            text-align: center;
            color: #FFFFFF;
        }

        .studio-info h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .studio-info p {
            font-size: 14px;
        }

        /* Contact Section */
        .contact {
            padding: 80px 20px;
            background: #1A1A1A;
        }

        .contact-grid {
            display: flex;
            gap: 40px;
        }

        .contact-info {
            flex: 1;
        }

        .contact-info h3 {
            font-size: 24px;
            color: #FFFFFF;
            margin-bottom: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 16px;
            color: #CCCCCC;
        }

        .contact-icon {
            font-size: 20px;
            margin-right: 10px;
        }

        .contact-icon.cyan {
            color: #FF6200;
        }

        .contact-icon.pink {
            color: #FFC107;
        }

        .contact-form {
            flex: 1;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            font-size: 16px;
            background: #F9F9F9;
            color: #1A1A1A;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: #FF6200;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #FF6200, #FFC107);
            color: #FFFFFF;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
        }

        .btn-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(255, 98, 0, 0.4);
        }

        @media (max-width: 768px) {
            .contact-grid {
                flex-direction: column;
            }
        }

        /* Footer */
        .footer {
            background: #1A1A1A;
            padding: 40px 20px;
            color: #CCCCCC;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-brand h3 {
            font-size: 24px;
            color: #FFC107;
        }

        .footer-brand p {
            font-size: 14px;
            color: #CCCCCC;
        }

        .footer-links {
            display: flex;
            gap: 20px;
        }

        .footer-links a {
            color: #FFFFFF;
            text-decoration: none;
            font-size: 14px;
        }

        .footer-links a:hover {
            color: #FF6200;
        }

        .footer-bottom {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .footer-content {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Error/Success Message -->
    <div class="message">
        <?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error']) : (isset($_GET['success']) ? htmlspecialchars($_GET['success']) : ''); ?>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-content">
                <div class="nav-logo">
                    <h1>TAMINO ETV</h1>
                </div>
                <div class="nav-menu" id="navMenu">
                    <a href="#services" class="nav-link">Services</a>
                    <a href="#academy" class="nav-link">Academy</a>
                    <a href="#studio" class="nav-link">Studio</a>
                    <a href="#contact" class="nav-link">Contact</a>
                    <a href="staff_login.php" class="nav-link">Staff Login</a>
                    <a href="staff_signup.php" class="nav-link">Staff Signup</a>
                    <a href="admin_login.php" class="nav-link">Admin Login</a>
                    <a href="host_login.php" class="nav-link">Host Login</a>
                </div>
                <div class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg">
            <img src="https://images.unsplash.com/photo-1478737270239-2f02b77fc618?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                alt="Podcast recording studio">
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content">
            <h1 class="hero-title">
                Amplify Your <span class="text-cyan">Voice</span>
            </h1>
            <p class="hero-subtitle">
                Professional podcasting and media education at Tamino ETV
            </p>
            <div class="hero-buttons">
                <a href="#contact" class="btn btn-primary">Start Podcast</a>
                <a href="#services" class="btn btn-secondary">View Services</a>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <h2>Our Services</h2>
                <p>From concept to distribution, we provide comprehensive podcasting services</p>
            </div>

            <div class="services-grid">
                <!-- Podcasting -->
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1581368135153-a506cf13b1e1?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTZ8fHBvZGNhc3R8ZW58MHx8MHx8fDA%3D"
                            alt="Podcast studio">
                    </div>
                    <div class="service-content">
                        <h3 class="text-cyan">Podcasting</h3>
                        <p>Professional podcast recording, editing, and distribution services</p>
                    </div>
                </div>

                <!-- Academy -->
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1556761175-129418cb2dfe?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8cG9kY2FzdHxlbnwwfHwwfHx8MA%3D%3D"
                            alt="Podcast academy training">
                    </div>
                    <div class="service-content">
                        <h3 class="text-cyan">Academy</h3>
                        <p>Learn podcasting and audio production from industry experts</p>
                    </div>
                </div>

                <!-- Studio Rental -->
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1589903308904-1010c2294adc?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NHx8cG9kY2FzdHxlbnwwfHwwfHx8MA%3D%3D"
                            alt="Professional podcast studio">
                    </div>
                    <div class="service-content">
                        <h3 class="text-cyan">Studio Hire</h3>
                        <p>Rent our state-of-the-art podcast studios and equipment</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Academy Section -->
    <section id="academy" class="academy">
        <div class="container">
            <div class="academy-grid">
                <div class="academy-content">
                    <h2>Tamino ETV Academy</h2>
                    <p class="academy-description">
                        Master the art of podcasting with our comprehensive audio production courses. Learn from
                        industry professionals and get hands-on experience with professional equipment.
                    </p>
                    <ul class="academy-features">
                        <li><span class="dot cyan"></span>Podcast Creation & Audio Engineering</li>
                        <li><span class="dot pink"></span>Content Strategy & Distribution</li>
                        <li><span class="dot cyan"></span>Audio Editing & Post-Production</li>
                        <li><span class="dot pink"></span>Voice Training & Presentation</li>
                    </ul>
                    <a href="#contact" class="btn btn-pink">Enroll Now</a>
                </div>
                <div class="academy-image">
                    <img src="https://images.unsplash.com/photo-1590602846989-e99596d2a6ee?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTh8fHBvZGNhc3R8ZW58MHx8MHx8fDA%3D"
                        alt="Students learning podcast production">
                </div>
            </div>
        </div>
    </section>

    <!-- Studio Section -->
    <section id="studio" class="studio">
        <div class="container">
            <div class="section-header">
                <h2>Professional Studios</h2>
                <p>State-of-the-art facilities equipped for podcast production</p>
            </div>

            <div class="studio-grid">
                <div class="studio-card">
                    <img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=400&h=300&fit=crop&crop=center"
                        alt="Podcast recording studio">
                    <div class="studio-overlay">
                        <div class="studio-info">
                            <h3>Recording Studio</h3>
                            <p>Professional audio recording for podcasts</p>
                        </div>
                    </div>
                </div>

                <div class="studio-card">
                    <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=300&fit=crop&crop=center"
                        alt="Podcast production setup">
                    <div class="studio-overlay">
                        <div class="studio-info">
                            <h3>Production Studio</h3>
                            <p>Full podcast production setup</p>
                        </div>
                    </div>
                </div>

                <div class="studio-card">
                    <img src="https://images.unsplash.com/photo-1593693397690-362cb9666fc2?w=400&h=300&fit=crop&crop=center"
                        alt="Podcast edit suite">
                    <div class="studio-overlay">
                        <div class="studio-info">
                            <h3>Edit Suites</h3>
                            <p>Post-production for audio content</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2>Get In Touch</h2>
                <p>Ready to start your podcast? Let's discuss your project</p>
            </div>

            <div class="contact-grid">
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    <div class="contact-item">
                        <span class="contact-icon cyan">📧</span>
                        <span>info@taminoetv.com</span>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon pink">📞</span>
                        <span>+1 (555) 123-4567</span>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon cyan">📍</span>
                        <span>123 Media Street, Production City</span>
                    </div>
                </div>

                <form class="contact-form" action="contact.php" method="POST">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <textarea name="message" placeholder="Tell us about your project" rows="4" required></textarea>
                    <button type="submit" class="btn btn-gradient">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <h3>TAMINO ETV</h3>
                    <p>Amplifying your voice</p>
                </div>
                <div class="footer-links">
                    <a href="#">Privacy</a>
                    <a href="#">Terms</a>
                    <a href="#">Careers</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Tamino ETV. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Hamburger menu toggle
        document.getElementById('hamburger').addEventListener('click', () => {
            document.getElementById('navMenu').classList.toggle('active');
        });
    </script>
</body>

</html>