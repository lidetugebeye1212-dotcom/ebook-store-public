<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - Ethiopian E-Book Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/responsive.css">
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
    <style>
        .terms-hero {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .terms-hero::before {
            content: '📜';
            position: absolute;
            bottom: -20px;
            right: 20px;
            font-size: 150px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }
        .terms-container {
            max-width: 900px;
            margin: 3rem auto;
            padding: 0 2rem;
        }
        .terms-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .terms-section h2 {
            color: #6c5ce7;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .terms-section p {
            line-height: 1.8;
            color: #4a4a4a;
            margin-bottom: 1rem;
        }
        .terms-section ul {
            padding-left: 2rem;
            color: #4a4a4a;
        }
        .terms-section ul li {
            margin-bottom: 0.5rem;
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
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="privacy.php">Privacy</a></li>
                <li><a href="terms.php" class="active">Terms</a></li>
                <?php if (SessionManager::isLoggedIn()): ?>
                    <li><a href="../user/dashboard.php">Dashboard</a></li>
                    <li><a href="../user/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="terms-hero">
        <h1><i class="fas fa-file-contract"></i> Terms of Service</h1>
        <p>Please read these terms carefully before using our service</p>
    </div>

    <div class="terms-container">
        <div class="terms-section">
            <h2><i class="fas fa-check-circle"></i> Acceptance of Terms</h2>
            <p>By accessing or using Ethiopian E-Book Store, you agree to be bound by these Terms of Service. If you do not agree to all the terms, you may not access or use our services.</p>
        </div>

        <div class="terms-section">
            <h2><i class="fas fa-user"></i> Account Registration</h2>
            <p>To make purchases, you must register for an account. You are responsible for maintaining the confidentiality of your account credentials and for all activities under your account.</p>
        </div>

        <div class="terms-section">
            <h2><i class="fas fa-shopping-cart"></i> Purchases & Payments</h2>
            <p>All purchases are final. By purchasing an e-book, you receive a non-exclusive, non-transferable license to download and read the book for personal use only. You may not redistribute, resell, or share the e-book files.</p>
        </div>

        <div class="terms-section">
            <h2><i class="fas fa-download"></i> Digital Downloads</h2>
            <p>Once purchased, e-books are available for download through your account. We recommend downloading your purchases promptly and backing them up. We are not responsible for lost files after download.</p>
        </div>

        <div class="terms-section">
            <h2><i class="fas fa-ban"></i> Prohibited Activities</h2>
            <ul>
                <li>Sharing account credentials</li>
                <li>Distributing purchased e-books</li>
                <li>Using automated systems to access the site</li>
                <li>Attempting to bypass security measures</li>
                <li>Uploading malicious content</li>
            </ul>
        </div>

        <div class="terms-section">
            <h2><i class="fas fa-copyright"></i> Intellectual Property</h2>
            <p>All content on this site, including e-books, images, and text, is protected by copyright and other intellectual property laws. You may not use our content without express written permission.</p>
        </div>

        <div class="terms-section">
            <h2><i class="fas fa-shield-alt"></i> Limitation of Liability</h2>
            <p>We provide our services "as is" without warranties. We are not liable for any indirect, incidental, or consequential damages arising from your use of our services.</p>
        </div>

        <div class="terms-section">
            <h2><i class="fas fa-envelope"></i> Contact Us</h2>
            <p>For questions about these Terms, please contact us at legal@ethiopianebooks.com</p>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Ethiopian E-Book Store</h3>
                <p>Ethiopia's premier digital reading destination.</p>
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
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Ethiopian E-Book Store. All rights reserved. 🇪🇹</p>
        </div>
    </footer>
</body>
</html>