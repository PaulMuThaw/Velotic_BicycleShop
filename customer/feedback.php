<?php
require_once 'db_conn.php';

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $product_input = $_POST['product_input'] ?? null;
    $product_id = $_POST['product_id'] ?? null;
    $rating = $_POST['rating'];
    $message = $_POST['message'];
    
    try {
        // If a product was typed manually (not selected from dropdown)
        if (!empty($product_input) && empty($product_id)) {
            // Check if product exists in database
            $stmt = $conn->prepare("SELECT id FROM products WHERE name = ?");
            $stmt->execute([$product_input]);
            $existing_product = $stmt->fetch();
            
            if ($existing_product) {
                $product_id = $existing_product['id'];
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, product_id, product_name, rating, message) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $name, 
            $email, 
            $product_id,
            empty($product_id) ? $product_input : null, // Store manual entry if no ID
            $rating, 
            $message
        ]);
        
        $success = "Thank you for your feedback!";
        $_POST = []; // Clear form
    } catch (PDOException $e) {
        $error = "Error submitting feedback. Please try again.";
    }
}

// Get products for dropdown
$products = $conn->query("SELECT id, name FROM products WHERE status = 'In Stock'")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback | Bicycle Shop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .star-rating i {
            cursor: pointer;
            transition: all 0.2s;
        }
        .star-rating i:hover {
            transform: scale(1.2);
        }
        /* Style for the combobox */
        .combobox {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .combobox-input {
            width: 100%;
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
        }
        .combobox-options {
            position: absolute;
            z-index: 10;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: none;
        }
        .combobox-option {
            padding: 0.5rem 1rem;
            cursor: pointer;
        }
        .combobox-option:hover {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Simple header with back button -->
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center">
                <a href="index.php" class="text-blue-600 hover:text-blue-800 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-xl font-semibold text-gray-800">Share Your Feedback</h1>
            </div>
        </div>
    </header>
    
    <main class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-md p-6">
            <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-gray-700 mb-2 font-medium">Your Name*</label>
                        <input type="text" id="name" name="name" required 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-gray-700 mb-2 font-medium">Email Address*</label>
                        <input type="email" id="email" name="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <div>
                    <label for="product_input" class="block text-gray-700 mb-2 font-medium">Product (select or type)*</label>
                    <div class="combobox">
                        <input type="text" id="product_input" name="product_input" 
                               value="<?= htmlspecialchars($_POST['product_input'] ?? '') ?>"
                               class="combobox-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Type or select a product..."
                               autocomplete="off">
                        <input type="hidden" id="product_id" name="product_id" value="<?= $_POST['product_id'] ?? '' ?>">
                        <div class="combobox-options" id="product-options">
                            <?php foreach ($products as $product): ?>
                            <div class="combobox-option" data-value="<?= $product['id'] ?>" data-text="<?= htmlspecialchars($product['name']) ?>">
                                <?= htmlspecialchars($product['name']) ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2 font-medium">Your Rating*</label>
                    <div class="star-rating text-3xl text-gray-300 mb-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star cursor-pointer" 
                           data-rating="<?= $i ?>" 
                           onmouseover="highlightStars(this)"
                           onclick="setRating(this)"
                           <?= isset($_POST['rating']) && $_POST['rating'] >= $i ? 'style="color: gold;"' : '' ?>></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="rating" value="<?= $_POST['rating'] ?? '' ?>" required>
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Poor</span>
                        <span>Excellent</span>
                    </div>
                </div>
                
                <div>
                    <label for="message" class="block text-gray-700 mb-2 font-medium">Your Feedback*</label>
                    <textarea id="message" name="message" rows="5" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= 
                              htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" 
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200 font-medium">
                    <i class="fas fa-paper-plane mr-2"></i> Submit Feedback
                </button>
            </form>
        </div>
    </main>

    <script>
        // Star rating functionality
        function highlightStars(element) {
            const stars = document.querySelectorAll('.star-rating i');
            const rating = parseInt(element.getAttribute('data-rating'));
            
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.style.color = 'gold';
                } else {
                    star.style.color = '#e5e7eb';
                }
            });
        }
        
        function setRating(element) {
            const rating = element.getAttribute('data-rating');
            document.getElementById('rating').value = rating;
            
            const stars = document.querySelectorAll('.star-rating i');
            stars.forEach((star, index) => {
                star.style.color = index < rating ? 'gold' : '#e5e7eb';
            });
        }

        // Combobox functionality
        const productInput = document.getElementById('product_input');
        const productIdInput = document.getElementById('product_id');
        const productOptions = document.getElementById('product-options');
        const allOptions = Array.from(document.querySelectorAll('.combobox-option'));

        productInput.addEventListener('focus', () => {
            productOptions.style.display = 'block';
            filterOptions();
        });

        productInput.addEventListener('blur', () => {
            setTimeout(() => {
                productOptions.style.display = 'none';
            }, 200);
        });

        productInput.addEventListener('input', filterOptions);

        function filterOptions() {
            const searchTerm = productInput.value.toLowerCase();
            let hasMatches = false;
            
            allOptions.forEach(option => {
                const text = option.getAttribute('data-text').toLowerCase();
                if (text.includes(searchTerm)) {
                    option.style.display = 'block';
                    hasMatches = true;
                } else {
                    option.style.display = 'none';
                }
            });
            
            productOptions.style.display = hasMatches ? 'block' : 'none';
        }

        allOptions.forEach(option => {
            option.addEventListener('mousedown', (e) => {
                e.preventDefault();
                productInput.value = option.getAttribute('data-text');
                productIdInput.value = option.getAttribute('data-value');
                productOptions.style.display = 'none';
            });
        });
    </script>
</body>
</html>