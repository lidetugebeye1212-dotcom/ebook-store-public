<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireLogin();

$db = Database::getInstance();
$userId = SessionManager::getUserId();

// Get user details
$userSql = "SELECT * FROM users WHERE id = ?";
$userStmt = $db->query($userSql, [$userId]);
$user = $userStmt->get_result()->fetch_assoc();

// Get recent orders
$ordersSql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$ordersStmt = $db->query($ordersSql, [$userId]);
$recentOrders = $ordersStmt->get_result();

// Get total spent
$totalSql = "SELECT SUM(total_amount) as total FROM orders WHERE user_id = ? AND status = 'completed'";
$totalStmt = $db->query($totalSql, [$userId]);
$totalSpent = $totalStmt->get_result()->fetch_assoc()['total'] ?? 0;

// Get total books purchased
$booksSql = "SELECT COUNT(DISTINCT book_id) as total FROM order_details od 
             JOIN orders o ON od.order_id = o.id 
             WHERE o.user_id = ? AND o.status = 'completed'";
$booksStmt = $db->query($booksSql, [$userId]);
$totalBooks = $booksStmt->get_result()->fetch_assoc()['total'] ?? 0;

// Get cart count
$cartSql = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
$cartStmt = $db->query($cartSql, [$userId]);
$cartCount = $cartStmt->get_result()->fetch_assoc()['total'] ?? 0;

