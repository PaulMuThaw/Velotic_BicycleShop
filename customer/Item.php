<?php
require_once 'db_conn.php';
session_start();

// Get product ID from URL
$productId = $_GET['id'] ?? 0;

try {
    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: view_allProduct.php");
        exit();
    }

    // Get related products (same category, excluding current product)
    $relatedStmt = $conn->prepare("
        SELECT * FROM products 
        WHERE category_id = ? AND id != ?
        ORDER BY RAND() 
        LIMIT 4
    ");
    $relatedStmt->execute([$product['category_id'], $productId]);
    $relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: view_allProduct.php");
    exit();
}

// Function to get product image
function getProductImage($imagePath) {
    $filename = basename($imagePath);
    $uploadsDir = '../uploads/';
    $defaultImage = '../uploads/default-product.jpg';
    
    if (file_exists($uploadsDir . $filename)) {
        return $uploadsDir . $filename;
    }
    
    return $defaultImage;
}

// Handle add to cart form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'] ?? 1;
    $size = $_POST['size'] ?? 'M';
    
    // Check if product already exists in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productId && $item['size'] == $size) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'size' => $size,
            'image' => $product['image']
        ];
    }
    
    // Redirect to cart page or stay on current page with success message
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | Velociti Bicycle Shop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .product-gallery img {
            transition: all 0.3s ease;
        }
        .product-gallery img:hover {
            transform: scale(1.03);
        }
        .active-image {
            border: 2px solid #3b82f6;
        }
    </style>
