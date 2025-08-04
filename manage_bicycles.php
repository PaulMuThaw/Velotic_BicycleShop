<?php
require_once 'db_conn.php';

// Handle form submission for new product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Add new product
        $name = $_POST['name'];
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];
        $status = $_POST['status'];
        $description = $_POST['description'];
        
        // Handle file upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $imageName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image = $targetPath;
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO products (name, price, category_id, status, image, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $category_id, $status, $image, $description]);
        header("Location: ?page=bicycles");
        exit();
    } elseif (isset($_POST['update_product'])) {
        // Update existing product
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];
        $status = $_POST['status'];
        $description = $_POST['description'];
        
        // Handle file upload if new image provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $imageName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Delete old image if exists
                $oldImage = $conn->query("SELECT image FROM products WHERE id = $id")->fetchColumn();
                if ($oldImage && file_exists($oldImage)) {
                    unlink($oldImage);
                }
                $image = $targetPath;
                $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category_id = ?, status = ?, image = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $price, $category_id, $status, $image, $description, $id]);
            }
        } else {
            $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, category_id = ?, status = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $price, $category_id, $status, $description, $id]);
        }
        header("Location: ?page=bicycles");
        exit();
    }
}

// Handle product deletion
if (isset($_GET['delete_id'])) {
    // Delete product image if exists
    $image = $conn->query("SELECT image FROM products WHERE id = " . $_GET['delete_id'])->fetchColumn();
    if ($image && file_exists($image)) {
        unlink($image);
    }
    
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: ?page=bicycles");
    exit();
}

// Get all categories for dropdowns
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all products with category names
$products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.name
")->fetchAll(PDO::FETCH_ASSOC);

// Get product data for editing if edit_id is set
$editProduct = null;
if (isset($_GET['edit_id'])) {
    $editProduct = $conn->query("
        SELECT * FROM products 
        WHERE id = " . $_GET['edit_id']
    )->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="p-8">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Manage Products</h2>
    <button onclick="document.getElementById('productModal').classList.remove('hidden')" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition flex items-center">
      <i class="fas fa-plus mr-2"></i> Add New Product
    </button>
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
        <?php foreach ($products as $product): ?>
        <tr class="hover:bg-gray-50">
          <td class="py-3 px-5 border-b"><?= htmlspecialchars($product['id']) ?></td>
          <td class="py-3 px-5 border-b">
            <div class="font-medium"><?= htmlspecialchars($product['name']) ?></div>
            <div class="text-sm text-gray-500"><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</div>
          </td>
          <td class="py-3 px-5 border-b">$<?= number_format($product['price'], 2) ?></td>
          <td class="py-3 px-5 border-b"><?= htmlspecialchars($product['category_name']) ?></td>
          <td class="py-3 px-5 border-b">
            <span class="bg-<?= $product['status'] === 'In Stock' ? 'green' : ($product['status'] === 'New' ? 'blue' : 'red') ?>-100 text-<?= $product['status'] === 'In Stock' ? 'green' : ($product['status'] === 'New' ? 'blue' : 'red') ?>-800 text-xs px-2 py-1 rounded-full">
              <?= htmlspecialchars($product['status']) ?>
            </span>
          </td>
          <td class="py-3 px-5 border-b">
            <?php if ($product['image']): ?>
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image" class="w-16 h-16 object-cover rounded">
            <?php else: ?>
            <span class="text-gray-400">No image</span>
            <?php endif; ?>
          </td>
          <td class="py-3 px-5 border-b space-x-2">
            <a href="?page=bicycles&edit_id=<?= $product['id'] ?>" class="px-3 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 transition">
              <i class="fas fa-edit"></i>
            </a>
            <a href="?page=bicycles&delete_id=<?= $product['id'] ?>" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition" onclick="return confirm('Are you sure you want to delete this product?')">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Product Modal (Add/Edit) -->
<div id="productModal" class="<?= !isset($_GET['edit_id']) ? 'hidden' : '' ?> fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
    <div class="flex justify-between items-center border-b p-4">
      <h3 class="text-lg font-semibold"><?= isset($_GET['edit_id']) ? 'Edit' : 'Add' ?> Product</h3>
      <button onclick="window.location.href='?page=bicycles'" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <form method="POST" enctype="multipart/form-data" class="p-4">
      <?php if (isset($_GET['edit_id'])): ?>
      <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
      <?php endif; ?>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Product Name*</label>
        <input type="text" name="name" value="<?= $editProduct['name'] ?? '' ?>" required class="w-full px-3 py-2 border rounded">
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Price*</label>
        <input type="number" step="0.01" name="price" value="<?= $editProduct['price'] ?? '' ?>" required class="w-full px-3 py-2 border rounded">
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Category*</label>
        <select name="category_id" required class="w-full px-3 py-2 border rounded">
          <?php foreach ($categories as $category): ?>
          <option value="<?= $category['id'] ?>" <?= isset($editProduct) && $editProduct['category_id'] == $category['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($category['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Status*</label>
        <select name="status" required class="w-full px-3 py-2 border rounded">
          <option value="In Stock" <?= isset($editProduct) && $editProduct['status'] == 'In Stock' ? 'selected' : '' ?>>In Stock</option>
          <option value="Out of Stock" <?= isset($editProduct) && $editProduct['status'] == 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
          <option value="New" <?= isset($editProduct) && $editProduct['status'] == 'New' ? 'selected' : '' ?>>New</option>
        </select>
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Description</label>
        <textarea name="description" class="w-full px-3 py-2 border rounded"><?= $editProduct['description'] ?? '' ?></textarea>
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Product Image<?= !isset($_GET['edit_id']) ? '*' : '' ?></label>
        <input type="file" name="image" <?= !isset($_GET['edit_id']) ? 'required' : '' ?> accept="image/*" class="w-full px-3 py-2 border rounded">
        <?php if (isset($_GET['edit_id']) && !empty($editProduct['image'])): ?>
        <div class="mt-2">
          <img src="<?= $editProduct['image'] ?>" alt="Current Image" class="w-16 h-16 object-cover rounded">
          <p class="text-sm text-gray-500 mt-1">Current image</p>
        </div>
        <?php endif; ?>
      </div>
      
      <div class="flex justify-end space-x-2 pt-4 border-t">
        <button type="button" onclick="window.location.href='?page=bicycles'" class="px-4 py-2 border rounded hover:bg-gray-100">
          Cancel
        </button>
        <button type="submit" name="<?= isset($_GET['edit_id']) ? 'update_product' : 'add_product' ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
          <?= isset($_GET['edit_id']) ? 'Update' : 'Save' ?> Product
        </button>
      </div>
    </form>
  </div>
</div>

<?php if (isset($_GET['edit_id'])): ?>
<script>
  // Auto-open modal when edit_id is present
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('productModal').classList.remove('hidden');
  });
</script>
<?php endif; ?>