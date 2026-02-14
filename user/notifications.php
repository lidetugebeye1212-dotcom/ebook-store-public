<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireLogin();

$db = Database::getInstance();
$userId = SessionManager::getUserId();
$success = '';

// Get user data
$userSql = "SELECT * FROM users WHERE id = ?";
$userStmt = $db->query($userSql, [$userId]);
$user = $userStmt->get_result()->fetch_assoc();

// Handle notification preferences update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_orders = isset($_POST['email_orders']) ? 1 : 0;
    $email_promotions = isset($_POST['email_promotions']) ? 1 : 0;
    $email_reviews = isset($_POST['email_reviews']) ? 1 : 0;
    $email_new_books = isset($_POST['email_new_books']) ? 1 : 0;
    
    // Here you would save to a notifications_settings table
    // For now, just show success message
    $success = 'Notification preferences updated successfully!';
}

// Mock preferences (in real app, fetch from database)
$preferences = [
    'email_orders' => true,
    'email_promotions' => false,
    'email_reviews' => true,
    'email_new_books' => true
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Settings - Ethiopian E-Book Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/responsive.css">
    
    <style>
        /* Reuse styles from profile.php */
        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .profile-header {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 30px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(108, 92, 231, 0.3);
        }

        .profile-header::before {
            content: '🔔';
            position: absolute;
            bottom: -30px;
            right: 20px;
            font-size: 150px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        .profile-sidebar {
            background: white;
            border-radius: 20px;
            padding: 2rem 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #6c5ce7, #a463f5);
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            box-shadow: 0 10px 20px rgba(108, 92, 231, 0.3);
            border: 4px solid white;
        }

        .profile-name {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .profile-email {
            text-align: center;
            color: #636e72;
            margin-bottom: 2rem;
        }

        .profile-menu {
            list-style: none;
        }

        .profile-menu li {
            margin-bottom: 0.5rem;
        }

        .profile-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            text-decoration: none;
            color: #2d3436;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .profile-menu a:hover,
        .profile-menu a.active {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            transform: translateX(5px);
        }

        .profile-content {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .content-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f1f1;
        }

        .content-header h2 {
            font-size: 1.8rem;
            color: #2d3436;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: rgba(0, 184, 148, 0.1);
            color: #00b894;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-left: 4px solid #00b894;
        }

        .notification-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .notification-card:hover {
            border-color: #6c5ce7;
            transform: translateX(5px);
        }

        .notification-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .notification-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .notification-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3436;
        }

        .notification-desc {
            color: #636e72;
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
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
        }

        .btn-secondary:hover {
            background: #e1e1e1;
            transform: translateY(-3px);
        }

        @media (max-width: 992px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar {
                position: static;
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

    <div class="profile-container">
        <div class="profile-header">
            <h1><i class="fas fa-bell"></i> Notification Settings</h1>
            <p>Manage your email preferences and notifications</p>
        </div>

        <div class="profile-grid">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
                
                <ul class="profile-menu">
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile Information</a></li>
                    <li><a href="change-password.php"><i class="fas fa-lock"></i> Change Password</a></li>
                    <li><a href="notifications.php" class="active"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="orders.php"><i class="fas fa-download"></i> My Downloads</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="profile-content">
                <div class="content-header">
                    <h2><i class="fas fa-bell"></i> Email Preferences</h2>
                </div>

                <?php if ($success): ?>
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="notification-card">
                        <div class="notification-header">
                            <div class="notification-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="notification-title">Order Updates</div>
                            <label class="toggle-switch" style="margin-left: auto;">
                                <input type="checkbox" name="email_orders" <?php echo $preferences['email_orders'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="notification-desc">
                            Receive email notifications about your order status, shipping updates, and delivery confirmations.
                        </div>
                    </div>

                    <div class="notification-card">
                        <div class="notification-header">
                            <div class="notification-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="notification-title">Promotions & Offers</div>
                            <label class="toggle-switch" style="margin-left: auto;">
                                <input type="checkbox" name="email_promotions" <?php echo $preferences['email_promotions'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="notification-desc">
                            Get special offers, discounts, and promotional deals on Ethiopian books.
                        </div>
                    </div>

                    <div class="notification-card">
                        <div class="notification-header">
                            <div class="notification-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="notification-title">Review Requests</div>
                            <label class="toggle-switch" style="margin-left: auto;">
                                <input type="checkbox" name="email_reviews" <?php echo $preferences['email_reviews'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="notification-desc">
                            Receive emails asking you to review books you've purchased and share your thoughts.
                        </div>
                    </div>

                    <div class="notification-card">
                        <div class="notification-header">
                            <div class="notification-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="notification-title">New Ethiopian Books</div>
                            <label class="toggle-switch" style="margin-left: auto;">
                                <input type="checkbox" name="email_new_books" <?php echo $preferences['email_new_books'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="notification-desc">
                            Get notified when new Ethiopian books are added to our collection.
                        </div>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Preferences
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
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
</body>
</html>