</head>
<body class="font-sans bg-gray-50">
    <?php require_once "navbar.php"; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumbs -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="index.php" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home mr-2"></i>
                        Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <a href="view_allProduct.php" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Products</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2"><?= htmlspecialchars($product['name']) ?></span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Product Section -->
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Product Images -->
            <div class="lg:w-1/2">
                <div class="mb-4 rounded-lg overflow-hidden bg-white p-4 shadow-md">
                    <img id="mainImage" src="<?= getProductImage($product['image']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="w-full h-auto max-h-96 object-contain">
                </div>
                
                <?php 
                // Handle multiple product images
                $productImages = explode(',', $product['image']);
                if (count($productImages) > 1): ?>
                <div class="grid grid-cols-4 gap-2 product-gallery">
                    <?php foreach ($productImages as $index => $image): ?>
                    <div class="cursor-pointer">
                        <img src="<?= getProductImage(trim($image)) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?> - thumbnail <?= $index + 1 ?>"
                             class="w-full h-20 object-cover rounded border <?= $index === 0 ? 'active-image' : 'border-gray-200' ?>"
                             onclick="document.getElementById('mainImage').src = this.src; 
                                      document.querySelectorAll('.product-gallery img').forEach(img => img.classList.remove('active-image'));
                                      this.classList.add('active-image');">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="lg:w-1/2">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h1>
                            <div class="flex items-center mb-4">
                                <div class="flex text-yellow-400 mr-2">
                                    <?php
                                    $rating = $product['rating'] ?? 4.5;
                                    $fullStars = floor($rating);
                                    $hasHalfStar = $rating - $fullStars >= 0.5;
                                    
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $fullStars) {
                                            echo '<i class="fas fa-star"></i>';
                                        } elseif ($i == $fullStars + 1 && $hasHalfStar) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <span class="text-sm text-gray-600"><?= $product['review_count'] ?? '24' ?> Reviews</span>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full 
                            <?= $product['status'] == 'In Stock' ? 'bg-green-100 text-green-800' : 
                               ($product['status'] == 'Out of Stock' ? 'bg-red-100 text-red-800' : 
                               'bg-blue-100 text-blue-800') ?>">
                            <?= $product['status'] ?>
                        </span>
                    </div>

                    <div class="mb-6">
                        <span class="text-3xl font-bold text-gray-900">$<?= number_format($product['price'], 2) ?></span>
                        <?php if ($product['discount'] > 0): ?>
                            <span class="ml-2 text-sm text-gray-500 line-through">$<?= number_format($product['price'] + $product['discount'], 2) ?></span>
                            <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded">Save $<?= number_format($product['discount'], 2) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Description</h3>
                        <p class="text-gray-700"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Bike Type</h4>
                            <p class="text-gray-900"><?= ucfirst($product['bike_type']) ?></p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Frame Material</h4>
                            <p class="text-gray-900"><?= ucfirst($product['frame_material'] ?? 'Aluminum') ?></p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Wheel Size</h4>
                            <p class="text-gray-900"><?= $product['wheel_size'] ?? '26"' ?></p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Gears</h4>
                            <p class="text-gray-900"><?= $product['gears'] ?? '21-speed' ?></p>
                        </div>
                    </div>

                    <form method="POST" action="Item.php?id=<?= $productId ?>">
                        <input type="hidden" name="product_id" value="<?= $productId ?>">
                        <div class="flex items-center mb-6">
                            <div class="mr-4">
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                <div class="flex border rounded-md">
                                    <button type="button" class="px-3 py-1 bg-gray-100 text-gray-600" onclick="if(parseInt(this.parentNode.querySelector('input').value) > 1) this.parentNode.querySelector('input').value--;">
                                        -
                                    </button>
                                    <input type="number" id="quantity" name="quantity" min="1" value="1" 
                                           class="w-12 text-center border-0 focus:ring-0">
                                    <button type="button" class="px-3 py-1 bg-gray-100 text-gray-600" onclick="this.parentNode.querySelector('input').value++;">
                                        +
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label for="size" class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                                <select id="size" name="size" class="border rounded-md px-3 py-1">
                                    <option value="S">S</option>
                                    <option value="M" selected>M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="submit" name="add_to_cart"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-medium transition duration-300
                                <?= ($product['status'] == 'Out of Stock') ? 'opacity-50 cursor-not-allowed' : '' ?>"
                                <?= ($product['status'] == 'Out of Stock') ? 'disabled' : '' ?>>
                                <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                            </button>
                            <button type="button" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-medium transition duration-300">
                                <i class="far fa-heart mr-2"></i> Wishlist
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Product Tabs -->
        <div class="mt-12 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button class="tab-button active py-4 px-6 text-center border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                        Specifications
                    </button>
                    <button class="tab-button py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Reviews
                    </button>
                    <button class="tab-button py-4 px-6 text-center border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Shipping & Returns
                    </button>
                </nav>
            </div>
            <div class="p-6">
                <div class="tab-content active">
                    <h3 class="text-lg font-semibold mb-4">Technical Specifications</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border-b pb-2">
                            <span class="text-gray-600">Brand</span>
                            <span class="float-right font-medium"><?= htmlspecialchars($product['brand'] ?? 'Velociti') ?></span>
                        </div>
                        <div class="border-b pb-2">
                            <span class="text-gray-600">Model</span>
                            <span class="float-right font-medium"><?= htmlspecialchars($product['model'] ?? $product['name']) ?></span>
                        </div>
                        <div class="border-b pb-2">
                            <span class="text-gray-600">Weight</span>
                            <span class="float-right font-medium"><?= $product['weight'] ?? '12.5' ?> kg</span>
                        </div>
                        <div class="border-b pb-2">
                            <span class="text-gray-600">Color</span>
                            <span class="float-right font-medium"><?= ucfirst($product['color'] ?? 'Black') ?></span>
                        </div>
                        <div class="border-b pb-2">
                            <span class="text-gray-600">Frame</span>
                            <span class="float-right font-medium"><?= ucfirst($product['frame_material'] ?? 'Aluminum') ?></span>
                        </div>
                        <div class="border-b pb-2">
                            <span class="text-gray-600">Brakes</span>
                            <span class="float-right font-medium"><?= $product['brakes'] ?? 'Disc brakes' ?></span>
                        </div>
                    </div>
                </div>
                <div class="tab-content hidden">
                    <h3 class="text-lg font-semibold mb-4">Customer Reviews</h3>
                    <div class="space-y-6">
                        <!-- Sample review - you would fetch these from your database -->
                        <div class="border-b pb-4">
                            <div class="flex items-center mb-2">
                                <div class="flex text-yellow-400 mr-2">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="font-medium">John D.</span>
                                <span class="text-gray-500 text-sm ml-auto">2 weeks ago</span>
                            </div>
                            <p class="text-gray-700">"This bike is amazing! The ride is smooth and it's very comfortable even on long distances."</p>
                        </div>
                        <!-- Add more reviews here -->
                    </div>
                </div>
                <div class="tab-content hidden">
                    <h3 class="text-lg font-semibold mb-4">Shipping & Returns</h3>
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium mb-1">Shipping Information</h4>
                            <p class="text-gray-700">Standard shipping: $5.00 (3-5 business days)</p>
                            <p class="text-gray-700">Express shipping: $15.00 (1-2 business days)</p>
                        </div>
                        <div>
                            <h4 class="font-medium mb-1">Returns Policy</h4>
                            <p class="text-gray-700">30-day return policy. Items must be in original condition with all tags attached.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="mt-16">
            <h2 class="text-3xl font-bold mb-8">You May Also Like</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($relatedProducts as $related): ?>
                <div class="bg-white rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <a href="Item.php?id=<?= $related['id'] ?>" class="block h-full">
                        <div class="relative pb-[75%] overflow-hidden">
                            <img src="<?= getProductImage($related['image']) ?>" 
                                 alt="<?= htmlspecialchars($related['name']) ?>" 
                                 class="absolute w-full h-full object-cover transition duration-500 hover:scale-105">
                            <?php if ($related['status'] == 'New'): ?>
                                <span class="absolute top-3 left-3 bg-blue-600 text-white text-xs px-2 py-1 rounded-full uppercase font-bold tracking-wide shadow">
                                    New Arrival
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2"><?= htmlspecialchars($related['name']) ?></h3>
                            <p class="text-gray-600 text-sm mb-2"><?= ucfirst($related['bike_type']) ?> Bike</p>
                            <div class="flex justify-between items-center">
                                <span class="text-2xl font-bold">$<?= number_format($related['price'], 2) ?></span>
                                <span class="text-xs px-3 py-1 rounded-full 
                                    <?= $related['status'] == 'In Stock' ? 'bg-green-100 text-green-800' : 
                                       ($related['status'] == 'Out of Stock' ? 'bg-red-100 text-red-800' : 
                                       'bg-blue-100 text-blue-800') ?>">
                                    <?= $related['status'] ?>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php require_once "footer.php"; ?>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and content
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active', 'border-blue-500', 'text-blue-600'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
                
                // Add active class to clicked button
                button.classList.add('active', 'border-blue-500', 'text-blue-600');
                
                // Show corresponding content
                const tabIndex = Array.from(document.querySelectorAll('.tab-button')).indexOf(button);
                document.querySelectorAll('.tab-content')[tabIndex].classList.remove('hidden');
            });
        });

        // Quantity buttons functionality
        document.querySelectorAll('[onclick*="step"]').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('input');
                const event = new Event('change');
                input.dispatchEvent(event);
            });
        });
    </script>
</body>
</html>