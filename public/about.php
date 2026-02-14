<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Ethiopian E-Book Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/responsive.css">
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Ethiopian E-Book Store</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="search.php">Search</a></li>
                <li><a href="about.php" class="active">About</a></li>
                <li><a href="contact.php">Contact</a></li>
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

    <div style="max-width: 800px; margin: 3rem auto; padding: 2rem; background: white; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
        <h1 style="font-size: 2.5rem; color: #6c5ce7; margin-bottom: 1rem;">About Us</h1>
        <p style="margin-bottom: 1rem; line-height: 1.8;">Welcome to Ethiopian E-Book Store, your premier destination for Ethiopian literature and international e-books.</p>
        <p style="margin-bottom: 1rem; line-height: 1.8;">We are dedicated to preserving and promoting Ethiopian literature in all its forms - from classic Amharic novels to contemporary works in English, Afan Oromo, and Tigrinya.</p>
        <p style="margin-bottom: 1rem; line-height: 1.8;">Our mission is to make Ethiopian books accessible to readers worldwide and to support Ethiopian authors by providing a platform for their work.</p>
        <div style="margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 10px;">
            <h3 style="color: #6c5ce7; margin-bottom: 0.5rem;">🇪🇹 Our Commitment</h3>
            <p>We are 100% Ethiopian-owned and committed to supporting local authors and publishers.</p>
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
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Ethiopian E-Book Store. All rights reserved. 🇪🇹</p>
        </div>
    </footer>
</body>
</html>