<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) { exit("Unauthorized"); }

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = mysqli_query($conn, "SELECT image FROM category WHERE category_id = $id");
    $row = mysqli_fetch_assoc($res);

    if ($row['image'] != 'default_cat.jpg') {
        $file_path = "../assets/uploads/" . $row['image'];
        if (file_exists($file_path)) { unlink($file_path); }
    }

    mysqli_query($conn, "DELETE FROM categorized_in WHERE category_id = $id");
    mysqli_query($conn, "DELETE FROM category WHERE category_id = $id");
    header("Location: categories.php");
}