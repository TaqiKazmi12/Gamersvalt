<?php
// Process Accept Friend Starting
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

$stmt = $conn->prepare("UPDATE friendships SET status = 'accepted', action_user_id = ? WHERE user_one_id = ? AND user_two_id = ? AND status = 'pending' AND action_user_id != ?");
$stmt->bind_param("iiii", $user_id, $user_one, $user_two, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not accept request.']);
}
$stmt->close();
$conn->close();
// Process Accept Friend Ending
?>