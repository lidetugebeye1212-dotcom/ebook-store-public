<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();
$message = '';
$error = '';

// Get all books
$booksSql = "SELECT id, title, author, pdf_file FROM books ORDER BY title";
$booksResult = $db->query($booksSql);
$books = $booksResult->get_result();

// Handle PDF upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_pdf'])) {
    $bookId = (int)$_POST['book_id'];
    
    // Get book details
    $bookSql = "SELECT * FROM books WHERE id = ?";
    $bookStmt = $db->query($bookSql, [$bookId]);
    $book = $bookStmt->get_result()->fetch_assoc();
    
    if ($_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['pdf_file'];
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validate file type
        if ($fileExt !== 'pdf') {
            $error = 'Only PDF files are allowed!';
        } elseif ($fileSize > 50 * 1024 * 1024) { // 50MB max
            $error = 'File size must be less than 50MB';
        } else {
            // Create safe filename
            $safeFileName = preg_replace('/[^a-zA-Z0-9\s-]/', '', $book['title']);
            $safeFileName = str_replace(' ', '_', $safeFileName) . '_' . time() . '.pdf';
            
            $uploadPath = DOWNLOAD_PATH . $safeFileName;
            
            // Create directory if not exists
            if (!file_exists(DOWNLOAD_PATH)) {
                mkdir(DOWNLOAD_PATH, 0777, true);
            }
            
            // Delete old PDF if exists
            if (!empty($book['pdf_file']) && file_exists(DOWNLOAD_PATH . $book['pdf_file'])) {
                unlink(DOWNLOAD_PATH . $book['pdf_file']);
            }
            
            // Upload new PDF
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Update database
                $updateSql = "UPDATE books SET pdf_file = ?, file_size = ? WHERE id = ?";
                $db->query($updateSql, [$safeFileName, $fileSize, $bookId]);
                
                $message = 'PDF uploaded successfully!';
            } else {
                $error = 'Failed to upload file. Check folder permissions.';
            }
        }
    } else {
        $error = 'Please select a PDF file to upload.';
    }
}

// Handle PDF deletion
if (isset($_GET['delete_pdf']) && is_numeric($_GET['delete_pdf'])) {
    $bookId = (int)$_GET['delete_pdf'];
    
    // Get book details
    $bookSql = "SELECT * FROM books WHERE id = ?";
    $bookStmt = $db->query($bookSql, [$bookId]);
    $book = $bookStmt->get_result()->fetch_assoc();
    
    if ($book && !empty($book['pdf_file'])) {
        $filePath = DOWNLOAD_PATH . $book['pdf_file'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Update database
        $updateSql = "UPDATE books SET pdf_file = NULL, file_size = NULL WHERE id = ?";
        $db->query($updateSql, [$bookId]);
        
        $message = 'PDF deleted successfully!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload PDF Books - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo SITE_NAME; ?></h2>
                <p>Admin Panel</p>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="books.php"><i class="fas fa-book"></i> Books</a>
                <a href="add-book.php"><i class="fas fa-plus-circle"></i> Add Book</a>
                <a href="upload-pdf.php" class="active"><i class="fas fa-upload"></i> Upload PDF</a>
                <a href="categories.php"><i class="fas fa-tags"></i> Categories</a>
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="admin-header">
                <h1>Upload PDF Books</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Upload Form -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fas fa-upload"></i> Upload New PDF</h2>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Select Book</label>
                        <select name="book_id" class="form-control" required>
                            <option value="">-- Choose a book --</option>
                            <?php while($book = $books->fetch_assoc()): ?>
                                <option value="<?php echo $book['id']; ?>">
                                    <?php echo htmlspecialchars($book['title']); ?> 
                                    by <?php echo htmlspecialchars($book['author']); ?>
                                    <?php echo $book['pdf_file'] ? ' (Has PDF)' : ' (No PDF)'; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>PDF File (Max 50MB)</label>
                        <input type="file" name="pdf_file" class="form-control" accept=".pdf" required>
                        <small style="color: #636e72;">Only PDF files are allowed. Max size: 50MB</small>
                    </div>
                    
                    <button type="submit" name="upload_pdf" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload PDF
                    </button>
                </form>
            </div>
            
            <!-- Books with PDFs -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fas fa-file-pdf"></i> Books with PDFs</h2>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Author</th>
                            <th>PDF File</th>
                            <th>Size</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Reset pointer
                        $books->data_seek(0);
                        $hasPdf = false;
                        while($book = $books->fetch_assoc()): 
                            if (!empty($book['pdf_file'])):
                                $hasPdf = true;
                                $filePath = DOWNLOAD_PATH . $book['pdf_file'];
                                $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td>
                                    <i class="fas fa-file-pdf" style="color: #d63031;"></i>
                                    <?php echo $book['pdf_file']; ?>
                                </td>
                                <td><?php echo round($fileSize / 1024 / 1024, 2); ?> MB</td>
                                <td>
                                    <a href="../user/download.php?book_id=<?php echo $book['id']; ?>" 
                                       class="btn-action btn-view" target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="?delete_pdf=<?php echo $book['id']; ?>" 
                                       class="btn-action btn-delete"
                                       onclick="return confirm('Delete this PDF? Users will no longer be able to download it.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endif; endwhile; ?>
                        
                        <?php if (!$hasPdf): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-file-pdf" style="font-size: 3rem; color: #ccc;"></i>
                                    <p>No PDFs uploaded yet.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>