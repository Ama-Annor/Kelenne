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
    <link rel="stylesheet" href="../assets/css/testimonials.css">
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
                <a href="locations.html">Locations</a>
                <a href="login.html">Login</a>
                <a href="signup.html">Register</a>
        </nav>
    </div>
</header>

<!-- Testimonials Section -->
<section class="testimonials-section">
    <div class="container">
        <h2 class="section-title">What Our Clients Say</h2>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"Best car wash service I've ever used. My car looks brand new every time!"</p>
                    <div class="client-info">
                        <strong>Augustina Iheji</strong>
                        <span>Regular Customer</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"Professional, courteous, and attention to detail. Highly recommended!"</p>
                    <div class="client-info">
                        <strong>Maameeee Amaaa</strong>
                        <span>VIP Client</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"The premium wash package is worth every penny. My car hasn't looked this good since I bought it!"</p>
                    <div class="client-info">
                        <strong>Kwame Mensah</strong>
                        <span>VIP Member</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"Fast service without compromising on quality. The staff is always friendly and professional."</p>
                    <div class="client-info">
                        <strong>Chioma Adebayo</strong>
                        <span>Regular Customer </span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"I manage a fleet of delivery vehicles, and Kelenne keeps them all looking pristine. Excellent service!"</p>
                    <div class="client-info">
                        <strong>Emmanuel Osei</strong>
                        <span>VIP Customer Client</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"The interior detailing is exceptional. They got out stains I thought would never come clean!"</p>
                    <div class="client-info">
                        <strong>Aisha Mohammed</strong>
                        <span>VIP Customer</span>
                    </div>
                </div>
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