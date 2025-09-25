<?php
// Process Withdraw Funds Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['developer_id'])) {
    http_response_code(403); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit();
}

$developer_id = $_SESSION['developer_id'];
$amount = (float)$_POST['amount'];

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'No funds to withdraw.']); exit();
}

$conn->begin_transaction();
try {
    $stmt_payout = $conn->prepare("INSERT INTO payouts (developer_id, amount) VALUES (?, ?)");
    $stmt_payout->bind_param("id", $developer_id, $amount);
    $stmt_payout->execute();
    $payout_id = $conn->insert_id; 
    $stmt_payout->close();
    $sql_unpaid_purchases = "SELECT p.id FROM purchases p JOIN games g ON p.game_id = g.id WHERE g.developer_id = ? AND p.payout_id IS NULL";
    $stmt_find = $conn->prepare($sql_unpaid_purchases);
    $stmt_find->bind_param("i", $developer_id);
    $stmt_find->execute();
    $unpaid_ids_result = $stmt_find->get_result()->fetch_all(MYSQLI_ASSOC);
    $unpaid_ids = array_column($unpaid_ids_result, 'id');
    $stmt_find->close();
    if (!empty($unpaid_ids)) {
        $placeholders = implode(',', array_fill(0, count($unpaid_ids), '?'));
        $types = 'i' . str_repeat('i', count($unpaid_ids));
        
        $stmt_update = $conn->prepare("UPDATE purchases SET payout_id = ? WHERE id IN ($placeholders)");
        $stmt_update->bind_param($types, $payout_id, ...$unpaid_ids);
        $stmt_update->execute();
        $stmt_update->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Withdrawal of $' . number_format($amount, 2) . ' initiated successfully! Your balance is now $0.00.']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred: ' . $e->getMessage()]);
}

$conn->close();
// Process Withdraw Funds Ending
?>