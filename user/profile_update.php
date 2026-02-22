<?php
session_start();
require_once '../config/db.php';

// Redirect if not logged in or if the form wasn't submitted via POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: settings.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$update_type = $_POST['update_type'];

// --- SECTION 1: UPDATE PROFILE (NAME & EMAIL) ---
if ($update_type === 'profile') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Check if email is already taken by another user
    $check_email = mysqli_query($conn, "SELECT user_id FROM users WHERE email = '$email' AND user_id != '$user_id'");
    if (mysqli_num_rows($check_email) > 0) {
        header("Location: settings.php?status=email_exists");
        exit();
    }

    $query = "UPDATE users SET name = '$name', email = '$email' WHERE user_id = '$user_id'";
    
    if (mysqli_query($conn, $query)) {
        // Update the session name so the Sidebar updates immediately
        $_SESSION['user_name'] = $name; 
        header("Location: settings.php?status=profile_updated");
        exit();
    } else {
        die("Error updating profile: " . mysqli_error($conn));
    }
}

// --- SECTION 2: UPDATE PASSWORD ---
elseif ($update_type === 'password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Fetch the stored password hash for this user
    $res = mysqli_query($conn, "SELECT password FROM users WHERE user_id = '$user_id'");
    $user = mysqli_fetch_assoc($res);

    // 2. Verify the "Current Password" matches what is in the database
    if (password_verify($current_password, $user['password'])) {
        
        // 3. Check if the two new passwords match each other
        if ($new_password === $confirm_password) {
            
            // 4. Hash the new password and update
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = '$new_hashed_password' WHERE user_id = '$user_id'";
            
            if (mysqli_query($conn, $update_query)) {
                header("Location: settings.php?status=password_updated");
                exit();
            }
        } else {
            // New passwords don't match
            header("Location: settings.php?status=pass_mismatch");
            exit();
        }
    } else {
        // Current password entered is wrong
        header("Location: settings.php?status=wrong_pass");
        exit();
    }
}

// Default redirect if update_type is missing
header("Location: settings.php");
exit();
?>