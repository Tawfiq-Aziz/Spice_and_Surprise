<?php
require 'db.php';

$shop_name = $_GET['shop_name'] ?? '';
$location = $_GET['location'] ?? '';
$license_no = $_GET['license_no'] ?? '';

$query = "SELECT * FROM shop WHERE 1=1";
$params = [];
$types = "";

if (!empty($shop_name)) {
    $query .= " AND shop_name LIKE ?";
    $params[] = "%$shop_name%";
    $types .= "s";
}
if (!empty($location)) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}
if (!empty($license_no)) {
    $query .= " AND license_no LIKE ?";
    $params[] = "%$license_no%";
    $types .= "s";
}

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - Spice & Surprise</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>


    <div class="container">
        <h2 style="text-align: center;"><i class="fas fa-store"></i> Search Results</h2>
        <div class="grid grid-3">
            <?php if ($results->num_rows > 0): ?>
                <?php while ($shop = $results->fetch_assoc()): ?>
                    <div class="card shop-card">
                        <h3><?php echo htmlspecialchars($shop['shop_name']); ?></h3>
                        <p><strong>License:</strong> <?php echo htmlspecialchars($shop['license_no']); ?></p>
                        <p><strong>Menu:</strong> <?php echo htmlspecialchars($shop['menu']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($shop['location']); ?></p>
                        <?php if (!empty($shop['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($shop['image']); ?>" alt="Shop Image" class="shop-image">
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center;">No vendors found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
