<?php
require_once 'db_conn.php';

// Handle feedback deletion
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: ?page=feedback");
    exit();
}

// Handle feedback status update
if (isset($_POST['update_feedback_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE feedback SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    header("Location: ?page=feedback");
    exit();
}

// Fetch all feedback with product names and user info
$feedback = $conn->query("
    SELECT f.*, 
           p.name as product_name,
           u.username as user_name
    FROM feedback f
    LEFT JOIN products p ON f.product_id = p.id
    LEFT JOIN users u ON f.user_id = u.id
    ORDER BY f.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get feedback details for editing
$editFeedback = null;
if (isset($_GET['edit_id'])) {
    $editFeedback = $conn->query("
        SELECT * FROM feedback 
        WHERE id = " . $_GET['edit_id']
    )->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="p-8">
  <div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Customer Feedback</h2>
    <div class="flex space-x-2">
      <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition flex items-center">
        <i class="fas fa-filter mr-2"></i> Filter
      </button>
      <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition flex items-center">
        <i class="fas fa-download mr-2"></i> Export
      </button>
    </div>
  </div>

  <div class="overflow-x-auto rounded-lg shadow">
    <table class="min-w-full bg-white border border-gray-200">
      <thead>
        <tr class="bg-gray-100 text-gray-700">
          <th class="py-3 px-5 border-b">#</th>
          <th class="py-3 px-5 border-b text-left">User</th>
          <th class="py-3 px-5 border-b text-left">Product</th>
          <th class="py-3 px-5 border-b text-left">Feedback</th>
          <th class="py-3 px-5 border-b text-left">Rating</th>
          <th class="py-3 px-5 border-b text-left">Status</th>
          <th class="py-3 px-5 border-b text-left">Date</th>
          <th class="py-3 px-5 border-b text-left">Actions</th>
        </tr>
      </thead>
      <tbody class="text-gray-600">
        <?php foreach ($feedback as $item): ?>
        <tr class="hover:bg-gray-50">
          <td class="py-3 px-5 border-b"><?= htmlspecialchars($item['id']) ?></td>
          <td class="py-3 px-5 border-b">
            <?= htmlspecialchars($item['user_name'] ?? $item['email']) ?>
          </td>
          <td class="py-3 px-5 border-b"><?= htmlspecialchars($item['product_name'] ?? 'N/A') ?></td>
          <td class="py-3 px-5 border-b">
            <div class="max-w-xs truncate"><?= htmlspecialchars($item['message']) ?></div>
          </td>
          <td class="py-3 px-5 border-b">
            <div class="flex text-yellow-400">
              <?php
              $rating = $item['rating'];
              for ($i = 1; $i <= 5; $i++) {
                  if ($i <= $rating) {
                      echo '<i class="fas fa-star"></i>';
                  } elseif ($i - 0.5 <= $rating) {
                      echo '<i class="fas fa-star-half-alt"></i>';
                  } else {
                      echo '<i class="far fa-star"></i>';
                  }
              }
              ?>
            </div>
          </td>
          <td class="py-3 px-5 border-b">
            <span class="bg-<?= $item['status'] === 'approved' ? 'green' : ($item['status'] === 'pending' ? 'yellow' : 'red') ?>-100 text-<?= $item['status'] === 'approved' ? 'green' : ($item['status'] === 'pending' ? 'yellow' : 'red') ?>-800 text-xs px-2 py-1 rounded-full">
              <?= ucfirst($item['status']) ?>
            </span>
          </td>
          <td class="py-3 px-5 border-b"><?= date('M d, Y', strtotime($item['created_at'])) ?></td>
          <td class="py-3 px-5 border-b space-x-2">
            <a href="?page=feedback&edit_id=<?= $item['id'] ?>" class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition">
              <i class="fas fa-edit"></i>
            </a>
            <a href="?page=feedback&delete_id=<?= $item['id'] ?>" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition" onclick="return confirm('Are you sure?')">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="flex justify-between items-center mt-6">
    <div class="text-gray-600">
      Showing <?= count($feedback) ?> entries
    </div>
  </div>
</div>

<!-- Feedback Edit Modal -->
<?php if (isset($_GET['edit_id'])): ?>
<div id="feedbackModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
    <div class="flex justify-between items-center border-b p-4">
      <h3 class="text-lg font-semibold">Update Feedback Status</h3>
      <button onclick="window.location.href='?page=feedback'" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <form method="POST" class="p-4">
      <input type="hidden" name="id" value="<?= $editFeedback['id'] ?>">
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Feedback</label>
        <div class="p-3 bg-gray-50 rounded"><?= htmlspecialchars($editFeedback['message']) ?></div>
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Current Rating</label>
        <div class="flex text-yellow-400">
          <?php
          $rating = $editFeedback['rating'];
          for ($i = 1; $i <= 5; $i++) {
              echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
          }
          ?>
        </div>
      </div>
      
      <div class="mb-4">
        <label class="block text-gray-700 mb-2">Status*</label>
        <select name="status" required class="w-full px-3 py-2 border rounded">
          <option value="pending" <?= $editFeedback['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="approved" <?= $editFeedback['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="rejected" <?= $editFeedback['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </div>
      
      <div class="flex justify-end space-x-2 pt-4 border-t">
        <button type="button" onclick="window.location.href='?page=feedback'" class="px-4 py-2 border rounded hover:bg-gray-100">
          Cancel
        </button>
        <button type="submit" name="update_feedback_status" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
          Update Status
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('feedbackModal').classList.remove('hidden');
  });
</script>
<?php endif; ?>