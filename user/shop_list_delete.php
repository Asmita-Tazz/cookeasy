<?php
session_start();
require_once '../config/db.php';

/**
 * We check if:
 * 1. The user is logged in.
 * 2. A specific list ID (id) has been passed in the URL.
 */
if (isset($_SESSION['user_id']) && isset($_GET['id'])) {
    
    $list_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    /**
     * Security Check & Delete:
     * We include 'user_id' in the WHERE clause to ensure a user
     * cannot delete someone else's list by guessing the ID.
     */
    $delete_query = "DELETE FROM shopping_list 
                     WHERE list_id = $list_id AND user_id = $user_id";

    if (mysqli_query($conn, $delete_query)) {
        // Success: Redirect back to the shopping notes page
        header("Location: shopping_list.php?msg=deleted");
        exit();
    } else {
        // SQL Error: Redirect back with an error flag
        header("Location: shopping_list.php?msg=error");
        exit();
    }
} else {
    // If someone tries to access this file directly without an ID
    header("Location: shopping_list.php");
    exit();
}
?>