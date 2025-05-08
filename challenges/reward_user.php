<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) exit();

$user_id = $_SESSION['user_id'];
$points = isset($_POST['points']) ? (int)$_POST['points'] : 0;

if ($points > 0) {
    $stmt = $conn->prepare("UPDATE User SET points_earned = points_earned + ? WHERE user_id = ?");
    $stmt->bind_param("ii", $points, $user_id);
    $stmt->execute();
    $stmt->close();
}
?>
