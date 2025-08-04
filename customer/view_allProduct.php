<?php
require_once 'db_conn.php';

// Get all available categories with names for filter and sidebar
$categories = $conn->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Get all available bike types for filter
$bikeTypes = $conn->query("SELECT DISTINCT bike_type FROM products WHERE bike_type IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

// Initialize filter variables
$searchTerm = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Build base query
$query = "SELECT * FROM products WHERE 1=1";
$params = [];

// Add search filter
if (!empty($searchTerm)) {
    $query .= " AND (name LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$searchTerm%";
}

// Add category filter
if (!empty($categoryFilter)) {
    $query .= " AND category_id = :category";
    $params[':category'] = $categoryFilter;
}

// Add type filter
if (!empty($typeFilter)) {
    $query .= " AND bike_type = :type";
    $params[':type'] = $typeFilter;
}

// Add status filter
if (!empty($statusFilter)) {
    $query .= " AND status = :status";
    $params[':status'] = $statusFilter;
}

// Add sorting and pagination
$query .= " ORDER BY created_at DESC";
$page = $_GET['page'] ?? 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Get total count for pagination
$countQuery = str_replace('SELECT *', 'SELECT COUNT(*) as total', $query);
$totalResult = $conn->prepare($countQuery);
$totalResult->execute($params);
$totalProducts = $totalResult->fetchColumn();

// Add pagination to main query
$query .= " LIMIT $perPage OFFSET $offset";

// Execute main query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get product image from uploads directory
function getProductImage($imagePath) {
    $filename = basename($imagePath);
    $uploadsDir = '../uploads/';  // Changed to match your structure
    $defaultImage = '../uploads/default-product.jpg';
    
    if (file_exists($uploadsDir . $filename)) {
        return $uploadsDir . $filename;
    }
    
    return $defaultImage;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalog | Velociti Bicycle Shop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .mobile-filter-btn {
                display: block;
            }
        }
        .mobile-filter-btn {
            display: none;
        }
    </style>
</head>
<body class="font-sans bg-gray-50">
    <?php require_once "navbar.php"; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Mobile Filter Button -->
        <button id="mobileFilterBtn" class="mobile-filter-btn bg-blue-600 text-white px-4 py-2 rounded-lg mb-4">
            <i class="fas fa-filter mr-2"></i> Filters
        </button>

        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar -->
            <div id="sidebar" class="sidebar w-full md:w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Categories</h2>
                    <ul class="space-y-2">
                        <li>
                            <a href="products.php" 
                               class="block px-3 py-2 rounded hover:bg-gray-100 <?= empty($categoryFilter) ? 'bg-blue-50 text-blue-600 font-medium' : '' ?>">
                                All Categories
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="?<?= http_build_query(array_merge($_GET, ['category' => $cat['id'], 'page' => 1])) ?>" 
                               class="block px-3 py-2 rounded hover:bg-gray-100 <?= $categoryFilter == $cat['id'] ? 'bg-blue-50 text-blue-600 font-medium' : '' ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <!-- Additional filters -->
                    <h2 class="text-xl font-bold mt-6 mb-4 border-b pb-2">Filter By</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-medium mb-2">Bike Type</h3>
                            <select name="type" onchange="window.location.href=this.value" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="?<?= http_build_query(array_merge($_GET, ['type' => '', 'page' => 1])) ?>">All Types</option>
                                <?php foreach ($bikeTypes as $type): ?>
                                    <option value="?<?= http_build_query(array_merge($_GET, ['type' => $type, 'page' => 1])) ?>" <?= $typeFilter == $type ? 'selected' : '' ?>>
                                        <?= ucfirst($type) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <h3 class="font-medium mb-2">Status</h3>
                            <select name="status" onchange="window.location.href=this.value" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="?<?= http_build_query(data: array_merge($_GET, ['status' => '', 'page' => 1])) ?>">All Statuses</option>
                                <option value="?<?= http_build_query(array_merge($_GET, ['status' => 'In Stock', 'page' => 1])) ?>" <?= $statusFilter == 'In Stock' ? 'selected' : '' ?>>In Stock</option>
                                <option value="?<?= http_build_query(array_merge($_GET, ['status' => 'Out of Stock', 'page' => 1])) ?>" <?= $statusFilter == 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                                <option value="?<?= http_build_query(array_merge($_GET, ['status' => 'New', 'page' => 1])) ?>" <?= $statusFilter == 'New' ? 'selected' : '' ?>>New Arrivals</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="flex-1">
                <h1 class="text-3xl font-bold mb-8">Our Bicycle Collection</h1>
                
                <!-- Search Bar -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <form method="GET" class="flex items-center">
                        <div class="relative flex-1">
                            <input type="text" name="search" placeholder="Search products..." 
                                   value="<?= htmlspecialchars($searchTerm) ?>"
                                   class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        <button type="submit" class="ml-2 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                            Search
                        </button>
                        <?php if (!empty($searchTerm) || !empty($categoryFilter) || !empty($typeFilter) || !empty($statusFilter)): ?>
                        <a href="view_allProduct.php" class="ml-2 bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300">
                            Reset
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Product Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                    <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <a href="Item.php?id=<?= $product['id'] ?>" class="block h-full">
                        <div class="relative pb-[75%] overflow-hidden">
                            <img src="<?= getProductImage($product['image']) ?>" 
                                alt="<?= htmlspecialchars($product['name']) ?>" 
                                class="absolute w-full h-full object-cover transition duration-500 hover:scale-105"
                                onerror="this.src='<?= getProductImage('default-product.jpg') ?>'">
                            <?php if ($product['status'] == 'New'): ?>
                                <span class="absolute top-3 left-3 bg-blue-600 text-white text-xs px-2 py-1 rounded-full uppercase font-bold tracking-wide shadow">
                                    New Arrival
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="text-gray-600 text-sm mb-2"><?= ucfirst($product['bike_type']) ?> Bike</p>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold">$<?= number_format($product['price'], 2) ?></span>
                                <span class="text-xs px-3 py-1 rounded-full 
                                    <?= $product['status'] == 'In Stock' ? 'bg-green-100 text-green-800' : 
                                    ($product['status'] == 'Out of Stock' ? 'bg-red-100 text-red-800' : 
                                    'bg-blue-100 text-blue-800') ?>">
                                    <?= $product['status'] ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="text-center py-12 bg-white rounded-lg shadow-md">
                        <i class="fas fa-bicycle text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-medium mb-2">No products found</h3>
                        <p class="text-gray-600 mb-4">Try adjusting your search or filter criteria</p>
                        <a href="products.php" class="text-blue-600 hover:text-blue-800 font-medium">
                            Clear all filters
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($totalProducts > $perPage): ?>
                <div class="flex justify-center mt-8">
                    <nav class="flex items-center space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                               class="px-3 py-1 border rounded-lg hover:bg-gray-100">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php 
                        $totalPages = ceil($totalProducts / $perPage);
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                               class="px-3 py-1 border rounded-lg <?= $i == $page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                               class="px-3 py-1 border rounded-lg hover:bg-gray-100">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require_once "footer.php"; ?>

    <script>
        // Toggle mobile sidebar
        document.getElementById('mobileFilterBtn').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
        });
    </script>
</body>
</html>