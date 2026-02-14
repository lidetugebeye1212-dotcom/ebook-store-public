<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$db = Database::getInstance();

// Get selected category
$selectedCategory = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get all categories with book counts
$categoriesSql = "SELECT c.*, COUNT(b.id) as book_count 
                 FROM categories c 
                 LEFT JOIN books b ON c.id = b.category_id 
                 GROUP BY c.id 
                 ORDER BY c.name";
$categoriesStmt = $db->query($categoriesSql);
$categoriesResult = $categoriesStmt->get_result();

// Get books based on category
if ($selectedCategory) {
    $booksSql = "SELECT b.*, c.name as category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.id 
                WHERE b.category_id = ? 
                ORDER BY b.created_at DESC";
    $booksStmt = $db->query($booksSql, [$selectedCategory]);
    $books = $booksStmt->get_result();
    
    // Get category name
    $catNameSql = "SELECT name FROM categories WHERE id = ?";
    $catNameStmt = $db->query($catNameSql, [$selectedCategory]);
    $catName = $catNameStmt->get_result()->fetch_assoc();
} else {
    $booksSql = "SELECT b.*, c.name as category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.id 
                ORDER BY b.created_at DESC";
    $booksStmt = $db->query($booksSql);
    $books = $booksStmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Ethiopian eBook Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/responsive.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/categories.css">
        <!-- Ethiopian Flag Favicon - PASTE HERE -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Ethiopian E-Book Store</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="categories.php" class="active">Categories</a></li>
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

    <div class="categories-hero">
        <h1>Browse Categories</h1>
        <p>Discover books from Ethiopian literature, history, culture and more</p>
    </div>
    
    <div class="categories-container">
        <!-- Sidebar -->
        <div class="categories-sidebar">
            <h3>All Categories</h3>
            <ul class="category-list">
                <li class="<?php echo !$selectedCategory ? 'active' : ''; ?>">
                    <a href="categories.php">All Books</a>
                </li>
                <?php 
                // Reset pointer to beginning
                mysqli_data_seek($categoriesResult, 0);
                while($category = $categoriesResult->fetch_assoc()): 
                ?>
                    <li class="<?php echo $selectedCategory == $category['id'] ? 'active' : ''; ?>">
                        <a href="categories.php?id=<?php echo $category['id']; ?>">
                            <?php echo $category['name']; ?>
                            <span class="count">(<?php echo $category['book_count']; ?>)</span>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
            
            <div class="ethiopian-badge">
                <i class="fas fa-flag" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                <h4>Ethiopian Collection</h4>
                <p>Discover our curated collection of Ethiopian books in Amharic, Afan Oromo, Tigrinya, and English</p>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="categories-main">
            <div class="category-header">
                <h2>
                    <?php 
                    if ($selectedCategory && isset($catName)) {
                        echo $catName['name'];
                    } else {
                        echo 'All Books';
                    }
                    ?>
                </h2>
                <div class="sort-options">
                    <label>Sort by:</label>
                    <select onchange="window.location.href=this.value">
                        <option value="?sort=newest<?php echo $selectedCategory ? '&id='.$selectedCategory : ''; ?>">Newest</option>
                        <option value="?sort=price_low<?php echo $selectedCategory ? '&id='.$selectedCategory : ''; ?>">Price: Low to High</option>
                        <option value="?sort=price_high<?php echo $selectedCategory ? '&id='.$selectedCategory : ''; ?>">Price: High to Low</option>
                    </select>
                </div>
            </div>
            
            <?php if ($books->num_rows > 0): ?>
                <div class="books-grid">
                    <?php while($book = $books->fetch_assoc()): ?>
                        <div class="book-card">
                            <div class="book-cover">
                                <?php if(isset($book['cover_image']) && $book['cover_image']): ?>
                                    <img src="/ebook-store/assets/uploads/covers/<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>">
                                <?php else: ?>
                                    <span>📚</span>
                                <?php endif; ?>
                                
                                <?php if(isset($book['bestseller']) && $book['bestseller']): ?>
                                    <span class="book-badge-small">Bestseller</span>
                                <?php endif; ?>
                            </div>
                            <div class="book-info">
                                <h3 class="book-title"><?php echo Functions::truncateText($book['title'], 40); ?></h3>
                                <p class="book-author">by <?php echo $book['author']; ?></p>
                                <p class="book-category"><?php echo isset($book['category_name']) ? $book['category_name'] : 'Uncategorized'; ?></p>
                                <div class="book-price">$<?php echo number_format($book['price'], 2); ?></div>
                                <a href="book-details.php?id=<?php echo $book['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px;">
                    <i class="fas fa-book-open" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h3>No books found in this category</h3>
                    <p style="color: #666; margin: 1rem 0;">Check back later for new additions</p>
                    <a href="categories.php" class="btn btn-primary">View All Books</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Ethiopian E-Book Store</h3>
                <p>Ethiopia's premier digital reading destination. Discover thousands of Ethiopian and international e-books.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul style="list-style: none;">
                    <li><a href="about.php" style="color: #ccc; text-decoration: none;">About Us</a></li>
                    <li><a href="contact.php" style="color: #ccc; text-decoration: none;">Contact</a></li>
                    <li><a href="privacy.php" style="color: #ccc; text-decoration: none;">Privacy Policy</a></li>
                    <li><a href="terms.php" style="color: #ccc; text-decoration: none;">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div style="display: flex; gap: 1rem;">
                    <a href="#" style="color: #ccc; text-decoration: none;">Facebook</a>
                    <a href="#" style="color: #ccc; text-decoration: none;">Twitter</a>
                    <a href="#" style="color: #ccc; text-decoration: none;">Instagram</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Ethiopian E-Book Store. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>