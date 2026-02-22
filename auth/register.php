
<?php
session_start();
require_once '../config/db.php';

// Initialize variables to empty strings
$name_val = "";
$email_val = "";

if (isset($_POST['register'])) {
    $name_val = mysqli_real_escape_string($conn, $_POST['name']);
    $email_val = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email_val'");
    if (mysqli_num_rows($check) > 0) {
        $error = "This email address is already in use.";
    } else {
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name_val', '$email_val', '$password')";
        if (mysqli_query($conn, $sql)) {
            header("Location: login.php?msg=success");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link rel="stylesheet" href="../assets/css/auth/register.css">
</head>
<body>
    <div class="auth-container">
        <div class="header-area">
            <h2>Create Account</h2>
            <p>Join our community of food lovers.</p>
        </div>

        <?php if(isset($error)) echo "<div class='error-msg'>$error</div>"; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="John Doe" value="<?php echo htmlspecialchars($name_val); ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="john@example.com" value="<?php echo htmlspecialchars($email_val); ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required>
                <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
            </div>
            <button type="submit" name="register" class="btn-submit">Sign Up</button>
        </form>
        <div style="text-align:center; margin-top:25px;">Already have an account? <a href="login.php" style="color:var(--accent); text-decoration:none; font-weight:bold;">Log In</a></div>
    </div>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>