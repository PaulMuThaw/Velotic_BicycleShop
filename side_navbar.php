<?php
$currentPage = $_GET['page'] ?? 'dashboard';
?>

<!-- Mobile toggle button -->
<button id="sidebarToggle" class="md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-md shadow-lg">
  <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Navigation -->
<div id="sidebar" class="fixed md:static inset-y-0 left-0 transform -translate-x-full md:translate-x-0 w-64 bg-white shadow-lg flex flex-col transition-transform duration-300 ease-in-out z-40">
  <div class="p-6 border-b flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">Admin Panel</h1>
    <button id="closeSidebar" class="md:hidden text-gray-500 hover:text-gray-700">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
    <a href="?page=dashboard" class="flex items-center p-3 rounded-lg no-underline transition-all <?= $currentPage === 'dashboard' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-600' ?>">
      <i class="fas fa-tachometer-alt mr-3"></i>
      <span>Dashboard</span>
    </a>
    <a href="?page=bicycles" class="flex items-center p-3 rounded-lg no-underline transition-all <?= $currentPage === 'bicycles' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-600' ?>">
      <i class="fas fa-bicycle mr-3"></i>
      <span>Manage Bicycles</span>
    </a>
    <a href="?page=feedback" class="flex items-center p-3 rounded-lg no-underline transition-all <?= $currentPage === 'feedback' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-600' ?>">
      <i class="fas fa-comment-alt mr-3"></i>
      <span>Feedback</span>
    </a>
    <a href="?page=users" class="flex items-center p-3 rounded-lg no-underline transition-all <?= $currentPage === 'users' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-600' ?>">
      <i class="fas fa-users mr-3"></i>
      <span>Users</span>
    </a>
    <a href="?page=customers" class="flex items-center p-3 rounded-lg no-underline transition-all <?= $currentPage === 'customers' ? 'bg-blue-100 text-blue-600' : 'text-gray-700 hover:bg-blue-100 hover:text-blue-600' ?>">
      <i class="fas fa-users mr-3"></i>
      <span>Manage Customers</span>
    </a>
  </nav>

  <div class="p-4 border-t">
    <a href="logout.php" class="flex items-center p-3 w-full text-gray-600 no-underline hover:bg-red-100 hover:text-red-600 rounded-lg transition-all">
    <i class="fas fa-sign-out-alt mr-3"></i>
    <span>Logout</span>
    </a>
  </div>
</div>

<!-- Overlay for mobile (hidden by default) -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden"></div>

<script>
  // Toggle sidebar on mobile
  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const closeSidebar = document.getElementById('closeSidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');
  
  sidebarToggle.addEventListener('click', () => {
    sidebar.classList.remove('-translate-x-full');
    sidebarOverlay.classList.remove('hidden');
  });
  
  closeSidebar.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    sidebarOverlay.classList.add('hidden');
  });
  
  sidebarOverlay.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    sidebarOverlay.classList.add('hidden');
  });
</script>