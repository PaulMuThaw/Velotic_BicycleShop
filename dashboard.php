<?php
require_once 'db_conn.php';

// Get counts for dashboard
$productCount = $conn ->query("SELECT COUNT(*) FROM products")->fetchColumn();
$userCount = $conn ->query("SELECT COUNT(*) FROM users")->fetchColumn();
$feedbackCount = $conn ->query("SELECT COUNT(*) FROM feedback")->fetchColumn();

// Data for charts
$orderStatusData = $conn ->query("
    SELECT status, COUNT(*) as count 
    FROM orders 
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

$monthlySalesData = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(total_amount) as total
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
")->fetchAll(PDO::FETCH_ASSOC);

$feedbackRatingData = $conn ->query("
    SELECT rating, COUNT(*) as count
    FROM feedback
    GROUP BY rating
    ORDER BY rating ASC
")->fetchAll(PDO::FETCH_ASSOC);

$recentOrders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$recentFeedback = $conn ->query("
    SELECT f.*, p.name as product_name 
    FROM feedback f
    LEFT JOIN products p ON f.product_id = p.id
    ORDER BY f.created_at DESC LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="p-8">
  <h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Overview</h2>
  
  <!-- Stats Cards Row -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow">
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
          <i class="fas fa-bicycle text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-gray-500">Total Products</h3>
          <p class="text-2xl font-semibold"><?= $productCount ?></p>
        </div>
      </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-green-100 text-green-600">
          <i class="fas fa-users text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-gray-500">Total Users</h3>
          <p class="text-2xl font-semibold"><?= $userCount ?></p>
        </div>
      </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow">
      <div class="flex items-center">
        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
          <i class="fas fa-comments text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-gray-500">Total Feedback</h3>
          <p class="text-2xl font-semibold"><?= $feedbackCount ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Row -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Order Status Pie Chart -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-semibold mb-4">Order Status Distribution</h3>
      <div class="h-64">
        <canvas id="orderStatusChart"></canvas>
      </div>
    </div>

    <!-- Monthly Sales Line Chart -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-semibold mb-4">Monthly Sales (Last 6 Months)</h3>
      <div class="h-64">
        <canvas id="salesTrendChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Second Charts Row -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Feedback Ratings Chart -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-semibold mb-4">Feedback Ratings</h3>
      <div class="h-64">
        <canvas id="feedbackRatingsChart"></canvas>
      </div>
    </div>

    <!-- Product Category Distribution -->
    <div class="bg-white p-6 rounded-lg shadow">
      <h3 class="text-lg font-semibold mb-4">Product Categories</h3>
      <div class="h-64">
        <canvas id="productCategoryChart"></canvas>
      </div>
    </div>
  </div>
  
  <!-- Recent Orders -->
  <div class="bg-white p-6 rounded-lg shadow mb-8">
    <h3 class="text-lg font-semibold mb-4">Recent Orders</h3>
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead>
          <tr class="border-b">
            <th class="py-2 px-4 text-left">Order ID</th>
            <th class="py-2 px-4 text-left">Customer</th>
            <th class="py-2 px-4 text-left">Amount</th>
            <th class="py-2 px-4 text-left">Status</th>
            <th class="py-2 px-4 text-left">Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $order): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="py-2 px-4">#<?= $order['id'] ?></td>
            <td class="py-2 px-4"><?= $order['customer_name'] ?></td>
            <td class="py-2 px-4">$<?= number_format($order['total_amount'], 2) ?></td>
            <td class="py-2 px-4">
              <span class="px-2 py-1 text-xs rounded-full bg-<?= 
                $order['status'] === 'completed' ? 'green' : 
                ($order['status'] === 'processing' ? 'blue' : 'yellow')
              ?>-100 text-<?= 
                $order['status'] === 'completed' ? 'green' : 
                ($order['status'] === 'processing' ? 'blue' : 'yellow')
              ?>-800">
                <?= ucfirst($order['status']) ?>
              </span>
            </td>
            <td class="py-2 px-4"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <!-- Recent Feedback -->
  <div class="bg-white p-6 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Recent Feedback</h3>
    <div class="space-y-4">
      <?php foreach ($recentFeedback as $feedback): ?>
      <div class="border-b pb-4">
        <div class="flex justify-between">
          <h4 class="font-medium"><?= htmlspecialchars($feedback['product_name'] ?? 'General') ?></h4>
          <div class="flex text-yellow-400">
            <?php
            $rating = $feedback['rating'];
            for ($i = 1; $i <= 5; $i++) {
                echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
            }
            ?>
          </div>
        </div>
        <p class="text-gray-600 my-2"><?= htmlspecialchars(substr($feedback['message'], 0, 100)) ?>...</p>
        <div class="flex justify-between text-sm text-gray-500">
          <span><?= htmlspecialchars($feedback['email']) ?></span>
          <span><?= date('M d, Y', strtotime($feedback['created_at'])) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Order Status Pie Chart
const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
const orderStatusChart = new Chart(orderStatusCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($orderStatusData, 'status')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($orderStatusData, 'count')) ?>,
            backgroundColor: [
                '#10B981', // green
                '#3B82F6', // blue
                '#F59E0B', // yellow
                '#EF4444', // red
                '#6B7280'  // gray
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Monthly Sales Line Chart
const salesTrendCtx = document.getElementById('salesTrendChart').getContext('2d');
const salesTrendChart = new Chart(salesTrendCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthlySalesData, 'month')) ?>,
        datasets: [{
            label: 'Monthly Sales ($)',
            data: <?= json_encode(array_column($monthlySalesData, 'total')) ?>,
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderColor: '#3B82F6',
            borderWidth: 2,
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Feedback Ratings Chart
const feedbackRatingsCtx = document.getElementById('feedbackRatingsChart').getContext('2d');
const feedbackRatingsChart = new Chart(feedbackRatingsCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($d) => $d['rating'] . ' Stars', $feedbackRatingData)) ?>,
        datasets: [{
            label: 'Number of Reviews',
            data: <?= json_encode(array_column($feedbackRatingData, 'count')) ?>,
            backgroundColor: '#F59E0B',
            borderColor: '#D97706',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Product Category Chart (you'll need to fetch this data)
const productCategoryCtx = document.getElementById('productCategoryChart').getContext('2d');
const productCategoryChart = new Chart(productCategoryCtx, {
    type: 'doughnut',
    data: {
        labels: ['Road Bikes', 'Mountain Bikes', 'Hybrid Bikes', 'Accessories'],
        datasets: [{
            data: [35, 25, 20, 20],
            backgroundColor: [
                '#3B82F6',
                '#10B981',
                '#F59E0B',
                '#8B5CF6'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>