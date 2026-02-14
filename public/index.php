<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$db = Database::getInstance();

// Get featured books
$featuredSql = "SELECT b.*, c.name as category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.id 
                WHERE b.is_featured = 1 
                LIMIT 8";
$featuredResult = $db->query($featuredSql);
$featuredBooks = $featuredResult->get_result();

// Get categories
$categoriesSql = "SELECT * FROM categories LIMIT 6";
$categoriesResult = $db->query($categoriesSql);
$categories = $categoriesResult->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Your Digital Reading Destination</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
<link rel="stylesheet" href="/ebook-store/assets/css/responsive.css">
<!-- Ethiopian Flag Favicon - PASTE HERE -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect
     width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' 
     fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50'
     cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="logo"><?php echo SITE_NAME; ?></div>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="search.php">Search</a></li>
                <?php if (SessionManager::isLoggedIn()): ?>
                    <li><a href="../user/dashboard.php">Dashboard</a></li>
                    <li><a href="../user/cart.php">Cart</a></li>
                    <li><a href="../user/orders.php">My Orders</a></li>
                    <li><a href="../user/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Discover Your Next Great Read</h1>
            <p>Thousands of e-books waiting for you. Start your reading journey today!</p>
            <a href="search.php" class="btn btn-primary">Browse Books</a>
            <a href="categories.php" class="btn btn-secondary">View Categories</a>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section">
        <div class="search-container">
            <form action="search.php" method="GET" style="display: flex; width: 100%; gap: 1rem;">
                <input type="text" name="q" class="search-input" placeholder="Search by title, author, or category..." required>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </section>

    <!-- Featured Books -->
    <section class="featured-books">
        <div class="section-title">
            <h2>Featured Books</h2>
            <p>Hand-picked books just for you</p>
        </div>
        
        <div class="books-grid">
            <?php while($book = $featuredBooks->fetch_assoc()): ?>
            <div class="book-card">
                <div class="book-cover">
                    <?php if($book['cover_image']): ?>
                        <img src="../assets/uploads/covers/<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>">
                    <?php else: ?>
                        <span>📚</span>
                    <?php endif; ?>
                </div>
                <div class="book-info">
                    <h3 class="book-title"><?php echo $book['title']; ?></h3>
                    <p class="book-author">by <?php echo $book['author']; ?></p>
                    <div class="book-price"><?php echo Functions::formatPrice($book['price']); ?></div>
                    <a href="book-details.php?id=<?php echo $book['id']; ?>" class="btn btn-primary" style="width: 100%;">View Details</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="section-title">
            <h2>Browse Categories</h2>
            <p>Explore books by your favorite topics</p>
        </div>
        
        <div class="categories-grid">
            <?php while($category = $categories->fetch_assoc()): ?>
            <div class="category-card" onclick="window.location.href='categories.php?id=<?php echo $category['id']; ?>'">
                <div class="category-icon">📖</div>
                <h3><?php echo $category['name']; ?></h3>
                <p style="margin-top: 0.5rem; color: var(--gray);"><?php echo Functions::truncateText($category['description'], 50); ?></p>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3><?php echo SITE_NAME; ?></h3>
                <p>Your premier destination for digital reading. Thousands of e-books at your fingertips.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul style="list-style: none;">
                    <li style="margin-bottom: 0.5rem;"><a href="about.php" style="color: var(--light); text-decoration: none;">About Us</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="contact.php" style="color: var(--light); text-decoration: none;">Contact</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="privacy.php" style="color: var(--light); text-decoration: none;">Privacy Policy</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="terms.php" style="color: var(--light); text-decoration: none;">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div style="display: flex; gap: 1rem;">
                    <a href="#" style="color: var(--light); text-decoration: none;">Facebook</a>
                    <a href="#" style="color: var(--light); text-decoration: none;">Twitter</a>
                    <a href="#" style="color: var(--light); text-decoration: none;">Instagram</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>