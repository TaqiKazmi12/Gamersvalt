<?php
// Process Checkout Starting
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: explore.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = (int)$_POST['game_id'];
$amount = (float)$_POST['amount'];
$save_card = isset($_POST['save_card']);
$stmt_insert_purchase = $conn->prepare("INSERT INTO purchases (user_id, game_id, price_paid) VALUES (?, ?, ?)");
$stmt_insert_purchase->bind_param("iid", $user_id, $game_id, $amount);
$stmt_insert_purchase->execute();
$stmt_insert_purchase->close();

if ($save_card && !empty($_POST['card_number'])) {
    $cardholder_name = trim($_POST['cardholder_name']);
    $card_number = preg_replace('/\s+/', '', $_POST['card_number']);
    $card_number_last4 = substr($card_number, -4);
    
    list($expiry_month, $expiry_year) = array_map('trim', explode('/', $_POST['expiry_date']));
    $expiry_year = '20' . $expiry_year; 

 
    $card_type = 'visa';
    if (strpos($card_number, '5') === 0) $card_type = 'mastercard';
    if (strpos($card_number, '3') === 0) $card_type = 'amex';

    $stmt_save_card = $conn->prepare("INSERT INTO user_payment_methods (user_id, card_type, cardholder_name, card_number_last4, expiry_month, expiry_year) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_save_card->bind_param("isssss", $user_id, $card_type, $cardholder_name, $card_number_last4, $expiry_month, $expiry_year);
    $stmt_save_card->execute();
    $stmt_save_card->close();
}

$conn->close();


header("Location: thank_you.php?game_id=" . $game_id);
exit();
// Process Checkout Ending
?>