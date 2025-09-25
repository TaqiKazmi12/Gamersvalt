<?php
// Fetch Chat History Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['friend_id']) || !is_numeric($_GET['friend_id'])) {
    http_response_code(403); echo json_encode([]); exit();
}

$user_id = $_SESSION['user_id'];
$friend_id = (int)$_GET['friend_id'];

$sql = "SELECT cm.id, cm.sender_id, cm.message_text, cm.image_url, cm.shared_game_id, cm.created_at, g.title as shared_game_title, g.thumbnail as shared_game_thumbnail FROM chat_messages cm LEFT JOIN games g ON cm.shared_game_id = g.id WHERE (cm.sender_id = ? AND cm.receiver_id = ?) OR (cm.sender_id = ? AND cm.receiver_id = ?) ORDER BY cm.created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt_update = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
$stmt_update->bind_param("ii", $friend_id, $user_id);
$stmt_update->execute();
$stmt_update->close();

$conn->close();
echo json_encode($messages);
// Fetch Chat History Ending
?>