<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();

// Handle book deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $bookId = (int)$_GET['delete'];
    
    // Get book details to delete files
    $fileSql = "SELECT cover_image, pdf_file FROM books WHERE id = ?";
    $fileStmt = $db->query($fileSql, [$bookId]);
    $files = $fileStmt->get_result()->fetch_assoc();
    
    // Delete files
    if ($files) {
        if ($files['cover_image'] && file_exists(UPLOAD_PATH . 'covers/' . $files['cover_image'])) {
            unlink(UPLOAD_PATH . 'covers/' . $files['cover_image']);
        }
        if ($files['pdf_file'] && file_exists(DOWNLOAD_PATH . $files['pdf_file'])) {
            unlink(DOWNLOAD_PATH . $files['pdf_file']);
        }
    }
    
    // Delete from database
    $deleteSql = "DELETE FROM books WHERE id = ?";
    $db->query($deleteSql, [$bookId]);
    
    Functions::redirect('books.php?msg=deleted');
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $selectedIds = json_decode($_POST['selected_ids'], true);
    
    if (!empty($selectedIds)) {
        $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
        
        switch ($action) {
            case 'delete':
                $sql = "DELETE FROM books WHERE id IN ($placeholders)";
                break;
            case 'featured':
                $sql = "UPDATE books SET is_featured = 1 WHERE id IN ($placeholders)";
                break;
            case 'unfeatured':
                $sql = "UPDATE books SET is_featured = 0 WHERE id IN ($placeholders)";
                break;
            case 'bestseller':
                $sql = "UPDATE books SET bestseller = 1 WHERE id IN ($placeholders)";
                break;
        }
        
        if (isset($sql)) {
            $db->query($sql, $selectedIds);
        }
    }
    
    Functions::redirect('books.php?msg=bulk_updated');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? Functions::sanitize($_GET['search']) : '';

// Get total books count
$countSql = "SELECT COUNT(*) as total FROM books";
$countParams = [];

if ($search) {
    $countSql .= " WHERE title LIKE ? OR author LIKE ?";
    $searchTerm = "%$search%";
    $countParams = [$searchTerm, $searchTerm];
}

$countStmt = $db->query($countSql, $countParams);
$totalBooks = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalBooks / $limit);

// Get books
$booksSql = "SELECT b.*, c.name as category_name 
             FROM books b 
             LEFT JOIN categories c ON b.category_id = c.id";

if ($search) {
    $booksSql .= " WHERE b.title LIKE ? OR b.author LIKE ?";
    $booksParams = [$searchTerm, $searchTerm];
} else {
    $booksParams = [];
}

$booksSql .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
$booksParams[] = $limit;
$booksParams[] = $offset;

