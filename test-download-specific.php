<?php
require_once 'includes/config.php';

$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 167;

echo "<h1>🔍 Test Download for Book ID: $bookId</h1>";

require_once 'includes/database.php';
$db = Database::getInstance();

// Get book info
$sql = "SELECT * FROM books WHERE id = ?";
$stmt = $db->query($sql, [$bookId]);
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    die("Book not found!");
}

echo "<h2>Book: " . htmlspecialchars($book['title']) . "</h2>";
echo "<p>Author: " . htmlspecialchars($book['author']) . "</p>";
echo "<p>PDF File in DB: <strong>" . ($book['pdf_file'] ?: 'None') . "</strong></p>";
echo "<p>File Size in DB: " . ($book['file_size'] ? round($book['file_size']/1024/1024,2).' MB' : 'Unknown') . "</p>";

if (!empty($book['pdf_file'])) {
    $filePath = DOWNLOAD_PATH . $book['pdf_file'];
    echo "<p>Full Path: " . $filePath . "</p>";
    echo "<p>File Exists: " . (file_exists($filePath) ? '✅ YES' : '❌ NO') . "</p>";
    
    if (file_exists($filePath)) {
        $actualSize = filesize($filePath);
        echo "<p>Actual File Size: " . round($actualSize/1024/1024,2) . " MB</p>";
        echo "<p>Readable: " . (is_readable($filePath) ? '✅ YES' : '❌ NO') . "</p>";
        
        echo "<p><a href='user/download.php?book_id=$bookId' target='_blank' style='display: inline-block; padding: 10px 20px; background: #6c5ce7; color: white; text-decoration: none; border-radius: 5px;'>Test Download via download.php</a></p>";
        
        // Direct file access test
        echo "<p><a href='downloads/" . $book['pdf_file'] . "' target='_blank' style='display: inline-block; padding: 10px 20px; background: #00b894; color: white; text-decoration: none; border-radius: 5px;'>Direct File Access</a></p>";
    }
}

echo "<hr>";
echo "<h3>Other Books with Real PDFs:</h3>";
$realSql = "SELECT id, title, pdf_file FROM books WHERE pdf_file IS NOT NULL AND file_size > 1000000";
$realResult = $db->query($realSql);
$realBooks = $realResult->get_result();

while ($b = $realBooks->fetch_assoc()) {
    echo "<p>📚 <a href='?id={$b['id']}'>ID {$b['id']}: {$b['title']}</a> - {$b['pdf_file']}</p>";
}
?>