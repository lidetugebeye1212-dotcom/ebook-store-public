<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();
$message = '';
$error = '';

// Handle order status update
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = Functions::sanitize($_POST['status']);
    
    $updateSql = "UPDATE orders SET status = ? WHERE id = ?";
    $updateStmt = $db->query($updateSql, [$status, $orderId]);
    
    if ($updateStmt) {
        $message = "Order status updated successfully!";
    } else {
        $error = "Failed to update order status.";
    }
}

// Handle order deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $orderId = (int)$_GET['delete'];
    
    // First delete order details
    $db->query("DELETE FROM order_details WHERE order_id = ?", [$orderId]);
    // Then delete order
    $db->query("DELETE FROM orders WHERE id = ?", [$orderId]);
    
    Functions::redirect('orders.php?msg=deleted');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Filters
$status = isset($_GET['status']) ? Functions::sanitize($_GET['status']) : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search = isset($_GET['search']) ? Functions::sanitize($_GET['search']) : '';

// Build query
$countSql = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.id WHERE 1=1";
$ordersSql = "SELECT o.*, u.username, u.full_name, u.email,
              (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) as item_count
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE 1=1";
$params = [];

if ($status) {
    $countSql .= " AND o.status = ?";
    $ordersSql .= " AND o.status = ?";
    $params[] = $status;
}

if ($dateFrom) {
    $countSql .= " AND DATE(o.created_at) >= ?";
    $ordersSql .= " AND DATE(o.created_at) >= ?";
    $params[] = $dateFrom;
}

if ($dateTo) {
    $countSql .= " AND DATE(o.created_at) <= ?";
    $ordersSql .= " AND DATE(o.created_at) <= ?";
    $params[] = $dateTo;
}

if ($search) {
    $countSql .= " AND (o.order_number LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
    $ordersSql .= " AND (o.order_number LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Get total count for pagination
$countStmt = $db->query($countSql, $params);
$totalOrders = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);

// Add pagination to orders query
$ordersSql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$ordersStmt = $db->query($ordersSql, $params);
$orders = $ordersStmt->get_result();

// Get statistics
$stats = [];

// Total orders
$result = $db->query("SELECT COUNT(*) as count FROM orders");
$stats['total'] = $result->get_result()->fetch_assoc()['count'];

// Pending orders
$result = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$stats['pending'] = $result->get_result()->fetch_assoc()['count'];

// Completed orders
$result = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'");
$stats['completed'] = $result->get_result()->fetch_assoc()['count'];

// Total revenue
$result = $db->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
$stats['revenue'] = $result->get_result()->fetch_assoc()['total'] ?? 0;

