<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
Auth::requireAdmin();

echo "<h1>📋 Copying PDF Files to Downloads Folder</h1>";

$sourceDir = UPLOAD_PATH . 'ebooks/';
$destDir = DOWNLOAD_PATH;

if (!file_exists($destDir)) {
    mkdir($destDir, 0777, true);
    echo "Created downloads folder.<br>";
}

if (file_exists($sourceDir)) {
    $files = scandir($sourceDir);
    $copied = 0;
    
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'pdf') {
            $source = $sourceDir . $file;
            $dest = $destDir . $file;
            
            if (copy($source, $dest)) {
                echo "✅ Copied: $file<br>";
                $copied++;
            } else {
                echo "❌ Failed to copy: $file<br>";
            }
        }
    }
    
    echo "<h3>Total files copied: $copied</h3>";
} else {
    echo "❌ Source directory not found: $sourceDir";
}

echo "<br><a href='link-pdfs.php'>Go to Link PDFs Tool</a>";
?>