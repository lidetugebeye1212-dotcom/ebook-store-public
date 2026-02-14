<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$db = Database::getInstance();

// Get search parameters
$searchQuery = isset($_GET['q']) ? Functions::sanitize($_GET['q']) : '';
$language = isset($_GET['language']) ? Functions::sanitize($_GET['language']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 200;
$sort = isset($_GET['sort']) ? Functions::sanitize($_GET['sort']) : 'relevance';

$results = [];
$totalResults = 0;

// Build search query
if ($searchQuery) {
    $params = [];
    
    // Base query
    $sql = "SELECT b.*, c.name as category_name 
            FROM books b 
            LEFT JOIN categories c ON b.category_id = c.id 
            WHERE 1=1";
    
    // Search term
    if ($searchQuery) {
        $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.description LIKE ?)";
        $searchTerm = "%$searchQuery%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Language filter
    if ($language) {
        $sql .= " AND b.language = ?";
        $params[] = $language;
    }
    
    // Category filter
    if ($category > 0) {
        $sql .= " AND b.category_id = ?";
        $params[] = $category;
    }
    
    // Price range
    $sql .= " AND b.price BETWEEN ? AND ?";
    $params[] = $minPrice;
    $params[] = $maxPrice;
    
    // Sorting
    switch ($sort) {
        case 'price_low':
            $sql .= " ORDER BY b.price ASC";
            break;
        case 'price_high':
            $sql .= " ORDER BY b.price DESC";
            break;
        case 'newest':
            $sql .= " ORDER BY b.created_at DESC";
            break;
        case 'bestseller':
            $sql .= " ORDER BY b.bestseller DESC, b.created_at DESC";
            break;
        default:
            $sql .= " ORDER BY 
                        CASE 
                            WHEN b.bestseller = 1 THEN 1
                            WHEN b.award_winning = 1 THEN 2
                            ELSE 3
                        END,
                        b.created_at DESC";
    }
    
    $stmt = $db->query($sql, $params);
    $results = $stmt->get_result();
    $totalResults = $results->num_rows;
}

// Get categories for filter
$categoriesSql = "SELECT * FROM categories ORDER BY name";
$categoriesResult = $db->query($categoriesSql);
$categories = $categoriesResult->get_result();

// Get featured Ethiopian books for when no search
$featuredEthiopianSql = "SELECT b.*, c.name as category_name 
                         FROM books b 
                         LEFT JOIN categories c ON b.category_id = c.id 
                         WHERE b.country = 'Ethiopia' AND b.is_featured = 1 
                         LIMIT 6";
$featuredEthiopianResult = $db->query($featuredEthiopianSql);
$featuredEthiopian = $featuredEthiopianResult->get_result();

// Get popular categories with counts
$popularCategoriesSql = "SELECT c.name, c.icon, COUNT(b.id) as book_count 
                        FROM categories c 
                        LEFT JOIN books b ON c.id = b.category_id 
                        GROUP BY c.id 
                        HAVING book_count > 0 
                        ORDER BY book_count DESC 
                        LIMIT 8";
$popularCategoriesResult = $db->query($popularCategoriesSql);
$popularCategories = $popularCategoriesResult->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $searchQuery ? "Search Results for '$searchQuery' - " : "Search Books - "; ?>Ethiopian eBook Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/style.css">
    <link rel="stylesheet" href="/ebook-store/assets/css/responsive.css">
    
    <!-- Ethiopian Flag Favicon -->
    <link rel="icon" type="image/png" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='33.33' fill='%23078930'/%3E%3Crect y='33.33' width='100' height='33.34' fill='%23FCDD09'/%3E%3Crect y='66.67' width='100' height='33.33' fill='%23DA121A'/%3E%3Ccircle cx='50' cy='50' r='20' fill='%230F47AF'/%3E%3Ccircle cx='50' cy='50' r='15' fill='%23FCDD09'/%3E%3C/svg%3E">
    
    <style>
        /* Additional Search Page Styles */
        .search-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
        }

        .search-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 4rem 5%;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .search-hero::before {
            content: '🔍';
            position: absolute;
            top: -20px;
            right: 20px;
            font-size: 150px;
            opacity: 0.1;
            transform: rotate(15deg);
        }

        .search-hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: fadeInDown 1s ease;
        }

        .search-hero p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .advanced-search-form {
            max-width: 1000px;
            margin: 0 auto;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            animation: fadeInUp 1s ease 0.4s both;
        }

        .search-main {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-input-large {
            flex: 1;
            padding: 1.2rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }

        .search-input-large:focus {
            outline: none;
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .btn-large {
            padding: 1.2rem 3rem;
            font-size: 1.1rem;
            border-radius: 50px;
            border: none;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .btn-large:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(108, 92, 231, 0.4);
        }

        .search-filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .filter-group {
            text-align: left;
        }

        .filter-group label {
            display: block;
            color: white;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 10px;
            background: rgba(255,255,255,0.9);
            font-size: 0.95rem;
        }

        .price-range {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .price-range input {
            width: 45%;
        }

        .price-range span {
            color: white;
            font-weight: 600;
        }

        .search-results-container {
            max-width: 1400px;
            margin: 3rem auto;
            padding: 0 5%;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .results-header h2 {
            font-size: 1.8rem;
            color: #333;
        }

        .results-header p {
            color: #6c5ce7;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .results-sort {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .results-sort select {
            padding: 0.5rem 2rem 0.5rem 1rem;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 0.95rem;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236c5ce7' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            appearance: none;
        }

        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .no-results i {
            font-size: 5rem;
            color: #ccc;
            margin-bottom: 1.5rem;
        }

        .no-results h3 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .no-results p {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .suggestions {
            max-width: 400px;
            margin: 2rem auto 0;
            text-align: left;
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
        }

        .suggestions h4 {
            color: #6c5ce7;
            margin-bottom: 1rem;
        }

        .suggestions ul {
            list-style: none;
        }

        .suggestions li {
            padding: 0.5rem 0;
            color: #666;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .suggestions li::before {
            content: '✓';
            color: #00b894;
            font-weight: 600;
        }

        .featured-ethiopian {
            margin-top: 4rem;
        }

        .featured-ethiopian h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .featured-ethiopian > p {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
        }

        .category-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .chip {
            padding: 0.8rem 1.5rem;
            background: white;
            border-radius: 50px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chip:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(108, 92, 231, 0.3);
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
        }

        .chip i {
            font-size: 1.2rem;
        }

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .filter-tag {
            background: rgba(108, 92, 231, 0.1);
            color: #6c5ce7;
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-tag i {
            cursor: pointer;
        }

        .filter-tag i:hover {
            color: #d63031;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 3rem;
        }

        .page-link {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .page-link:hover,
        .page-link.active {
            background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
            color: white;
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .search-hero h1 {
                font-size: 2rem;
            }
            
            .search-main {
                flex-direction: column;
            }
            
            .btn-large {
                width: 100%;
            }
            
            .results-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">Ethiopian E-Book Store</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="search.php" class="active">Search</a></li>
                <?php if (SessionManager::isLoggedIn()): ?>
                    <li><a href="../user/dashboard.php">Dashboard</a></li>
                    <li><a href="../user/cart.php">Cart</a></li>
                    <li><a href="../user/orders.php">My Orders</a></li>
                    <li><a href="../user/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <div class="search-page">
        <div class="search-hero">
            <h1>🔍 Search Ethiopian Books</h1>
            <p>Discover thousands of books in Amharic, Afan Oromo, Tigrinya, English and more</p>
            
            <div class="advanced-search-form">
                <form action="search.php" method="GET" id="searchForm">
                    <div class="search-main">
                        <input type="text" name="q" class="search-input-large" 
                               placeholder="Search by title, author, or keywords..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>" 
                               autocomplete="off"
                               id="searchInput">
                        <button type="submit" class="btn-large">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    
                    <div class="search-filters">
                        <div class="filter-group">
                            <label><i class="fas fa-language"></i> Language</label>
                            <select name="language" id="language">
                                <option value="">All Languages</option>
                                <option value="English" <?php echo $language == 'English' ? 'selected' : ''; ?>>English</option>
                                <option value="Amharic" <?php echo $language == 'Amharic' ? 'selected' : ''; ?>>አማርኛ (Amharic)</option>
                                <option value="Afan Oromo" <?php echo $language == 'Afan Oromo' ? 'selected' : ''; ?>>Afan Oromo</option>
                                <option value="Tigrigna" <?php echo $language == 'Tigrigna' ? 'selected' : ''; ?>>ትግርኛ (Tigrinya)</option>
                                <option value="Somali" <?php echo $language == 'Somali' ? 'selected' : ''; ?>>Somali</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label><i class="fas fa-tags"></i> Category</label>
                            <select name="category" id="category">
                                <option value="0">All Categories</option>
                                <?php 
                                mysqli_data_seek($categoriesResult, 0);
                                while($cat = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo $cat['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label><i class="fas fa-dollar-sign"></i> Price Range</label>
                            <div class="price-range">
                                <input type="number" name="min_price" placeholder="Min" value="<?php echo $minPrice; ?>" min="0" step="1">
                                <span>-</span>
                                <input type="number" name="max_price" placeholder="Max" value="<?php echo $maxPrice; ?>" min="0" step="1">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label><i class="fas fa-sort"></i> Sort By</label>
                            <select name="sort" id="sort">
                                <option value="relevance" <?php echo $sort == 'relevance' ? 'selected' : ''; ?>>Relevance</option>
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="bestseller" <?php echo $sort == 'bestseller' ? 'selected' : ''; ?>>Bestsellers</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="search-results-container">
            <?php if ($searchQuery): ?>
                <!-- Active Filters -->
                <?php if ($language || $category > 0 || $minPrice > 0 || $maxPrice < 200): ?>
                    <div class="active-filters">
                        <span class="filter-tag">
                            <i class="fas fa-search"></i> "<?php echo $searchQuery; ?>"
                        </span>
                        <?php if ($language): ?>
                            <span class="filter-tag">
                                <?php echo $language; ?> 
                                <i class="fas fa-times" onclick="removeFilter('language')"></i>
                            </span>
                        <?php endif; ?>
                        <?php if ($category > 0): 
                            $catName = $categories->fetch_assoc()['name'] ?? 'Category';
                        ?>
                            <span class="filter-tag">
                                <?php echo $catName; ?>
                                <i class="fas fa-times" onclick="removeFilter('category')"></i>
                            </span>
                        <?php endif; ?>
                        <?php if ($minPrice > 0 || $maxPrice < 200): ?>
                            <span class="filter-tag">
                                $<?php echo $minPrice; ?> - $<?php echo $maxPrice; ?>
                                <i class="fas fa-times" onclick="removeFilter('price')"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="results-header">
                    <div>
                        <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>
                        <p><?php echo $totalResults; ?> books found</p>
                    </div>
                    <div class="results-sort">
                        <label>Sort by:</label>
                        <select onchange="window.location.href=this.value">
                            <option value="?q=<?php echo urlencode($searchQuery); ?>&sort=relevance&language=<?php echo $language; ?>&category=<?php echo $category; ?>&min_price=<?php echo $minPrice; ?>&max_price=<?php echo $maxPrice; ?>" <?php echo $sort == 'relevance' ? 'selected' : ''; ?>>Relevance</option>
                            <option value="?q=<?php echo urlencode($searchQuery); ?>&sort=newest&language=<?php echo $language; ?>&category=<?php echo $category; ?>&min_price=<?php echo $minPrice; ?>&max_price=<?php echo $maxPrice; ?>" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="?q=<?php echo urlencode($searchQuery); ?>&sort=price_low&language=<?php echo $language; ?>&category=<?php echo $category; ?>&min_price=<?php echo $minPrice; ?>&max_price=<?php echo $maxPrice; ?>" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="?q=<?php echo urlencode($searchQuery); ?>&sort=price_high&language=<?php echo $language; ?>&category=<?php echo $category; ?>&min_price=<?php echo $minPrice; ?>&max_price=<?php echo $maxPrice; ?>" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="?q=<?php echo urlencode($searchQuery); ?>&sort=bestseller&language=<?php echo $language; ?>&category=<?php echo $category; ?>&min_price=<?php echo $minPrice; ?>&max_price=<?php echo $maxPrice; ?>" <?php echo $sort == 'bestseller' ? 'selected' : ''; ?>>Bestsellers</option>
                        </select>
                    </div>
                </div>
                
                <?php if ($totalResults > 0): ?>
                    <div class="books-grid">
                        <?php while($book = $results->fetch_assoc()): ?>
                            <div class="book-card">
                                <div class="book-cover">
                                    <?php if(isset($book['cover_image']) && $book['cover_image']): ?>
                                        <img src="/ebook-store/assets/uploads/covers/<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>">
                                    <?php else: ?>
                                        <span>📚</span>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($book['country']) && $book['country'] == 'Ethiopia'): ?>
                                        <span class="ethiopian-flag-badge">🇪🇹 Ethiopian</span>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($book['bestseller']) && $book['bestseller']): ?>
                                        <span class="book-badge-small">⭐ Bestseller</span>
                                    <?php endif; ?>
                                    
                                    <?php if(isset($book['award_winning']) && $book['award_winning']): ?>
                                        <span class="award-badge-small">🏆 Award Winner</span>
                                    <?php endif; ?>
                                </div>
                                <div class="book-info">
                                    <h3 class="book-title"><?php echo Functions::truncateText($book['title'], 50); ?></h3>
                                    <p class="book-author">by <?php echo $book['author']; ?></p>
                                    <p class="book-category"><?php echo $book['category_name'] ?? 'Uncategorized'; ?></p>
                                    <p class="book-language"><i class="fas fa-globe"></i> <?php echo $book['language'] ?? 'English'; ?></p>
                                    <div class="book-price">$<?php echo number_format($book['price'], 2); ?></div>
                                    <a href="book-details.php?id=<?php echo $book['id']; ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination">
                        <a href="#" class="page-link active">1</a>
                        <a href="#" class="page-link">2</a>
                        <a href="#" class="page-link">3</a>
                        <a href="#" class="page-link">4</a>
                        <a href="#" class="page-link">5</a>
                    </div>
                    
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-book-open"></i>
                        <h3>No books found</h3>
                        <p>We couldn't find any books matching "<?php echo htmlspecialchars($searchQuery); ?>"</p>
                        
                        <div class="suggestions">
                            <h4>💡 Suggestions:</h4>
                            <ul>
                                <li>Check your spelling</li>
                                <li>Try more general keywords</li>
                                <li>Try different language</li>
                                <li>Browse by category below</li>
                                <li>Adjust your price range</li>
                            </ul>
                        </div>
                        
                        <div class="category-chips" style="margin-top: 2rem;">
                            <a href="categories.php?id=2" class="chip"><i class="fas fa-book"></i> Amharic Fiction</a>
                            <a href="categories.php?id=3" class="chip"><i class="fas fa-landmark"></i> Ethiopian History</a>
                            <a href="categories.php?id=4" class="chip"><i class="fas fa-music"></i> Ethiopian Culture</a>
                            <a href="categories.php?id=11" class="chip"><i class="fas fa-robot"></i> AI Engineering</a>
                            <a href="categories.php?id=17" class="chip"><i class="fas fa-code"></i> Programming</a>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Featured Ethiopian Books Section (When no search) -->
                <div class="featured-ethiopian">
                    <h2>🇪🇹 Featured Ethiopian Books</h2>
                    <p>Discover the rich literary heritage of Ethiopia</p>
                    
                    <?php if ($featuredEthiopian->num_rows > 0): ?>
                        <div class="books-grid">
                            <?php while($book = $featuredEthiopian->fetch_assoc()): ?>
                                <div class="book-card">
                                    <div class="book-cover">
                                        <?php if(isset($book['cover_image']) && $book['cover_image']): ?>
                                            <img src="/ebook-store/assets/uploads/covers/<?php echo $book['cover_image']; ?>" alt="<?php echo $book['title']; ?>">
                                        <?php else: ?>
                                            <span>📚</span>
                                        <?php endif; ?>
                                        <span class="ethiopian-flag-badge">🇪🇹 Ethiopian</span>
                                        <?php if(isset($book['bestseller']) && $book['bestseller']): ?>
                                            <span class="book-badge-small">⭐ Bestseller</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="book-info">
                                        <h3 class="book-title"><?php echo Functions::truncateText($book['title'], 40); ?></h3>
                                        <p class="book-author">by <?php echo $book['author']; ?></p>
                                        <p class="book-category"><?php echo $book['category_name'] ?? 'Uncategorized'; ?></p>
                                        <div class="book-price">$<?php echo number_format($book['price'], 2); ?></div>
                                        <a href="book-details.php?id=<?php echo $book['id']; ?>" class="btn btn-primary">View Details</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Popular Categories -->
                    <h2 style="margin-top: 4rem;">📚 Popular Categories</h2>
                    <div class="category-chips">
                        <?php while($cat = $popularCategories->fetch_assoc()): ?>
                            <a href="categories.php?name=<?php echo urlencode($cat['name']); ?>" class="chip">
                                <i class="fas <?php 
                                    echo $cat['icon'] ?? match($cat['name']) {
                                        'Programming' => 'fa-code',
                                        'AI Engineering' => 'fa-robot',
                                        'Amharic Fiction' => 'fa-book',
                                        'Ethiopian History' => 'fa-landmark',
                                        'Ethiopian Culture' => 'fa-music',
                                        'Biography' => 'fa-user',
                                        'Children Books' => 'fa-child',
                                        default => 'fa-book-open'
                                    };
                                ?>"></i>
                                <?php echo $cat['name']; ?> 
                                <span style="background: rgba(255,255,255,0.3); padding: 0.2rem 0.5rem; border-radius: 20px; font-size: 0.8rem;">
                                    <?php echo $cat['book_count']; ?>
                                </span>
                            </a>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Browse All Link -->
                    <div style="text-align: center; margin-top: 3rem;">
                        <a href="categories.php" class="btn btn-primary btn-large">
                            <i class="fas fa-compass"></i> Browse All Categories
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Ethiopian E-Book Store</h3>
                <p>Ethiopia's premier digital reading destination. Discover thousands of Ethiopian and international e-books in multiple languages.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul style="list-style: none;">
                    <li><a href="about.php" style="color: #ccc; text-decoration: none;">About Us</a></li>
                    <li><a href="contact.php" style="color: #ccc; text-decoration: none;">Contact</a></li>
                    <li><a href="privacy.php" style="color: #ccc; text-decoration: none;">Privacy Policy</a></li>
                    <li><a href="terms.php" style="color: #ccc; text-decoration: none;">Terms of Service</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div style="display: flex; gap: 1rem;">
                    <a href="#" style="color: #ccc; text-decoration: none; font-size: 1.5rem;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: #ccc; text-decoration: none; font-size: 1.5rem;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color: #ccc; text-decoration: none; font-size: 1.5rem;"><i class="fab fa-instagram"></i></a>
                    <a href="#" style="color: #ccc; text-decoration: none; font-size: 1.5rem;"><i class="fab fa-telegram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Ethiopian E-Book Store. All rights reserved. 🇪🇹</p>
        </div>
    </footer>
    
    <script>
        // Remove filter function
        function removeFilter(filter) {
            const url = new URL(window.location.href);
            
            if (filter === 'language') {
                url.searchParams.delete('language');
            } else if (filter === 'category') {
                url.searchParams.delete('category');
            } else if (filter === 'price') {
                url.searchParams.delete('min_price');
                url.searchParams.delete('max_price');
            }
            
            window.location.href = url.toString();
        }
        
        // Live search suggestions (optional)
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                // You can add AJAX search suggestions here
            });
        }
        
        // Auto-submit on filter change
        document.querySelectorAll('#language, #category, #sort').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('searchForm').submit();
            });
        });
        
        // Price range debounce
        let timer;
        document.querySelectorAll('input[name="min_price"], input[name="max_price"]').forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    document.getElementById('searchForm').submit();
                }, 1000);
            });
        });
    </script>
</body>
</html>