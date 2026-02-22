<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: my_recipes.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$recipe_id = intval($_GET['id']);

// Check if it already exists in favourites
$check = mysqli_query($conn, "SELECT * FROM favourites WHERE user_id = $user_id AND recipe_id = $recipe_id");

if (mysqli_num_rows($check) > 0) {
    // If it exists, remove it (or toggle status)
    $query = "DELETE FROM favourites WHERE user_id = $user_id AND recipe_id = $recipe_id";
} else {
    // If it doesn't exist, add it
    $query = "INSERT INTO favourites (user_id, recipe_id, status) VALUES ($user_id, $recipe_id, 'active')";
}

if (mysqli_query($conn, $query)) {
    // Redirect back to the page the user came from
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    echo "Error updating favorites: " . mysqli_error($conn);
}
?>