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

// Get user data
$userSql = "SELECT * FROM users WHERE id = ?";
$userStmt = $db->query($userSql, [$userId]);
$user = $userStmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $error = 'Current password is incorrect';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updateSql = "UPDATE users SET password = ? WHERE id = ?";
        $updateStmt = $db->query($updateSql, [$hashed_password, $userId]);
        
        if ($updateStmt) {
            $success = 'Password changed successfully!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Ethiopian E-Book Store</title>
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
            content: '🔒';
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

        .profile-menu a i {
            width: 20px;
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

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
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

        .password-strength {
            margin-top: 0.5rem;
            height: 5px;
            border-radius: 5px;
            background: #f1f1f1;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s;
        }

        .strength-weak {
            background: #d63031;
            width: 33.33%;
        }

        .strength-medium {
            background: #fdcb6e;
            width: 66.66%;
        }

        .strength-strong {
            background: #00b894;
            width: 100%;
        }

        .requirements {
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .requirements p {
            margin-bottom: 0.5rem;
            color: #2d3436;
            font-weight: 500;
        }

        .requirements ul {
            list-style: none;
        }

        .requirements li {
            margin-bottom: 0.3rem;
            color: #636e72;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .requirements li.valid {
            color: #00b894;
        }

        .requirements li i {
            width: 20px;
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
            <h1><i class="fas fa-lock"></i> Change Password</h1>
            <p>Update your password to keep your account secure</p>
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
                    <li><a href="change-password.php" class="active"><i class="fas fa-lock"></i> Change Password</a></li>
                    <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                    <li><a href="orders.php"><i class="fas fa-download"></i> My Downloads</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="profile-content">
                <div class="content-header">
                    <h2><i class="fas fa-key"></i> Update Your Password</h2>
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

                <form method="POST" action="" id="passwordForm">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Current Password</label>
                        <input type="password" class="form-control" name="current_password" 
                               placeholder="Enter your current password" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-key"></i> New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               placeholder="Enter new password" required>
                        <div class="password-strength">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-check-circle"></i> Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm new password" required>
                    </div>

                    <div class="requirements">
                        <p><i class="fas fa-shield-alt"></i> Password Requirements:</p>
                        <ul>
                            <li id="length"><i class="far fa-circle"></i> At least 6 characters</li>
                            <li id="match"><i class="far fa-circle"></i> Passwords match</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Password
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Password strength checker
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('strengthBar');
        const lengthReq = document.getElementById('length');
        const matchReq = document.getElementById('match');

        function checkPasswordStrength() {
            const password = newPassword.value;
            
            // Check length
            if (password.length >= 6) {
                lengthReq.innerHTML = '<i class="fas fa-check-circle" style="color: #00b894;"></i> At least 6 characters ✓';
                lengthReq.style.color = '#00b894';
            } else {
                lengthReq.innerHTML = '<i class="far fa-circle"></i> At least 6 characters';
                lengthReq.style.color = '#636e72';
            }

            // Check strength
            let strength = 0;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;

            strengthBar.className = 'strength-bar';
            if (password.length === 0) {
                strengthBar.style.width = '0';
            } else if (password.length < 6 || strength < 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength < 3) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        }

        function checkMatch() {
            if (confirmPassword.value.length > 0) {
                if (newPassword.value === confirmPassword.value) {
                    matchReq.innerHTML = '<i class="fas fa-check-circle" style="color: #00b894;"></i> Passwords match ✓';
                    matchReq.style.color = '#00b894';
                } else {
                    matchReq.innerHTML = '<i class="fas fa-times-circle" style="color: #d63031;"></i> Passwords do not match';
                    matchReq.style.color = '#d63031';
                }
            } else {
                matchReq.innerHTML = '<i class="far fa-circle"></i> Passwords match';
                matchReq.style.color = '#636e72';
            }
        }

        newPassword.addEventListener('input', () => {
            checkPasswordStrength();
            checkMatch();
        });
        
        confirmPassword.addEventListener('input', checkMatch);
    </script>

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