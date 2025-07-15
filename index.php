<?php
// Main index file
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
  <div class="flex h-screen">
    <?php require_once "side_navbar.php"; ?>
    
    <div class="flex-1 overflow-auto">
      <?php require_once "manage_bicycles.php"; ?>
    </div>
  </div>
  
  <!-- Mobile menu toggle button -->
  <button id="mobileToggle" class="fixed bottom-4 right-4 bg-blue-600 text-white p-3 rounded-full shadow-lg md:hidden z-50">
    <i class="fas fa-bars"></i>
  </button>

  <script>
    // Mobile toggle functionality
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebar = document.querySelector('#sidebar');
    
    mobileToggle.addEventListener('click', () => {
      sidebar.classList.toggle('hidden');
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