<?php
// Get Social Data Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { http_response_code(403); echo json_encode([]); exit(); }

$user_id = $_SESSION['user_id'];
$response = ['friends' => [], 'notifications' => []];

$sql_friends = "SELECT u.id, u.name, u.is_online FROM friendships f JOIN users u ON u.id = IF(f.user_one_id = ?, f.user_two_id, f.user_one_id) WHERE (f.user_one_id = ? OR f.user_two_id = ?) AND f.status = 'accepted' ORDER BY u.is_online DESC, u.name ASC";
$stmt_friends = $conn->prepare($sql_friends);
$stmt_friends->bind_param("iii", $user_id, $user_id, $user_id);
$stmt_friends->execute();
$response['friends'] = $stmt_friends->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_friends->close();

$stmt_notifs = $conn->prepare("SELECT id, message, is_read FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt_notifs->bind_param("i", $user_id);
$stmt_notifs->execute();
$response['notifications'] = $stmt_notifs->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_notifs->close();

echo json_encode($response);
$conn->close();
// Get Social Data Ending
?>