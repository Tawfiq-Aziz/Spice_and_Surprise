<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to redeem coupons']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$discount = $data['discount'] ?? 0;
$points = $data['points'] ?? 0;

// Validate input
if (!in_array($discount, [5, 15, 30, 60]) || !in_array($points, [100, 250, 500, 1000])) {
    echo json_encode(['success' => false, 'message' => 'Invalid discount or points value']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's current points
$stmt = $conn->prepare("SELECT points_earned FROM User WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['points_earned'] < $points) {
    echo json_encode(['success' => false, 'message' => 'Not enough points']);
    exit();
}

// Generate unique coupon code
$coupon_code = strtoupper(substr(md5(uniqid()), 0, 8));

// Insert coupon into database
$stmt = $conn->prepare("INSERT INTO coupons (user_id, discount_percent, points_cost, coupon_code, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iiis", $user_id, $discount, $points, $coupon_code);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Coupon redeemed successfully!',
        'coupon_code' => $coupon_code,
        'discount' => $discount
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error redeeming coupon']);
}

$stmt->close();
$conn->close();
?> 