$booksStmt = $db->query($booksSql, $booksParams);
$books = $booksStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Ethiopian Flag Favicon -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
    
    <style>
        /* Additional styles for PDF column */
        .pdf-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.5rem;
            background: rgba(214, 48, 49, 0.1);
            color: #d63031;
            border-radius: 5px;
            font-size: 0.75rem;
        }
        
        .pdf-badge i {
            font-size: 0.8rem;
        }
        
        .pdf-actions {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        
        .btn-pdf {
            padding: 0.2rem 0.5rem;
            background: #d63031;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
        }
        
        .btn-pdf:hover {
            background: #b13030;
        }
        
        .btn-upload {
            padding: 0.2rem 0.5rem;
            background: #6c5ce7;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
        }
        
        .btn-upload:hover {
            background: #5849c2;
        }
        
        .file-size {
            font-size: 0.65rem;
            color: #636e72;
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
                <a href="books.php" class="active"><i class="fas fa-book"></i> Books</a>
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
                <h1>Manage Books</h1>
                <div style="display: flex; gap: 1rem;">
                    <a href="upload-pdf.php" class="btn btn-secondary">
                        <i class="fas fa-upload"></i> Upload PDF
                    </a>
                    <a href="add-book.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Book
                    </a>
                </div>
            </div>
            
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success">
                    <?php 
                    if ($_GET['msg'] == 'deleted') {
                        echo 'Book deleted successfully!';
                    } elseif ($_GET['msg'] == 'bulk_updated') {
                        echo 'Bulk action completed successfully!';
                    } elseif ($_GET['msg'] == 'added') {
                        echo 'Book added successfully!';
                    } elseif ($_GET['msg'] == 'updated') {
                        echo 'Book updated successfully!';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Filters and Search -->
            <div class="admin-card">
                <div class="filters-section">
                    <form method="GET" action="" class="filters-form">
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search books..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="form-group">
                                <select name="category" class="form-control">
                                    <option value="">All Categories</option>
                                    <?php
                                    $catSql = "SELECT * FROM categories ORDER BY name";
                                    $catResult = $db->query($catSql);
                                    $catResult2 = $catResult->get_result();
                                    while($cat = $catResult2->fetch_assoc()) {
                                        echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <select name="language" class="form-control">
                                    <option value="">All Languages</option>
                                    <option value="English">English</option>
                                    <option value="Amharic">Amharic</option>
                                    <option value="Afan Oromo">Afan Oromo</option>
                                    <option value="Tigrigna">Tigrigna</option>
                                    <option value="Somali">Somali</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <select name="pdf_status" class="form-control">
                                    <option value="">All Books</option>
                                    <option value="has_pdf">Has PDF</option>
                                    <option value="no_pdf">No PDF</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="books.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Books Table -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fas fa-list"></i> All Books</h2>
                    <div class="bulk-actions">
                        <select id="bulkActions" class="form-control" style="width: auto; display: inline-block;">
                            <option value="">Bulk Actions</option>
                            <option value="delete">Delete Selected</option>
                            <option value="featured">Mark as Featured</option>
                            <option value="unfeatured">Remove from Featured</option>
                            <option value="bestseller">Mark as Bestseller</option>
                        </select>
                        <button id="applyBulkAction" class="btn btn-secondary">Apply</button>
                    </div>
                </div>
                
                <table class="admin-table data-table">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>ID</th>
                            <th>Cover</th>
                            <th data-sortable>Title</th>
                            <th data-sortable>Author</th>
                            <th data-sortable>Category</th>
                            <th data-sortable>Language</th>
                            <th data-sortable>Price</th>
                            <th>Status</th>
                            <th>PDF File</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($books->num_rows > 0): ?>
                            <?php while($book = $books->fetch_assoc()): 
                                // Check if PDF exists
                                $hasPdf = !empty($book['pdf_file']);
                                $pdfPath = DOWNLOAD_PATH . $book['pdf_file'];
                                $pdfExists = $hasPdf && file_exists($pdfPath);
                                $fileSize = $pdfExists ? filesize($pdfPath) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="select-item" value="<?php echo $book['id']; ?>">
                                    </td>
                                    <td>#<?php echo $book['id']; ?></td>
                                    <td>
                                        <?php if($book['cover_image']): ?>
                                            <img src="../assets/uploads/covers/<?php echo $book['cover_image']; ?>" 
                                                 alt="<?php echo $book['title']; ?>" 
                                                 style="width: 50px; height: 70px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                            <span style="font-size: 2rem;">📚</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo Functions::truncateText($book['title'], 30); ?>
                                        <?php if(isset($book['country']) && $book['country'] == 'Ethiopia'): ?>
                                            <span class="ethiopian-badge-admin">
                                                <i class="fas fa-flag"></i> Ethiopian
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><?php echo $book['category_name'] ?? 'Uncategorized'; ?></td>
                                    <td><?php echo $book['language'] ?? 'English'; ?></td>
                                    <td><?php echo Functions::formatPrice($book['price']); ?></td>
                                    <td>
                                        <?php if($book['is_featured']): ?>
                                            <span class="status-badge" style="background: rgba(108, 92, 231, 0.1); color: #6c5ce7;">
                                                Featured
                                            </span>
                                        <?php endif; ?>
                                        <?php if($book['bestseller']): ?>
                                            <span class="status-badge" style="background: rgba(253, 203, 110, 0.1); color: #fdcb6e;">
                                                Bestseller
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="pdf-actions">
                                            <?php if ($hasPdf && $pdfExists): ?>
                                                <span class="pdf-badge">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </span>
                                                <span class="file-size">
                                                    <?php echo round($fileSize / 1024 / 1024, 2); ?> MB
                                                </span>
                                                <a href="../user/download.php?book_id=<?php echo $book['id']; ?>" 
                                                   class="btn-pdf" target="_blank">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            <?php elseif ($hasPdf && !$pdfExists): ?>
                                                <span class="pdf-badge" style="background: rgba(214, 48, 49, 0.1); color: #d63031;">
                                                    <i class="fas fa-exclamation-triangle"></i> File Missing
                                                </span>
                                                <a href="upload-pdf.php?book_id=<?php echo $book['id']; ?>" class="btn-upload">
                                                    <i class="fas fa-upload"></i> Re-upload
                                                </a>
                                            <?php else: ?>
                                                <span class="pdf-badge" style="background: rgba(99, 110, 114, 0.1); color: #636e72;">
                                                    <i class="fas fa-times"></i> No PDF
                                                </span>
                                                <a href="upload-pdf.php?book_id=<?php echo $book['id']; ?>" class="btn-upload">
                                                    <i class="fas fa-upload"></i> Upload
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.3rem;">
                                            <a href="edit-book.php?id=<?php echo $book['id']; ?>" class="btn-action btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $book['id']; ?>" class="btn-action btn-delete delete-btn"
                                               data-type="book" data-name="<?php echo htmlspecialchars($book['title']); ?>" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="../public/book-details.php?id=<?php echo $book['id']; ?>" 
                                               class="btn-action btn-view" target="_blank" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-book" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem; display: block;"></i>
                                    <p style="color: #636e72;">No books found</p>
                                    <a href="add-book.php" class="btn btn-primary" style="margin-top: 1rem;">
                                        Add Your First Book
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                               class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page+1; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
    <script>
        // Bulk actions
        document.getElementById('applyBulkAction').addEventListener('click', function() {
            const action = document.getElementById('bulkActions').value;
            const checkboxes = document.querySelectorAll('.select-item:checked');
            const selectedIds = Array.from(checkboxes).map(cb => cb.value);
            
            if (!action) {
                Swal.fire('No Action Selected', 'Please select a bulk action', 'warning');
                return;
            }
            
            if (selectedIds.length === 0) {
                Swal.fire('No Items Selected', 'Please select at least one book', 'warning');
                return;
            }
            
            Swal.fire({
                title: 'Confirm Bulk Action',
                text: `Are you sure you want to ${action} ${selectedIds.length} book(s)?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6c5ce7',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="bulk_action" value="${action}">
                        <input type="hidden" name="selected_ids" value='${JSON.stringify(selectedIds)}'>
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
        
        // Select all checkbox
        document.getElementById('selectAll').addEventListener('change', function() {
            document.querySelectorAll('.select-item').forEach(cb => cb.checked = this.checked);
        });
    </script>
</body>
</html>