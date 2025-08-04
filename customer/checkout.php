<?php
require_once 'db_conn.php';
session_start();

// Redirect if cart is empty
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
    try {
        $conn->beginTransaction();
        
        // Get customer info
        $customerId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Default to admin if not logged in
        $customerName = $_POST['first_name'] . ' ' . $_POST['last_name'];
        $customerEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'guest@example.com';
        
        // Insert into orders table
        $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, customer_email, total_amount, status) 
                               VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$customerId, $customerName, $customerEmail, $total]);
        $orderId = $conn->lastInsertId();
        
        // Insert into order_details
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
        
        // Insert into payments
        $stmt = $conn->prepare("INSERT INTO payments (order_id, amount, payment_method, status) 
                               VALUES (?, ?, ?, 'pending')");
        $stmt->execute([
            $orderId,
            $total,
            $_POST['payment_method']
        ]);
        
        $conn->commit();
        
        // Store receipt info in session
        $_SESSION['order_receipt'] = [
            'order_id' => $orderId,
            'date' => date('F j, Y'),
            'items' => $_SESSION['cart'],
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
            'customer_name' => $customerName,
            'shipping_info' => [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'address' => $_POST['address'],
                'city' => $_POST['city'],
                'zip' => $_POST['zip'],
                'phone' => $_POST['phone']
            ]
        ];
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        // Redirect to receipt
        header('Location: receipt.php');
        exit();
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Order processing failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Velociti Bicycle Shop</title>
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

        <h1 class="text-3xl font-bold mb-8">Checkout</h1>
        
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
                                    <label class="block text-gray-700 mb-2" for="first_name">First Name*</label>
                                    <input type="text" id="first_name" name="first_name" class="w-full px-4 py-2 border rounded-lg" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2" for="last_name">Last Name*</label>
                                    <input type="text" id="last_name" name="last_name" class="w-full px-4 py-2 border rounded-lg" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2" for="address">Address*</label>
                                    <input type="text" id="address" name="address" class="w-full px-4 py-2 border rounded-lg" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2" for="city">City*</label>
                                    <input type="text" id="city" name="city" class="w-full px-4 py-2 border rounded-lg" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2" for="zip">ZIP Code*</label>
                                    <input type="text" id="zip" name="zip" class="w-full px-4 py-2 border rounded-lg" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2" for="phone">Phone*</label>
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
                                    <i class="far fa-credit-card text-xl mr-2"></i>
                                    <span>Credit Card</span>
                                </label>
                            </div>
                            <div class="border-t pt-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-gray-700 mb-2" for="card_name">Name on Card*</label>
                                        <input type="text" id="card_name" name="card_name" class="w-full px-4 py-2 border rounded-lg" required>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-gray-700 mb-2" for="card_number">Card Number*</label>
                                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" class="w-full px-4 py-2 border rounded-lg" required>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2" for="expiry">Expiry Date*</label>
                                        <input type="text" id="expiry" name="expiry" placeholder="MM/YY" class="w-full px-4 py-2 border rounded-lg" required>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2" for="cvv">CVV*</label>
                                        <input type="text" id="cvv" name="cvv" placeholder="123" class="w-full px-4 py-2 border rounded-lg" required>
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
    </div>

    <?php require_once "footer.php"; ?>
</body>
</html>