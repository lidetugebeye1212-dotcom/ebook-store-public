<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireLogin();

$db = Database::getInstance();
$userId = SessionManager::getUserId();

// Add to cart
if (isset($_GET['add'])) {
    $bookId = (int)$_GET['add'];
    
    $sql = "INSERT INTO cart (user_id, book_id) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE quantity = quantity + 1";
    $db->query($sql, [$userId, $bookId]);
    
    Functions::redirect('cart.php');
}

// Remove from cart
if (isset($_GET['remove'])) {
    $cartId = (int)$_GET['remove'];
    $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $db->query($sql, [$cartId, $userId]);
    Functions::redirect('cart.php');
}

// Update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cartId => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            $db->query("DELETE FROM cart WHERE id = ? AND user_id = ?", [$cartId, $userId]);
        } else {
            $db->query("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?", [$quantity, $cartId, $userId]);
        }
    }
    Functions::redirect('cart.php');
}

// Get cart items - FIXED: Get result first, then fetch
$cartSql = "SELECT c.*, b.title, b.author, b.price, b.cover_image, b.id as book_id
            FROM cart c 
            JOIN books b ON c.book_id = b.id 
            WHERE c.user_id = ?";
$cartStmt = $db->query($cartSql, [$userId]);
$cartResult = $cartStmt->get_result(); // IMPORTANT: Get result first
$cartItems = $cartResult; // Now this is a mysqli_result object

// Calculate total
$totalSql = "SELECT SUM(b.price * c.quantity) as total 
             FROM cart c 
             JOIN books b ON c.book_id = b.id 
             WHERE c.user_id = ?";
$totalStmt = $db->query($totalSql, [$userId]);
$totalResult = $totalStmt->get_result();
$total = $totalResult->fetch_assoc()['total'] ?? 0;

