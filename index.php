<?php
session_start();
require 'db.php';

$error = '';

// Let's first examine the structure of the user table to see exactly what columns exist
function debugTableStructure($conn) {
    $debug_info = "";
    
    // Show table structure
    $result = $conn->query("DESCRIBE user");
    if ($result) {
        $debug_info .= "<h3>User Table Structure:</h3><ul>";
        while($row = $result->fetch_assoc()) {
            $debug_info .= "<li>Column: <strong>{$row['Field']}</strong>, Type: {$row['Type']}, Key: {$row['Key']}</li>";
        }
        $debug_info .= "</ul>";
    } else {
        $debug_info .= "<p>Error fetching table structure: " . $conn->error . "</p>";
        
        // Let's also try with backticks to handle special table names
        $result = $conn->query("DESCRIBE `user`");
        if ($result) {
            $debug_info .= "<h3>User Table Structure (with backticks):</h3><ul>";
            while($row = $result->fetch_assoc()) {
                $debug_info .= "<li>Column: <strong>{$row['Field']}</strong>, Type: {$row['Type']}, Key: {$row['Key']}</li>";
            }
            $debug_info .= "</ul>";
        } else {
            $debug_info .= "<p>Error fetching table structure with backticks: " . $conn->error . "</p>";
        }
    }
    
    // Get all tables in database
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        $debug_info .= "<h3>All Tables in Database:</h3><ul>";
        while($row = $result->fetch_row()) {
            $debug_info .= "<li>{$row[0]}</li>";
        }
        $debug_info .= "</ul>";
    } else {
        $debug_info .= "<p>Error fetching tables: " . $conn->error . "</p>";
    }
    
    return $debug_info;
}

// Only show debug info if requested
$show_debug = isset($_GET['debug']) && $_GET['debug'] == 1;
$debug_output = "";

