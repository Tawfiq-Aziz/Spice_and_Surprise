<?php
session_start();
include("db.php");

// Fetch all shops with their vendor info
$query = "SELECT s.*, u.name as vendor_name 
          FROM shop s 
          JOIN user u ON s.vendor_id = u.user_id 
          ORDER BY s.shop_name";

$shops = $conn->query($query);

if (!$shops) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Vendor Shops - Spice & Surprise</title>
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

        .shops-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .shop-card {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .shop-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .shop-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .shop-content {
            padding: 1.5rem;
        }

        .shop-name {
            font-size: 1.5rem;
            color: var(--text-color);
            margin: 0 0 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .shop-info {
            color: #b2bec3;
            margin-bottom: 1.5rem;
        }

        .shop-info p {
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 0.95rem;
        }

        .shop-info i {
            color: var(--accent-color);
            width: 20px;
        }

        .menu-preview {
            background: var(--secondary-color);
            padding: 1.2rem;
            border-radius: 10px;
            margin-top: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .menu-preview h4 {
            color: var(--text-color);
            margin: 0 0 1rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 1.1rem;
        }

        .menu-preview p {
            color: #b2bec3;
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .review-btn {
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
        }

        .review-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
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

            .shops-grid {
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
        }

        /* Redeem Modal Styles */
        .redeem-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .redeem-modal.active {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .redeem-content {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            border: 1px solid var(--accent-color);
        }

        .redeem-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .redeem-title {
            font-size: 1.8rem;
            color: var(--accent-color);
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            color: #b3b3b3;
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: var(--accent-color);
        }

        .coupon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .coupon-card {
            background: var(--secondary-color);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            border: 1px solid var(--accent-color);
            transition: all 0.3s ease;
        }

        .coupon-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
        }

        .discount-amount {
            font-size: 2.5rem;
            color: var(--accent-color);
            font-weight: 700;
            margin: 1rem 0;
        }

        .points-required {
            color: #b3b3b3;
            margin-bottom: 1rem;
        }

        .redeem-button {
            background: var(--gradient-2);
            color: #fff;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-weight: 500;
        }

        .redeem-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
        }

        .redeem-button:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
        }

        .user-points {
            background: var(--secondary-color);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 1px solid var(--accent-color);
        }

        .user-points span {
            color: var(--accent-color);
            font-weight: 600;
            font-size: 1.2rem;
        }

        #reviewModal { display:none; align-items:center; justify-content:center; }
        @media (max-width: 600px) {
            #reviewModal > div { padding: 1rem !important; }
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

    <!-- Add Redeem Modal -->
    <div class="redeem-modal" id="redeemModal">
        <div class="redeem-content">
            <div class="redeem-header">
                <h2 class="redeem-title">Redeem Points</h2>
                <button class="close-modal" onclick="closeRedeemModal()">&times;</button>
            </div>
            <div class="user-points">
                Your Points: <span id="userPoints"><?= $user['points_earned'] ?? 0 ?></span>
            </div>
            <div class="coupon-grid">
                <div class="coupon-card">
                    <h3>5% Discount</h3>
                    <div class="discount-amount">5% OFF</div>
                    <div class="points-required">100 Points</div>
                    <button class="redeem-button" onclick="redeemCoupon(5, 100)" id="redeem5">Redeem</button>
                </div>
                <div class="coupon-card">
                    <h3>15% Discount</h3>
                    <div class="discount-amount">15% OFF</div>
                    <div class="points-required">250 Points</div>
                    <button class="redeem-button" onclick="redeemCoupon(15, 250)" id="redeem15">Redeem</button>
                </div>
                <div class="coupon-card">
                    <h3>30% Discount</h3>
                    <div class="discount-amount">30% OFF</div>
                    <div class="points-required">500 Points</div>
                    <button class="redeem-button" onclick="redeemCoupon(30, 500)" id="redeem30">Redeem</button>
                </div>
                <div class="coupon-card">
                    <h3>60% Discount</h3>
                    <div class="discount-amount">60% OFF</div>
                    <div class="points-required">1000 Points</div>
                    <button class="redeem-button" onclick="redeemCoupon(60, 1000)" id="redeem60">Redeem</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Update the navigation to include redeem link -->
        <div class="nav-links">
            <a href="home.php" class="nav-link">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="#" class="nav-link" onclick="openRedeemModal()">
                <i class="fas fa-gift"></i> Redeem
            </a>
            <a href="profile.php" class="nav-link">
                <i class="fas fa-user"></i> Profile
            </a>
        </div>

        <div class="page-header">
            <h1><i class="fas fa-store"></i> Vendor Shops</h1>
            <p>Discover amazing food shops and share your experiences</p>
        </div>

        <div class="shops-grid">
            <?php while ($shop = $shops->fetch_assoc()): ?>
                <div class="shop-card">
                    <img src="uploads/<?= htmlspecialchars($shop['image']) ?>" alt="Shop Image" class="shop-image">
                    <div class="shop-content">
                        <h2 class="shop-name"><?= htmlspecialchars($shop['shop_name']) ?></h2>
                        <div class="shop-info">
                            <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($shop['location']) ?></p>
                            <p><i class="fas fa-id-card"></i> License: <?= htmlspecialchars($shop['license_no']) ?></p>
                        </div>
                        <div class="menu-preview">
                            <h4><i class="fas fa-utensils"></i> Menu Preview</h4>
                            <p><?= nl2br(htmlspecialchars(substr($shop['menu'], 0, 150))) ?>...</p>
                        </div>
                        <a href="#" class="review-btn" onclick="openReviewModal('<?= htmlspecialchars($shop['shop_name']) ?>', '<?= htmlspecialchars($shop['license_no']) ?>', <?= $shop['id'] ?>); return false;">
                            <i class="fas fa-star"></i> Write a Review
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="reviewModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(30,30,30,0.65); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:rgba(44,62,80,0.98); border-radius:18px; max-width:420px; width:95%; margin:auto; box-shadow:0 8px 32px rgba(76,175,80,0.18); padding:2.2rem 1.5rem; position:relative;">
            <button onclick="closeReviewModal()" style="position:absolute; top:18px; right:18px; background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;"><i class='fas fa-times'></i></button>
            <h2 style="color:#4CAF50; text-align:center; margin-bottom:1.2rem;">Write a Review</h2>
            <form id="modalReviewForm" method="POST" action="submit_review.php" style="display:flex; flex-direction:column; gap:1.1rem;">
                <input type="hidden" name="shop_id" id="modal_shop_id">
                <div class="form-group">
                    <label style="color:#fff; font-weight:600;">Shop Name or License No:</label>
                    <input type="text" id="modal_vendor_identifier" name="vendor_identifier" readonly style="background:rgba(255,255,255,0.08); color:#fff; border:1.5px solid #4CAF50;">
                </div>
                <div class="form-group">
                    <label style="color:#fff; font-weight:600;">Spice Rating</label>
                    <input type="range" id="modal_spice_rating" name="spice_rating" min="0" max="5" step="0.1" value="2.5" oninput="document.getElementById('modal_spice_val').innerText = this.value" style="width:100%;">
                    <div class="rating-labels"><span>0</span><span id="modal_spice_val">2.5</span><span>5</span></div>
                </div>
                <div class="form-group">
                    <label style="color:#fff; font-weight:600;">Hygiene Rating</label>
                    <input type="range" id="modal_hygiene_rating" name="hygine_rating" min="0" max="5" step="0.1" value="2.5" oninput="document.getElementById('modal_hygiene_val').innerText = this.value" style="width:100%;">
                    <div class="rating-labels"><span>0</span><span id="modal_hygiene_val">2.5</span><span>5</span></div>
                </div>
                <div class="form-group">
                    <label style="color:#fff; font-weight:600;">Taste Rating</label>
                    <input type="range" id="modal_taste_rating" name="taste_rating" min="0" max="5" step="0.1" value="2.5" oninput="document.getElementById('modal_taste_val').innerText = this.value" style="width:100%;">
                    <div class="rating-labels"><span>0</span><span id="modal_taste_val">2.5</span><span>5</span></div>
                </div>
                <div class="form-group">
                    <label style="color:#fff; font-weight:600;">Comments</label>
                    <textarea id="modal_comments" name="comments" rows="3" style="resize:vertical; background:rgba(255,255,255,0.08); color:#fff; border:1.5px solid #4CAF50;"></textarea>
                </div>
                <button type="submit" class="submit-btn" style="background:linear-gradient(90deg,#4CAF50,#388E3C); color:#fff; border:none; border-radius:10px; font-weight:700; font-size:1.1rem; padding:0.8rem 2rem; box-shadow:0 4px 16px rgba(76,175,80,0.18); display:flex; align-items:center; gap:0.7rem; margin-top:0.5rem; transition:background 0.3s,transform 0.2s;"><i class="fas fa-paper-plane"></i> Submit Review</button>
            </form>
        </div>
    </div>

    <script>
        // Check for saved theme preference
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
            }
        });

        // Redeem Modal Functions
        function openRedeemModal() {
            document.getElementById('redeemModal').classList.add('active');
            updateRedeemButtons();
        }

        function closeRedeemModal() {
            document.getElementById('redeemModal').classList.remove('active');
        }

        function updateRedeemButtons() {
            const userPoints = parseInt(document.getElementById('userPoints').textContent);
            const buttons = {
                'redeem5': 100,
                'redeem15': 250,
                'redeem30': 500,
                'redeem60': 1000
            };

            for (const [buttonId, pointsRequired] of Object.entries(buttons)) {
                const button = document.getElementById(buttonId);
                if (userPoints < pointsRequired) {
                    button.disabled = true;
                    button.textContent = 'Not Enough Points';
                } else {
                    button.disabled = false;
                    button.textContent = 'Redeem';
                }
            }
        }

        function redeemCoupon(discount, points) {
            if (confirm(`Are you sure you want to redeem ${points} points for a ${discount}% discount coupon?`)) {
                fetch('redeem_coupon.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        discount: discount,
                        points: points
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Congratulations! You've received a ${discount}% discount coupon!\nYour coupon code is: ${data.coupon_code}`);
                        closeRedeemModal();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while redeeming the coupon. Please try again.');
                });
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('redeemModal');
            if (event.target === modal) {
                closeRedeemModal();
            }
        }

        function openReviewModal(shopName, licenseNo, shopId) {
            document.getElementById('reviewModal').style.display = 'flex';
            document.getElementById('modal_vendor_identifier').value = shopName;
            document.getElementById('modal_shop_id').value = shopId;
            document.getElementById('modal_spice_rating').value = 2.5;
            document.getElementById('modal_spice_val').innerText = 2.5;
            document.getElementById('modal_hygiene_rating').value = 2.5;
            document.getElementById('modal_hygiene_val').innerText = 2.5;
            document.getElementById('modal_taste_rating').value = 2.5;
            document.getElementById('modal_taste_val').innerText = 2.5;
            document.getElementById('modal_comments').value = '';
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }

        // Responsive rating value display for modal
        ['modal_spice_rating','modal_hygiene_rating','modal_taste_rating'].forEach(function(id) {
            document.getElementById(id).addEventListener('input', function() {
                document.getElementById(id.replace('rating','val')).innerText = this.value;
            });
        });

        // AJAX review submission for modal
        const modalReviewForm = document.getElementById('modalReviewForm');
        modalReviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(modalReviewForm);
            fetch('submit_review.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                let msgBox = document.getElementById('modalReviewMsg');
                if (!msgBox) {
                    msgBox = document.createElement('div');
                    msgBox.id = 'modalReviewMsg';
                    msgBox.style.margin = '0.5rem 0 0.5rem 0';
                    msgBox.style.textAlign = 'center';
                    modalReviewForm.insertBefore(msgBox, modalReviewForm.firstChild);
                }
                msgBox.innerHTML = data.message;
                msgBox.style.color = data.success ? '#4CAF50' : '#ff6b6b';
                if (data.success) {
                    setTimeout(() => {
                        closeReviewModal();
                        msgBox.innerHTML = '';
                    }, 1200);
                }
            })
            .catch(() => {
                let msgBox = document.getElementById('modalReviewMsg');
                if (!msgBox) {
                    msgBox = document.createElement('div');
                    msgBox.id = 'modalReviewMsg';
                    msgBox.style.margin = '0.5rem 0 0.5rem 0';
                    msgBox.style.textAlign = 'center';
                    modalReviewForm.insertBefore(msgBox, modalReviewForm.firstChild);
                }
                msgBox.innerHTML = '❌ An error occurred. Please try again.';
                msgBox.style.color = '#ff6b6b';
            });
        });
    </script>
</body>
</html>
