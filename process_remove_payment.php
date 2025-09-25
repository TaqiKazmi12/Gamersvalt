<?php
// Process Remove Payment Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || !isset($_POST['card_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid Request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$card_id = (int)$_POST['card_id'];

$stmt = $conn->prepare("DELETE FROM user_payment_methods WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $card_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Card not found or could not be removed.']);
}

$stmt->close();
$conn->close();
// Process Remove Payment Ending
?>