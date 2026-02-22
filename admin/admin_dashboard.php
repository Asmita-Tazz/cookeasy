<?php
session_start();
require_once '../config/db.php';

// Security Check: Ensure only Admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch basic stats for the Overview cards
$user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];
$recipe_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM recipe"))['total'];
$pending_reqs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM ingredient_requests WHERE status = 'pending'"))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" >
    <link rel="stylesheet" href="../assets/css/admin/admin_dashboard.css">
    
</head>
<body>

    <?php include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋</h1>
            <div class="admin-profile">
                Role: <span style="color: #0ea5e9;"><?php echo strtoupper($_SESSION['role']); ?></span>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $user_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Recipes Posted</h3>
                <p><?php echo $recipe_count; ?></p>
            </div>
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <h3>Pending Requests</h3>
                <p><?php echo $pending_reqs; ?></p>
            </div>
        </div>

        <div class="action-section">
            <h2>Recent Ingredient Requests</h2>
            <table>
                <thead>
                    <tr>
                        <th>Ingredient</th>
                        <th>Requested By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $req_query = "SELECT r.*, u.name as user_name FROM ingredient_requests r JOIN users u ON r.user_id = u.user_id WHERE r.status = 'pending' LIMIT 5";
                    $req_result = mysqli_query($conn, $req_query);
                    
                    if(mysqli_num_rows($req_result) > 0) {
                        while($row = mysqli_fetch_assoc($req_result)) {
                            echo "<tr>
                                    <td>{$row['ingredient_name']}</td>
                                    <td>{$row['user_name']}</td>
                                    <td>".date('M d, Y', strtotime($row['request_date']))."</td>
                                    <td><span class='badge badge-pending'>Pending</span></td>
                                    <td><a href='ingredient_requests.php?id={$row['request_id']}' style='color:#0ea5e9; text-decoration:none;'>Review</a></td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center;'>No pending requests!</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>