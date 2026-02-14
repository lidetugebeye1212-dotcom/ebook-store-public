<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/auth.php';

$auth = new Auth();
$result = $auth->login('admin', 'admin1234');

if ($result['success']) {
    echo "✅ Login successful! Redirecting...";
    echo "<br>User Type: " . $result['user_type'];
    echo "<br><a href='admin/dashboard.php'>Go to Admin Dashboard</a>";
} else {
    echo "❌ Login failed: " . $result['message'];
}
?>