<?php
class SessionManager {
    
    public static function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function setUser($userData) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['user_type'] = $userData['user_type'];
        $_SESSION['full_name'] = $userData['full_name'];
        $_SESSION['logged_in'] = true;
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function isAdmin() {
        return self::isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
    }
    
    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function destroy() {
        session_unset();
        session_destroy();
    }
}

// Initialize session
SessionManager::startSession();
?>