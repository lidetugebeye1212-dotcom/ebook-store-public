<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_upload'])) {
    $uploadDir = DOWNLOAD_PATH . 'batch/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $files = $_FILES['pdf_files'];
    $fileCount = count($files['name']);
    $successCount = 0;
    $errorCount = 0;
    
    for ($i = 0; $i < $fileCount; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $fileName = $files['name'][$i];
            $fileTmp = $files['tmp_name'][$i];
            $fileSize = $files['size'][$i];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if ($fileExt === 'pdf' && $fileSize <= 50 * 1024 * 1024) {
                // Try to match with book title
                $bookTitle = pathinfo($fileName, PATHINFO_FILENAME);
                $bookTitle = str_replace('_', ' ', $bookTitle);
                
                $searchSql = "SELECT id, title FROM books WHERE title LIKE ?";
                $searchTerm = "%$bookTitle%";
                $searchStmt = $db->query($searchSql, [$searchTerm]);
                $book = $searchStmt->get_result()->fetch_assoc();
                
                if ($book) {
                    // Create safe filename
                    $safeFileName = preg_replace('/[^a-zA-Z0-9\s-]/', '', $book['title']);
                    $safeFileName = str_replace(' ', '_', $safeFileName) . '_' . time() . '.pdf';
                    
                    $uploadPath = DOWNLOAD_PATH . $safeFileName;
                    
                    if (move_uploaded_file($fileTmp, $uploadPath)) {
                        // Update database
                        $updateSql = "UPDATE books SET pdf_file = ?, file_size = ? WHERE id = ?";
                        $db->query($updateSql, [$safeFileName, $fileSize, $book['id']]);
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } else {
                    // Save to batch folder for manual assignment
                    move_uploaded_file($fileTmp, $uploadDir . $fileName);
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }
    }
    
    $message = "Batch upload complete. Success: $successCount, Failed: $errorCount";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Upload PDFs - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/admin.css">
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
                <a href="batch-upload-pdfs.php" class="active"><i class="fas fa-layer-group"></i> Batch Upload</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="admin-header">
                <h1>Batch Upload PDFs</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fas fa-layer-group"></i> Upload Multiple PDFs</h2>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Select PDF Files</label>
                        <input type="file" name="pdf_files[]" class="form-control" accept=".pdf" multiple required>
                        <small style="color: #636e72;">
                            You can select multiple PDF files. The system will try to match filenames with book titles.
                            <br>Filename example: "Fikir_Eske_Mekabir.pdf" will match "Fikir Eske Mekabir"
                        </small>
                    </div>
                    
                    <button type="submit" name="batch_upload" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload All
                    </button>
                </form>
            </div>
            
            <!-- Unmatched Files -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fas fa-question-circle"></i> Unmatched Files</h2>
                </div>
                
                <?php
                $batchDir = DOWNLOAD_PATH . 'batch/';
                if (file_exists($batchDir)) {
                    $files = scandir($batchDir);
                    $hasUnmatched = false;
                    
                    foreach ($files as $file) {
                        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'pdf') {
                            $hasUnmatched = true;
                            break;
                        }
                    }
                    
                    if ($hasUnmatched):
                ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): 
                                if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'pdf'):
                                    $filePath = $batchDir . $file;
                                    $fileSize = filesize($filePath);
                            ?>
                                <tr>
                                    <td><?php echo $file; ?></td>
                                    <td><?php echo round($fileSize / 1024 / 1024, 2); ?> MB</td>
                                    <td>
                                        <a href="assign-pdf.php?file=<?php echo urlencode($file); ?>" 
                                           class="btn-action btn-edit">
                                            <i class="fas fa-tag"></i> Assign to Book
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; color: #636e72;">No unmatched files found.</p>
                <?php 
                    endif;
                } 
                ?>
            </div>
        </div>
    </div>
</body>
</html>