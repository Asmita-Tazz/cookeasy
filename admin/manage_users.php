
<?php
session_start();
require_once '../config/db.php';

// 1. Security Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. Handle Block/Unblock Logic
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $new_status = ($_GET['action'] === 'block') ? 'blocked' : 'active';
    
    $update_sql = "UPDATE users SET status = '$new_status' WHERE user_id = $user_id";
    if (mysqli_query($conn, $update_sql)) {
        header("Location: manage_users.php?msg=User status updated to $new_status");
        exit();
    }
}

// 3. Fetch all users
$query = "SELECT u.*, COUNT(r.recipe_id) as recipe_count 
          FROM users u 
          LEFT JOIN recipe r ON u.user_id = r.user_id 
          GROUP BY u.user_id 
          ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="../assets/css/admin/manage_users.css">
   
</head>
<body>

    <?php include __DIR__ . '/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="header-flex">
            <div>
                <h1 style="margin:0;">User Moderation</h1>
                <p style="color: #64748b; margin-top: 5px;">Manage accounts and view community contributions.</p>
            </div>
            
            <a href="view_users.php" class="btn-view-header">
                <i class="fa-solid fa-eye"></i> View Users Profile
            </a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="msg-success"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <div class="action-section">
            <table>
                <thead>
                    <tr>
                        <th>User Details</th>
                        <th>Recipes</th>
                        <th>Joined Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <div style="display:flex; flex-direction:column;">
                                <span style="font-weight:700; color:#0f172a;"><?php echo htmlspecialchars($row['name']); ?></span>
                                <span style="font-size:12px; color:#64748b;"><?php echo htmlspecialchars($row['email']); ?></span>
                            </div>
                        </td>
                        <td><i class="fa-solid fa-utensils" style="color:#94a3b8; margin-right:5px;"></i> <?php echo $row['recipe_count']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $row['status']; ?>">
                                <?php echo strtoupper($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <?php if($row['status'] === 'active'): ?>
                                    <a href="manage_users.php?action=block&id=<?php echo $row['user_id']; ?>" 
                                       class="btn-sm btn-block" 
                                       onclick="return confirm('Are you sure you want to block this user?')">
                                         <i class="fa-solid fa-ban"></i> Block
                                    </a>
                                <?php else: ?>
                                    <a href="manage_users.php?action=unblock&id=<?php echo $row['user_id']; ?>" 
                                       class="btn-sm btn-unblock">
                                         <i class="fa-solid fa-check"></i> Unblock
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>