<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    Functions::redirect('books.php');
}

$bookId = (int)$_GET['id'];

// Get book details
$bookSql = "SELECT * FROM books WHERE id = ?";
$bookStmt = $db->query($bookSql, [$bookId]);
$book = $bookStmt->get_result()->fetch_assoc();

if (!$book) {
    Functions::redirect('books.php');
}

// Get categories for dropdown
$categoriesSql = "SELECT * FROM categories ORDER BY name";
$categoriesResult = $db->query($categoriesSql);
$categories = $categoriesResult->get_result();

// Handle PDF deletion
if (isset($_GET['delete_pdf'])) {
    // Delete old PDF
    if (!empty($book['pdf_file']) && file_exists(DOWNLOAD_PATH . $book['pdf_file'])) {
        unlink(DOWNLOAD_PATH . $book['pdf_file']);
    }
    
    // Update database
    $updateSql = "UPDATE books SET pdf_file = NULL, file_size = NULL WHERE id = ?";
    $db->query($updateSql, [$bookId]);
    
    Functions::redirect('edit-book.php?id=' . $bookId . '&msg=pdf_deleted');
}

$error = '';
$success = '';

if (isset($_GET['msg']) && $_GET['msg'] == 'pdf_deleted') {
    $success = 'PDF file deleted successfully!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $title = Functions::sanitize($_POST['title']);
    $author = Functions::sanitize($_POST['author']);
    $description = Functions::sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $language = Functions::sanitize($_POST['language']);
    $country = Functions::sanitize($_POST['country']);
    $page_count = (int)$_POST['page_count'];
    $publisher = Functions::sanitize($_POST['publisher']);
    $isbn = Functions::sanitize($_POST['isbn']);
    $publication_date = $_POST['publication_date'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $bestseller = isset($_POST['bestseller']) ? 1 : 0;
    $award_winning = isset($_POST['award_winning']) ? 1 : 0;
    
    // Handle cover image upload
    $cover_image = $book['cover_image'];
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        // Validate image
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['cover_image']['type'];
        
        if (in_array($fileType, $allowed)) {
            // Delete old cover
            if ($cover_image && file_exists(UPLOAD_PATH . 'covers/' . $cover_image)) {
                unlink(UPLOAD_PATH . 'covers/' . $cover_image);
            }
            $cover_image = Functions::uploadFile($_FILES['cover_image'], 'cover');
        } else {
            $error = 'Invalid image format. Please use JPG, PNG or GIF.';
        }
    }
    
    // Handle PDF upload
    $pdf_file = $book['pdf_file'];
    $file_size = $book['file_size'];
    
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
        $file = $_FILES['pdf_file'];
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Validate PDF
        if ($fileExt !== 'pdf') {
            $error = 'Only PDF files are allowed!';
        } elseif ($fileSize > 50 * 1024 * 1024) { // 50MB max
            $error = 'PDF file size must be less than 50MB';
        } else {
            // Delete old PDF
            if ($pdf_file && file_exists(DOWNLOAD_PATH . $pdf_file)) {
                unlink(DOWNLOAD_PATH . $pdf_file);
            }
            
            // Create safe filename
            $safeFileName = preg_replace('/[^a-zA-Z0-9\s-]/', '', $title);
            $safeFileName = str_replace(' ', '_', $safeFileName) . '_' . time() . '.pdf';
            $uploadPath = DOWNLOAD_PATH . $safeFileName;
            
            // Create directory if not exists
            if (!file_exists(DOWNLOAD_PATH)) {
                mkdir(DOWNLOAD_PATH, 0777, true);
            }
            
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $pdf_file = $safeFileName;
                $file_size = $fileSize;
            } else {
                $error = 'Failed to upload PDF file. Check folder permissions.';
            }
        }
    }
    
    // Update book if no errors
    if (empty($error)) {
        $sql = "UPDATE books SET 
                title = ?, author = ?, description = ?, price = ?, category_id = ?,
                language = ?, country = ?, page_count = ?, publisher = ?, isbn = ?,
                publication_date = ?, cover_image = ?, pdf_file = ?, file_size = ?,
                is_featured = ?, bestseller = ?, award_winning = ?
                WHERE id = ?";
        
        $params = [
            $title, $author, $description, $price, $category_id, $language, $country,
            $page_count, $publisher, $isbn, $publication_date, $cover_image, $pdf_file, $file_size,
            $is_featured, $bestseller, $award_winning, $bookId
        ];
        
        $stmt = $db->query($sql, $params);
        
        if ($stmt) {
            Functions::redirect('books.php?msg=updated');
        } else {
            $error = 'Failed to update book. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- Ethiopian Flag Favicon -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
    
    <style>
        /* PDF Management Styles */
        .current-file {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #6c5ce7;
        }
        
        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .file-icon {
            font-size: 2rem;
            color: #d63031;
        }
        
        .file-details {
            flex: 1;
        }
        
        .file-name {
            font-weight: 600;
            color: #2d3436;
            word-break: break-all;
        }
        
        .file-meta {
            color: #636e72;
            font-size: 0.85rem;
            margin-top: 0.3rem;
        }
        
        .file-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-file {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.3s;
        }
        
        .btn-download {
            background: #00b894;
            color: white;
        }
        
        .btn-download:hover {
            background: #00a187;
            transform: translateY(-2px);
        }
        
        .btn-delete-file {
            background: #d63031;
            color: white;
        }
        
        .btn-delete-file:hover {
            background: #b13030;
            transform: translateY(-2px);
        }
        
        .btn-upload {
            background: #6c5ce7;
            color: white;
        }
        
        .btn-upload:hover {
            background: #5849c2;
            transform: translateY(-2px);
        }
        
        .upload-hint {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #fff3cd;
            border-radius: 6px;
            color: #856404;
            font-size: 0.85rem;
        }
        
        .alert-success {
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
                <a href="upload-pdf.php"><i class="fas fa-upload"></i> Upload PDF</a>
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                <a href="categories.php"><i class="fas fa-tags"></i> Categories</a>
                <a href="reviews.php"><i class="fas fa-star"></i> Reviews</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="admin-header">
                <h1>Edit Book: <?php echo htmlspecialchars($book['title']); ?></h1>
                <div style="display: flex; gap: 1rem;">
                    <a href="upload-pdf.php?book_id=<?php echo $bookId; ?>" class="btn btn-secondary">
                        <i class="fas fa-upload"></i> Upload PDF
                    </a>
                    <a href="books.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Books
                    </a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <!-- PDF Management Section -->
                <div class="admin-card-header">
                    <h2><i class="fas fa-file-pdf"></i> PDF File Management</h2>
                </div>
                
                <div class="current-file">
                    <?php if (!empty($book['pdf_file'])): 
                        $filePath = DOWNLOAD_PATH . $book['pdf_file'];
                        $fileExists = file_exists($filePath);
                        $fileSize = $fileExists ? filesize($filePath) : 0;
                    ?>
                        <div class="file-info">
                            <div class="file-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="file-details">
                                <div class="file-name">
                                    <?php echo $book['pdf_file']; ?>
                                </div>
                                <div class="file-meta">
                                    <?php if ($fileExists): ?>
                                        <i class="fas fa-check-circle" style="color: #00b894;"></i>
                                        File exists on server
                                        <?php if ($fileSize > 0): ?>
                                            • Size: <?php echo round($fileSize / 1024 / 1024, 2); ?> MB
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-triangle" style="color: #d63031;"></i>
                                        File missing from server!
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($book['file_size'])): ?>
                                        • DB Record: <?php echo round($book['file_size'] / 1024 / 1024, 2); ?> MB
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="file-actions">
                                <?php if ($fileExists): ?>
                                    <a href="../user/download.php?book_id=<?php echo $bookId; ?>" 
                                       class="btn-file btn-download" target="_blank">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                <?php endif; ?>
                                <a href="?id=<?php echo $bookId; ?>&delete_pdf=1" 
                                   class="btn-file btn-delete-file"
                                   onclick="return confirm('Are you sure you want to delete this PDF? This action cannot be undone.')">
                                    <i class="fas fa-trash"></i> Delete PDF
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="file-info">
                            <div class="file-icon">
                                <i class="fas fa-file-pdf" style="color: #636e72;"></i>
                            </div>
                            <div class="file-details">
                                <div class="file-name">No PDF file uploaded yet</div>
                                <div class="file-meta">Upload a PDF file for this book</div>
                            </div>
                            <div class="file-actions">
                                <a href="upload-pdf.php?book_id=<?php echo $bookId; ?>" 
                                   class="btn-file btn-upload">
                                    <i class="fas fa-upload"></i> Upload PDF
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="upload-hint">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> You can upload a new PDF using the form below or use the dedicated
                        <a href="upload-pdf.php?book_id=<?php echo $bookId; ?>" style="color: #6c5ce7; font-weight: 500;">PDF Upload Page</a>
                        for better file management.
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <form id="bookForm" method="POST" enctype="multipart/form-data" class="book-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Book Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($book['title']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="author">Author *</label>
                            <input type="text" class="form-control" id="author" name="author" 
                                   value="<?php echo htmlspecialchars($book['author']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($book['description']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price ($) *</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                   value="<?php echo $book['price']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php while($category = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php echo $category['id'] == $book['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo $category['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="language">Language *</label>
                            <select class="form-control" id="language" name="language" required>
                                <option value="English" <?php echo $book['language'] == 'English' ? 'selected' : ''; ?>>English</option>
                                <option value="Amharic" <?php echo $book['language'] == 'Amharic' ? 'selected' : ''; ?>>አማርኛ</option>
                                <option value="Afan Oromo" <?php echo $book['language'] == 'Afan Oromo' ? 'selected' : ''; ?>>Afan Oromo</option>
                                <option value="Tigrigna" <?php echo $book['language'] == 'Tigrigna' ? 'selected' : ''; ?>>ትግርኛ</option>
                                <option value="Somali" <?php echo $book['language'] == 'Somali' ? 'selected' : ''; ?>>Somali</option>
                                <option value="Other" <?php echo $book['language'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country *</label>
                            <select class="form-control" id="country" name="country" required>
                                <option value="Ethiopia" <?php echo $book['country'] == 'Ethiopia' ? 'selected' : ''; ?>>Ethiopia</option>
                                <option value="International" <?php echo $book['country'] == 'International' ? 'selected' : ''; ?>>International</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="page_count">Page Count</label>
                            <input type="number" class="form-control" id="page_count" name="page_count" 
                                   value="<?php echo $book['page_count']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="publisher">Publisher</label>
                            <input type="text" class="form-control" id="publisher" name="publisher" 
                                   value="<?php echo htmlspecialchars($book['publisher']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" class="form-control" id="isbn" name="isbn" 
                                   value="<?php echo htmlspecialchars($book['isbn']); ?>" placeholder="978-99944-2-010-6">
                        </div>
                        
                        <div class="form-group">
                            <label for="publication_date">Publication Date</label>
                            <input type="date" class="form-control" id="publication_date" name="publication_date" 
                                   value="<?php echo $book['publication_date']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cover_image">Cover Image</label>
                            <input type="file" class="form-control-file" id="cover_image" name="cover_image" accept="image/*">
                            <small style="color: #636e72;">Leave empty to keep current cover. Accepted: JPG, PNG, GIF</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="pdf_file">PDF File (Optional)</label>
                            <input type="file" class="form-control-file" id="pdf_file" name="pdf_file" accept=".pdf">
                            <small style="color: #636e72;">
                                Leave empty to keep current PDF. Max size: 50MB
                                <?php if (!empty($book['pdf_file'])): ?>
                                    <br>Uploading a new file will replace the existing PDF.
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div id="imagePreview" style="margin-top: 10px;">
                            <?php if($book['cover_image']): ?>
                                <img src="../assets/uploads/covers/<?php echo $book['cover_image']; ?>" 
                                     alt="Current cover" style="max-width: 200px; max-height: 300px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <p style="margin-top: 10px; color: #636e72;">
                                    <i class="fas fa-image"></i> Current cover image
                                </p>
                            <?php else: ?>
                                <p style="color: #636e72;">No cover image uploaded</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_featured" <?php echo $book['is_featured'] ? 'checked' : ''; ?>> 
                                <span>Feature this book</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="bestseller" <?php echo $book['bestseller'] ? 'checked' : ''; ?>> 
                                <span>Mark as Bestseller</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="award_winning" <?php echo $book['award_winning'] ? 'checked' : ''; ?>> 
                                <span>Award Winning</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">
                            <i class="fas fa-save"></i> Update Book
                        </button>
                        <a href="books.php" class="btn btn-secondary btn-large">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
    <script>
        // Preview image before upload
        document.getElementById('cover_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.innerHTML = `
                        <img src="${e.target.result}" style="max-width: 200px; max-height: 300px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                        <p style="margin-top: 10px; color: #00b894;">
                            <i class="fas fa-check-circle"></i> New image selected
                        </p>
                    `;
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Show file name for PDF
        document.getElementById('pdf_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                alert(`Selected PDF: ${file.name}\nSize: ${fileSize} MB\n\nThis will replace the existing PDF when you save.`);
            }
        });
        
        // Confirm before leaving with unsaved changes
        let formChanged = false;
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', () => formChanged = true);
        });
        
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
        
        document.getElementById('bookForm').addEventListener('submit', function() {
            formChanged = false;
        });
    </script>
</body>
</html>