
<?php
session_start();
require_once '../config/db.php';

// 1. Logic Change: Check for admin_id instead of user_id
if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    header("Location: manage_recipes.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$recipe_id = intval($_GET['id']);
$message = "";

// 2. Logic Change: Fetch data ensuring it belongs to the logged-in ADMIN
$recipe_res = mysqli_query($conn, "SELECT * FROM recipe WHERE recipe_id = '$recipe_id' AND admin_id = '$admin_id'");
$recipe = mysqli_fetch_assoc($recipe_res);

if (!$recipe) {
    header("Location: manage_recipes.php");
    exit();
}

// Fetch Current Category association
$cat_assoc_res = mysqli_query($conn, "SELECT category_id FROM categorized_in WHERE recipe_id = '$recipe_id'");
$current_cat = mysqli_fetch_assoc($cat_assoc_res);
$current_category_id = $current_cat ? $current_cat['category_id'] : 0;

// Fetch All Categories for the dropdown
$categories_list = mysqli_query($conn, "SELECT * FROM category ORDER BY name ASC");

// Fetch Existing Nutrition
$nut_res = mysqli_query($conn, "SELECT * FROM nutrition WHERE recipe_id = '$recipe_id'");
$nutrition = mysqli_fetch_assoc($nut_res);

// Fetch Existing Ingredients
$ing_res = mysqli_query($conn, "SELECT u.*, i.name FROM uses u JOIN ingredient i ON u.ingredient_id = i.ingredient_id WHERE u.recipe_id = '$recipe_id'");

// 3. Update Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $instructions = mysqli_real_escape_string($conn, $_POST['instructions']);
    $prep_time = intval($_POST['prep_time']);
    $servings = intval($_POST['servings']);
    $category_id = intval($_POST['category_id']);
    
    // Nutrition
    $calories = intval($_POST['calories']);
    $protein = intval($_POST['protein']);
    $carbs = intval($_POST['carbs']);
    $fat = intval($_POST['fat']);

    // Image Upload
    $image_name = $recipe['image']; 
    if (!empty($_FILES['recipe_image']['name'])) {
        $target_dir = "../assets/uploads/";
        $image_name = time() . "_" . basename($_FILES["recipe_image"]["name"]);
        move_uploaded_file($_FILES["recipe_image"]["tmp_name"], $target_dir . $image_name);
    }

    // Logic Change: Update based on admin_id
    $update_query = "UPDATE recipe SET name='$name', instructions='$instructions', prep_time='$prep_time', servings='$servings', image='$image_name' 
                     WHERE recipe_id = '$recipe_id' AND admin_id = '$admin_id'";

    if (mysqli_query($conn, $update_query)) {
        
        // Update Category Link
        mysqli_query($conn, "DELETE FROM categorized_in WHERE recipe_id = '$recipe_id'");
        mysqli_query($conn, "INSERT INTO categorized_in (recipe_id, category_id) VALUES ('$recipe_id', '$category_id')");

        // Update Nutrition
        mysqli_query($conn, "UPDATE nutrition SET calories='$calories', protein_g='$protein', carbs_g='$carbs', fat_g='$fat' WHERE recipe_id='$recipe_id'");

        // Update Ingredients
        mysqli_query($conn, "DELETE FROM uses WHERE recipe_id = '$recipe_id'");

        if (isset($_POST['ingredient_names'])) {
            foreach ($_POST['ingredient_names'] as $index => $ing_name) {
                if(!empty(trim($ing_name))){
                    $ing_name = mysqli_real_escape_string($conn, $ing_name);
                    
                    $check = mysqli_query($conn, "SELECT ingredient_id FROM ingredient WHERE name = '$ing_name'");
                    if(mysqli_num_rows($check) > 0){
                        $row = mysqli_fetch_assoc($check);
                        $ing_id = $row['ingredient_id'];
                    } else {
                        mysqli_query($conn, "INSERT INTO ingredient (name) VALUES ('$ing_name')");
                        $ing_id = mysqli_insert_id($conn);
                    }

                    $qty = mysqli_real_escape_string($conn, $_POST['quantities'][$index]);
                    $unit = mysqli_real_escape_string($conn, $_POST['units'][$index]);
                    mysqli_query($conn, "INSERT INTO uses (recipe_id, ingredient_id, quantity, unit) VALUES ('$recipe_id', '$ing_id', '$qty', '$unit')");
                }
            }
        }
        header("Location: manage_recipes.php?msg=updated");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Official Recipe | CookEasy</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin/edit_recipe.css">
    

</head>

<body>

    
    <?php include 'admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="form-container">
            <h1>📝 Edit Official Recipe</h1>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Recipe Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($recipe['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php while($cat = mysqli_fetch_assoc($categories_list)): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo ($cat['category_id'] == $current_category_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Prep Time (Min)</label>
                        <input type="number" name="prep_time" value="<?php echo $recipe['prep_time']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Servings</label>
                        <input type="number" name="servings" value="<?php echo $recipe['servings']; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ingredients</label>
                    <div id="ingredient-list">
                        <?php 
                        mysqli_data_seek($ing_res, 0);
                        while($ing = mysqli_fetch_assoc($ing_res)): 
                        ?>
                        <div class="ingredient-row">
                            <input type="text" name="ingredient_names[]" value="<?php echo htmlspecialchars($ing['name']); ?>" placeholder="Ingredient" required>
                            <input type="text" name="quantities[]" value="<?php echo htmlspecialchars($ing['quantity']); ?>" placeholder="Qty">
                            <input type="text" name="units[]" value="<?php echo htmlspecialchars($ing['unit']); ?>" placeholder="Unit">
                            <i class="fa-solid fa-circle-xmark" style="color:#ef4444; cursor:pointer; margin-top:14px;" onclick="this.parentElement.remove()"></i>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <button type="button" class="btn-add-more" onclick="addIngredientRow()">+ Add Ingredient</button>
                </div>

                <div class="form-group">
                    <label>Instructions</label>
                    <textarea name="instructions" rows="5" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Nutrition</label>
                    <div class="grid-4">
                        <input type="number" name="calories" placeholder="kcal" value="<?php echo $nutrition['calories']; ?>">
                        <input type="number" name="protein" placeholder="Prot(g)" value="<?php echo $nutrition['protein_g']; ?>">
                        <input type="number" name="carbs" placeholder="Carb(g)" value="<?php echo $nutrition['carbs_g']; ?>">
                        <input type="number" name="fat" placeholder="Fat(g)" value="<?php echo $nutrition['fat_g']; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Recipe Image (Square)</label>
                    <div class="image-upload-wrapper">
                        <div class="preview-box">
                            <img id="image-preview" src="../assets/uploads/<?php echo $recipe['image']; ?>">
                        </div>
                        <p style="font-size: 0.8rem; color: #64748b;">Leave empty to keep current image</p>
                        <input type="file" name="recipe_image" accept="image/*" onchange="showPreview(event)">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Update Official Recipe</button>
            </form>
        </div>
    </main>

<script>
function showPreview(event) {
    if(event.target.files.length > 0) {
        let src = URL.createObjectURL(event.target.files[0]);
        document.getElementById("image-preview").src = src;
    }
}

function addIngredientRow() {
    const container = document.getElementById('ingredient-list');
    const row = document.createElement('div');
    row.className = 'ingredient-row';
    row.innerHTML = `
        <input type="text" name="ingredient_names[]" placeholder="Ingredient" required>
        <input type="text" name="quantities[]" placeholder="Qty">
        <input type="text" name="units[]" placeholder="Unit">
        <i class="fa-solid fa-circle-xmark" style="color:#ef4444; cursor:pointer; margin-top:14px;" onclick="this.parentElement.remove()"></i>
    `;
    container.appendChild(row);
}
</script>
</body>
</html>