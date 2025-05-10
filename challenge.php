<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Challenge Hub - Spice & Surprise</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
  <div class="nav-container">
    <a href="home.php" class="nav-logo">
      <i class="fas fa-pepper-hot"></i> Spice & Surprise
    </a>
    <div class="nav-links">
      <a href="challenge.php" class="nav-link"><i class="fas fa-fire"></i> Challenges</a>
      <a href="leaderboard.php" class="nav-link"><i class="fas fa-trophy"></i> Leaderboard</a>
      <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
      <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </div>
</nav>

<div class="container">
  <h2 class="section-title">Choose Your Challenge</h2>
  <div class="grid grid-3">
    <div class="card challenge-type-card">
      <h3>🎡 Spin the Wheel</h3>
      <p>Get a random food challenge from Dhaka streets.</p>
    </div>
    
    <div class="card challenge-type-card">
      <h3>🧩 Food Bingo</h3>
      <p>Complete a line with Dhaka food icons!</p>
      <a href="bingo.php" class="btn">Play</a>
    </div>

    <div class="card challenge-type-card">
      <h3>⏱️ Timed Event</h3>
      <p>Race against the clock with spicy missions.</p>
      <a href="timed_event.php" class="btn">Begin</a>
    </div>
  </div>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Spin the Wheel - Dhaka Street Food</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../style.css">
  <style>
    .wheel-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      text-align: center;
    }
    .wheel {
      width: 300px;
      height: 300px;
      border-radius: 50%;
      border: 10px solid #ff6347;
      position: relative;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(0,0,0,0.2);
    }
    .wheel .segment {
      width: 50%;
      height: 50%;
      position: absolute;
      top: 50%;
      left: 50%;
      transform-origin: 0% 0%;
      background-color: #ffe0b3;
      border: 1px solid #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 0.8rem;
      font-weight: bold;
      padding: 5px;
      box-sizing: border-box;
    }
    .pointer {
      width: 0; 
      height: 0; 
      border-left: 15px solid transparent;
      border-right: 15px solid transparent;
      border-bottom: 30px solid #222;
      margin-bottom: 1rem;
    }
    .spin-btn {
      margin-top: 1rem;
      padding: 0.5rem 1.5rem;
      background-color: #ff6347;
      color: white;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      font-size: 1rem;
    }
    .result {
      margin-top: 1rem;
      font-size: 1.1rem;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="wheel-container">
    <div class="pointer"></div>
    <div class="wheel" id="wheel"></div>
    <button class="spin-btn" onclick="spinWheel()">Spin</button>
    <div class="result" id="result"></div>
  </div>

  <script>
    const foods = [
      'Fuchka', 'Chotpoti', 'Jhalmuri', 'Shingara', 'Samosa', 'Kebab', 'Paratha Roll', 'Pitha'
    ];

    const wheel = document.getElementById('wheel');
    const result = document.getElementById('result');

    const anglePerSegment = 360 / foods.length;

    // Create segments
    foods.forEach((item, index) => {
      const segment = document.createElement('div');
      segment.className = 'segment';
      segment.style.transform = `rotate(${index * anglePerSegment}deg) skewY(${90 - anglePerSegment}deg)`;
      segment.innerHTML = item;
      wheel.appendChild(segment);
    });

    let spinning = false;

    function spinWheel() {
      if (spinning) return;
      spinning = true;

      const randomDeg = Math.floor(Math.random() * 360 + 720); // 2+ full spins
      wheel.style.transition = 'transform 4s ease-out';
      wheel.style.transform = `rotate(${randomDeg}deg)`;

      setTimeout(() => {
        const actualDeg = randomDeg % 360;
        const index = Math.floor((360 - actualDeg) / anglePerSegment) % foods.length;
        const selectedFood = foods[index];
        result.textContent = `🎉 Try this: ${selectedFood} from Dhaka streets! +5 points`;

        // Send reward to server
        fetch('reward_user.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'challenge_type=spin_wheel&points=5'
        });

        spinning = false;
      }, 4000);
    }
  </script>
</body>
</html>

