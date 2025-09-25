<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php?redirect=cart.php"); exit();
}
$user_id = $_SESSION['user_id'];

$purchased_games_stmt = $conn->prepare("SELECT game_id FROM purchases WHERE user_id = ?");
$purchased_games_stmt->bind_param("i", $user_id);
$purchased_games_stmt->execute();
$purchased_game_ids = array_column($purchased_games_stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'game_id');
$purchased_games_stmt->close();

if (!empty($purchased_game_ids)) {
    $placeholders = implode(',', array_fill(0, count($purchased_game_ids), '?'));
    $delete_stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND game_id IN ($placeholders)");
    $types = 'i' . str_repeat('i', count($purchased_game_ids));
    $delete_stmt->bind_param($types, $user_id, ...$purchased_game_ids);
    $delete_stmt->execute();
    $delete_stmt->close();
}

$sql = "SELECT g.id as game_id, g.title, g.thumbnail, g.price, d_user.name as developer_name FROM cart_items ci JOIN games g ON ci.game_id = g.id LEFT JOIN developers d ON g.developer_id = d.id LEFT JOIN users d_user ON d.user_id = d_user.id WHERE ci.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="cart.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Cart Page Starting -->
    <div class="cart-container">
        <header class="cart-header">
            <h1>Shopping Cart</h1>
        </header>
        
<form action="cart_checkout.php" method="POST" id="cart-form">
            <div class="cart-content" id="cart-content-wrapper">
                <?php if (!empty($cart_items)): ?>
                    <div class="cart-layout">
                        <div class="cart-items-list">
                            <div class="cart-list-header">
                                <label class="select-all-container">
                                    <input type="checkbox" id="select-all-checkbox">
                                    <span class="custom-checkbox"></span>
                                    Select All (<?php echo count($cart_items); ?> items)
                                </label>
                            </div>
                            <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item" data-price="<?php echo $item['price']; ?>">
                                    <label class="item-selection">
                                        <input type="checkbox" class="item-checkbox" name="selected_games[]" value="<?php echo $item['game_id']; ?>">
                                        <span class="custom-checkbox"></span>
                                    </label>
                                    <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" alt="" class="item-thumbnail">
                                    <div class="item-details">
                                        <a href="innergamepage.php?id=<?php echo $item['game_id']; ?>" class="item-title"><?php echo htmlspecialchars($item['title']); ?></a>
                                        <span class="item-developer">by <?php echo htmlspecialchars($item['developer_name']); ?></span>
                                    </div>
                                    <div class="item-price-actions">
                                        <span class="item-price">$<?php echo number_format($item['price'], 2); ?></span>
                                        <button type="button" class="remove-item-btn" data-game-id="<?php echo $item['game_id']; ?>">Remove</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <aside class="cart-summary">
                            <h2>Order Summary</h2>
                            <div class="summary-details">
                                <div class="summary-row">
                                    <span>Selected Items</span>
                                    <span id="selected-count">0</span>
                                </div>
                                <div class="summary-row total">
                                    <span>Total</span>
                                    <span id="total-price">$0.00</span>
                                </div>
                            </div>
                            <button type="submit" id="checkout-button" class="checkout-button" disabled>Checkout</button>
                        </aside>
                    </div>
                <?php else: ?>
                    <div class="empty-cart">
                        <h2>Your Cart is Empty</h2>
                        <p>Looks like you haven't added any games to your cart yet. Time to explore!</p>
                        <a href="explore.php" class="explore-button">Explore Games</a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <!-- Cart Page Ending -->

    <script>
    // Cart Page Starting
    document.addEventListener('DOMContentLoaded', function () {
        const cartContentWrapper = document.getElementById('cart-content-wrapper');
        if (!cartContentWrapper) return;

        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const checkoutButton = document.getElementById('checkout-button');
        const selectedCountEl = document.getElementById('selected-count');
        const totalPriceEl = document.getElementById('total-price');

        function updateCartState() {
            let total = 0;
            let selectedCount = 0;
            itemCheckboxes.forEach(checkbox => {
                const itemEl = checkbox.closest('.cart-item');
                if (checkbox.checked) {
                    total += parseFloat(itemEl.dataset.price);
                    selectedCount++;
                    itemEl.classList.add('selected');
                } else {
                    itemEl.classList.remove('selected');
                }
            });

            if(totalPriceEl) totalPriceEl.textContent = `$${total.toFixed(2)}`;
            if(selectedCountEl) selectedCountEl.textContent = selectedCount;
            if(checkoutButton) {
                checkoutButton.disabled = selectedCount === 0;
                checkoutButton.textContent = selectedCount > 0 ? `Checkout (${selectedCount} Items)` : 'Checkout';
            }
            if(selectAllCheckbox) {
                selectAllCheckbox.checked = selectedCount > 0 && selectedCount === itemCheckboxes.length;
            }
        }

        if(selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                itemCheckboxes.forEach(checkbox => checkbox.checked = this.checked);
                updateCartState();
            });
        }

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateCartState);
        });

        cartContentWrapper.addEventListener('click', function(e) {
            const button = e.target.closest('.remove-item-btn');
            if (!button) return;

            const gameId = button.dataset.gameId;
            const cartItemElement = button.closest('.cart-item');
            
            cartItemElement.classList.add('removing');
            
            const formData = new FormData();
            formData.append('game_id', gameId);

            fetch('process_remove_from_cart.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cartItemElement.addEventListener('transitionend', () => {
                        cartItemElement.remove();
                    
                        const remainingCheckboxes = document.querySelectorAll('.item-checkbox');
                        if (remainingCheckboxes.length === 0) {
                            cartContentWrapper.innerHTML = `
                                <div class="empty-cart">
                                    <h2>Your Cart is Empty</h2>
                                    <p>Your cart is now empty. Find more games to love!</p>
                                    <a href="explore.php" class="explore-button">Explore Games</a>
                                </div>`;
                        }
                    });
                    updateCartState();
                } else {
                    cartItemElement.classList.remove('removing');
                    alert('Could not remove item.');
                }
            }).catch(() => {
                cartItemElement.classList.remove('removing');
                alert('An error occurred.');
            });
        });

        updateCartState();
    });
    // Cart Page Ending
    </script>
   
<?php include "footer.php" ?>
</body>
</html>