<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit();
}

include 'db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_challenge':
                $max_tries = $_POST['max_tries'];
                $option_text = $_POST['option_text'];
                $description = $_POST['description'];
                $difficulty_level = $_POST['difficulty_level'];
                $time_limit = $_POST['time_limit'];
                $reward_pts = $_POST['reward_pts'];
                $challenge_type = $_POST['challenge_type'];
                
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Insert into challenge table first
                    $stmt = $conn->prepare("INSERT INTO challenge (description, type, difficulty_level, time_limit, reward_pts, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                    $stmt->bind_param("ssiii", $description, $challenge_type, $difficulty_level, $time_limit, $reward_pts);
                    $stmt->execute();
                    $challenge_id = $conn->insert_id;
                    
                    if ($challenge_type === 'Spin') {
                        // Insert into spin_challenge table
                        $stmt = $conn->prepare("INSERT INTO spin_challenge (challenge_id, max_tries) VALUES (?, ?)");
                        $stmt->bind_param("ii", $challenge_id, $max_tries);
                        $stmt->execute();
                        // Insert into spin_challenge_option table
                        $stmt = $conn->prepare("INSERT INTO spin_challenge_option (sp_id, option_text) VALUES (?, ?)");
                        $stmt->bind_param("is", $challenge_id, $option_text);
                        $stmt->execute();
                    }
                    $conn->commit();
                    header("Location: admin_dashboard.php?success=1");
                    exit();
                } catch (Exception $e) {
                    $conn->rollback();
                    header("Location: admin_dashboard.php?error=1");
                    exit();
                }
                break;

            case 'delete_challenge':
                $challenge_id = $_POST['challenge_id'];
                
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Delete from spin_challenge_option first (due to foreign key)
                    $stmt = $conn->prepare("DELETE FROM spin_challenge_option WHERE sp_id = ?");
                    $stmt->bind_param("i", $challenge_id);
                    $stmt->execute();
                    
                    // Delete from spin_challenge
                    $stmt = $conn->prepare("DELETE FROM spin_challenge WHERE challenge_id = ?");
                    $stmt->bind_param("i", $challenge_id);
                    $stmt->execute();
                    
                    // Delete from challenge
                    $stmt = $conn->prepare("DELETE FROM challenge WHERE challenge_id = ?");
                    $stmt->bind_param("i", $challenge_id);
                    $stmt->execute();
                    
                    $conn->commit();
                    header("Location: admin_dashboard.php?success=2");
                    exit();
                } catch (Exception $e) {
                    $conn->rollback();
                    header("Location: admin_dashboard.php?error=2");
                    exit();
                }
                break;

            case 'delete_option':
                $option_text = $_POST['option_text'];
                $sp_id = $_POST['sp_id'];
                
                $stmt = $conn->prepare("DELETE FROM spin_challenge_option WHERE sp_id = ? AND option_text = ?");
                $stmt->bind_param("is", $sp_id, $option_text);
                
                if ($stmt->execute()) {
                    header("Location: admin_dashboard.php?success=4");
                } else {
                    header("Location: admin_dashboard.php?error=4");
                }
                exit();
                break;
        }
    }
}

// Fetch all spin challenges with their options
$query = "SELECT c.*, sc.max_tries, GROUP_CONCAT(sco.option_text SEPARATOR '||') as options 
          FROM challenge c 
          JOIN spin_challenge sc ON c.challenge_id = sc.challenge_id 
          LEFT JOIN spin_challenge_option sco ON sc.challenge_id = sco.sp_id 
          WHERE c.type = 'Spin'
          GROUP BY c.challenge_id";
$result = $conn->query($query);

// Fetch all spin wheel options for the dropdown
$options_query = "SELECT c.challenge_id, c.description, sc.max_tries 
                 FROM challenge c 
                 JOIN spin_challenge sc ON c.challenge_id = sc.challenge_id 
                 WHERE c.type = 'Spin'";
