<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();

// Handle review deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $reviewId = (int)$_GET['delete'];
    $bookId = isset($_GET['book']) ? (int)$_GET['book'] : 0;
    
    $deleteSql = "DELETE FROM book_reviews WHERE id = ?";
    $db->query($deleteSql, [$reviewId]);
    
    if ($bookId > 0) {
        Functions::redirect("reviews.php?deleted=1");
    } else {
        Functions::redirect("reviews.php?deleted=1");
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get total reviews count
$countSql = "SELECT COUNT(*) as total FROM book_reviews";
$countResult = $db->query($countSql);
$totalReviews = $countResult->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalReviews / $limit);

// Get all reviews with book and user info
$reviewsSql = "SELECT r.*, b.title as book_title, b.author as book_author, 
               u.username, u.full_name, u.email
               FROM book_reviews r
               JOIN books b ON r.book_id = b.id
               JOIN users u ON r.user_id = u.id
               ORDER BY r.created_at DESC
               LIMIT ? OFFSET ?";
$reviewsStmt = $db->query($reviewsSql, [$limit, $offset]);
$reviews = $reviewsStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/admin.css">
    <style>
        .rating-stars {
            color: #FFD700;
        }
        .review-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo SITE_NAME; ?></h2>
                <p>Admin Panel</p>
            </div>
            
            <div class="sidebar-menu">
                <a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="books.php"><i class="fas fa-book"></i> Books</a>
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                <a href="categories.php"><i class="fas fa-tags"></i> Categories</a>
                <a href="reviews.php" class="active"><i class="fas fa-star"></i> Reviews</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="admin-header">
                <h1>Manage Reviews</h1>
            </div>
            
            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Review deleted successfully!</div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $totalReviews; ?></h3>
                        <p>Total Reviews</p>
                    </div>
                </div>
                
                <?php
                $avgSql = "SELECT AVG(rating) as avg_rating FROM book_reviews";
                $avgResult = $db->query($avgSql);
                $avgRating = round($avgResult->get_result()->fetch_assoc()['avg_rating'] ?? 0, 1);
                ?>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $avgRating; ?> / 5</h3>
                        <p>Average Rating</p>
                    </div>
                </div>
                
                <?php
                $todaySql = "SELECT COUNT(*) as today FROM book_reviews WHERE DATE(created_at) = CURDATE()";
                $todayResult = $db->query($todaySql);
                $todayReviews = $todayResult->get_result()->fetch_assoc()['today'];
                ?>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $todayReviews; ?></h3>
                        <p>Today's Reviews</p>
                    </div>
                </div>
            </div>
            
            <!-- Reviews Table -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fas fa-list"></i> All Reviews</h2>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Book</th>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($reviews->num_rows > 0): ?>
                            <?php while($review = $reviews->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $review['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars(Functions::truncateText($review['book_title'], 40)); ?></strong>
                                        <br>
                                        <small>by <?php echo htmlspecialchars($review['book_author']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($review['full_name'] ?? $review['username']); ?>
                                        <br>
                                        <small><?php echo htmlspecialchars($review['email']); ?></small>
                                    </td>
                                    <td>
                                        <div class="rating-stars">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($i <= $review['rating']): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <br>
                                            <small>(<?php echo $review['rating']; ?>/5)</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="review-preview" title="<?php echo htmlspecialchars($review['review_text']); ?>">
                                            <?php echo htmlspecialchars(Functions::truncateText($review['review_text'], 50)); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                        <br>
                                        <small><?php echo date('h:i A', strtotime($review['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <a href="../public/book-details.php?id=<?php echo $review['book_id']; ?>" 
                                           class="btn-action btn-view" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?delete=<?php echo $review['id']; ?>" 
                                           class="btn-action btn-delete"
                                           onclick="return confirm('Delete this review?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-star" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>No reviews yet</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>