<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include database connection
include 'db.php';

// Get the current section from URL parameter, default to 'all'
$current_section = $_GET['section'] ?? 'all';

// Fetch spin wheel options if in spin section
$spin_options = [];
if ($current_section === 'spin') {
    $spin_query = "SELECT sco.option_text, sc.max_tries 
                   FROM spin_challenge sc 
                   JOIN spin_challenge_option sco ON sc.challenge_id = sco.sp_id
                   WHERE sc.challenge_id = 1";  // Add specific challenge_id
    $spin_result = $conn->query($spin_query);
    
    if ($spin_result) {
        while ($option = $spin_result->fetch_assoc()) {
            $spin_options[] = $option;
        }
    } else {
        // Log error if query fails
        error_log("Error fetching spin options: " . $conn->error);
    }
}

// Base query for challenges
$query = "SELECT c.*, 
          CASE 
              WHEN EXISTS (SELECT 1 FROM completes WHERE challenge_id = c.challenge_id AND user_id = ?) 
              THEN 'completed' 
              ELSE 'active' 
          END as status,
          sc.max_tries,
          tec.start_time,
          tec.end_time,
          tec.max_participants,
          (SELECT COUNT(*) FROM completes WHERE challenge_id = c.challenge_id) as current_participants
          FROM challenge c 
          LEFT JOIN spin_challenge sc ON c.challenge_id = sc.challenge_id
          LEFT JOIN timed_event_challenge tec ON c.challenge_id = tec.challenge_id
          WHERE c.is_active = 1";

// Add section filter
if ($current_section === 'spin') {
    $query .= " AND c.type = 'Spin'";
} elseif ($current_section === 'bingo') {
    $query .= " AND c.type = 'Bingo'";
} elseif ($current_section === 'timed') {
    $query .= " AND c.type = 'Timed'";
}

$query .= " ORDER BY c.challenge_id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$challenges = $stmt->get_result();

