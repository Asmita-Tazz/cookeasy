<?php
session_start();
require_once '../config/db.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || !isset($_POST['recipe_id'])) {
    header("Location: my_recipes.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$recipe_id = intval($_POST['recipe_id']);
$user_note = mysqli_real_escape_string($conn, $_POST['user_note']);

// 2. Capture the Scaled Servings from the form
// If not provided, we fall back to 0 and handle it below
$target_servings = isset($_POST['scaled_servings']) ? intval($_POST['scaled_servings']) : 0;

// 3. Fetch Original Recipe Data
$res = mysqli_query($conn, "SELECT name, servings FROM recipe WHERE recipe_id = $recipe_id");
$recipe = mysqli_fetch_assoc($res);

$orig_servings = ($recipe['servings'] > 0) ? $recipe['servings'] : 1;

// If user didn't scale (target is 0), use the original servings
if ($target_servings <= 0) {
    $target_servings = $orig_servings;
}

// Calculate the final Scaling Factor
$scale_factor = $target_servings / $orig_servings;

// Create a descriptive name for the shopping list
$list_name = "List: " . mysqli_real_escape_string($conn, $recipe['name']) . " (For $target_servings)";

// 4. Create the main Shopping List entry
$query = "INSERT INTO shopping_list (user_id, list_name, notes) VALUES ($user_id, '$list_name', '$user_note')";

if (mysqli_query($conn, $query)) {
    $list_id = mysqli_insert_id($conn);

    // 5. Fetch all ingredients used in this recipe
    $ingredients = mysqli_query($conn, "SELECT * FROM uses WHERE recipe_id = $recipe_id");

    while ($row = mysqli_fetch_assoc($ingredients)) {
        $ing_id = $row['ingredient_id'];
        $unit = $row['unit'];
        
        // MATH: Calculate the scaled quantity for the database
        $original_qty = floatval($row['quantity']);
        $scaled_qty = $original_qty * $scale_factor;
        
        // Formatting: Round to 2 decimals or keep as whole number
        $final_qty = (round($scaled_qty, 2) == round($scaled_qty)) ? round($scaled_qty) : number_format($scaled_qty, 2);

        // 6. Insert the scaled ingredient into the list items table (isIncludedIn)
        $insert_item = "INSERT INTO isIncludedIn (list_id, ingredient_id, quantity, unit, is_bought) 
                        VALUES ($list_id, $ing_id, '$final_qty', '$unit', 0)";
        mysqli_query($conn, $insert_item);
    }
    
    // Redirect to your notes/shopping list page with a success message
    header("Location: notes.php?status=success");
    exit();
} else {
    die("Error creating list: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Notes | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel ="stylesheet" href="../assets/css/user/notes.css">
   
</head>
<body>
    <?php include 'user_sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fa-solid fa-clipboard-check"></i> Shopping Notes</h1>
        </div>

        <?php if (mysqli_num_rows($lists) > 0): ?>
            <?php while($list = mysqli_fetch_assoc($lists)): 
                $list_id = $list['list_id'];
                $item_query = "SELECT i.name, incl.* FROM isIncludedIn incl JOIN ingredient i ON incl.ingredient_id = i.ingredient_id WHERE incl.list_id = $list_id";
                $items = mysqli_query($conn, $item_query);
            ?>
            <div class="list-card">
                <div class="list-header">
                    <div>
                        <h2><?php echo htmlspecialchars($list['list_name']); ?></h2>
                        <?php if(!empty($list['notes'])): ?>
                            <div class="note-display">
                                <i class="fa-solid fa-sticky-note"></i> <?php echo htmlspecialchars($list['notes']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <a href="shop_list_delete.php?id=<?php echo $list_id; ?>" class="btn-delete" onclick="return confirm('Delete this shopping note?')">
                        <i class="fa-solid fa-trash-can"></i>
                    </a>
                </div>

                <?php while($item = mysqli_fetch_assoc($items)): ?>
                    <div class="item" onclick="toggleItem(<?php echo $list_id; ?>, <?php echo $item['ingredient_id']; ?>, this)">
                        <div class="checkbox <?php echo $item['is_bought'] ? 'checked' : ''; ?>">
                            <?php if($item['is_bought']) echo '<i class="fa-solid fa-check"></i>'; ?>
                        </div>
                        <span class="item-text <?php echo $item['is_bought'] ? 'strikethrough' : ''; ?>">
                            <span class="qty"><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?></span>
                            <?php echo htmlspecialchars($item['name']); ?>
                        </span>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center; padding-top:100px; color:#94a3b8;">
                <i class="fa-solid fa-note-sticky" style="font-size:3.5rem; margin-bottom:15px; opacity:0.3;"></i>
                <p>No shopping notes yet. Add ingredients from a recipe!</p>
            </div>
        <?php endif; ?>
    </main>

    <script>
    function toggleItem(listId, ingId, element) {
        const checkbox = element.querySelector('.checkbox');
        const text = element.querySelector('.item-text');
        const isBought = checkbox.classList.contains('checked') ? 0 : 1;

        // AJAX update using shop_item_update.php
        fetch('shop_item_update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `list_id=${listId}&ingredient_id=${ingId}&is_bought=${isBought}`
        }).then(response => {
            if(response.ok) {
                checkbox.classList.toggle('checked');
                text.classList.toggle('strikethrough');
                checkbox.innerHTML = isBought ? '<i class="fa-solid fa-check"></i>' : '';
            }
        });
    }
    </script>
</body>
</html>