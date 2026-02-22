<?php
session_start();
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $recipe_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // 1. Verify ownership and get image filename before deleting
    $check_query = "SELECT image FROM recipe WHERE recipe_id = $recipe_id AND user_id = $user_id";
    $result = mysqli_query($conn, $check_query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $image_name = $row['image'];

        // 2. Clear associations in junction table FIRST (otherwise deletion will fail)
        mysqli_query($conn, "DELETE FROM categorized_in WHERE recipe_id = $recipe_id");

        // 3. Delete the actual recipe
        $delete_sql = "DELETE FROM recipe WHERE recipe_id = $recipe_id AND user_id = $user_id";
        
        if (mysqli_query($conn, $delete_sql)) {
            // 4. Delete the physical image file from the folder if it's not a default
            if (!empty($image_name) && $image_name != 'default_recipe.jpg') {
                $file_path = "../assets/uploads/" . $image_name;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            header("Location: my_recipes.php?msg=deleted");
            exit();
        }
    }
}
header("Location: my_recipes.php");
exit();