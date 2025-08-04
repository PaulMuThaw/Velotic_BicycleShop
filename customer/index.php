<?php
require_once 'db_conn.php';

// Function to get product image from uploads directory
function getProductImage($imagePath) {
    $filename = basename($imagePath);
    $uploadsDir = '../uploads/';  // Updated to match your structure
    $defaultImage = '../uploads/default-product.jpg';
    
    if (file_exists($uploadsDir . $filename)) {
        return $uploadsDir . $filename;
    }
    
    return $defaultImage;
}

try {
    // Fetch featured products (ONLY In Stock status, excluding New)
    $featuredProducts = $conn->query("
        SELECT * FROM products 
        WHERE status = 'In Stock'
        ORDER BY RAND()
        LIMIT 4
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch new arrivals (ONLY New status)
    $newArrivals = $conn->query("
        SELECT * FROM products 
        WHERE status = 'New'
        ORDER BY created_at DESC
        LIMIT 4
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch approved feedback for testimonials
    $approvedFeedback = $conn->query("
        SELECT f.*, p.name as product_name 
        FROM feedback f
        LEFT JOIN products p ON f.product_id = p.id
        WHERE f.status = 'approved'
        ORDER BY f.created_at DESC
        LIMIT 3
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $featuredProducts = $newArrivals = $approvedFeedback = [];
    $dbError = "We're experiencing technical difficulties. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velociti Bicycle Shop</title>
     <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="font-sans bg-gray-50">
    <?php require_once "navbar.php";?>

    <!-- Hero Section -->
    <section class="hero relative bg-gray-900 text-white py-24 md:py-36 overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full z-0">
            <video autoplay muted loop playsinline class="w-full h-full object-cover">
                <source src="../uploads/bicycle.mp4" type="video/mp4">
                <!-- Fallback image if video can't be loaded -->
                <img src="<?= getProductImage('default-hero.jpg') ?>" alt="Bicycle background" class="w-full h-full object-cover">
            </video>
            <div class="absolute inset-0 bg-black opacity-50"></div>
        </div>
        <div class="container mx-auto px-4 text-center relative z-10">
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

    <!-- Featured Products -->
    <section id="featured" class="py-20">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-16">Featured Bikes</h2>
            
            <?php if (!empty($dbError)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-8 max-w-2xl mx-auto">
                    <?= htmlspecialchars($dbError) ?>
                </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($featuredProducts as $product): ?>
                <div class="bg-white rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <a href="Item.php?id=<?= $product['id'] ?>" class="block h-full">
                        <div class="relative pb-[75%] overflow-hidden">
                            <img src="<?= getProductImage($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="absolute w-full h-full object-cover transition duration-500 hover:scale-105"
                                 onerror="this.src='<?= getProductImage('default-product.jpg') ?>'">
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold">$<?= number_format($product['price'] ?? 0, 2) ?></span>
                                <span class="text-xs px-3 py-1 rounded-full bg-green-100 text-green-800">
                                    <?= $product['status'] ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-12">
                <a href="products.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-lg transition duration-300">
                    View All Bikes
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold">New Arrivals</h2>
                <a href="products.php?status=New" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                    View All New Arrivals <i class="fas fa-chevron-right ml-1"></i>
                </a>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($newArrivals as $product): ?>
                <div class="bg-white rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <a href="Item.php?id=<?= $product['id'] ?>" class="block h-full">
                        <div class="relative pb-[75%] overflow-hidden">
                            <img src="<?= getProductImage($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="absolute w-full h-full object-cover transition duration-500 hover:scale-105"
                                 onerror="this.src='<?= getProductImage('default-product.jpg') ?>'">
                            <div class="absolute top-3 left-3 bg-blue-600 text-white text-xs px-2 py-1 rounded-full uppercase font-bold tracking-wide shadow">New Arrival</div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold">$<?= number_format($product['price'] ?? 0, 2) ?></span>
                                <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full">New</span>
                            </div>
                            <div class="mt-2 text-xs text-blue-600 font-medium">Just Added to Our Collection!</div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">What Our Customers Say</h2>
            
            <?php if (empty($approvedFeedback)): ?>
                <div class="text-center text-gray-500 py-8">
                    <p>No customer feedback yet. Be the first to share your experience!</p>
                    <a href="feedback.php" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                        Leave Feedback <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php foreach ($approvedFeedback as $feedback): ?>
                    <div class="bg-white p-8 rounded-xl shadow-md">
                        <div class="flex items-center mb-4">
                            <div class="flex text-yellow-400 mr-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star<?= $i <= $feedback['rating'] ? '' : '-half-alt' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <?php if (!empty($feedback['product_name'])): ?>
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                    <?= htmlspecialchars($feedback['product_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-gray-600 mb-6 italic">"<?= htmlspecialchars($feedback['message']) ?>"</p>
                        <div class="flex items-center">
                            <div class="bg-gray-200 rounded-full w-12 h-12 flex items-center justify-center mr-4">
                                <?php 
                                $initials = '';
                                $nameParts = explode(' ', $feedback['name']);
                                foreach ($nameParts as $part) {
                                    $initials .= strtoupper(substr($part, 0, 1));
                                    if (strlen($initials) >= 2) break;
                                }
                                echo $initials;
                                ?>
                            </div>
                            <div>
                                <h4 class="font-semibold"><?= htmlspecialchars($feedback['name']) ?></h4>
                                <p class="text-gray-500 text-sm">
                                    <?= date('F j, Y', strtotime($feedback['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-8">
                    <a href="feedback.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                        Share Your Experience <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php require_once "footer.php"; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.md\\:hidden');
            // Mobile menu functionality can be added here
        });
    </script>
</body>
</html>