<?php

session_start();
require_once 'connection.php';
if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: userlogin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = (int)$_GET['id'];

$stmt_game = $conn->prepare("SELECT price FROM games WHERE id = ?");
$stmt_game->bind_param("i", $game_id);
$stmt_game->execute();
$result = $stmt_game->get_result();
$game = $result->fetch_assoc();
$stmt_game->close();

if (!$game || $game['price'] > 0) {
    header("Location: explore.php");
    exit();
}

$stmt_check = $conn->prepare("SELECT id FROM purchases WHERE user_id = ? AND game_id = ?");
$stmt_check->bind_param("ii", $user_id, $game_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    $stmt_insert = $conn->prepare("INSERT INTO purchases (user_id, game_id, price_paid) VALUES (?, ?, 0.00)");
    $stmt_insert->bind_param("ii", $user_id, $game_id);
    $stmt_insert->execute();
    $stmt_insert->close();
}
$stmt_check->close();
$conn->close();

header("Location: innergamepage.php?id=" . $game_id);
exit();
// Process Add to Library Ending
?>