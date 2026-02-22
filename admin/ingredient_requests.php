 <?php
session_start();
require_once '../config/db.php';

// 1. Security Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// 2. Notification Badge Logic (For Admin's Sidebar/Header)
$count_query = "SELECT COUNT(*) as unread FROM notifications 
                WHERE user_id = $admin_id AND user_role = 'admin' AND is_read = 0";
$count_res = mysqli_query($conn, $count_query);
$unread_count = mysqli_fetch_assoc($count_res)['unread'];

// 3. Fetch Pending Ingredient Requests
$query = "SELECT * FROM ingredient_requests WHERE status = 'pending' ORDER BY request_date DESC";
$requests = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ingredient Requests | Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin/ingredient_requests.css">
    
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="header">
            <div>
                <h1>Ingredient Requests</h1>
                <p style="color: #64748b; margin-top: 5px;">Manage suggestions from your cooking community.</p>
            </div>
            
            
        </div>

        <?php if(mysqli_num_rows($requests) > 0): ?>
            <table class="request-table">
                <thead>
                    <tr>
                        <th>Ingredient</th>
                        <th>Requested By</th>
                        <th>Requested On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($requests)): ?>
                    <tr class="request-row">
                        <td>
                            <div style="font-weight: 700; color: #1e293b; font-size: 1.1rem;"><?php echo htmlspecialchars($row['ingredient_name']); ?></div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 30px; height: 30px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold;">U</div>
                                <span style="font-weight: 600; color: #64748b;">User ID: #<?php echo $row['user_id']; ?></span>
                            </div>
                        </td>
                        <td style="color: #94a3b8; font-size: 0.9rem;">
                            <?php echo date('M d, Y', strtotime($row['request_date'])); ?>
                        </td>
                        <td>
                            <a href="ingredient_process_request.php?id=<?php echo $row['request_id']; ?>&action=approve" class="btn btn-approve">
                                <i class="fa-solid fa-circle-check"></i> Approve
                            </a>
                            <a href="ingredient_process_request.php?id=<?php echo $row['request_id']; ?>&action=reject" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this ingredient?')">
                                <i class="fa-solid fa-circle-xmark"></i> Reject
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-seedling" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 20px;"></i>
                <h3 style="color: #475569; margin: 0;">No Pending Requests</h3>
                <p style="color: #94a3b8;">Everything is processed! New requests will appear here.</p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>