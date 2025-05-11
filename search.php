<?php
require 'db.php';

$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $query = "SELECT * FROM shop WHERE shop_name LIKE ? OR location LIKE ? OR license_no LIKE ?";
    $param = "%$q%";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $param, $param, $param);
    $stmt->execute();
    $results = $stmt->get_result();
} else {
    // No query provided, show no results
    $results = false;
}
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
            <?php if ($results && $results->num_rows > 0): ?>
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
            <?php elseif ($results): ?>
                <p style="text-align: center;">No vendors found matching your criteria.</p>
            <?php else: ?>
                <p style="text-align: center;">Please enter a search term above.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
