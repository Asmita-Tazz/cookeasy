<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CookEasy | Master Your Kitchen</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #e88a1a; 
            --accent: ;  /* Saffron Orange */
            --text-main: #2d3436;
            --text-light: #636e72;
            --bg-subtle: #f9f9f9;
            --white: #ffffff;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
    }
        body {
         font-family: 'Inter', sans-serif;
         color: var(--text-main); 
         background: var(--white); 
         line-height: 1.6; 
    }

        header {
            background: var(--white);
            padding: 1rem 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 20px rgba(0,0,0,0.04);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo { 
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem; 
            font-weight: 700; 
            color: var(--primary); 
            text-decoration: none; 
            letter-spacing: -0.5px;
        }
        .logo span { color: var(--accent); }

        .nav-menu { 
        display: flex; 
        align-items: center;
         gap: 1rem; 
    }
        .nav-menu a { 
            text-decoration: none; 
            color: var(--text-main); 
            font-weight: 500; 
            font-size: 0.95rem;
            transition: var(--transition);
        }
        .nav-menu a:hover {
         color: var(--accent);
     }

        .auth-btns {
        display: flex; 
        gap: 1rem; 
        align-items: center;
     }
        
        
        .btn-login:hover { 
            background: ; 
            transform: translateY(-2px);
     }
        .btn-signup:hover { 
            background: ;
            transform: translateY(-2px);
     }
    </style>
</head>
<body>
<header>
       
    <a href="index.php" class="logo">Cook<span>Easy</span></a>
    <nav class="nav-menu">
        <a href="index.php">Home</a>
        <a href="user/category_view.php">categories</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="user/user_dashboard.php" style="color: var(--accent);">MyKitchen</a>

           
            
             <div class="auth-btns"><a href="auth/login.php" class="btn-login">login</a></div>

            <div class="auth-btns"><a href="auth/logout.php" class="btn-signup">Logout</a></div>
        <?php else: ?>
            <div class="auth-btns">
                <a href="auth/login.php" class="btn-login">Login</a>
                <a href="auth/register.php" class="btn-signup">SignUp</a>
            </div>
        <?php endif; ?>
    </nav>
</header>