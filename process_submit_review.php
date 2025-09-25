<?php
// Process Submit Review Starting
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: explore.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';

if ($game_id <= 0 || $rating < 1 || $rating > 5 || empty($review_text)) {
    $_SESSION['review_error'] = "Invalid data. Please provide a rating and a comment.";
    header("Location: innergamepage.php?id=" . $game_id);
    exit();
}

$stmt_check = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND game_id = ?");
$stmt_check->bind_param("ii", $user_id, $game_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $_SESSION['review_error'] = "You have already reviewed this game.";
    $stmt_check->close();
    header("Location: innergamepage.php?id=" . $game_id);
    exit();
}
$stmt_check->close();

$stmt_insert = $conn->prepare("INSERT INTO reviews (user_id, game_id, rating, review_text) VALUES (?, ?, ?, ?)");
$stmt_insert->bind_param("iiis", $user_id, $game_id, $rating, $review_text);

if ($stmt_insert->execute()) {
    $_SESSION['review_success'] = "Your review has been submitted successfully!";
} else {
    $_SESSION['review_error'] = "There was an error submitting your review.";
}

$stmt_insert->close();
$conn->close();
header("Location: innergamepage.php?id=" . $game_id);
exit();
// Process Submit Review Ending
?>