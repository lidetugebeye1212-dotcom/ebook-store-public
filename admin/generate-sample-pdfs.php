<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();

// Get all books that don't have PDF files
$booksSql = "SELECT * FROM books WHERE pdf_file IS NULL OR pdf_file = ''";
$booksResult = $db->query($booksSql);
$books = $booksResult->get_result();

echo "<h1>Generating Sample PDFs for Books</h1>";

// Create downloads directory if it doesn't exist
if (!file_exists(DOWNLOAD_PATH)) {
    mkdir(DOWNLOAD_PATH, 0777, true);
    echo "Created downloads directory.<br>";
}

$count = 0;

while ($book = $books->fetch_assoc()) {
    // Create a safe filename
    $filename = preg_replace('/[^a-zA-Z0-9\s-]/', '', $book['title']);
    $filename = str_replace(' ', '_', $filename) . '.pdf';
    $filepath = DOWNLOAD_PATH . $filename;
    
    // Generate sample PDF content
    $content = generatePDF($book);
    
    // Save the file
    if (file_put_contents($filepath, $content)) {
        // Update database
        $updateSql = "UPDATE books SET pdf_file = ? WHERE id = ?";
        $db->query($updateSql, [$filename, $book['id']]);
        
        echo "✅ Generated PDF for: " . $book['title'] . "<br>";
        $count++;
    } else {
        echo "❌ Failed to generate PDF for: " . $book['title'] . "<br>";
    }
}

echo "<br>Total PDFs generated: " . $count . "<br>";
echo "<br><a href='../user/orders.php'>Go to Orders Page to Test Downloads</a>";

function generatePDF($book) {
    $content = "%PDF-1.4\n";
    $content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $content .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";
    $content .= "4 0 obj\n<< /Length 350 >>\nstream\nBT\n/F1 28 Tf\n100 700 Td\n( Ethiopian E-Book Store ) Tj\n0 -50 Td\n/F1 20 Tf\n(" . $book['title'] . ") Tj\n0 -30 Td\n/F1 14 Tf\n(By " . $book['author'] . ") Tj\n0 -40 Td\n(Price: $" . number_format($book['price'], 2) . ") Tj\n0 -30 Td\n(Language: " . ($book['language'] ?? 'English') . ") Tj\n0 -30 Td\n(Country: " . ($book['country'] ?? 'Ethiopia') . ") Tj\n0 -50 Td\n(This is a sample PDF for testing purposes.) Tj\n0 -20 Td\n(Your actual book will be available soon.) Tj\n0 -30 Td\n(Thank you for shopping at Ethiopian E-Book Store!) Tj\nET\nendstream\nendobj\n";
    $content .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    $content .= "xref\n0 6\n0000000000 65535 f\n0000000010 00000 n\n0000000056 00000 n\n0000000111 00000 n\n0000000280 00000 n\n0000000500 00000 n\n";
    $content .= "trailer\n<< /Size 6 /Root 1 0 R >>\n";
    $content .= "startxref\n650\n%%EOF";
    
    return $content;
}
?>