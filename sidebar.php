<?php
// Your PHP code here
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .sidebar-collapsed {
      width: 80px;
    }
    .sidebar-collapsed .nav-text {
      display: none;
    }
    .sidebar-collapsed .logo-text {
      display: none;
    }
  </style>
</head>
<body class="bg-gray-100">
  <div class="flex h-screen">
    <!-- Sidebar -->
    <div id="sidebar" class="bg-white shadow-lg w-64 flex flex-col transition-all duration-300 ease-in-out">
      <div class="p-6 border-b flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800 logo-text">Admin Panel</h1>
        <button id="toggleCollapse" class="hidden md:block">
          <i class="fas fa-bars"></i>
        </button>
      </div>

      <nav class="flex-1 p-4 space-y-2">
        <a href="dashboard.php" class="flex items-center p-3 rounded-lg text-gray-700 no-underline hover:bg-blue-100 hover:text-blue-600 transition-all">
          <i class="fas fa-tachometer-alt mr-3"></i>
          <span class="nav-text">Dashboard</span>
        </a>
        <a href="manage_bicycles.php" class="flex items-center p-3 rounded-lg text-blue-600 no-underline bg-blue-100 transition-all">
          <i class="fas fa-bicycle mr-3"></i>
          <span class="nav-text">Manage Bicycles</span>
        </a>
        <a href="manage_feedback.php" class="flex items-center p-3 rounded-lg text-gray-700 no-underline hover:bg-blue-100 hover:text-blue-600 transition-all">
          <i class="fas fa-comment-alt mr-3"></i>
          <span class="nav-text">Feedback</span>
        </a>
        <a href="manage_users.php" class="flex items-center p-3 rounded-lg text-gray-700 no-underline hover:bg-blue-100 hover:text-blue-600 transition-all">
          <i class="fas fa-users mr-3"></i>
          <span class="nav-text">Users</span>
        </a>
      </nav>

      <div class="p-4 border-t">
        <a href="logout.php" class="flex items-center p-3 w-full text-gray-600 no-underline hover:bg-red-100 hover:text-red-600 rounded-lg transition-all">
          <i class="fas fa-sign-out-alt mr-3"></i>
          <span class="nav-text">Logout</span>
        </a>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-auto">
      <div class="p-8">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-bold text-gray-800">Manage Bicycles</h2>
          <a href="insert_product.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition flex items-center">
            <i class="fas fa-plus mr-2"></i> Add New Product
          </a>
        </div>

        <div class="overflow-x-auto rounded-lg shadow">
          <table class="min-w-full bg-white border border-gray-200">
            <thead>
              <tr class="bg-gray-100 text-gray-700">
                <th class="py-3 px-5 border-b">#</th>
                <th class="py-3 px-5 border-b text-left">Name</th>
                <th class="py-3 px-5 border-b text-left">Price</th>
                <th class="py-3 px-5 border-b text-left">Category</th>
                <th class="py-3 px-5 border-b text-left">Status</th>
                <th class="py-3 px-5 border-b text-left">Image</th>
                <th class="py-3 px-5 border-b text-left">Actions</th>
              </tr>
            </thead>
            <tbody class="text-gray-600">
              <!-- Example Row (replace with PHP loop later) -->
              <tr class="hover:bg-gray-50">
                <td class="py-3 px-5 border-b">1</td>
                <td class="py-3 px-5 border-b">Roadmaster X200</td>
                <td class="py-3 px-5 border-b">$1200.00</td>
                <td class="py-3 px-5 border-b">Bicycle</td>
                <td class="py-3 px-5 border-b">
                  <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">In Stock</span>
                </td>
                <td class="py-3 px-5 border-b">
                  <img src="uploads/bike1.jpg" alt="Bike" class="w-16 h-16 object-cover rounded">
                </td>
                <td class="py-3 px-5 border-b space-x-2">
                  <a href="update_product.php?id=1" class="px-3 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 transition">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="delete_product.php?id=1" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
              <!-- Add more rows dynamically via PHP loop -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile menu toggle button -->
  <button id="mobileToggle" class="fixed bottom-4 right-4 bg-blue-600 text-white p-3 rounded-full shadow-lg md:hidden z-50">
    <i class="fas fa-bars"></i>
  </button>

  <script>
    // Mobile toggle
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebar = document.getElementById('sidebar');
    
    mobileToggle.addEventListener('click', () => {
      sidebar.classList.toggle('hidden');
    });

    // Collapse toggle for desktop
    const toggleCollapse = document.getElementById('toggleCollapse');
    
    toggleCollapse?.addEventListener('click', () => {
      sidebar.classList.toggle('sidebar-collapsed');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
      if (window.innerWidth < 768 && !sidebar.contains(e.target) && e.target !== mobileToggle) {
        sidebar.classList.add('hidden');
      }
    });
  </script>
</body>
</html>