<?php
// Process Cart Checkout Starting
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || !isset($_POST['game_ids'])) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$game_ids = $_POST['game_ids'];
$save_card = isset($_POST['save_card']);

$placeholders = implode(',', array_fill(0, count($game_ids), '?'));
$types = str_repeat('i', count($game_ids));
$sql_prices = "SELECT id, price FROM games WHERE id IN ($placeholders)";
$stmt_prices = $conn->prepare($sql_prices);
$stmt_prices->bind_param($types, ...$game_ids);
$stmt_prices->execute();
$games_data = $stmt_prices->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_prices->close();

$conn->begin_transaction();
try {
    $stmt_insert = $conn->prepare("INSERT INTO purchases (user_id, game_id, price_paid) VALUES (?, ?, ?)");
    foreach ($games_data as $game) {
        $stmt_insert->bind_param("iid", $user_id, $game['id'], $game['price']);
        $stmt_insert->execute();
    }
    $stmt_insert->close();

    $stmt_delete_cart = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND game_id IN ($placeholders)");
    $stmt_delete_cart->bind_param('i' . $types, $user_id, ...$game_ids);
    $stmt_delete_cart->execute();
    $stmt_delete_cart->close();
    
    if ($save_card && !empty($_POST['card_number'])) {
        $cardholder_name = trim($_POST['cardholder_name']);
        $card_number = preg_replace('/\s+/', '', $_POST['card_number']);
        $card_number_last4 = substr($card_number, -4);
        list($expiry_month, $expiry_year) = array_map('trim', explode('/', $_POST['expiry_date']));
        $expiry_year = '20' . $expiry_year;
        $card_type = 'visa';
        if (strpos($card_number, '5') === 0) $card_type = 'mastercard';

        $stmt_save_card = $conn->prepare("INSERT INTO user_payment_methods (user_id, card_type, cardholder_name, card_number_last4, expiry_month, expiry_year) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_save_card->bind_param("isssss", $user_id, $card_type, $cardholder_name, $card_number_last4, $expiry_month, $expiry_year);
        $stmt_save_card->execute();
        $stmt_save_card->close();
    }
    
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    die("An error occurred during checkout. Please try again.");
}

$conn->close();

header("Location: thank_you.php");
exit();
// Process Cart Checkout Ending
?>