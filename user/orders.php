<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireLogin();

$db = Database::getInstance();
$userId = SessionManager::getUserId();

// Get all orders with details
$ordersSql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$ordersStmt = $db->query($ordersSql, [$userId]);
$orders = $ordersStmt->get_result();

// Get order details for a specific order if requested
$selectedOrder = isset($_GET['order']) ? $_GET['order'] : null;
$orderDetails = null;
$orderItems = null;

if ($selectedOrder) {
    // Get order details
    $detailsSql = "SELECT o.*, u.username, u.full_name, u.email
                   FROM orders o
                   JOIN users u ON o.user_id = u.id
                   WHERE o.order_number = ? AND o.user_id = ?";
    $detailsStmt = $db->query($detailsSql, [$selectedOrder, $userId]);
    $orderDetails = $detailsStmt->get_result()->fetch_assoc();
    
    // Get order items
    if ($orderDetails) {
        $itemsSql = "SELECT od.*, b.title, b.author, b.cover_image, b.id as book_id
                     FROM order_details od
                     JOIN books b ON od.book_id = b.id
                     WHERE od.order_id = ?";
        $itemsStmt = $db->query($itemsSql, [$orderDetails['id']]);
        $orderItems = $itemsStmt->get_result();
    }
}

// Get Ethiopian categories for suggestions
$catSql = "SELECT * FROM categories WHERE name LIKE '%Ethiopian%' OR name LIKE '%Amharic%' LIMIT 4";
$catStmt = $db->query($catSql);
$categories = $catStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Ethiopian E-Book Store</title>
    
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
        /* Orders Page Styles */
        .orders-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            padding: 2rem 0;
        }

        .orders-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .orders-header {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 30px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(108, 92, 231, 0.3);
        }

        .orders-header::before {
            content: '📦';
            position: absolute;
            bottom: -30px;
            right: 20px;
            font-size: 150px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .orders-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .orders-header p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        /* Order Details View */
        .order-details-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease;
        }

        .order-details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .order-details-header h2 {
            font-size: 1.8rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .order-details-header h2 i {
            color: #6c5ce7;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .info-item {
            text-align: center;
        }

        .info-label {
            color: #636e72;
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3436;
        }

        .info-value.status {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
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

        .order-items {
            margin: 2rem 0;
        }

        .order-items h3 {
            margin-bottom: 1rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .order-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .order-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.2);
        }

        .item-cover {
            width: 60px;
            height: 80px;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }

        .item-details {
            flex: 1;
        }

        .item-title {
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 0.3rem;
        }

        .item-author {
            color: #636e72;
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 600;
            color: #6c5ce7;
        }

        .download-btn {
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }

        .download-btn.disabled {
            background: #ccc;
            pointer-events: none;
            opacity: 0.6;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: #f1f1f1;
            color: #2d3436;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #e1e1e1;
            transform: translateX(-5px);
        }

        /* Orders List */
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .order-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(108, 92, 231, 0.15);
        }

        .order-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .order-number {
            font-weight: 700;
            color: #6c5ce7;
            font-size: 1.2rem;
        }

        .order-date {
            color: #636e72;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .order-status {
            padding: 0.4rem 1.2rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .order-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d3436;
        }

        .order-items-preview {
            margin: 1rem 0;
            color: #636e72;
        }

        .view-details-btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .view-details-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }

        /* No Orders */
        .no-orders {
            background: white;
            border-radius: 20px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .no-orders i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 1.5rem;
        }

        .no-orders h2 {
            font-size: 2rem;
            color: #2d3436;
            margin-bottom: 1rem;
        }

        .category-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            margin: 2rem 0;
        }

        .chip {
            padding: 0.8rem 1.5rem;
            background: #f8f9fa;
            border-radius: 50px;
            text-decoration: none;
            color: #2d3436;
            font-weight: 500;
            transition: all 0.3s;
        }

        .chip:hover {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .order-info-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .order-details-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .order-card-header {
                flex-direction: column;
                gap: 0.5rem;
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
                <li><a href="../public/index.php">Home</a></li>
                <li><a href="../public/categories.php">Categories</a></li>
                <li><a href="../public/search.php">Search</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="orders.php" class="active">My Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="orders-page">
        <div class="orders-container">
            <div class="orders-header">
                <h1><i class="fas fa-shopping-bag"></i> My Orders</h1>
                <p>View and manage your book purchases</p>
            </div>

            <?php if ($selectedOrder && $orderDetails): ?>
                <!-- Order Details View -->
                <div class="order-details-container">
                    <div class="order-details-header">
                        <h2>
                            <i class="fas fa-receipt"></i> 
                            Order #<?php echo htmlspecialchars($selectedOrder); ?>
                        </h2>
                        <a href="orders.php" class="back-btn">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                    </div>

                    <div class="order-info-grid">
                        <div class="info-item">
                            <div class="info-label">Order Date</div>
                            <div class="info-value">
                                <i class="far fa-calendar-alt"></i> 
                                <?php echo date('F d, Y', strtotime($orderDetails['created_at'])); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Order Status</div>
                            <div class="info-value status status-<?php echo $orderDetails['status']; ?>">
                                <?php echo ucfirst($orderDetails['status']); ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Payment Method</div>
                            <div class="info-value">
                                <i class="fas fa-credit-card"></i> 
                                <?php echo $orderDetails['payment_method']; ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Total Amount</div>
                            <div class="info-value" style="color: #6c5ce7; font-weight: 700;">
                                $<?php echo number_format($orderDetails['total_amount'], 2); ?>
                            </div>
                        </div>
                    </div>

                    <div class="order-items">
                        <h3>
                            <i class="fas fa-books"></i> 
                            Items in this order (<?php echo $orderItems ? $orderItems->num_rows : 0; ?>)
                        </h3>

                        <?php if ($orderItems && $orderItems->num_rows > 0): ?>
                            <?php while($item = $orderItems->fetch_assoc()): ?>
                                <div class="order-item">
                                    <div class="item-cover">
                                        <?php if(isset($item['cover_image']) && $item['cover_image']): ?>
                                            <img src="/ebook-store/assets/uploads/covers/<?php echo $item['cover_image']; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                                        <?php else: ?>
                                            <span>📚</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="item-details">
                                        <div class="item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <div class="item-author">by <?php echo htmlspecialchars($item['author']); ?></div>
                                        <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                                    </div>
                                    
                                    <?php if ($orderDetails['status'] === 'completed'): ?>
                                        <!-- In your order details view -->
                                      <!-- In your order details view -->
<?php if ($orderDetails['status'] === 'completed'): ?>
    <a href="force-download.php?book_id=<?php echo $item['book_id']; ?>" class="download-btn">
    <i class="fas fa-download"></i> Download PDF
</a>
<?php endif; ?>
                                    <?php else: ?>
                                        <span class="download-btn disabled">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                        <p style="color: #636e72;">
                            <i class="fas fa-envelope"></i> 
                            A confirmation was sent to <?php echo htmlspecialchars($orderDetails['email']); ?>
                        </p>
                    </div>
                </div>

            <?php elseif ($orders->num_rows > 0): ?>
                <!-- Orders List View -->
                <div class="orders-list">
                    <?php while($order = $orders->fetch_assoc()): ?>
                        <div class="order-card">
                            <div class="order-card-header">
                                <div>
                                    <span class="order-number">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                                    <span class="order-date">
                                        <i class="far fa-calendar-alt"></i> 
                                        <?php echo date('F d, Y', strtotime($order['created_at'])); ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                    <span class="order-total">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>

                            <?php
                            // Get preview of items in this order
                            $previewSql = "SELECT b.title, b.author 
                                         FROM order_details od 
                                         JOIN books b ON od.book_id = b.id 
                                         WHERE od.order_id = ? 
                                         LIMIT 3";
                            $previewStmt = $db->query($previewSql, [$order['id']]);
                            $previewItems = $previewStmt->get_result();
                            ?>

                            <div class="order-items-preview">
                                <?php while($item = $previewItems->fetch_assoc()): ?>
                                    <div>📚 <?php echo htmlspecialchars(Functions::truncateText($item['title'], 40)); ?></div>
                                <?php endwhile; ?>
                                
                                <?php
                                $countSql = "SELECT COUNT(*) as total FROM order_details WHERE order_id = ?";
                                $countStmt = $db->query($countSql, [$order['id']]);
                                $count = $countStmt->get_result()->fetch_assoc()['total'];
                                if ($count > 3): 
                                ?>
                                    <div style="color: #6c5ce7; margin-top: 0.5rem;">
                                        +<?php echo $count - 3; ?> more items
                                    </div>
                                <?php endif; ?>
                            </div>

                            <a href="?order=<?php echo urlencode($order['order_number']); ?>" class="view-details-btn">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>

            <?php else: ?>
                <!-- No Orders -->
                <div class="no-orders">
                    <i class="fas fa-shopping-bag"></i>
                    <h2>No orders yet</h2>
                    <p>Looks like you haven't purchased any books yet. Start exploring our Ethiopian collection!</p>
                    
                    <div class="category-chips">
                        <a href="../public/categories.php?name=Amharic%20Fiction" class="chip">
                            <i class="fas fa-book"></i> Amharic Fiction
                        </a>
                        <a href="../public/categories.php?name=Ethiopian%20History" class="chip">
                            <i class="fas fa-landmark"></i> Ethiopian History
                        </a>
                        <a href="../public/categories.php?name=Ethiopian%20Culture" class="chip">
                            <i class="fas fa-music"></i> Ethiopian Culture
                        </a>
                        <a href="../public/categories.php?name=Programming" class="chip">
                            <i class="fas fa-code"></i> Programming
                        </a>
                    </div>
                    
                    <a href="../public/index.php" class="btn btn-primary" style="margin-top: 2rem;">
                        <i class="fas fa-shopping-cart"></i> Start Shopping
                    </a>
                </div>
            <?php endif; ?>
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
                    <li><a href="../public/about.php">About Us</a></li>
                    <li><a href="../public/contact.php">Contact</a></li>
                    <li><a href="../public/privacy.php">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Ethiopian E-Book Store. All rights reserved. 🇪🇹</p>
        </div>
    </footer>
    <!-- Add this JavaScript at the bottom of orders.php -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Find all download buttons
    const downloadBtns = document.querySelectorAll('.download-btn');
    
    downloadBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default link behavior
            
            const url = this.href;
            
            // Create a hidden iframe to force download
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = url;
            document.body.appendChild(iframe);
            
            // Remove iframe after download starts
            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 5000);
        });
    });
});
</script>
</body>
</html>