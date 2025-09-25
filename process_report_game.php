<?php
// Process Report Game Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(403); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit();
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$report_type = isset($_POST['report_type']) ? trim($_POST['report_type']) : '';
$comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';

if ($game_id <= 0 || empty($report_type)) {
    http_response_code(400); echo json_encode(['success' => false, 'message' => 'Invalid report data.']); exit();
}

$stmt_check = $conn->prepare("SELECT id FROM reports WHERE user_id = ? AND game_id = ?");
$stmt_check->bind_param("ii", $user_id, $game_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reported this game.']);
    $stmt_check->close();
    exit();
}
$stmt_check->close();

$stmt_insert = $conn->prepare("INSERT INTO reports (user_id, game_id, report_type, comments) VALUES (?, ?, ?, ?)");
$stmt_insert->bind_param("iiss", $user_id, $game_id, $report_type, $comments);

if ($stmt_insert->execute()) {
    echo json_encode(['success' => true, 'message' => 'Report submitted. Thank you for your feedback.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to submit report.']);
}

$stmt_insert->close();
$conn->close();
// Process Report Game Ending
?>