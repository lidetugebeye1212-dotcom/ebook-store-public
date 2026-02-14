<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireLogin();

$db = Database::getInstance();
$userId = SessionManager::getUserId();

$order_number = isset($_GET['order']) ? $_GET['order'] : '';

// Get order details
$orderSql = "SELECT o.*, u.username, u.full_name, u.email
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE o.order_number = ? AND o.user_id = ?";
$orderStmt = $db->query($orderSql, [$order_number, $userId]);
$order = $orderStmt->get_result()->fetch_assoc();

if (!$order) {
    Functions::redirect('orders.php');
}

// Get order items
$itemsSql = "SELECT od.*, b.title, b.author, b.cover_image
             FROM order_details od
             JOIN books b ON od.book_id = b.id
             WHERE od.order_id = ?";
$itemsStmt = $db->query($itemsSql, [$order['id']]);
$items = $itemsStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Ethiopian E-Book Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    
    <style>
        .success-container {
            max-width: 800px;
            margin: 3rem auto;
            padding: 0 2rem;
            text-align: center;
        }

        .success-card {
            background: white;
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            animation: slideUp 0.5s ease;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #00b894, #00cec9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 3rem;
            animation: pulse 2s infinite;
        }

        .success-title {
            font-size: 2.5rem;
            color: #00b894;
            margin-bottom: 1rem;
        }

        .order-number {
            font-size: 1.5rem;
            color: #6c5ce7;
            font-weight: 600;
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .order-details {
            text-align: left;
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e1e1e1;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #636e72;
            font-weight: 500;
        }

        .detail-value {
            font-weight: 600;
            color: #2d3436;
        }

        .items-list {
            margin: 2rem 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #e1e1e1;
        }

        .item-title {
            font-weight: 500;
        }

        .item-price {
            color: #6c5ce7;
            font-weight: 600;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
            margin: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(108, 92, 231, 0.3);
        }

        .btn-secondary {
            background: #f1f1f1;
            color: #2d3436;
        }

        .btn-secondary:hover {
            background: #e1e1e1;
            transform: translateY(-3px);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        .email-note {
            margin-top: 2rem;
            padding: 1rem;
            background: #fff3cd;
            border-radius: 10px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="success-title">Payment Successful!</h1>
            <p style="color: #636e72; margin-bottom: 2rem;">Thank you for your purchase. Your order has been confirmed.</p>
            
            <div class="order-number">
                Order #<?php echo htmlspecialchars($order['order_number']); ?>
            </div>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value"><?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value"><?php echo $order['payment_method']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color: #00b894;"><?php echo ucfirst($order['status']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value" style="color: #6c5ce7; font-size: 1.2rem;">$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
            
            <h3 style="margin: 2rem 0 1rem;">Order Summary</h3>
            <div class="items-list">
                <?php while($item = $items->fetch_assoc()): ?>
                    <div class="item-row">
                        <span class="item-title"><?php echo htmlspecialchars(Functions::truncateText($item['title'], 40)); ?> (x<?php echo $item['quantity']; ?>)</span>
                        <span class="item-price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="email-note">
                <i class="fas fa-envelope"></i> 
                A confirmation email has been sent to <?php echo htmlspecialchars($order['email']); ?>
            </div>
            
            <div style="margin-top: 2rem;">
                <a href="orders.php" class="btn btn-primary">
                    <i class="fas fa-eye"></i> View My Orders
                </a>
                <a href="../public/index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</body>
</html>