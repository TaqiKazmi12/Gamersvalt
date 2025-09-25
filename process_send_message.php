<?php
// Process Send Message Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(403); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$message_text = isset($_POST['message_text']) ? trim($_POST['message_text']) : '';
$shared_game_id = isset($_POST['shared_game_id']) && is_numeric($_POST['shared_game_id']) ? (int)$_POST['shared_game_id'] : null;
$image_url = null;

if ($receiver_id <= 0) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'Invalid receiver.']); exit(); }

if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
    $target_dir = "uploads/chat_images/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    $file_extension = strtolower(pathinfo($_FILES["image_file"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid('img_', true) . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
        $image_url = $target_file;
    }
}

if (empty($message_text) && is_null($shared_game_id) && is_null($image_url)) {
    http_response_code(400); echo json_encode(['success' => false, 'message' => 'Cannot send an empty message.']); exit();
}

$stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message_text, image_url, shared_game_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisii", $sender_id, $receiver_id, $message_text, $image_url, $shared_game_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
}

$stmt->close();
$conn->close();
// Process Send Message Ending
?>