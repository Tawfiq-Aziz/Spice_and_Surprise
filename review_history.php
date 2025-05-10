<?php
session_start();
include 'db.php';
?>

<?php include 'header.php'; ?>

<h2>📋 Latest Reviews</h2>

<?php
$reviews = $conn->query("SELECT r.*, u.name AS user_name, s.shop_name
                         FROM review r
                         JOIN user u ON r.user_id = u.user_id
                         JOIN vendor v ON r.vendor_id = v.vendor_id
                         JOIN shop s ON v.vendor_id = s.vendor_id
                         ORDER BY r.date DESC
                         LIMIT 5");

if ($reviews && $reviews->num_rows > 0) {
    while ($row = $reviews->fetch_assoc()) {
        echo "<div style='background:#2a2a3d;padding:15px;border-radius:8px;margin:10px 0'>";
        echo "<strong>" . htmlspecialchars($row['user_name']) . "</strong> on <em>" . htmlspecialchars($row['shop_name']) . "</em><br>";
        echo "🌶️ Spice: " . $row['spice_rating'] . " | 🧼 Hygiene: " . $row['hygine_rating'] . " | 😋 Taste: " . $row['taste_rating'] . "<br>";
        echo "<p>" . htmlspecialchars($row['comments']) . "</p>";
        echo "<small>📅 " . $row['date'] . "</small>";
        echo "</div>";
    }
} else {
    echo "<p>No reviews found.</p>";
}
?>

<?php include 'footer.php'; ?>
