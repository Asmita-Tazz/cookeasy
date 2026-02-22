<?php
// Note: session_start() should be at the very top of the main pages including this file
require_once '../config/db.php';

// 1. Detect User ID safely
$is_user = isset($_SESSION['user_id']);
$unread_count = 0;

if ($is_user) {
    $sidebar_user_id = $_SESSION['user_id'];
    $sidebar_role = 'user'; // Ensures we only count User-specific alerts

    // 2. Query for unread notifications count
    // This matches your updated table: user_id, user_role, and is_read
    $notif_count_query = "SELECT COUNT(*) as unread_total FROM notifications 
                          WHERE user_id = '$sidebar_user_id' 
                          AND user_role = '$sidebar_role' 
                          AND is_read = 0";
                          
    $notif_count_res = mysqli_query($conn, $notif_count_query);
    
    if ($notif_count_res) {
        $notif_row = mysqli_fetch_assoc($notif_count_res);
        $unread_count = $notif_row['unread_total'];
    }
}

// 3. Helper to detect active page for styling
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel ="stylesheet" href="../assets/css/user/user_sidebar.css">


<div class="sidebar">
    <div class="sidebar-header">
        <h2>CookEasy</h2>
        <p style="font-size: 12px; color: #64748b;">User Workspace</p>
    </div>

    <div class="menu-container">
        <ul class="nav-links">
            <li>
                <a href="user_dashboard.php" class="<?= ($current_page == 'user_dashboard.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="categories.php" class="<?= ($current_page == 'categories.php' || $current_page == 'category_view.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-layer-group"></i> Categories
                </a>
            </li>
            <li>
                <a href="my_recipes.php" class="<?= ($current_page == 'my_recipes.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-utensils"></i> My Recipes
                </a>
            </li>
            <li>
                <a href="fav_recipe.php" class="<?= ($current_page == 'fav_recipe.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-heart"></i> Favourites
                </a>
            </li>
            <li>
                <a href="shopping_list.php" class="<?= ($current_page == 'shopping_list.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-cart-shopping"></i> Shopping List</a>
            </li>
            <li>
          <a href="user_notifications.php" class="<?= ($current_page == 'user_notifications.php') ? 'active' : '' ?>">
        <i class="fa-solid fa-bell"></i> Notifications
        <?php if($unread_count > 0): ?>
            <span class="notif-badge"><?= $unread_count ?></span>
        <?php endif; ?>
    </a>
</li>
            <li>
                <a href="pantry_search.php" class="<?= ($current_page == 'pantry_search.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-magnifying-glass"></i> Pantry Search</a>
            </li>
            <li>
                <a href="profile.php" class="<?= ($current_page == 'profile.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-circle-user"></i> Profile</a>
            </li>
        </ul>
    </div>
    
    <a href="../auth/logout.php" class="logout-btn">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>
</div>