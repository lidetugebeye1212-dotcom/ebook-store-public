<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

$db = Database::getInstance();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    Functions::redirect('index.php');
}

$bookId = (int)$_GET['id'];

// Get book details with category
$bookSql = "SELECT b.*, c.name as category_name, c.id as category_id 
            FROM books b 
            LEFT JOIN categories c ON b.category_id = c.id 
            WHERE b.id = ?";
$bookStmt = $db->query($bookSql, [$bookId]);
$book = $bookStmt->get_result()->fetch_assoc();
$bookStmt->close();

if (!$book) {
    Functions::redirect('index.php');
}

// Get related books (same category)
$relatedSql = "SELECT * FROM books 
               WHERE category_id = ? AND id != ? 
               LIMIT 4";
$relatedStmt = $db->query($relatedSql, [$book['category_id'], $bookId]);
$relatedBooks = $relatedStmt->get_result();
$relatedStmt->close();

// Get book reviews - FIXED: Properly handle result sets
$reviewsSql = "SELECT r.*, u.username, u.full_name 
               FROM book_reviews r 
               JOIN users u ON r.user_id = u.id 
               WHERE r.book_id = ? 
               ORDER BY r.created_at DESC";
$reviewsStmt = $db->query($reviewsSql, [$bookId]);
$reviews = $reviewsStmt->get_result();
// DON'T close $reviewsStmt yet - we need it for fetch later

// Calculate average rating - Use a separate query to avoid sync issues
$avgSql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
           FROM book_reviews WHERE book_id = ?";
$avgStmt = $db->query($avgSql, [$bookId]);
$avgData = $avgStmt->get_result()->fetch_assoc();
$avgStmt->close();

$avgRating = $avgData['avg_rating'] ? round($avgData['avg_rating'], 1) : 0;
$totalReviews = $avgData['total_reviews'] ?? 0;

// Handle add to cart
if (isset($_POST['add_to_cart']) && SessionManager::isLoggedIn()) {
    $userId = SessionManager::getUserId();
    $cartSql = "INSERT INTO cart (user_id, book_id) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE quantity = quantity + 1";
    $cartStmt = $db->query($cartSql, [$userId, $bookId]);
    $cartStmt->close();
    Functions::redirect('book-details.php?id=' . $bookId . '&added=1');
}

// Handle review submission
if (isset($_POST['submit_review']) && SessionManager::isLoggedIn()) {
    $userId = SessionManager::getUserId();
    $rating = (int)$_POST['rating'];
    $reviewText = Functions::sanitize($_POST['review_text']);
    
    // Check if user already reviewed this book
    $checkSql = "SELECT id FROM book_reviews WHERE book_id = ? AND user_id = ?";
    $checkStmt = $db->query($checkSql, [$bookId, $userId]);
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows == 0) {
        $checkStmt->close();
        $insertSql = "INSERT INTO book_reviews (book_id, user_id, rating, review_text) 
                      VALUES (?, ?, ?, ?)";
        $insertStmt = $db->query($insertSql, [$bookId, $userId, $rating, $reviewText]);
        $insertStmt->close();
    } else {
        $checkStmt->close();
    }
    
    Functions::redirect("book-details.php?id=$bookId&reviewed=1");
}

// Get success/error messages
$added = isset($_GET['added']) ? true : false;
$reviewed = isset($_GET['reviewed']) ? true : false;

// Format publication date safely
$publicationDate = !empty($book['publication_date']) ? date('M d, Y', strtotime($book['publication_date'])) : 'Not specified';

