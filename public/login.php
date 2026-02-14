<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = Functions::sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $result = $auth->login($username, $password);
    
    if ($result['success']) {
        if ($result['user_type'] === 'admin') {
            Functions::redirect('../admin/dashboard.php');
        } else {
            Functions::redirect('../user/dashboard.php');
        }
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
        <!-- Ethiopian Flag Favicon - PASTE HERE -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' 
    viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' 
    fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' 
    fill='%23FCDD09'/%3E%3C/svg%3E">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Welcome Back!</h2>
                <p style="color: var(--gray);">Please login to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div style="background: rgba(214, 48, 49, 0.1); color: var(--danger-color); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="background: rgba(0, 184, 148, 0.1); color: var(--success-color); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">Login</button>
                
                <p style="text-align: center;">
                    Don't have an account? <a href="register.php" style="color: var(--primary-color); text-decoration: none;">Register here</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>