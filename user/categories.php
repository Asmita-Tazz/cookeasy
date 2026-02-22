<?php
session_start();
require_once '../config/db.php';

// Fetch categories and count how many recipes are in each
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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
   <link rel ="stylesheet" href="../assets/css/user/categories.css">
   
</head>
<body>
    <?php include 'user_sidebar.php'; ?>
    
    <main class="main-content">
        <div class="gallery-header">
            <h1>Cuisine Gallery</h1>
            <p>Discover recipes organized by regions, diets, and meal types.</p>
        </div>

        <div class="category-grid">
            <?php if(mysqli_num_rows($categories) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($categories)): ?>
                <a href="category_view.php?id=<?php echo $row['category_id']; ?>" class="cat-card">
                    <div class="img-box">
                        <img src="../assets/uploads/<?php echo $row['image']; ?>" onerror="this.src='../assets/uploads/default_cat.jpg'">
                        <span class="recipe-badge"><?php echo $row['count']; ?> Recipes</span>
                    </div>
                    <div class="cat-details">
                        <h2><?php echo strtolower($row['name']); ?></h2>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                    </div>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-msg">
                    <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 10px;"></i>
                    <p>No categories found yet. Check back soon!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>