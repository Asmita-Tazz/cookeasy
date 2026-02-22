<?php
session_start();
require_once '../config/db.php';

// Security: Only allow Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all users from the database
$query = "SELECT user_id, name, email, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | CookEasy Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="../assets/css/admin/view_users.css">
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="header-flex">
            <h1>User Management</h1>
            <p style="color: #64748b;">Total Registered Members: <?php echo mysqli_num_rows($result); ?></p>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User Details</th>
                        <th>Email</th>
                        <th>Joined Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar-sm"><?php echo strtoupper(substr($row['name'], 0, 1)); ?></div>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td style="color: #94a3b8; font-size: 0.9rem;">
                            <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                        </td>
                        <td>
                            <a href="../user/profile.php?view_id=<?php echo $row['user_id']; ?>" class="btn-view">
                                <i class="fa-solid fa-eye"></i> View Profile
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>