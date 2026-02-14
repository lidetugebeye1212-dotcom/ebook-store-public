<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();
$message = '';
$error = '';

// Handle Add Category
if (isset($_POST['add_category'])) {
    $name = Functions::sanitize($_POST['name']);
    $description = Functions::sanitize($_POST['description']);
    $icon = Functions::sanitize($_POST['icon']);
    
    if (!empty($name)) {
        $checkSql = "SELECT id FROM categories WHERE name = ?";
        $checkStmt = $db->query($checkSql, [$name]);
        
        if ($checkStmt->get_result()->num_rows == 0) {
            $insertSql = "INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)";
            $db->query($insertSql, [$name, $description, $icon]);
            $message = "Category added successfully!";
        } else {
            $error = "Category already exists!";
        }
    } else {
        $error = "Category name is required!";
    }
}

// Handle Edit Category
if (isset($_POST['edit_category'])) {
    $id = (int)$_POST['id'];
    $name = Functions::sanitize($_POST['name']);
    $description = Functions::sanitize($_POST['description']);
    $icon = Functions::sanitize($_POST['icon']);
    
    if (!empty($name)) {
        $updateSql = "UPDATE categories SET name = ?, description = ?, icon = ? WHERE id = ?";
        $db->query($updateSql, [$name, $description, $icon, $id]);
        $message = "Category updated successfully!";
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if category has books
    $checkSql = "SELECT COUNT(*) as count FROM books WHERE category_id = ?";
    $checkStmt = $db->query($checkSql, [$id]);
    $bookCount = $checkStmt->get_result()->fetch_assoc()['count'];
    
    if ($bookCount == 0) {
        $deleteSql = "DELETE FROM categories WHERE id = ?";
        $db->query($deleteSql, [$id]);
        $message = "Category deleted successfully!";
    } else {
        $error = "Cannot delete category with $bookCount books. Move or delete books first.";
    }
}

// Get all categories
$categoriesSql = "SELECT c.*, 
                  (SELECT COUNT(*) FROM books WHERE category_id = c.id) as book_count 
                  FROM categories c 
                  ORDER BY c.name";
$categoriesResult = $db->query($categoriesSql);
$categories = $categoriesResult->get_result();

// Get category for editing
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editSql = "SELECT * FROM categories WHERE id = ?";
    $editStmt = $db->query($editSql, [$editId]);
    $editCategory = $editStmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/admin.css">
    
    <style>
        .category-icon-preview {
            font-size: 2rem;
            margin-right: 1rem;
        }
        .icon-selector {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .icon-option {
            text-align: center;
            padding: 0.5rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1.5rem;
        }
        .icon-option:hover,
        .icon-option.selected {
            background: #6c5ce7;
            color: white;
            border-color: #6c5ce7;
        }
        .book-count-badge {
            background: #6c5ce7;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
            margin-left: 0.5rem;
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
                <a href="categories.php" class="active"><i class="fas fa-tags"></i> Categories</a>
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                <a href="reviews.php"><i class="fas fa-star"></i> Reviews</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="admin-header">
                <h1>Manage Categories</h1>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="admin-grid" style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
                <!-- Add/Edit Category Form -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><i class="fas <?php echo $editCategory ? 'fa-edit' : 'fa-plus'; ?>"></i> 
                            <?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?>
                        </h2>
                    </div>
                    
                    <form method="POST" action="">
                        <?php if ($editCategory): ?>
                            <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="name">Category Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>" 
                                   placeholder="e.g., Amharic Fiction" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="icon">Category Icon</label>
                            <input type="text" class="form-control" id="icon" name="icon" 
                                   value="<?php echo $editCategory ? htmlspecialchars($editCategory['icon']) : ''; ?>" 
                                   placeholder="e.g., 📚 or fa-book">
                            <small style="color: #636e72;">You can use emoji or Font Awesome class (e.g., fa-book)</small>
                            
                            <div class="icon-selector">
                                <div class="icon-option" onclick="selectIcon('📚')">📚</div>
                                <div class="icon-option" onclick="selectIcon('📖')">📖</div>
                                <div class="icon-option" onclick="selectIcon('🏛️')">🏛️</div>
                                <div class="icon-option" onclick="selectIcon('🎭')">🎭</div>
                                <div class="icon-option" onclick="selectIcon('⚖️')">⚖️</div>
                                <div class="icon-option" onclick="selectIcon('🧸')">🧸</div>
                                <div class="icon-option" onclick="selectIcon('📝')">📝</div>
                                <div class="icon-option" onclick="selectIcon('👤')">👤</div>
                                <div class="icon-option" onclick="selectIcon('💻')">💻</div>
                                <div class="icon-option" onclick="selectIcon('🤖')">🤖</div>
                                <div class="icon-option" onclick="selectIcon('🔒')">🔒</div>
                                <div class="icon-option" onclick="selectIcon('🌐')">🌐</div>
                                <div class="icon-option" onclick="selectIcon('📊')">📊</div>
                                <div class="icon-option" onclick="selectIcon('📱')">📱</div>
                                <div class="icon-option" onclick="selectIcon('☁️')">☁️</div>
                                <div class="icon-option" onclick="selectIcon('🎓')">🎓</div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" 
                                      placeholder="Brief description of this category"><?php echo $editCategory ? htmlspecialchars($editCategory['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="<?php echo $editCategory ? 'edit_category' : 'add_category'; ?>" 
                                    class="btn btn-primary">
                                <i class="fas <?php echo $editCategory ? 'fa-save' : 'fa-plus-circle'; ?>"></i> 
                                <?php echo $editCategory ? 'Update Category' : 'Add Category'; ?>
                            </button>
                            
                            <?php if ($editCategory): ?>
                                <a href="categories.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Categories List -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2><i class="fas fa-list"></i> All Categories</h2>
                    </div>
                    
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Icon</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Books</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($categories->num_rows > 0): ?>
                                <?php while($category = $categories->fetch_assoc()): ?>
                                    <tr>
                                        <td style="font-size: 2rem; text-align: center;">
                                            <?php 
                                            if (!empty($category['icon'])) {
                                                if (strpos($category['icon'], 'fa-') === 0) {
                                                    echo '<i class="fas ' . $category['icon'] . '"></i>';
                                                } else {
                                                    echo $category['icon'];
                                                }
                                            } else {
                                                echo '📁';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(Functions::truncateText($category['description'] ?? '', 50)); ?>
                                        </td>
                                        <td>
                                            <span class="book-count-badge"><?php echo $category['book_count']; ?> books</span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $category['id']; ?>" class="btn-action btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($category['book_count'] == 0): ?>
                                                <a href="?delete=<?php echo $category['id']; ?>" 
                                                   class="btn-action btn-delete"
                                                   onclick="return confirm('Delete category <?php echo htmlspecialchars($category['name']); ?>?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="btn-action btn-delete disabled" 
                                                      title="Cannot delete - has <?php echo $category['book_count']; ?> books">
                                                    <i class="fas fa-trash"></i>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 3rem;">
                                        <i class="fas fa-tags" style="font-size: 3rem; color: #ccc;"></i>
                                        <p style="margin-top: 1rem;">No categories yet. Add your first category!</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Ethiopian Categories Quick Add -->
            <div class="admin-card" style="margin-top: 2rem;">
                <div class="admin-card-header">
                    <h2><i class="fas fa-flag"></i> Quick Add Ethiopian Categories</h2>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
                    <button class="btn btn-secondary" onclick="quickAdd('Amharic Fiction', '📚', 'Fictional works written in Amharic')">
                        <i class="fas fa-book"></i> Amharic Fiction
                    </button>
                    <button class="btn btn-secondary" onclick="quickAdd('Ethiopian Literature', '📖', 'Classic and contemporary Ethiopian literary works')">
                        <i class="fas fa-book-open"></i> Ethiopian Literature
                    </button>
                    <button class="btn btn-secondary" onclick="quickAdd('Ethiopian History', '🏛️', 'Historical books about Ethiopia')">
                        <i class="fas fa-landmark"></i> Ethiopian History
                    </button>
                    <button class="btn btn-secondary" onclick="quickAdd('Ethiopian Culture', '🎭', 'Books about Ethiopian traditions and culture')">
                        <i class="fas fa-music"></i> Ethiopian Culture
                    </button>
                    <button class="btn btn-secondary" onclick="quickAdd('Ethiopian Politics', '⚖️', 'Political science and governance in Ethiopia')">
                        <i class="fas fa-gavel"></i> Ethiopian Politics
                    </button>
                    <button class="btn btn-secondary" onclick="quickAdd('Children Books', '🧸', 'Children literature in Ethiopian languages')">
                        <i class="fas fa-child"></i> Children Books
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function selectIcon(icon) {
            document.getElementById('icon').value = icon;
            
            // Highlight selected icon
            document.querySelectorAll('.icon-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            event.target.classList.add('selected');
        }
        
        function quickAdd(name, icon, description) {
            document.getElementById('name').value = name;
            document.getElementById('icon').value = icon;
            document.getElementById('description').value = description;
            
            // Scroll to form
            document.querySelector('.admin-card').scrollIntoView({ behavior: 'smooth' });
        }
        
        // Auto-hide alerts after 3 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 3000);
    </script>
</body>
</html>