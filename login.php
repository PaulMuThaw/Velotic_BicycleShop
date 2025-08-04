<?php

require_once "db_conn.php";
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($conn)) {
    die("Database connection not established");
}


// Initialize variables
$username = '';
$error = '';

// Check if user is already logged in
if (isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true) {
    // Redirect based on role
    redirectBasedOnRole($_SESSION['role']);
}

// Check if login form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            // Prepare SQL with parameterized query
            $sql = "SELECT id, username, password, role, name, email FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$username]);
            $userInfo = $stmt->fetch();

            if (!$userInfo) {
                // User not found
                $error = "Invalid username or password.";
                error_log("Failed login attempt for username: $username");
            } else {
                // Verify password
                if (password_verify($password, $userInfo['password'])) {
                    // Password is correct, set session variables
                    $_SESSION['user_id'] = $userInfo['id'];
                    $_SESSION['username'] = $userInfo['username'];
                    $_SESSION['role'] = $userInfo['role'];
                    $_SESSION['name'] = $userInfo['name'];
                    $_SESSION['email'] = $userInfo['email'];
                    $_SESSION['logged_in'] = true;
                    
                    // Regenerate session ID
                    session_regenerate_id(true);
                    
                    // Redirect based on role
                    redirectBasedOnRole($userInfo['role']);
                } else {
                    // Password is incorrect
                    $error = "Invalid username or password.";
                    error_log("Failed login attempt for username: $username");
                }
            }
        } catch (PDOException $e) {
            error_log("Database error during login: " . $e->getMessage());
            $error = "A system error occurred. Please try again later.";
        }
    }
}

function redirectBasedOnRole($role) {
    switch ($role) {
        case 'admin':
            header("Location: index.php");
            break;
    }
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Bicycle Shop</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Bicycle Shop</h1>
                <p class="text-gray-600 mt-2">Please login to continue</p>
            </div>
            
            <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-gray-700 mb-2">Username</label>
                    <input type="text" id="username" name="username" required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="<?php echo htmlspecialchars($username); ?>"
                           placeholder="Enter your username">
                </div>
                
                <div>
                    <label for="password" class="block text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter your password">
                </div>
                
                <button type="submit" name="login"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>
            </form>
        </div>
    </div>
</body>
</html>
