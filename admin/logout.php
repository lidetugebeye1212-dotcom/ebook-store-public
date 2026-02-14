<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->logout();

// Redirect to login page
Functions::redirect('../public/login.php');
exit;
?>