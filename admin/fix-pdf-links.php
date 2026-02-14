<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_pdf'])) {
    $bookId = (int)$_POST['book_id'];
    $pdfFile = $_POST['pdf_file'];
    
    $filePath = DOWNLOAD_PATH . $pdfFile;
    
    if (file_exists($filePath)) {
        $fileSize = filesize($filePath);
        $updateSql = "UPDATE books SET pdf_file = ?, file_size = ? WHERE id = ?";
        $db->query($updateSql, [$pdfFile, $fileSize, $bookId]);
        $message = "PDF linked successfully!";
    } else {
        $message = "File does not exist!";
    }
}

// Get all books
$booksSql = "SELECT * FROM books ORDER BY title";
$booksResult = $db->query($booksSql);
$books = $booksResult->get_result();

// Get all PDF files in downloads folder
$pdfFiles = [];
if (file_exists(DOWNLOAD_PATH)) {
    $files = scandir(DOWNLOAD_PATH);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
            $pdfFiles[] = $file;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix PDF Links</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Fix PDF Links</h2>
            </div>
        </div>
        
        <div class="main-content">
            <h1>Manual PDF Assignment</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Select Book:</label>
                    <select name="book_id" class="form-control" required>
                        <option value="">-- Choose Book --</option>
                        <?php while($book = $books->fetch_assoc()): ?>
                            <option value="<?php echo $book['id']; ?>">
                                <?php echo htmlspecialchars($book['title']); ?> 
                                (Current PDF: <?php echo $book['pdf_file'] ?: 'None'; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Select PDF File:</label>
                    <select name="pdf_file" class="form-control" required>
                        <option value="">-- Choose PDF --</option>
                        <?php foreach($pdfFiles as $file): ?>
                            <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="fix_pdf" class="btn btn-primary">Link PDF to Book</button>
            </form>
            
            <hr>
            
            <h2>Current PDF Files in Folder:</h2>
            <ul>
                <?php foreach($pdfFiles as $file): ?>
                    <li>📄 <?php echo $file; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>
</html>