<?php
session_start();
require_once '../config/db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's shopping notes from the database
$list_query = "SELECT * FROM shopping_list WHERE user_id = $user_id ORDER BY created_at DESC";
$lists = mysqli_query($conn, $list_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping List| CookEasy</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel ="stylesheet" href="../assets/css/user/shopping_list.css">
   
</head>
<body>
    <?php include 'user_sidebar.php'; ?>
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fa-solid fa-clipboard-list"></i> Shopping List</h1>
        </div>

        <?php if (mysqli_num_rows($lists) > 0): ?>
            <?php while($list = mysqli_fetch_assoc($lists)): 
                $list_id = $list['list_id'];
                $item_query = "SELECT i.name, incl.* FROM isIncludedIn incl JOIN ingredient i ON incl.ingredient_id = i.ingredient_id WHERE incl.list_id = $list_id";
                $items = mysqli_query($conn, $item_query);
            ?>
            <div class="list-card">
                <div class="list-header">
                    <div>
                        <h2><?php echo htmlspecialchars($list['list_name']); ?></h2>
                        <?php if(!empty($list['notes'])): ?>
                            <div class="user-note">
                                <i class="fa-solid fa-pen-fancy"></i> <?php echo htmlspecialchars($list['notes']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <a href="shop_list_delete.php?id=<?php echo $list_id; ?>" class="btn-delete" onclick="return confirm('Delete this shopping note?')">
                        <i class="fa-solid fa-trash-can"></i>
                    </a>
                </div>

                <?php while($item = mysqli_fetch_assoc($items)): ?>
                    <div class="item" onclick="toggleItem(<?php echo $list_id; ?>, <?php echo $item['ingredient_id']; ?>, this)">
                        <div class="checkbox <?php echo $item['is_bought'] ? 'checked' : ''; ?>">
                            <?php if($item['is_bought']) echo '<i class="fa-solid fa-check"></i>'; ?>
                        </div>
                        <span class="item-text <?php echo $item['is_bought'] ? 'strikethrough' : ''; ?>">
                            <span class="qty"><?php echo $item['quantity']; ?> <?php echo $item['unit']; ?></span>
                            <?php echo htmlspecialchars($item['name']); ?>
                        </span>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center; padding-top:100px; color:#94a3b8;">
                <i class="fa-solid fa-basket-shopping" style="font-size:3.5rem; margin-bottom:20px; opacity:0.5;"></i>
                <h2>No Notes Found</h2>
                <p>Add ingredients from your recipes to start your list.</p>
            </div>
        <?php endif; ?>
    </main>

    <script>
    function toggleItem(listId, ingId, element) {
        const checkbox = element.querySelector('.checkbox');
        const text = element.querySelector('.item-text');
        const isBought = checkbox.classList.contains('checked') ? 0 : 1;

        // AJAX update to shop_item_update.php
        fetch('shop_item_update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `list_id=${listId}&ingredient_id=${ingId}&is_bought=${isBought}`
        }).then(response => {
            if(response.ok) {
                checkbox.classList.toggle('checked');
                text.classList.toggle('strikethrough');
                checkbox.innerHTML = isBought ? '<i class="fa-solid fa-check"></i>' : '';
            }
        });
    }
    </script>
</body>
</html>