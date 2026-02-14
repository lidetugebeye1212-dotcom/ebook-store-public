<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Disable output buffering
while (ob_get_level()) {
    ob_end_clean();
}

Auth::requireLogin();

$db = Database::getInstance();
$userId = SessionManager::getUserId();

// Check if book_id is provided
if (!isset($_GET['book_id']) || !is_numeric($_GET['book_id'])) {
    $_SESSION['error'] = 'Invalid book ID.';
    header('Location: orders.php');
    exit;
}

$bookId = (int)$_GET['book_id'];

// Check if user purchased this book
$checkSql = "SELECT o.id, o.status, o.order_number
             FROM orders o 
             JOIN order_details od ON o.id = od.order_id 
             WHERE o.user_id = ? AND od.book_id = ?";
$checkStmt = $db->query($checkSql, [$userId, $bookId]);
$order = $checkStmt->get_result()->fetch_assoc();

if (!$order) {
    $_SESSION['error'] = 'You have not purchased this book.';
    header('Location: ../public/book-details.php?id=' . $bookId);
    exit;
}

if ($order['status'] !== 'completed') {
    $_SESSION['error'] = 'Your order is not completed yet. Status: ' . $order['status'];
    header('Location: orders.php?order=' . urlencode($order['order_number']));
    exit;
}

// Get book details
$bookSql = "SELECT * FROM books WHERE id = ?";
$bookStmt = $db->query($bookSql, [$bookId]);
$book = $bookStmt->get_result()->fetch_assoc();

if (!$book) {
    $_SESSION['error'] = 'Book not found.';
    header('Location: orders.php');
    exit;
}

// Check if PDF exists
if (empty($book['pdf_file'])) {
    $_SESSION['error'] = 'No PDF file available for this book.';
    header('Location: orders.php?order=' . urlencode($order['order_number']));
    exit;
}

// Define the full file path
$filePath = DOWNLOAD_PATH . $book['pdf_file'];

// Check if file exists
if (!file_exists($filePath)) {
    $_SESSION['error'] = 'PDF file not found on server. Please contact support.';
    header('Location: orders.php?order=' . urlencode($order['order_number']));
    exit;
}

// Log the download
$logSql = "INSERT INTO book_downloads (user_id, book_id, download_count) 
           VALUES (?, ?, 1) 
           ON DUPLICATE KEY UPDATE download_count = download_count + 1, last_downloaded = NOW()";
$db->query($logSql, [$userId, $bookId]);

// Get file info
$fileSize = filesize($filePath);
$fileName = $book['title'] . '.pdf';
// Clean filename for download
$fileName = preg_replace('/[^a-zA-Z0-9\s-]/', '', $fileName);
$fileName = str_replace(' ', '_', $fileName) . '.pdf';

// IMPORTANT: Force download headers
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream'); // Force download
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $fileSize);

// Clear any output buffer
if (ob_get_level()) {
    ob_end_clean();
}

// Output the file
readfile($filePath);
exit;
?>

/**
 * Generate a sample PDF file for books that don't have a real PDF
 */
function generateSamplePDF($book, $order) {
    $orderNumber = $order['order_number'] ?? 'N/A';
    $orderDate = date('F d, Y');
    
    $content = "%PDF-1.4\n";
    $content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $content .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";
    $content .= "4 0 obj\n<< /Length 500 >>\nstream\nBT\n/F1 28 Tf\n100 700 Td\n( Ethiopian E-Book Store ) Tj\n0 -50 Td\n/F1 20 Tf\n(" . addslashes($book['title']) . ") Tj\n0 -30 Td\n/F1 14 Tf\n(By " . addslashes($book['author']) . ") Tj\n0 -30 Td\n(Price: $" . number_format($book['price'], 2) . ") Tj\n0 -30 Td\n(Order #: " . $orderNumber . ") Tj\n0 -30 Td\n(Date: " . $orderDate . ") Tj\n0 -50 Td\n(This is a sample PDF for testing purposes.) Tj\n0 -20 Td\n(Your actual book will be available after purchase.) Tj\n0 -30 Td\n(Thank you for shopping at Ethiopian E-Book Store!) Tj\n";
    
    if (isset($book['country']) && $book['country'] == 'Ethiopia') {
        $content .= "0 -30 Td\n(🇪🇹 Ethiopian Book) Tj\n";
    }
    
    $content .= "ET\nendstream\nendobj\n";
    $content .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    $content .= "xref\n0 6\n0000000000 65535 f\n0000000010 00000 n\n0000000056 00000 n\n0000000111 00000 n\n0000000280 00000 n\n0000000600 00000 n\n";
    $content .= "trailer\n<< /Size 6 /Root 1 0 R >>\n";
    $content .= "startxref\n750\n%%EOF";
    
    return $content;
}
?>