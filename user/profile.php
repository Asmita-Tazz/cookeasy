<?php
session_start();
require_once '../config/db.php';

// 1. Identity Logic
$is_admin = isset($_SESSION['admin_id']);
$logged_in_user = $_SESSION['user_id'] ?? null;

// Determine which user we are looking at
if ($is_admin && isset($_GET['view_id'])) {
    // Admin is viewing a specific user
    $target_id = intval($_GET['view_id']);
    $readonly = true; 
} elseif ($logged_in_user) {
    // User is viewing their own profile
    $target_id = $logged_in_user;
    $readonly = false;
} else {
    header("Location: ../auth/login.php");
    exit();
}

// 2. Fetch target user data
$stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $target_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// 3. Fetch user stats
$recipe_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM recipe WHERE user_id = '$target_id'"))['total'];
$feedback_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback WHERE user_id = '$target_id'"))['total'];

// Determine which sidebar to show
$sidebar = $is_admin ? '../admin/admin_sidebar.php' : 'user_sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $readonly ? "Viewing Profile" : "My Profile"; ?> | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel ="stylesheet" href="../assets/css/user/profile.css">
    
</head>
<body>
    <?php include $sidebar; ?>

    <main class="main-content">
        <div class="container">
            <?php if($readonly): ?>
                <div class="badge-admin"><i class="fa-solid fa-shield-halved"></i> Admin View Mode (Read Only)</div>
            <?php endif; ?>

            <h1 style="margin:0;"><?php echo htmlspecialchars($user['name']); ?>'s Profile</h1>
            <p style="color: #64748b; margin-bottom: 30px;">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>

            <?php if(isset($_GET['status']) && $_GET['status'] == 'profile_updated'): ?>
                <div class="alert alert-success">Profile details updated successfully!</div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <h2><?php echo $recipe_count; ?></h2>
                    <span>Recipes Created</span>
                </div>
                <div class="stat-card">
                    <h2><?php echo $feedback_count; ?></h2>
                    <span>Reviews Given</span>
                </div>
            </div>

            <div class="profile-card">
                <h3><i class="fa-solid fa-id-card" style="color: var(--primary);"></i> Account Details</h3>
                <form action="profile_update.php" method="POST">
                    <input type="hidden" name="update_type" value="profile">
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" <?php echo $readonly ? 'disabled' : 'required'; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" <?php echo $readonly ? 'disabled' : 'required'; ?>>
                    </div>

                    <?php if(!$readonly): ?>
                        <button type="submit" class="btn-update">Save Profile Changes</button>
                    <?php endif; ?>
                </form>
            </div>

            <?php if(!$readonly): ?>
            <div class="profile-card">
                <h3><i class="fa-solid fa-lock" style="color: var(--primary);"></i> Change Password</h3>
                <form action="profile.php" method="POST">
                    <input type="hidden" name="update_type" value="password">
                    
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" id="current_password" required>
                        <i class="fa-solid fa-eye password-toggle" onclick="togglePassword('current_password', this)"></i>
                    </div>

                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" id="new_password" required>
                        <i class="fa-solid fa-eye password-toggle" onclick="togglePassword('new_password', this)"></i>
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                        <i class="fa-solid fa-eye password-toggle" onclick="togglePassword('confirm_password', this)"></i>
                    </div>

                    <button type="submit" class="btn-save">Change Password</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <script>
        function togglePassword(inputId, iconElement) {
            const passwordInput = document.getElementById(inputId);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                iconElement.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                passwordInput.type = "password";
                iconElement.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
</body>
</html>