// Get cart count for header
$cartCountSql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
$cartCountStmt = $db->query($cartCountSql, [$userId]);
$cartCountResult = $cartCountStmt->get_result();
$cartCount = $cartCountResult->fetch_assoc()['count'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Ethiopian E-Book Store</title>
    
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
        /* ============================================
           CART PAGE STYLES
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

        .cart-count {
            background: #6c5ce7;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 50px;
            font-size: 0.7rem;
            margin-left: 0.3rem;
        }

        /* Cart Container */
        .cart-container {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        .cart-header {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(108, 92, 231, 0.3);
            position: relative;
            overflow: hidden;
        }

        .cart-header::before {
            content: '🛒';
            position: absolute;
            bottom: -20px;
            right: 20px;
            font-size: 120px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .cart-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .cart-header p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        /* Cart Items */
        .cart-items {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.3s;
            animation: slideIn 0.5s ease;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item:hover {
            background: #f8f9fa;
            border-radius: 15px;
            transform: translateX(5px);
        }

        .cart-item-image {
            width: 100px;
            height: 130px;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            border-radius: 10px;
            margin-right: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
            overflow: hidden;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
            color: #2d3436;
        }

        .cart-item-author {
            color: #636e72;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }

        .cart-item-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #6c5ce7;
        }

        .cart-item-price small {
            font-size: 0.8rem;
            color: #636e72;
            font-weight: 400;
        }

        .cart-item-actions {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f1f1f1;
            padding: 0.3rem;
            border-radius: 50px;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: none;
            background: white;
            color: #6c5ce7;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .quantity-btn:hover {
            background: #6c5ce7;
            color: white;
            transform: scale(1.1);
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            border: none;
            background: transparent;
            font-weight: 600;
            font-size: 1rem;
        }

        .quantity-input:focus {
            outline: none;
        }

        .item-subtotal {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3436;
            min-width: 100px;
            text-align: right;
        }

        .remove-item {
            color: #d63031;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
            padding: 0.5rem;
            border-radius: 50%;
        }

        .remove-item:hover {
            background: rgba(214, 48, 49, 0.1);
            transform: scale(1.1);
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

        /* Cart Summary */
        .cart-summary {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .summary-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .summary-row.total {
            border-bottom: none;
            font-size: 1.3rem;
            font-weight: 700;
            color: #6c5ce7;
            padding-top: 1.5rem;
        }

        .summary-label {
            color: #636e72;
        }

        .summary-value {
            font-weight: 600;
        }

        .cart-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
            flex: 2;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.5);
        }

        .btn-secondary {
            background: #f1f1f1;
            color: #2d3436;
            flex: 1;
        }

        .btn-secondary:hover {
            background: #e1e1e1;
            transform: translateY(-3px);
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease;
        }

        .empty-cart i {
            font-size: 6rem;
            color: #ddd;
            margin-bottom: 2rem;
            animation: bounce 2s ease-in-out infinite;
        }

        .empty-cart h2 {
            font-size: 2.5rem;
            color: #2d3436;
            margin-bottom: 1rem;
        }

        .empty-cart p {
            color: #636e72;
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        .suggested-categories {
            margin: 2rem 0;
            padding: 2rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
        }

        .suggested-categories h3 {
            color: #2d3436;
            margin-bottom: 1.5rem;
        }

        .category-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
        }

        .chip {
            padding: 0.8rem 1.8rem;
            background: white;
            border-radius: 50px;
            text-decoration: none;
            color: #2d3436;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chip:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(108, 92, 231, 0.3);
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
        }

        .btn-large {
            padding: 1.2rem 3rem;
            font-size: 1.2rem;
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

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .cart-item {
                flex-wrap: wrap;
            }
            
            .cart-item-image {
                margin-bottom: 1rem;
            }
            
            .cart-item-actions {
                width: 100%;
                justify-content: space-between;
                margin-top: 1rem;
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
            }
            
            .cart-header h1 {
                font-size: 2rem;
            }
            
            .cart-actions {
                flex-direction: column;
            }
            
            .category-chips {
                flex-direction: column;
            }
            
            .chip {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .cart-item-image {
                margin-right: 0;
            }
            
            .cart-item-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .quantity-control {
                width: 100%;
                justify-content: center;
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
                <li><a href="cart.php" class="active">Cart <?php if($cartCount > 0): ?><span class="cart-count"><?php echo $cartCount; ?></span><?php endif; ?></a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="cart-container">
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>
        
        <?php if ($cartItems && $cartItems->num_rows > 0): ?>
            <form method="POST" action="">
                <div class="cart-items">
                    <?php 
                    $subtotal = 0;
                    while($item = $cartItems->fetch_assoc()): 
                        $itemTotal = $item['price'] * $item['quantity'];
                        $subtotal += $itemTotal;
                    ?>
                        <div class="cart-item" id="cart-item-<?php echo $item['id']; ?>">
                            <div class="cart-item-image">
                                <?php if(isset($item['cover_image']) && $item['cover_image']): ?>
                                    <img src="/ebook-store/assets/uploads/covers/<?php echo $item['cover_image']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <?php else: ?>
                                    <span>📚</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cart-item-details">
                                <h3 class="cart-item-title">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                    <?php if(isset($item['country']) && $item['country'] == 'Ethiopia'): ?>
                                        <span class="ethiopian-badge">🇪🇹 Ethiopian</span>
                                    <?php endif; ?>
                                </h3>
                                <p class="cart-item-author">by <?php echo htmlspecialchars($item['author']); ?></p>
                                <div class="cart-item-price" data-price="<?php echo $item['price']; ?>">
                                    $<?php echo number_format($item['price'], 2); ?>
                                </div>
                            </div>
                            
                            <div class="cart-item-actions">
                                <div class="quantity-control">
                                    <button type="button" class="quantity-btn" onclick="decrementQuantity(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                                           class="quantity-input" id="quantity-<?php echo $item['id']; ?>"
                                           value="<?php echo $item['quantity']; ?>" min="0" max="10" 
                                           onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                                    <button type="button" class="quantity-btn" onclick="incrementQuantity(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <div class="item-subtotal" id="subtotal-<?php echo $item['id']; ?>">
                                    $<?php echo number_format($itemTotal, 2); ?>
                                </div>
                                
                                <a href="?remove=<?php echo $item['id']; ?>" class="remove-item" onclick="return confirm('Remove this item from your cart?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="cart-summary">
                    <h3 class="summary-title"><i class="fas fa-receipt"></i> Order Summary</h3>
                    
                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value" id="subtotal">$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Shipping</span>
                        <span class="summary-value">Free</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span class="summary-label">Total</span>
                        <span class="summary-value" id="total">$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <div class="cart-actions">
                        <button type="submit" name="update_cart" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Update Cart
                        </button>
                        <a href="checkout.php" class="btn btn-primary">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <!-- Empty Cart - Beautiful Design -->
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any books to your cart yet.</p>
                
                <div class="suggested-categories">
                    <h3>📚 Explore Ethiopian Books:</h3>
                    <div class="category-chips">
                        <a href="../public/categories.php?name=Amharic%20Fiction" class="chip">
                            <i class="fas fa-book"></i> Amharic Fiction
                        </a>
                        <a href="../public/categories.php?name=Ethiopian%20Literature" class="chip">
                            <i class="fas fa-book-open"></i> Ethiopian Literature
                        </a>
                        <a href="../public/categories.php?name=Ethiopian%20History" class="chip">
                            <i class="fas fa-landmark"></i> Ethiopian History
                        </a>
                        <a href="../public/categories.php?name=Programming" class="chip">
                            <i class="fas fa-code"></i> Programming
                        </a>
                    </div>
                </div>
                
                <a href="../public/index.php" class="btn btn-primary btn-large">
                    <i class="fas fa-shopping-bag"></i> Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Ethiopian E-Book Store</h3>
                <p>Ethiopia's premier digital reading destination. Discover thousands of Ethiopian and international e-books.</p>
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
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Ethiopian E-Book Store. All rights reserved. 🇪🇹</p>
        </div>
    </footer>

    <script>
        // Quantity functions
        function incrementQuantity(cartId) {
            const input = document.getElementById('quantity-' + cartId);
            const currentValue = parseInt(input.value);
            if (currentValue < 10) {
                input.value = currentValue + 1;
                updateQuantity(cartId, input.value);
            }
        }

        function decrementQuantity(cartId) {
            const input = document.getElementById('quantity-' + cartId);
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateQuantity(cartId, input.value);
            } else if (currentValue === 1) {
                if (confirm('Remove this item from your cart?')) {
                    window.location.href = '?remove=' + cartId;
                }
            }
        }

        function updateQuantity(cartId, quantity) {
            // Update subtotal for this item
            const priceElement = document.querySelector(`#cart-item-${cartId} .cart-item-price`);
            const price = parseFloat(priceElement.dataset.price || priceElement.innerText.replace('$', ''));
            const subtotalElement = document.getElementById('subtotal-' + cartId);
            const newSubtotal = price * quantity;
            subtotalElement.innerText = '$' + newSubtotal.toFixed(2);
            
            // Recalculate total
            recalculateTotal();
        }

        function recalculateTotal() {
            const subtotals = document.querySelectorAll('[id^="subtotal-"]');
            let total = 0;
            subtotals.forEach(el => {
                total += parseFloat(el.innerText.replace('$', ''));
            });
            
            document.getElementById('subtotal').innerText = '$' + total.toFixed(2);
            document.getElementById('total').innerText = '$' + total.toFixed(2);
        }

        // Auto-submit on quantity change (optional - remove if you want manual update)
        let timeout;
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    document.querySelector('button[name="update_cart"]').click();
                }, 1000);
            });
        });
    </script>
</body>
</html>