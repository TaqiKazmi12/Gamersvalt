<?php
// Process Delete Screenshot Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['developer_id']) || !isset($_POST['image_id'])) {
    http_response_code(403); echo json_encode(['success' => false]); exit();
}

$developer_id = $_SESSION['developer_id'];
$image_id = (int)$_POST['image_id'];

$stmt_select = $conn->prepare("SELECT gi.image_url FROM game_images gi JOIN games g ON gi.game_id = g.id WHERE gi.id = ? AND g.developer_id = ?");
$stmt_select->bind_param("ii", $image_id, $developer_id);
$stmt_select->execute();
$image = $stmt_select->get_result()->fetch_assoc();
$stmt_select->close();

if ($image) {
    $stmt_delete = $conn->prepare("DELETE FROM game_images WHERE id = ?");
    $stmt_delete->bind_param("i", $image_id);
    if ($stmt_delete->execute()) {
        if (file_exists($image['image_url'])) {
            unlink($image['image_url']);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt_delete->close();
} else {
    http_response_code(403);
    echo json_encode(['success' => false]);
}
$conn->close();
// Process Delete Screenshot Ending
?>