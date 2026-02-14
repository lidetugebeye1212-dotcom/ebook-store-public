<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require admin login
Auth::requireAdmin();

$db = Database::getInstance();
$userId = SessionManager::getUserId();

// Get statistics
$stats = [];

// Total books
$result = $db->query("SELECT COUNT(*) as count FROM books");
$stats['books'] = $result->get_result()->fetch_assoc()['count'];

// Total Ethiopian books
$result = $db->query("SELECT COUNT(*) as count FROM books WHERE country = 'Ethiopia'");
$stats['ethiopian_books'] = $result->get_result()->fetch_assoc()['count'];

// Total users
$result = $db->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'");
$stats['users'] = $result->get_result()->fetch_assoc()['count'];

// Total orders
$result = $db->query("SELECT COUNT(*) as count FROM orders");
$stats['orders'] = $result->get_result()->fetch_assoc()['count'];

// Total revenue
$result = $db->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
$stats['revenue'] = $result->get_result()->fetch_assoc()['total'] ?? 0;

// Recent orders
$ordersSql = "SELECT o.*, u.username, u.full_name 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              ORDER BY o.created_at DESC 
              LIMIT 5";
$ordersResult = $db->query($ordersSql);
$recentOrders = $ordersResult->get_result();

// Recent users
$usersSql = "SELECT * FROM users WHERE user_type = 'customer' ORDER BY created_at DESC LIMIT 5";
$usersResult = $db->query($usersSql);
$recentUsers = $usersResult->get_result();

// Top selling books
$topBooksSql = "SELECT b.id, b.title, b.author, b.price, COUNT(od.id) as sales_count
                FROM books b
                LEFT JOIN order_details od ON b.id = od.book_id
                LEFT JOIN orders o ON od.order_id = o.id AND o.status = 'completed'
                GROUP BY b.id
                ORDER BY sales_count DESC
                LIMIT 5";
$topBooksResult = $db->query($topBooksSql);
$topBooks = $topBooksResult->get_result();

// Books by language
$langStatsSql = "SELECT language, COUNT(*) as count 
                 FROM books 
                 WHERE country = 'Ethiopia' 
                 GROUP BY language 
                 ORDER BY count DESC";
