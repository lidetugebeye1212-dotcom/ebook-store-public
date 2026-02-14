<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ebook_store_ethiopia');

// Application configuration
define('SITE_NAME', 'Ethiopian E-Book Store');
define('BASE_URL', 'http://localhost/ebook-store');
define('SITE_URL', 'http://localhost/ebook-store');

// File paths
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/ebook-store/assets/uploads/');
define('DOWNLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/ebook-store/downloads/');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('Africa/Addis_Ababa');
?>