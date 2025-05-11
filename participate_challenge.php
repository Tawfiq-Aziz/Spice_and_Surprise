<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to participate in challenges']);
    exit();
}

include 'db.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$challenge_id = $data['challenge_id'] ?? null;
$challenge_type = $data['challenge_type'] ?? null;

if (!$challenge_id || !$challenge_type) {
    echo json_encode(['success' => false, 'message' => 'Invalid challenge data']);
    exit();
}

// Check if challenge exists and is active
$stmt = $conn->prepare("SELECT c.*, 
                       sc.max_tries,
                       tec.start_time,
                       tec.end_time,
                       tec.max_participants,
                       (SELECT COUNT(*) FROM completes WHERE challenge_id = c.challenge_id) as current_participants
                       FROM challenge c 
                       LEFT JOIN spin_challenge sc ON c.challenge_id = sc.challenge_id
                       LEFT JOIN timed_event_challenge tec ON c.challenge_id = tec.challenge_id
                       WHERE c.challenge_id = ? AND c.is_active = 1");
$stmt->bind_param("i", $challenge_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Challenge not found or inactive']);
    exit();
}

$challenge = $result->fetch_assoc();

// Check if user has already participated
$stmt = $conn->prepare("SELECT 1 FROM completes WHERE challenge_id = ? AND user_id = ?");
$stmt->bind_param("ii", $challenge_id, $_SESSION['user_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already participated in this challenge']);
    exit();
}

// Additional checks based on challenge type
if ($challenge_type === 'Spin') {
    // Check if user has exceeded max tries
    $stmt = $conn->prepare("SELECT COUNT(*) as tries FROM completes WHERE challenge_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $challenge_id, $_SESSION['user_id']);
    $stmt->execute();
    $tries = $stmt->get_result()->fetch_assoc()['tries'];
    
    if ($tries >= $challenge['max_tries']) {
        echo json_encode(['success' => false, 'message' => 'You have reached the maximum number of tries for this challenge']);
        exit();
    }
} elseif ($challenge_type === 'Timed') {
    // Check if challenge is within time window
    $now = new DateTime();
    $start_time = new DateTime($challenge['start_time']);
    $end_time = new DateTime($challenge['end_time']);
    
    if ($now < $start_time) {
        echo json_encode(['success' => false, 'message' => 'This challenge has not started yet']);
        exit();
    }
    
    if ($now > $end_time) {
        echo json_encode(['success' => false, 'message' => 'This challenge has ended']);
        exit();
    }
    
    // Check if max participants reached
    if ($challenge['current_participants'] >= $challenge['max_participants']) {
        echo json_encode(['success' => false, 'message' => 'Maximum number of participants reached']);
        exit();
    }
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert completion record
    $stmt = $conn->prepare("INSERT INTO completes (user_id, challenge_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $_SESSION['user_id'], $challenge_id);
    $stmt->execute();

    // Update user points
    $stmt = $conn->prepare("UPDATE User SET points = points + ? WHERE user_id = ?");
    $stmt->bind_param("ii", $challenge['reward_pts'], $_SESSION['user_id']);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Successfully participated in the challenge',
        'points_earned' => $challenge['reward_pts']
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'An error occurred while participating in the challenge']);
}
?> 