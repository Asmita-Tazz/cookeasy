<?php
session_start();
require_once 'config/db.php';

// Get current user ID if logged in
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Fetch Featured Recipes
// Using 'favourites' table name as per your PHP file
$featured_query = "SELECT r.*, c.name as category_name,
                    (SELECT COUNT(*) FROM favourites f WHERE f.recipe_id = r.recipe_id AND f.user_id = '$user_id') as is_fav
                   FROM recipe r 
                   LEFT JOIN categorized_in ci ON r.recipe_id = ci.recipe_id 
                   LEFT JOIN category c ON ci.category_id = c.category_id 
                   WHERE r.status = 'approved' 
                   ORDER BY r.created_at DESC LIMIT 6";

$featured_result = mysqli_query($conn, $featured_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CookEasy | Discover & Share Recipes</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <section class="hero">
        <h1>Delicious Starts Here</h1>
        <form action="user/search_results.php" method="GET" class="search-container">
            <input type="text" name="query" placeholder="Search recipes..." required>
            <button type="submit" class="search-btn">Search</button>
        </form>
    </section>

    <div class="content-wrapper">
        <div class="recipe-grid">
            <?php if (mysqli_num_rows($featured_result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($featured_result)): ?>
                    <div class="recipe-card-wrapper">
                        <button class="fav-btn-modern <?php echo ($row['is_fav'] > 0) ? 'active' : ''; ?>" 
                                onclick="location.href='user/fav_toggle.php?id=<?php echo $row['recipe_id']; ?>'">
                            <i class="fa-solid fa-bookmark"></i>
                            <span><?php echo ($row['is_fav'] > 0) ? 'Saved' : 'Save'; ?></span>
                        </button>

                        <a href="user/recipe_view.php?id=<?php echo $row['recipe_id']; ?>" class="recipe-card">
                            <img src="assets/uploads/<?php echo $row['image']; ?>" class="card-image" onerror="this.src='assets/images/default-recipe.jpg'"> 
                            <div class="card-content">
                                <span class="card-tag"><?php echo $row['category_name'] ?: 'General'; ?></span>
                                <h3 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                                <div class="card-footer">
                                    <span><i class="fa-regular fa-clock"></i> <?php echo $row['prep_time']; ?>m</span>
                                    <span>Chef Verified</span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>
</html>