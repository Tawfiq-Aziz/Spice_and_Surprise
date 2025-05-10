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

// Get active challenges
$challenges = [];
$stmt = $conn->prepare("SELECT c.challenge_id, c.description, c.type, c.difficulty_level, 
                        (SELECT COUNT(*) FROM Completes WHERE challenge_id = c.challenge_id AND user_id = ?) as completed
                        FROM Challenge c
                        WHERE c.is_active = TRUE");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $challenges[] = $row;
}
$stmt->close();

// Calculate progress percentage (example logic)
$progress = min($user['points_earned'] / 1000 * 100, 100); // Assuming 1000 points to next level

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Spice & Surprise</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar">
    <div class="nav-container">
        <a href="home.php" class="nav-logo">
            <i class="fas fa-pepper-hot"></i> Spice & Surprise
        </a>
        <div class="search-bar-container">
            <form action="search.php" method="GET" class="search-form">
                <input type="text" name="shop_name" placeholder="Shop Name">
                <input type="text" name="location" placeholder="Location">
                <input type="text" name="license_no" placeholder="License Number">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>
        <div class="nav-links">
            <a href="challenge.php" class="nav-link"><i class="fas fa-fire"></i> Challenges</a>
            <a href="vendor_shops.php" class="nav-link"><i class="fas fa-store"></i> Vendor Shops</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<main class="container">
    <!-- Welcome Section -->
    <div class="card welcome-card">
        <div class="welcome-text">
            <h1>Welcome back, <span class="highlight"><?= htmlspecialchars($user['name']) ?></span>!</h1>
            <p class="subtitle">"The only thing we're serious about is flavor."</p>
        </div>
        <div class="welcome-image">
            <i class="fas fa-pepper-hot"></i>
        </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="grid grid-3">
        <div class="card stat-card">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-info">
                <h3>Spice Points</h3>
                <div class="stat-value"><?= $user['points_earned'] ?></div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="stat-icon"><i class="fas fa-trophy"></i></div>
            <div class="stat-info">
                <h3>Explorer Level</h3>
                <div class="stat-value"><?= $user['achievement_lvl'] ?></div>
            </div>
        </div>
        <div class="card stat-card">
            <div class="stat-icon"><i class="fas fa-medal"></i></div>
            <div class="stat-info">
                <h3>Challenges</h3>
                <div class="stat-value"><?= count(array_filter($challenges, fn($c) => $c['completed'])) ?>/<?= count($challenges) ?></div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="card progress-card">
        <h2><i class="fas fa-chart-line"></i> Your Progress</h2>
        <div class="progress-container">
            <div class="progress-labels">
                <span>Beginner</span>
                <span>Expert</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progress ?>%"></div>
            </div>
            <div class="progress-text"><?= round($progress) ?>% to next level</div>
        </div>
    </div>

    <!-- Active Challenges -->
    <div class="card">
        <div class="section-header">
            <h2><i class="fas fa-fire-alt"></i> Active Challenges</h2>
            <a href="challenges.php" class="btn btn-sm">View All</a>
        </div>

        <?php if (count($challenges) > 0): ?>
            <div class="grid grid-3">
                <?php foreach ($challenges as $challenge): ?>
                    <div class="challenge-card <?= $challenge['completed'] ? 'completed' : '' ?>">
                        <div class="challenge-header">
                            <span class="difficulty-badge <?= strtolower($challenge['difficulty_level']) ?>">
                                <?= $challenge['difficulty_level'] ?>
                            </span>
                            <?php if ($challenge['completed']): ?>
                                <span class="completed-badge"><i class="fas fa-check-circle"></i> Completed</span>
                            <?php endif; ?>
                        </div>
                        <div class="challenge-body">
                            <h3><?= htmlspecialchars($challenge['type']) ?></h3>
                            <p><?= htmlspecialchars($challenge['description']) ?></p>
                        </div>
                        <div class="challenge-footer">
                            <a href="challenge.php" class="btn <?= $challenge['completed'] ? 'btn-completed' : 'btn-primary' ?>">
                                <?= $challenge['completed'] ? 'View' : 'Start' ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-utensils"></i>
                <h3>No Active Challenges</h3>
                <p>Check back later for new spicy challenges!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Activities -->
    <div class="card">
        <h2><i class="fas fa-history"></i> Recent Activities</h2>
        <div class="activity-list">
            <div class="activity-item">
                <div class="activity-icon"><i class="fas fa-fire"></i></div>
                <div class="activity-content">
                    <p>Completed <strong>Spicy Ramen Challenge</strong></p>
                    <small>2 days ago • +50 points</small>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon"><i class="fas fa-flag"></i></div>
                <div class="activity-content">
                    <p>Reached <strong>Hot Explorer</strong> level</p>
                    <small>1 week ago</small>
                </div>
            </div>
            <div class="activity-item">
                <div class="activity-icon"><i class="fas fa-comment"></i></div>
                <div class="activity-content">
                    <p>Reviewed <strong>Dragon's Breath Curry</strong></p>
                    <small>1 week ago • +10 points</small>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-logo"><i class="fas fa-pepper-hot"></i> Spice & Surprise</div>
        <div class="footer-links">
            <a href="#">About</a>
            <a href="#">Contact</a>
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
        </div>
        <div class="footer-social">
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-facebook"></i></a>
        </div>
    </div>
    <div class="footer-copyright">
        &copy; <?= date('Y') ?> Spice & Surprise. All rights reserved.
    </div>
