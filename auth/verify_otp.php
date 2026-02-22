<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$message = ""; 

if (isset($_POST['verify_otp'])) {
    $user_otp = mysqli_real_escape_string($conn, $_POST['otp']);

    // Fetch the OTP and Expiry from the database
    $sql = "SELECT reset_otp, otp_expiry FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $db_otp = $row['reset_otp'];
        $db_expiry = $row['otp_expiry'];
        $current_time = date("Y-m-d H:i:s"); // Current Nepal Time

        // Logic check: Does OTP match AND is the current time before expiry?
        if ($user_otp === $db_otp) {
            if ($current_time <= $db_expiry) {
                $_SESSION['otp_verified'] = true;
                header("Location: new_password.php");
                exit();
            } else {
                $message = "<div style='color: #e74c3c;'>Code expired at $db_expiry. Please resend.</div>";
            }
        } else {
            $message = "<div style='color: #e74c3c;'>Invalid code. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP | CookEasy</title>
     <link rel="stylesheet" href="../assets/css/auth/verify_otp.css">
   
</head>
<body>
    <div class="auth-card">
        <h2 style="color:var(--primary)">Verify Code</h2>
        <p style="color:#666; font-size:14px;">Enter the 6-digit code sent to<br><b><?php echo $email; ?></b></p>
        
        <?php echo $message; ?>
        
        <form method="POST">
            <input type="text" name="otp" placeholder="000000" maxlength="6" class="otp-input" required autofocus autocomplete="off">
            <button type="submit" name="verify_otp" class="btn-verify">Verify OTP</button>
        </form>
        
        <p style="margin-top:20px; font-size:13px;">
            Didn't get the code? 
            <a href="verify_otp.php?action=resend"  style="color:var(--primary); text-decoration:none; font-weight:bold; ">Resend Code</a>
        </p>
    </div>
</body>
</html>