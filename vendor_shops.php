<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get vendor shop posts
$query = "SELECT s.shop_name, s.location, s.menu, s.image, u.name AS vendor_name 
          FROM Shop s 
          JOIN User u ON s.vendor_id = u.user_id";
$result = $conn->query($query);

if (!$result) {
    die("Query Failed: " . $conn->error); // helpful debug message
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Shops - Spice & Surprise</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .shop-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .shop-card {
            background-color: #fff3e0;
            padding: 1.2rem;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .shop-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .shop-card h3 {
            margin: 0.2rem 0;
        }
        .shop-card p {
            font-size: 0.9rem;
            margin: 0.4rem 0;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar">
    <div class="nav-container">
        <a href="home.php" class="nav-logo">
            <i class="fas fa-pepper-hot"></i> Spice & Surprise
        </a>
        <div class="nav-links">
            <a href="challenge.php" class="nav-link"><i class="fas fa-fire"></i> Challenges</a>
            <a href="vendor_shops.php" class="nav-link"><i class="fas fa-store"></i> Vendor Shops</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<div class="container">
    <h1>Explore Vendor Shops</h1>
    <div class="shop-grid">
        <?php while ($shop = $result->fetch_assoc()): ?>
            <div class="shop-card">
                <?php if (!empty($shop['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($shop['image']); ?>" alt="Shop Image">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($shop['shop_name']); ?></h3>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($shop['location']); ?></p>
                <p><strong>Menu:</strong> <?php echo nl2br(htmlspecialchars($shop['menu'])); ?></p>
                <p><strong>Vendor:</strong> <?php echo htmlspecialchars($shop['vendor_name']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
