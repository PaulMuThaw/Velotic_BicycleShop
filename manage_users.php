<?php
require_once 'db_conn.php';
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: ?page=users");
    exit();
}

// Handle user creation and updates
if (isset($_POST['update_user'])) {
    $id = $_POST['id'] ?? null; // Will be null for new users
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($email) || empty($role)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ?page=users");
        exit();
    }

    // Handle new user creation
    if (empty($id)) {
        if (empty($password)) {
            $_SESSION['error'] = "Password is required for new users";
            header("Location: ?page=users");
            exit();
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, role, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $role, $hashed_password]);
        
        $_SESSION['success'] = "User created successfully";
        header("Location: ?page=users");
        exit();
    }
    // Handle existing user update
    else {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $email, $role, $hashed_password, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$username, $email, $role, $id]);
        }
        
        $_SESSION['success'] = "User updated successfully";
        header("Location: ?page=users");
        exit();
    }
}

// Fetch all users
$users = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get user details for editing
$editUser = null;
if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$editUser) {
        $_SESSION['error'] = "User not found";
        header("Location: ?page=users");
        exit();
    }
}
?>

<div class="p-8">
  <!-- Display success/error messages -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>
  
  <?php if (isset($_SESSION['error'])): ?>
    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
      <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Manage Users</h2>
    <button onclick="document.getElementById('userModal').classList.remove('hidden')" 
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition flex items-center">
      <i class="fas fa-plus mr-2"></i> Add New User
    </button>
  </div>

  <div class="overflow-x-auto rounded-lg shadow">
    <table class="min-w-full bg-white border border-gray-200">
      <thead>
        <tr class="bg-gray-100 text-gray-700">
          <th class="py-3 px-5 border-b">#</th>
          <th class="py-3 px-5 border-b text-left">Username</th>
          <th class="py-3 px-5 border-b text-left">Email</th>
          <th class="py-3 px-5 border-b text-left">Role</th>
          <th class="py-3 px-5 border-b text-left">Joined</th>
          <th class="py-3 px-5 border-b text-left">Actions</th>
        </tr>
      </thead>
      <tbody class="text-gray-600">
        <?php foreach ($users as $user): ?>
        <tr class="hover:bg-gray-50">
          <td class="py-3 px-5 border-b"><?= htmlspecialchars($user['id']) ?></td>
          <td class="py-3 px-5 border-b"><?= htmlspecialchars($user['username']) ?></td>
          <td class="py-3 px-5 border-b"><?= htmlspecialchars($user['email']) ?></td>
          <td class="py-3 px-5 border-b">
            <span class="bg-<?= $user['role'] === 'admin' ? 'purple' : 'blue' ?>-100 text-<?= $user['role'] === 'admin' ? 'purple' : 'blue' ?>-800 text-xs px-2 py-1 rounded-full">
              <?= ucfirst($user['role']) ?>
            </span>
          </td>
          <td class="py-3 px-5 border-b"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
          <td class="py-3 px-5 border-b space-x-2">
            <a href="?page=users&edit_id=<?= $user['id'] ?>" class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition">
              <i class="fas fa-edit"></i>
            </a>
            <a href="?page=users&delete_id=<?= $user['id'] ?>" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition" onclick="return confirm('Are you sure?')">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- User Modal (Add/Edit) -->
<div id="userModal" class="<?= (isset($_GET['edit_id']) || isset($_GET['add_user'])) ? '' : 'hidden' ?> fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
    <div class="flex justify-between items-center border-b p-4">
      <h3 class="text-lg font-semibold"><?= isset($_GET['edit_id']) ? 'Edit' : 'Add' ?> User</h3>
      <button onclick="window.location.href='?page=users'" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <form method="POST" class="p-4">
      <?php if (isset($_GET['edit_id']) && $editUser): ?>
      <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
      <?php endif; ?>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Username*</label>
        <input type="text" name="username" value="<?= $editUser['username'] ?? '' ?>" required class="w-full px-3 py-2 border rounded">
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Email*</label>
        <input type="email" name="email" value="<?= $editUser['email'] ?? '' ?>" required class="w-full px-3 py-2 border rounded">
      </div>
      
      <?php if (isset($_GET['edit_id']) && $editUser): ?>
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">New Password (leave blank to keep current)</label>
        <input type="password" name="password" class="w-full px-3 py-2 border rounded">
      </div>
      <?php else: ?>
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Password*</label>
        <input type="password" name="password" required class="w-full px-3 py-2 border rounded">
      </div>
      <?php endif; ?>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Role*</label>
        <select name="role" required class="w-full px-3 py-2 border rounded">
          <option value="user" <?= isset($editUser) && $editUser['role'] == 'user' ? 'selected' : '' ?>>User</option>
          <option value="admin" <?= isset($editUser) && $editUser['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
      </div>
      
      <div class="flex justify-end space-x-2 pt-4 border-t">
        <button type="button" onclick="window.location.href='?page=users'" class="px-4 py-2 border rounded hover:bg-gray-100">
          Cancel
        </button>
        <button type="submit" name="update_user" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
          <?= isset($_GET['edit_id']) ? 'Update' : 'Add' ?> User
        </button>
      </div>
    </form>
  </div>
</div>

<?php if (isset($_GET['edit_id']) || isset($_GET['add_user'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('userModal').classList.remove('hidden');
  });
</script>
<?php endif; ?>