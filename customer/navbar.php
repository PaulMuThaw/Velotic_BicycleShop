<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="bg-white shadow-lg">
    <div class="container mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="index.php" class="text-2xl font-bold text-blue-600">Velociti</a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="index.php" class="text-gray-800 hover:text-blue-600">Home</a>
                <a href="view_allproduct.php" class="text-gray-800 hover:text-blue-600">Our Product</a>
                <a href="about.php" class="text-gray-800 hover:text-blue-600">About</a>
                <a href="contact.php" class="text-gray-800 hover:text-blue-600">Contact</a>
            </div>

            <!-- Right-side icons and buttons -->
            <div class="flex items-center space-x-4">
                <!-- Cart Icon -->
                <a href="cart.php" class="text-gray-800 hover:text-blue-600 relative">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            <?= $_SESSION['cart_count'] ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if (isset($_SESSION['customer_id'])): ?>
                    <!-- User Profile Dropdown (Logged In) -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-1 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            <span><?= htmlspecialchars(explode(' ', $_SESSION['customer_name'])[0]) ?></span>
                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'transform rotate-180': open }"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" 
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 border border-gray-200"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">
                            <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-50">My Profile</a>
                            <a href="orders.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-50">My Orders</a>
                            <a href="login.php" class="block px-4 py-2 text-gray-800 hover:bg-blue-50">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Login Button (Not Logged In) -->
                    <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-user mr-2"></i>Login
                    </a>
                <?php endif; ?>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-gray-800 mobile-menu-button">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            
        </div>

        <!-- Mobile Navigation -->
        <div class="hidden mobile-menu md:hidden">
            <div class="px-2 pt-2 pb-4 space-y-1 sm:px-3">
                <a href="index.php" class="block px-3 py-2 text-gray-800 hover:text-blue-600">Home</a>
                <a href="products.php" class="block px-3 py-2 text-gray-800 hover:text-blue-600">Our Product</a>
                <a href="about.php" class="block px-3 py-2 text-gray-800 hover:text-blue-600">About</a>
                <a href="contact.php" class="block px-3 py-2 text-gray-800 hover:text-blue-600">Contact</a>
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <div class="pt-4 border-t border-gray-200">
                        <a href="profile.php" class="block px-3 py-2 text-gray-800 hover:text-blue-600">My Profile</a>
                        <a href="orders.php" class="block px-3 py-2 text-gray-800 hover:text-blue-600">My Orders</a>
                        <a href="login.php" class="block px-3 py-2 text-gray-800 hover:text-blue-600">Logout</a>
                    </div>
                <?php else: ?>
                    <div class="pt-4 border-t border-gray-200">
                        <a href="login.php" class="block px-3 py-2 text-gray-800 hover:text-blue-600">Login</a>
                        <a href="signup.php" class="block px-3 py-2 text-gray-800 hover:text-blue-600">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Add Alpine.js for the dropdown functionality -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.querySelector('.mobile-menu-button');
        const mobileMenu = document.querySelector('.mobile-menu');
        
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    });
</script>