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
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        background: url('https://images.unsplash.com/photo-1615234590668-43416e63bf2c?q=80&w=2080&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center fixed;
        background-size: cover;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .auth-container {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 15px;
        padding: 40px;
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        text-align: center;
        width: 100%;
        max-width: 400px;
        color: #ffffff;
    }

    .logo-title {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        gap: 10px;
        margin-bottom: 10px;
        color: #ffaaaa;
    }

    .logo-icon {
        width: 30px;
        height: 30px;
    }

    .subtitle {
        font-size: 14px;
        color:rgb(50, 148, 194);
        margin-bottom: 30px;
    }

    .form-group {
        text-align: left;
        margin-bottom: 20px;
    }

    .form-group label {
        font-size: 14px;
        color: #f5f5f5;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
        margin-top: 5px;
        outline: none;
        background-color: rgba(255, 255, 255, 0.8);
    }

    .btn {
        width: 100%;
        padding: 12px;
        background-color: #ff6b6b;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .btn:hover {
        background-color: #ff4c4c;
    }

    .footer-links {
        margin-top: 20px;
        font-size: 14px;
        color: #fff;
    }

    .footer-links a {
        color: #ffdada;
        text-decoration: none;
    }

    .alert-error {
        background-color: rgba(255, 0, 0, 0.2);
        color: #ffbaba;
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
