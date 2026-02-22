
<?php
session_start();
require_once '../config/db.php';

// 1. Security: Admin Only
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$current_admin_id = $_SESSION['admin_id'];

// 2. Action Logic (Approve, Reject, Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    // Fetch recipe details first to get the owner and name for notifications
    $res = mysqli_query($conn, "SELECT name, user_id FROM recipe WHERE recipe_id = $id");
    $recipe_data = mysqli_fetch_assoc($res);
    
    if ($recipe_data) {
        $u_id = $recipe_data['user_id'];
        $r_name = $recipe_data['name'];

        if ($action === 'approve') {
            mysqli_query($conn, "UPDATE recipe SET status = 'approved' WHERE recipe_id = $id");
            
            $msg = mysqli_real_escape_string($conn, "Great news! Your recipe '$r_name' has been approved and is now live.");
            
            // UPDATED: Added sender info and target_id
            mysqli_query($conn, "INSERT INTO notifications (user_id, user_role, sender_id, sender_role, message, type, target_id) 
                                VALUES ('$u_id', 'user', '$current_admin_id', 'admin', '$msg', 'approval', '$id')");
            
        } elseif ($action === 'reject') {
            mysqli_query($conn, "UPDATE recipe SET status = 'rejected' WHERE recipe_id = $id");
            
            $msg = mysqli_real_escape_string($conn, "Your recipe '$r_name' was rejected. Please check our guidelines and try again.");
            
            // UPDATED: Added sender info and target_id
            mysqli_query($conn, "INSERT INTO notifications (user_id, user_role, sender_id, sender_role, message, type, target_id) 
                                VALUES ('$u_id', 'user', '$current_admin_id', 'admin', '$msg', 'system', '$id')");
            
        } elseif ($action === 'delete') {
            mysqli_query($conn, "DELETE FROM recipe WHERE recipe_id = $id");
        }
    }
    
    header("Location: manage_recipes.php?msg=" . $action);
    exit();
}

// 3. Fetch all recipes (The rest of your code remains the same...)
$query = "SELECT r.*, u.name as user_name, a.name as admin_name 
          FROM recipe r 
          LEFT JOIN users u ON r.user_id = u.user_id 
          LEFT JOIN admin a ON r.admin_id = a.admin_id
          ORDER BY r.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage All Recipes | Admin</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin/manage_recipes.css">
   
</head>
<body>
    <?php include __DIR__ . '/admin_sidebar.php'; ?>
    
    <div class="main-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <div>
                <h1 style="margin:0;">Recipe Management</h1>
                <p style="color:#64748b; margin-top:5px;">Manage official content and moderate user submissions.</p>
            </div>
            <a href="add_recipe.php" class="btn" style="background:var(--blue); color:white; padding:12px 24px; font-size:15px;">
                <i class="fa-solid fa-plus"></i> Create Official Recipe
            </a>
        </div>

        <?php while($row = mysqli_fetch_assoc($result)): 
            $is_my_recipe = ($row['admin_id'] == $current_admin_id);
            $is_user_submission = !empty($row['user_id']);
        ?>
            <div class="recipe-card <?= $row['admin_id'] ? 'is-official' : 'is-user' ?>">
                <div>
                    <span class="owner-label">
                        <?php if($row['admin_id']): ?>
                            <i class="fa-solid fa-crown" style="color:var(--orange)"></i> 
                            <?= $is_my_recipe ? 'Your Official Recipe' : 'Admin: ' . htmlspecialchars($row['admin_name']) ?>
                        <?php else: ?>
                            <i class="fa-solid fa-user"></i> User Submission: <?= htmlspecialchars($row['user_name']) ?>
                        <?php endif; ?>
                    </span>
                    <h3 style="margin:8px 0; color:#1e293b;"><?= htmlspecialchars($row['name']) ?></h3>
                    <span class="badge status-<?= $row['status'] ?>"><?= $row['status'] ?></span>
                </div>
                
                <div class="action-buttons">
                    <?php if($is_user_submission): ?>
                        <a href="../user/recipe_view.php?id=<?= $row['recipe_id'] ?>" class="btn btn-view-user" title="Review User Submission">
                            <i class="fa-solid fa-magnifying-glass"></i> Review User
                        </a>

                        <?php if($row['status'] === 'pending'): ?>
                            <a href="manage_recipes.php?action=approve&id=<?= $row['recipe_id'] ?>" class="btn btn-approve">Approve</a>
                            <a href="manage_recipes.php?action=reject&id=<?= $row['recipe_id'] ?>" class="btn btn-reject">Reject</a>
                        <?php endif; ?>
                        
                        <a href="manage_recipes.php?action=delete&id=<?= $row['recipe_id'] ?>" 
                           class="btn btn-delete" 
                           onclick="return confirm('Permanently delete this user submission?')">
                            <i class="fa-solid fa-trash"></i>
                        </a>

                    <?php elseif($is_my_recipe): ?>
                        <a href="view_recipe.php?id=<?= $row['recipe_id'] ?>" class="btn btn-view-official" title="View Your Official Recipe">
                            <i class="fa-solid fa-file-lines"></i> View Official
                        </a>

                        <a href="edit_recipe.php?id=<?= $row['recipe_id'] ?>" class="btn btn-edit">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </a>
                        <a href="manage_recipes.php?action=delete&id=<?= $row['recipe_id'] ?>" 
                           class="btn btn-delete" 
                           onclick="return confirm('Delete your official recipe?')">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>