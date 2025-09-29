<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamino ETV - Professional Film Production</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
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
                    <a href="login.html" class="nav-link">Login</a>
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
            <img src="https://images.unsplash.com/photo-1518611012118-696072aa579a?w=1920&h=1080&fit=crop&crop=center" alt="Film production studio">
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content">
            <h1 class="hero-title">
                Bringing Stories to <span class="text-cyan">Life</span>
            </h1>
            <p class="hero-subtitle">
                Professional film production, podcasting, and media education at Tamino ETV
            </p>
            <div class="hero-buttons">
                <button class="btn btn-primary">Start Your Project</button>
                <button class="btn btn-secondary">View Our Work</button>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <h2>Our Services</h2>
                <p>From concept to completion, we provide comprehensive media production services</p>
            </div>

            <div class="services-grid">
                <!-- Film Production -->
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1485846234645-a62644f84728?w=400&h=300&fit=crop&crop=center" alt="Film production">
                    </div>
                    <div class="service-content">
                        <h3 class="text-cyan">Film Production</h3>
                        <p>Complete film production services from pre-production to post-production</p>
                    </div>
                </div>

                <!-- Podcasting -->
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1590602176988-66273c2fd55f?w=400&h=300&fit=crop&crop=center" alt="Podcast studio">
                    </div>
                    <div class="service-content">
                        <h3 class="text-pink">Podcasting</h3>
                        <p>Professional podcast recording, editing, and distribution services</p>
                    </div>
                </div>

                <!-- Academy -->
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400&h=300&fit=crop&crop=center" alt="Media academy training">
                    </div>
                    <div class="service-content">
                        <h3 class="text-cyan">Academy</h3>
                        <p>Learn filmmaking, podcasting, and media production from industry experts</p>
                    </div>
                </div>

                <!-- Studio Rental -->
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=400&h=300&fit=crop&crop=center" alt="Professional studio">
                    </div>
                    <div class="service-content">
                        <h3 class="text-pink">Studio Hire</h3>
                        <p>Rent our state-of-the-art studios and equipment for your projects</p>
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
                        Master the art of storytelling with our comprehensive media production courses. Learn from industry
                        professionals and get hands-on experience with professional equipment.
                    </p>
                    <ul class="academy-features">
                        <li><span class="dot cyan"></span>Film Production & Directing</li>
                        <li><span class="dot pink"></span>Podcast Creation & Audio Engineering</li>
                        <li><span class="dot cyan"></span>Video Editing & Post-Production</li>
                        <li><span class="dot pink"></span>Content Strategy & Distribution</li>
                    </ul>
                    <button class="btn btn-pink">Enroll Now</button>
                </div>
                <div class="academy-image">
                    <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop&crop=center" alt="Students learning film production">
                </div>
            </div>
        </div>
    </section>

    <!-- Studio Section -->
    <section id="studio" class="studio">
        <div class="container">
            <div class="section-header">
                <h2>Professional Studios</h2>
                <p>State-of-the-art facilities equipped with the latest technology for your productions</p>
            </div>

            <div class="studio-grid">
                <div class="studio-card">
                    <img src="https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=400&h=300&fit=crop&crop=center" alt="Recording studio">
                    <div class="studio-overlay">
                        <div class="studio-info">
                            <h3>Recording Studio</h3>
                            <p>Professional audio recording</p>
                        </div>
                    </div>
                </div>

                <div class="studio-card">
                    <img src="https://images.unsplash.com/photo-1574717024653-61fd2cf4d44d?w=400&h=300&fit=crop&crop=center" alt="Video production studio">
                    <div class="studio-overlay">
                        <div class="studio-info">
                            <h3>Video Studio</h3>
                            <p>Full video production setup</p>
                        </div>
                    </div>
                </div>

                <div class="studio-card">
                    <img src="https://images.unsplash.com/photo-1551818255-e6e10975bc17?w=400&h=300&fit=crop&crop=center" alt="Edit suite">
                    <div class="studio-overlay">
                        <div class="studio-info">
                            <h3>Edit Suites</h3>
                            <p>Post-production facilities</p>
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
                <p>Ready to bring your vision to life? Let's discuss your project</p>
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

                <form class="contact-form" id="contactForm">
                    <input type="text" placeholder="Your Name" required>
                    <input type="email" placeholder="Your Email" required>
                    <textarea placeholder="Tell us about your project" rows="4" required></textarea>
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
                    <p>Bringing stories to life</p>
                </div>
                <div class="footer-links">
                    <a href="#">Privacy</a>
                    <a href="#">Terms</a>
                    <a href="#">Careers</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Tamino ETV. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
