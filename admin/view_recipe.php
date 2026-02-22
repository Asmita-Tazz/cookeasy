<?php
session_start();
require_once '../config/db.php';

// 1. Security: Ensure Admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header("Location: manage_recipes.php");
    exit();
}

$recipe_id = intval($_GET['id']);
$admin_id = $_SESSION['admin_id'];

// 2. Main Query - Joining Nutrition and Category
$query = "SELECT r.*, n.calories, n.protein_g, n.carbs_g, n.fat_g, 
          (SELECT GROUP_CONCAT(name) FROM category JOIN categorized_in ON category.category_id = categorized_in.category_id WHERE categorized_in.recipe_id = r.recipe_id) as category_names,
          u.name as author_user, a.name as author_admin
          FROM recipe r
          LEFT JOIN nutrition n ON r.recipe_id = n.recipe_id
          LEFT JOIN users u ON r.user_id = u.user_id
          LEFT JOIN admin a ON r.admin_id = a.admin_id
          WHERE r.recipe_id = '$recipe_id'";

$result = mysqli_query($conn, $query);
$recipe = mysqli_fetch_assoc($result);

if (!$recipe) {
    die("Recipe Error: No recipe found. <a href='manage_recipes.php'>Return</a>");
}

$original_servings = ($recipe['servings'] > 0) ? $recipe['servings'] : 1;
$is_my_recipe = ($recipe['admin_id'] == $admin_id);

// 3. Fetch Ingredients
$ing_query = "SELECT u.quantity, u.unit, i.name 
              FROM uses u 
              JOIN ingredient i ON u.ingredient_id = i.ingredient_id 
              WHERE u.recipe_id = '$recipe_id'";
$ing_result = mysqli_query($conn, $ing_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($recipe['name']); ?> | Admin Preview</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="../assets/css/admin/view_recipe.css">
   
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="no-print" style="max-width: 900px; margin: auto; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div class="btn-group">
                <a href="manage_recipes.php" style="text-decoration:none; color:#64748b; font-weight: 600;">← Back to Management</a>
                <button onclick="window.print()" class="btn-action btn-pdf">
                    <i class="fa-solid fa-file-pdf"></i> Print / PDF
                </button>
                <?php if($is_my_recipe): ?>
                    <a href="edit_recipe.php?id=<?php echo $recipe_id; ?>" class="btn-action btn-edit">
                        <i class="fa-solid fa-pen"></i> Edit My Recipe
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="recipe-card">
            <img src="../assets/uploads/<?php echo $recipe['image']; ?>" class="hero-img" onerror="this.src='https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=1200'">
            <div class="padding">
                <div class="admin-badge">
                    <i class="fa-solid fa-shield-halved"></i> 
                    <?php echo $recipe['admin_id'] ? 'OFFICIAL CONTENT' : 'USER SUBMISSION'; ?>
                </div>

                <span style="color:var(--primary); font-weight:800; text-transform:uppercase; font-size:0.8rem; display: block;">
                    <?php echo htmlspecialchars($recipe['category_names'] ?: 'General'); ?>
                </span>
                <h1><?php echo htmlspecialchars($recipe['name']); ?></h1>
                
                <p style="color:#64748b; margin-bottom: 20px;">
                    <i class="fa-regular fa-clock"></i> <?php echo $recipe['prep_time']; ?> mins • 
                    <i class="fa-solid fa-user-group"></i> Serves: <?php echo $original_servings; ?> •
                    <i class="fa-solid fa-user-pen"></i> Author: <?php echo htmlspecialchars($recipe['author_admin'] ?: $recipe['author_user']); ?>
                </p>

                <div class="nutrition-bar">
                    <div class="item"><span class="nut-val"><?php echo $recipe['calories'] ?: '0'; ?></span><span class="nut-lbl">Calories</span></div>
                    <div class="item"><span class="nut-val"><?php echo $recipe['protein_g'] ?: '0'; ?>g</span><span class="nut-lbl">Protein</span></div>
                    <div class="item"><span class="nut-val"><?php echo $recipe['carbs_g'] ?: '0'; ?>g</span><span class="nut-lbl">Carbs</span></div>
                    <div class="item"><span class="nut-val"><?php echo $recipe['fat_g'] ?: '0'; ?>g</span><span class="nut-lbl">Fat</span></div>
                </div>

                <div class="grid">
                    <div>
                        <h3 style="border-bottom: 2px solid var(--accent); display: inline-block; padding-bottom: 5px;">Ingredients</h3>
                        <ul style="padding:0; list-style:none;">
                            <?php while($ing = mysqli_fetch_assoc($ing_result)): ?>
                                <li style="padding:10px 0; border-bottom:1px solid #f1f5f9;">
                                    <strong style="color: var(--primary);"><?php echo $ing['quantity']; ?> <?php echo $ing['unit']; ?></strong> <?php echo htmlspecialchars($ing['name']); ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                    <div>
                        <h3 style="border-bottom: 2px solid var(--accent); display: inline-block; padding-bottom: 5px;">Instructions</h3>
                        <p style="line-height:1.8; white-space:pre-line; color: #475569;"><?php echo htmlspecialchars($recipe['instructions']); ?></p>
                    </div>
                </div>

                <div class="feedback-section no-print">
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.8rem;">Audit Reviews</h3>
                    <div class="reviews-container">
                        <?php
                        $fb_query = "SELECT f.*, u.name FROM feedback f 
                                     JOIN users u ON f.user_id = u.user_id 
                                     WHERE f.recipe_id = '$recipe_id' 
                                     ORDER BY f.created_at DESC";
                        $fb_result = mysqli_query($conn, $fb_query);

                        if(mysqli_num_rows($fb_result) > 0):
                            while($review = mysqli_fetch_assoc($fb_result)): ?>
                                <div class="review-item">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <strong style="color:var(--accent);"><?php echo htmlspecialchars($review['name']); ?></strong>
                                        <span style="color: #fbbf24;"><?php echo str_repeat("⭐", $review['rating']); ?></span>
                                    </div>
                                    <p style="color: #475569; margin: 10px 0;"><?php echo htmlspecialchars($review['comments']); ?></p>
                                    <small style="color: #94a3b8;"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                            <?php endwhile;
                        else: ?>
                            <p style="text-align: center; color: #94a3b8; padding: 20px;">No community feedback yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>