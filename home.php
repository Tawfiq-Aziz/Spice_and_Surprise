<?php
include 'db.php'; // already included
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, points_earned, achievement_lvl FROM User WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: logout.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Calculate progress percentage (example logic)
$progress = min($user['points_earned'] / 1000 * 100, 100); // Assuming 1000 points to next level

// Fetch latest reviews with error handling
$reviews_query = "SELECT r.*, u.name AS user_name, s.shop_name, r.date
                 FROM review r
                 JOIN user u ON r.user_id = u.user_id
                 JOIN shop s ON r.vendor_id = s.vendor_id
                 ORDER BY r.date DESC
                 LIMIT 5";

$reviews = $conn->query($reviews_query);
if (!$reviews) {
    die("Error fetching reviews: " . $conn->error);
}

// Handle review submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $user_id = $_SESSION['user_id'];
    $vendor_identifier = $_POST['vendor_identifier'];
    $hygiene_rating = $_POST['hygiene_rating'];
    $taste_rating = $_POST['taste_rating'];
    $spice_rating = $_POST['spice_rating'];
    $comments = $_POST['comments'];

    // First get the vendor_id from the shop table
    $vendor_stmt = $conn->prepare("SELECT vendor_id FROM shop WHERE shop_name = ? OR license_no = ?");
    if (!$vendor_stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $vendor_stmt->bind_param("ss", $vendor_identifier, $vendor_identifier);
    $vendor_stmt->execute();
    $vendor_result = $vendor_stmt->get_result();

    if ($vendor_result->num_rows === 0) {
        die("Error: Vendor not found!");
    }

    $vendor_id = $vendor_result->fetch_assoc()['vendor_id'];
    $vendor_stmt->close();

    // Now insert the review
    $stmt = $conn->prepare("INSERT INTO review (user_id, vendor_id, hygine_rating, taste_rating, spice_rating, comments, date) VALUES (?, ?, ?, ?, ?, ?, CURDATE())");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iiddds", $user_id, $vendor_id, $hygiene_rating, $taste_rating, $spice_rating, $comments);

    if ($stmt->execute()) {
        echo "<script>alert('Review added successfully!');</script>";
    } else {
        echo "Execute failed: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spice & Surprise - Food Adventure Awaits</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary-color: #2d2d2d;
            --secondary-color: #3d3d3d;
            --accent-color: #4CAF50;
            --text-color: #ffffff;
            --bg-color: #1f1f1f;
            --card-bg: #2d2d2d;
            --gradient-1: linear-gradient(135deg, #2d2d2d 0%, #4CAF50 100%);
            --gradient-2: linear-gradient(135deg, #3d3d3d 0%, #4CAF50 100%);
            --gradient-3: linear-gradient(135deg, #2d2d2d 0%, #3d3d3d 100%);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background: url('https://images.unsplash.com/photo-1511497584788-876760111969?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') no-repeat center center fixed;
            background-size: cover;
            color: var(--text-color);
            min-height: 100vh;
            position: relative;
        }

        body::before {
            display: none;
        }

        .navbar {
            background: var(--primary-color);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.3s ease;
        }

        .nav-logo:hover {
            transform: scale(1.05);
        }

        .nav-logo i {
            color: var(--primary-color);
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-link {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
        }

        .hero-section {
            background: var(--primary-color);
            color: var(--text-color);
            padding: 100px 20px;
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1511497584788-876760111969?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') no-repeat center center;
            background-size: cover;
            opacity: 0.2;
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background: rgba(45, 45, 45, 0.8);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid var(--accent-color);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--accent-color);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            font-family: 'Playfair Display', serif;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: #b3b3b3;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .cta-button {
            display: inline-block;
            background: var(--accent-color);
            color: var(--text-color);
            padding: 15px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid var(--accent-color);
            position: relative;
            overflow: hidden;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: 0.5s;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .cta-button:hover::before {
            left: 100%;
        }

        .cta-button i {
            margin-left: 8px;
            transition: transform 0.3s ease;
        }

        .cta-button:hover i {
            transform: translateX(5px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            text-align: center;
            margin-bottom: 40px;
            color: var(--text-color);
            font-size: 2.5rem;
            position: relative;
            padding-bottom: 15px;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient-1);
            border-radius: 2px;
        }

        .features-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .feature-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            border: 1px solid var(--accent-color);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.2);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            color: var(--accent-color);
        }

        .feature-title {
            font-size: 1.8rem;
            color: var(--accent-color);
            margin-bottom: 15px;
        }

        .feature-description {
            color: #b3b3b3;
            line-height: 1.6;
        }

        .reviews-section {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border: 1px solid var(--accent-color);
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .review-card {
            background: var(--secondary-color);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            border: 1px solid var(--accent-color);
        }

        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .reviewer-avatar {
            width: 45px;
            height: 45px;
            background: var(--gradient-1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .reviewer-name {
            font-weight: 600;
            color: var(--accent-color);
        }

        .shop-name {
            color: #b3b3b3;
            font-size: 0.9rem;
        }

        .review-date {
            color: #b3b3b3;
            font-size: 0.9rem;
        }

        .rating-stars {
            display: flex;
            gap: 15px;
            margin: 15px 0;
        }

        .rating-item {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 20px;
            background: var(--primary-color);
            color: var(--text-color);
        }

        .rating-item .rating-icon {
            font-size: 1.2rem;
        }

        .review-comment {
            color: #b3b3b3;
            line-height: 1.6;
        }

        /* Progress Bar Section */
        .progress-section {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border: 1px solid var(--accent-color);
        }

        .achievement-level {
            font-size: 2rem;
            color: var(--accent-color);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .progress-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid rgba(176, 224, 230, 0.2);
        }

        .progress-bar {
            height: 20px;
            background: var(--secondary-color);
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress-fill {
            height: 100%;
            background: var(--accent-color);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .level-markers {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            color: var(--text-color);
        }

        .level-marker {
            text-align: center;
            font-size: 0.9rem;
            font-weight: 500;
            color: #b3b3b3;
        }

        .level-marker.active {
            color: var(--accent-color);
            font-weight: 600;
        }

        .points-display {
            font-size: 1.2rem;
            color: var(--accent-color);
            margin-top: 15px;
            font-weight: 500;
        }

        /* Redeem Section Styles */
        .redeem-section {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border: 1px solid var(--accent-color);
        }

        .coupon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .coupon-card {
            background: var(--secondary-color);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            border: 2px solid var(--accent-color);
            text-align: center;
            transition: all 0.3s ease;
            color: var(--text-color);
        }

        .coupon-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
        }

        .discount-amount {
            font-size: 2rem;
            color: var(--accent-color);
            font-weight: 700;
            margin: 10px 0;
        }

        .points-required {
            color: #b3b3b3;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .redeem-button {
            background: var(--accent-color);
            color: var(--text-color);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .redeem-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
        }

        .redeem-button:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg,
                rgba(133, 129, 225, 0.95) 0%,
                rgba(176, 224, 230, 0.95) 100%);
            color: white;
            padding: 60px 0 30px;
            margin-top: 80px;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') no-repeat center center;
            background-size: cover;
            opacity: 0.05;
            z-index: 0;
        }

        .footer-content {
            position: relative;
            z-index: 1;
        }

        .footer-section {
            margin-bottom: 30px;
        }

        .footer-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: white;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-links a:hover {
            transform: translateX(5px);
            opacity: 0.8;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            color: white;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            transform: translateY(-3px);
            opacity: 0.8;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Dark mode styles */
        body.dark-mode {
            --text-color: #ffffff;
            --bg-color: #1f1f1f;
            --card-bg: #2d2d2d;
        }

        body.dark-mode::before {
            display: none;
        }

        body.dark-mode .navbar,
        body.dark-mode .reviews-section,
        body.dark-mode .feature-card,
        body.dark-mode .progress-section,
        body.dark-mode .redeem-section {
            background: var(--card-bg);
        }

        body.dark-mode .footer {
            background: linear-gradient(135deg,
                rgba(44, 62, 80, 0.95) 0%,
                rgba(133, 129, 225, 0.95) 100%);
        }

        body.dark-mode .review-card {
            background: var(--secondary-color);
        }

        body.dark-mode .coupon-card {
            background: var(--secondary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-content {
                padding: 30px 20px;
            }

            .nav-container {
                flex-direction: column;
                gap: 15px;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
            }

            .nav-link {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .features-section,
            .reviews-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .social-links {
                justify-content: center;
            }

            .footer-links a {
                justify-content: center;
            }
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Discover Hidden Food Gems</h1>
            <p class="hero-subtitle">Join our community of food explorers and share your culinary adventures</p>
            <a href="vendor_shops.php" class="cta-button">
                Start Exploring <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

    <div class="container">
        <!-- Progress Section -->
        <section class="progress-section">
            <h2 class="section-title">Your Progress</h2>
            <div class="achievement-level">
                <?php
                $level = $user['achievement_lvl'];
                if ($level <= 3) {
                    echo "Beginner Foodie";
                } elseif ($level <= 6) {
                    echo "Elite Explorer";
                } else {
                    echo "Master Connoisseur";
                }
                ?>
            </div>
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                </div>
                <div class="level-markers">
                    <div class="level-marker <?= $level <= 3 ? 'active' : '' ?>">Beginner</div>
                    <div class="level-marker <?= $level > 3 && $level <= 6 ? 'active' : '' ?>">Elite</div>
                    <div class="level-marker <?= $level > 6 ? 'active' : '' ?>">Master</div>
                </div>
                <div class="points-display">
                    <?= $user['points_earned'] ?> Points Earned
                </div>
            </div>
        </section>

        <!-- Add Redeem Section after Progress Section -->
        <section class="redeem-section">
            <h2 class="section-title">Redeem Points</h2>
            <p class="section-description">Exchange your points for exclusive discount coupons!</p>
            <div class="coupon-grid">
                <div class="coupon-card">
                    <h3>5% Discount</h3>
                    <div class="discount-amount">5% OFF</div>
                    <div class="points-required">100 Points</div>
                    <button class="redeem-button" onclick="redeemCoupon(5, 100)" <?= $user['points_earned'] < 100 ? 'disabled' : '' ?>>Redeem</button>
                </div>
                <div class="coupon-card">
                    <h3>15% Discount</h3>
                    <div class="discount-amount">15% OFF</div>
                    <div class="points-required">250 Points</div>
                    <button class="redeem-button" onclick="redeemCoupon(15, 250)" <?= $user['points_earned'] < 250 ? 'disabled' : '' ?>>Redeem</button>
                </div>
                <div class="coupon-card">
                    <h3>30% Discount</h3>
                    <div class="discount-amount">30% OFF</div>
                    <div class="points-required">500 Points</div>
                    <button class="redeem-button" onclick="redeemCoupon(30, 500)" <?= $user['points_earned'] < 500 ? 'disabled' : '' ?>>Redeem</button>
                </div>
                <div class="coupon-card">
                    <h3>60% Discount</h3>
                    <div class="discount-amount">60% OFF</div>
                    <div class="points-required">1000 Points</div>
                    <button class="redeem-button" onclick="redeemCoupon(60, 1000)" <?= $user['points_earned'] < 1000 ? 'disabled' : '' ?>>Redeem</button>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 class="feature-title">Achievement System</h3>
                <p class="feature-description">Earn badges and level up as you complete challenges. Track your progress and showcase your culinary achievements!</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="feature-title">Community Events</h3>
                <p class="feature-description">Join monthly food festivals, cooking competitions, and meetups with fellow food enthusiasts in your area.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-gift"></i>
                </div>
                <h3 class="feature-title">Rewards Program</h3>
                <p class="feature-description">Earn points for reviews and challenges. Redeem them for exclusive discounts, free meals, and special experiences!</p>
            </div>
        </section>

        <!-- Reviews Section -->
        <section class="reviews-section">
            <h2 class="section-title">Latest Reviews</h2>
            <div class="reviews-grid">
                <?php while ($row = $reviews->fetch_assoc()): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="reviewer-name"><?= htmlspecialchars($row['user_name']) ?></div>
                                    <div class="shop-name"><?= htmlspecialchars($row['shop_name']) ?></div>
                                </div>
                            </div>
                            <div class="review-date"><?= date('M j, Y', strtotime($row['date'])) ?></div>
                        </div>
                        <div class="rating-stars">
                            <div class="rating-item">
                                <span class="rating-icon">🌶️</span>
                                <span class="rating-value"><?= number_format($row['spice_rating'], 1) ?>/5</span>
                            </div>
                            <div class="rating-item">
                                <span class="rating-icon">🧼</span>
                                <span class="rating-value"><?= number_format($row['hygine_rating'], 1) ?>/5</span>
                            </div>
                            <div class="rating-item">
                                <span class="rating-icon">😋</span>
                                <span class="rating-value"><?= number_format($row['taste_rating'], 1) ?>/5</span>
                            </div>
                        </div>
                        <div class="review-comment"><?= nl2br(htmlspecialchars($row['comments'])) ?></div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>

    <!-- Add Footer Section before closing body tag -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-title">About Us</h3>
                <p>Spice & Surprise is your ultimate food adventure companion. Discover hidden gems, share experiences, and earn rewards while exploring the culinary world.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="challenge.php"><i class="fas fa-fire"></i> Challenges</a></li>
                    <li><a href="vendor_shops.php"><i class="fas fa-store"></i> Vendor Shops</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3 class="footer-title">Contact Us</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-envelope"></i> support@spiceandsurprise.com</a></li>
                    <li><a href="#"><i class="fas fa-phone"></i> +1 234 567 890</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> 123 Food Street, Cuisine City</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Spice & Surprise. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Check for saved theme preference
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
            }
        });

        // Add JavaScript for Redeem Functionality
        function redeemCoupon(discount, points) {
            if (confirm(`Are you sure you want to redeem ${points} points for a ${discount}% discount coupon?`)) {
                // Here you would typically make an AJAX call to a PHP endpoint
                // For now, we'll just show an alert
                alert(`Congratulations! You've received a ${discount}% discount coupon!`);
            }
        }
    </script>
</body>
</html>
