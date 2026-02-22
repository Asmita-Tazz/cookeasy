<?php
session_start();
require_once '../config/db.php';

// 1. Security: Only logged-in Users allowed
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = 'user';

// 2. Clear the Badge: Mark as Read when user opens this page
mysqli_query($conn, "UPDATE notifications SET is_read = 1 
                    WHERE user_id = '$user_id' AND user_role = '$user_role'");

// 3. Action: Delete a single notification
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM notifications WHERE id = $del_id AND user_id = '$user_id'");
    header("Location: user_notifications.php");
    exit();
}

// 4. Action: Clear All
if (isset($_POST['clear_all'])) {
    mysqli_query($conn, "DELETE FROM notifications WHERE user_id = '$user_id' AND user_role = '$user_role'");
    header("Location: user_notifications.php");
    exit();
}

// 5. Fetch User-Specific Notifications
$query = "SELECT * FROM notifications 
          WHERE user_id = '$user_id' AND user_role = '$user_role' 
          ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Notifications | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel ="stylesheet" href="../assets/css/user/user_notifications.css">
    
</head>
<body>

    <?php include 'user_sidebar.php'; ?>

    <div class="main-container">
        <div class="header-box">
            <h1 class="page-title">Notifications</h1>
            <?php if (mysqli_num_rows($result) > 0): ?>
            <form method="POST">
                <button type="submit" name="clear_all" class="clear-btn" onclick="return confirm('Clear all notifications?')">
                    <i class="fa-solid fa-broom"></i> Clear All
                </button>
            </form>
            <?php endif; ?>
        </div>

        <div class="notif-card">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="notif-row">
                        <div class="icon-circle <?= 'type-'.$row['type'] ?>">
                            <?php 
                                $icon = "fa-bell";
                                if($row['type'] == 'feedback') $icon = "fa-star";
                                if($row['type'] == 'approval') $icon = "fa-check-double";
                            ?>
                            <i class="fa-solid <?= $icon ?>"></i>
                        </div>
                        
                        <div class="body-wrap">
                            <div class="message-text"><?= htmlspecialchars($row['message']) ?></div>
                            <div class="date-text"><?= date('d M, Y • h:i A', strtotime($row['created_at'])) ?></div>
                        </div>

                        <?php if($row['target_id']): ?>
                            <a href="recipe_view.php?id=<?= $row['target_id'] ?>" class="view-btn">View</a>
                        <?php endif; ?>

                        <a href="?delete=<?= $row['id'] ?>" class="delete-icon" title="Remove">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-data">
                    <i class="fa-solid fa-envelope-open-text fa-4x" style="opacity: 0.1; margin-bottom: 20px;"></i>
                    <p style="font-weight: 600;">Nothing here yet!</p>
                    <p style="font-size: 13px; opacity: 0.6;">We'll notify you when your recipes are reviewed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>