$langStatsResult = $db->query($langStatsSql);
$langStats = $langStatsResult->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ethiopian E-Book Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/admin.css">
    
    <!-- Ethiopian Flag Favicon -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
    
    <style>
        /* Additional Admin Dashboard Styles */
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(108, 92, 231, 0.3);
        }

        .welcome-banner h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .welcome-banner p {
            opacity: 0.95;
            font-size: 1.1rem;
        }

        .welcome-banner .date {
            background: rgba(255,255,255,0.2);
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(108, 92, 231, 0.15);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            letter-spacing: 1px;
        }

        .stat-details small {
            color: #00b894;
            font-size: 0.8rem;
            display: block;
            margin-top: 0.3rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
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

        .card-header a {
            color: #6c5ce7;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .card-header a:hover {
            color: #a463f5;
            transform: translateX(5px);
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.3s;
        }

        .order-item:hover {
            background: #f8f9fa;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: 10px;
        }

        .order-info {
            flex: 1;
        }

        .order-number {
            font-weight: 600;
            color: #2d3436;
            display: block;
            margin-bottom: 0.3rem;
        }

        .order-customer {
            color: #636e72;
            font-size: 0.85rem;
        }

        .order-date {
            color: #636e72;
            font-size: 0.8rem;
        }

        .order-status {
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 1rem;
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
        }

        .user-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 1rem;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            color: #2d3436;
            display: block;
            margin-bottom: 0.2rem;
        }

        .user-email {
            color: #636e72;
            font-size: 0.8rem;
        }

        .user-date {
            color: #636e72;
            font-size: 0.75rem;
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .action-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 1rem;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }

        .action-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(108, 92, 231, 0.5);
        }

        .action-item i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .action-item span {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .language-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }

        .lang-badge {
            background: #f1f1f1;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .lang-badge i {
            color: #6c5ce7;
        }

        .lang-badge span {
            background: #6c5ce7;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-size: 0.7rem;
            margin-left: 0.3rem;
        }

        .ethiopian-flag-badge {
            background: linear-gradient(135deg, #078930, #FCDD09, #DA121A);
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-left: 0.5rem;
        }

        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .welcome-banner {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions-grid {
                grid-template-columns: 1fr;
            }
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
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="books.php">
                    <i class="fas fa-book"></i> Books
                </a>
                <a href="add-book.php">
                    <i class="fas fa-plus-circle"></i> Add Book
                </a>
                <a href="categories.php">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="reviews.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>">
                      <i class="fas fa-star"></i> Reviews
                </a>
                <a href="users.php">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="reports.php">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="logout.php" style="margin-top: 2rem;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <div class="sidebar-footer" style="padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); margin-top: auto;">
                <div style="display: flex; align-items: center; gap: 0.5rem; color: rgba(255,255,255,0.7);">
                    <i class="fas fa-flag"></i>
                    <span style="font-size: 0.85rem;">Ethiopian Edition</span>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div>
                    <h1>Welcome back, <?php echo $_SESSION['full_name'] ?? 'Admin'; ?>! 👋</h1>
                    <p>Here's what's happening with your Ethiopian eBook store today.</p>
                </div>
                <div class="date">
                    <i class="fas fa-calendar-alt"></i> 
                    <?php echo date('l, F j, Y'); ?>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['books']; ?></h3>
                        <p>Total Books</p>
                        <small>
                            <i class="fas fa-flag"></i> <?php echo $stats['ethiopian_books']; ?> Ethiopian
                        </small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['users']; ?></h3>
                        <p>Registered Users</p>
                        <small>
                            <i class="fas fa-user-plus"></i> +12 this month
                        </small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['orders']; ?></h3>
                        <p>Total Orders</p>
                        <small>
                            <i class="fas fa-check-circle"></i> 85% completed
                        </small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-details">
                        <h3>$<?php echo number_format($stats['revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                        <small>
                            <i class="fas fa-chart-line"></i> +23% vs last month
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Recent Orders -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-clock"></i> Recent Orders</h2>
                        <a href="orders.php">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <?php if ($recentOrders->num_rows > 0): ?>
                        <?php while($order = $recentOrders->fetch_assoc()): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <span class="order-number">#<?php echo $order['order_number']; ?></span>
                                    <span class="order-customer"><?php echo $order['full_name'] ?? $order['username']; ?></span>
                                    <span class="order-date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                                <span class="order-amount">$<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #636e72;">
                            <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No orders yet</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Users -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-user-plus"></i> New Users</h2>
                        <a href="users.php">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <?php if ($recentUsers->num_rows > 0): ?>
                        <?php while($user = $recentUsers->fetch_assoc()): ?>
                            <div class="user-item">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                                <div class="user-info">
                                    <span class="user-name"><?php echo $user['full_name'] ?: $user['username']; ?></span>
                                    <span class="user-email"><?php echo $user['email']; ?></span>
                                    <span class="user-date">Joined <?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #636e72;">
                            <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No users yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Second Row -->
            <div class="dashboard-grid">
                <!-- Top Selling Books -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-chart-line"></i> Top Selling Books</h2>
                        <a href="books.php">Manage Books <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <?php if ($topBooks->num_rows > 0): ?>
                        <?php while($book = $topBooks->fetch_assoc()): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <span class="order-number"><?php echo Functions::truncateText($book['title'], 30); ?></span>
                                    <span class="order-customer">by <?php echo $book['author']; ?></span>
                                </div>
                                <span class="order-amount">$<?php echo number_format($book['price'], 2); ?></span>
                                <span style="background: #6c5ce7; color: white; padding: 0.2rem 0.5rem; border-radius: 5px; font-size: 0.75rem; margin-left: 1rem;">
                                    <?php echo $book['sales_count'] ?? 0; ?> sold
                                </span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #636e72;">
                            <i class="fas fa-book" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No sales data yet</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Language Statistics -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-language"></i> Ethiopian Books by Language</h2>
                    </div>
                    
                    <?php if ($langStats->num_rows > 0): ?>
                        <div class="language-stats">
                            <?php while($lang = $langStats->fetch_assoc()): ?>
                                <div class="lang-badge">
                                    <i class="fas fa-book"></i>
                                    <?php echo $lang['language']; ?>
                                    <span><?php echo $lang['count']; ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div style="margin-top: 2rem;">
                            <div style="background: #f1f1f1; height: 10px; border-radius: 10px; overflow: hidden;">
                                <div style="width: 75%; height: 100%; background: linear-gradient(90deg, #6c5ce7, #a463f5);"></div>
                            </div>
                            <p style="text-align: center; margin-top: 0.5rem; color: #636e72; font-size: 0.85rem;">
                                75% of your collection is in Ethiopian languages
                            </p>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #636e72;">
                            <i class="fas fa-language" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No language data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                </div>
                
                <div class="quick-actions-grid">
                    <a href="add-book.php" class="action-item">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add New Book</span>
                    </a>
                    
                    <a href="add-ethiopian-book.php" class="action-item" style="background: linear-gradient(135deg, #078930, #FCDD09, #DA121A);">
                        <i class="fas fa-flag"></i>
                        <span>Add Ethiopian Book</span>
                    </a>
                    
                    <a href="categories.php" class="action-item" style="background: linear-gradient(135deg, #00b894, #00cec9);">
                        <i class="fas fa-tags"></i>
                        <span>Manage Categories</span>
                    </a>
                    
                    <a href="users.php" class="action-item" style="background: linear-gradient(135deg, #e17055, #d63031);">
                        <i class="fas fa-users"></i>
                        <span>View Users</span>
                    </a>
                    
                    <a href="orders.php" class="action-item" style="background: linear-gradient(135deg, #fdcb6e, #e17055);">
                        <i class="fas fa-truck"></i>
                        <span>Process Orders</span>
                    </a>
                    
                    <a href="reports.php" class="action-item" style="background: linear-gradient(135deg, #6c5ce7, #a463f5);">
                        <i class="fas fa-chart-pie"></i>
                        <span>View Reports</span>
                    </a>
                </div>
            </div>
            
            <!-- Ethiopian Books Spotlight -->
            <div class="dashboard-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h2 style="color: white; margin-bottom: 0.5rem;">
                            <i class="fas fa-flag"></i> Ethiopian Literature Spotlight
                        </h2>
                        <p style="opacity: 0.95;">Promote Ethiopian authors and books to your readers</p>
                    </div>
                    <div style="font-size: 4rem; opacity: 0.3;">🇪🇹</div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 2rem;">
                    <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 10px;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📚</div>
                        <h4 style="margin-bottom: 0.3rem;">42</h4>
                        <p style="font-size: 0.85rem; opacity: 0.9;">Amharic Books</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 10px;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📖</div>
                        <h4 style="margin-bottom: 0.3rem;">18</h4>
                        <p style="font-size: 0.85rem; opacity: 0.9;">Oromo Books</p>
                    </div>
                    <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 10px;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">✍️</div>
                        <h4 style="margin-bottom: 0.3rem;">25</h4>
                        <p style="font-size: 0.85rem; opacity: 0.9;">Ethiopian Authors</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh stats every 30 seconds (optional)
        // setInterval(() => {
        //     location.reload();
        // }, 30000);
        
        // Tooltips
        document.querySelectorAll('[data-tooltip]').forEach(el => {
            el.addEventListener('mouseenter', e => {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = e.target.dataset.tooltip;
                tooltip.style.cssText = `
                    position: absolute;
                    background: #333;
                    color: white;
                    padding: 5px 10px;
                    border-radius: 5px;
                    font-size: 12px;
                    z-index: 1000;
                `;
                document.body.appendChild(tooltip);
                
                const rect = e.target.getBoundingClientRect();
                tooltip.style.top = rect.bottom + window.scrollY + 5 + 'px';
                tooltip.style.left = rect.left + window.scrollX + 'px';
                
                e.target.addEventListener('mouseleave', () => tooltip.remove(), { once: true });
            });
        });
    </script>
</body>
</html>