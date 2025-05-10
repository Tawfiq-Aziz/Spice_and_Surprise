<?php
session_start();
include("db.php");

// Redirect if not logged in
if (!isset($_SESSION['vendor_id'])) {
    header("Location: index.php");
    exit;
}

$vendor_id = $_SESSION['vendor_id'];
$message = "";

// Add shop info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_shop'])) {
    $shop_name = $_POST['shop_name'];
    $license_no = $_POST['license_no'];
    $menu = $_POST['menu'];
    $location = $_POST['location'];
    $image = $_FILES['shop_image']['name'];
    $image_tmp = $_FILES['shop_image']['tmp_name'];

    if (!file_exists("uploads")) {
        mkdir("uploads", 0777, true);
    }

    $image_path = "uploads/" . basename($image);
    if (move_uploaded_file($image_tmp, $image_path)) {
        $stmt = $conn->prepare("INSERT INTO Shop (vendor_id, shop_name, license_no, menu, location, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $vendor_id, $shop_name, $license_no, $menu, $location, $image);
        if ($stmt->execute()) {
            $message = "✅ Shop info added successfully!";
        } else {
            $message = "❌ Error: " . $stmt->error;
        }
    } else {
        $message = "❌ Failed to upload image.";
    }
}

// Delete shop
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Shop WHERE id = ? AND vendor_id = ?");
    $stmt->bind_param("ii", $id, $vendor_id);
    $stmt->execute();
    $message = "🗑️ Shop deleted!";
}

// Fetch all shops of this vendor
$stmt = $conn->prepare("SELECT * FROM Shop WHERE vendor_id = ?");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$shops = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vendor Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 30px 20px;
            background: url('https://images.unsplash.com/photo-1448375240586-882707db888b?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center fixed;
            background-size: cover;
            color: #f0fff0;
        }

        h2, h3 {
            text-align: center;
            color: #dfffdc;
            margin-bottom: 25px;
        }

        .message {
            padding: 12px 20px;
            background-color: rgba(34, 139, 34, 0.8);
            border-radius: 12px;
            text-align: center;
            max-width: 500px;
            margin: 20px auto;
            font-weight: bold;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.3);
        }

        form {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 30px;
            border-radius: 18px;
            max-width: 550px;
            margin: 0 auto 40px auto;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        input, textarea, button {
            width: 100%;
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 10px;
            border: none;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
        }

        textarea {
            resize: vertical;
        }

        input[type="file"] {
            background-color: #f8fff8;
        }

        button {
            background-color: #4caf50;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #388e3c;
        }

        .shop-box {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            color: #ffffff;
            padding: 20px;
            margin: 20px auto;
            border-left: 6px solid #76c893;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.25);
            max-width: 600px;
        }

        .shop-box img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-top: 15px;
        }

        .delete-link {
            color: #ffcccc;
            font-weight: bold;
            text-decoration: none;
        }

        .delete-link:hover {
            text-decoration: underline;
        }

        hr {
            border: none;
            height: 1px;
            background-color: rgba(255, 255, 255, 0.3);
            margin: 40px auto;
            max-width: 600px;
        }

        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }
        }
    </style>
</head>
<body>

<h2>🌲 Vendor Dashboard</h2>

<?php if ($message): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="shop_name" placeholder="Shop Name" required>
    <input type="text" name="license_no" placeholder="License Number" required>
    <textarea name="menu" placeholder="Menu Details" required></textarea>
    <input type="text" name="location" placeholder="Location" required>
    <input type="file" name="shop_image" required>
    <button type="submit" name="add_shop">🌿 Post Shop Info</button>
</form>

<hr>

<h3>🛖 Your Shop(s)</h3>
<?php while ($shop = $shops->fetch_assoc()): ?>
    <div class="shop-box">
        <strong><?= htmlspecialchars($shop['shop_name']) ?></strong><br>
        License: <?= htmlspecialchars($shop['license_no']) ?><br>
        Menu: <?= nl2br(htmlspecialchars($shop['menu'])) ?><br>
        Location: <?= htmlspecialchars($shop['location']) ?><br>
        <img src="uploads/<?= htmlspecialchars($shop['image']) ?>" alt="Shop Image"><br><br>
        <a class="delete-link" href="?delete=<?= $shop['id'] ?>" onclick="return confirm('Are you sure you want to delete this shop?')">🗑️ Delete</a>
    </div>
<?php endwhile; ?>

</body>
</html>

