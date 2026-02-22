<?php
session_start();
require_once '../config/db.php';

// 1. SECURITY: Only allow Admin access
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. FETCH DATA
if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

$id = intval($_GET['id']);
$res = mysqli_query($conn, "SELECT * FROM category WHERE category_id = $id");
$cat = mysqli_fetch_assoc($res);

if (!$cat) {
    die("Category not found.");
}

// 3. UPDATE LOGIC
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $desc = mysqli_real_escape_string($conn, $_POST['desc']);
    
    if (!empty($_FILES['image']['name'])) {
        // Optional: Delete old image from server to save space
        if ($cat['image'] != 'default_cat.jpg' && file_exists("../assets/uploads/" . $cat['image'])) {
            unlink("../assets/uploads/" . $cat['image']);
        }

        $img = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/uploads/" . $img);
        
        $sql = "UPDATE category SET name='$name', description='$desc', image='$img' WHERE category_id=$id";
    } else {
        $sql = "UPDATE category SET name='$name', description='$desc' WHERE category_id=$id";
    }
    
    if(mysqli_query($conn, $sql)) {
        header("Location: categories.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Category | CookEasy Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin/category_edit.css">

</head>
<body>
    <div class="edit-container">
        <h2>Edit Category</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Category Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
            
            <label>Current Image</label>
            <div class="current-img-wrapper">
                <img src="../assets/uploads/<?php echo $cat['image']; ?>" class="current-img">
                <p style="margin: 5px 0 0 0; font-size: 0.7rem; color: var(--orange);">CURRENT PREVIEW</p>
            </div>
            
            <label>Upload New Image</label>
            <input type="file" name="image" style="margin-bottom: 20px;">

            <label>Description</label>
            <textarea name="desc" rows="3"><?php echo htmlspecialchars($cat['description']); ?></textarea>
            
            <button type="submit" class="update-btn">Save Changes</button>
            <a href="categories.php" class="cancel-link">← Cancel and Go Back</a>
        </form>
    </div>
</body>
</html>