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

            // Get user type from the user table
            $role = $user['user_type'] ?? 'food_explorer';

            // Check if vendor
            if ($role === 'vendor') {
                $vendor_check = $conn->prepare("SELECT vendor_id FROM Vendor WHERE user_id = ?");
                $vendor_check->bind_param("i", $user_id);
                $vendor_check->execute();
                $vendor_result = $vendor_check->get_result();
                
                if ($vendor_result->num_rows === 1) {
                    $vendor_data = $vendor_result->fetch_assoc();
                    $_SESSION['vendor_id'] = $vendor_data['vendor_id'];
                }
            }

            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['points'] = $user['points_earned'] ?? 0;
            $_SESSION['role'] = $role;
            $_SESSION['user_type'] = $role;

            // Redirect based on user type
            switch ($role) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'vendor':
                    header("Location: vendor_dashboard.php");
                    break;
                default:
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Spice & Surprise</title>
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
        /* Overlay for readability */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(30, 30, 30, 0.55);
            z-index: 0;
        }
        .login-container {
            position: relative;
            z-index: 1;
            background: rgba(40, 40, 40, 0.65);
            box-shadow: 0 8px 32px rgba(0,0,0,0.35);
            border-radius: 28px;
            padding: 3rem 2.5rem 2.5rem 2.5rem;
            max-width: 400px;
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
        .login-logo {
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
        .login-logo i {
            color: #4CAF50;
            font-size: 2.5rem;
            filter: drop-shadow(0 2px 8px #4CAF50aa);
        }
        .login-subtitle {
            color: #e0e0e0;
            font-size: 1.1rem;
            margin-bottom: 2.2rem;
            text-align: center;
            text-shadow: 0 1px 8px rgba(0,0,0,0.13);
        }
        .login-form {
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
        .form-group input {
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
        .form-group input:focus {
            border: 1.5px solid #81c784;
            background: rgba(255,255,255,0.18);
        }
        .btn-login {
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
        .btn-login:hover {
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
            .login-container {
                padding: 1.5rem 0.7rem;
            }
            .login-logo {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <i class="fas fa-pepper-hot"></i> Spice & Surprise
        </div>
        <div class="login-subtitle">Discover bold flavors and exciting challenges</div>
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="you@example.com">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
        <div class="footer-links">
            New here? <a href="register.php">Create an account</a>
        </div>
    </div>
</body>
</html>
