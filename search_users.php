<?php
// Search Users Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['term'])) {
    http_response_code(403); echo json_encode([]); exit();
}

$user_id = $_SESSION['user_id'];
$search_term = '%' . trim($_GET['term']) . '%';

$sql = "SELECT u.id, u.name
        FROM users u
        WHERE u.name LIKE ? AND u.id != ? AND NOT EXISTS (
            SELECT 1 FROM friendships f
            WHERE (f.user_one_id = ? AND f.user_two_id = u.id)
               OR (f.user_one_id = u.id AND f.user_two_id = ?)
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("siii", $search_term, $user_id, $user_id, $user_id);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

echo json_encode($users);
// Search Users Ending
?>