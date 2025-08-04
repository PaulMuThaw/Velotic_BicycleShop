<?php
require_once 'db_conn.php';
session_start();

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'] ?? 1;
    $size = $_POST['size'] ?? 'M';
    
    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        $cartItem = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'size' => $size,
            'image' => $product['image']
        ];
        
        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $productId && $item['size'] == $size) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $_SESSION['cart'][] = $cartItem;
        }
    }
}

// Remove item from cart
if (isset($_GET['remove'])) {
    $index = $_GET['remove'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
    }
}

// Update quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $index => $quantity) {
        if (isset($_SESSION['cart'][$index])) {
            $_SESSION['cart'][$index]['quantity'] = max(1, (int)$quantity);
        }
    }
}

// Calculate totals
$subtotal = 0;
$totalItems = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $totalItems += $item['quantity'];
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | Velociti Bicycle Shop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Shopping Cart</span>
                    </div>
                </li>
            </ol>
        </nav>

        <h1 class="text-3xl font-bold mb-8">Your Shopping Cart</h1>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-shopping-cart text-5xl text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-medium mb-2">Your cart is empty</h2>
                <p class="text-gray-600 mb-6">Looks like you haven't added any items to your cart yet.</p>
                <a href="view_allProduct.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                    Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items -->
                <div class="lg:w-2/3">
                    <form method="POST" action="cart.php">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <!-- Cart Header -->
                            <div class="hidden md:grid grid-cols-12 bg-gray-100 p-4 font-medium text-gray-700">
                                <div class="col-span-5">Product</div>
                                <div class="col-span-2 text-center">Price</div>
                                <div class="col-span-3 text-center">Quantity</div>
                                <div class="col-span-2 text-center">Total</div>
                            </div>
                            
                            <!-- Cart Items -->
                            <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex flex-col md:flex-row md:items-center gap-4">
                                    <!-- Product Image and Info -->
                                    <div class="flex items-center md:w-5/12">
                                        <a href="Item.php?id=<?= $item['id'] ?>" class="w-24 h-24 flex-shrink-0 overflow-hidden rounded-lg">
                                            <img src="<?= getProductImage($item['image']) ?>" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                                 class="w-full h-full object-cover">
                                        </a>
                                        <div class="ml-4">
                                            <a href="Item.php?id=<?= $item['id'] ?>" class="font-medium hover:text-blue-600">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                            <p class="text-sm text-gray-500">Size: <?= htmlspecialchars($item['size']) ?></p>
                                            <a href="cart.php?remove=<?= $index ?>" class="text-sm text-red-600 hover:text-red-800 mt-1 inline-block">
                                                <i class="fas fa-trash-alt mr-1"></i> Remove
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Price -->
                                    <div class="md:w-2/12 text-center">
                                        <span class="md:hidden text-gray-600 mr-2">Price:</span>
                                        <span class="font-medium">$<?= number_format($item['price'], 2) ?></span>
                                    </div>
                                    
                                    <!-- Quantity -->
                                    <div class="md:w-3/12">
                                        <div class="flex items-center justify-center">
                                            <button type="button" onclick="this.parentNode.querySelector('input').stepDown()" 
                                                    class="bg-gray-200 text-gray-600 px-3 py-1 rounded-l">
                                                -
                                            </button>
                                            <input type="number" name="quantity[<?= $index ?>]" 
                                                   value="<?= $item['quantity'] ?>" min="1" 
                                                   class="w-12 text-center border-t border-b border-gray-300 py-1">
                                            <button type="button" onclick="this.parentNode.querySelector('input').stepUp()" 
                                                    class="bg-gray-200 text-gray-600 px-3 py-1 rounded-r">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Total -->
                                    <div class="md:w-2/12 text-center">
                                        <span class="md:hidden text-gray-600 mr-2">Total:</span>
                                        <span class="font-medium">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <!-- Cart Footer -->
                            <div class="p-4 bg-gray-50 flex justify-between">
                                <a href="view_allProduct.php" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                    <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                                </a>
                                <button type="submit" name="update_cart" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg font-medium">
                                    Update Cart
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                        <h2 class="text-xl font-bold mb-6 border-b pb-2">Order Summary</h2>
                        
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal (<?= $totalItems ?> items)</span>
                                <span class="font-medium">$<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-medium">$5.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax</span>
                                <span class="font-medium">$<?= number_format($subtotal * 0.1, 2) ?></span>
                            </div>
                            <div class="flex justify-between border-t pt-4">
                                <span class="text-lg font-bold">Total</span>
                                <span class="text-lg font-bold">$<?= number_format($subtotal + 5 + ($subtotal * 0.1), 2) ?></span>
                            </div>
                        </div>
                        
                        <a href="checkout.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 px-4 rounded-lg font-medium transition duration-300">
                            Proceed to Checkout
                        </a>
                        
                        <div class="mt-4 text-sm text-gray-500">
                            <p><i class="fas fa-lock mr-2"></i> Secure checkout</p>
                            <p class="mt-2"><i class="fas fa-shield-alt mr-2"></i> 100% Satisfaction Guarantee</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once "footer.php"; ?>
</body>
</html>