if ($show_debug) {
    $debug_output = debugTableStructure($conn);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST["email"], $conn);
    $password = $_POST["password"];
    
    // Try a query that doesn't depend on column names first to see if the row exists
    $check_stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    if ($check_stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }
    
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 1) {
        $user = $check_result->fetch_assoc();
        
        // Let's see the actual column names (in debug mode)
        if ($show_debug) {
            $debug_output .= "<h3>User Data from Database:</h3><ul>";
            foreach ($user as $key => $value) {
                $debug_output .= "<li><strong>$key</strong>: " . (($key === 'password') ? '[HIDDEN]' : $value) . "</li>";
            }
            $debug_output .= "</ul>";
        }
        
        // Now use the actual column name for ID based on what we retrieved
        $id_column = null;
        // Check for common ID column names
        foreach (['user_id', 'id', 'userid', 'uid', 'u_id'] as $possible_id) {
            if (isset($user[$possible_id])) {
                $id_column = $possible_id;
                break;
            }
        }
        
        // Verify the password
        if (password_verify($password, $user['password'])) {
            if ($id_column) {
                $_SESSION['user_id'] = $user[$id_column];  // Use the discovered ID column
                if ($show_debug) {
                    $debug_output .= "<p>Using <strong>$id_column</strong> as ID column with value: {$user[$id_column]}</p>";
                }
            } else {
                die("Could not determine ID column in user table!");
            }
            
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['points'] = $user['points_earned'];

            // Detect role - this part might also need fixing if column names differ
            $role = '';
            $user_id = $user[$id_column];  // Use the discovered ID column

            // For debug, let's try different role detection
            if ($show_debug) {
                // Let's also see what role information exists in the database
                $debug_output .= "<h3>Role Detection:</h3>";
                
                // Check if user_type column exists and use it directly
                if (isset($user['user_type'])) {
                    $debug_output .= "<p>user_type column exists with value: {$user['user_type']}</p>";
                    $role = strtolower($user['user_type']);
                } else {
                    $debug_output .= "<p>user_type column not found, checking role tables</p>";
                
                    // Check Admin
                    $role_check = $conn->prepare("SELECT * FROM Admin WHERE u_id = ? OR user_id = ?");
                    if ($role_check === false) {
                        $debug_output .= "<p>Admin table query error: " . $conn->error . "</p>";
                    } else {
                        $role_check->bind_param("ii", $user_id, $user_id);
                        $role_check->execute();
                        $role_result = $role_check->get_result();
                        $debug_output .= "<p>Admin check rows: " . $role_result->num_rows . "</p>";
                        if ($role_result->num_rows === 1) {
                            $role = 'admin';
                        }
                        $role_check->close();
                    }
                    
                    // Check Vendor if not admin
                    if (!$role) {
                        $role_check = $conn->prepare("SELECT * FROM Vendor WHERE user_id = ?");
                        if ($role_check === false) {
                            $debug_output .= "<p>Vendor table query error: " . $conn->error . "</p>";
                        } else {
                            $role_check->bind_param("i", $user_id);
                            $role_check->execute();
                            $role_result = $role_check->get_result();
                            $debug_output .= "<p>Vendor check rows: " . $role_result->num_rows . "</p>";
                            if ($role_result->num_rows === 1) {
                                $role = 'vendor';
                            }
                            $role_check->close();
                        }
                    }
                    
                    // Default to Food Explorer if not admin or vendor
                    if (!$role) {
                        $role = 'explorer';
                    }
                }
                
                $debug_output .= "<p>Final determined role: <strong>$role</strong></p>";
            } else {
                // Normal role detection
                // Try using user_type column if it exists
                if (isset($user['user_type'])) {
                    $role = strtolower($user['user_type']);
                } else {
                    // Check Admin - notice we're checking both column names
                    $role_check = $conn->prepare("SELECT * FROM Admin WHERE u_id = ?");
                    if ($role_check === false) {
                        // Try alternate column name
                        $role_check = $conn->prepare("SELECT * FROM Admin WHERE user_id = ?");
                        if ($role_check === false) {
                            die('MySQL prepare error for Admin check: ' . $conn->error);
                        }
                    }
                    $role_check->bind_param("i", $user_id);
                    $role_check->execute();
                    $role_result = $role_check->get_result();
                    if ($role_result->num_rows === 1) {
                        $role = 'admin';
                    } else {
                        // Check Vendor
                        $role_check = $conn->prepare("SELECT * FROM Vendor WHERE user_id = ?");
                        if ($role_check === false) {
                            die('MySQL prepare error for Vendor check: ' . $conn->error);
                        }
                        $role_check->bind_param("i", $user_id);
                        $role_check->execute();
                        $role_result = $role_check->get_result();
                        if ($role_result->num_rows === 1) {
                            $role = 'vendor';
                        } else {
                            // Default to Food Explorer
                            $role = 'explorer';
                        }
                    }
                    if (isset($role_check)) {
                        $role_check->close();
                    }
                }
            }
            
            $_SESSION['role'] = $role;

            // If in debug mode, don't redirect
            if ($show_debug) {
                echo "<h2>Login Successful!</h2>";
                echo "<p>Would redirect to: ";
                switch ($role) {
                    case 'admin':
                        echo "admin_dashboard.php";
                        break;
                    case 'vendor':
                        echo "vendor_dashboard.php";
                        break;
                    case 'explorer':
                    default:
                        echo "home.php";
                        break;
                }
                echo "</p>";
                echo $debug_output;
                exit();
            }

            // Redirect based on role
            switch ($role) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'vendor':
                    header("Location: vendor_dashboard.php");
                    break;
                case 'explorer':
                default:
                    header("Location: home.php");
                    break;
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }

    $check_stmt->close();
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
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($show_debug && !empty($debug_output)): ?>
                <div class="debug-info" style="background: #f8f9fa; padding: 15px; border: 1px solid #ddd; margin-bottom: 20px; font-size: 14px; overflow-x: auto;">
                    <h3>Debug Information</h3>
                    <?php echo $debug_output; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo $show_debug ? '?debug=1' : ''; ?>">
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
                <?php if (!$show_debug): ?>
                    | <a href="?debug=1">Debug Mode</a>
                <?php else: ?>
                    | <a href="index.php">Normal Mode</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>