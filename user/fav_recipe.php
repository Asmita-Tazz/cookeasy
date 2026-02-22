<?php
session_start();
require_once '../config/db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// SQL Query joining 'favourites' (fixed spelling) with 'recipe'
$query = "SELECT r.*, GROUP_CONCAT(c.name SEPARATOR ', ') AS category_names 
          FROM favourites f
          JOIN recipe r ON f.recipe_id = r.recipe_id
          LEFT JOIN categorized_in ci ON r.recipe_id = ci.recipe_id
          LEFT JOIN category c ON ci.category_id = c.category_id
          WHERE f.user_id = $user_id 
          GROUP BY r.recipe_id
          ORDER BY f.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Favourites | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel ="stylesheet" href="../assets/css/user/fav_recipe.css">

</head>
<body>
    <?php include 'user_sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1><i class="fa-solid fa-heart" style="color:var(--heart);"></i> My Favourites</h1>
            <a href="../index.php" style="text-decoration:none; color:var(--primary); font-weight:700;">Find More Recipes →</a>
        </div>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="recipe-grid">
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $img_path = "../assets/uploads/" . $row['image'];
                    $display_img = (!empty($row['image']) && file_exists($img_path)) ? $img_path : "../assets/images/default-recipe.jpg";
                ?>
                    <div style="position: relative;">
                        <button class="unfav-btn" onclick="window.location.href='fav_toggle.php?id=<?php echo $row['recipe_id']; ?>'" title="Remove from Favourites">
                            <i class="fa-solid fa-bookmark"></i>
                        </button>

                        <a href="recipe_view.php?id=<?php echo $row['recipe_id']; ?>" class="recipe-card">
                            <div class="img-box">
                                <img src="<?php echo $display_img; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            </div>

                            <div class="card-info">
                                <span class="cat-tag"><?php echo htmlspecialchars($row['category_names'] ?: 'General'); ?></span>
                                <span class="recipe-name"><?php echo htmlspecialchars($row['name']); ?></span>
                                
                                <div class="meta-info">
                                    <span><i class="fa-regular fa-clock"></i> <?php echo $row['prep_time']; ?>m</span>
                                    <span><i class="fa-solid fa-user-group"></i> Serves <?php echo $row['servings']; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:100px; background: white; border-radius: 20px;">
                <i class="fa-regular fa-heart" style="font-size:4rem; color:#cbd5e1; margin-bottom:20px; display:block;"></i>
                <h2>Your heart is empty!</h2>
                <p style="color: #64748b;">Save recipes you love to find them quickly here.</p>
                <br>
                <a href="../index.php" style="background:var(--primary); color:white; padding:12px 30px; border-radius:12px; text-decoration:none; font-weight:700;">Explore Recipes</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>