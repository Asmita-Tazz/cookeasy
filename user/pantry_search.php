<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$selected_ingredients = isset($_POST['ingredients']) ? $_POST['ingredients'] : [];
$recipes = [];
$message = "";

// --- FEATURE: Handle Ingredient Request ---
if (isset($_POST['request_ing'])) {
    $req_name = mysqli_real_escape_string($conn, trim(ucwords(strtolower($_POST['req_name']))));
    
    if (!empty($req_name)) {
        // 1. Check if it already exists in main list
        $check_exists = mysqli_query($conn, "SELECT name FROM ingredient WHERE name = '$req_name'");
        // 2. Check if it's already pending
        $check_pending = mysqli_query($conn, "SELECT ingredient_name FROM ingredient_requests WHERE ingredient_name = '$req_name' AND status = 'pending'");

        if (mysqli_num_rows($check_exists) > 0) {
            $message = "<div class='alert alert-info'>'$req_name' is already in the list!</div>";
        } elseif (mysqli_num_rows($check_pending) > 0) {
            $message = "<div class='alert alert-info'>A request for '$req_name' is already pending admin approval.</div>";
        } else {
            $ins = "INSERT INTO ingredient_requests (user_id, ingredient_name, status) VALUES ($user_id, '$req_name', 'pending')";
            if (mysqli_query($conn, $ins)) {
                $message = "<div class='alert alert-success'>Request for '$req_name' sent! Admin will review it.</div>";
            }
        }
    }
}

// --- LOGIC: Pantry Search ---
if (!empty($selected_ingredients)) {
    $ingredient_ids = implode(',', array_map('intval', $selected_ingredients));
    
    // Query finds recipes matching selected ingredients and calculates the match ratio
    $query = "SELECT r.*, COUNT(u.ingredient_id) as match_count,
              (SELECT COUNT(*) FROM uses WHERE recipe_id = r.recipe_id) as total_required
              FROM recipe r
              JOIN uses u ON r.recipe_id = u.recipe_id
              WHERE u.ingredient_id IN ($ingredient_ids)
              GROUP BY r.recipe_id
              ORDER BY match_count DESC, r.name ASC";
              
    $recipes = mysqli_query($conn, $query);
}

// Fetch all approved ingredients for the checklist
$all_ingredients = mysqli_query($conn, "SELECT * FROM ingredient ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pantry Search | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel ="stylesheet" href="../assets/css/user/pantry_search.css">
    
</head>
<body>
    <?php include 'user_sidebar.php'; ?>
    
    <main class="main-content">
        <h1><i class="fa-solid fa-kitchen-set" style="color: var(--primary);"></i> Pantry Search</h1>
        <p style="color: #64748b; margin-bottom: 30px;">Select what you have in your kitchen, and we'll find the best matching recipes!</p>

        <?php echo $message; ?>

        <div class="card">
            <form action="pantry_search.php" method="POST">
                <h3 style="margin-top:0;"><i class="fa-solid fa-list-check"></i> 1. Select your Ingredients</h3>
                <div class="ingredient-grid">
                    <?php while($ing = mysqli_fetch_assoc($all_ingredients)): ?>
                        <label class="ing-label">
                            <input type="checkbox" name="ingredients[]" value="<?php echo $ing['ingredient_id']; ?>" 
                            <?php echo in_array($ing['ingredient_id'], $selected_ingredients) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($ing['name']); ?>
                        </label>
                    <?php endwhile; ?>
                </div>
                <button type="submit" class="btn-action" style="width: 100%; font-size: 1rem;">
                    <i class="fa-solid fa-magnifying-glass"></i> Find Recipes I Can Cook
                </button>
            </form>

            <div class="request-section">
                <h4 style="margin: 0; color: var(--accent);"><i class="fa-solid fa-paper-plane"></i> Missing an ingredient?</h4>
                <p style="font-size: 0.85rem; color: #64748b; margin: 5px 0 15px 0;">Request it to be added to our global kitchen list.</p>
                <form action="pantry_search.php" method="POST" class="request-form">
                    <input type="text" name="req_name" class="input-req" placeholder="Enter missing item (e.g. Avocado)" required>
                    <button type="submit" name="request_ing" class="btn-action btn-secondary">Request Addition</button>
                </form>
            </div>
        </div>

        <div class="recipe-grid">
            <?php if (!empty($selected_ingredients) && mysqli_num_rows($recipes) > 0): ?>
                <?php while($r = mysqli_fetch_assoc($recipes)): 
                    $missing = $r['total_required'] - $r['match_count'];
                ?>
                    <a href="recipe_view.php?id=<?php echo $r['recipe_id']; ?>" class="recipe-card">
                        <img src="../assets/uploads/<?php echo $r['image']; ?>" style="width:100%; height:180px; object-fit:cover;" onerror="this.src='https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=500'">
                        <div style="padding: 20px;">
                            <span class="badge <?php echo ($missing == 0) ? 'badge-match' : 'badge-missing'; ?>">
                                <?php if($missing == 0): ?>
                                    <i class="fa-solid fa-square-check"></i> Perfect Match!
                                <?php else: ?>
                                    <i class="fa-solid fa-circle-info"></i> Missing <?php echo $missing; ?> item(s)
                                <?php endif; ?>
                            </span>
                            <h3 style="margin: 5px 0; font-size: 1.2rem;"><?php echo htmlspecialchars($r['name']); ?></h3>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                <span style="font-size: 0.85rem; color: #64748b;">
                                    <i class="fa-solid fa-layer-group"></i> <?php echo $r['match_count']; ?>/<?php echo $r['total_required']; ?> items
                                </span>
                                <span style="font-size: 0.85rem; color: #64748b;">
                                    <i class="fa-regular fa-clock"></i> <?php echo $r['prep_time']; ?>m
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php elseif (!empty($selected_ingredients)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: white; border-radius: 20px; border: 2px dashed #e2e8f0;">
                    <i class="fa-solid fa-basket-shopping" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;"></i>
                    <h3 style="color: #64748b;">No matching recipes found</h3>
                    <p style="color: #94a3b8;">Try selecting more ingredients or different combinations.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>