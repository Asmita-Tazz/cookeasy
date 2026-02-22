<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$status_type = "";

// --- HANDLE NEW REQUEST ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_ingredient'])) {
    $req_name = mysqli_real_escape_string($conn, trim(ucwords(strtolower($_POST['ingredient_name']))));

    if (!empty($req_name)) {
        // 1. Check if it already exists in the main approved list
        $check_exists = mysqli_query($conn, "SELECT name FROM ingredient WHERE name = '$req_name'");
        
        // 2. Check if this specific user already has a pending request for this
        $check_pending = mysqli_query($conn, "SELECT * FROM ingredient_requests WHERE ingredient_name = '$req_name' AND user_id = $user_id AND status = 'pending'");

        if (mysqli_num_rows($check_exists) > 0) {
            $message = "The ingredient '$req_name' is already available in the system!";
            $status_type = "error";
        } elseif (mysqli_num_rows($check_pending) > 0) {
            $message = "You already have a pending request for '$req_name'.";
            $status_type = "info";
        } else {
            $insert = "INSERT INTO ingredient_requests (user_id, ingredient_name, status) VALUES ($user_id, '$req_name', 'pending')";
            if (mysqli_query($conn, $insert)) {
                $message = "Request for '$req_name' submitted successfully for Admin approval!";
                $status_type = "success";
            }
        }
    }
}

// --- FETCH USER'S REQUEST HISTORY ---
$my_requests = mysqli_query($conn, "SELECT * FROM ingredient_requests WHERE user_id = $user_id ORDER BY request_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ingredient Requests | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel ="stylesheet" href="../assets/css/user/user_ingredients.css">
    
    
</head>
<body>
    <?php include 'user_sidebar.php'; ?>
    
    <main class="main-content">
        <div class="card">
            <h1><i class="fa-solid fa-flask-vial"></i> Ingredient Requests</h1>
            <p style="color: #64748b;">Can't find an ingredient in the Pantry Search? Request it here, and the Admin will review it.</p>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $status_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="user_ingredients.php" method="POST" class="req-form">
                <input type="text" name="ingredient_name" placeholder="Enter ingredient name (e.g., Dragon Fruit)" required>
                <button type="submit" name="request_ingredient" class="btn-req">Submit Request</button>
            </form>
        </div>

        

        <div class="card">
            <h3><i class="fa-solid fa-clock-rotate-left"></i> My Request History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Ingredient</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($my_requests) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($my_requests)): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['ingredient_name']); ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($row['request_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8; padding: 40px;">
                                You haven't requested any ingredients yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>