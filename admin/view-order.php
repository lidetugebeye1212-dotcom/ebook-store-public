<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    Functions::redirect('orders.php');
}

$orderId = (int)$_GET['id'];

// Get order details
$orderSql = "SELECT o.*, u.username, u.full_name, u.email, u.created_at as user_since
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE o.id = ?";
$orderStmt = $db->query($orderSql, [$orderId]);
$order = $orderStmt->get_result()->fetch_assoc();

if (!$order) {
    Functions::redirect('orders.php');
}

// Get order items
$itemsSql = "SELECT od.*, b.title, b.author, b.cover_image, b.isbn, b.language
             FROM order_details od
             JOIN books b ON od.book_id = b.id
             WHERE od.order_id = ?";
$itemsStmt = $db->query($itemsSql, [$orderId]);
$items = $itemsStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo SITE_NAME; ?></h2>
                <p>Admin Panel</p>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="books.php"><i class="fas fa-book"></i> Books</a>
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
                <a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="admin-header">
                <h1>Order Details #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                <a href="orders.php" class="btn btn-secondary">← Back to Orders</a>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Order Information -->
                <div class="admin-card">
                    <h2>Order Information</h2>
                    <table style="width: 100%;">
                        <tr><td><strong>Order Number:</strong></td><td>#<?php echo $order['order_number']; ?></td></tr>
                        <tr><td><strong>Order Date:</strong></td><td><?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></td></tr>
                        <tr><td><strong>Status:</strong></td><td><?php echo ucfirst($order['status']); ?></td></tr>
                        <tr><td><strong>Payment Method:</strong></td><td><?php echo $order['payment_method']; ?></td></tr>
                        <tr><td><strong>Total Amount:</strong></td><td>$<?php echo number_format($order['total_amount'], 2); ?></td></tr>
                    </table>
                </div>
                
                <!-- Customer Information -->
                <div class="admin-card">
                    <h2>Customer Information</h2>
                    <table style="width: 100%;">
                        <tr><td><strong>Name:</strong></td><td><?php echo $order['full_name'] ?: $order['username']; ?></td></tr>
                        <tr><td><strong>Username:</strong></td><td><?php echo $order['username']; ?></td></tr>
                        <tr><td><strong>Email:</strong></td><td><?php echo $order['email']; ?></td></tr>
                        <tr><td><strong>Customer Since:</strong></td><td><?php echo date('F d, Y', strtotime($order['user_since'])); ?></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="admin-card" style="margin-top: 2rem;">
                <h2>Order Items</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Author</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = $items->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $item['title']; ?></td>
                            <td><?php echo $item['author']; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr style="font-weight: bold;">
                            <td colspan="4" style="text-align: right;">Total:</td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>