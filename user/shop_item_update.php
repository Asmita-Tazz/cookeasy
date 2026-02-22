<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and the request is coming via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
    // Collect and sanitize the data sent from the JavaScript fetch
    $list_id = intval($_POST['list_id']);
    $ing_id = intval($_POST['ingredient_id']);
    $status = intval($_POST['is_bought']); // Will be 1 (checked) or 0 (unchecked)
    $user_id = $_SESSION['user_id'];

    /**
     * Security Check: 
     * We verify that the shopping list being updated actually belongs 
     * to the logged-in user to prevent unauthorized database changes.
     */
    $verify_query = "SELECT list_id FROM shopping_list 
                     WHERE list_id = $list_id AND user_id = $user_id";
    $verify_res = mysqli_query($conn, $verify_query);

    if (mysqli_num_rows($verify_res) > 0) {
        // The list is verified. Update the specific ingredient status.
        $update_query = "UPDATE isIncludedIn 
                         SET is_bought = $status 
                         WHERE list_id = $list_id AND ingredient_id = $ing_id";
        
        if (mysqli_query($conn, $update_query)) {
            // Send a 200 OK response back to the JavaScript
            http_response_code(200);
            echo "Status updated successfully.";
        } else {
            // Database error
            http_response_code(500);
            echo "Error updating database.";
        }
    } else {
        // User does not own this list
        http_response_code(403);
        echo "Unauthorized access.";
    }
} else {
    // Not a valid POST request
    http_response_code(400);
    echo "Invalid request.";
}
?>