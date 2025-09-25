<?php
// Process Add to Cart Starting
session_start(); 
require_once 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = (int)$_GET['id'];

$stmt_check = $conn->prepare("SELECT id FROM cart_items WHERE user_id = ? AND game_id = ?");
$stmt_check->bind_param("ii", $user_id, $game_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'In Cart']);
    $stmt_check->close();
    exit();
}
$stmt_check->close();

$stmt_insert = $conn->prepare("INSERT INTO cart_items (user_id, game_id) VALUES (?, ?)");
$stmt_insert->bind_param("ii", $user_id, $game_id);

if ($stmt_insert->execute()) {
    echo json_encode(['success' => true, 'message' => 'Added to Cart']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server Error']);
}

$stmt_insert->close();
$conn->close();
// Process Add to Cart Ending
?>