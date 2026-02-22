<?php
session_start();
require_once '../config/db.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipe_id = intval($_POST['recipe_id']);
    $rating = intval($_POST['rating']);
    $comments = mysqli_real_escape_string($conn, $_POST['comments']); // Using your 'comments' column
    $sender_id = $_SESSION['user_id'];
    $sender_name = $_SESSION['user_name'] ?? "A user";
    $sender_role = 'user';

    // 2. Insert into your 'feedback' table
    $feedback_sql = "INSERT INTO feedback (user_id, recipe_id, rating, comments) 
                     VALUES ('$sender_id', '$recipe_id', '$rating', '$comments')";
    
    if (mysqli_query($conn, $feedback_sql)) {
        
        // 3. Find the owner of the recipe
        $find_owner = mysqli_query($conn, "SELECT user_id, user_role, name FROM recipe WHERE recipe_id = '$recipe_id'");
        $recipe_data = mysqli_fetch_assoc($find_owner);

        if ($recipe_data) {
            $receiver_id = $recipe_data['user_id'];
            $receiver_role = $recipe_data['user_role']; 
            $recipe_name = $recipe_data['name'];
            
            // 4. Create the detailed message
            $short_comment = (strlen($comments) > 50) ? substr($comments, 0, 47) . "..." : $comments;
            $notif_msg = "$sender_name rated '$recipe_name' $rating/5 stars: \"$short_comment\"";
            $notif_msg = mysqli_real_escape_string($conn, $notif_msg);

            // 5. NOTIFICATION LOGIC
            // If receiver is USER -> Send active notification (is_read = 0)
            // If receiver is ADMIN -> Send silent log (is_read = 1)
            $is_read = ($receiver_role === 'admin') ? 1 : 0;

            $notif_sql = "INSERT INTO notifications (user_id, user_role, sender_id, sender_role, message, type, target_id, is_read) 
                          VALUES ('$receiver_id', '$receiver_role', '$sender_id', '$sender_role', '$notif_msg', 'feedback', '$recipe_id', '$is_read')";
            
            mysqli_query($conn, $notif_sql);
        }

        header("Location: recipe_view.php?id=$recipe_id&status=success");
        exit();
    } else {
        echo "Database Error: " . mysqli_error($conn);
    }
}
?>