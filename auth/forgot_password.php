<?php
session_start();
require_once '../config/db.php';
require_once '../includes/mail.php';

$email_val = "";

if (isset($_POST['request_otp'])) {
    $email_val = mysqli_real_escape_string($conn, $_POST['email']);
    $user_query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email_val'");
    
    if (mysqli_num_rows($user_query) > 0) {
        $user = mysqli_fetch_assoc($user_query);
        $otp = rand(100000, 999999);
        
        // This now uses Asia/Kathmandu time (e.g., 20:15 + 10 mins = 20:25)
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        $update = "UPDATE users SET reset_otp = '$otp', otp_expiry = '$expiry' WHERE email = '$email_val'";
        
        if (mysqli_query($conn, $update)) {
            if (sendOTP($email_val, $user['name'], $otp)) {
                $_SESSION['reset_email'] = $email_val;
                header("Location: verify_otp.php");
                exit();
            }
        }
    } else {
        $error = "No account found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | CookEasy</title>
    <link rel="stylesheet" href="../assets/css/auth/forgot_password.css">
</head>
<body>
    <div class="auth-card">
        <h2>Forgot Password?</h2>
        <p style="color:#666; font-size:14px;">Enter your email to receive  verification code.</p>
        
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        
        <form method="POST" action="forgot_password.php">
            <div class="form-group">
                <input type="email" name="email" placeholder="Enter registered email" value="<?php echo htmlspecialchars($email_val); ?>" required>
            </div>
            <button type="submit" name="request_otp" class="btn-submit">Send Reset Code</button>
        </form>
        <p class="para" style="margin-top:20px;"><a href="login.php" style="color:var(--accent); text-decoration:none;">Back to Login</a></p>
    </div>
</body>
</html>