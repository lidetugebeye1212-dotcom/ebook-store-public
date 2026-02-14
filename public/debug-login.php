<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$db = Database::getInstance();

echo "<h1>🔍 Login Debug Tool</h1>";

$username = 'admin';
$password = 'admin123';

echo "<h2>Testing login for: $username</h2>";

// 1. Check if user exists
$sql = "SELECT * FROM users WHERE username = ? OR email = ?";
$stmt = $db->query($sql, [$username, $username]);
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    echo "✅ User found in database!<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "User Type: " . $user['user_type'] . "<br>";
    echo "Full Name: " . ($user['full_name'] ?? 'Not set') . "<br>";
    echo "Password Hash: " . $user['password'] . "<br>";
    echo "Hash Length: " . strlen($user['password']) . " characters<br>";
    
    // 2. Test password verification
    echo "<h3>Testing password verification:</h3>";
    
    if (password_verify($password, $user['password'])) {
        echo "✅ <span style='color: green; font-weight: bold;'>PASSWORD VERIFICATION SUCCESSFUL!</span><br>";
        echo "The password 'admin123' matches the hash!<br>";
        
        // 3. Test the login function
        require_once '../includes/auth.php';
        $auth = new Auth();
        $login_result = $auth->login($username, $password);
        
        if ($login_result['success']) {
            echo "✅ Login function working!<br>";
            echo "Redirecting to: " . ($login_result['user_type'] === 'admin' ? 'admin' : 'user') . " dashboard<br>";
            echo "<a href='../admin/dashboard.php'>Go to Admin Dashboard</a>";
        } else {
            echo "❌ Login function failed: " . $login_result['message'];
        }
        
    } else {
        echo "❌ <span style='color: red; font-weight: bold;'>PASSWORD VERIFICATION FAILED!</span><br>";
        echo "The password 'admin123' does NOT match the stored hash.<br>";
        
        // 4. Show what the correct hash should be
        $correct_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "<h4>Fix it by running this SQL:</h4>";
        echo "<code style='background: #f4f4f4; padding: 10px; display: block; border-radius: 5px;'>";
        echo "UPDATE users SET password = '" . $correct_hash . "' WHERE id = " . $user['id'] . ";";
        echo "</code>";
        
        // 5. Alternative: Use this exact hash
        echo "<h4>Or use this exact hash:</h4>";
        echo "<code style='background: #f4f4f4; padding: 10px; display: block; border-radius: 5px;'>";
        echo "'" . $correct_hash . "'";
        echo "</code>";
    }
    
} else {
    echo "❌ User '$username' not found in database!<br>";
    
    // Create admin user
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert_sql = "INSERT INTO users (username, email, password, full_name, user_type) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $db->query($insert_sql, ['admin', 'admin@ethiopianebooks.com', $hash, 'Administrator', 'admin']);
    
    if ($insert_stmt) {
        echo "✅ Admin user created!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "Hash: " . $hash . "<br>";
        echo "<a href='debug-login.php'>Refresh page</a>";
    }
}
?>