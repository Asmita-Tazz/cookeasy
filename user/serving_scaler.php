<?php
session_start();
require_once '../config/db.php';

// 1. Capture Data from the URL
$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$target_servings = isset($_GET['servings']) ? intval($_GET['servings']) : 0;

// Security: Check if user is logged in (User or Admin)
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($recipe_id == 0 || $target_servings <= 0) {
    // If no servings specified, we will default to the original later, 
    // but for now, let's ensure we don't crash.
}

// 2. Fetch Original Recipe & Nutrition Data (UPDATED for Admin)
// We use LEFT JOIN on both users and admin to find the author
$query = "SELECT r.*, n.calories, n.protein_g, n.carbs_g, n.fat_g, 
          u.name as user_author, a.name as admin_author
          FROM recipe r
          LEFT JOIN users u ON r.user_id = u.user_id
          LEFT JOIN admin a ON r.admin_id = a.admin_id
          LEFT JOIN nutrition n ON r.recipe_id = n.recipe_id
          WHERE r.recipe_id = '$recipe_id'";

$result = mysqli_query($conn, $query);
$recipe = mysqli_fetch_assoc($result);

if (!$recipe) {
    die("Recipe not found.");
}

// 3. MATH LOGIC: Determine Scale
$original_servings = ($recipe['servings'] > 0) ? intval($recipe['servings']) : 1;

// If target_servings wasn't passed in URL, default to the original
if ($target_servings <= 0) { $target_servings = $original_servings; }

// Define Scale Factor
$scale_factor = $target_servings / $original_servings;

// RECALCULATE NUTRITION
$scaled_cal   = round(floatval($recipe['calories'] ?? 0) * $scale_factor);
$scaled_prot  = round(floatval($recipe['protein_g'] ?? 0) * $scale_factor, 1);
$scaled_carbs = round(floatval($recipe['carbs_g'] ?? 0) * $scale_factor, 1);
$scaled_fat   = round(floatval($recipe['fat_g'] ?? 0) * $scale_factor, 1);

// 4. Fetch Ingredients
$ing_query = "SELECT u.quantity, u.unit, i.name 
              FROM uses u 
              JOIN ingredient i ON u.ingredient_id = i.ingredient_id 
              WHERE u.recipe_id = '$recipe_id'";
$ing_result = mysqli_query($conn, $ing_query);

// Determine Author Display Name
$display_author = ($recipe['admin_id']) ? "Official: " . $recipe['admin_author'] : $recipe['user_author'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scaled: <?php echo htmlspecialchars($recipe['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel ="stylesheet" href="../assets/css/user/serving_scaler.css">
   
</head>
<body>
    <?php 
    // Conditional Sidebar
    if(isset($_SESSION['admin_id'])) { include '../admin/admin_sidebar.php'; } 
    else { include 'user_sidebar.php'; } 
    ?>
    
    <main class="main-content" style="<?php echo isset($_SESSION['admin_id']) || isset($_SESSION['user_id']) ? 'margin-left: 280px;' : ''; ?>">
        <div class="recipe-card">
            <div class="scale-info-bar">
                <span><i class="fa-solid fa-calculator"></i> Showing Total for <strong><?php echo $target_servings; ?> people</strong></span>
                <p style="margin:0; font-size: 0.8rem; opacity: 0.8;">Author: <?php echo htmlspecialchars($display_author); ?></p>
            </div>

            <img src="../assets/uploads/<?php echo $recipe['image']; ?>" class="hero-img" onerror="this.src='https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=1200'">
            
            <div class="padding">
                <h1 style="margin-top:0;"><?php echo htmlspecialchars($recipe['name']); ?></h1>
                
                <form method="GET" action="serving_scaler.php" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="id" value="<?php echo $recipe_id; ?>">
                    <label>Change Servings:</label>
                    <input type="number" name="servings" value="<?php echo $target_servings; ?>" min="1" style="width: 70px; padding: 5px; border-radius: 8px; border: 1px solid #ddd;">
                    <button type="submit" style="background: var(--primary); color: white; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer;">Update</button>
                </form>

                <div class="nutrition-bar">
                    <div class="item"><span class="nut-val"><?php echo $scaled_cal; ?></span><span class="nut-lbl">Total Calories</span></div>
                    <div class="item"><span class="nut-val"><?php echo $scaled_prot; ?>g</span><span class="nut-lbl">Total Protein</span></div>
                    <div class="item"><span class="nut-val"><?php echo $scaled_carbs; ?>g</span><span class="nut-lbl">Total Carbs</span></div>
                    <div class="item"><span class="nut-val"><?php echo $scaled_fat; ?>g</span><span class="nut-lbl">Total Fat</span></div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px; margin-top: 30px;">
                    <div>
                        <h3 style="border-bottom: 2px solid var(--primary); padding-bottom: 10px;">Scaled Ingredients</h3>
                        <ul style="list-style: none; padding: 0;">
                            <?php while($ing = mysqli_fetch_assoc($ing_result)): 
                                $orig_qty = floatval($ing['quantity']);
                                $new_qty = $orig_qty * $scale_factor;
                                $display_qty = (round($new_qty, 2) == round($new_qty)) ? round($new_qty) : number_format($new_qty, 2);
                            ?>
                                <li style="padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                                    <span class="qty-box"><?php echo $display_qty; ?></span> 
                                    <span style="color: #64748b; font-weight: 600;"><?php echo $ing['unit']; ?></span> 
                                    <?php echo htmlspecialchars($ing['name']); ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                    <div>
                        <h3 style="border-bottom: 2px solid var(--accent); padding-bottom: 10px;">Cooking Instructions</h3>
                        <p style="line-height: 1.8; color: #475569; white-space: pre-line;">
                            <?php echo htmlspecialchars($recipe['instructions']); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>