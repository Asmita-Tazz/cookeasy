<?php
session_start();
require_once '../config/db.php';

// 1. Security Check: Ensure only an Admin can access this logic
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Validate incoming data
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: ingredient_requests.php");
    exit();
}

$request_id = intval($_GET['id']);
$action = $_GET['action'];

// 3. Fetch the specific request to get the User ID and Ingredient Name
// Using prepared statement for the select
$stmt = $conn->prepare("SELECT user_id, ingredient_name FROM ingredient_requests WHERE request_id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$req_result = $stmt->get_result();
$request = $req_result->fetch_assoc();
$stmt->close();

if ($request) {
    $ing_name = $request['ingredient_name'];
    $target_user_id = $request['user_id'];
    $notification_msg = "";

    if ($action === 'approve') {
        // A. Check if the ingredient already exists
        $stmt = $conn->prepare("SELECT ingredient_id FROM ingredient WHERE name = ?");
        $stmt->bind_param("s", $ing_name);
        $stmt->execute();
        $check_exists = $stmt->get_result();
        $stmt->close();
        
        if ($check_exists->num_rows == 0) {
            // B. Insert into main ingredient table
            $stmt = $conn->prepare("INSERT INTO ingredient (name) VALUES (?)");
            $stmt->bind_param("s", $ing_name);
            $stmt->execute();
            $stmt->close();
        }
        
        // C. Update the request status
        $stmt = $conn->prepare("UPDATE ingredient_requests SET status = 'approved' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
        
        $notification_msg = "Your request for '$ing_name' has been approved and added to the system!";
        
    } elseif ($action === 'reject') {
        // D. Mark the request as rejected
        $stmt = $conn->prepare("UPDATE ingredient_requests SET status = 'rejected' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
        
        $notification_msg = "Your request for '$ing_name' was reviewed and unfortunately rejected.";
    }

    // 4. TRIGGER NOTIFICATION
    if (!empty($notification_msg)) {
        $user_role = 'user';
        $is_read = 0;
        // This fixes the Fatal Error by handling quotes automatically
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, user_role, message, is_read) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $target_user_id, $user_role, $notification_msg, $is_read);
        $stmt->execute();
        $stmt->close();
    }
}

// 5. Redirect back
header("Location: ingredient_requests.php?msg=processed");
exit();