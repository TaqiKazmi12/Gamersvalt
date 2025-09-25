<?php
// Get Library Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { http_response_code(403); echo json_encode([]); exit(); }
$user_id = $_SESSION['user_id'];
$stmt_games = $conn->prepare("SELECT g.id, g.title, g.thumbnail FROM purchases p JOIN games g ON p.game_id = g.id WHERE p.user_id = ?");
$stmt_games->bind_param("i", $user_id);
$stmt_games->execute();
$library_games = $stmt_games->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_games->close();
$conn->close();
echo json_encode($library_games);
// Get Library Ending
?>