<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Ethiopian E-Book Store</title>
    
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
           PRIVACY POLICY PAGE STYLES
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

        /* Privacy Hero */
        .privacy-hero {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .privacy-hero::before {
            content: '🔒';
            position: absolute;
            bottom: -20px;
            right: 20px;
            font-size: 150px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .privacy-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: fadeInDown 1s ease;
        }

        .privacy-hero p {
            font-size: 1.2rem;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .last-updated {
            margin-top: 1rem;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Privacy Container */
        .privacy-container {
            max-width: 900px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        /* Policy Sections */
        .policy-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            animation: fadeInUp 0.5s ease;
            transition: all 0.3s;
        }

        .policy-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(108, 92, 231, 0.15);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .section-title i {
            font-size: 2rem;
            color: #6c5ce7;
            background: rgba(108, 92, 231, 0.1);
            padding: 1rem;
            border-radius: 15px;
        }

        .section-title h2 {
            font-size: 1.8rem;
            color: #2d3436;
        }

        .section-content {
            padding-left: 1rem;
        }

        .section-content p {
            color: #4a4a4a;
            line-height: 1.8;
            margin-bottom: 1rem;
        }

        .section-content ul {
            list-style: none;
            margin: 1rem 0;
        }

        .section-content ul li {
            margin-bottom: 0.8rem;
            padding-left: 2rem;
            position: relative;
            color: #4a4a4a;
        }

        .section-content ul li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #6c5ce7;
            font-weight: 700;
        }

        .section-content ul li strong {
            color: #2d3436;
        }

        /* Info Cards */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .info-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s;
        }

        .info-card:hover {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            transform: translateY(-5px);
        }

        .info-card:hover i,
        .info-card:hover h4 {
            color: white;
        }

        .info-card i {
            font-size: 2rem;
            color: #6c5ce7;
            margin-bottom: 0.5rem;
        }

        .info-card h4 {
            color: #2d3436;
            margin-bottom: 0.3rem;
        }

        .info-card p {
            font-size: 0.85rem;
            color: #636e72;
        }

        .info-card:hover p {
            color: rgba(255,255,255,0.9);
        }

        /* Contact Box */
        .contact-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin-top: 2rem;
        }

        .contact-box h3 {
            color: #2d3436;
            margin-bottom: 1rem;
        }

        .contact-box .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            margin-top: 1rem;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }

        .contact-box .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.5);
        }

        /* Table of Contents */
        .toc {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .toc h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: #2d3436;
        }

        .toc-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
        }

        .toc-item {
            padding: 0.8rem;
            background: #f8f9fa;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: #2d3436;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .toc-item:hover {
            background: #6c5ce7;
            color: white;
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

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .privacy-hero h1 {
                font-size: 2.5rem;
            }
            
            .toc-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .section-title {
                flex-direction: column;
                text-align: center;
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
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="privacy.php" class="active">Privacy</a></li>
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

    <div class="privacy-hero">
        <h1><i class="fas fa-shield-alt"></i> Privacy Policy</h1>
        <p>Your privacy is important to us. Learn how we protect and handle your data.</p>
        <div class="last-updated">
            <i class="far fa-calendar-alt"></i> Last Updated: January 1, 2024
        </div>
    </div>

    <div class="privacy-container">
        <!-- Table of Contents -->
        <div class="toc">
            <h3><i class="fas fa-list"></i> Quick Navigation</h3>
            <div class="toc-grid">
                <a href="#information" class="toc-item">Information</a>
                <a href="#usage" class="toc-item">How We Use</a>
                <a href="#cookies" class="toc-item">Cookies</a>
                <a href="#rights" class="toc-item">Your Rights</a>
                <a href="#security" class="toc-item">Security</a>
                <a href="#third-party" class="toc-item">Third Party</a>
                <a href="#children" class="toc-item">Children</a>
                <a href="#changes" class="toc-item">Changes</a>
            </div>
        </div>

        <!-- Information We Collect -->
        <div id="information" class="policy-section">
            <div class="section-title">
                <i class="fas fa-database"></i>
                <h2>Information We Collect</h2>
            </div>
            <div class="section-content">
                <p>We collect information to provide better services to our users. The information we collect includes:</p>
                
                <div class="info-grid">
                    <div class="info-card">
                        <i class="fas fa-user"></i>
                        <h4>Personal Info</h4>
                        <p>Name, email, and account details</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-credit-card"></i>
                        <h4>Payment Data</h4>
                        <p>Securely processed by payment partners</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-history"></i>
                        <h4>Purchase History</h4>
                        <p>Books you've bought and downloaded</p>
                    </div>
                </div>

                <ul>
                    <li><strong>Account Information:</strong> When you register, we collect your name, email address, and password.</li>
                    <li><strong>Profile Information:</strong> You may add additional information to your profile like a bio or profile picture.</li>
                    <li><strong>Purchase Information:</strong> We keep records of books you purchase, download, or add to your wishlist.</li>
                    <li><strong>Usage Data:</strong> How you interact with our website, including pages visited and features used.</li>
                    <li><strong>Device Information:</strong> IP address, browser type, and operating system for analytics and security.</li>
                </ul>
            </div>
        </div>

        <!-- How We Use Information -->
        <div id="usage" class="policy-section">
            <div class="section-title">
                <i class="fas fa-cogs"></i>
                <h2>How We Use Your Information</h2>
            </div>
            <div class="section-content">
                <p>We use the information we collect to:</p>
                <ul>
                    <li><strong>Provide Services:</strong> Process your orders, manage your account, and deliver purchased e-books.</li>
                    <li><strong>Improve Experience:</strong> Personalize your recommendations and enhance our website based on usage.</li>
                    <li><strong>Communication:</strong> Send order confirmations, updates, and promotional offers (you can opt out).</li>
                    <li><strong>Security:</strong> Protect against unauthorized access and ensure the safety of your data.</li>
                    <li><strong>Legal Compliance:</strong> Meet legal requirements and enforce our terms of service.</li>
                </ul>
            </div>
        </div>

        <!-- Cookies -->
        <div id="cookies" class="policy-section">
            <div class="section-title">
                <i class="fas fa-cookie-bite"></i>
                <h2>Cookies & Tracking</h2>
            </div>
            <div class="section-content">
                <p>We use cookies and similar technologies to enhance your browsing experience:</p>
                <ul>
                    <li><strong>Essential Cookies:</strong> Required for the website to function (cart, login sessions).</li>
                    <li><strong>Analytics Cookies:</strong> Help us understand how visitors interact with our site.</li>
                    <li><strong>Preference Cookies:</strong> Remember your settings and preferences.</li>
                    <li><strong>Marketing Cookies:</strong> Used to show relevant book recommendations.</li>
                </ul>
                <p style="margin-top: 1rem; background: #f8f9fa; padding: 1rem; border-radius: 10px;">
                    <i class="fas fa-info-circle" style="color: #6c5ce7;"></i> 
                    You can control cookies through your browser settings. Disabling cookies may affect site functionality.
                </p>
            </div>
        </div>

        <!-- Your Rights -->
        <div id="rights" class="policy-section">
            <div class="section-title">
                <i class="fas fa-gavel"></i>
                <h2>Your Rights</h2>
            </div>
            <div class="section-content">
                <p>As a user, you have the following rights regarding your personal data:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of the personal data we hold about you.</li>
                    <li><strong>Correction:</strong> Update or correct inaccurate information.</li>
                    <li><strong>Deletion:</strong> Request deletion of your account and personal data.</li>
                    <li><strong>Opt-out:</strong> Unsubscribe from marketing communications.</li>
                    <li><strong>Data Portability:</strong> Receive your data in a structured format.</li>
                </ul>
                <p style="margin-top: 1rem;">To exercise these rights, please contact us at privacy@ethiopianebooks.com</p>
            </div>
        </div>

        <!-- Data Security -->
        <div id="security" class="policy-section">
            <div class="section-title">
                <i class="fas fa-lock"></i>
                <h2>Data Security</h2>
            </div>
            <div class="section-content">
                <p>We implement robust security measures to protect your information:</p>
                <ul>
                    <li><strong>Encryption:</strong> SSL/TLS encryption for all data transmission.</li>
                    <li><strong>Secure Storage:</strong> Passwords are hashed and salted.</li>
                    <li><strong>Regular Audits:</strong> We regularly review our security practices.</li>
                    <li><strong>Payment Security:</strong> We don't store credit card details; all payments are processed securely by our payment partners.</li>
                </ul>
                <div style="background: linear-gradient(135deg, #00b89420 0%, #00cec920 100%); padding: 1rem; border-radius: 10px; margin-top: 1rem;">
                    <i class="fas fa-shield-check" style="color: #00b894;"></i> 
                    Your security is our priority. We never share your personal information with third parties without your consent.
                </div>
            </div>
        </div>

        <!-- Third Party Services -->
        <div id="third-party" class="policy-section">
            <div class="section-title">
                <i class="fas fa-handshake"></i>
                <h2>Third Party Services</h2>
            </div>
            <div class="section-content">
                <p>We may use trusted third-party services for:</p>
                <ul>
                    <li><strong>Payment Processing:</strong> Secure payment gateways (Chapa, PayPal, etc.)</li>
                    <li><strong>Analytics:</strong> Understanding how users interact with our site.</li>
                    <li><strong>Email Services:</strong> Sending order confirmations and newsletters.</li>
                    <li><strong>Cloud Storage:</strong> Securely storing e-book files.</li>
                </ul>
                <p>These third parties have their own privacy policies and are contractually obligated to protect your data.</p>
            </div>
        </div>

        <!-- Children's Privacy -->
        <div id="children" class="policy-section">
            <div class="section-title">
                <i class="fas fa-child"></i>
                <h2>Children's Privacy</h2>
            </div>
            <div class="section-content">
                <p>Our service is not directed to children under 13. We do not knowingly collect personal information from children under 13. If you are a parent or guardian and believe your child has provided us with personal information, please contact us.</p>
            </div>
        </div>

        <!-- Changes to Policy -->
        <div id="changes" class="policy-section">
            <div class="section-title">
                <i class="fas fa-history"></i>
                <h2>Changes to This Policy</h2>
            </div>
            <div class="section-content">
                <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date. You are advised to review this Privacy Policy periodically for any changes.</p>
            </div>
        </div>

        <!-- Contact Us -->
        <div class="contact-box">
            <i class="fas fa-envelope-open-text" style="font-size: 3rem; color: #6c5ce7; margin-bottom: 1rem;"></i>
            <h3>Have Questions About Your Privacy?</h3>
            <p>If you have any questions about this Privacy Policy, please contact us:</p>
            <ul style="list-style: none; margin: 1rem 0; color: #636e72;">
                <li><i class="fas fa-envelope"></i> privacy@ethiopianebooks.com</li>
                <li><i class="fas fa-phone"></i> +251 911 234 567</li>
                <li><i class="fas fa-map-marker-alt"></i> Bole Road, Addis Ababa, Ethiopia</li>
            </ul>
            <a href="contact.php" class="btn">
                <i class="fas fa-paper-plane"></i> Contact Us
            </a>
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
                <div class="social-links" style="display: flex; gap: 1rem;">
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