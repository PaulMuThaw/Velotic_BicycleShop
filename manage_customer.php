<?php
require_once 'db_conn.php';

// Handle form submission for new customer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_customer'])) {
        // Add new customer
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $status = 'active'; // Default status
        
        $stmt = $conn->prepare("INSERT INTO customers (name, email, password, phone, address, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $phone, $address, $status]);
        header("Location: ?page=customers");
        exit();
    } elseif (isset($_POST['update_customer'])) {
        // Update existing customer
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $status = $_POST['status'];
        
        // Handle password update if provided
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, password = ?, phone = ?, address = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $email, $password, $phone, $address, $status, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, address = ?, status = ? WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $address, $status, $id]);
        }
        header("Location: ?page=customers");
        exit();
    }
}

// Handle customer deletion
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: ?page=customers");
    exit();
}

// Fetch all customers
$customers = $conn->query("SELECT * FROM customers ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get customer data for editing if edit_id is set
$editCustomer = null;
if (isset($_GET['edit_id'])) {
    $editCustomer = $conn->query("SELECT * FROM customers WHERE id = " . $_GET['edit_id'])->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="p-8">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Manage Customers</h2>
    <button onclick="document.getElementById('customerModal').classList.remove('hidden')" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition flex items-center">
      <i class="fas fa-plus mr-2"></i> Add New Customer
    </button>
  </div>

  <div class="overflow-x-auto rounded-lg shadow">
    <table class="min-w-full bg-white border border-gray-200">
      <thead>
        <tr class="bg-gray-100 text-gray-700">
          <th class="py-3 px-5 border-b">#</th>
          <th class="py-3 px-5 border-b text-left">Name</th>
          <th class="py-3 px-5 border-b text-left">Email</th>
          <th class="py-3 px-5 border-b text-left">Phone</th>
          <th class="py-3 px-5 border-b text-left">Status</th>
          <th class="py-3 px-5 border-b text-left">Registered</th>
          <th class="py-3 px-5 border-b text-left">Actions</th>
        </tr>
      </thead>
      <tbody class="text-gray-600">
        <?php foreach ($customers as $customer): ?>
        <tr class="hover:bg-gray-50">
          <td class="py-3 px-5 border-b"><?= htmlspecialchars($customer['id']) ?></td>
          <td class="py-3 px-5 border-b"><?= htmlspecialchars($customer['name']) ?></td>
          <td class="py-3 px-5 border-b"><?= htmlspecialchars($customer['email']) ?></td>
          <td class="py-3 px-5 border-b"><?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></td>
          <td class="py-3 px-5 border-b">
            <span class="bg-<?= $customer['status'] === 'active' ? 'green' : 'red' ?>-100 text-<?= $customer['status'] === 'active' ? 'green' : 'red' ?>-800 text-xs px-2 py-1 rounded-full">
              <?= ucfirst($customer['status']) ?>
            </span>
          </td>
          <td class="py-3 px-5 border-b"><?= date('M d, Y', strtotime($customer['created_at'])) ?></td>
          <td class="py-3 px-5 border-b space-x-2">
            <a href="?page=customers&edit_id=<?= $customer['id'] ?>" class="px-3 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500 transition">
              <i class="fas fa-edit"></i>
            </a>
            <a href="?page=customers&delete_id=<?= $customer['id'] ?>" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition" onclick="return confirm('Are you sure you want to delete this customer?')">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Customer Modal (Add/Edit) -->
<div id="customerModal" class="<?= !isset($_GET['edit_id']) ? 'hidden' : '' ?> fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
    <div class="flex justify-between items-center border-b p-4">
      <h3 class="text-lg font-semibold"><?= isset($_GET['edit_id']) ? 'Edit' : 'Add' ?> Customer</h3>
      <button onclick="window.location.href='?page=customers'" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <form method="POST" class="p-4">
      <?php if (isset($_GET['edit_id'])): ?>
      <input type="hidden" name="id" value="<?= $editCustomer['id'] ?>">
      <?php endif; ?>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Full Name*</label>
        <input type="text" name="name" value="<?= $editCustomer['name'] ?? '' ?>" required class="w-full px-3 py-2 border rounded">
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Email*</label>
        <input type="email" name="email" value="<?= $editCustomer['email'] ?? '' ?>" required class="w-full px-3 py-2 border rounded">
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2"><?= isset($_GET['edit_id']) ? 'New ' : '' ?>Password<?= !isset($_GET['edit_id']) ? '*' : '' ?></label>
        <input type="password" name="password" <?= !isset($_GET['edit_id']) ? 'required' : '' ?> class="w-full px-3 py-2 border rounded">
        <?php if (isset($_GET['edit_id'])): ?>
        <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password</p>
        <?php endif; ?>
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Phone</label>
        <input type="tel" name="phone" value="<?= $editCustomer['phone'] ?? '' ?>" class="w-full px-3 py-2 border rounded">
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Address</label>
        <textarea name="address" class="w-full px-3 py-2 border rounded"><?= $editCustomer['address'] ?? '' ?></textarea>
      </div>
      
      <?php if (isset($_GET['edit_id'])): ?>
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Status*</label>
        <select name="status" required class="w-full px-3 py-2 border rounded">
          <option value="active" <?= isset($editCustomer) && $editCustomer['status'] == 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= isset($editCustomer) && $editCustomer['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <?php endif; ?>
      
      <div class="flex justify-end space-x-2 pt-4 border-t">
        <button type="button" onclick="window.location.href='?page=customers'" class="px-4 py-2 border rounded hover:bg-gray-100">
          Cancel
        </button>
        <button type="submit" name="<?= isset($_GET['edit_id']) ? 'update_customer' : 'add_customer' ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
          <?= isset($_GET['edit_id']) ? 'Update' : 'Save' ?> Customer
        </button>
      </div>
    </form>
  </div>
</div>

<?php if (isset($_GET['edit_id'])): ?>
<script>
  // Auto-open modal when edit_id is present
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('customerModal').classList.remove('hidden');
  });
</script>
<?php endif; ?>