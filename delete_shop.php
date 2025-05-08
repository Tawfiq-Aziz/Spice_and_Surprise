<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: login.php");
    exit();
}

$shop_id = $_POST['shop_id'];
$vendor_id = $_SESSION['user_id'];

// Make sure vendor owns this shop
$query = "DELETE FROM shops WHERE id = ? AND vendor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $shop_id, $vendor_id);
$stmt->execute();

header("Location: vendor_dashboard.php");
exit();
?>
