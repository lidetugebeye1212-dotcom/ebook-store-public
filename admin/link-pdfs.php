<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
Auth::requireAdmin();

$db = Database::getInstance();
$message = '';

// Handle linking PDF to book
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_pdf'])) {
    $bookId = (int)$_POST['book_id'];
    $pdfFile = $_POST['pdf_file'];
    $sourcePath = $_POST['source_path'];
    
    // Copy file to downloads folder
    $destPath = DOWNLOAD_PATH . basename($pdfFile);
    
    if (copy($sourcePath, $destPath)) {
        $fileSize = filesize($destPath);
        
        // Update database
        $updateSql = "UPDATE books SET pdf_file = ?, file_size = ? WHERE id = ?";
        $db->query($updateSql, [basename($pdfFile), $fileSize, $bookId]);
        
        $message = "✅ Successfully linked PDF to book!";
    } else {
        $message = "❌ Failed to copy file. Check permissions.";
    }
}

// Get all real PDF files from various locations
$realPdfs = [];

// Check assets/uploads/ebooks/
$ebooksPath = UPLOAD_PATH . 'ebooks/';
if (file_exists($ebooksPath)) {
    $files = scandir($ebooksPath);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'pdf') {
            $fullPath = $ebooksPath . $file;
            $realPdfs[] = [
                'name' => $file,
                'path' => $fullPath,
                'size' => filesize($fullPath),
                'location' => 'assets/uploads/ebooks/'
            ];
        }
    }
}

// Check downloads folder for any real PDFs
if (file_exists(DOWNLOAD_PATH)) {
    $files = scandir(DOWNLOAD_PATH);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'pdf') {
            $fullPath = DOWNLOAD_PATH . $file;
            $size = filesize($fullPath);
            // Only include if it's a real file (>1MB or not a sample)
            if ($size > 1024 * 1024 || strpos($file, 'sample') === false) {
                $realPdfs[] = [
                    'name' => $file,
                    'path' => $fullPath,
                    'size' => $size,
                    'location' => 'downloads/'
                ];
            }
        }
    }
}

// Get all books that need PDFs
$booksSql = "SELECT id, title, author, pdf_file FROM books ORDER BY title";
$booksResult = $db->query($booksSql);
$books = $booksResult->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link PDF Files to Books</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .pdf-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        .pdf-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        .pdf-card:hover {
            border-color: #6c5ce7;
            transform: translateY(-3px);
        }
        .pdf-card.real {
            background: rgba(0, 184, 148, 0.1);
            border-left: 4px solid #00b894;
        }
        .pdf-name {
            font-weight: 600;
            word-break: break-all;
            font-size: 0.9rem;
        }
        .pdf-size {
            color: #636e72;
            font-size: 0.8rem;
        }
        .pdf-location {
            font-size: 0.75rem;
            background: #e1e1e1;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            display: inline-block;
            margin: 0.3rem 0;
        }
        .select-book {
            width: 100%;
            margin: 0.5rem 0;
            padding: 0.5rem;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
        }
        .btn-link {
            background: #6c5ce7;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-weight: 500;
        }
        .btn-link:hover {
            background: #5849c2;
        }
        .success-message {
            background: rgba(0, 184, 148, 0.1);
            color: #00b894;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #00b894;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo SITE_NAME; ?></h2>
                <p>Admin Panel</p>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="books.php"><i class="fas fa-book"></i> Books</a>
                <a href="upload-pdf.php"><i class="fas fa-upload"></i> Upload PDF</a>
                <a href="link-pdfs.php" class="active"><i class="fas fa-link"></i> Link PDFs</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="admin-header">
                <h1>🔗 Link Real PDF Files to Books</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fas fa-file-pdf"></i> Real PDF Files Found (<?php echo count($realPdfs); ?>)</h2>
                </div>
                
                <div class="pdf-grid">
                    <?php foreach ($realPdfs as $pdf): ?>
                        <div class="pdf-card <?php echo $pdf['size'] > 1024*1024 ? 'real' : ''; ?>">
                            <div class="pdf-name">
                                <i class="fas fa-file-pdf" style="color: #d63031;"></i>
                                <?php echo htmlspecialchars($pdf['name']); ?>
                            </div>
                            <div class="pdf-size">
                                Size: <?php echo round($pdf['size'] / 1024 / 1024, 2); ?> MB
                            </div>
                            <div class="pdf-location">
                                Location: <?php echo $pdf['location']; ?>
                            </div>
                            
                            <form method="POST">
                                <input type="hidden" name="pdf_file" value="<?php echo htmlspecialchars($pdf['name']); ?>">
                                <input type="hidden" name="source_path" value="<?php echo htmlspecialchars($pdf['path']); ?>">
                                
                                <select name="book_id" class="select-book" required>
                                    <option value="">-- Select Book --</option>
                                    <?php 
                                    $books->data_seek(0);
                                    while($book = $books->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $book['id']; ?>">
                                            <?php echo htmlspecialchars(substr($book['title'], 0, 50)); ?> 
                                            (<?php echo $book['pdf_file'] ? 'has PDF' : 'no PDF'; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                
                                <button type="submit" name="link_pdf" class="btn-link">
                                    <i class="fas fa-link"></i> Link to Book
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fas fa-book"></i> Books Missing PDFs</h2>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Current PDF</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $books->data_seek(0);
                        $missingCount = 0;
                        while($book = $books->fetch_assoc()): 
                            if (empty($book['pdf_file']) || !file_exists(DOWNLOAD_PATH . $book['pdf_file'])):
                                $missingCount++;
                        ?>
                            <tr>
                                <td>#<?php echo $book['id']; ?></td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td>
                                    <?php if (!empty($book['pdf_file'])): ?>
                                        <span style="color: #d63031;">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            File missing: <?php echo $book['pdf_file']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #636e72;">No PDF assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="upload-pdf.php?book_id=<?php echo $book['id']; ?>" class="btn-action btn-edit">
                                        <i class="fas fa-upload"></i> Upload
                                    </a>
                                </td>
                            </tr>
                        <?php endif; endwhile; ?>
                        
                        <?php if ($missingCount == 0): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">All books have valid PDFs! 🎉</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>