<?php
require_once 'includes/config.php';

echo "<h1>📊 PDF File Verification</h1>";

$files = scandir(DOWNLOAD_PATH);
echo "<h2>Files in Downloads Folder:</h2>";
echo "<ul>";
foreach ($files as $file) {
    if ($file != '.' && $file != '..' && strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'pdf') {
        $path = DOWNLOAD_PATH . $file;
        $size = filesize($path);
        $mb = round($size / 1024 / 1024, 2);
        $color = $mb > 1 ? 'green' : 'orange';
        echo "<li style='color: $color;'>📄 $file - $mb MB " . ($mb > 1 ? '✅ REAL FILE' : '⚠️ SAMPLE FILE') . "</li>";
    }
}
echo "</ul>";

echo "<p><a href='user/download.php?book_id=167' target='_blank'>Test Download Python Handwritten (Book ID 167)</a></p>";
?>