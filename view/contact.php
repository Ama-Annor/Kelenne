<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Kelenne Car Wash</title>
    <link rel="stylesheet" href="../assets/css/contact.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Add AOS library for scroll animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
</head>
<body>
<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-content">
        <div class="contact">
            <span>📞 Call Us: +233 46493 8388</span>
        </div>
        <div class="social-icons">
            <a href="#" aria-label="Instagram"><i class='bx bxl-instagram'></i></a>
            <a href="#" aria-label="Twitter"><i class='bx bxl-twitter'></i></a>
            <a href="#" aria-label="Facebook"><i class='bx bxl-facebook'></i></a>
            <a href="#" aria-label="YouTube"><i class='bx bxl-youtube'></i></a>
        </div>
    </div>
</div>

<!-- Header -->
<header class="header">
    <div class="nav-container">
        <div class="logo">KELENNE<span> CAR WASH</span></div>
        <nav>
        <a href="../index.html">Home</a>
                <a href="services.html">Services</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
                <a href="testimonials.php">Testimonials</a>
                <a href="blog.html">Blog</a>
                <a href="locations.html">Locations</a>
                <a href="login.html">Login</a>
                <a href="signup.html">Register</a>
        </nav>
    </div>
</header>

<!-- Contact Hero Section -->
<section class="contact-hero">
    <div class="contact-hero-content" data-aos="fade-up">
        <h1>Get In Touch</h1>
        <p>We're here to help and answer any questions you might have</p>
    </div>
</section>

<!-- Main Contact Section -->
<section class="contact-section">
    <div class="container">
        <!-- Contact Information Cards -->
        <div class="contact-info">
            <div class="info-card" data-aos="fade-right">
                <i class='bx bx-map'></i>
                <h3>Visit Us</h3>
                <p>26 Nkorman ansah estate,<br>GRA Abia state</p>
            </div>
            <div class="info-card" data-aos="fade-up">
                <i class='bx bx-phone'></i>
                <h3>Call Us</h3>
                <p>+234 890 3900 342<br>+233 46493 8388</p>
            </div>
            <div class="info-card" data-aos="fade-left">
                <i class='bx bx-envelope'></i>
                <h3>Email Us</h3>
                <p>info@kelennecarwash.com<br>support@kelennecarwash.com</p>
            </div>
        </div>

        <!-- Contact Form and Map -->
        <div class="contact-content">
            <div class="form-container" data-aos="fade-right">
                <h2>Send Us a Message</h2>
                <form id="contactForm" class="contact-form" action="../actions/process_contact.php" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                        <div class="input-focus-effect"></div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                        <div class="input-focus-effect"></div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                        <div class="input-focus-effect"></div>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                        <div class="input-focus-effect"></div>
                    </div>
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                        <div class="input-focus-effect"></div>
                    </div>
                    <button type="submit" class="submit-btn">
                        <span>Send Message</span>
                        <i class='bx bx-send'></i>
                    </button>
                </form>
            </div>

            <div class="map-container" data-aos="fade-left">
                <div class="map-wrapper">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3969.6718347877504!2d-0.22249812552325315!3d5.760280531562755!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xfdf767731dfa583%3A0xc30b0f51f3b91add!2sAshesi%20University!5e0!3m2!1sen!2sgh!4v1732372936356!5m2!1sen!2sgh" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        width="100%"
                        height="450"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Business Hours Section -->
<section class="business-hours">
    <div class="container">
        <h2 data-aos="fade-up">Business Hours</h2>
        <div class="hours-grid">
            <div class="hours-card" data-aos="fade-up" data-aos-delay="100">
                <h3>Weekdays</h3>
                <p>Monday - Friday</p>
                <span>8:00 AM - 6:00 PM</span>
            </div>
            <div class="hours-card" data-aos="fade-up" data-aos-delay="200">
                <h3>Weekends</h3>
                <p>Saturday - Sunday</p>
                <span>9:00 AM - 5:00 PM</span>
            </div>
            <div class="hours-card" data-aos="fade-up" data-aos-delay="300">
                <h3>Holidays</h3>
                <p>Public Holidays</p>
                <span>10:00 AM - 4:00 PM</span>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-section">
                <h3>About Kelenne Car Wash</h3>
                <p>Professional car washing services committed to excellence and customer satisfaction.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Services</a></li>
                    <li><a href="#">Pricing</a></li>
                    <li><a href="#">Book Now</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>📍 26 Nkorman ansah estate, GRA Abia state</p>
                <p>📞 (234) 890 3900 342</p>
                <p>✉️ info@kelennecarwash.com</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="footer-social">
                    <a href="#"><i class='bx bxl-instagram'></i></a>
                    <a href="#"><i class='bx bxl-twitter'></i></a>
                    <a href="#"><i class='bx bxl-facebook'></i></a>
                    <a href="#"><i class='bx bxl-youtube'></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Kelenne Car Wash. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="../assets/js/contact.js"></script>
<script>
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(this);

        // Submit form using fetch
        fetch('../actions/process_contact.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Show success message
                    alert(data.message);
                    // Reset form
                    document.getElementById('contactForm').reset();
                } else {
                    // Show error message
                    alert(data.message);
                }
            })
    });
</script>
</body>
</html>
