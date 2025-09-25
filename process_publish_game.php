<?php
// Process Publish Game Starting
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['developer_id']) || !isset($_POST['game_id'])) {
    header("Location: dev_dashboard.php");
    exit();
}

$developer_id = $_SESSION['developer_id'];
$game_id = (int)$_POST['game_id'];
$stmt = $conn->prepare("UPDATE games SET status = 'published' WHERE id = ? AND developer_id = ? AND status = 'draft'");
$stmt->bind_param("ii", $game_id, $developer_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['message'] = "Your game has been successfully published!";
} else {
    $_SESSION['message'] = "Error: Could not publish the game. It might already be published or you may not have permission.";
}

$stmt->close();
$conn->close();
header("Location: dev_dashboard.php");
exit();
// Process Publish Game Ending
?>