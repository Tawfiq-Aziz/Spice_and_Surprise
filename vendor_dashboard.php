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

// Fetch vendor's stats
$stmt = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM Shop WHERE vendor_id = ?) as total_shops,
        (SELECT COUNT(*) FROM review r JOIN Shop s ON r.vendor_id = s.vendor_id WHERE s.vendor_id = ?) as total_reviews,
        (SELECT AVG(spice_rating) FROM review r JOIN Shop s ON r.vendor_id = s.vendor_id WHERE s.vendor_id = ?) as avg_spice_rating,
        (SELECT AVG(hygine_rating) FROM review r JOIN Shop s ON r.vendor_id = s.vendor_id WHERE s.vendor_id = ?) as avg_hygiene_rating,
        (SELECT AVG(taste_rating) FROM review r JOIN Shop s ON r.vendor_id = s.vendor_id WHERE s.vendor_id = ?) as avg_taste_rating
");
$stmt->bind_param("iiiii", $vendor_id, $vendor_id, $vendor_id, $vendor_id, $vendor_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Fetch recent reviews
$stmt = $conn->prepare("
    SELECT r.*, u.name as reviewer_name, s.shop_name 
    FROM review r 
    JOIN user u ON r.user_id = u.user_id 
    JOIN shop s ON r.vendor_id = s.vendor_id 
    WHERE s.vendor_id = ? 
    ORDER BY r.date DESC 
    LIMIT 5
");
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$recent_reviews = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vendor Dashboard - Spice & Surprise</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
            background: url('https://images.unsplash.com/photo-1511497584788-876760111969?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') no-repeat center center fixed;
            background-size: cover;
            color: #2d3436;
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            color: #2ecc71;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3436;
        }

        .stat-label {
            color: #636e72;
            font-size: 0.9rem;
        }

        .add-shop-form {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            font-family: inherit;
        }

        button {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #27ae60;
        }

        .shops-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .shop-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .shop-card:hover {
            transform: translateY(-5px);
        }

        .shop-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .reviews-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .review-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .reviewer-name {
            font-weight: 600;
            color: #2d3436;
        }

        .review-date {
            color: #636e72;
            font-size: 0.9rem;
        }

        .rating-stars {
            color: #f1c40f;
            margin: 10px 0;
        }

        .message {
            background: #2ecc71;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .delete-btn:hover {
            background: #c0392b;
        }

        /* Dark mode styles */
        body.dark-mode {
            color: #fff;
        }

        body.dark-mode .dashboard-header,
        body.dark-mode .stat-card,
        body.dark-mode .add-shop-form,
        body.dark-mode .shop-card,
        body.dark-mode .reviews-section {
            background: rgba(42, 42, 61, 0.95);
        }

        body.dark-mode input,
        body.dark-mode textarea {
            background: #2a2a3d;
            color: #fff;
            border-color: #3a3a4d;
        }

        body.dark-mode .stat-value,
        body.dark-mode .reviewer-name {
            color: #fff;
        }

        body.dark-mode .stat-label,
        body.dark-mode .review-date {
            color: #b2bec3;
        }

        body.dark-mode .review-card {
            background: #3a3a4d;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1><i class="fas fa-store"></i> Vendor Dashboard</h1>
            <div class="theme-switch">
                <button onclick="toggleTheme()"><i class="fas fa-moon"></i> Toggle Theme</button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-store"></i>
                <div class="stat-value"><?= $stats['total_shops'] ?></div>
                <div class="stat-label">Total Shops</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-comments"></i>
                <div class="stat-value"><?= $stats['total_reviews'] ?></div>
                <div class="stat-label">Total Reviews</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-fire"></i>
                <div class="stat-value"><?= number_format($stats['avg_spice_rating'], 1) ?></div>
                <div class="stat-label">Avg Spice Rating</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-star"></i>
                <div class="stat-value"><?= number_format($stats['avg_taste_rating'], 1) ?></div>
                <div class="stat-label">Avg Taste Rating</div>
            </div>
        </div>

        <div class="add-shop-form">
            <h2><i class="fas fa-plus-circle"></i> Add New Shop</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div>
                        <input type="text" name="shop_name" placeholder="Shop Name" required>
                        <input type="text" name="license_no" placeholder="License Number" required>
                    </div>
                    <div>
                        <input type="text" name="location" placeholder="Location" required>
                        <input type="file" name="shop_image" required>
                    </div>
                </div>
                <textarea name="menu" placeholder="Menu Details" required></textarea>
                <button type="submit" name="add_shop"><i class="fas fa-plus"></i> Add Shop</button>
            </form>
        </div>

        <div class="shops-grid">
            <?php while ($shop = $shops->fetch_assoc()): ?>
                <div class="shop-card">
                    <img src="uploads/<?= htmlspecialchars($shop['image']) ?>" alt="Shop Image">
                    <h3><?= htmlspecialchars($shop['shop_name']) ?></h3>
                    <p><strong>License:</strong> <?= htmlspecialchars($shop['license_no']) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($shop['location']) ?></p>
                    <p><strong>Menu:</strong> <?= nl2br(htmlspecialchars($shop['menu'])) ?></p>
                    <a href="?delete=<?= $shop['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this shop?')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="reviews-section">
            <h2><i class="fas fa-comments"></i> Recent Reviews</h2>
            <?php if ($recent_reviews->num_rows > 0): ?>
                <?php while ($review = $recent_reviews->fetch_assoc()): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <span class="reviewer-name"><?= htmlspecialchars($review['reviewer_name']) ?></span>
                            <span class="review-date"><?= date('M j, Y', strtotime($review['date'])) ?></span>
                        </div>
                        <div class="rating-stars">
                            <span>🌶️ Spice: <?= number_format($review['spice_rating'], 1) ?>/5</span>
                            <span>🧼 Hygiene: <?= number_format($review['hygine_rating'], 1) ?>/5</span>
                            <span>😋 Taste: <?= number_format($review['taste_rating'], 1) ?>/5</span>
                        </div>
                        <p><?= nl2br(htmlspecialchars($review['comments'])) ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No reviews yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Theme switching functionality
        function toggleTheme() {
            document.body.classList.toggle('dark-mode');
            const isDarkMode = document.body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        }

        // Check for saved theme preference
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
            }
        });
    </script>
</body>
</html>

