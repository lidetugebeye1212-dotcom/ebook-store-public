<?php
require_once 'includes/config.php';

echo "<h1>📥 Test Real Download</h1>";

$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 1;

echo "<h2>Testing download for Book ID: $bookId</h2>";

// Include database
require_once 'includes/database.php';
$db = Database::getInstance();

// Get book info
$sql = "SELECT * FROM books WHERE id = ?";
$stmt = $db->query($sql, [$bookId]);
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    die("Book not found!");
}

echo "<h3>Book Information:</h3>";
echo "<ul>";
echo "<li><strong>Title:</strong> " . htmlspecialchars($book['title']) . "</li>";
echo "<li><strong>PDF File in DB:</strong> " . ($book['pdf_file'] ?: 'Not set') . "</li>";
echo "<li><strong>File Size in DB:</strong> " . ($book['file_size'] ? round($book['file_size'] / 1024 / 1024, 2) . ' MB' : 'N/A') . "</li>";
echo "</ul>";

if (empty($book['pdf_file'])) {
    echo "<p style='color: red;'>❌ No PDF file associated with this book in database.</p>";
} else {
    $filePath = DOWNLOAD_PATH . $book['pdf_file'];
    echo "<h3>File System Check:</h3>";
    echo "<ul>";
    echo "<li><strong>Full Path:</strong> " . $filePath . "</li>";
    echo "<li><strong>File exists:</strong> " . (file_exists($filePath) ? '✅ YES' : '❌ NO') . "</li>";
    
    if (file_exists($filePath)) {
        $actualSize = filesize($filePath);
        echo "<li><strong>Actual file size:</strong> " . round($actualSize / 1024 / 1024, 2) . " MB</li>";
        echo "<li><strong>Permissions:</strong> " . substr(sprintf('%o', fileperms($filePath)), -4) . "</li>";
        echo "<li><strong>Readable:</strong> " . (is_readable($filePath) ? '✅ YES' : '❌ NO') . "</li>";
    }
    echo "</ul>";
    
    echo "<p><a href='user/download.php?book_id=$bookId' target='_blank' style='display: inline-block; padding: 10px 20px; background: #6c5ce7; color: white; text-decoration: none; border-radius: 5px;'>Test Download via download.php</a></p>";
    
    echo "<p><a href='downloads/" . $book['pdf_file'] . "' target='_blank' style='display: inline-block; padding: 10px 20px; background: #00b894; color: white; text-decoration: none; border-radius: 5px;'>Direct File Access (should be blocked)</a></p>";
}
?>