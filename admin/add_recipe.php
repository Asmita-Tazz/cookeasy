<?php
session_start();
require_once '../config/db.php';

// Security: Only Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$message = "";

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
    $image_name = "default_recipe.jpg"; 
    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] == 0) {
        $image_name = time() . "_" . basename($_FILES['recipe_image']['name']);
        $target = "../assets/uploads/" . $image_name;
        
        // Ensure directory exists
        if (!is_dir('../assets/uploads/')) {
            mkdir('../assets/uploads/', 0777, true);
        }
        move_uploaded_file($_FILES['recipe_image']['tmp_name'], $target);
    }

    // 1. Insert as Official (admin_id set, status approved, user_role admin)
    // Removed the broken notification block from here
    $recipe_query = "INSERT INTO recipe (admin_id, name, instructions, prep_time, servings, image, status, user_role) 
                     VALUES ('$admin_id', '$name', '$instructions', '$prep_time', '$servings', '$image_name', 'approved', 'admin')";

    if (mysqli_query($conn, $recipe_query)) {
        $recipe_id = mysqli_insert_id($conn);
        
        // 2. Category
        mysqli_query($conn, "INSERT INTO categorized_in (recipe_id, category_id) VALUES ('$recipe_id', '$category_id')");

        // 3. Nutrition
        mysqli_query($conn, "INSERT INTO nutrition (recipe_id, calories, protein_g, carbs_g, fat_g) 
                             VALUES ('$recipe_id', '$calories', '$protein', '$carbs', '$fat')");

        // 4. Ingredients
        if (isset($_POST['ingredient_names'])) {
            foreach ($_POST['ingredient_names'] as $index => $ing_name) {
                if(!empty(trim($ing_name))){
                    $ing_name = mysqli_real_escape_string($conn, $ing_name);
                    
                    // Check if ingredient exists
                    $check = mysqli_query($conn, "SELECT ingredient_id FROM ingredient WHERE name = '$ing_name'");
                    if(mysqli_num_rows($check) > 0){
                        $ing_id = mysqli_fetch_assoc($check)['ingredient_id'];
                    } else {
                        mysqli_query($conn, "INSERT INTO ingredient (name) VALUES ('$ing_name')");
                        $ing_id = mysqli_insert_id($conn);
                    }
                    
                    $qty = mysqli_real_escape_string($conn, $_POST['quantities'][$index]);
                    $unit = mysqli_real_escape_string($conn, $_POST['units'][$index]);
                    mysqli_query($conn, "INSERT INTO uses (recipe_id, ingredient_id, quantity, unit) 
                                        VALUES ('$recipe_id', '$ing_id', '$qty', '$unit')");
                }
            }
        }
        $message = "<div class='alert success'>✨ Official Recipe Published Successfully!</div>";
    } else {
        $message = "<div class='alert' style='background:#fee2e2; color:#b91c1c; padding:15px; border-radius:15px;'>Error: " . mysqli_error($conn) . "</div>";
    }
}

$cat_result = mysqli_query($conn, "SELECT * FROM category ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Official Recipe | CookEasy Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" >
    <link rel= "stylesheet" href="../assets/css/admin/add_recipe.css" >
</head>
<body>
    
    <?php include __DIR__ . '/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="form-container">
            <h1>🍳 Add Official Recipe</h1>
            <?php echo $message; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Recipe Name</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php while($cat = mysqli_fetch_assoc($cat_result)): ?>
                            <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Prep Time (Min)</label>
                        <input type="number" name="prep_time" required>
                    </div>
                    <div class="form-group">
                        <label>Servings</label>
                        <input type="number" name="servings" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ingredients</label>
                    <div id="ingredient-list">
                        <div class="ingredient-row">
                            <input type="text" name="ingredient_names[]" placeholder="Ingredient" required>
                            <input type="text" name="quantities[]" placeholder="Qty">
                            <input type="text" name="units[]" placeholder="Unit">
                            <span></span>
                        </div>
                    </div>
                    <button type="button" onclick="addIngredientRow()" style="width:100%; padding:10px; border-radius:10px; border:1px dashed var(--primary); background:none; color:var(--primary); cursor:pointer;">+ Add More</button>
                </div>

                <div class="form-group">
                    <label>Instructions</label>
                    <textarea name="instructions" rows="5" required></textarea>
                </div>

                <div class="form-group">
                    <label>Nutrition</label>
                    <div class="grid-4">
                        <input type="number" name="calories" placeholder="kcal">
                        <input type="number" name="protein" placeholder="Prot(g)">
                        <input type="number" name="carbs" placeholder="Carb(g)">
                        <input type="number" name="fat" placeholder="Fat(g)">
                    </div>
                </div>

                <div class="form-group">
                    <label>Recipe Image (Square Preview)</label>
                    <div class="image-upload-wrapper">
                        <div class="preview-box" id="preview-box">
                            <img id="image-preview" src="">
                        </div>
                        <input type="file" name="recipe_image" accept="image/*" onchange="showPreview(event)" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Publish Official Content</button>
            </form>
        </div>
    </main>

<script>
function showPreview(event) {
    if(event.target.files.length > 0) {
        let src = URL.createObjectURL(event.target.files[0]);
        let previewImg = document.getElementById("image-preview");
        let previewBox = document.getElementById("preview-box");
        previewImg.src = src;
        previewBox.style.display = "block";
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