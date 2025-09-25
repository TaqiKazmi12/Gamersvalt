<?php
// Process Remove From Cart Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;

$stmt_delete = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND game_id = ?");
$stmt_delete->bind_param("ii", $user_id, $game_id);
$stmt_delete->execute();
$was_deleted = $stmt_delete->affected_rows > 0;
$stmt_delete->close();

$sql = "SELECT g.price FROM cart_items ci JOIN games g ON ci.game_id = g.id WHERE ci.user_id = ?";
$stmt_total = $conn->prepare($sql);
$stmt_total->bind_param("i", $user_id);
$stmt_total->execute();
$items = $stmt_total->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_total->close();
$conn->close();

$subtotal = array_sum(array_column($items, 'price'));

echo json_encode([
    'success' => $was_deleted,
    'newSubtotal' => '$' . number_format($subtotal, 2),
    'newTotal' => '$' . number_format($subtotal, 2),
    'itemCount' => count($items)
]);
// Process Remove From Cart Ending
?>