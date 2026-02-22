<?php
session_start();
require_once '../config/db.php';

$email_val = ""; 

if (isset($_POST['login'])) {
    $email_val = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // 1. First, check the ADMIN table
    $admin_sql = "SELECT * FROM admin WHERE email = '$email_val'";
    $admin_result = mysqli_query($conn, $admin_sql);

    if (mysqli_num_rows($admin_result) === 1) {
        $admin = mysqli_fetch_assoc($admin_result);
        if (password_verify($password, $admin['password'])) {
            // Set Admin Sessions
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['name'] = $admin['name'];
            $_SESSION['role'] = $admin['role']; // e.g., 'super_admin'
            
            header("Location: ../admin/admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid Password.";
        }
    } 
    // 2. If not found in Admin, check the USERS table
    else {
        $user_sql = "SELECT * FROM users WHERE email = '$email_val'";
        $user_result = mysqli_query($conn, $user_sql);

        if (mysqli_num_rows($user_result) === 1) {
            $user = mysqli_fetch_assoc($user_result);
            if (password_verify($password, $user['password'])) {
                // Check if user is blocked
                if ($user['status'] === 'blocked') {
                    $error = "Your account has been blocked. Please contact support.";
                } else {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['name'];
                    header("Location: ../user/user_dashboard.php");
                    exit();
                }
            } else {
                $error = "Invalid Password.";
            }
        } else {
            $error = "No user found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link rel="stylesheet" href="../assets/css/auth/login.css">
   
</head>
<body>
<div class="login-card">
    <div class="login-header">
        <h2>Welcome Back</h2>
        <p>Log in to your CookEasy account</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($email_val); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="••••••••" required>
            <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
        </div>

        <button type="submit" name="login" class="btn-login">Log In</button>
        <div style="text-align:center; margin-top:15px;">
            <a href="forgot_password.php" style="color:var(--text-muted); font-size:13px; text-decoration:none;">Forgot password?</a>
        </div>
    </form>

    <div class="footer-text">
        Don't have an account? <a href="register.php">Sign Up</a>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>
</body>
</html>