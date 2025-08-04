<?php
session_start();
require_once 'db_conn.php';

// Redirect if not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

// Get customer details
$customer_id = $_SESSION['customer_id'];
try {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
    
    // Get password change date
    $password_changed = $conn->query("
        SELECT updated_at FROM customers 
        WHERE id = $customer_id AND password_changed_at IS NOT NULL
        ORDER BY password_changed_at DESC LIMIT 1
    ")->fetchColumn();
    
    // Get customer orders
    $orders = $conn->query("
        SELECT o.*, COUNT(i.id) as item_count, SUM(i.quantity * i.price) as order_total 
        FROM orders o
        LEFT JOIN order_items i ON o.id = i.order_id
        WHERE o.customer_id = $customer_id
        GROUP BY o.id
        ORDER BY o.order_date DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get customer addresses
    $addresses = $conn->query("
        SELECT * FROM customer_addresses 
        WHERE customer_id = $customer_id
        ORDER BY is_default DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $orders = $addresses = [];
    $dbError = "We're experiencing technical difficulties. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Velociti Bicycle Shop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-pic {
            background-color: #3b82f6;
            color: white;
        }
        .order-status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-processing {
            background-color: #93c5fd;
            color: #1e40af;
        }
        .status-shipped {
            background-color: #a7f3d0;
            color: #065f46;
        }
        .status-delivered {
            background-color: #d1fae5;
            color: #047857;
        }
        .status-cancelled {
            background-color: #fca5a5;
            color: #991b1b;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php require_once "navbar.php"; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar -->
            <div class="w-full md:w-1/4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex flex-col items-center">
                        <div class="profile-pic rounded-full w-24 h-24 flex items-center justify-center text-3xl font-bold mb-4">
                            <?= strtoupper(substr($customer['name'], 0, 1)) ?>
                        </div>
                        <h3 class="text-xl font-bold"><?= htmlspecialchars($customer['name']) ?></h3>
                        <p class="text-gray-600 text-sm">Member since <?= date('M Y', strtotime($customer['created_at'])) ?></p>
                    </div>
                    
                    <nav class="mt-8">
                        <ul class="space-y-2">
                            <li>
                                <a href="profile.php" class="flex items-center px-4 py-3 bg-blue-100 text-blue-800 rounded-lg font-medium">
                                    <i class="fas fa-user-circle mr-3"></i> My Profile
                                </a>
                            </li>
                            <li>
                                <a href="orders.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                                    <i class="fas fa-shopping-bag mr-3"></i> My Orders
                                </a>
                            </li>
                            <li>
                                <a href="addresses.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                                    <i class="fas fa-map-marker-alt mr-3"></i> My Addresses
                                </a>
                            </li>
                            <li>
                                <a href="wishlist.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                                    <i class="fas fa-heart mr-3"></i> Wishlist
                                </a>
                            </li>
                            <li>
                                <a href="settings.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg">
                                    <i class="fas fa-cog mr-3"></i> Account Settings
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="w-full md:w-3/4">
                <!-- Welcome Banner -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-2">Welcome back, <?= htmlspecialchars(explode(' ', $customer['name'])[0]) ?>!</h2>
                    <p class="text-gray-600">Here's what's happening with your account today.</p>
                </div>
                
            <!-- Account Overview -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">Account Overview</h3>
                    <a href="edit-profile.php" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-edit mr-1"></i> Edit Profile
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold mb-3">Personal Information</h4>
                        <div class="space-y-2">
                            <p><span class="font-medium">Name:</span> <?= htmlspecialchars($customer['name']) ?></p>
                            <p><span class="font-medium">Email:</span> <?= htmlspecialchars($customer['email']) ?></p>
                            <p><span class="font-medium">Phone:</span> <?= $customer['phone'] ? htmlspecialchars($customer['phone']) : 'Not provided' ?></p>
                            <p><span class="font-medium">Last Login:</span> <?= $customer['last_login'] ? date('M j, Y g:i a', strtotime($customer['last_login'])) : 'First login' ?></p>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-3">Password</h4>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <div class="flex justify-between items-center">
                                <p class="font-medium">Manage your account password</p>
                                <a href="change-password.php" class="text-blue-600 hover:text-blue-800 font-medium">
                                    Change Password
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                
                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold">Recent Orders</h3>
                        <a href="orders.php" class="text-blue-600 hover:text-blue-800">View All Orders</a>
                    </div>
                    
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-shopping-bag text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-600">You haven't placed any orders yet.</p>
                            <a href="products.php" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                                Start Shopping <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?= $order['id'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $order['item_count'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">$<?= number_format($order['order_total'], 2) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="order-status status-<?= strtolower($order['status']) ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="order-details.php?id=<?= $order['id'] ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Saved Addresses -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold">Saved Addresses</h3>
                        <a href="add-address.php" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-plus mr-1"></i> Add New
                        </a>
                    </div>
                    
                    <?php if (empty($addresses)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-map-marker-alt text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-600">You haven't saved any addresses yet.</p>
                            <a href="add-address.php" class="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium">
                                Add Address <i class="fas fa-plus ml-1"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($addresses as $address): ?>
                            <div class="border rounded-lg p-4 <?= $address['is_default'] ? 'border-blue-500 bg-blue-50' : 'border-gray-200' ?>">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-medium"><?= htmlspecialchars($address['address_title']) ?></h4>
                                    <?php if ($address['is_default']): ?>
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Default</span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2 text-sm text-gray-600">
                                    <p><?= htmlspecialchars($address['recipient_name']) ?></p>
                                    <p><?= htmlspecialchars($address['address_line1']) ?></p>
                                    <?php if ($address['address_line2']): ?>
                                        <p><?= htmlspecialchars($address['address_line2']) ?></p>
                                    <?php endif; ?>
                                    <p><?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['state']) ?> <?= htmlspecialchars($address['postal_code']) ?></p>
                                    <p class="mt-2"><span class="font-medium">Phone:</span> <?= htmlspecialchars($address['phone']) ?></p>
                                </div>
                                <div class="mt-3 flex space-x-3">
                                    <a href="edit-address.php?id=<?= $address['id'] ?>" class="text-sm text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </a>
                                    <?php if (!$address['is_default']): ?>
                                        <a href="set-default-address.php?id=<?= $address['id'] ?>" class="text-sm text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-star mr-1"></i> Set Default
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once "footer.php"; ?>
</body>
</html>