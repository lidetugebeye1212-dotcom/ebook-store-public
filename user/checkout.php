<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireLogin();

$db = Database::getInstance();
$userId = SessionManager::getUserId();

// Check if cart has items
$cartSql = "SELECT c.*, b.price, b.title, b.author, b.id as book_id 
            FROM cart c 
            JOIN books b ON c.book_id = b.id 
            WHERE c.user_id = ?";
$cartStmt = $db->query($cartSql, [$userId]);
$cartItems = $cartStmt->get_result();

if ($cartItems->num_rows === 0) {
    Functions::redirect('cart.php');
}

// Calculate total
$totalSql = "SELECT SUM(b.price * c.quantity) as total 
             FROM cart c 
             JOIN books b ON c.book_id = b.id 
             WHERE c.user_id = ?";
$totalStmt = $db->query($totalSql, [$userId]);
$total = $totalStmt->get_result()->fetch_assoc()['total'];

// Get user details
$userSql = "SELECT * FROM users WHERE id = ?";
$userStmt = $db->query($userSql, [$userId]);
$user = $userStmt->get_result()->fetch_assoc();

$error = '';
$success = '';

// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = Functions::sanitize($_POST['payment_method']);
    
    // Credit Card validation
    $card_name = isset($_POST['card_name']) ? Functions::sanitize($_POST['card_name']) : '';
    $card_number = isset($_POST['card_number']) ? preg_replace('/\s+/', '', $_POST['card_number']) : '';
    $expiry = isset($_POST['expiry']) ? Functions::sanitize($_POST['expiry']) : '';
    $cvv = isset($_POST['cvv']) ? Functions::sanitize($_POST['cvv']) : '';
    
    // CBE Birr validation
    $cbe_phone = isset($_POST['cbe_phone']) ? Functions::sanitize($_POST['cbe_phone']) : '';
    $cbe_password = isset($_POST['cbe_password']) ? $_POST['cbe_password'] : '';
    
    // Telebirr validation
 $telebirr_phone = isset($_POST['telebirr_phone']) ? Functions::sanitize($_POST['telebirr_phone']) : '';
    $telebirr_pin = isset($_POST['telebirr_pin']) ? $_POST['telebirr_pin'] : '';
    
    // HelloCash validation
    $hellocash_phone = isset($_POST['hellocash_phone']) ? Functions::sanitize($_POST['hellocash_phone']) : '';
    $hellocash_pin = isset($_POST['hellocash_pin']) ? $_POST['hellocash_pin'] : '';
    
    // Validate based on payment method
    if ($payment_method === 'credit_card') {
        if (empty($card_name)) {
            $error = 'Cardholder name is required';
        } elseif (empty($card_number)) {
            $error = 'Card number is required';
        } elseif (strlen($card_number) < 16) {
            $error = 'Please enter a valid 16-digit card number';
        } elseif (!preg_match('/^[0-9]{16}$/', $card_number)) {
            $error = 'Card number must contain only digits';
        } elseif (empty($expiry)) {
            $error = 'Expiry date is required';
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry)) {
            $error = 'Expiry date must be in MM/YY format';
        } else {
            // Validate expiry not in past
            $exp_parts = explode('/', $expiry);
            $exp_month = $exp_parts[0];
            $exp_year = '20' . $exp_parts[1];
            $current_year = date('Y');
            $current_month = date('m');
            
            if ($exp_year < $current_year || ($exp_year == $current_year && $exp_month < $current_month)) {
                $error = 'Card has expired';
            }
        }
        
        if (empty($error)) {
            if (empty($cvv)) {
                $error = 'CVV is required';
            } elseif (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
                $error = 'CVV must be 3 or 4 digits';
            }
        }
        
    } elseif ($payment_method === 'cbe') {
        if (empty($cbe_phone)) {
            $error = 'Phone number is required for CBE Birr';
        } elseif (!preg_match('/^[0-9]{10}$/', $cbe_phone)) {
            $error = 'Please enter a valid 10-digit Ethiopian phone number (e.g., 0912345678)';
        } elseif (empty($cbe_password)) {
            $error = 'CBE Birr password is required';
        } elseif (strlen($cbe_password) < 4) {
            $error = 'Password must be at least 4 characters';
        }
        
    } elseif ($payment_method === 'telebirr') {
        if (empty($telebirr_phone)) {
            $error = 'Phone number is required for Telebirr';
        } elseif (!preg_match('/^[0-9]{10}$/', $telebirr_phone)) {
            $error = 'Please enter a valid 10-digit Ethiopian phone number (e.g., 0912345678)';
        } elseif (empty($telebirr_pin)) {
            $error = 'Telebirr PIN is required';
        } elseif (!preg_match('/^[0-9]{4,6}$/', $telebirr_pin)) {
            $error = 'PIN must be 4-6 digits';
        }
        
    } elseif ($payment_method === 'hello_cash') {
        if (empty($hellocash_phone)) {
            $error = 'Phone number is required for HelloCash';
        } elseif (!preg_match('/^[0-9]{10}$/', $hellocash_phone)) {
            $error = 'Please enter a valid 10-digit Ethiopian phone number (e.g., 0912345678)';
        } elseif (empty($hellocash_pin)) {
            $error = 'HelloCash PIN is required';
        } elseif (!preg_match('/^[0-9]{4}$/', $hellocash_pin)) {
            $error = 'PIN must be exactly 4 digits';
        }
    }
    
    if (empty($error)) {
        // Begin transaction
        $db->getConnection()->begin_transaction();
        
        try {
            // Generate unique order number
            $order_number = 'ORD-' . strtoupper(uniqid()) . '-' . date('Ymd');
            
            // Map payment method to display name
            $payment_methods = [
                'credit_card' => 'Credit Card',
                'cbe' => 'CBE Birr',
                'telebirr' => 'Telebirr',
                'hello_cash' => 'HelloCash'
            ];
            
            // Create order
            $orderSql = "INSERT INTO orders (user_id, order_number, total_amount, status, payment_method) 
                         VALUES (?, ?, ?, 'completed', ?)";
            $orderStmt = $db->query($orderSql, [$userId, $order_number, $total, $payment_methods[$payment_method]]);
            
            $orderId = $db->getLastInsertId();
            
            // Add order details
            $cartItems->data_seek(0);
            while ($item = $cartItems->fetch_assoc()) {
                $detailSql = "INSERT INTO order_details (order_id, book_id, price, quantity) 
                              VALUES (?, ?, ?, ?)";
                $db->query($detailSql, [$orderId, $item['book_id'], $item['price'], $item['quantity']]);
            }
            
            // Clear cart
            $clearSql = "DELETE FROM cart WHERE user_id = ?";
            $db->query($clearSql, [$userId]);
            
            // Commit transaction
            $db->getConnection()->commit();
            
            // Redirect to success page
            Functions::redirect('order-success.php?order=' . $order_number);
            
        } catch (Exception $e) {
            $db->getConnection()->rollback();
            $error = "Checkout failed. Please try again. Error: " . $e->getMessage();
        }
    }
}