// Get Ethiopian recommendations
$recommendSql = "SELECT * FROM books WHERE country = 'Ethiopia' AND is_featured = 1 LIMIT 3";
$recommendStmt = $db->query($recommendSql);
$recommendations = $recommendStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Ethiopian E-Book Store</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Main CSS (FIXED PATHS) -->
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/responsive.css">
    
    <!-- Ethiopian Flag Favicon -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
    
    <style>
        /* ============================================
           USER DASHBOARD STYLES
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
            line-height: 1.6;
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

        /* Dashboard Container */
        .dashboard-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Welcome Section */
        .dashboard-header {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(108, 92, 231, 0.3);
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '🇪🇹';
            position: absolute;
            bottom: -20px;
            right: 20px;
            font-size: 150px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .welcome-section h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .welcome-section p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        /* Statistics Cards */
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            transition: all 0.3s;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(108, 92, 231, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-right: 1.5rem;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            box-shadow: 0 10px 20px rgba(108, 92, 231, 0.3);
        }

        .stat-details h3 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            color: #2d3436;
        }

        .stat-details p {
            color: #636e72;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        /* Dashboard Cards */
        .dashboard-card {
            background: white;
            border-radius: 20px;
            padding: 1.8rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .dashboard-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .card-header h2 {
            font-size: 1.3rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-header h2 i {
            color: #6c5ce7;
        }

        .btn-link {
            color: #6c5ce7;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-link:hover {
            color: #a463f5;
            transform: translateX(5px);
        }

        /* Orders List */
        .orders-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.3s;
        }

        .order-item:hover {
            background: #f8f9fa;
            border-radius: 10px;
            padding-left: 1.5rem;
        }

        .order-info {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .order-number {
            font-weight: 600;
            color: #2d3436;
            font-size: 0.95rem;
        }

        .order-date {
            color: #636e72;
            font-size: 0.8rem;
        }

        .order-status {
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: rgba(253, 203, 110, 0.2);
            color: #fdcb6e;
        }

        .status-processing {
            background: rgba(108, 92, 231, 0.1);
            color: #6c5ce7;
        }

        .status-completed {
            background: rgba(0, 184, 148, 0.1);
            color: #00b894;
        }

        .status-cancelled {
            background: rgba(214, 48, 49, 0.1);
            color: #d63031;
        }

        .order-amount {
            font-weight: 700;
            color: #2d3436;
            font-size: 1.1rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #636e72;
        }

        .empty-state p {
            margin-bottom: 1rem;
        }

        /* Recommendations List */
        .recommendations-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .recommendation-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 15px;
            transition: all 0.3s;
        }

        .recommendation-item:hover {
            transform: translateX(5px);
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .rec-book-cover {
            width: 60px;
            height: 80px;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }

        .rec-book-info {
            flex: 1;
        }

        .rec-book-info h4 {
            font-size: 1rem;
            margin-bottom: 0.3rem;
            color: #2d3436;
        }

        .rec-author {
            color: #636e72;
            font-size: 0.8rem;
            margin-bottom: 0.3rem;
        }

        .rec-price {
            font-weight: 600;
            color: #6c5ce7;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .btn-small {
            padding: 0.3rem 1rem;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.8rem;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }

        /* Settings List */
        .settings-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .setting-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .setting-item:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .setting-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .setting-info {
            flex: 1;
        }

        .setting-info h4 {
            font-size: 1rem;
            margin-bottom: 0.2rem;
            color: #2d3436;
        }

        .setting-info p {
            color: #636e72;
            font-size: 0.8rem;
        }

        /* Quick Actions Grid */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .action-item {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            padding: 1.2rem 0.5rem;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }

        .action-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(108, 92, 231, 0.5);
        }

        .action-item i {
            font-size: 1.5rem;
        }

        .action-item span {
            font-size: 0.8rem;
            font-weight: 500;
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
            color: white;
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

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            color: #b2bec3;
            font-size: 1.5rem;
            transition: all 0.3s;
        }

        .social-links a:hover {
            color: #6c5ce7;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #b2bec3;
        }

        /* Ethiopian Badge */
        .ethiopian-badge {
            display: inline-block;
            background: linear-gradient(135deg, #078930, #FCDD09, #DA121A);
            color: white;
            padding: 0.2rem 0.8rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card, .dashboard-card {
            animation: fadeInUp 0.5s ease;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .dashboard-grid {
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
                gap: 1rem;
            }
            
            .dashboard-header {
                padding: 2rem 1rem;
            }
            
            .welcome-section h1 {
                font-size: 2rem;
            }
            
            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .social-links {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                padding: 1.2rem;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .recommendation-item {
                flex-direction: column;
                text-align: center;
            }
            
            .rec-book-cover {
                margin: 0 auto;
            }
        }

        /* Ethiopian Flag Colors */
        .ethiopian-gradient {
            background: linear-gradient(135deg, #078930, #FCDD09, #DA121A);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #6c5ce7, #a463f5);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6c5ce7;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Ethiopian E-Book Store</div>
            <ul class="nav-links">
                <li><a href="../public/index.php">Home</a></li>
                <li><a href="../public/categories.php">Categories</a></li>
                <li><a href="../public/search.php">Search</a></li>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="cart.php">Cart <?php echo $cartCount > 0 ? '<span style="background: #6c5ce7; color: white; padding: 0.2rem 0.5rem; border-radius: 50px; font-size: 0.7rem; margin-left: 0.3rem;">'.$cartCount.'</span>' : ''; ?></a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="dashboard-container">
        <!-- Welcome Banner -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1>Welcome back, <?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>! 👋</h1>
                <p>Manage your account, view orders, and discover new Ethiopian books</p>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $totalBooks; ?></h3>
                    <p>Books Purchased</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-details">
                    <h3>$<?php echo number_format($totalSpent, 2); ?></h3>
                    <p>Total Spent</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $cartCount; ?></h3>
                    <p>Items in Cart</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-flag"></i>
                </div>
                <div class="stat-details">
                    <h3>Ethiopian</h3>
                    <p>Local Books</p>
                </div>
            </div>
        </div>
        
        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Recent Orders -->
            <div class="dashboard-card recent-orders">
                <div class="card-header">
                    <h2><i class="fas fa-clock"></i> Recent Orders</h2>
                    <a href="orders.php" class="btn-link">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <?php if ($recentOrders->num_rows > 0): ?>
                    <div class="orders-list">
                        <?php while($order = $recentOrders->fetch_assoc()): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <span class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                                    <span class="order-date">
                                        <i class="far fa-calendar-alt"></i> 
                                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                    </span>
                                </div>
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                                <span class="order-amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag" style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
                        <p>No orders yet</p>
                        <a href="../public/index.php" class="btn-small">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Ethiopian Recommendations -->
            <div class="dashboard-card recommendations">
                <div class="card-header">
                    <h2><i class="fas fa-flag"></i> Ethiopian Books You May Like</h2>
                </div>
                
                <div class="recommendations-list">
                    <?php if ($recommendations->num_rows > 0): ?>
                        <?php while($book = $recommendations->fetch_assoc()): ?>
                            <div class="recommendation-item">
                                <div class="rec-book-cover">
                                    <?php if(isset($book['cover_image']) && $book['cover_image']): ?>
                                        <img src="/ebook-store/assets/uploads/covers/<?php echo $book['cover_image']; ?>" 
                                             alt="<?php echo htmlspecialchars($book['title']); ?>"
                                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                    <?php else: ?>
                                        <span>📚</span>
                                    <?php endif; ?>
                                </div>
                                <div class="rec-book-info">
                                    <h4><?php echo htmlspecialchars(Functions::truncateText($book['title'], 30)); ?></h4>
                                    <p class="rec-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                                    <p class="rec-price">$<?php echo number_format($book['price'], 2); ?></p>
                                    <a href="../public/book-details.php?id=<?php echo $book['id']; ?>" class="btn-small">View</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-book-open" style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
                            <p>No recommendations available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Account Settings Section -->
<div class="dashboard-card account-settings">
    <div class="card-header">
        <h2><i class="fas fa-user-cog"></i> Account Settings</h2>
    </div>
    
    <div class="settings-list">
        <a href="profile.php" class="setting-item" style="text-decoration: none;">
            <div class="setting-icon">
                <i class="fas fa-user"></i>
            </div>
            <div class="setting-info">
                <h4>Profile Information</h4>
                <p>Update your personal details</p>
            </div>
            <i class="fas fa-chevron-right" style="color: #6c5ce7;"></i>
        </a>
        
        <a href="change-password.php" class="setting-item" style="text-decoration: none;">
            <div class="setting-icon">
                <i class="fas fa-lock"></i>
            </div>
            <div class="setting-info">
                <h4>Change Password</h4>
                <p>Update your password</p>
            </div>
            <i class="fas fa-chevron-right" style="color: #6c5ce7;"></i>
        </a>
        
        <a href="notifications.php" class="setting-item" style="text-decoration: none;">
            <div class="setting-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="setting-info">
                <h4>Notifications</h4>
                <p>Manage email preferences</p>
            </div>
            <i class="fas fa-chevron-right" style="color: #6c5ce7;"></i>
        </a>
        
        <a href="downloads.php" class="setting-item" style="text-decoration: none;">
            <div class="setting-icon">
                <i class="fas fa-download"></i>
            </div>
            <div class="setting-info">
                <h4>My Downloads</h4>
                <p>Access your purchased books</p>
            </div>
            <i class="fas fa-chevron-right" style="color: #6c5ce7;"></i>
        </a>
    </div>
</div>
            
            <!-- Quick Actions -->
            <div class="dashboard-card quick-actions">
                <div class="card-header">
                    <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                </div>
                
                <div class="actions-grid">
                    <a href="../public/search.php" class="action-item">
                        <i class="fas fa-search"></i>
                        <span>Find Books</span>
                    </a>
                    
                    <a href="cart.php" class="action-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>View Cart</span>
                    </a>
                    
                    <a href="../public/categories.php?name=Amharic%20Fiction" class="action-item">
                        <i class="fas fa-flag"></i>
                        <span>Ethiopian Lit</span>
                    </a>
                    
                    <a href="orders.php" class="action-item">
                        <i class="fas fa-history"></i>
                        <span>Order History</span>
                    </a>
                    
                    <a href="#" class="action-item">
                        <i class="fas fa-gift"></i>
                        <span>Gift Cards</span>
                    </a>
                    
                    <a href="support.php" class="action-item">
                        <i class="fas fa-headset"></i>
                        <span>Support</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Ethiopian E-Book Store</h3>
                <p>Ethiopia's premier digital reading destination. Discover thousands of Ethiopian and international e-books in multiple languages.</p>
                <div style="margin-top: 1rem;">
                    <span class="ethiopian-badge">🇪🇹 Ethiopian Owned</span>
                </div>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="../public/about.php">About Us</a></li>
                    <li><a href="../public/contact.php">Contact</a></li>
                    <li><a href="../public/privacy.php">Privacy Policy</a></li>
                    <li><a href="../public/terms.php">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div class="social-links">
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
</body>
</html>