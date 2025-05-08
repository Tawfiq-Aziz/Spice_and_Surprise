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
    <style>
        body {
            font-family: Arial;
            margin: 30px;
            background-color: #f4f4f4;
        }
        h2, h3 {
            color: #333;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            background-color: #e0ffe0;
            border: 1px solid #00aa00;
            color: #006600;
            width: fit-content;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }
        input, textarea, button {
            display: block;
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #aaa;
        }
        .shop-box {
            background-color: white;
            padding: 15px;
            margin: 10px 0;
            border-left: 5px solid #00aaff;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .shop-box img {
            max-width: 100%;
            height: auto;
        }
        .delete-link {
            color: red;
            text-decoration: none;
        }
    </style>
</head>
<body>

<h2>Vendor Dashboard</h2>

<?php if ($message): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="shop_name" placeholder="Shop Name" required>
    <input type="text" name="license_no" placeholder="License Number" required>
    <textarea name="menu" placeholder="Menu Details" required></textarea>
    <input type="text" name="location" placeholder="Location" required>
    <input type="file" name="shop_image" required>
    <button type="submit" name="add_shop">Post Shop Info</button>
</form>

<hr>

<h3>Your Shop(s)</h3>
<?php while ($shop = $shops->fetch_assoc()): ?>
    <div class="shop-box">
        <strong><?= htmlspecialchars($shop['shop_name']) ?></strong><br>
        License: <?= htmlspecialchars($shop['license_no']) ?><br>
        Menu: <?= nl2br(htmlspecialchars($shop['menu'])) ?><br>
        Location: <?= htmlspecialchars($shop['location']) ?><br>
        <img src="uploads/<?= htmlspecialchars($shop['image']) ?>" width="200"><br><br>
        <a class="delete-link" href="?delete=<?= $shop['id'] ?>" onclick="return confirm('Are you sure you want to delete this shop?')">Delete</a>
    </div>
<?php endwhile; ?>

</body>
</html>
