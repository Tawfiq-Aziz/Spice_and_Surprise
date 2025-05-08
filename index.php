<?php
session_start();
require 'db.php';

// Initialize all variables
$error = '';
$show_debug = isset($_GET['debug']) && $_GET['debug'] == 1;
$debug_output = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST["email"], $conn);
    $password = $_POST["password"];
    
    // First check if user exists
    $stmt = $conn->prepare("SELECT * FROM `user` WHERE email = ?");
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Get user ID - checking multiple possible column names
            $user_id = $user['user_id'] ?? $user['id'] ?? $user['u_id'] ?? null;
            
            if (!$user_id) {
                die("Could not determine user ID");
            }
            
            // Set session variables
            $_SESSION = [
                'user_id' => $user_id,
                'name' => $user['name'],
                'email' => $user['email'],
                'points' => $user['points_earned'] ?? 0
            ];
            
            // DETERMINE USER ROLE
            $role = 'explorer'; // Default role
            
            // Check if user_type column exists
            if (isset($user['user_type'])) {
                $role = strtolower($user['user_type']);
            } 
            // If no user_type column, check role tables
            else {
                // Check if admin
                $admin_check = $conn->prepare("SELECT 1 FROM `Admin` WHERE `user_id` = ?");
                if ($admin_check && $admin_check->bind_param("i", $user_id) && $admin_check->execute()) {
                    if ($admin_check->get_result()->num_rows === 1) {
                        $role = 'admin';
                    }
                    $admin_check->close();
                }
                
                // If not admin, check if vendor
                if ($role !== 'admin') {
                    $vendor_check = $conn->prepare("SELECT 1 FROM `Vendor` WHERE `user_id` = ?");
                    if ($vendor_check && $vendor_check->bind_param("i", $user_id) && $vendor_check->execute()) {
                        if ($vendor_check->get_result()->num_rows === 1) {
                            $role = 'vendor';
                        }
                        $vendor_check->close();
                    }
                }
            }
            
            $_SESSION['role'] = $role;
            
            // DEBUG OUTPUT (if enabled)
            if ($show_debug) {
                echo "<pre>SESSION DATA:\n";
                print_r($_SESSION);
                echo "\nWould redirect to: ";
                switch ($role) {
                    case 'admin': echo "admin_dashboard.php"; break;
                    case 'vendor': echo "vendor_dashboard.php"; break;
                    default: echo "home.php"; break;
                }
                echo "</pre>";
                exit();
            }
            
            // ACTUAL REDIRECT
            switch ($role) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    exit();
                case 'vendor':
                    header("Location: vendor_dashboard.php");
                    exit();
                default:
                    header("Location: home.php");
                    exit();
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Spice & Surprise</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-form">
            <h1><i class="fas fa-pepper-hot"></i> Spice & Surprise</h1>
            <p class="subtitle">Discover bold flavors and exciting challenges</p>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="your@email.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
            
            <div class="footer-links">
                New here? <a href="register.php">Create an account</a>
                | <a href="?debug=1">Debug Mode</a>
            </div>
        </div>
    </div>
</body>
</html>
