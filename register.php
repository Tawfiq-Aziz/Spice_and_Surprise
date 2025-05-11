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
                } elseif ($role === "admin") {
                    // Insert into admin table
                    $admin_stmt = $conn->prepare("INSERT INTO admin (u_id, access_lvl) VALUES (?, 'full')");
                    
                    if (!$admin_stmt) {
                        die("Prepare failed for Admin: " . $conn->error);
                    }
                
                    $admin_stmt->bind_param("i", $user_id);
                    if (!$admin_stmt->execute()) {
                        $error = "Admin registration failed: " . $conn->error;
                    }
                    $admin_stmt->close();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Spice & Surprise</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1500&q=80') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            font-family: 'Montserrat', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(30, 30, 30, 0.55);
            z-index: 0;
        }
        .register-container {
            position: relative;
            z-index: 1;
            background: rgba(40, 40, 40, 0.65);
            box-shadow: 0 8px 32px rgba(0,0,0,0.35);
            border-radius: 28px;
            padding: 3rem 2.5rem 2.5rem 2.5rem;
            max-width: 430px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            backdrop-filter: blur(16px);
            border: 1.5px solid rgba(255,255,255,0.13);
            animation: fadeIn 1s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.7rem;
            color: #fff;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            letter-spacing: 1px;
            text-shadow: 0 2px 12px rgba(76,175,80,0.18);
        }
        .register-logo i {
            color: #4CAF50;
            font-size: 2.5rem;
            filter: drop-shadow(0 2px 8px #4CAF50aa);
        }
        .register-subtitle {
            color: #e0e0e0;
            font-size: 1.1rem;
            margin-bottom: 2.2rem;
            text-align: center;
            text-shadow: 0 1px 8px rgba(0,0,0,0.13);
        }
        .register-form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .form-group label {
            color: #b3b3b3;
            font-size: 1rem;
            font-weight: 500;
        }
        .form-group input, .form-group select {
            padding: 0.9rem 1.1rem;
            border-radius: 12px;
            border: 1.5px solid #4CAF50;
            background: rgba(255,255,255,0.10);
            color: #fff;
            font-size: 1rem;
            font-family: inherit;
            outline: none;
            transition: border 0.2s, background 0.2s;
        }
        .form-group input:focus, .form-group select:focus {
            border: 1.5px solid #81c784;
            background: rgba(255,255,255,0.18);
        }
        .form-group select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: rgba(255,255,255,0.10) url('data:image/svg+xml;utf8,<svg fill="white" height="18" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 1.1rem center/1.2em;
            color: #fff;
            padding-right: 2.5rem;
            border: 1.5px solid #4CAF50;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            outline: none;
            transition: border 0.2s, background 0.2s;
            cursor: pointer;
        }
        .form-group select:focus {
            border: 1.5px solid #81c784;
            background-color: rgba(255,255,255,0.18);
        }
        .form-group option {
            color: #222;
            background: #fff;
        }
        .btn-register {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(90deg, #4CAF50 0%, #388E3C 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
            box-shadow: 0 4px 16px rgba(76, 175, 80, 0.18);
            transition: background 0.3s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
        }
        .btn-register:hover {
            background: linear-gradient(90deg, #388E3C 0%, #4CAF50 100%);
            transform: translateY(-2px) scale(1.04);
        }
        .footer-links {
            margin-top: 2rem;
            color: #b3b3b3;
            font-size: 1rem;
            text-align: center;
        }
        .footer-links a {
            color: #4CAF50;
            font-weight: 600;
            text-decoration: none;
            margin-left: 0.3rem;
            transition: color 0.2s;
        }
        .footer-links a:hover {
            color: #81c784;
        }
        .alert-error {
            background: rgba(255, 0, 0, 0.13);
            color: #ffbaba;
            padding: 0.8rem 1rem;
            border: 1.5px solid #ff6b6b;
            border-radius: 8px;
            margin-bottom: 1.2rem;
            text-align: center;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 1.5rem 0.7rem;
            }
            .register-logo {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-logo">
            <i class="fas fa-pepper-hot"></i> Spice & Surprise
        </div>
        <div class="register-subtitle">Create your account and join the food adventure!</div>
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="register-form">
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
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" class="btn-register"><i class="fas fa-user-plus"></i> Register</button>
        </form>
        <div class="footer-links">
            Already have an account? <a href="index.php">Login here</a>
        </div>
    </div>
    <?php if (!empty($success)): ?>
        <div id="registerSuccessModal" style="display:flex; align-items:center; justify-content:center; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(30,30,30,0.65); z-index:9999;">
            <div style="background:rgba(44,62,80,0.98); border-radius:18px; max-width:400px; width:90%; margin:auto; box-shadow:0 8px 32px rgba(76,175,80,0.18); padding:2.2rem 1.5rem; position:relative; text-align:center;">
                <h2 style="color:#4CAF50; margin-bottom:1.2rem;">Registration Successful!</h2>
                <p style="color:#fff; font-size:1.1rem; margin-bottom:1.5rem;">Your account has been created. You can now log in and start your food adventure!</p>
                <a href="index.php" style="display:inline-block; background:linear-gradient(90deg,#4CAF50,#388E3C); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:1.1rem; padding:0.8rem 2rem; box-shadow:0 4px 16px rgba(76,175,80,0.18); text-decoration:none; transition:background 0.3s,transform 0.2s;">Go to Login</a>
            </div>
        </div>
        <script>
            // Prevent form resubmission on reload
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        </script>
    <?php endif; ?>
</body>
</html>

