<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php?redirect=wishlist.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$sql = "SELECT g.id, g.title, g.thumbnail, g.price, d_user.name as developer_name
        FROM wishlists w
        JOIN games g ON w.game_id = g.id
        LEFT JOIN developers d ON g.developer_id = d.id
        LEFT JOIN users d_user ON d.user_id = d_user.id
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlist_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - Gamer's Valt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="wishlist.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Wishlist Page Starting -->
    <div class="wishlist-container">
        <header class="wishlist-header">
            <h1>My Wishlist</h1>
            <p>Your curated list of future adventures and must-have titles.</p>
        </header>

        <main class="wishlist-content" id="wishlist-list">
            <?php if (!empty($wishlist_items)): ?>
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="wishlist-item" data-game-id="<?php echo $item['id']; ?>">
                        <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" alt="" class="item-thumbnail">
                        <div class="item-details">
                            <a href="innergamepage.php?id=<?php echo $item['id']; ?>" class="item-title"><?php echo htmlspecialchars($item['title']); ?></a>
                            <span class="item-developer">by <?php echo htmlspecialchars($item['developer_name']); ?></span>
                        </div>
                        <div class="item-price-actions">
                            <span class="item-price">$<?php echo number_format($item['price'], 2); ?></span>
                            <div class="item-buttons">
                                <button class="action-button to-cart-btn" data-game-id="<?php echo $item['id']; ?>">Move to Cart</button>
                                <button class="action-button remove-btn" data-game-id="<?php echo $item['id']; ?>">Remove</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-wishlist">
                    <h2>Your Wishlist is Empty</h2>
                    <p>Explore the store and add games to your wishlist to keep track of titles you're interested in!</p>
                    <a href="explore.php" class="explore-button">Find Games</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <!-- Wishlist Page Ending -->

    <script>
    // Wishlist Page Starting
    document.addEventListener('DOMContentLoaded', function () {
        const wishlistList = document.getElementById('wishlist-list');
        if (!wishlistList) return;

        wishlistList.addEventListener('click', function(e) {
            const button = e.target;
            const gameId = button.dataset.gameId;
            if (!gameId) return;

            const wishlistItem = button.closest('.wishlist-item');

            if (button.classList.contains('remove-btn')) {
                wishlistItem.classList.add('removing');
                const formData = new FormData();
                formData.append('game_id', gameId);
                fetch('process_remove_from_wishlist.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            wishlistItem.addEventListener('transitionend', () => wishlistItem.remove());
                        } else {
                            wishlistItem.classList.remove('removing');
                        }
                    });
            } else if (button.classList.contains('to-cart-btn')) {
                button.disabled = true;
                button.textContent = 'Moving...';
                const formData = new FormData();
                formData.append('game_id', gameId);
                fetch('process_wishlist_to_cart.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            button.textContent = '✔ Added!';
                            button.classList.add('success');
                            wishlistItem.style.opacity = '0.5';
                        } else {
                            button.textContent = data.message || 'Error';
                            button.classList.add('error');
                        }
                    });
            }
        });
    });
    // Wishlist Page Ending
    </script>
    <?php include "footer.php" ?>
</body>
</html>