// Reset cart items pointer for display
$cartItems->data_seek(0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Ethiopian E-Book Store</title>
    
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
        /* Checkout Page Styles */
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .checkout-header {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 30px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(108, 92, 231, 0.3);
        }

        .checkout-header::before {
            content: '💰';
            position: absolute;
            bottom: -30px;
            right: 20px;
            font-size: 150px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .checkout-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .checkout-header p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
        }

        /* Order Summary Card */
        .order-summary {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .summary-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .summary-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 1.5rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .item-info {
            flex: 1;
        }

        .item-title {
            font-weight: 600;
            color: #2d3436;
            font-size: 0.95rem;
        }

        .item-quantity {
            color: #636e72;
            font-size: 0.85rem;
        }

        .item-price {
            font-weight: 600;
            color: #6c5ce7;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
        }

        .summary-row.total {
            border-top: 2px solid #f1f1f1;
            font-size: 1.3rem;
            font-weight: 700;
            color: #6c5ce7;
            padding-top: 1.5rem;
        }

        /* Checkout Form */
        .checkout-form {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .form-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: #6c5ce7;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2d3436;
        }

        .form-group label i {
            color: #6c5ce7;
            margin-right: 0.3rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #6c5ce7;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Payment Methods */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .payment-method {
            position: relative;
        }

        .payment-method input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payment-method label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem 1rem;
            background: #f8f9fa;
            border: 2px solid #e1e1e1;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .payment-method input[type="radio"]:checked + label {
            border-color: #6c5ce7;
            background: linear-gradient(135deg, rgba(108, 92, 231, 0.1), rgba(164, 99, 245, 0.1));
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(108, 92, 231, 0.2);
        }

        .payment-method label i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: #6c5ce7;
        }

        .payment-method label span {
            font-weight: 600;
            color: #2d3436;
        }

        .payment-method label small {
            color: #636e72;
            font-size: 0.8rem;
            margin-top: 0.3rem;
        }

        .ethiopian-badge {
            background: linear-gradient(135deg, #078930, #FCDD09, #DA121A);
            color: white;
            padding: 0.2rem 0.8rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 0.3rem;
        }

        /* Payment Fields */
        .payment-fields {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 15px;
            border: 2px solid #e1e1e1;
            animation: slideDown 0.3s ease;
        }

        .field-hint {
            font-size: 0.8rem;
            color: #636e72;
            margin-top: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .field-hint i {
            color: #6c5ce7;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideIn 0.3s ease;
        }

        .alert-error {
            background: rgba(214, 48, 49, 0.1);
            color: #d63031;
            border-left: 4px solid #d63031;
        }

        .alert-info {
            background: rgba(108, 92, 231, 0.1);
            color: #6c5ce7;
            border-left: 4px solid #6c5ce7;
        }

        .alert-success {
            background: rgba(0, 184, 148, 0.1);
            color: #00b894;
            border-left: 4px solid #00b894;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
            width: 100%;
            font-size: 1.1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.5);
        }

        .btn-secondary {
            background: #f1f1f1;
            color: #2d3436;
            margin-top: 1rem;
        }

        .btn-secondary:hover {
            background: #e1e1e1;
            transform: translateY(-3px);
        }

        .security-badge {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            color: #636e72;
        }

        .security-badge i {
            color: #00b894;
            font-size: 2rem;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 992px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .payment-methods {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
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
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <div class="checkout-container">
        <div class="checkout-header">
            <h1><i class="fas fa-lock"></i> Secure Checkout</h1>
            <p>Complete your purchase safely and securely</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="checkout-grid">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <form method="POST" action="" id="checkoutForm">
                    <!-- Contact Information -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-user"></i> Contact Information
                        </h3>
                        
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            <div class="field-hint">
                                <i class="fas fa-info-circle"></i> Order confirmation will be sent to this email
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>" readonly>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-credit-card"></i> Select Payment Method
                        </h3>

                        <div class="payment-methods">
                            <!-- Credit Card -->
                            <div class="payment-method">
                                <input type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                                <label for="credit_card">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Credit Card</span>
                                    <small>Visa, Mastercard</small>
                                </label>
                            </div>

                            <!-- CBE Birr -->
                            <div class="payment-method">
                                <input type="radio" name="payment_method" id="cbe" value="cbe">
                                <label for="cbe">
                                    <i class="fas fa-university"></i>
                                    <span>CBE Birr</span>
                                    <small class="ethiopian-badge">🇪🇹 Ethiopian</small>
                                </label>
                            </div>

                            <!-- Telebirr -->
                            <div class="payment-method">
                                <input type="radio" name="payment_method" id="telebirr" value="telebirr">
                                <label for="telebirr">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>Telebirr</span>
                                    <small class="ethiopian-badge">🇪🇹 Ethiopian</small>
                                </label>
                            </div>

                            <!-- HelloCash -->
                            <div class="payment-method">
                                <input type="radio" name="payment_method" id="hello_cash" value="hello_cash">
                                <label for="hello_cash">
                                    <i class="fas fa-wallet"></i>
                                    <span>HelloCash</span>
                                    <small class="ethiopian-badge">🇪🇹 Ethiopian</small>
                                </label>
                            </div>
                        </div>

                        <!-- Credit Card Fields -->
                        <div id="credit_card_fields" class="payment-fields">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Cardholder Name *</label>
                                <input type="text" class="form-control" name="card_name" placeholder="As it appears on card" required>
                                <div class="field-hint">
                                    <i class="fas fa-info-circle"></i> Enter the name exactly as printed on your card
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-credit-card"></i> Card Number *</label>
                                <input type="text" class="form-control" name="card_number" id="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                                <div class="field-hint">
                                    <i class="fas fa-info-circle"></i> Enter your 16-digit card number without spaces
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar"></i> Expiry Date *</label>
                                    <input type="text" class="form-control" name="expiry" id="expiry" placeholder="MM/YY" maxlength="5" required>
                                    <div class="field-hint">
                                        <i class="fas fa-info-circle"></i> Format: MM/YY (e.g., 12/25)
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-lock"></i> CVV *</label>
                                    <input type="password" class="form-control" name="cvv" id="cvv" placeholder="123" maxlength="4" required>
                                    <div class="field-hint">
                                        <i class="fas fa-info-circle"></i> 3-4 digit security code on back of card
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-shield-alt"></i> 
                                <strong>Test Mode:</strong> Use any valid format for testing. No real payments are processed.
                            </div>
                        </div>

                        <!-- CBE Birr Fields -->
                        <div id="cbe_fields" class="payment-fields" style="display: none;">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> CBE Birr Phone Number *</label>
                                <input type="tel" class="form-control" name="cbe_phone" id="cbe_phone" placeholder="0912345678" maxlength="10">
                                <div class="field-hint">
                                    <i class="fas fa-info-circle"></i> Enter the phone number registered with CBE Birr
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> CBE Birr Password *</label>
                                <input type="password" class="form-control" name="cbe_password" id="cbe_password" placeholder="Enter your CBE Birr password" maxlength="20">
                                <div class="field-hint">
                                    <i class="fas fa-info-circle"></i> Enter your CBE Birr mobile banking password
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-mobile-alt"></i> 
                                <strong>How it works:</strong> You will receive a payment request on your CBE Birr app. Enter your password to confirm.
                            </div>
                        </div>

                        <!-- Telebirr Fields -->
                        <div id="telebirr_fields" class="payment-fields" style="display: none;">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Telebirr Phone Number *</label>
                                <input type="tel" class="form-control" name="telebirr_phone" id="telebirr_phone" placeholder="0912345678" maxlength="10">
                                <div class="field-hint">
                                    <i class="fas fa-info-circle"></i> Enter the phone number registered with Telebirr
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> Telebirr PIN *</label>
                                <input type="password" class="form-control" name="telebirr_pin" id="telebirr_pin" placeholder="Enter your 4-6 digit PIN" maxlength="6">
                                <div class="field-hint">
                                    <i class="fas fa-info-circle"></i> Enter your Telebirr secret PIN (4-6 digits)
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-mobile-alt"></i> 
                                <strong>How it works:</strong> You will receive a USSD prompt on your phone. Enter your PIN to complete payment.
                            </div>
                        </div>

                        <!-- HelloCash Fields -->
                        <div id="hellocash_fields" class="payment-fields" style="display: none;">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> HelloCash Phone Number *</label>
                                <input type="tel" class="form-control" name="hellocash_phone" id="hellocash_phone" placeholder="0912345678" maxlength="10">
                                <div class="field-hint">
                                    <i class="fas fa-info-circle"></i> Enter the phone number registered with HelloCash
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> HelloCash PIN *</label>
                                <input type="password" class="form-control" name="hellocash_pin" id="hellocash_pin" placeholder="Enter your 4-digit PIN" maxlength="4">
                                <div class="field-hint">
                                    <i class="fas fa-info-circle"></i> Enter your HelloCash 4-digit secret PIN
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-wallet"></i> 
                                <strong>How it works:</strong> You will receive a payment request. Enter your PIN to authorize the transaction.
                            </div>
                        </div>
                    </div>

                    <!-- Security Badge -->
                    <div class="security-badge">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <strong>🔒 256-bit SSL Secure Payment</strong>
                            <p style="font-size: 0.9rem; margin: 0;">Your payment information is encrypted and secure. We never store your full card details or passwords.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-lock"></i> Complete Purchase
                    </button>
                    
                    <a href="cart.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Cart
                    </a>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <h3 class="summary-title">
                    <i class="fas fa-shopping-bag"></i> Order Summary
                </h3>

                <div class="summary-items">
                    <?php 
                    $subtotal = 0;
                    while($item = $cartItems->fetch_assoc()): 
                        $itemTotal = $item['price'] * $item['quantity'];
                        $subtotal += $itemTotal;
                    ?>
                        <div class="summary-item">
                            <div class="item-info">
                                <div class="item-title"><?php echo htmlspecialchars(Functions::truncateText($item['title'], 30)); ?></div>
                                <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="item-price">$<?php echo number_format($itemTotal, 2); ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>

                <div class="summary-row">
                    <span>Shipping</span>
                    <span style="color: #00b894;">Free</span>
                </div>

                <div class="summary-row total">
                    <span>Total</span>
                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                </div>

                <div style="margin-top: 2rem; text-align: center;">
                    <p style="color: #636e72; margin-bottom: 0.5rem;">We Accept:</p>
                    <div style="display: flex; gap: 1rem; justify-content: center; font-size: 2rem;">
                        <i class="fab fa-cc-visa" style="color: #1a1f71;" title="Visa"></i>
                        <i class="fab fa-cc-mastercard" style="color: #f79e1b;" title="Mastercard"></i>
                        <i class="fas fa-university" style="color: #078930;" title="CBE Birr"></i>
                        <i class="fas fa-mobile-alt" style="color: #FCDD09;" title="Telebirr"></i>
                        <i class="fas fa-wallet" style="color: #DA121A;" title="HelloCash"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Ethiopian E-Book Store</h3>
                <p>Ethiopia's premier digital reading destination. Secure payments accepted.</p>
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

    <script>
        // Get all payment method radios
        const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
        
        // Get all payment field sections
        const creditCardFields = document.getElementById('credit_card_fields');
        const cbeFields = document.getElementById('cbe_fields');
        const telebirrFields = document.getElementById('telebirr_fields');
        const hellocashFields = document.getElementById('hellocash_fields');
        
        // Get all input fields
        const cardName = document.querySelector('input[name="card_name"]');
        const cardNumber = document.querySelector('input[name="card_number"]');
        const expiry = document.querySelector('input[name="expiry"]');
        const cvv = document.querySelector('input[name="cvv"]');
        
        const cbePhone = document.querySelector('input[name="cbe_phone"]');
        const cbePassword = document.querySelector('input[name="cbe_password"]');
        
        const telebirrPhone = document.querySelector('input[name="telebirr_phone"]');
        const telebirrPin = document.querySelector('input[name="telebirr_pin"]');
        
        const hellocashPhone = document.querySelector('input[name="hellocash_phone"]');
        const hellocashPin = document.querySelector('input[name="hellocash_pin"]');
        
        const submitBtn = document.getElementById('submitBtn');

        // Function to reset all required attributes
        function resetRequired() {
            // Credit Card
            if (cardName) cardName.required = false;
            if (cardNumber) cardNumber.required = false;
            if (expiry) expiry.required = false;
            if (cvv) cvv.required = false;
            
            // CBE
            if (cbePhone) cbePhone.required = false;
            if (cbePassword) cbePassword.required = false;
            
            // Telebirr
            if (telebirrPhone) telebirrPhone.required = false;
            if (telebirrPin) telebirrPin.required = false;
            
            // HelloCash
            if (hellocashPhone) hellocashPhone.required = false;
            if (hellocashPin) hellocashPin.required = false;
        }

        // Function to hide all payment fields
        function hideAllFields() {
            creditCardFields.style.display = 'none';
            cbeFields.style.display = 'none';
            telebirrFields.style.display = 'none';
            hellocashFields.style.display = 'none';
        }

        // Function to toggle payment fields
        function togglePaymentFields(method) {
            resetRequired();
            hideAllFields();
            
            if (method === 'credit_card') {
                creditCardFields.style.display = 'block';
                if (cardName) cardName.required = true;
                if (cardNumber) cardNumber.required = true;
                if (expiry) expiry.required = true;
                if (cvv) cvv.required = true;
                submitBtn.innerHTML = '<i class="fas fa-lock"></i> Pay with Credit Card';
                
            } else if (method === 'cbe') {
                cbeFields.style.display = 'block';
                if (cbePhone) cbePhone.required = true;
                if (cbePassword) cbePassword.required = true;
                submitBtn.innerHTML = '<i class="fas fa-university"></i> Pay with CBE Birr';
                
            } else if (method === 'telebirr') {
                telebirrFields.style.display = 'block';
                if (telebirrPhone) telebirrPhone.required = true;
                if (telebirrPin) telebirrPin.required = true;
                submitBtn.innerHTML = '<i class="fas fa-mobile-alt"></i> Pay with Telebirr';
                
            } else if (method === 'hello_cash') {
                hellocashFields.style.display = 'block';
                if (hellocashPhone) hellocashPhone.required = true;
                if (hellocashPin) hellocashPin.required = true;
                submitBtn.innerHTML = '<i class="fas fa-wallet"></i> Pay with HelloCash';
            }
        }

        // Add event listeners to radio buttons
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                togglePaymentFields(this.value);
            });
        });

        // Initialize with credit card selected
        togglePaymentFields('credit_card');

        // Format card number with spaces
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = this.value.replace(/\s/g, '').replace(/[^0-9]/g, '');
            if (value.length > 0) {
                value = value.match(new RegExp('.{1,4}', 'g'))?.join(' ') || value;
            }
            this.value = value;
        });

        // Format expiry date
        document.getElementById('expiry').addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0,2) + '/' + value.slice(2,4);
            }
            this.value = value;
        });

        // Format CVV - only digits
        document.getElementById('cvv').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });

        // Format phone numbers - only digits
        document.getElementById('cbe_phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });
        
        document.getElementById('telebirr_phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });
        
        document.getElementById('hellocash_phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
        });

        // Form validation before submit
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (selectedMethod === 'credit_card') {
                // Validate credit card
                const cardNum = document.getElementById('card_number').value.replace(/\s/g, '');
                const cvvVal = document.getElementById('cvv').value;
                const expiryVal = document.getElementById('expiry').value;
                
                if (cardNum.length !== 16) {
                    e.preventDefault();
                    alert('Please enter a valid 16-digit card number');
                    return false;
                }
                
                if (cvvVal.length < 3) {
                    e.preventDefault();
                    alert('Please enter a valid CVV');
                    return false;
                }
                
                if (!expiryVal.match(/^(0[1-9]|1[0-2])\/([0-9]{2})$/)) {
                    e.preventDefault();
                    alert('Please enter a valid expiry date (MM/YY)');
                    return false;
                }
                
            } else if (selectedMethod === 'cbe') {
                // Validate CBE Birr
                const phone = document.getElementById('cbe_phone').value;
                const password = document.getElementById('cbe_password').value;
                
                if (phone.length !== 10) {
                    e.preventDefault();
                    alert('Please enter a valid 10-digit Ethiopian phone number');
                    return false;
                }
                
                if (password.length < 4) {
                    e.preventDefault();
                    alert('Please enter your CBE Birr password');
                    return false;
                }
                
            } else if (selectedMethod === 'telebirr') {
                // Validate Telebirr
                const phone = document.getElementById('telebirr_phone').value;
                const pin = document.getElementById('telebirr_pin').value;
                
                if (phone.length !== 10) {
                    e.preventDefault();
                    alert('Please enter a valid 10-digit Ethiopian phone number');
                    return false;
                }
                
                if (pin.length < 4 || pin.length > 6) {
                    e.preventDefault();
                    alert('Telebirr PIN must be 4-6 digits');
                    return false;
                }
                
            } else if (selectedMethod === 'hello_cash') {
                // Validate HelloCash
                const phone = document.getElementById('hellocash_phone').value;
                const pin = document.getElementById('hellocash_pin').value;
                
                if (phone.length !== 10) {
                    e.preventDefault();
                    alert('Please enter a valid 10-digit Ethiopian phone number');
                    return false;
                }
                
                if (pin.length !== 4) {
                    e.preventDefault();
                    alert('HelloCash PIN must be exactly 4 digits');
                    return false;
                }
            }
        });
    </script>
</body>
</html>