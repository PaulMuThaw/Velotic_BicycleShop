<?php
require_once 'db_conn.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Velociti Bicycle Shop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-bg {
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../uploads/about-hero.jpg');
            background-size: cover;
            background-position: center;
        }
        .team-member:hover img {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="font-sans bg-gray-50">
    <?php require_once "navbar.php"; ?>

    <!-- Hero Section -->
    <section class="hero-bg text-white py-24 md:py-36">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">Our Story</h1>
            <p class="text-xl lg:text-2xl mb-10 max-w-3xl mx-auto">Passion for cycling since 2010</p>
        </div>
    </section>

    <!-- About Content Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-12 items-center">
                <div class="lg:w-1/2">
                    <img src="../uploads/about-shop.jpg" alt="Our bicycle shop" class="rounded-lg shadow-xl w-full h-auto">
                </div>
                <div class="lg:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">About Velociti Bicycle Shop</h2>
                    <p class="text-lg text-gray-700 mb-4">
                        Founded in 2010, Velociti Bicycle Shop began as a small neighborhood bike repair shop. What started as a passion project quickly grew into the premier bicycle destination in the region.
                    </p>
                    <p class="text-lg text-gray-700 mb-4">
                        We believe bicycles transform lives and communities. Our mission is to get more people on bikes by providing exceptional products, expert service, and a welcoming environment for cyclists of all levels.
                    </p>
                    <div class="mt-8 grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-bold text-blue-800 mb-2">10,000+</h3>
                            <p class="text-gray-700">Bikes Sold</p>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-bold text-blue-800 mb-2">50+</h3>
                            <p class="text-gray-700">Brands Carried</p>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-bold text-blue-800 mb-2">100%</h3>
                            <p class="text-gray-700">Satisfaction Guarantee</p>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-bold text-blue-800 mb-2">Expert</h3>
                            <p class="text-gray-700">Mechanics</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Meet Our Team</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Team Member 1 -->
                <div class="bg-white rounded-xl overflow-hidden shadow-md team-member">
                    <div class="relative h-64 overflow-hidden">
                        <img src="../uploads/team1.jpg" alt="Alex Johnson" class="w-full h-full object-cover transition duration-500">
                    </div>
                    <div class="p-6 text-center">
                        <h3 class="text-xl font-bold">Alex Johnson</h3>
                        <p class="text-blue-600 font-medium">Founder & CEO</p>
                        <p class="text-gray-600 mt-2">Cycling enthusiast since childhood</p>
                    </div>
                </div>
                
                <!-- Team Member 2 -->
                <div class="bg-white rounded-xl overflow-hidden shadow-md team-member">
                    <div class="relative h-64 overflow-hidden">
                        <img src="../uploads/team2.jpg" alt="Sarah Chen" class="w-full h-full object-cover transition duration-500">
                    </div>
                    <div class="p-6 text-center">
                        <h3 class="text-xl font-bold">Sarah Chen</h3>
                        <p class="text-blue-600 font-medium">Head Mechanic</p>
                        <p class="text-gray-600 mt-2">15 years of bike repair experience</p>
                    </div>
                </div>
                
                <!-- Team Member 3 -->
                <div class="bg-white rounded-xl overflow-hidden shadow-md team-member">
                    <div class="relative h-64 overflow-hidden">
                        <img src="../uploads/team3.jpg" alt="Michael Rodriguez" class="w-full h-full object-cover transition duration-500">
                    </div>
                    <div class="p-6 text-center">
                        <h3 class="text-xl font-bold">Michael Rodriguez</h3>
                        <p class="text-blue-600 font-medium">Sales Manager</p>
                        <p class="text-gray-600 mt-2">Knows every bike we carry</p>
                    </div>
                </div>
                
                <!-- Team Member 4 -->
                <div class="bg-white rounded-xl overflow-hidden shadow-md team-member">
                    <div class="relative h-64 overflow-hidden">
                        <img src="../uploads/team4.jpg" alt="Jamie Wilson" class="w-full h-full object-cover transition duration-500">
                    </div>
                    <div class="p-6 text-center">
                        <h3 class="text-xl font-bold">Jamie Wilson</h3>
                        <p class="text-blue-600 font-medium">Customer Service</p>
                        <p class="text-gray-600 mt-2">Makes everyone feel welcome</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl md:text-4xl font-bold text-center mb-12">Our Values</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <div class="bg-blue-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-heart text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Passion for Cycling</h3>
                    <p class="text-gray-600">We live and breathe bicycles. Our team consists of passionate cyclists who understand your needs.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <div class="bg-blue-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-cogs text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Expert Craftsmanship</h3>
                    <p class="text-gray-600">Every bike we sell or service receives our meticulous attention to detail.</p>
                </div>
                
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <div class="bg-blue-100 w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-handshake text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Community Focus</h3>
                    <p class="text-gray-600">We support local cycling initiatives and organize regular group rides.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-blue-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Ride With Us?</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Visit our shop or browse our online collection to find your perfect bike.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="view_allProduct.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition duration-300">
                    Shop Bikes
                </a>
                <a href="contact.php" class="bg-transparent border-2 border-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700 transition duration-300">
                    Contact Us
                </a>
            </div>
        </div>
    </section>

    <?php require_once "footer.php"; ?>
</body>
</html>