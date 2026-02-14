<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireLogin();

$db = Database::getInstance();
$userId = SessionManager::getUserId();

if (!isset($_GET['book_id']) || !is_numeric($_GET['book_id'])) {
    die('Invalid request');
}

$bookId = (int)$_GET['book_id'];

// Verify purchase (simplified - add your full verification here)
$checkSql = "SELECT o.id FROM orders o 
             JOIN order_details od ON o.id = od.order_id 
             WHERE o.user_id = ? AND od.book_id = ? AND o.status = 'completed'";
$checkStmt = $db->query($checkSql, [$userId, $bookId]);
if ($checkStmt->get_result()->num_rows === 0) {
    die('You have not purchased this book');
}

// Get book info
$bookSql = "SELECT * FROM books WHERE id = ?";
$bookStmt = $db->query($bookSql, [$bookId]);
$book = $bookStmt->get_result()->fetch_assoc();

if (!$book || empty($book['pdf_file'])) {
    die('Book or PDF not found');
}

$filePath = DOWNLOAD_PATH . $book['pdf_file'];
if (!file_exists($filePath)) {
    die('PDF file not found');
}

// Force download with HTML5 download attribute approach
?>
<!DOCTYPE html>
<html>
<head>
    <title>Downloading...</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .download-message {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #6c5ce7;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="download-message">
        <div class="spinner"></div>
        <h2>Your download is starting...</h2>
        <p>If the download doesn't start automatically, 
           <a href="download.php?book_id=<?php echo $bookId; ?>" target="_blank">click here</a>.</p>
    </div>
    
    <script>
        // Force download using hidden iframe
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = 'download.php?book_id=<?php echo $bookId; ?>';
        document.body.appendChild(iframe);
        
        // Redirect back after 3 seconds
        setTimeout(() => {
            window.location.href = 'orders.php?order=<?php echo urlencode($order['order_number'] ?? ''); ?>';
        }, 3000);
    </script>
</body>
</html>