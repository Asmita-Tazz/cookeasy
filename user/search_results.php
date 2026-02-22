<?php
session_start();
require_once '../config/db.php';

// Get current user ID if logged in
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Get the search query and sanitize it
$search_query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';

// SQL Logic: Search by Name, Category, or Ingredients + Favorite status check
$sql = "SELECT DISTINCT r.*, c.name as category_name,
        (SELECT COUNT(*) FROM favourites f WHERE f.recipe_id = r.recipe_id AND f.user_id = '$user_id') as is_fav
        FROM recipe r 
        LEFT JOIN categorized_in ci ON r.recipe_id = ci.recipe_id 
        LEFT JOIN category c ON ci.category_id = c.category_id 
        LEFT JOIN uses u ON r.recipe_id = u.recipe_id
        LEFT JOIN ingredient i ON u.ingredient_id = i.ingredient_id
        WHERE r.status = 'approved' 
        AND (r.name LIKE '%$search_query%' 
             OR c.name LIKE '%$search_query%' 
             OR i.name LIKE '%$search_query%')
        ORDER BY r.created_at DESC";

$result = mysqli_query($conn, $sql);
$count = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results for "<?php echo htmlspecialchars($search_query); ?>" | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel ="stylesheet" href="../assets/css/user/search_results.css">
   
</head>
<body>

    <?php include 'user_sidebar.php'; ?>

    <main class="main-content">
        <div class="search-header">
            <h1>Search Results</h1>
            <p class="results-count">
                <?php echo $count; ?> recipes found for <span style="color: var(--primary);">"<?php echo htmlspecialchars($search_query); ?>"</span>
            </p>
        </div>

        <?php if ($count > 0): ?>
            <div class="recipe-grid">
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="recipe-card-wrapper">
                        <button class="fav-btn-modern <?php echo ($row['is_fav'] > 0) ? 'active' : ''; ?>" 
                                onclick="location.href='fav_toggle.php?id=<?php echo $row['recipe_id']; ?>'">
                            <i class="fa-solid fa-bookmark"></i>
                            <span><?php echo ($row['is_fav'] > 0) ? 'Saved' : 'Save'; ?></span>
                        </button>

                        <a href="recipe_view.php?id=<?php echo $row['recipe_id']; ?>" class="recipe-card">
                            <img src="../assets/uploads/<?php echo $row['image']; ?>" 
                                 class="card-img" 
                                 onerror="this.src='../assets/images/default-recipe.jpg'">
                            
                            <div class="card-body">
                                <span class="category-tag">
                                    <?php echo htmlspecialchars($row['category_name'] ?: 'General'); ?>
                                </span>
                                <h3 class="recipe-title">
                                    <?php 
                                        $title = htmlspecialchars($row['name']);
                                        if ($search_query !== '') {
                                            echo str_ireplace($search_query, "<span class='highlight'>$search_query</span>", $title);
                                        } else {
                                            echo $title;
                                        }
                                    ?>
                                </h3>
                                
                                <div class="card-meta">
                                    <span><i class="fa-regular fa-clock"></i> <?php echo $row['prep_time']; ?> mins</span>
                                    <span><i class="fa-solid fa-utensils"></i> <?php echo $row['servings']; ?> servings</span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fa-solid fa-magnifying-glass" style="font-size: 4rem; color: #e2e8f0; margin-bottom: 20px;"></i>
                <h2>Oops! No recipes found.</h2>
                <p style="color: #64748b;">Try searching for different ingredients or generic terms like "Pasta" or "Cake".</p>
                <a href="../index.php" style="color: var(--primary); text-decoration: none; font-weight: 700;">Back to Home</a>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>