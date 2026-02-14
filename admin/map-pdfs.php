<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
Auth::requireAdmin();

$db = Database::getInstance();
$message = '';

// Handle manual mapping
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['map_pdf'])) {
    $bookId = (int)$_POST['book_id'];
    $pdfFile = $_POST['pdf_file'];
    
    // Get file size
    $filePath = DOWNLOAD_PATH . $pdfFile;
    if (file_exists($filePath)) {
        $fileSize = filesize($filePath);
        
        // Update database
        $updateSql = "UPDATE books SET pdf_file = ?, file_size = ? WHERE id = ?";
        $db->query($updateSql, [$pdfFile, $fileSize, $bookId]);
        
        $message = "✅ Successfully mapped PDF to book!";
    } else {
        $message = "❌ PDF file not found in downloads folder!";
    }
}

// Get all real PDF files from downloads folder
$realPdfs = [];
if (file_exists(DOWNLOAD_PATH)) {
    $files = scandir(DOWNLOAD_PATH);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'pdf') {
            $filePath = DOWNLOAD_PATH . $file;
            $size = filesize($filePath);
            // Only include real files (size > 1MB)
            if ($size > 1024 * 1024) {
                $realPdfs[] = [
                    'name' => $file,
                    'size' => $size,
                    'size_mb' => round($size / 1024 / 1024, 2)
                ];
            }
        }
    }
}

// Get all books
$booksSql = "SELECT id, title, author, pdf_file, file_size FROM books ORDER BY title";
$booksResult = $db->query($booksSql);
$books = $booksResult->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map PDF Files</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .mapping-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        .pdf-list {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            max-height: 500px;
            overflow-y: auto;
        }
        .pdf-item {
            background: white;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            border-left: 4px solid #00b894;
            cursor: pointer;
            transition: all 0.3s;
        }
        .pdf-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .pdf-item.selected {
            background: #6c5ce7;
            color: white;
        }
        .pdf-item .size {
            font-size: 0.8rem;
            color: #636e72;
        }
        .pdf-item.selected .size {
            color: rgba(255,255,255,0.8);
        }
        .book-item {
            background: white;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            border-left: 4px solid #6c5ce7;
        }
        .btn-map {
            background: #00b894;
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 1rem;
        }
        .btn-map:hover {
            background: #00a187;
        }
        .current-pdf {
            font-size: 0.8rem;
            color: #636e72;
            margin-top: 0.3rem;
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
                <a href="dashboard.php">Dashboard</a>
                <a href="books.php">Books</a>
                <a href="map-pdfs.php" class="active">Map PDFs</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="admin-header">
                <h1>🗺️ Map Real PDF Files to Books</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="mapping-grid">
                <!-- Left side: Real PDF files -->
                <div>
                    <h2>📁 Real PDF Files (<?php echo count($realPdfs); ?>)</h2>
                    <div class="pdf-list" id="pdfList">
                        <?php foreach ($realPdfs as $pdf): ?>
                            <div class="pdf-item" onclick="selectPDF('<?php echo $pdf['name']; ?>', <?php echo $pdf['size']; ?>)">
                                <strong><i class="fas fa-file-pdf" style="color: #d63031;"></i> <?php echo $pdf['name']; ?></strong>
                                <div class="size"><?php echo $pdf['size_mb']; ?> MB</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Right side: Books -->
                <div>
                    <h2>📚 Books Needing PDFs</h2>
                    <div style="max-height: 500px; overflow-y: auto;">
                        <?php while($book = $books->fetch_assoc()): 
                            $needsPdf = empty($book['pdf_file']) || $book['file_size'] < 1024 * 1024;
                        ?>
                            <div class="book-item" style="<?php echo $needsPdf ? 'border-left-color: #d63031;' : ''; ?>">
                                <strong><?php echo htmlspecialchars($book['title']); ?></strong>
                                <div>by <?php echo htmlspecialchars($book['author']); ?></div>
                                <div class="current-pdf">
                                    Current: <?php echo $book['pdf_file'] ?: 'None'; ?> 
                                    (<?php echo $book['file_size'] ? round($book['file_size']/1024/1024,2).' MB' : '0 MB'; ?>)
                                </div>
                                
                                <form method="POST" style="margin-top: 0.5rem;">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <input type="hidden" name="pdf_file" id="pdf_<?php echo $book['id']; ?>" value="">
                                    <button type="submit" name="map_pdf" class="btn-map" 
                                            onclick="return confirm('Map selected PDF to this book?')"
                                            style="padding: 0.3rem; font-size: 0.9rem;">
                                        <i class="fas fa-link"></i> Map PDF
                                    </button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let selectedPDF = '';
        let selectedSize = 0;
        
        function selectPDF(filename, size) {
            selectedPDF = filename;
            selectedSize = size;
            
            // Update all hidden inputs
            document.querySelectorAll('input[type="hidden"][name="pdf_file"]').forEach(input => {
                input.value = filename;
            });
            
            // Highlight selected PDF
            document.querySelectorAll('.pdf-item').forEach(item => {
                item.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            alert(`Selected PDF: ${filename} (${(size/1024/1024).toFixed(2)} MB)`);
        }
    </script>
</body>
</html>