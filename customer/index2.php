<?php
require_once 'db_conn.php';

// Function to get product image
function getProductImage($imagePath) {
    $filename = basename($imagePath);
    $uploadsDir = 'customer/uploads/';
    $defaultImage = 'customer/uploads/default-product.jpg';
    
    if (file_exists($uploadsDir . $filename)) {
        return $uploadsDir . $filename;
    }
    return $defaultImage;
}

// Get featured products with average ratings
try {
    $featuredProducts = $conn->query("
        SELECT p.*, 
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(r.id) as review_count
        FROM products p
        LEFT JOIN ratings r ON p.id = r.product_id
        WHERE p.status = 'In Stock'
        GROUP BY p.id
        ORDER BY RAND()
        LIMIT 4
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Get new arrivals
    $newArrivals = $conn->query("
        SELECT p.*, 
               COALESCE(AVG(r.rating), 0) as avg_rating
        FROM products p
        LEFT JOIN ratings r ON p.id = r.product_id
        WHERE p.status = 'In Stock'
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT 4
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Get special offers
    $specialOffers = $conn->query("
        SELECT * FROM products 
        WHERE status = 'In Stock' AND discount > 0
        ORDER BY RAND()
        LIMIT 2
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $featuredProducts = $newArrivals = $specialOffers = [];
    $dbError = "We're experiencing technical difficulties. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bicycle Shop - Premium Bikes & Accessories</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero {
            background-image: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('images/bike-hero.jpg');
            background-size: cover;
            background-position: center;
        }
        .product-card {
            transition: all 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: bold;
        }
    </style>
</head>
<body class="font-sans bg-gray-50">
    <?php require_once "navbar.php"; ?>

    <!-- Hero Section with Search -->
    <section class="hero bg-gray-900 text-white py-24 md:py-36">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">Premium Bikes for Every Rider</h1>
            <p class="text-xl lg:text-2xl mb-10 max-w-3xl mx-auto">Discover our collection of high-performance bicycles and accessories</p>
            
            <!-- Search Bar -->
            <div class="max-w-2xl mx-auto relative">
                <input type="text" placeholder="Search bikes, brands, categories..." 
                       class="w-full px-6 py-4 rounded-full text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button class="absolute right-2 top-2 bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <div class="mt-8">
                <a href="products.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-10 py-4 rounded-lg text-lg font-medium transition duration-300 mr-4">
                    Shop Now
                </a>
                <a href="#featured" class="inline-block bg-transparent border-2 border-white text-white px-10 py-4 rounded-lg text-lg font-medium hover:bg-white hover:text-gray-900 transition duration-300">
                    Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl font-bold text-center mb-10">Shop By Category</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <a href="products.php?category=road" class="category-card bg-gray-100 rounded-lg p-6 text-center hover:bg-gray-200 transition">
                    <div class="text-4xl mb-3 text-blue-600">
                        <i class="fas fa-road"></i>
                    </div>
                    <h3 class="font-semibold">Road Bikes</h3>
                </a>
                <a href="products.php?category=mountain" class="category-card bg-gray-100 rounded-lg p-6 text-center hover:bg-gray-200 transition">
                    <div class="text-4xl mb-3 text-green-600">
                        <i class="fas fa-mountain"></i>
                    </div>
                    <h3 class="font-semibold">Mountain Bikes</h3>
                </a>
                <a href="products.php?category=hybrid" class="category-card bg-gray-100 rounded-lg p-6 text-center hover:bg-gray-200 transition">
                    <div class="text-4xl mb-3 text-yellow-600">
                        <i class="fas fa-bicycle"></i>
                    </div>
                    <h3 class="font-semibold">Hybrid Bikes</h3>
                </a>
                <a href="products.php?category=accessories" class="category-card bg-gray-100 rounded-lg p-6 text-center hover:bg-gray-200 transition">
                    <div class="text-4xl mb-3 text-red-600">
                        <i class="fas fa-helmet-safety"></i>
                    </div>
                    <h3 class="font-semibold">Accessories</h3>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section id="featured" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold">Featured Bikes</h2>
                <a href="products.php" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                    View All <i class="fas fa-chevron-right ml-1"></i>
                </a>
            </div>
            
            <?php if (!empty($dbError)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-8 max-w-2xl mx-auto">
                    <?= htmlspecialchars($dbError) ?>
                </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300">
                    <?php if ($product['discount'] > 0): ?>
                        <div class="discount-badge">-<?= $product['discount'] ?>%</div>
                    <?php endif; ?>
                    <a href="product.php?id=<?= htmlspecialchars($product['id']) ?>" class="block h-full">
                        <div class="relative pb-[75%] overflow-hidden">
                            <img src="<?= getProductImage($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="absolute w-full h-full object-cover transition duration-500 hover:scale-105"
                                 onerror="this.src='<?= getProductImage('default-product.jpg') ?>'">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                            
                            <!-- Rating Stars -->
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400 mr-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= round($product['avg_rating']) ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-gray-600 text-sm">(<?= $product['review_count'] ?>)</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div>
                                    <?php if ($product['discount'] > 0): ?>
                                        <span class="text-gray-400 line-through mr-2">$<?= number_format($product['price'], 2) ?></span>
                                        <span class="text-2xl font-bold text-red-600">$<?= number_format($product['price'] * (1 - $product['discount']/100), 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-2xl font-bold">$<?= number_format($product['price'], 2) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full">In Stock</span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Special Offers Banner -->
    <section class="py-12 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Summer Sale!</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Up to 30% off selected bikes and accessories. Limited time offer.</p>
            <a href="products.php?discount=1" class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg text-lg font-medium hover:bg-gray-100 transition">
                Shop Special Offers
            </a>
        </div>
    </section>

    <!-- New Arrivals -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold">New Arrivals</h2>
                <a href="products.php?sort=newest" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                    View All <i class="fas fa-chevron-right ml-1"></i>
                </a>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($newArrivals as $product): ?>
                <div class="product-card bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300">
                    <a href="product.php?id=<?= htmlspecialchars($product['id']) ?>" class="block h-full">
                        <div class="relative pb-[75%] overflow-hidden">
                            <img src="<?= getProductImage($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="absolute w-full h-full object-cover transition duration-500 hover:scale-105"
                                 onerror="this.src='<?= getProductImage('default-product.jpg') ?>'">
                            <div class="absolute top-3 left-3 bg-blue-600 text-white text-xs px-2 py-1 rounded-full">New</div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="flex items-center mb-3">
                                <div class="flex text-yellow-400 mr-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= round($product['avg_rating']) ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold">$<?= number_format($product['price'], 2) ?></span>
                                <span class="bg-green-100 text-green-800 text-xs px-3 py-1 rounded-full">In Stock</span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">What Our Customers Say</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-md">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400 mr-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">"The bike I purchased exceeded my expectations. Smooth ride and excellent customer service!"</p>
                    <div class="flex items-center">
                        <div class="bg-gray-200 rounded-full w-12 h-12 flex items-center justify-center mr-4">
                            <i class="fas fa-user text-gray-500"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold">John D.</h4>
                            <p class="text-gray-500 text-sm">Verified Buyer</p>
                        </div>
                    </div>
                </div>
                
                <!-- Repeat for 2 more testimonials -->
            </div>
        </div>
    </section>

    <!-- Newsletter Subscription -->
    <section class="py-16 bg-blue-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Stay Updated</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Subscribe to our newsletter for the latest products, offers, and cycling tips.</p>
            
            <form class="max-w-md mx-auto flex">
                <input type="email" placeholder="Your email address" 
                       class="flex-grow px-4 py-3 rounded-l-lg focus:outline-none text-gray-900">
                <button type="submit" class="bg-blue-800 hover:bg-blue-900 px-6 py-3 rounded-r-lg font-medium transition">
                    Subscribe
                </button>
            </form>
        </div>
    </section>

    <?php require_once "footer.php"; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.querySelector('.mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
            
            // Simple image lazy loading
            const lazyImages = document.querySelectorAll('img[data-src]');
            
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                lazyImages.forEach((img) => {
                    imageObserver.observe(img);
                });
            }
        });
    </script>
</body>
</html>