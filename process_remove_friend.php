<?php
// Process Remove Friend Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['friend_id']) || !is_numeric($_POST['friend_id'])) {
    http_response_code(403); echo json_encode(['success' => false]); exit();
}

$user_id = $_SESSION['user_id'];
$friend_id = (int)$_POST['friend_id'];

$user_one = min($user_id, $friend_id);
$user_two = max($user_id, $friend_id);

$stmt = $conn->prepare("DELETE FROM friendships WHERE user_one_id = ? AND user_two_id = ?");
$stmt->bind_param("ii", $user_one, $user_two);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not remove friend.']);
}
$stmt->close();
$conn->close();
// Process Remove Friend Ending
?>