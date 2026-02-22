<?php
// Note: session_start() should be at the very top of your main admin pages
require_once '../config/db.php';

// 1. Detect Admin ID safely
$is_admin = isset($_SESSION['admin_id']);
$admin_unread_count = 0;

if ($is_admin) {
    $sidebar_admin_id = $_SESSION['admin_id'];
    $sidebar_role = 'admin'; // Specifically target admin notifications

    // 2. Query for unread notifications count
    $notif_count_query = "SELECT COUNT(*) as unread_total FROM notifications 
                          WHERE user_id = '$sidebar_admin_id' 
                          AND user_role = '$sidebar_role' 
                          AND is_read = 0";
                          
    $notif_count_res = mysqli_query($conn, $notif_count_query);
    
    if ($notif_count_res) {
        $notif_row = mysqli_fetch_assoc($notif_count_res);
        $admin_unread_count = $notif_row['unread_total'];
    }
}

// 3. Helper to detect active page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="../assets/css/admin/admin_sidebar.css">


<div class="sidebar">
    <div class="sidebar-header">
        <h2>CookEasy</h2>
        <p style="font-size: 12px; color: #64748b;">Admin Panel</p>
    </div>
    <ul class="nav-links">
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        
        <li><a href="admin_dashboard.php" class="<?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>

        <li><a href="manage_users.php" class="<?= ($current_page == 'manage_users.php') ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Users</a></li>

        <li><a href="manage_recipes.php" class="<?= ($current_page == 'manage_recipes.php') ? 'active' : '' ?>"><i class="fa-solid fa-utensils"></i> Recipes</a></li>

        <li><a href="ingredient_requests.php" class="<?= ($current_page == 'ingredient_requests.php') ? 'active' : '' ?>"><i class="fa-solid fa-flask"></i> Ingredient Requests</a></li>

        <li><a href="categories.php" class="<?= ($current_page == 'categories.php') ? 'active' : '' ?>"><i class="fa-solid fa-layer-group"></i> Categories</a></li>

        
        
        
    </ul>
    
    <a href="../auth/logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>