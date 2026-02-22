<?php
session_start();
require_once '../config/db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$status_msg = "";
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $status_msg = "<div class='alert success'>🗑️ Recipe removed from your library.</div>";
    } elseif ($_GET['msg'] == 'updated') {
        $status_msg = "<div class='alert success'>✨ Recipe updated successfully!</div>";
    }
}

$query = "SELECT r.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS category_names 
          FROM recipe r
          LEFT JOIN categorized_in ci ON r.recipe_id = ci.recipe_id
          LEFT JOIN category c ON ci.category_id = c.category_id
          WHERE r.user_id = $user_id 
          GROUP BY r.recipe_id
          ORDER BY r.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Recipes | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel ="stylesheet" href="../assets/css/user/my_recipes.css">
    
</head>
<body>
    <?php include 'user_sidebar.php'; ?>
    <main class="main-content">
        <?php echo $status_msg; ?>
        <div class="page-header">
            <h1>My Kitchen Library</h1>
            <a href="recipe_add.php" style="background: var(--accent); color: white; padding: 12px 25px; border-radius: 12px; text-decoration: none; font-weight: 700;">+ Add Recipe</a>
        </div>
        <div class="recipe-grid">
            <?php while($row = mysqli_fetch_assoc($result)): 
                $img_name = $row['image'];
                $check_path = __DIR__ . "/../assets/uploads/" . $img_name;
                $display_path = "../assets/uploads/" . $img_name;

                if (empty($img_name) || !file_exists($check_path)) {
                    $display_path = "https://images.unsplash.com/photo-1495195129352-aec325a55b65?auto=format&fit=crop&w=800&q=60";
                }

                // CHECK IF RECIPE IS IN FAVOURITES TABLE
                $recipe_id = $row['recipe_id'];
                $fav_check = mysqli_query($conn, "SELECT * FROM favourites WHERE user_id = $user_id AND recipe_id = $recipe_id");
                $is_fav = mysqli_num_rows($fav_check) > 0;
            ?>
            <div class="recipe-card">
                <div class="img-box">
                    <img src="<?php echo $display_path; ?>" alt="Recipe Image">
                    <div class="overlay-actions">
                        <a href="fav_toggle.php?id=<?php echo $recipe_id; ?>" class="action-btn fav-btn <?php echo $is_fav ? 'active' : ''; ?>" title="Add to Favorites">
                            <i class="<?php echo $is_fav ? 'fa-solid' : 'fa-regular'; ?>  fa-bookmark"></i>
                        </a>

                        <a href="recipe_view.php?id=<?php echo $row['recipe_id']; ?>" class="action-btn view-btn" title="View Details">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="recipe_edit.php?id=<?php echo $row['recipe_id']; ?>" class="action-btn edit-btn" title="Edit">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <a href="recipe_delete.php?id=<?php echo $row['recipe_id']; ?>" class="action-btn delete-btn" onclick="return confirm('Delete this recipe?')" title="Delete">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </div>
                </div>
                <div class="card-info">
                    <span class="cat-tag"><?php echo htmlspecialchars($row['category_names'] ?: 'Uncategorized'); ?></span>
                    <h3 class="recipe-name">
                        <a href="recipe_view.php?id=<?php echo $row['recipe_id']; ?>">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </a>
                    </h3>
                    <div style="color: #64748b; font-size: 0.85rem;">
                        <i class="fa-regular fa-clock"></i> <?php echo $row['prep_time']; ?> mins • 
                        <i class="fa-solid fa-user-group"></i> <?php echo $row['servings']; ?> serves
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
</body>
</html>