$options_result = $conn->query($options_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Spice & Surprise</title>
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

        .dashboard-header {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dashboard-header h1 {
            margin: 0;
            color: var(--text-color);
            font-size: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .dashboard-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .card-header i {
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--text-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            background: var(--secondary-color);
            color: var(--text-color);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-color);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--gradient-2);
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .challenges-list {
            margin-top: 2rem;
        }

        .challenge-item {
            background: var(--secondary-color);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .challenge-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .challenge-title {
            font-size: 1.2rem;
            color: var(--text-color);
            margin: 0;
        }

        .challenge-options {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .option-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .option-item i {
            color: var(--accent-color);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid var(--accent-color);
            color: var(--accent-color);
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .dashboard-header {
                padding: 1.5rem;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        .challenge-details {
            background: var(--secondary-color);
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .challenge-details p {
            margin: 0.5rem 0;
            color: var(--text-color);
        }

        .challenge-options {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .option-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            background: var(--secondary-color);
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }

        .option-item i {
            color: var(--accent-color);
        }

        .challenge-item {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .challenge-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .challenge-title {
            margin: 0;
            color: var(--text-color);
            font-size: 1.2rem;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .option-card {
            background: var(--secondary-color);
            padding: 1rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .option-text {
            flex-grow: 1;
        }

        .option-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1><i class="fas fa-cog"></i> Admin Dashboard</h1>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert <?= $_GET['success'] == 1 || $_GET['success'] == 3 || $_GET['success'] == 4 ? 'alert-success' : 'alert-danger' ?>">
                <?php
                switch ($_GET['success']) {
                    case 1:
                        echo 'Challenge added successfully!';
                        break;
                    case 2:
                        echo 'Challenge deleted successfully!';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php
                switch ($_GET['error']) {
                    case 1:
                        echo 'Error adding challenge!';
                        break;
                    case 2:
                        echo 'Error deleting challenge!';
                        break;
                    case 3:
                        echo 'Error adding spin wheel option!';
                        break;
                    case 4:
                        echo 'Error deleting spin wheel option!';
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Add New Challenge Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-plus-circle"></i>
                    <h2>Add New Challenge</h2>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_challenge">
                    
                    <div class="form-group">
                        <label for="description">Challenge Description</label>
                        <input type="text" id="description" name="description" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="challenge_type">Challenge Type</label>
                        <select id="challenge_type" name="challenge_type" class="form-control" required>
                            <option value="Spin">Spin</option>
                            <option value="Timed">Timed</option>
                            <option value="Bingo">Bingo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="difficulty_level">Difficulty Level</label>
                        <select id="difficulty_level" name="difficulty_level" class="form-control" required>
                            <option value="Easy">Easy</option>
                            <option value="Medium">Medium</option>
                            <option value="Hard">Hard</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="time_limit">Time Limit (minutes)</label>
                        <input type="number" id="time_limit" name="time_limit" class="form-control" required min="1">
                    </div>

                    <div class="form-group">
                        <label for="reward_pts">Reward Points</label>
                        <input type="number" id="reward_pts" name="reward_pts" class="form-control" required min="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="max_tries">Max Tries</label>
                        <input type="number" id="max_tries" name="max_tries" class="form-control" required min="1" max="10">
                    </div>
                    
                    <div class="form-group">
                        <label for="option_text">Initial Challenge Task</label>
                        <textarea id="option_text" name="option_text" class="form-control" required rows="3" placeholder="Enter the first task"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Challenge
                    </button>
                </form>
            </div>

            <!-- Current Spin Wheel Challenges Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <i class="fas fa-list"></i>
                    <h2>Current Challenges</h2>
                </div>
                
                <div class="challenges-list">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($challenge = $result->fetch_assoc()): ?>
                            <div class="challenge-item">
                                <div class="challenge-header">
                                    <h3 class="challenge-title"><?= htmlspecialchars($challenge['description']) ?></h3>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_challenge">
                                        <input type="hidden" name="challenge_id" value="<?= $challenge['challenge_id'] ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this challenge?')">
                                            <i class="fas fa-trash"></i> Delete Challenge
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="challenge-details">
                                    <p><strong>Difficulty:</strong> <?= htmlspecialchars($challenge['difficulty_level']) ?></p>
                                    <p><strong>Time Limit:</strong> <?= htmlspecialchars($challenge['time_limit']) ?> minutes</p>
                                    <p><strong>Reward Points:</strong> <?= htmlspecialchars($challenge['reward_pts']) ?></p>
                                    <p><strong>Max Tries:</strong> <?= htmlspecialchars($challenge['max_tries']) ?></p>
                                </div>
                                
                                <div class="challenge-options">
                                    <?php 
                                    $options = explode('||', $challenge['options']);
                                    foreach ($options as $option): 
                                    ?>
                                        <div class="option-card">
                                            <div class="option-text">
                                                <?= htmlspecialchars($option) ?>
                                            </div>
                                            <div class="option-actions">
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete_option">
                                                    <input type="hidden" name="option_text" value="<?= htmlspecialchars($option) ?>">
                                                    <input type="hidden" name="sp_id" value="<?= $challenge['challenge_id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this option?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No spin wheel challenges found. Add your first challenge!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 