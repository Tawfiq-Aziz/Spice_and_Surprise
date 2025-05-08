<?php
require 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize_input($_POST["name"], $conn);
    $email = sanitize_input($_POST["email"], $conn);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $role = $_POST["role"];

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM User WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Modified to include user_type in the INSERT statement
            $insert_stmt = $conn->prepare("INSERT INTO User (name, email, password, join_date, points_earned, achievement_lvl, user_type) 
                                           VALUES (?, ?, ?, NOW(), 0, 'Beginner', ?)");
            $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($insert_stmt->execute()) {
                $user_id = $conn->insert_id;

                if ($role === "vendor") {
                    $license_num = rand(100000, 999999); // generate random license
                    $vendor_stmt = $conn->prepare("INSERT INTO Vendor (user_id, license_num) VALUES (?, ?)");
                    
                    if (!$vendor_stmt) {
                        die("Prepare failed for Vendor: " . $conn->error);
                    }
                
                    $vendor_stmt->bind_param("is", $user_id, $license_num);
                    if (!$vendor_stmt->execute()) {
                        $error = "Vendor registration failed: " . $conn->error;
                    }
                    $vendor_stmt->close();
                } else {
                    $explorer_stmt = $conn->prepare("INSERT INTO Food_Explorer (user_id) VALUES (?)");
                
                    if (!$explorer_stmt) {
                        die("Prepare failed for Food_Explorer: " . $conn->error);
                    }
                
                    $explorer_stmt->bind_param("i", $user_id);
                    if (!$explorer_stmt->execute()) {
                        $error = "Explorer registration failed: " . $conn->error;
                    }
                    $explorer_stmt->close();
                }

                // Only show success if no errors occurred
                if (empty($error)) {
                    $success = "Registration successful! You can now login.";
                }
            } else {
                $error = "Registration failed. Please try again. Error: " . $conn->error;
            }

            $insert_stmt->close();
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Spice & Surprise</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 3rem auto;
            padding: 2rem;
        }

        .auth-form {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .auth-title {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }

        .alert {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .alert-error {
            background-color: #ffdddd;
            color: #d8000c;
        }

        .alert-success {
            background-color: #ddffdd;
            color: #270;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1rem;
            background: #ff4d4d;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            width: 100%;
            cursor: pointer;
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
        }

        .auth-footer a {
            color: #ff4d4d;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-form">
            <div class="auth-title">
                <h1><i class="fas fa-pepper-hot"></i> Spice & Surprise</h1>
                <h2>Create Your Account</h2>
                <p>Join our community of flavor explorers</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success; ?></div>
                <a href="index.php" class="btn">Continue to Login</a>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>

                    <div class="form-group">
                        <label for="role">Register As</label>
                        <select name="role" id="role" required>
                            <option value="food_explorer">Food Explorer</option>
                            <option value="vendor">Vendor</option>
                        </select>
                    </div>

                    <button type="submit" class="btn"><i class="fas fa-user-plus"></i> Create Account</button>
                </form>

                <div class="auth-footer">
                    Already have an account? <a href="index.php">Sign in</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

