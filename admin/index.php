<?php
/**
 * Admin Index Page - Redirects to Dashboard
 * Ethiopian E-Book Store
 */

// Start session
require_once '../includes/session.php';

// Redirect to dashboard
header('Location: dashboard.php');
exit;
?>