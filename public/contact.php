<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$db = Database::getInstance();
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = Functions::sanitize($_POST['name']);
    $email = Functions::sanitize($_POST['email']);
    $subject = Functions::sanitize($_POST['subject']);
    $message = Functions::sanitize($_POST['message']);
    
    // Validate
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Here you would typically send an email or save to database
        // For now, we'll just show success message
        
        // You can add code to save to database if you want
        /*
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $db->query($sql, [$name, $email, $subject, $message]);
        */
        
        $success = 'Thank you for your message! We will get back to you soon.';
        
        // Clear form
        $name = $email = $subject = $message = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Ethiopian E-Book Store</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/responsive.css">
    
    <!-- Ethiopian Flag Favicon -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
    
    <style>
        /* ============================================
           CONTACT PAGE STYLES
           ============================================ */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            color: #2d3436;
            min-height: 100vh;
        }

        /* Header Styles */
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #2d3436;
            font-weight: 500;
            transition: color 0.3s;
            position: relative;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #6c5ce7;
        }

        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #6c5ce7;
            border-radius: 2px;
        }

        /* Contact Hero */
        .contact-hero {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .contact-hero::before {
            content: '📧';
            position: absolute;
            bottom: -20px;
            right: 20px;
            font-size: 150px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .contact-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: fadeInDown 1s ease;
        }

        .contact-hero p {
            font-size: 1.2rem;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto;
            animation: fadeInUp 1s ease 0.2s both;
        }

        /* Contact Container */
        .contact-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 2rem;
        }

        /* Contact Info Cards */
        .contact-info {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            animation: slideInLeft 0.5s ease;
        }

        .info-title {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-title i {
            color: #6c5ce7;
        }

        .info-card {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(108, 92, 231, 0.15);
            background: white;
        }

        .info-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }

        .info-content h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: #2d3436;
        }

        .info-content p {
            color: #636e72;
            line-height: 1.6;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f1f1f1;
        }

        .social-link {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #f1f1f1;
            color: #6c5ce7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s;
            text-decoration: none;
        }

        .social-link:hover {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            transform: translateY(-5px);
        }

        /* Contact Form */
        .contact-form-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            animation: slideInRight 0.5s ease;
        }

        .form-title {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-title i {
            color: #6c5ce7;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideInDown 0.3s ease;
        }

        .alert-success {
            background: rgba(0, 184, 148, 0.1);
            color: #00b894;
            border-left: 4px solid #00b894;
        }

        .alert-error {
            background: rgba(214, 48, 49, 0.1);
            color: #d63031;
            border-left: 4px solid #d63031;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2d3436;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #6c5ce7;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
            width: 100%;
            justify-content: center;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.5);
        }

        .btn-primary i {
            font-size: 1.1rem;
        }

        /* Map Section */
        .map-section {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .map-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .map-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .map-placeholder {
            background: linear-gradient(135deg, #f1f1f1 0%, #e1e1e1 100%);
            height: 300px;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #636e72;
            position: relative;
            overflow: hidden;
        }

        .map-placeholder::before {
            content: '🇪🇹';
            position: absolute;
            font-size: 150px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .map-placeholder i {
            font-size: 3rem;
            color: #6c5ce7;
            margin-bottom: 1rem;
            z-index: 1;
        }

        .map-placeholder p {
            z-index: 1;
        }

        /* Footer */
        .footer {
            background: #2d3436;
            color: white;
            padding: 3rem 5% 1rem;
            margin-top: 3rem;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }

        .footer-section p {
            color: #b2bec3;
            line-height: 1.6;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: #b2bec3;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-section ul li a:hover {
            color: #6c5ce7;
        }

        .footer-bottom {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #b2bec3;
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .contact-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .contact-hero h1 {
                font-size: 2.5rem;
            }
            
            .info-card {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .social-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Ethiopian E-Book Store</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="search.php">Search</a></li>
                <?php if (SessionManager::isLoggedIn()): ?>
                    <li><a href="../user/dashboard.php">Dashboard</a></li>
                    <li><a href="../user/cart.php">Cart</a></li>
                    <li><a href="../user/orders.php">My Orders</a></li>
                    <li><a href="contact.php" class="active">Contact</a></li>
                    <li><a href="../user/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="contact.php" class="active">Contact</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="contact-hero">
        <h1><i class="fas fa-envelope"></i> Get in Touch</h1>
        <p>Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
    </div>

    <div class="contact-container">
        <!-- Contact Information -->
        <div class="contact-info">
            <h2 class="info-title">
                <i class="fas fa-info-circle"></i> Contact Information
            </h2>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="info-content">
                    <h3>Our Location</h3>
                    <p>Bole Road, Addis Ababa<br>Ethiopia</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <div class="info-content">
                    <h3>Phone Number</h3>
                    <p>+251 911 234 567<br>+251 116 123 456</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="info-content">
                    <h3>Email Address</h3>
                    <p>info@ethiopianebooks.com<br>support@ethiopianebooks.com</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="info-content">
                    <h3>Working Hours</h3>
                    <p>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM</p>
                </div>
            </div>
            
            <div class="social-links">
                <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-link"><i class="fab fa-telegram-plane"></i></a>
                <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="contact-form-container">
            <h2 class="form-title">
                <i class="fas fa-paper-plane"></i> Send us a Message
            </h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Your Name</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" 
                           placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                           placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="subject"><i class="fas fa-tag"></i> Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" 
                           value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" 
                           placeholder="What is this about?" required>
                </div>
                
                <div class="form-group">
                    <label for="message"><i class="fas fa-comment"></i> Message</label>
                    <textarea class="form-control" id="message" name="message" 
                              placeholder="Write your message here..." required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        </div>
    </div>
    
    <!-- Map Section -->
    <div class="map-section">
        <div class="map-container">
            <h2 class="map-title">
                <i class="fas fa-map-marked-alt"></i> Find Us
            </h2>
            <div class="map-placeholder">
                <i class="fas fa-map"></i>
                <p>Interactive Map - Addis Ababa, Ethiopia</p>
                <p style="font-size: 0.9rem;">📍 Bole Road, near Edna Mall</p>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Ethiopian E-Book Store</h3>
                <p>Ethiopia's premier digital reading destination. Discover thousands of Ethiopian and international e-books in multiple languages.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div class="social-links" style="justify-content: flex-start;">
                    <a href="#" style="color: #b2bec3; font-size: 1.5rem;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: #b2bec3; font-size: 1.5rem;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color: #b2bec3; font-size: 1.5rem;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: #b2bec3; font-size: 1.5rem;"><i class="fab fa-telegram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Ethiopian E-Book Store. All rights reserved. 🇪🇹</p>
        </div>
    </footer>
</body>
</html>