// If no challenges found, set to empty result
if (!$challenges) {
    $challenges = new mysqli_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Challenges - Spice & Surprise</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2d3436;
            --secondary-color: #3d3d3d;
            --accent-color: #4CAF50;
            --text-color: #ffffff;
            --bg-color: #121212;
            --card-bg: #1a1a1a;
            --gradient-1: linear-gradient(135deg, #2d3436 0%, #1a1a1a 100%);
            --gradient-2: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            --gradient-3: linear-gradient(135deg, #2d3436 0%, #1a1a1a 100%);
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .page-header h1 {
            margin: 0;
            color: var(--text-color);
            font-size: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .page-header p {
            color: #b2bec3;
            margin: 1rem 0 0;
            font-size: 1.1rem;
        }

        .challenges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .challenge-card {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .challenge-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .challenge-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .challenge-content {
            padding: 1.5rem;
        }

        .challenge-title {
            font-size: 1.5rem;
            color: var(--text-color);
            margin: 0 0 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .challenge-info {
            color: #b2bec3;
            margin-bottom: 1.5rem;
        }

        .challenge-info p {
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 0.95rem;
        }

        .challenge-info i {
            color: var(--accent-color);
            width: 20px;
        }

        .challenge-description {
            background: var(--secondary-color);
            padding: 1.2rem;
            border-radius: 10px;
            margin-top: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .challenge-description h4 {
            color: var(--text-color);
            margin: 0 0 1rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 1.1rem;
        }

        .challenge-description p {
            color: #b2bec3;
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .participate-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--gradient-2);
            color: white;
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
            font-weight: 500;
            border: none;
            cursor: pointer;
            width: 100%;
            justify-content: center;
        }

        .participate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
        }

        .participate-btn:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
        }

        .nav-links {
            display: flex;
            gap: 1.2rem;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-link {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            background: var(--secondary-color);
        }

        .nav-link:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
        }

        .nav-link i {
            font-size: 1.1rem;
        }

        .go-back-bar {
            background: var(--gradient-3);
            padding: 1rem 0;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .go-back-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
        }

        .go-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            background: var(--secondary-color);
            transition: all 0.3s ease;
        }

        .go-back-link:hover {
            background: var(--accent-color);
            transform: translateX(-5px);
        }

        .go-back-link i {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .go-back-link:hover i {
            transform: translateX(-3px);
        }

        .points-badge {
            background: var(--gradient-2);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .challenge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-left: 1rem;
        }

        .status-active {
            background: rgba(76, 175, 80, 0.2);
            color: var(--accent-color);
        }

        .status-completed {
            background: rgba(33, 150, 243, 0.2);
            color: #2196F3;
        }

        .challenge-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-right: 1rem;
            background: rgba(76, 175, 80, 0.1);
            color: var(--accent-color);
        }

        .challenge-details {
            background: var(--secondary-color);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .challenge-details p {
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 0.9rem;
            color: #b2bec3;
        }

        .challenge-details i {
            color: var(--accent-color);
            width: 20px;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--secondary-color);
            border-radius: 4px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--accent-color);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .challenge-nav {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background: var(--card-bg);
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .challenge-nav-link {
            flex: 1;
            text-align: center;
            padding: 1rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            font-weight: 500;
            background: var(--secondary-color);
        }

        .challenge-nav-link:hover {
            background: var(--accent-color);
            transform: translateY(-2px);
        }

        .challenge-nav-link.active {
            background: var(--accent-color);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
        }

        .challenge-nav-link i {
            font-size: 1.2rem;
        }

        .no-challenges {
            text-align: center;
            padding: 3rem;
            background: var(--card-bg);
            border-radius: 12px;
            margin-top: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .no-challenges i {
            font-size: 3rem;
            color: var(--accent-color);
            margin-bottom: 1rem;
        }

        .no-challenges h3 {
            color: var(--text-color);
            margin: 0 0 1rem;
        }

        .no-challenges p {
            color: #b2bec3;
            margin: 0;
        }

        /* Updated Spin Wheel Styles */
        .wheel-section {
            position: absolute;
            width: 50%;
            height: 50%;
            transform-origin: 100% 100%;
      display: flex;
      align-items: center;
      justify-content: center;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-color);
            text-align: center;
            padding: 0.5rem;
            box-sizing: border-box;
            background: var(--secondary-color);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .wheel-section:nth-child(odd) {
            background: var(--accent-color);
        }

        .wheel-result {
            margin-top: 1rem;
            padding: 1.5rem;
            background: var(--secondary-color);
            border-radius: 8px;
            display: none;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .wheel-result.active {
            display: block;
        }

        .wheel-result h3 {
            color: var(--accent-color);
            margin: 0 0 1rem;
            font-size: 1.2rem;
        }

        .wheel-result p {
            color: var(--text-color);
            margin: 0 0 1rem;
            font-size: 1rem;
            line-height: 1.5;
        }

        .wheel-result .task-details {
            background: var(--card-bg);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .wheel-result .task-details p {
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .wheel-result .task-details i {
            color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .challenges-grid {
                grid-template-columns: 1fr;
            }

            .nav-links {
                flex-direction: column;
                gap: 0.8rem;
            }

            .nav-link {
                width: 100%;
                justify-content: center;
            }

            .go-back-container {
                justify-content: center;
            }

            .challenge-nav {
                flex-direction: column;
            }

            .wheel-container {
                width: 250px;
                height: 250px;
            }
        }

        /* Spin Wheel Styles */
        .spin-wheel-container {
            display: none;
            text-align: center;
      padding: 2rem;
            background: var(--card-bg);
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .spin-wheel-container.active {
            display: block;
        }

        .wheel-container {
            position: relative;
      width: 300px;
      height: 300px;
            margin: 2rem auto;
        }

        .wheel {
            width: 100%;
            height: 100%;
      border-radius: 50%;
      position: relative;
      overflow: hidden;
            border: 8px solid var(--accent-color);
            box-shadow: 0 0 20px rgba(76, 175, 80, 0.3);
            transition: transform 4s cubic-bezier(0.17, 0.67, 0.12, 0.99);
    }

        .wheel-pointer {
      position: absolute;
            top: -20px;
      left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 40px;
            background: var(--accent-color);
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            z-index: 2;
        }

        .spin-button {
            background: var(--gradient-2);
      color: white;
      border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
      cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
        }

        .spin-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
        }

        .spin-button:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
    }
  </style>
</head>
<body>
    <!-- Add Go Back Bar -->
    <div class="go-back-bar">
        <div class="go-back-container">
            <a href="home.php" class="go-back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </div>

    <div class="container">
        <!-- Main Navigation -->
        <div class="nav-links">
            <a href="home.php" class="nav-link">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="vendor_shops.php" class="nav-link">
                <i class="fas fa-store"></i> Shops
            </a>
            <a href="profile.php" class="nav-link">
                <i class="fas fa-user"></i> Profile
            </a>
        </div>

        <div class="page-header">
            <h1><i class="fas fa-trophy"></i> Food Challenges</h1>
            <p>Complete challenges to earn points and rewards</p>
        </div>

        <!-- Challenge Type Navigation -->
        <div class="challenge-nav">
            <a href="?section=all" class="challenge-nav-link <?= isset(
                $current_section) && $current_section === 'all' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> All Challenges
            </a>
            <a href="spin_wheel.php" class="challenge-nav-link">
                <i class="fas fa-sync"></i> Spin Wheel
            </a>
            <a href="?section=bingo" class="challenge-nav-link <?= isset(
                $current_section) && $current_section === 'bingo' ? 'active' : '' ?>">
                <i class="fas fa-dice"></i> Food Bingo
            </a>
            <a href="?section=timed" class="challenge-nav-link <?= isset(
                $current_section) && $current_section === 'timed' ? 'active' : '' ?>">
                <i class="fas fa-clock"></i> Timed Events
            </a>
        </div>

        <!-- Spin Wheel Container -->
        <div class="spin-wheel-container <?= $current_section === 'spin' ? 'active' : '' ?>">
            <h2><i class="fas fa-sync"></i> Spin the Wheel</h2>
            <p>Test your luck and get exciting food challenges!</p>
            
  <div class="wheel-container">
                <div class="wheel-pointer"></div>
                <div class="wheel" id="wheel">
                    <!-- Wheel sections will be added dynamically -->
                </div>
            </div>

            <button class="spin-button" id="spinButton">
                <i class="fas fa-sync"></i> Spin the Wheel
            </button>

            <div class="wheel-result" id="wheelResult">
                <h3>Your Challenge!</h3>
                <p>Complete this task to earn points:</p>
                <div class="task-details">
                    <p id="taskText"></p>
                    <p><i class="fas fa-redo"></i> Max Tries: <span id="maxTries"></span></p>
                </div>
            </div>
        </div>

        <div class="challenges-grid">
            <?php if ($challenges->num_rows > 0): ?>
                <?php while ($challenge = $challenges->fetch_assoc()): ?>
                    <div class="challenge-card">
                        <div class="challenge-content">
                            <h2 class="challenge-title">
                                <span class="challenge-type-badge">
                                    <i class="fas fa-<?= $challenge['type'] === 'Spin' ? 'sync' : ($challenge['type'] === 'Timed' ? 'clock' : 'dice') ?>"></i>
                                    <?= htmlspecialchars($challenge['type']) ?>
                                </span>
                                <?= htmlspecialchars($challenge['description']) ?>
                                <span class="points-badge">
                                    <i class="fas fa-star"></i>
                                    <?= htmlspecialchars($challenge['reward_pts']) ?> Points
                                </span>
                                <?php if ($challenge['status'] === 'active'): ?>
                                    <span class="challenge-status status-active">
                                        <i class="fas fa-clock"></i> Active
                                    </span>
                                <?php elseif ($challenge['status'] === 'completed'): ?>
                                    <span class="challenge-status status-completed">
                                        <i class="fas fa-check"></i> Completed
                                    </span>
                                <?php endif; ?>
                            </h2>
                            
                            <div class="challenge-info">
                                <p><i class="fas fa-signal"></i> Difficulty: <?= htmlspecialchars($challenge['difficulty_level']) ?></p>
                                <p><i class="fas fa-clock"></i> Time Limit: <?= htmlspecialchars($challenge['time_limit']) ?> minutes</p>
                            </div>

                            <div class="challenge-details">
                                <?php if ($challenge['type'] === 'Spin'): ?>
                                    <p><i class="fas fa-redo"></i> Max Tries: <?= htmlspecialchars($challenge['max_tries']) ?></p>
                                <?php elseif ($challenge['type'] === 'Timed'): ?>
                                    <p><i class="fas fa-calendar"></i> Start: <?= date('M d, Y H:i', strtotime($challenge['start_time'])) ?></p>
                                    <p><i class="fas fa-calendar-check"></i> End: <?= date('M d, Y H:i', strtotime($challenge['end_time'])) ?></p>
                                    <p><i class="fas fa-users"></i> Participants: <?= htmlspecialchars($challenge['current_participants']) ?>/<?= htmlspecialchars($challenge['max_participants']) ?></p>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= ($challenge['current_participants'] / $challenge['max_participants']) * 100 ?>%"></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button class="participate-btn" 
                                    data-challenge-id="<?= htmlspecialchars($challenge['challenge_id']) ?>"
                                    data-challenge-type="<?= htmlspecialchars($challenge['type']) ?>"
                                    <?= $challenge['status'] === 'completed' ? 'disabled' : '' ?>>
                                <i class="fas fa-flag"></i>
                                <?= $challenge['status'] === 'completed' ? 'Completed' : 'Participate' ?>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-challenges">
                    <i class="fas fa-trophy"></i>
                    <h3>No Challenges Available</h3>
                    <p>Check back later for new challenges!</p>
                </div>
            <?php endif; ?>
        </div>
  </div>

  <script>
        document.querySelectorAll('.participate-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (!this.disabled) {
                    const challengeId = this.dataset.challengeId;
                    const challengeType = this.dataset.challengeType;
                    
                    if (confirm('Are you sure you want to participate in this challenge?')) {
                        fetch('participate_challenge.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                challenge_id: challengeId,
                                challenge_type: challengeType
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(`Congratulations! You've earned ${data.points_earned} points!`);
                                location.reload(); // Refresh to update status
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while participating in the challenge. Please try again.');
                        });
                    }
                }
            });
        });

        // Spin Wheel Functionality
        document.addEventListener('DOMContentLoaded', function() {
    const wheel = document.getElementById('wheel');
            const spinButton = document.getElementById('spinButton');
            const wheelResult = document.getElementById('wheelResult');
            const taskText = document.getElementById('taskText');
            const maxTries = document.getElementById('maxTries');
            
            // Get spin options from PHP
            const spinOptions = <?= json_encode($spin_options) ?>;
            
            if (spinOptions.length > 0) {
                // Create wheel sections
                spinOptions.forEach((option, index) => {
                    const sectionElement = document.createElement('div');
                    sectionElement.className = 'wheel-section';
                    sectionElement.style.transform = `rotate(${index * (360 / spinOptions.length)}deg)`;
                    sectionElement.innerHTML = option.option_text;
                    wheel.appendChild(sectionElement);
                });

                let isSpinning = false;

                spinButton.addEventListener('click', function() {
                    if (isSpinning) return;
                    
                    isSpinning = true;
                    spinButton.disabled = true;
                    wheelResult.classList.remove('active');

                    // Random rotation between 5 and 10 full spins
                    const spins = 5 + Math.random() * 5;
                    const degrees = spins * 360;
                    const randomSection = Math.floor(Math.random() * spinOptions.length);
                    const finalRotation = degrees + (randomSection * (360 / spinOptions.length));

                    wheel.style.transform = `rotate(${finalRotation}deg)`;

                    // Show result after spin
      setTimeout(() => {
                        isSpinning = false;
                        spinButton.disabled = false;
                        wheelResult.classList.add('active');
                        
                        const selectedTask = spinOptions[randomSection];
                        taskText.textContent = selectedTask.option_text;
                        maxTries.textContent = selectedTask.max_tries;
                    }, 4000);
                });
            } else {
                wheel.innerHTML = '<div class="no-challenges"><p>No spin wheel tasks available</p></div>';
                spinButton.disabled = true;
            }
        });
  </script>
</body>
</html>

