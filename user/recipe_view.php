<?php
session_start();
require_once '../config/db.php';

// 1. Security: Admin OR User
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: my_recipes.php");
    exit();
}

$recipe_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'] ?? 0;

// Main Query - Joining Nutrition and Category
$query = "SELECT r.*, n.calories, n.protein_g, n.carbs_g, n.fat_g, 
          (SELECT GROUP_CONCAT(name) FROM category JOIN categorized_in ON category.category_id = categorized_in.category_id WHERE categorized_in.recipe_id = r.recipe_id) as category_names
          FROM recipe r
          LEFT JOIN nutrition n ON r.recipe_id = n.recipe_id
          WHERE r.recipe_id = '$recipe_id'"; 

$result = mysqli_query($conn, $query);
$recipe = mysqli_fetch_assoc($result);

if (!$recipe) {
    die("Recipe Error: No recipe found. <a href='my_recipes.php'>Return</a>");
}

$original_servings = ($recipe['servings'] > 0) ? $recipe['servings'] : 1;

// Fetch Ingredients
$ing_query = "SELECT u.quantity, u.unit, i.name 
              FROM uses u 
              JOIN ingredient i ON u.ingredient_id = i.ingredient_id 
              WHERE u.recipe_id = '$recipe_id'";
$ing_result = mysqli_query($conn, $ing_query);

// --- NEW NOTIFICATION LOGIC ---
// If the owner is viewing this page, mark notifications for THIS recipe as read
if (isset($_SESSION['user_id']) && $recipe['user_id'] == $user_id) {
    $update_notif = "UPDATE notifications SET is_read = 1 
                     WHERE user_id = '$user_id' AND message LIKE '%" . mysqli_real_escape_string($conn, $recipe['name']) . "%'";
    mysqli_query($conn, $update_notif);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($recipe['name']); ?> | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel ="stylesheet" href="../assets/css/user/recipe_view.css">
   
</head>
<body>
    <?php 
    if (isset($_SESSION['admin_id'])) {
        include '../admin/admin_sidebar.php'; 
    } else {
        include 'user_sidebar.php'; 
    }
    ?>
    <main class="main-content">
        <div class="no-print" style="max-width: 900px; margin: auto; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div class="btn-group">
                <a href="<?php echo isset($_SESSION['admin_id']) ? '../admin/manage_recipes.php' : 'my_recipes.php'; ?>" style="text-decoration:none; color:#64748b; font-weight: 600;">← Back</a>
                <button onclick="window.print()" class="btn-action btn-pdf">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </button>
            </div>
        </div>
        
        <div class="recipe-card">
            <img src="../assets/uploads/<?php echo $recipe['image']; ?>" class="hero-img" onerror="this.src='https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=1200'">
            <div class="padding">
                <span style="color:var(--primary); font-weight:800; text-transform:uppercase; font-size:0.8rem;">
                    <?php echo htmlspecialchars($recipe['category_names'] ?: 'General'); ?>
                </span>
                <h1><?php echo htmlspecialchars($recipe['name']); ?></h1>
                
                <div style="display: flex; align-items: center; flex-wrap: wrap; margin-bottom: 10px;">
                    <p style="color:#64748b; margin: 0;">
                        <i class="fa-regular fa-clock"></i> <?php echo $recipe['prep_time']; ?> mins • 
                        <i class="fa-solid fa-user-group"></i> Original: <?php echo $original_servings; ?>
                    </p>

                    <div class="scaler-ui no-print">
                        <form action="serving_scaler.php" method="GET" style="display: flex; align-items: center; gap: 8px;">
                            <input type="hidden" name="id" value="<?php echo $recipe_id; ?>">
                            <span style="font-size: 0.75rem; font-weight: 700; color: #475569;">Scale to:</span>
                            <input type="number" name="servings" value="<?php echo $original_servings; ?>" min="1" max="100">
                            <button type="submit" class="btn-scale">Update</button>
                        </form>
                    </div>
                </div>

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
                            <?php mysqli_data_seek($ing_result, 0); while($ing = mysqli_fetch_assoc($ing_result)): ?>
                                <li style="padding:10px 0; border-bottom:1px solid #f1f5f9;">
                                    <strong style="color: var(--primary);"><?php echo $ing['quantity']; ?> <?php echo $ing['unit']; ?></strong> <?php echo htmlspecialchars($ing['name']); ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>

                        <div class="shop-note-box no-print">
                            <h3 style="margin-top:0;"><i class="fa-solid fa-pen-to-square"></i> Add Shopping Note</h3>
                            <form action="notes.php" method="POST">
                                <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>">
                                <input type="hidden" name="scaled_servings" value="<?php echo $original_servings; ?>">
                                <textarea name="user_note" class="note-input" rows="3" placeholder="Example: Buy at organic store..."></textarea>
                                <button type="submit" class="btn-action btn-cart">
                                    <i class="fa-solid fa-cart-plus"></i> Add to My Notes
                                </button>
                            </form>
                        </div>
                    </div>
                    <div>
                        <h3 style="border-bottom: 2px solid var(--accent); display: inline-block; padding-bottom: 5px;">Instructions</h3>
                        <p style="line-height:1.8; white-space:pre-line; color: #475569;"><?php echo htmlspecialchars($recipe['instructions']); ?></p>
                    </div>
                </div>

                <div class="feedback-section no-print">
                    <?php if (isset($_SESSION['user_id']) && $recipe['user_id'] == $user_id): ?>
                        <div class="owner-alert">
                            <i class="fa-solid fa-circle-info"></i> You are viewing your own recipe. Below is the feedback from your community.
                        </div>
                    <?php endif; ?>

                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.8rem;">Community Feedback</h3>
                    
                    <?php if (isset($_SESSION['user_id']) && $recipe['user_id'] != $user_id): ?>
                    <div style="background: #f8fafc; padding: 25px; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 30px;">
                        <form action="submit_feedback.php" method="POST">
                            <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>">
                            <label style="font-weight:700; display:block; margin-bottom:8px;">Rate this Recipe</label>
                            <select name="rating" style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; margin-bottom:15px;">
                                <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                                <option value="4">⭐⭐⭐⭐ - Very Good</option>
                                <option value="3">⭐⭐⭐ - Good</option>
                                <option value="2">⭐⭐ - Average</option>
                                <option value="1">⭐ - Poor</option>
                            </select>
                            <textarea name="comments" style="width:100%; padding:15px; border-radius:12px; border:1px solid #cbd5e1; margin-bottom:15px; font-family:inherit;" rows="3" placeholder="Leave a public review for the owner..."></textarea>
                            <button type="submit" class="btn-action" style="background: var(--primary); color: white; width: 100%; justify-content: center;">Send Feedback & Notify Owner</button>
                        </form>
                    </div>
                    <?php endif; ?>

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
                            <p style="text-align: center; color: #94a3b8; padding: 20px;">No reviews yet. Be the first to share!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>