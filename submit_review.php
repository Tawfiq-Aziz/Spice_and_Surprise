<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'food_explorer') {
    header("Location: home.php");
    exit();
}

// Pre-fill shop name/license if shop_id is provided
$prefill_shop = '';
$readonly = false;
if (isset($_GET['shop_id'])) {
    $shop_id = intval($_GET['shop_id']);
    $shop_stmt = $conn->prepare("SELECT shop_name, license_no FROM shop WHERE id = ?");
    $shop_stmt->bind_param("i", $shop_id);
    $shop_stmt->execute();
    $shop_result = $shop_stmt->get_result();
    if ($shop_result->num_rows > 0) {
        $shop = $shop_result->fetch_assoc();
        $prefill_shop = $shop['shop_name'] . ' / ' . $shop['license_no'];
        $readonly = true;
    }
    $shop_stmt->close();
}

if (isset(
		$_POST['submit_review']) ||
		(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
) {
    $vendor_identifier = isset($_POST['vendor_identifier']) ? trim($_POST['vendor_identifier']) : null;
    $spice_rating = $_POST['spice_rating'] ?? null;
    $hygine_rating = $_POST['hygine_rating'] ?? null;
    $taste_rating = $_POST['taste_rating'] ?? null;
    $comments = trim($_POST['comments'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (!$vendor_identifier || $spice_rating === null || $hygine_rating === null || $taste_rating === null) {
        $msg = "❌ All fields are required.";
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => false, 'message' => $msg]);
            exit();
        } else {
            $_SESSION['review_error'] = $msg;
            header("Location: submit_review.php" . (isset($_GET['shop_id']) ? '?shop_id=' . intval($_GET['shop_id']) : ''));
            exit();
        }
    }

    $vendor_stmt = $conn->prepare("SELECT vendor_id FROM shop WHERE shop_name = ? OR license_no = ?");
    if (!$vendor_stmt) {
        $msg = "❌ Vendor query failed.";
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => false, 'message' => $msg]);
            exit();
        } else {
            $_SESSION['review_error'] = $msg;
            header("Location: submit_review.php" . (isset($_GET['shop_id']) ? '?shop_id=' . intval($_GET['shop_id']) : ''));
            exit();
        }
    }

    $vendor_stmt->bind_param("ss", $vendor_identifier, $vendor_identifier);
    $vendor_stmt->execute();
    $vendor_result = $vendor_stmt->get_result();

    if ($vendor_result->num_rows > 0) {
        $vendor_id = $vendor_result->fetch_assoc()['vendor_id'];

        $stmt = $conn->prepare("INSERT INTO review (user_id, vendor_id, hygine_rating, comments, date, spice_rating, taste_rating)
                                VALUES (?, ?, ?, ?, CURDATE(), ?, ?)");
        if (!$stmt) {
            $msg = "❌ Review insert failed.";
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                echo json_encode(['success' => false, 'message' => $msg]);
                exit();
            } else {
                $_SESSION['review_error'] = $msg;
                header("Location: submit_review.php" . (isset($_GET['shop_id']) ? '?shop_id=' . intval($_GET['shop_id']) : ''));
                exit();
            }
        }

        $stmt->bind_param("iissdd", $user_id, $vendor_id, $hygine_rating, $comments, $spice_rating, $taste_rating);
        $stmt->execute();
        $stmt->close();

        $msg = "✅ Review submitted successfully!";
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => $msg]);
            exit();
        } else {
            $_SESSION['review_success'] = $msg;
        }
    } else {
        $msg = "❌ Vendor not found.";
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => false, 'message' => $msg]);
            exit();
        } else {
            $_SESSION['review_error'] = $msg;
        }
    }

    $vendor_stmt->close();
    if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        header("Location: submit_review.php" . (isset($_GET['shop_id']) ? '?shop_id=' . intval($_GET['shop_id']) : ''));
        exit();
    }
}
?>

<?php include 'header.php'; ?>

<h2>Submit a Review</h2>

<?php
if (isset($_SESSION['review_success'])) {
    echo "<p style='color:lightgreen'>" . $_SESSION['review_success'] . "</p>";
    unset($_SESSION['review_success']);
}
if (isset($_SESSION['review_error'])) {
    echo "<p style='color:orange'>" . $_SESSION['review_error'] . "</p>";
    unset($_SESSION['review_error']);
}
?>

<form method="post" action="submit_review.php<?php if (isset($_GET['shop_id'])) echo '?shop_id=' . intval($_GET['shop_id']); ?>">
    <label>Shop Name or License No:</label><br>
    <input type="text" name="vendor_identifier" value="<?= htmlspecialchars($prefill_shop) ?>" <?php if ($readonly) echo 'readonly'; ?> required><br><br>

    <label>Spice Rating (0–5):</label><br>
    <input type="number" name="spice_rating" step="0.1" min="0" max="5" required><br><br>

    <label>Hygiene Rating (0–5):</label><br>
    <input type="number" name="hygine_rating" step="0.1" min="0" max="5" required><br><br>

    <label>Taste Rating (0–5):</label><br>
    <input type="number" name="taste_rating" step="0.1" min="0" max="5" required><br><br>

    <label>Comments:</label><br>
    <textarea name="comments" rows="4" cols="50"></textarea><br><br>

    <input type="submit" name="submit_review" value="Submit Review">
</form>

<?php include 'footer.php'; ?>
