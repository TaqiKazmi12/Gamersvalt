<?php
// Process Upload Screenshots Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['developer_id']) || !isset($_POST['game_id'])) {
    http_response_code(403); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit();
}

$developer_id = $_SESSION['developer_id'];
$game_id = (int)$_POST['game_id'];
$upload_dir = "uploads/screenshots/";
if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

$stmt_verify = $conn->prepare("SELECT id FROM games WHERE id = ? AND developer_id = ?");
$stmt_verify->bind_param("ii", $game_id, $developer_id);
$stmt_verify->execute();
if ($stmt_verify->get_result()->num_rows === 0) {
    http_response_code(403); echo json_encode(['success' => false, 'message' => 'Permission Denied']); exit();
}
$stmt_verify->close();

if (isset($_FILES['screenshots'])) {
    $stmt_insert = $conn->prepare("INSERT INTO game_images (game_id, image_url) VALUES (?, ?)");
    foreach ($_FILES['screenshots']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['screenshots']['error'][$key] === UPLOAD_ERR_OK) {
            $file_name = uniqid('ss_', true) . '_' . basename($_FILES["screenshots"]["name"][$key]);
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($tmp_name, $file_path)) {
                $stmt_insert->bind_param("is", $game_id, $file_path);
                $stmt_insert->execute();
            }
        }
    }
    $stmt_insert->close();
    echo json_encode(['success' => true, 'message' => 'Screenshots uploaded successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'No files were uploaded.']);
}
$conn->close();
// Process Upload Screenshots Ending
?>