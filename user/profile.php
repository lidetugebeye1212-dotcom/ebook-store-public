<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireLogin();

$db = Database::getInstance();
$userId = SessionManager::getUserId();
$success = '';
$error = '';

// Get current user data
$userSql = "SELECT * FROM users WHERE id = ?";
$userStmt = $db->query($userSql, [$userId]);
$user = $userStmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = Functions::sanitize($_POST['full_name']);
    $email = Functions::sanitize($_POST['email']);
    $username = Functions::sanitize($_POST['username']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email already exists (excluding current user)
        $checkSql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $checkStmt = $db->query($checkSql, [$email, $userId]);
        if ($checkStmt->get_result()->num_rows > 0) {
            $error = 'Email already in use by another account';
        } else {
            // Update user profile
            $updateSql = "UPDATE users SET full_name = ?, email = ?, username = ? WHERE id = ?";
            $updateStmt = $db->query($updateSql, [$full_name, $email, $username, $userId]);
            
            if ($updateStmt) {
                $success = 'Profile updated successfully!';
                // Refresh user data
                $user['full_name'] = $full_name;
                $user['email'] = $email;
                $user['username'] = $username;
            }
        }
    }
}

// Get additional stats
$statsSql = "SELECT 
                (SELECT COUNT(*) FROM orders WHERE user_id = ?) as total_orders,
                (SELECT COUNT(*) FROM book_reviews WHERE user_id = ?) as total_reviews,
                (SELECT SUM(total_amount) FROM orders WHERE user_id = ? AND status = 'completed') as total_spent
             FROM dual";
$statsStmt = $db->query($statsSql, [$userId, $userId, $userId]);
$stats = $statsStmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Ethiopian E-Book Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/responsive.css">
    
    <style>
        /* Profile Page Styles */
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
            content: '👤';
            position: absolute;
            bottom: -30px;
            right: 20px;
            font-size: 150px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .profile-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            animation: fadeInDown 0.5s ease;
        }

        .profile-header p {
            font-size: 1.1rem;
            opacity: 0.95;
            animation: fadeInUp 0.5s ease 0.2s both;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        /* Sidebar */
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
            font-size: 0.95rem;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            padding: 1rem 0;
            border-top: 2px solid #f1f1f1;
            border-bottom: 2px solid #f1f1f1;
            margin-bottom: 1.5rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #6c5ce7;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #636e72;
            text-transform: uppercase;
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

        .profile-menu a i {
            width: 20px;
        }

        /* Main Content */
        .profile-content {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .content-header h2 i {
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

        .form-control[readonly] {
            background: #f8f9fa;
            cursor: not-allowed;
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

        .info-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1.5rem;
            border-radius: 15px;
            margin: 1.5rem 0;
        }

        .info-box i {
            color: #6c5ce7;
            margin-right: 0.5rem;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
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
            .profile-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .profile-header h1 {
                font-size: 2rem;
            }
            
            .profile-stats {
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

    <div class="profile-container">
        <div class="profile-header">
            <h1><i class="fas fa-user-edit"></i> Profile Settings</h1>
            <p>Manage your personal information and account settings</p>
        </div>

        <div class="profile-grid">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($user['email']); ?></div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $stats['total_orders'] ?: 0; ?></div>
                        <div class="stat-label">Orders</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $stats['total_reviews'] ?: 0; ?></div>
                        <div class="stat-label">Reviews</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">$<?php echo number_format($stats['total_spent'] ?: 0, 2); ?></div>
                        <div class="stat-label">Spent</div>
                    </div>
                </div>
                
                <ul class="profile-menu">
                    <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profile Information</a></li>
                    <li><a href="change-password.php"><i class="fas fa-lock"></i> Change Password</a></li>
                    <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="orders.php"><i class="fas fa-download"></i> My Downloads</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="profile-content">
                <div class="content-header">
                    <h2><i class="fas fa-user-edit"></i> Edit Profile Information</h2>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" class="form-control" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" 
                               placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" 
                               placeholder="Enter your email" required>
                        <small style="color: #636e72;">We'll never share your email with anyone else.</small>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-at"></i> Username</label>
                        <input type="text" class="form-control" name="username" 
                               value="<?php echo htmlspecialchars($user['username']); ?>" 
                               placeholder="Enter username" required>
                    </div>

                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <strong>Account Created:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?>
                        <br>
                        <i class="fas fa-clock"></i>
                        <strong>Last Updated:</strong> <?php echo date('F d, Y', strtotime($user['updated_at'])); ?>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
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