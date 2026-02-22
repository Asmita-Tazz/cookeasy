<?php
session_start();
require_once '../config/db.php';

// Security: Only Admin
if (!isset($_SESSION['admin_id'])) { header("Location: ../login.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    
    $image = "default_cat.jpg";
    if (!empty($_FILES['cat_image']['name'])) {
        $image = time() . "_" . $_FILES['cat_image']['name'];
        move_uploaded_file($_FILES['cat_image']['tmp_name'], "../assets/uploads/" . $image);
    }

    $sql = "INSERT INTO category (name, description, image) VALUES ('$name', '$desc', '$image')";
    mysqli_query($conn, $sql);
    header("Location: categories.php");
}

$query = "SELECT c.*, COUNT(ci.recipe_id) as count 
          FROM category c 
          LEFT JOIN categorized_in ci ON c.category_id = ci.category_id 
          GROUP BY c.category_id";
$categories = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cuisine Gallery | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin/categories.css">
   
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    
    <main class="main-content">
        <div class="gallery-header">
            <h1>Cuisine Gallery</h1>
            <p>Organize recipes by regions, diets, or meal types.</p>
        </div>

        <div class="form-section">
            <h3 style="color:var(--orange); margin-top:0; font-size: 1.5rem;">Create New Category</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" placeholder="e.g. Mediterranean" required>
                    </div>
                    <div class="form-group">
                        <label>Display Image</label>
                        <input type="file" name="cat_image" id="cat_image" onchange="previewImage(event)">
                        <div id="preview-container">
                            <img id="image-preview" src="#" alt="Preview">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Describe the flavors of this category..."></textarea>
                </div>
                <button type="submit" name="save_category" class="save-btn">Save Category</button>
            </form>
        </div>

        <div class="category-grid">
            <?php while($row = mysqli_fetch_assoc($categories)): ?>
            <div class="cat-card">
                <div class="img-box">
                    <img src="../assets/uploads/<?php echo $row['image']; ?>">
                    <span class="recipe-badge"><?php echo $row['count']; ?> Recipes</span>
                </div>
                <div class="cat-details">
                    <a href="category_view.php?id=<?php echo $row['category_id']; ?>" style="text-decoration:none;">
                        <h2><?php echo strtolower($row['name']); ?></h2>
                    </a>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    
                    <div class="card-actions">
                        <a href="category_edit.php?id=<?php echo $row['category_id']; ?>" class="action-btn edit-link">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a href="category_delete.php?id=<?php echo $row['category_id']; ?>" class="action-btn delete-link" onclick="return confirm('Delete category?')">
                            <i class="fa-solid fa-trash-can"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script>
        function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const output = document.getElementById('image-preview');
        const container = document.getElementById('preview-container');
        
        // This replaces the FileReader logic
        output.src = URL.createObjectURL(file);
        
        container.style.display = 'block';
    }
}
    </script>
</body>
</html>