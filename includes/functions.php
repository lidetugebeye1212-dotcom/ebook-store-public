<?php
class Functions {
    
    // Generate unique order number
    public static function generateOrderNumber() {
        return 'ORD-' . strtoupper(uniqid()) . '-' . date('Ymd');
    }
    
    // Format price
    public static function formatPrice($price) {
        return '$' . number_format($price, 2);
    }
    
    // Truncate text
    public static function truncateText($text, $length = 100) {
        if ($text === null) return '';
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }
    
    // Upload file
    public static function uploadFile($file, $type = 'ebook') {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $targetDir = ($type == 'ebook') ? UPLOAD_PATH . 'ebooks/' : UPLOAD_PATH . 'covers/';
        
        // Create directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Generate safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        $targetFile = $targetDir . $fileName;
        
        // Validate file type for images
        if ($type == 'cover') {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                return false;
            }
        }
        
        // Validate file type for PDF
        if ($type == 'ebook') {
            if ($extension !== 'pdf') {
                return false;
            }
            // Check file size (50MB max)
            if ($file['size'] > 50 * 1024 * 1024) {
                return false;
            }
        }
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return $fileName;
        }
        
        return false;
    }
    
    // Sanitize input
    public static function sanitize($input) {
        if ($input === null) return '';
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    // Redirect
    public static function redirect($url) {
        header("Location: $url");
        exit;
    }
}
?>