<?php
require_once 'db_conn.php';
session_start();

// Redirect if no order receipt exists
if (!isset($_SESSION['order_receipt'])) {
    header('Location: cart.php');
    exit();
}

$receipt = $_SESSION['order_receipt'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt | Velociti Bicycle Shop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="font-sans bg-gray-50">
    <?php require_once "navbar.php"; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Receipt Section -->
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-green-100 p-6 text-center border-b border-green-200">
                <i class="fas fa-check-circle text-5xl text-green-600 mb-4"></i>
                <h1 class="text-2xl font-bold text-green-800 mb-2">Order Confirmed!</h1>
                <p class="text-green-600">Thank you for your purchase, <?= htmlspecialchars($receipt['customer_name']) ?>!</p>
                <p class="text-gray-600 mt-2">Order #<?= $receipt['order_id'] ?></p>
            </div>

            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-xl font-bold">Order Receipt</h2>
                        <p class="text-gray-600"><?= $receipt['date'] ?></p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold">Order #<?= $receipt['order_id'] ?></p>
                        <p class="text-sm text-gray-600">Status: Completed</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-lg font-medium mb-3 border-b pb-2">Shipping Information</h3>
                        <p class="font-medium"><?= htmlspecialchars($receipt['shipping_info']['first_name']) ?> <?= htmlspecialchars($receipt['shipping_info']['last_name']) ?></p>
                        <p><?= htmlspecialchars($receipt['shipping_info']['address']) ?></p>
                        <p><?= htmlspecialchars($receipt['shipping_info']['city']) ?>, <?= htmlspecialchars($receipt['shipping_info']['zip']) ?></p>
                        <p>Phone: <?= htmlspecialchars($receipt['shipping_info']['phone']) ?></p>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium mb-3 border-b pb-2">Payment Method</h3>
                        <p class="flex items-center">
                            <i class="far fa-credit-card text-xl mr-2"></i>
                            <span>Credit Card</span>
                        </p>
                        <p class="text-sm text-gray-600 mt-2">Paid on <?= $receipt['date'] ?></p>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-lg font-medium mb-3 border-b pb-2">Items Purchased</h3>
                    <div class="space-y-4">
                        <?php foreach ($receipt['items'] as $item): ?>
                        <div class="flex items-center justify-between border-b pb-4">
                            <div class="flex items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden mr-4">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="font-medium"><?= htmlspecialchars($item['name']) ?></p>
                                    <p class="text-sm text-gray-500">Size: <?= htmlspecialchars($item['size']) ?></p>
                                    <p class="text-sm text-gray-500">Qty: <?= $item['quantity'] ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">$<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                                <p class="text-sm text-gray-500">$<?= number_format($item['price'], 2) ?> each</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600">Subtotal (<?= count($receipt['items']) ?> items)</span>
                        <span class="font-medium">$<?= number_format($receipt['subtotal'], 2) ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600">Shipping</span>
                        <span class="font-medium">$<?= number_format($receipt['shipping'], 2) ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-600">Tax</span>
                        <span class="font-medium">$<?= number_format($receipt['tax'], 2) ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-t border-gray-200 mt-2">
                        <span class="text-lg font-bold">Total</span>
                        <span class="text-lg font-bold">$<?= number_format($receipt['total'], 2) ?></span>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <a href="index.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                        Continue Shopping
                    </a>
                    <p class="mt-4 text-gray-600">A copy of this receipt has been sent to your email.</p>
                </div>
            </div>
        </div>
    </div>

    <?php require_once "footer.php"; ?>
</body>
</html>