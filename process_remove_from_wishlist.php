<?php
// Process Remove From Wishlist Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || !isset($_POST['game_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = (int)$_POST['game_id'];

$stmt = $conn->prepare("DELETE FROM wishlists WHERE user_id = ? AND game_id = ?");
$stmt->bind_param("ii", $user_id, $game_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Item not found or could not be removed.']);
}
$stmt->close();
$conn->close();
// Process Remove From Wishlist Ending
?>