// Check if user has already reviewed
$userReviewed = false;
if (SessionManager::isLoggedIn()) {
    $userId = SessionManager::getUserId();
    $checkUserSql = "SELECT id FROM book_reviews WHERE book_id = ? AND user_id = ?";
    $checkUserStmt = $db->query($checkUserSql, [$bookId, $userId]);
    $userReviewed = $checkUserStmt->get_result()->num_rows > 0;
    $checkUserStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - Ethiopian E-Book Store</title>
    
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
        /* Book Details Page Styles */
        .book-details-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .book-details-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 3rem;
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }

        .book-cover-large {
            position: relative;
            height: 450px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(108, 92, 231, 0.3);
        }

        .book-cover-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-cover-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8rem;
            color: white;
        }

        .book-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            z-index: 2;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .award-badge {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
        }

        .bestseller-badge {
            background: linear-gradient(135deg, #6c5ce7, #a463f5);
            color: white;
        }

        .ethiopian-flag-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #078930, #FCDD09, #DA121A);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            z-index: 2;
        }

        .book-info-detailed {
            padding: 1rem 0;
        }

        .book-title-detailed {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #2d3436;
            line-height: 1.2;
        }

        .book-author-detailed {
            font-size: 1.3rem;
            color: #6c5ce7;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .book-rating-detailed {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stars {
            color: #FFD700;
            font-size: 1.2rem;
        }

        .rating-count {
            color: #636e72;
            font-size: 0.95rem;
        }

        .book-price-detailed {
            font-size: 2.5rem;
            font-weight: 800;
            color: #6c5ce7;
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }

        .book-price-detailed::before {
            content: '$';
            font-size: 1.2rem;
            position: relative;
            top: -10px;
            right: 2px;
            color: #636e72;
        }

        .book-price-detailed::after {
            content: 'ETB';
            font-size: 0.9rem;
            margin-left: 0.5rem;
            color: #636e72;
            font-weight: 400;
        }

        .book-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 0.85rem;
            color: #636e72;
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3436;
        }

        .meta-value.na {
            color: #b2bec3;
            font-style: italic;
        }

        .book-description {
            margin-bottom: 2rem;
            line-height: 1.8;
            color: #4a4a4a;
        }

        .book-description h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #2d3436;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .book-description h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            border-radius: 5px;
        }

        .book-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-large {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 10px;
            flex: 1;
            text-align: center;
        }

        .login-prompt {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
        }

        .login-prompt a {
            color: white;
            font-weight: 700;
            text-decoration: underline;
        }

        .reviews-section {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }

        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .reviews-header h2 {
            font-size: 1.8rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .reviews-summary {
            display: flex;
            align-items: center;
            gap: 3rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .average-rating {
            text-align: center;
        }

        .big-rating {
            font-size: 3.5rem;
            font-weight: 800;
            color: #6c5ce7;
            line-height: 1;
        }

        .rating-label {
            color: #636e72;
            font-size: 0.9rem;
            display: block;
        }

        .stars.large {
            font-size: 1.5rem;
            margin: 0.5rem 0;
        }

        .total-reviews {
            color: #636e72;
            font-size: 0.9rem;
        }

        .rating-distribution {
            flex: 1;
        }

        .rating-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.3rem;
        }

        .rating-bar span {
            width: 40px;
            font-size: 0.9rem;
        }

        .bar-container {
            flex: 1;
            height: 8px;
            background: #f1f1f1;
            border-radius: 4px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #6c5ce7, #a463f5);
            border-radius: 4px;
        }

        .reviews-list {
            max-height: 500px;
            overflow-y: auto;
            padding-right: 1rem;
        }

        .review-item {
            padding: 1.5rem;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.3s;
            position: relative;
        }

        .review-item:hover {
            background: #f8f9fa;
            border-radius: 10px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .reviewer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6c5ce7, #a463f5);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .reviewer-info strong {
            font-size: 1.1rem;
            color: #2d3436;
        }

        .review-stars {
            color: #FFD700;
            font-size: 0.9rem;
        }

        .review-date {
            color: #636e72;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .review-text {
            color: #4a4a4a;
            line-height: 1.6;
            margin-top: 1rem;
            padding-left: 3.5rem;
        }

        .no-reviews {
            text-align: center;
            padding: 3rem;
            color: #636e72;
        }

        .no-reviews i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .related-books {
            margin-top: 3rem;
        }

        .related-books h2 {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideInDown 0.3s ease;
        }

        .close {
            float: right;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            color: #636e72;
            transition: color 0.3s;
        }

        .close:hover {
            color: #d63031;
        }

        .rating-selector {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .rating-selector input {
            display: none;
        }

        .rating-selector label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: all 0.3s;
        }

        .rating-selector label:hover,
        .rating-selector label:hover ~ label,
        .rating-selector input:checked ~ label {
            color: #FFD700;
        }

        .alert-success {
            background: rgba(0, 184, 148, 0.1);
            color: #00b894;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #00b894;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .already-reviewed {
            background: #f1f1f1;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            color: #636e72;
        }

        @media (max-width: 768px) {
            .book-details-grid {
                grid-template-columns: 1fr;
            }
            
            .book-cover-large {
                height: 350px;
            }
            
            .book-meta-grid {
                grid-template-columns: 1fr;
            }
            
            .reviews-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .reviews-summary {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .book-actions {
                flex-direction: column;
            }
            
            .review-header {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .review-text {
                padding-left: 0;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
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
                    <?php if (SessionManager::isAdmin()): ?>
                        <li><a href="../admin/dashboard.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="../user/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="book-details-container">
        <?php if ($added): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Book added to cart successfully!
            </div>
        <?php endif; ?>
        
        <?php if ($reviewed): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> Thank you for your review!
            </div>
        <?php endif; ?>
        
        <div class="book-details-grid">
            <!-- Book Cover -->
            <div class="book-cover-large">
                <?php if(isset($book['cover_image']) && $book['cover_image']): ?>
                    <img src="/ebook-store/assets/uploads/covers/<?php echo $book['cover_image']; ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                <?php else: ?>
                    <div class="book-cover-placeholder">
                        <span>📚</span>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($book['award_winning']) && $book['award_winning']): ?>
                    <span class="book-badge award-badge">
                        <i class="fas fa-trophy"></i> Award Winner
                    </span>
                <?php endif; ?>
                
                <?php if(isset($book['bestseller']) && $book['bestseller']): ?>
                    <span class="book-badge bestseller-badge">
                        <i class="fas fa-star"></i> Bestseller
                    </span>
                <?php endif; ?>
                
                <?php if(isset($book['country']) && $book['country'] == 'Ethiopia'): ?>
                    <span class="ethiopian-flag-badge">
                        <i class="fas fa-flag"></i> Ethiopian
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- Book Info -->
            <div class="book-info-detailed">
                <h1 class="book-title-detailed"><?php echo htmlspecialchars($book['title']); ?></h1>
                <p class="book-author-detailed">by <?php echo htmlspecialchars($book['author']); ?></p>
                
                <div class="book-rating-detailed">
                    <div class="stars">
                        <?php 
                        $rating = $avgRating;
                        for($i = 1; $i <= 5; $i++): 
                            if($i <= floor($rating)):
                        ?>
                            <i class="fas fa-star"></i>
                        <?php elseif($i - 0.5 <= $rating): ?>
                            <i class="fas fa-star-half-alt"></i>
                        <?php else: ?>
                            <i class="far fa-star"></i>
                        <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-count">(<?php echo $totalReviews; ?> <?php echo $totalReviews == 1 ? 'review' : 'reviews'; ?>)</span>
                </div>
                
                <div class="book-price-detailed">
                    <?php echo number_format($book['price'], 2); ?>
                </div>
                
                <div class="book-meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">Publisher</span>
                        <span class="meta-value <?php echo empty($book['publisher']) ? 'na' : ''; ?>">
                            <?php echo !empty($book['publisher']) ? htmlspecialchars($book['publisher']) : 'Not specified'; ?>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Language</span>
                        <span class="meta-value"><?php echo htmlspecialchars($book['language'] ?? 'English'); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Pages</span>
                        <span class="meta-value"><?php echo $book['page_count'] ?? 'N/A'; ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">ISBN</span>
                        <span class="meta-value <?php echo empty($book['isbn']) ? 'na' : ''; ?>">
                            <?php echo !empty($book['isbn']) ? htmlspecialchars($book['isbn']) : 'N/A'; ?>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Publication Date</span>
                        <span class="meta-value"><?php echo $publicationDate; ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Country</span>
                        <span class="meta-value">
                            <?php echo htmlspecialchars($book['country'] ?? 'International'); ?>
                            <?php if(isset($book['country']) && $book['country'] == 'Ethiopia'): ?> 🇪🇹<?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <div class="book-description">
                    <h3>About this book</h3>
                    <p><?php echo nl2br(htmlspecialchars($book['description'] ?? 'No description available.')); ?></p>
                </div>
                
                <?php if (SessionManager::isLoggedIn()): ?>
                    <form method="POST" class="book-actions">
                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-large">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <a href="../user/checkout.php?book_id=<?php echo $book['id']; ?>" class="btn btn-secondary btn-large">
                            <i class="fas fa-bolt"></i> Buy Now
                        </a>
                    </form>
                <?php else: ?>
                    <div class="login-prompt">
                        <i class="fas fa-lock" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <p>Please <a href="login.php">login</a> or <a href="register.php">register</a> to purchase this book</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Reviews Section -->
        <div class="reviews-section">
            <div class="reviews-header">
                <h2><i class="fas fa-star"></i> Customer Reviews</h2>
                <?php if (SessionManager::isLoggedIn() && !$userReviewed): ?>
                    <button onclick="openReviewModal()" class="btn btn-primary">
                        <i class="fas fa-pen"></i> Write a Review
                    </button>
                <?php elseif (SessionManager::isLoggedIn() && $userReviewed): ?>
                    <span class="already-reviewed">
                        <i class="fas fa-check-circle"></i> You have already reviewed this book
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="reviews-summary">
                <div class="average-rating">
                    <span class="big-rating"><?php echo $avgRating; ?></span>
                    <span class="rating-label">out of 5</span>
                    <div class="stars large">
                        <?php 
                        for($i = 1; $i <= 5; $i++): 
                            if($i <= floor($avgRating)):
                        ?>
                            <i class="fas fa-star"></i>
                        <?php elseif($i - 0.5 <= $avgRating): ?>
                            <i class="fas fa-star-half-alt"></i>
                        <?php else: ?>
                            <i class="far fa-star"></i>
                        <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="total-reviews">Based on <?php echo $totalReviews; ?> <?php echo $totalReviews == 1 ? 'review' : 'reviews'; ?></span>
                </div>
                
                <?php if($totalReviews > 0): ?>
                <div class="rating-distribution">
                    <?php 
                    // Calculate rating distribution
                    $ratingDistribution = [5=>0,4=>0,3=>0,2=>0,1=>0];
                    
                    // Reset reviews pointer
                    $reviews->data_seek(0);
                    while($r = $reviews->fetch_assoc()) {
                        $ratingDistribution[$r['rating']] = ($ratingDistribution[$r['rating']] ?? 0) + 1;
                    }
                    // Reset again for display
                    $reviews->data_seek(0);
                    
                    for($i = 5; $i >= 1; $i--):
                        $count = $ratingDistribution[$i] ?? 0;
                        $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                    ?>
                    <div class="rating-bar">
                        <span><?php echo $i; ?> ★</span>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?php echo $percentage; ?>%;"></div>
                        </div>
                        <span><?php echo $count; ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="reviews-list">
                <?php if ($totalReviews > 0): ?>
                    <?php 
                    // Reset pointer again
                    $reviews->data_seek(0);
                    while($review = $reviews->fetch_assoc()): 
                    ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <div class="reviewer-avatar">
                                        <?php echo strtoupper(substr($review['full_name'] ?? $review['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($review['full_name'] ?? $review['username']); ?></strong>
                                        <div class="review-stars">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($i <= $review['rating']): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="review-date">
                                    <i class="far fa-calendar-alt"></i> 
                                    <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                </span>
                            </div>
                            <p class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                            
                            <?php if (SessionManager::isAdmin()): ?>
                                <div style="margin-top: 1rem; text-align: right;">
                                    <a href="../admin/delete-review.php?id=<?php echo $review['id']; ?>&book=<?php echo $bookId; ?>" 
                                       class="btn btn-small" 
                                       style="background: #d63031; color: white; padding: 0.3rem 1rem; border-radius: 5px; text-decoration: none; font-size: 0.8rem;"
                                       onclick="return confirm('Delete this review?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-reviews">
                        <i class="far fa-star"></i>
                        <h3>No reviews yet</h3>
                        <p>Be the first to share your thoughts about this book!</p>
                        <?php if (SessionManager::isLoggedIn() && !$userReviewed): ?>
                            <button onclick="openReviewModal()" class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-pen"></i> Write a Review
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Related Books -->
        <?php if ($relatedBooks && $relatedBooks->num_rows > 0): ?>
        <div class="related-books">
            <h2><i class="fas fa-book-open"></i> You might also like</h2>
            <div class="books-grid">
                <?php while($related = $relatedBooks->fetch_assoc()): ?>
                    <div class="book-card">
                        <div class="book-cover">
                            <?php if(isset($related['cover_image']) && $related['cover_image']): ?>
                                <img src="/ebook-store/assets/uploads/covers/<?php echo $related['cover_image']; ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                            <?php else: ?>
                                <span>📚</span>
                            <?php endif; ?>
                            <?php if(isset($related['bestseller']) && $related['bestseller']): ?>
                                <span class="book-badge-small">Bestseller</span>
                            <?php endif; ?>
                        </div>
                        <div class="book-info">
                            <h3 class="book-title"><?php echo Functions::truncateText($related['title'], 30); ?></h3>
                            <p class="book-author">by <?php echo htmlspecialchars($related['author']); ?></p>
                            <div class="book-price">$<?php echo number_format($related['price'], 2); ?></div>
                            <a href="book-details.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Review Modal -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeReviewModal()">&times;</span>
            <h2 style="margin-bottom: 1.5rem;">
                <i class="fas fa-star"></i> Write a Review
            </h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 0.5rem; display: block;">Your Rating</label>
                    <div class="rating-selector">
                        <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" required>
                            <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars"><i class="fas fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="review_text" style="font-weight: 600; margin-bottom: 0.5rem; display: block;">Your Review</label>
                    <textarea class="form-control" id="review_text" name="review_text" rows="5" 
                              placeholder="Share your thoughts about this book..." required></textarea>
                </div>
                <button type="submit" name="submit_review" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                    <i class="fas fa-paper-plane"></i> Submit Review
                </button>
            </form>
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
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div style="display: flex; gap: 1rem;">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-telegram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Ethiopian E-Book Store. All rights reserved. 🇪🇹</p>
        </div>
    </footer>
    
    <script>
        // Review Modal Functions
        function openReviewModal() {
            document.getElementById('reviewModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reviewModal');
            if (event.target == modal) {
                closeReviewModal();
            }
        }

        // Star rating hover effect
        document.querySelectorAll('.rating-selector label').forEach(label => {
            label.addEventListener('mouseover', function() {
                const stars = Array.from(this.parentElement.children).filter(el => el.tagName === 'LABEL');
                const index = stars.indexOf(this);
                stars.forEach((star, i) => {
                    if (i >= index) {
                        star.style.color = '#FFD700';
                    }
                });
            });

            label.addEventListener('mouseout', function() {
                const checked = document.querySelector('input[name="rating"]:checked');
                if (!checked) {
                    document.querySelectorAll('.rating-selector label').forEach(l => l.style.color = '#ddd');
                } else {
                    const checkedValue = checked.value;
                    document.querySelectorAll('.rating-selector label').forEach((l, i) => {
                        l.style.color = (5 - i) <= checkedValue ? '#FFD700' : '#ddd';
                    });
                }
            });
        });
    </script>
</body>
</html>