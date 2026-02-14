<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

echo "<h1>🔍 PDF File System Check</h1>";

$db = Database::getInstance();

// Get all books with PDFs
$sql = "SELECT id, title, pdf_file, file_size FROM books WHERE pdf_file IS NOT NULL AND pdf_file != ''";
$result = $db->query($sql);
$books = $result->get_result();

echo "<h2>Books with PDF entries in database:</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Title</th><th>PDF Filename</th><th>Status</th><th>Size</th><th>Path</th></tr>";

while ($book = $books->fetch_assoc()) {
    $filePath = DOWNLOAD_PATH . $book['pdf_file'];
    $fileExists = file_exists($filePath);
    $fileSize = $fileExists ? filesize($filePath) : 0;
    
    echo "<tr>";
    echo "<td>{$book['id']}</td>";
    echo "<td>" . htmlspecialchars($book['title']) . "</td>";
    echo "<td>{$book['pdf_file']}</td>";
    echo "<td style='color: " . ($fileExists ? 'green' : 'red') . "; font-weight: bold;'>" . ($fileExists ? '✅ EXISTS' : '❌ MISSING') . "</td>";
    echo "<td>" . ($fileSize > 0 ? round($fileSize / 1024 / 1024, 2) . ' MB' : 'N/A') . "</td>";
    echo "<td>" . DOWNLOAD_PATH . $book['pdf_file'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check if downloads directory exists
echo "<h2>Downloads Directory:</h2>";
if (file_exists(DOWNLOAD_PATH)) {
    echo "✅ Downloads directory exists: " . DOWNLOAD_PATH . "<br>";
    
    // List all PDF files in directory
    $files = scandir(DOWNLOAD_PATH);
    $pdfFiles = array_filter($files, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) == 'pdf';
    });
    
    echo "<h3>PDF files in directory (" . count($pdfFiles) . " found):</h3>";
    echo "<ul>";
    foreach ($pdfFiles as $file) {
        $filePath = DOWNLOAD_PATH . $file;
        $fileSize = filesize($filePath);
        echo "<li>📄 $file - " . round($fileSize / 1024 / 1024, 2) . " MB</li>";
    }
    echo "</ul>";
} else {
    echo "❌ Downloads directory does NOT exist!<br>";
}

// Check database vs filesystem mismatch
echo "<h2>Database vs Filesystem Mismatch:</h2>";
$db->query("SELECT id, title, pdf_file FROM books WHERE pdf_file IS NOT NULL AND pdf_file != ''");
$dbBooks = $result->get_result();
$dbFiles = [];
while ($book = $dbBooks->fetch_assoc()) {
    $dbFiles[] = $book['pdf_file'];
}

$actualFiles = scandir(DOWNLOAD_PATH);
$actualPdfFiles = array_filter($actualFiles, function($file) {
    return pathinfo($file, PATHINFO_EXTENSION) == 'pdf';
});

$missingFromDisk = array_diff($dbFiles, $actualPdfFiles);
$extraOnDisk = array_diff($actualPdfFiles, $dbFiles);

if (!empty($missingFromDisk)) {
    echo "<p style='color: red;'>❌ Files in database but missing on disk:</p>";
    echo "<ul>";
    foreach ($missingFromDisk as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
}

if (!empty($extraOnDisk)) {
    echo "<p style='color: orange;'>⚠️ Files on disk but not in database:</p>";
    echo "<ul>";
    foreach ($extraOnDisk as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
}
?>