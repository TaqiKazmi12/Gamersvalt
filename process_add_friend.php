<?php
// Process Add Friend Starting
session_start(); 
require_once 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['friend_id']) || !is_numeric($_POST['friend_id'])) {
    http_response_code(403); echo json_encode(['success' => false, 'message' => 'Not logged in or invalid request']); exit();
}

$user_id = $_SESSION['user_id'];
$friend_id = (int)$_POST['friend_id'];

if ($user_id == $friend_id) {
    echo json_encode(['success' => false, 'message' => 'You cannot add yourself.']); exit();
}

$user_one = min($user_id, $friend_id);
$user_two = max($user_id, $friend_id);

$stmt_check = $conn->prepare("SELECT id FROM friendships WHERE user_one_id = ? AND user_two_id = ?");
$stmt_check->bind_param("ii", $user_one, $user_two);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Request already sent or you are already friends.']);
    $stmt_check->close(); exit();
}
$stmt_check->close();

$stmt_insert = $conn->prepare("INSERT INTO friendships (user_one_id, user_two_id, status, action_user_id) VALUES (?, ?, 'pending', ?)");
$stmt_insert->bind_param("iii", $user_one, $user_two, $user_id);

if ($stmt_insert->execute()) {
    echo json_encode(['success' => true, 'message' => 'Friend request sent!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send request.']);
}
$stmt_insert->close();
$conn->close();
// Process Add Friend Ending
?>