</footer>

<!-- Reviews Section -->
<section class="reviews-container">
    <div class="reviews-wrapper">
        <h2 class="section-title"><i class="fas fa-pepper-hot"></i> Spice Meter & Reviews</h2>

        <?php if ($_SESSION['role'] === 'food_explorer') : ?>
        <form class="review-form" method="post">
            <div class="form-group">
                <input type="text" name="vendor_identifier" placeholder="Vendor Name or License No" required>
            </div>

            <div class="rating-group">
                <label>Spice Level</label>
                <div class="rating-control">
                    <input type="range" name="spice_rating" min="0" max="5" step="0.5">
                    <div class="rating-labels"><span>Mild</span><span>Hot</span><span>🔥 Fire!</span></div>
                </div>
            </div>

            <div class="rating-group">
                <label>Hygiene</label>
                <div class="rating-control">
                    <input type="range" name="hygiene_rating" min="0" max="5" step="0.5">
                    <div class="rating-labels"><span>Poor</span><span>Good</span><span>Sterile</span></div>
                </div>
            </div>

            <div class="rating-group">
                <label>Taste</label>
                <div class="rating-control">
                    <input type="range" name="taste_rating" min="0" max="5" step="0.5">
                    <div class="rating-labels"><span>Bland</span><span>Tasty</span><span>Divine!</span></div>
                </div>
            </div>

            <div class="form-group">
                <textarea name="comments" placeholder="Your detailed feedback..." rows="4"></textarea>
            </div>

            <button type="submit" name="submit_review" class="submit-btn">
                <i class="fas fa-paper-plane"></i> Submit Review
            </button>
        </form>
        <?php else : ?>
        <div class="access-message">
            <i class="fas fa-lock"></i> Only Food Explorers can submit reviews
        </div>
        <?php endif; ?>

        <h3 class="reviews-heading"><i class="fas fa-comment-alt"></i> Latest Reviews</h3>

        <div class="reviews-list">
            <?php if ($reviews && $reviews->num_rows > 0) : ?>
                <?php while ($row = $reviews->fetch_assoc()) : ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <span class="reviewer-name"><?= htmlspecialchars($row['user_name']) ?></span>
                            <span class="review-shop"><?= htmlspecialchars($row['shop_name']) ?></span>
                        </div>
                        <div class="review-date"><?= date('M j, Y', strtotime($row['date'])) ?></div>
                    </div>
                    <div class="review-ratings">
                        <div class="rating-item"><span class="rating-icon">🌶️</span><span class="rating-value"><?= $row['spice_rating'] ?>/5</span></div>
                        <div class="rating-item"><span class="rating-icon">🧼</span><span class="rating-value"><?= $row['hygiene_rating'] ?>/5</span></div>
                        <div class="rating-item"><span class="rating-icon">😋</span><span class="rating-value"><?= $row['taste_rating'] ?>/5</span></div>
                    </div>
                    <div class="review-comment"><?= htmlspecialchars($row['comments']) ?></div>
                </div>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="no-reviews"><i class="fas fa-comment-slash"></i> No reviews posted yet</div>
            <?php endif; ?>
        </div>
    </div>
</section>

</body>
</html>
