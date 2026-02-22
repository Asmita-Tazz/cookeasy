<?php
session_start();
require_once '../config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// 1. Fetch User Stats
$count_recipes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM recipe WHERE user_id = '$user_id'"))['total'];
$count_notifications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM notifications WHERE user_id = '$user_id' AND is_read = 0"))['total'];
// New: Count Favorites
$count_favs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM favourites WHERE user_id = '$user_id'"))['total'];

// 2. Fetch User's Recipes
$recipes_query = "SELECT r.*, c.name as category_name 
                  FROM recipe r 
                  LEFT JOIN categorized_in ci ON r.recipe_id = ci.recipe_id 
                  LEFT JOIN category c ON ci.category_id = c.category_id 
                  WHERE r.user_id = '$user_id' 
                  ORDER BY r.created_at DESC";
$recipes_result = mysqli_query($conn, $recipes_query);

// 3. New: Fetch User's Favorites
$fav_query = "SELECT r.*, c.name as category_name 
              FROM recipe r 
              INNER JOIN favourites f ON r.recipe_id = f.recipe_id
              LEFT JOIN categorized_in ci ON r.recipe_id = ci.recipe_id 
              LEFT JOIN category c ON ci.category_id = c.category_id 
              WHERE f.user_id = '$user_id' 
              ORDER BY f.created_at DESC LIMIT 4";
$fav_result = mysqli_query($conn, $fav_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel ="stylesheet" href="../assets/css/user/user_dashboard.css">
    
</head>
<body>

    <?php include 'user_sidebar.php'; ?>

    <main class="main-content">
        <div class="welcome-section">
            <h1>Welcome back, <?php echo explode(' ', $user_name)[0]; ?>! 👋</h1>
            <p>Ready to cook something delicious today?</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-utensils"></i></div>
                <div class="stat-info"><h3><?php echo $count_recipes; ?></h3><p>My Recipes</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e11d48;"><i class="fa-solid fa-heart"></i></div>
                <div class="stat-info"><h3><?php echo $count_favs; ?></h3><p>Favorites</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #3b82f6;"><i class="fa-solid fa-bell"></i></div>
                <div class="stat-info"><h3><?php echo $count_notifications; ?></h3><p>New Alerts</p></div>
            </div>
        </div>

        <div class="section-header">
            <h2>My Favorites</h2>
            <a href="fav_recipe.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">View all</a>
        </div>
        <div class="recipe-grid">
            <?php if (mysqli_num_rows($fav_result) > 0): ?>
                <?php while ($fav = mysqli_fetch_assoc($fav_result)): ?>
                    <a href="recipe_view.php?id=<?php echo $fav['recipe_id']; ?>" class="recipe-card">
                        <div class="image-container">
                            <span class="category-tag"><?php echo $fav['category_name'] ?? 'Fresh'; ?></span>
                            <img src="../assets/uploads/<?php echo htmlspecialchars($fav['image']); ?>" 
                                 class="recipe-image" 
                                 onerror="this.src='https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=500'" 
                                 alt="<?php echo $fav['name']; ?>">
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($fav['name']); ?></h3>
                            <div class="recipe-meta">
                                <span><i class="fa-regular fa-clock"></i> <?php echo $fav['prep_time']; ?> min</span>
                                <span style="color: #e11d48;"><i class="fa-solid fa-heart"></i> Saved</span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p style="color: #64748b;">No favorites saved yet. Browse recipes to add some!</p>
                </div>
            <?php endif; ?>
        </div>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 40px 0;">

        <div class="section-header">
            <h2>My Recipe Collection</h2>
            <a href="recipe_add.php" class="btn-add"><i class="fa-solid fa-plus"></i> Create New</a>
        </div>

        <div class="recipe-grid">
            <?php if (mysqli_num_rows($recipes_result) > 0): ?>
                <?php while ($recipe = mysqli_fetch_assoc($recipes_result)): ?>
                    <a href="recipe_view.php?id=<?php echo $recipe['recipe_id']; ?>" class="recipe-card">
                        <div class="image-container">
                            <span class="category-tag"><?php echo $recipe['category_name'] ?? 'Uncategorized'; ?></span>
                            <img src="../assets/uploads/<?php echo htmlspecialchars($recipe['image']); ?>" 
                                 class="recipe-image" 
                                 onerror="this.src='https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=500'" 
                                 alt="<?php echo $recipe['name']; ?>">
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($recipe['name']); ?></h3>
                            <div class="recipe-meta">
                                <span><i class="fa-regular fa-clock"></i> <?php echo $recipe['prep_time']; ?> min</span>
                                <span><i class="fa-solid fa-signal"></i> <?php echo $recipe['difficulty'] ?? 'Easy'; ?></span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-book-open" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 20px;"></i>
                    <p style="color: #64748b;">You haven't added any recipes yet!</p>
                    <a href="add_recipe.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Start cooking now</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>