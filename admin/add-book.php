<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();

// Get categories for dropdown
$categoriesSql = "SELECT * FROM categories ORDER BY name";
$categoriesResult = $db->query($categoriesSql);
$categories = $categoriesResult->get_result();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if form was submitted properly
    if (!isset($_POST['title']) || empty($_POST['title'])) {
        $error = 'Please fill in all required fields.';
    } else {
        // Sanitize and validate inputs
        $title = Functions::sanitize($_POST['title']);
        $author = Functions::sanitize($_POST['author']);
        $description = Functions::sanitize($_POST['description']);
        $price = (float)$_POST['price'];
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $language = Functions::sanitize($_POST['language'] ?? 'English');
        $country = Functions::sanitize($_POST['country'] ?? 'Ethiopia');
        $page_count = !empty($_POST['page_count']) ? (int)$_POST['page_count'] : null;
        $publisher = !empty($_POST['publisher']) ? Functions::sanitize($_POST['publisher']) : null;
        $isbn = !empty($_POST['isbn']) ? Functions::sanitize($_POST['isbn']) : null;
        $publication_date = !empty($_POST['publication_date']) ? $_POST['publication_date'] : null;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $bestseller = isset($_POST['bestseller']) ? 1 : 0;
        $award_winning = isset($_POST['award_winning']) ? 1 : 0;
        
        // Validate required fields
        if (empty($title) || empty($author) || empty($description) || empty($price) || empty($category_id)) {
            $error = 'Please fill in all required fields.';
        } elseif ($price <= 0) {
            $error = 'Price must be greater than 0.';
        } else {
            // Handle file uploads
            $cover_image = '';
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $upload_result = Functions::uploadFile($_FILES['cover_image'], 'cover');
                if ($upload_result) {
                    $cover_image = $upload_result;
                } else {
                    $error = 'Failed to upload cover image.';
                }
            }
            
            $pdf_file = '';
            $file_size = null;
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['pdf_file'];
                
                // Check file size (max 50MB)
                if ($file['size'] > 50 * 1024 * 1024) {
                    $error = 'PDF file size must be less than 50MB. Your file is ' . round($file['size'] / 1024 / 1024, 2) . 'MB.';
                } else {
                    $upload_result = Functions::uploadFile($file, 'ebook');
                    if ($upload_result) {
                        $pdf_file = $upload_result;
                        $file_size = $file['size'];
                    } else {
                        $error = 'Failed to upload PDF file.';
                    }
                }
            }
            
            // Insert book if no errors
            if (empty($error)) {
                $sql = "INSERT INTO books (title, author, description, price, category_id, language, country, 
                                          page_count, publisher, isbn, publication_date, cover_image, pdf_file, file_size,
                                          is_featured, bestseller, award_winning) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $params = [
                    $title, $author, $description, $price, $category_id, $language, $country,
                    $page_count, $publisher, $isbn, $publication_date, $cover_image, $pdf_file, $file_size,
                    $is_featured, $bestseller, $award_winning
                ];
                
                try {
                    $stmt = $db->query($sql, $params);
                    Functions::redirect('books.php?msg=added');
                } catch (Exception $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- Ethiopian Flag Favicon -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
    
    <style>
        .upload-progress {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e1e1e1;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            width: 0%;
            transition: width 0.3s;
        }
        
        .file-info {
            font-size: 0.85rem;
            color: #636e72;
            margin-top: 0.3rem;
        }
        
        .alert-error {
            background: rgba(214, 48, 49, 0.1);
            color: #d63031;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #d63031;
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
                <a href="add-book.php" class="active"><i class="fas fa-plus-circle"></i> Add Book</a>
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
                <h1>Add New Book</h1>
                <a href="books.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Books
                </a>
            </div>
            
            <?php if ($error): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <form id="bookForm" method="POST" enctype="multipart/form-data" class="book-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Book Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="author">Author *</label>
                            <input type="text" class="form-control" id="author" name="author" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="6" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price">Price ($) *</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php 
                                $categories->data_seek(0);
                                while($category = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $category['id']; ?>">
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
                                <option value="English">English</option>
                                <option value="Amharic">አማርኛ</option>
                                <option value="Afan Oromo">Afan Oromo</option>
                                <option value="Tigrigna">ትግርኛ</option>
                                <option value="Somali">Somali</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country *</label>
                            <select class="form-control" id="country" name="country" required>
                                <option value="Ethiopia">Ethiopia</option>
                                <option value="International">International</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="page_count">Page Count</label>
                            <input type="number" class="form-control" id="page_count" name="page_count">
                        </div>
                        
                        <div class="form-group">
                            <label for="publisher">Publisher</label>
                            <input type="text" class="form-control" id="publisher" name="publisher">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" class="form-control" id="isbn" name="isbn" placeholder="978-99944-2-010-6">
                        </div>
                        
                        <div class="form-group">
                            <label for="publication_date">Publication Date</label>
                            <input type="date" class="form-control" id="publication_date" name="publication_date">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cover_image">Cover Image</label>
                            <input type="file" class="form-control-file" id="cover_image" name="cover_image" accept="image/*">
                            <small style="color: #636e72;">Recommended size: 300x450px (JPG, PNG, GIF)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="pdf_file">PDF File (Max 50MB)</label>
                            <input type="file" class="form-control-file" id="pdf_file" name="pdf_file" accept=".pdf">
                            <div class="file-info" id="fileInfo"></div>
                        </div>
                    </div>
                    
                    <!-- Upload Progress Indicator -->
                    <div class="upload-progress" id="uploadProgress">
                        <div>Uploading... Please wait.</div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div id="imagePreview" style="margin-top: 10px;"></div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_featured"> 
                                <span>Feature this book</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="bestseller"> 
                                <span>Mark as Bestseller</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="award_winning"> 
                                <span>Award Winning</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large" id="submitBtn">
                            <i class="fas fa-save"></i> Save Book
                        </button>
                        <a href="books.php" class="btn btn-secondary btn-large">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Preview image before upload
        document.getElementById('cover_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').innerHTML = `
                        <img src="${e.target.result}" style="max-width: 200px; max-height: 300px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                        <p style="margin-top: 10px; color: #00b894;">
                            <i class="fas fa-check-circle"></i> Image selected
                        </p>
                    `;
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Show PDF file info
        document.getElementById('pdf_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / (1024 * 1024)).toFixed(2);
                const fileInfo = document.getElementById('fileInfo');
                
                if (file.size > 50 * 1024 * 1024) {
                    fileInfo.innerHTML = `<span style="color: #d63031;">
                        <i class="fas fa-exclamation-triangle"></i> File too large! ${fileSize}MB (Max: 50MB)
                    </span>`;
                    document.getElementById('submitBtn').disabled = true;
                } else {
                    fileInfo.innerHTML = `<span style="color: #00b894;">
                        <i class="fas fa-check-circle"></i> Selected: ${file.name} (${fileSize}MB)
                    </span>`;
                    document.getElementById('submitBtn').disabled = false;
                }
            }
        });
        
        // Show upload progress
        document.getElementById('bookForm').addEventListener('submit', function(e) {
            const pdfFile = document.getElementById('pdf_file').files[0];
            if (pdfFile && pdfFile.size > 50 * 1024 * 1024) {
                e.preventDefault();
                alert('PDF file is too large! Maximum size is 50MB.');
                return;
            }
            
            // Show progress bar for large files
            if (pdfFile && pdfFile.size > 10 * 1024 * 1024) {
                document.getElementById('uploadProgress').style.display = 'block';
                
                // Simulate progress (actual progress would need AJAX)
                let progress = 0;
                const interval = setInterval(function() {
                    progress += 10;
                    document.getElementById('progressFill').style.width = progress + '%';
                    if (progress >= 100) {
                        clearInterval(interval);
                    }
                }, 500);
            }
        });
    </script>
</body>
</html>