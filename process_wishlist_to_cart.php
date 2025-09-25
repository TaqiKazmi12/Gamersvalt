<?php
// Process Wishlist to Cart Starting
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
$stmt_check = $conn->prepare("SELECT id FROM cart_items WHERE user_id = ? AND game_id = ?");
$stmt_check->bind_param("ii", $user_id, $game_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Already in Cart']);
    $stmt_check->close();
    exit();
}
$stmt_check->close();

$conn->begin_transaction();
try {
    $stmt_add = $conn->prepare("INSERT INTO cart_items (user_id, game_id) VALUES (?, ?)");
    $stmt_add->bind_param("ii", $user_id, $game_id);
    $stmt_add->execute();
    $stmt_add->close();
    $stmt_remove = $conn->prepare("DELETE FROM wishlists WHERE user_id = ? AND game_id = ?");
    $stmt_remove->bind_param("ii", $user_id, $game_id);
    $stmt_remove->execute();
    $stmt_remove->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Moved to Cart']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database Error']);
}

$conn->close();
// Process Wishlist to Cart Ending
?>