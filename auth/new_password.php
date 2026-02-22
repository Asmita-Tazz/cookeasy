
<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: forgot_password.php");
    exit();
}

if (isset($_POST['reset_password'])) {
    $email = $_SESSION['reset_email'];
    $new_pass = $_POST['password'];
    $conf_pass = $_POST['confirm_password'];

    if ($new_pass !== $conf_pass) {
        $error = "Passwords do not match!";
    } elseif (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_otp = NULL, otp_expiry = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            unset($_SESSION['reset_email']);
            unset($_SESSION['otp_verified']);
            header("Location: login.php?reset=success");
            exit();
        } else {
            $error = "Database error. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set New Password | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link rel="stylesheet" href="../assets/css/auth/new_password.css">
  
</head>
<body>
    <div class="auth-card">
        <h2 style="color:var(--primary)">New Password</h2>
        <p style="color:#666; font-size:14px; margin-bottom: 20px;">Please enter your new secure password.</p>
        
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" id="pass1" placeholder="Min. 6 characters" required minlength="6">
                <i class="fa-solid fa-eye toggle-password" onclick="toggle('pass1', this)"></i>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="pass2" placeholder="Repeat your password" required>
                <i class="fa-solid fa-eye toggle-password" onclick="toggle('pass2', this)"></i>
            </div>
            <button type="submit" name="reset_password" class="btn-submit">Update Password</button>
        </form>
    </div>

    <script>
        function toggle(id, el) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
                el.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = "password";
                el.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>