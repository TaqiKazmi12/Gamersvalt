<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: userlogin.php"); exit();
}

$user_id = $_SESSION['user_id'];
$game_id = (int)$_GET['id'];

$stmt_game = $conn->prepare("SELECT title, price, thumbnail FROM games WHERE id = ?");
$stmt_game->bind_param("i", $game_id);
$stmt_game->execute();
$game = $stmt_game->get_result()->fetch_assoc();
$stmt_game->close();

if (!$game || $game['price'] <= 0) {
    header("Location: explore.php"); exit();
}

$stmt_cards = $conn->prepare("SELECT id, card_type, card_number_last4 FROM user_payment_methods WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$stmt_cards->bind_param("i", $user_id);
$stmt_cards->execute();
$saved_cards = $stmt_cards->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_cards->close();
$conn->close();

$card_image_urls = [
    'visa' => 'https://cdn4.iconfinder.com/data/icons/flat-brand-logo-2/512/visa-512.png',
    'mastercard' => 'https://e7.pngegg.com/pngimages/910/492/png-clipart-mastercard-logo-credit-card-visa-brand-mastercard-text-label-thumbnail.png',
    'amex' => 'https://cdn-icons-png.flaticon.com/512/179/179431.png' 
];
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="checkout.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Checkout Page Starting -->
    <div class="checkout-wrapper">
        <div class="checkout-container">
            <div class="order-summary-panel">
                <h2>Order Summary</h2>
                <div class="summary-game-card">
                    <img src="<?php echo htmlspecialchars($game['thumbnail']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>">
                    <div class="summary-game-details">
                        <h3><?php echo htmlspecialchars($game['title']); ?></h3>
                        <span>Base Game</span>
                    </div>
                    <div class="summary-game-price">$<?php echo number_format($game['price'], 2); ?></div>
                </div>
                <div class="summary-totals">
                    <div class="total-row">
                        <span>Price</span>
                        <span>$<?php echo number_format($game['price'], 2); ?></span>
                    </div>
                    <div class="total-row final-total">
                        <span>Total</span>
                        <span>$<?php echo number_format($game['price'], 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="payment-panel">
                <h2>Payment Method</h2>
                <form action="process_checkout.php" method="POST" id="payment-form">
                    <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
                    <input type="hidden" name="amount" value="<?php echo $game['price']; ?>">
                    
                    <div class="payment-options">
                        <div class="saved-cards-section <?php if (empty($saved_cards)) echo 'hidden'; ?>">
                            <h4>Your Saved Cards</h4>
                            <?php foreach ($saved_cards as $index => $card): ?>
                            <label class="saved-card-option">
                                <input type="radio" name="payment_method_id" value="<?php echo $card['id']; ?>" <?php if ($index === 0) echo 'checked'; ?>>
                                <div class="card-details">
                                  
                                    <img src="<?php echo $card_image_urls[$card['card_type']]; ?>" alt="<?php echo $card['card_type']; ?>">
                                    <span>**** <?php echo $card['card_number_last4']; ?></span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="add-new-card-section">
                            <h4><?php if (!empty($saved_cards)) echo 'Or '; ?>Add a New Card</h4>
                            <div class="form-group">
                                <label for="cardholder_name">Name on Card</label>
                                <input type="text" id="cardholder_name" name="cardholder_name" placeholder="John M. Doe">
                            </div>
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" placeholder="49... or 51..." maxlength="19">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Expiration Date</label>
                                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM / YY">
                                </div>
                                <div class="form-group">
                                    <label for="cvc">CVC</label>
                                    <input type="text" id="cvc" name="cvc" placeholder="123">
                                </div>
                            </div>
                        </div>

                        <div class="save-card-option">
                            <label><input type="checkbox" name="save_card" value="1" checked> Save payment method for next time</label>
                        </div>
                    </div>
                    
                    <div class="paypal-option disabled">
                       
                        <img src="https://pngimg.com/uploads/paypal/paypal_PNG7.png" alt="PayPal">
                        <span>Unavailable for this transaction</span>
                    </div>

                    <button type="submit" class="purchase-button">Complete Purchase</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Checkout Page Ending -->

    <script>
        // Checkout Page Starting
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('payment-form');
            const savedCardRadios = document.querySelectorAll('input[name="payment_method_id"]');
            const newCardInputs = [
                document.getElementById('cardholder_name'),
                document.getElementById('card_number'),
                document.getElementById('expiry_date'),
                document.getElementById('cvc')
            ];
            const saveCardCheckbox = document.querySelector('input[name="save_card"]');

            function toggleRequiredForNewCard(isRequired) {
                newCardInputs.forEach(input => {
                    input.required = isRequired;
                });
                saveCardCheckbox.parentElement.style.display = isRequired ? 'flex' : 'none';
            }

            function updateFormState() {
                const aSavedCardIsChecked = Array.from(savedCardRadios).some(radio => radio.checked);
                if (aSavedCardIsChecked) {
                    toggleRequiredForNewCard(false);
                } else {
                    toggleRequiredForNewCard(true);
                }
            }

            savedCardRadios.forEach(radio => {
                radio.addEventListener('change', updateFormState);
            });

            newCardInputs.forEach(input => {
                input.addEventListener('focus', () => {
                    savedCardRadios.forEach(radio => radio.checked = false);
                    updateFormState();
                });
            });

            const cardNumberInput = document.getElementById('card_number');
            const expiryDateInput = document.getElementById('expiry_date');
            
            cardNumberInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
            });

            expiryDateInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/[^\d]/g, '');
                if (value.length > 2) {
                    value = value.slice(0, 2) + ' / ' + value.slice(2, 4);
                }
                e.target.value = value;
            });

            updateFormState();
        });
        // Checkout Page Ending
    </script>
    <?php include "footer.php" ?>
</body>
</html>