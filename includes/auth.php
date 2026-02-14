<?php
require_once 'database.php';
require_once 'session.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Register new user
    public function register($username, $email, $password, $fullName) {
        // Check if user exists
        $checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $checkStmt = $this->db->query($checkSql, [$username, $email]);
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $sql = "INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->query($sql, [$username, $email, $hashedPassword, $fullName]);
        
        if ($stmt->affected_rows > 0) {
            return ['success' => true, 'message' => 'Registration successful'];
        }
        
        return ['success' => false, 'message' => 'Registration failed'];
    }
    
    // Login user
    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $this->db->query($sql, [$username, $username]);
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                SessionManager::setUser($user);
                return ['success' => true, 'message' => 'Login successful', 'user_type' => $user['user_type']];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid username or password'];
    }
    
    // Logout
    public function logout() {
        SessionManager::destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    // Check if user is authenticated
    public static function requireLogin() {
        if (!SessionManager::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/public/login.php');
            exit;
        }
    }
    
    // Check if user is admin
    public static function requireAdmin() {
        if (!SessionManager::isAdmin()) {
            header('Location: ' . SITE_URL . '/public/login.php');
            exit;
        }
    }
}
?>