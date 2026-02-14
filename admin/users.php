<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Auth::requireAdmin();

$db = Database::getInstance();

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = (int)$_GET['delete'];
    
    // Don't allow deleting own account
    if ($userId != SessionManager::getUserId()) {
        $deleteSql = "DELETE FROM users WHERE id = ? AND user_type = 'customer'";
        $db->query($deleteSql, [$userId]);
    }
    
    Functions::redirect('users.php?msg=deleted');
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? Functions::sanitize($_GET['search']) : '';

// Get total users count
$countSql = "SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'";
$countParams = [];

if ($search) {
    $countSql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $searchTerm = "%$search%";
    $countParams = [$searchTerm, $searchTerm, $searchTerm];
}

$countStmt = $db->query($countSql, $countParams);
$totalUsers = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $limit);

// Get users
$usersSql = "SELECT *, 
             (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as total_orders,
             (SELECT SUM(total_amount) FROM orders WHERE user_id = users.id AND status = 'completed') as total_spent
             FROM users 
             WHERE user_type = 'customer'";

if ($search) {
    $usersSql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $usersParams = [$searchTerm, $searchTerm, $searchTerm];
} else {
    $usersParams = [];
}

$usersSql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$usersParams[] = $limit;
$usersParams[] = $offset;

$usersStmt = $db->query($usersSql, $usersParams);
$users = $usersStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <!-- Ethiopian Flag Favicon - PASTE HERE -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
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
                <a href="users.php" class="active"><i class="fas fa-users"></i> Users</a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                <a href="categories.php"><i class="fas fa-tags"></i> Categories</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="admin-header">
                <h1>Manage Users</h1>
            </div>
            
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success">
                    <?php 
                    if ($_GET['msg'] == 'deleted') {
                        echo 'User deleted successfully!';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Search -->
            <div class="admin-card">
                <form method="GET" action="" class="filters-form">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by name, email or username..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="users.php" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Users Table -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2><i class="fas fa-users"></i> Registered Users</h2>
                    <span>Total: <?php echo $totalUsers; ?> users</span>
                </div>
                
                <table class="admin-table data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th data-sortable>Full Name</th>
                            <th data-sortable>Username</th>
                            <th data-sortable>Email</th>
                            <th data-sortable>Registered</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users->num_rows > 0): ?>
                            <?php while($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $user['id']; ?></td>
                                    <td><?php echo $user['full_name'] ?: 'N/A'; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td><?php echo $user['total_orders']; ?></td>
                                    <td><?php echo Functions::formatPrice($user['total_spent'] ?: 0); ?></td>
                                    <td>
                                        <span class="status-badge" style="background: rgba(0, 184, 148, 0.1); color: #00b894;">
                                            Active
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view-user.php?id=<?php echo $user['id']; ?>" class="btn-action btn-view">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($user['id'] != SessionManager::getUserId()): ?>
                                            <a href="?delete=<?php echo $user['id']; ?>" class="btn-action btn-delete delete-btn"
                                               data-type="user" data-name="<?php echo $user['username']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 3rem;">
                                    <i class="fas fa-users" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem; display: block;"></i>
                                    <p style="color: #636e72;">No users found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                               class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>