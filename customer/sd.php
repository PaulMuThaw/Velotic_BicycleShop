<?php
require_once 'db_conn.php';
session_start();

// Redirect to cart if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
$shipping = 5.00;
$totalItems = 0;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $totalItems += $item['quantity'];
}

$tax = $subtotal * 0.1;
$total = $subtotal + $shipping + $tax;

// Process order when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Get customer info (assuming user is logged in)
    $customerId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $customerName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest';
    $customerEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';

    // Create order in database
    try {
        $conn->beginTransaction();

        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, customer_email, total_amount, status) 
                               VALUES (?, ?, ?, ?, 'completed')");
        $stmt->execute([$customerId, $customerName, $customerEmail, $total]);
        $orderId = $conn->lastInsertId();

        // Insert order details
        $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) 
                               VALUES (?, ?, ?, ?, ?)");
        
        foreach ($_SESSION['cart'] as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $stmt->execute([
                $orderId,
                $item['id'],
                $item['quantity'],
                $item['price'],
                $subtotal
            ]);
        }

        // Insert payment (simplified)
        $stmt = $conn->prepare("INSERT INTO payments (order_id, amount, payment_method, status) 
                               VALUES (?, ?, 'Credit Card', 'completed')");
        $stmt->execute([$orderId, $total]);

        $conn->commit();

        // Store order ID for receipt
        $_SESSION['order_id'] = $orderId;
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        // Redirect to receipt page
        header('Location: checkout.php?success=1');
        exit();

    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Order processing failed: " . $e->getMessage();
    }
}

// Display receipt if order was successful
$showReceipt = isset($_GET['success']) && isset($_SESSION['order_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $showReceipt ? 'Order Confirmation' : 'Checkout' ?> | Velociti Bicycle Shop</title>
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
                        <a href="cart.php" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Shopping Cart</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Checkout</span>
                    </div>
                </li>
            </ol>
        </nav>

        <?php if ($showReceipt): ?>
            <!-- Receipt Section -->
            <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-green-100 p-6 text-center border-b border-green-200">
                    <i class="fas fa-check-circle text-5xl text-green-600 mb-4"></i>
                    <h1 class="text-2xl font-bold text-green-800 mb-2">Order Confirmed!</h1>
                    <p class="text-green-600">Thank you for your purchase. Your order has been placed successfully.</p>
                    <p class="text-gray-600 mt-2">Order #<?= $_SESSION['order_id'] ?></p>
                </div>

                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Order Summary</h2>
                    
                    <div class="mb-6">
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Order Number</span>
                            <span class="font-medium">#<?= $_SESSION['order_id'] ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Date</span>
                            <span class="font-medium"><?= date('F j, Y') ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b">
                            <span class="text-gray-600">Total Items</span>
                            <span class="font-medium"><?= $totalItems ?></span>
                        </div>
                    </div>

                    <h3 class="text-lg font-medium mb-3">Items Purchased</h3>
                    <div class="space-y-4 mb-6">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden mr-4">
                                    <img src="<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="font-medium"><?= htmlspecialchars($item['name']) ?></p>
                                    <p class="text-sm text-gray-500">Size: <?= htmlspecialchars($item['size']) ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">$<?= number_format($item['price'], 2) ?></p>
                                <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between py-2">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">$<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium">$<?= number_format($shipping, 2) ?></span>
                        </div>
                        <div class="flex justify-between py-2">
                            <span class="text-gray-600">Tax</span>
                            <span class="font-medium">$<?= number_format($tax, 2) ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-t border-gray-200 mt-2">
                            <span class="text-lg font-bold">Total</span>
                            <span class="text-lg font-bold">$<?= number_format($total, 2) ?></span>
                        </div>
                    </div>

                    <div class="mt-8 text-center">
                        <a href="index.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                            Continue Shopping
                        </a>
                        <p class="mt-4 text-gray-600">We'll send you a shipping confirmation email when your order ships.</p>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Checkout Form -->
            <h1 class="text-3xl font-bold mb-8">Checkout</h1>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Shipping & Payment Info -->
                <div class="lg:w-2/3">
                    <form method="POST" action="checkout.php">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                            <div class="bg-gray-100 p-4 font-medium text-gray-700">
                                <h2 class="text-xl">Shipping Information</h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2" for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" class="w-full px-4 py-2 border rounded-lg" required>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2" for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" class="w-full px-4 py-2 border rounded-lg" required>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-gray-700 mb-2" for="address">Address</label>
                                        <input type="text" id="address" name="address" class="w-full px-4 py-2 border rounded-lg" required>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2" for="city">City</label>
                                        <input type="text" id="city" name="city" class="w-full px-4 py-2 border rounded-lg" required>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2" for="zip">ZIP Code</label>
                                        <input type="text" id="zip" name="zip" class="w-full px-4 py-2 border rounded-lg" required>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-gray-700 mb-2" for="phone">Phone</label>
                                        <input type="tel" id="phone" name="phone" class="w-full px-4 py-2 border rounded-lg" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                            <div class="bg-gray-100 p-4 font-medium text-gray-700">
                                <h2 class="text-xl">Payment Method</h2>
                            </div>
                            <div class="p-6">
                                <div class="flex items-center mb-4">
                                    <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked class="mr-2">
                                    <label for="credit_card" class="flex items-center">
                                        
                                        <i class="fa-solid fa-money-bill text-xl mr-2"></i>
                                        <span>Kpay</span>
                                    </label>
                                </div>
                                 <div class="flex items-center mb-4">
                                    <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked class="mr-2">
                                    <label for="credit_card" class="flex items-center">
                                        <i class="far fa-credit-card text-xl mr-2"></i>
                                        <span>Aya pay</span>
                                    </label>
                                </div>
                                <div class="flex items-center mb-4">
                                    <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked class="mr-2">
                                    <label for="credit_card" class="flex items-center">
                                        <i class="fa-solid fa-wallet text-xl mr-2"></i>
                                        <span>Cash On Deli</span>
                                    </label>
                                </div>
                                <div class="border-t pt-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <label class="block text-gray-700 mb-2" for="card_number">Card Number</label>
                                            <input type="text" id="card_number" name="card_number" class="w-full px-4 py-2 border rounded-lg" required>
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2" for="expiry">Expiry Date</label>
                                            <input type="text" id="expiry" name="expiry" placeholder="MM/YY" class="w-full px-4 py-2 border rounded-lg" required>
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2" for="cvv">CVV</label>
                                            <input type="text" id="cvv" name="cvv" class="w-full px-4 py-2 border rounded-lg" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                <span class="font-medium">$<?= number_format($shipping, 2) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax</span>
                                <span class="font-medium">$<?= number_format($tax, 2) ?></span>
                            </div>
                            <div class="flex justify-between border-t pt-4">
                                <span class="text-lg font-bold">Total</span>
                                <span class="text-lg font-bold">$<?= number_format($total, 2) ?></span>
                            </div>
                        </div>
                        
                        <button type="submit" name="place_order" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 px-4 rounded-lg font-medium transition duration-300">
                            Place Order
                        </button>
                        
                        <div class="mt-4 text-sm text-gray-500">
                            <p><i class="fas fa-lock mr-2"></i> Secure checkout</p>
                            <p class="mt-2"><i class="fas fa-shield-alt mr-2"></i> 100% Satisfaction Guarantee</p>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once "footer.php"; ?>
</body>
</html>