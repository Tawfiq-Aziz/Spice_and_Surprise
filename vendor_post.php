<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header("Location: login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];
$shop_name = $_POST['shop_name'];
$license_no = $_POST['license_no'];
$menu = $_POST['menu'];
$location = $_POST['location'];

// Handle image upload
$image_name = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $image_name = basename($_FILES["image"]["name"]);
    $target_dir = "uploads/";
    $target_file = $target_dir . $image_name;
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
}

// Insert into DB
$query = "INSERT INTO shops (vendor_id, shop_name, license_no, menu, location, image) 
          VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("isssss", $vendor_id, $shop_name, $license_no, $menu, $location, $image_name);
$stmt->execute();

header("Location: vendor_dashboard.php");
exit();
?>
