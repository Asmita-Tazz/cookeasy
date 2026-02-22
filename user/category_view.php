<?php
session_start();
require_once '../config/db.php';

// 1. Get Category ID from URL
if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

$cat_id = intval($_GET['id']);

// 2. Fetch Category Details for the Header
$cat_query = "SELECT * FROM category WHERE category_id = $cat_id";
$cat_result = mysqli_query($conn, $cat_query);
$category = mysqli_fetch_assoc($cat_result);

if (!$category) {
    die("Category not found.");
}

// 3. Handle Search Query via POST to keep URL clean
$search_term = "";
$where_clause = "WHERE ci.category_id = $cat_id";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_POST['search']);
    if (!empty(trim($search_term))) {
        $where_clause .= " AND r.name LIKE '%$search_term%'";
    }
}

// 4. Fetch recipes belonging to this category
$recipe_query = "SELECT r.* FROM recipe r 
                  JOIN categorized_in ci ON r.recipe_id = ci.recipe_id 
                  $where_clause 
                  ORDER BY r.recipe_id DESC";
$recipes = mysqli_query($conn, $recipe_query);
$recipe_count = mysqli_num_rows($recipes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($category['name']); ?> Recipes | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel ="stylesheet" href="../assets/css/user/category_view.css">
   
</head>
<body>
    <?php include 'user_sidebar.php'; ?>

    <main class="main-content">
        <a href="categories.php" class="back-link">
            <i class="fa-solid fa-chevron-left"></i> Back to Gallery
        </a>

        <header class="category-header">
            <img src="../assets/uploads/<?php echo $category['image']; ?>" class="header-img" onerror="this.src='../assets/uploads/default_cat.jpg'">
            <div class="header-text">
                <h1><?php echo htmlspecialchars($category['name']); ?></h1>
                <p><?php echo htmlspecialchars($category['description']); ?></p>
                <span class="recipe-badge">
                    <?php echo $recipe_count; ?> RECIPES DISCOVERED
                </span>
            </div>
        </header>

        <section class="search-section">
            <form action="category_view.php?id=<?php echo $cat_id; ?>" method="POST" class="search-box">
                <input type="text" name="search" placeholder="Search within <?php echo htmlspecialchars($category['name']); ?>..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit" class="search-btn">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </section>

        <div class="recipe-grid">
            <?php if ($recipe_count > 0): ?>
                <?php while($row = mysqli_fetch_assoc($recipes)): ?>
                    <a href="recipe_view.php?id=<?php echo $row['recipe_id']; ?>" class="recipe-card">
                        <div class="recipe-img-box">
                            <img src="../assets/uploads/<?php echo $row['image']; ?>" class="recipe-img" onerror="this.src='../assets/uploads/default_recipe.jpg'">
                        </div>
                        <div class="recipe-info">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <div class="recipe-meta">
                                <span><i class="fa-regular fa-clock"></i> <?php echo $row['prep_time']; ?> MIN</span>
                                <span><i class="fa-solid fa-utensils"></i> <?php echo $row['servings']; ?> SERVES</span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-utensils"></i>
                    <h2>No matching recipes</h2>
                    <p>We couldn't find any recipes for "<?php echo htmlspecialchars($search_term); ?>" in this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>