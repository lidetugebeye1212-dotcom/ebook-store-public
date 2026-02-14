<?php
require_once 'includes/config.php';

echo "<h1>🔍 PDF File Locator</h1>";

// Check all possible upload locations
$possiblePaths = [
    'downloads' => DOWNLOAD_PATH,
    'assets/uploads/ebooks' => UPLOAD_PATH . 'ebooks/',
    'assets/uploads' => UPLOAD_PATH,
    'root/downloads' => $_SERVER['DOCUMENT_ROOT'] . '/downloads/',
];

echo "<h2>Checking Possible PDF Locations:</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Location</th><th>Path</th><th>Exists</th><th>PDF Files</th></tr>";

foreach ($possiblePaths as $name => $path) {
    $exists = file_exists($path) ? '✅ YES' : '❌ NO';
    $pdfCount = 0;
    $pdfList = [];
    
    if (file_exists($path)) {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'pdf') {
                $pdfCount++;
                $pdfList[] = $file;
            }
        }
    }
    
    echo "<tr>";
    echo "<td><strong>$name</strong></td>";
    echo "<td>$path</td>";
    echo "<td>$exists</td>";
    echo "<td>";
    if ($pdfCount > 0) {
        echo "📄 Found $pdfCount PDF files:<br>";
        echo "<ul>";
        foreach ($pdfList as $pdf) {
            $fullPath = $path . $pdf;
            $size = file_exists($fullPath) ? round(filesize($fullPath) / 1024 / 1024, 2) . ' MB' : 'unknown';
            echo "<li>$pdf ($size)</li>";
        }
        echo "</ul>";
    } else {
        echo "No PDF files found";
    }
    echo "</td>";
    echo "</tr>";
}
echo "</table>";

// Check database records
echo "<h2>Database PDF Records:</h2>";
require_once 'includes/database.php';
$db = Database::getInstance();

$sql = "SELECT id, title, pdf_file, file_size FROM books WHERE pdf_file IS NOT NULL AND pdf_file != ''";
$result = $db->query($sql);
$books = $result->get_result();

if ($books->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Title</th><th>PDF File</th><th>File Size</th><th>Status</th></tr>";
    
    while ($book = $books->fetch_assoc()) {
        $filePath = DOWNLOAD_PATH . $book['pdf_file'];
        $exists = file_exists($filePath) ? '✅ On Server' : '❌ Missing';
        $fileSize = $book['file_size'] ? round($book['file_size'] / 1024 / 1024, 2) . ' MB' : 'Unknown';
        
        echo "<tr>";
        echo "<td>{$book['id']}</td>";
        echo "<td>" . htmlspecialchars($book['title']) . "</td>";
        echo "<td>{$book['pdf_file']}</td>";
        echo "<td>$fileSize</td>";
        echo "<td style='color: " . (file_exists($filePath) ? 'green' : 'red') . "; font-weight: bold;'>$exists</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No PDF records in database.</p>";
}

// Show config
echo "<h2>Configuration:</h2>";
echo "<pre>";
echo "DOWNLOAD_PATH: " . DOWNLOAD_PATH . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "</pre>";
?>