// Today's orders
$result = $db->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
$stats['today'] = $result->get_result()->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Panel</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/admin.css">
    
    <!-- Ethiopian Flag Favicon -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
    
    <style>
        /* Additional Admin Styles */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(108, 92, 231, 0.2);
        }

        .stat-card .stat-icon {
            font-size: 2rem;
            color: #6c5ce7;
            margin-bottom: 0.5rem;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3436;
        }

        .stat-card .stat-label {
            color: #636e72;
            font-size: 0.9rem;
        }

        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 0.85rem;
            color: #636e72;
            margin-bottom: 0.3rem;
            font-weight: 500;
        }

        .filter-group input,
        .filter-group select {
            padding: 0.8rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #6c5ce7;
        }

        .filter-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .btn-filter {
            padding: 0.8rem 1.5rem;
            background: #6c5ce7;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-filter:hover {
            background: #5849c2;
            transform: translateY(-2px);
        }

        .btn-reset {
            padding: 0.8rem 1.5rem;
            background: #f1f1f1;
            color: #2d3436;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
        }

        .btn-reset:hover {
            background: #e1e1e1;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table th {
            background: #6c5ce7;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
        }

        .order-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f1f1;
        }

        .order-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
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

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.5rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
        }

        .btn-view {
            background: #6c5ce7;
        }

        .btn-edit {
            background: #00b894;
        }

        .btn-delete {
            background: #d63031;
        }

        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.2);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .page-link {
            padding: 0.5rem 1rem;
            background: white;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            text-decoration: none;
            color: #2d3436;
            transition: all 0.3s;
        }

        .page-link:hover,
        .page-link.active {
            background: #6c5ce7;
            color: white;
            border-color: #6c5ce7;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: rgba(0, 184, 148, 0.1);
            color: #00b894;
            border-left: 4px solid #00b894;
        }

        .alert-error {
            background: rgba(214, 48, 49, 0.1);
            color: #d63031;
            border-left: 4px solid #d63031;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .order-table {
                display: block;
                overflow-x: auto;
            }
            
            .filter-form {
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
                <a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="books.php"><i class="fas fa-book"></i> Books</a>
                <a href="add-book.php"><i class="fas fa-plus-circle"></i> Add Book</a>
                <a href="categories.php"><i class="fas fa-tags"></i> Categories</a>
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
                <a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a>
                <a href="reviews.php"><i class="fas fa-star"></i> Reviews</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="admin-header">
                <h1>Manage Orders</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
                           <!-- In your order details view - CORRECT VERSION -->
<?php if ($orderDetails['status'] === 'completed'): ?>
    <a href="download.php?book_id=<?php echo $item['book_id']; ?>" 
       class="download-btn" 
       target="_blank">
        <i class="fas fa-download"></i> Download PDF
    </a>
<?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Order deleted successfully!
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-value"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-value"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value"><?php echo $stats['completed']; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-value">$<?php echo number_format($stats['revenue'], 2); ?></div>
                    <div class="stat-label">Revenue</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-value"><?php echo $stats['today']; ?></div>
                    <div class="stat-label">Today</div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" action="" class="filter-form">
                    <div class="filter-group">
                        <label><i class="fas fa-search"></i> Search</label>
                        <input type="text" name="search" placeholder="Order #, Customer, Email" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-tag"></i> Status</label>
                        <select name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $status == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-calendar"></i> From Date</label>
                        <input type="date" name="date_from" value="<?php echo $dateFrom; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-calendar"></i> To Date</label>
                        <input type="date" name="date_to" value="<?php echo $dateTo; ?>">
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="orders.php" class="btn-reset">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Orders Table -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fas fa-list"></i> Orders List</h2>
                    <span>Total: <?php echo $totalOrders; ?> orders</span>
                </div>
                
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders->num_rows > 0): ?>
                            <?php while($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($order['full_name'] ?: $order['username']); ?>
                                        <br>
                                        <small style="color: #636e72;"><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                        <br>
                                        <small style="color: #636e72;"><?php echo date('h:i A', strtotime($order['created_at'])); ?></small>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="background: #6c5ce7; color: white; padding: 0.2rem 0.5rem; border-radius: 5px;">
                                            <?php echo $order['item_count']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $method = $order['payment_method'];
                                        if (strpos($method, 'CBE') !== false || strpos($method, 'Telebirr') !== false) {
                                            echo '<span style="background: linear-gradient(135deg, #078930, #FCDD09, #DA121A); color: white; padding: 0.2rem 0.5rem; border-radius: 5px; font-size: 0.8rem;">🇪🇹 ' . $method . '</span>';
                                        } else {
                                            echo $method;
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="status-badge status-<?php echo $order['status']; ?>" style="border: none; cursor: pointer; padding: 0.3rem 1rem;">
                                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view-order.php?id=<?php echo $order['id']; ?>" class="btn-action btn-view" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="invoice.php?id=<?php echo $order['id']; ?>" class="btn-action btn-edit" title="Print Invoice" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="?delete=<?php echo $order['id']; ?>" class="btn-action btn-delete" title="Delete Order" onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-shopping-cart" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem; display: block;"></i>
                                    <p style="color: #636e72;">No orders found</p>
                                    <?php if ($status || $dateFrom || $dateTo || $search): ?>
                                        <a href="orders.php" class="btn-filter" style="margin-top: 1rem; display: inline-block;">Clear Filters</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?>&status=<?php echo $status; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>&search=<?php echo urlencode($search); ?>" 
                               class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page+1; ?>&status=<?php echo $status; ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>