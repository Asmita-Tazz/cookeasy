
<?php
session_start();
require_once '../config/db.php';

// 1. Admin Security Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Get Category ID and validate
if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

$cat_id = intval($_GET['id']);

// 3. Fetch Category Details
$cat_query = "SELECT * FROM category WHERE category_id = $cat_id";
$cat_result = mysqli_query($conn, $cat_query);
$category = mysqli_fetch_assoc($cat_result);

if (!$category) {
    die("Category not found.");
}

// 4. Fetch recipes via 'categorized_in' table
$recipe_query = "SELECT r.* FROM recipe r 
                 JOIN categorized_in ci ON r.recipe_id = ci.recipe_id 
                 WHERE ci.category_id = $cat_id 
                 ORDER BY r.recipe_id DESC";
$recipes = mysqli_query($conn, $recipe_query);
$recipe_count = mysqli_num_rows($recipes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($category['name']); ?> | Admin View</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin/category_view.css">
   

</head>
<body>
    <?php include 'admin_sidebar.php'; ?> <main class="main-content">
        <a href="categories.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Management
        </a>

        <div class="category-header">
            <img src="../assets/uploads/<?php echo $category['image']; ?>" class="header-img" onerror="this.src='../assets/uploads/default_cat.jpg'">
            <div class="header-text">
                <h1><?php echo htmlspecialchars($category['name']); ?></h1>
                <p><?php echo htmlspecialchars($category['description']); ?></p>
                <span style="background: #fff4ee; color: var(--orange); padding: 5px 15px; border-radius: 15px; font-size: 0.8rem; font-weight: 800;">
                    <?php echo $recipe_count; ?> TOTAL RECIPES
                </span>
            </div>
        </div>

        <div class="recipe-grid">
            <?php if ($recipe_count > 0): ?>
                <?php while($row = mysqli_fetch_assoc($recipes)): ?>
                    <a href="recipe_view.php?id=<?php echo $row['recipe_id']; ?>" class="recipe-card">
                        <img src="../assets/uploads/<?php echo $row['image']; ?>" class="recipe-img" onerror="this.src='../assets/uploads/default_recipe.jpg'">
                        <div class="recipe-info">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <div class="recipe-meta">
                                <span><i class="fa-regular fa-clock"></i> <?php echo $row['prep_time']; ?> MIN</span>
                                <span><i class="fa-solid fa-utensils"></i> SERVES <?php echo $row['servings']; ?></span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-utensils" style="font-size: 4rem; margin-bottom: 20px;"></i>
                    <h2>No recipes here yet!</h2>
                    <p>There are currently no recipes assigned to "<?php echo htmlspecialchars($category['name']); ?>".</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>