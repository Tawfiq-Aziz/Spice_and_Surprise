<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM `user` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $user_id = $user['user_id'] ?? $user['id'] ?? $user['u_id'] ?? null;

            if (!$user_id) {
                die("Could not determine user ID");
            }

            // Default role
            $role = 'food_explorer';

            // Check if vendor
            $vendor_check = $conn->prepare("SELECT vendor_id FROM Vendor WHERE user_id = ?");
            $vendor_check->bind_param("i", $user_id);
            $vendor_check->execute();
            $vendor_result = $vendor_check->get_result();

            if ($vendor_result->num_rows === 1) {
                $role = 'vendor';
                $vendor_data = $vendor_result->fetch_assoc();
                $_SESSION['vendor_id'] = $vendor_data['vendor_id'];
            }

            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['points'] = $user['points_earned'] ?? 0;
            $_SESSION['role'] = $role;

            if ($role === 'vendor') {
                header("Location: vendor_dashboard.php");
            } else {
                header("Location: home.php");
            }
            exit();
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
<html>
<head>
    <title>Login | Spice & Surprise</title>
    <style>
        body {
            background: #fefefe;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }
        .auth-container {
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
            background: linear-gradient(to right, #ffecd2 0%, #fcb69f 100%);
        }
        .auth-form {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        .auth-form h1 {
            margin-bottom: 10px;
            color: #ff6b6b;
        }
        .subtitle {
            font-size: 14px;
            color: #555;
            margin-bottom: 30px;
        }
        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }
        .form-group label {
            font-size: 14px;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-top: 5px;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #ff6b6b;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
        }
        .btn:hover {
            background-color: #e55a5a;
        }
        .footer-links {
            margin-top: 20px;
            font-size: 14px;
        }
        .footer-links a {
            color: #ff6b6b;
            text-decoration: none;
        }
        .alert-error {
            background-color: #ffe0e0;
            color: #a33;
            padding: 10px;
            border: 1px solid #ff6b6b;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-form">
            <h1>Spice & Surprise</h1>
            <p class="subtitle">Discover bold flavors and exciting challenges</p>

            <?php if (!empty($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="you@example.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn">Login</button>
            </form>

            <div class="footer-links">
                New here? <a href="register.php">Create an account</a>
            </div>
        </div>
    </div>
</body>
</html>
