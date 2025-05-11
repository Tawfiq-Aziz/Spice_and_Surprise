<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Hardcoded spin wheel options
$spin_options = [
    [
        'text' => 'Try 3 different types of jhalmuri in 1 hour',
        'time_limit' => 60,
        'reward_pts' => 50,
        'max_tries' => 3
    ],
    [
        'text' => 'Visit 2 different street food vendors in 45 minutes',
        'time_limit' => 45,
        'reward_pts' => 40,
        'max_tries' => 3
    ],
    [
        'text' => 'Try 4 different types of chaat in 1 hour',
        'time_limit' => 60,
        'reward_pts' => 60,
        'max_tries' => 3
    ],
    [
        'text' => 'Complete a food bingo card in 30 minutes',
        'time_limit' => 30,
        'reward_pts' => 30,
        'max_tries' => 3
    ],
    [
        'text' => 'Try 5 different types of street food in 1.5 hours',
        'time_limit' => 90,
        'reward_pts' => 70,
        'max_tries' => 3
    ],
    [
        'text' => 'Visit 3 different food stalls in 1 hour',
        'time_limit' => 60,
        'reward_pts' => 55,
        'max_tries' => 3
    ],
    [
        'text' => 'Try 2 different types of biryani in 45 minutes',
        'time_limit' => 45,
        'reward_pts' => 45,
        'max_tries' => 3
    ],
    [
        'text' => 'Complete a dessert challenge in 30 minutes',
        'time_limit' => 30,
        'reward_pts' => 35,
        'max_tries' => 3
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spin the Wheel - Spice & Surprise</title>
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
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 20px;
            text-align: center;
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
            border: 2px solid var(--accent-color);
            transition: transform 4s cubic-bezier(0.17, 0.67, 0.12, 0.99);
            transform: rotate(0deg);
        }

        .wheel-section {
            position: absolute;
            width: 50%;
            height: 50%;
            transform-origin: 100% 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 10px;
            box-sizing: border-box;
            font-size: 12px;
            color: white;
            background: var(--accent-color);
            clip-path: polygon(0 0, 100% 0, 100% 100%);
        }

        .wheel-pointer {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 20px solid transparent;
            border-right: 20px solid transparent;
            border-top: 40px solid var(--accent-color);
            z-index: 2;
        }

        .spin-button {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 2rem;
            transition: all 0.3s ease;
        }

        .spin-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .spin-button:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
        }

        .result-container {
            margin-top: 2rem;
            padding: 1rem;
            background: var(--card-bg);
            border-radius: 8px;
            display: none;
        }

        .result-container.show {
            display: block;
        }

        .task-details {
            margin-top: 1rem;
            padding: 1rem;
            background: var(--secondary-color);
            border-radius: 8px;
        }

        .task-details p {
            margin: 0.5rem 0;
        }

        .timer {
            font-size: 1.5rem;
            color: var(--accent-color);
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <!-- Go Back Bar -->
    <div style="background: var(--secondary-color); padding: 1rem 0; margin-bottom: 2rem; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div style="max-width: 800px; margin: 0 auto; padding: 0 20px; display: flex; align-items: center;">
            <a href="challenge.php" style="display: inline-flex; align-items: center; gap: 0.8rem; color: var(--text-color); text-decoration: none; font-weight: 500; padding: 0.8rem 1.2rem; border-radius: 8px; background: var(--primary-color); transition: all 0.3s ease;">
                <i class="fas fa-arrow-left"></i> Back to Challenges
            </a>
        </div>
    </div>
    <div class="container">
        <h1>Spin the Wheel Challenge</h1>
        <div class="wheel-container">
            <div class="wheel-pointer"></div>
            <div class="wheel" id="wheel">
                <?php
                $total_options = count($spin_options);
                $angle_per_section = 360 / $total_options;
                foreach ($spin_options as $index => $option) {
                    $rotation = $index * $angle_per_section;
                    echo "<div class='wheel-section' style='transform: rotate({$rotation}deg) skewY(" . (90 - $angle_per_section) . "deg);'>";
                    echo "<span style='transform: skewY(" . ($angle_per_section - 90) . "deg) rotate(" . ($angle_per_section/2) . "deg);'>";
                    echo substr($option['text'], 0, 20) . "...";
                    echo "</span></div>";
                }
                ?>
            </div>
        </div>
        <button class="spin-button" id="spinButton">Spin the Wheel</button>
        <div class="result-container" id="resultContainer">
            <h2>Your Challenge:</h2>
            <div class="task-details" id="taskDetails">
                <!-- Task details will be inserted here -->
            </div>
            <div class="timer" id="timer"></div>
        </div>
    </div>
    <script>
        const wheel = document.getElementById('wheel');
        const spinButton = document.getElementById('spinButton');
        const resultContainer = document.getElementById('resultContainer');
        const taskDetails = document.getElementById('taskDetails');
        const timer = document.getElementById('timer');
        
        const spinOptions = <?php echo json_encode($spin_options); ?>;
        let isSpinning = false;
        let timeLeft = 0;
        let timerInterval;

        function spinWheel() {
            if (isSpinning || !spinOptions.length) return;
            
            isSpinning = true;
            spinButton.disabled = true;
            resultContainer.classList.remove('show');
            
            // Random number of full rotations (3-5) plus random angle
            const rotations = 3 + Math.floor(Math.random() * 3);
            const randomAngle = Math.floor(Math.random() * 360);
            const totalRotation = rotations * 360 + randomAngle;
            
            wheel.style.transform = `rotate(${totalRotation}deg)`;
            
            // Calculate which option was selected
            setTimeout(() => {
                const selectedIndex = Math.floor(((360 - (randomAngle % 360)) / (360 / spinOptions.length))) % spinOptions.length;
                const selectedOption = spinOptions[selectedIndex];
                
                // Display the result
                taskDetails.innerHTML = `
                    <p><strong>Task:</strong> ${selectedOption.text}</p>
                    <p><strong>Time Limit:</strong> ${selectedOption.time_limit} minutes</p>
                    <p><strong>Reward Points:</strong> ${selectedOption.reward_pts}</p>
                    <p><strong>Max Tries:</strong> ${selectedOption.max_tries}</p>
                `;
                
                resultContainer.classList.add('show');
                
                // Start the timer
                timeLeft = selectedOption.time_limit * 60;
                updateTimer();
                timerInterval = setInterval(updateTimer, 1000);
                
                isSpinning = false;
                spinButton.disabled = false;
            }, 4000);
        }

        function updateTimer() {
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                timer.textContent = "Time's up!";
                return;
            }
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timer.textContent = `Time Remaining: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            timeLeft--;
        }

        if (spinButton) {
            spinButton.addEventListener('click', spinWheel);
        }
    </script>
</body>
</html> 