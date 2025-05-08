<?php
session_start();
// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

require 'db.php';

// Get admin stats
$users_count = $conn->query("SELECT COUNT(*) FROM User")->fetch_row()[0];
$vendors_count = $conn->query("SELECT COUNT(*) FROM User WHERE user_type = 'vendor'")->fetch_row()[0];
$active_challenges = $conn->query("SELECT COUNT(*) FROM Challenges WHERE end_date > NOW()")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Spice & Surprise</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Admin-specific styles */
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #666;
            margin-top: 0;
        }
        .stat-card .value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #d35400;
            margin: 10px 0;
        }
        .admin-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .admin-section h2 {
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .action-btn {
            padding: 5px 10px;
            margin: 0 5px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .edit-btn {
            background: #3498db;
            color: white;
        }
        .delete-btn {
            background: #e74c3c;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="admin-container">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?></p>
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="value"><?php echo $users_count; ?></div>
                <p><a href="manage_users.php">View All</a></p>
            </div>
            <div class="stat-card">
                <h3>Vendors</h3>
                <div class="value"><?php echo $vendors_count; ?></div>
                <p><a href="manage_vendors.php">Manage Vendors</a></p>
            </div>
            <div class="stat-card">
                <h3>Active Challenges</h3>
                <div class="value"><?php echo $active_challenges; ?></div>
                <p><a href="manage_challenges.php">Manage Challenges</a></p>
            </div>
        </div>
        
        <!-- Recent Users Section -->
        <div class="admin-section">
            <h2><i class="fas fa-users"></i> Recent Users</h2>
            <?php
            $recent_users = $conn->query("SELECT user_id, name, email, user_type, created_at FROM User ORDER BY created_at DESC LIMIT 5");
            ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $recent_users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo ucfirst($user['user_type']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- System Management Section -->
        <div class="admin-section">
            <h2><i class="fas fa-cog"></i> Quick Actions</h2>
            <div class="action-grid">
                <a href="add_vendor.php" class="action-btn">
                    <i class="fas fa-store"></i> Add New Vendor
                </a>
                <a href="create_challenge.php" class="action-btn">
                    <i class="fas fa-trophy"></i> Create Challenge
                </a>
                <a href="system_settings.php" class="action-btn">
                    <i class="fas fa-sliders-h"></i> System Settings
                </a>
                <a href="backup_db.php" class="action-btn">
                    <i class="fas fa-database"></i> Backup Database
                </a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script>
        // Simple confirmation for delete actions
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>