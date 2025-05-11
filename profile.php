<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, points_earned, achievement_lvl, join_date FROM User WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: logout.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Get user's review count
$stmt = $conn->prepare("SELECT COUNT(*) as review_count FROM review WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$review_count = $stmt->get_result()->fetch_assoc()['review_count'];
$stmt->close();

// Get user's completed challenges
$stmt = $conn->prepare("SELECT COUNT(*) as challenge_count FROM Completes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$challenge_count = $stmt->get_result()->fetch_assoc()['challenge_count'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Spice & Surprise</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-header {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
            padding: 2rem;
            border-radius: 15px;
            color: white;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #ff6b6b;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .stat-card i {
            font-size: 2rem;
            color: #ff6b6b;
            margin-bottom: 0.5rem;
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

        .theme-switch {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .theme-switch h3 {
            margin-bottom: 1rem;
            color: #2d3436;
        }

        .theme-options {
            display: flex;
            gap: 1rem;
        }

        .theme-option {
            padding: 0.8rem 1.5rem;
            border: 2px solid #dfe6e9;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .theme-option:hover {
            border-color: #ff6b6b;
        }

        .theme-option.active {
            background: #ff6b6b;
            color: white;
            border-color: #ff6b6b;
        }

        .profile-info {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dfe6e9;
        }

        .info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: #ff6b6b;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 0.9rem;
            color: #636e72;
        }

        .info-value {
            font-weight: 500;
            color: #2d3436;
        }

        /* Dark mode styles */
        body.dark-mode {
            background: #1a1a2e;
            color: #fff;
        }

        body.dark-mode .stat-card,
        body.dark-mode .theme-switch,
        body.dark-mode .profile-info {
            background: #2a2a3d;
        }

        body.dark-mode .stat-value,
        body.dark-mode .info-value {
            color: #fff;
        }

        body.dark-mode .stat-label,
        body.dark-mode .info-label {
            color: #b2bec3;
        }

        body.dark-mode .info-icon {
            background: #3a3a4d;
        }

        body.dark-mode .theme-option {
            border-color: #3a3a4d;
            color: #fff;
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
                <a href="profile.php" class="nav-link active"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h1><?= htmlspecialchars($user['name']) ?></h1>
            <p class="achievement-level"><?= htmlspecialchars($user['achievement_lvl']) ?> Explorer</p>
        </div>

        <div class="profile-stats">
            <div class="stat-card">
                <i class="fas fa-coins"></i>
                <div class="stat-value"><?= number_format($user['points_earned']) ?></div>
                <div class="stat-label">Spice Points</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-comment"></i>
                <div class="stat-value"><?= $review_count ?></div>
                <div class="stat-label">Reviews Posted</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-trophy"></i>
                <div class="stat-value"><?= $challenge_count ?></div>
                <div class="stat-label">Challenges Completed</div>
            </div>
        </div>

        <div class="profile-info">
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="info-content">
                    <div class="info-label">Member Since</div>
                    <div class="info-value"><?= date('F j, Y', strtotime($user['join_date'])) ?></div>
                </div>
            </div>
        </div>

        <div class="theme-switch">
            <h3><i class="fas fa-palette"></i> Theme Settings</h3>
            <div class="theme-options">
                <div class="theme-option active" data-theme="light">
                    <i class="fas fa-sun"></i> Light Mode
                </div>
                <div class="theme-option" data-theme="dark">
                    <i class="fas fa-moon"></i> Dark Mode
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeOptions = document.querySelectorAll('.theme-option');
            const body = document.body;
            
            // Check for saved theme preference
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                body.classList.add('dark-mode');
                themeOptions[1].classList.add('active');
                themeOptions[0].classList.remove('active');
            }

            themeOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const theme = this.dataset.theme;
                    
                    // Remove active class from all options
                    themeOptions.forEach(opt => opt.classList.remove('active'));
                    
                    // Add active class to clicked option
                    this.classList.add('active');
                    
                    // Apply theme
                    if (theme === 'dark') {
                        body.classList.add('dark-mode');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        body.classList.remove('dark-mode');
                        localStorage.setItem('theme', 'light');
                    }
                });
            });
        });
    </script>
</body>
</html> 