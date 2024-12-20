<?php

include '../db/database.php';

$result = $conn->query("SELECT COUNT(*) AS total_services FROM services");
$total_services = $result->fetch_assoc()['total_services'];

$total_services = $total_services - 1;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelenne Car Wash - About Us</title>
    <link rel="stylesheet" href="../assets/css/about.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-content">
        <div class="contact">
            <span>📞 Call Us: +233 46493 8388</span>
        </div>
        <div class="social-icons">
            <a href="#"><i class='bx bxl-instagram'></i></a>
            <a href="#"><i class='bx bxl-twitter'></i></a>
            <a href="#"><i class='bx bxl-facebook'></i></a>
            <a href="#"><i class='bx bxl-youtube'></i></a>
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

<!-- About Section -->
<section class="about-section">
    <div class="container">
        <div class="about-image">
            <img src="../assets/images/carwash6.jpg" alt="Kelenne Car Wash Team">
        </div>
        <div class="about-content">
            <h2>About Kelenne Car Wash</h2>
            <p>Kelenne Car Wash is a premier car washing and detailing service provider that has been serving the community for over 15 years. Our commitment to excellence, attention to detail, and use of eco-friendly products have made us the trusted choice for vehicle care in the region.</p>
            <p>At Kelenne, we believe that your car deserves the best possible treatment. Our team of experienced and highly trained professionals are dedicated to providing a seamless and satisfying car washing experience, whether you're looking for a quick express wash or a comprehensive detailing service.</p>
            <p>We take pride in our state-of-the-art facilities, modern equipment, and innovative cleaning techniques. Our goal is to not only clean your car but to also protect its finish and maintain its value over time.</p>
        </div>
    </div>
</section>

<!-- Meet the Team -->
<section class="meet-the-team">
    <div class="container">
        <h2 class="section-title">Meet the Kelenne Car Wash Team</h2>
        <div class="team-grid">
            <div class="team-member">
                <img src="../assets/images/austine.jpeg" alt="Team Member 1">
                <h3>Austine iheji</h3>
                <p>Founder and CEO</p>
            </div>
            <div class="team-member">
                <img src="../assets/images/samuel.jpeg" alt="Team Member 2">
                <h3>Samuel Yussif</h3>
                <p>Operations Manager</p>
            </div>
            <div class="team-member">
                <img src="../assets/images/mcnobert.jpg" alt="Team Member 3">
                <h3>Mc Nobert Amoah</h3>
                <p>Lead Detailer</p>
            </div>
            <div class="team-member">
                <img src="../assets/images/ama.jpg" alt="Team Member 4">
                <h3>Maame Amaa</h3>
                <p>Customer Service Specialist</p>
            </div>
        </div>
    </div>
</section>

<!--How It Works-->
<section class="how-it-works">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <div class="steps-grid">
            <div class="step">
                <div class="step-icon">🚗</div>
                <h3>Send Your Car</h3>
                <p>You can either drop off your car at our facility or schedule an appointment for pickup and delivery.</p>
            </div>
            <div class="step">
                <div class="step-icon">🧹</div>
                <h3>We Clean It</h3>
                <p>Our team of experienced detailers will thoroughly clean and care for your vehicle using eco-friendly products.</p>
            </div>
            <div class="step">
                <div class="step-icon">💳</div>
                <h3>Pay and Collect</h3>
                <p>You can pay online or in cash when you come to collect your freshly cleaned and polished vehicle.</p>
            </div>
        </div>
    </div>
</section>

<!-- Our Numbers -->
<section class="our-numbers">
    <div class="container">
        <h2 class="section-title">Our Numbers</h2>
        <div class="numbers-grid">
            <div class="number-item">
                <h3>2+</h3>
                <p>Years of Experience</p>
            </div>
            <div class="number-item">
                <h3><?php echo htmlspecialchars($total_services). '+'; ?></h3>
                <p>Cars Serviced</p>
            </div>
            <div class="number-item">
                <h3>98%</h3>
                <p>Customer Satisfaction</p>
            </div>
            <div class="number-item">
                <h3>5</h3>
                <p>Industry Awards</p>
            </div>
        </div>
    </div>
</section>

<!-- Partners -->
<section class="partners">
    <div class="container">
        <h2 class="section-title">Our Partners</h2>
        <div class="partners-grid">
            <img src="../assets/images/carlogo1.jpg" alt="Partner 1">
            <img src="../assets/images/carlogo2.jpg" alt="Partner 2">
            <img src="../assets/images/carlogo5.jpeg" alt="Partner 3">
            <img src="../assets/images/carlogo6.jpeg" alt="Partner 4">
        </div>
    </div>
</section>

<!-- Footer -->
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
</body>
</html>
