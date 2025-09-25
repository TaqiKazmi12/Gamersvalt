<?php
// Process Upload Game Starting
session_start();
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'developer') {
    $_SESSION['upload_error'] = "Unauthorized access.";
    header("Location: upload_game.php");
    exit();
}

$developer_id = $_SESSION['developer_id'];
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$price = (float)$_POST['price'];
$category_id = (int)$_POST['category_id'];
$status = in_array($_POST['status'], ['published', 'draft']) ? $_POST['status'] : 'draft';

if (empty($title) || empty($description) || $price < 0 || $category_id <= 0) {
    $_SESSION['upload_error'] = "Please fill out all required fields.";
    header("Location: upload_game.php");
    exit();
}


function handleFileUpload($file, $type) {
    $target_dir = "uploads/" . $type . "/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid($type . '_', true) . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    }
    return null;
}

$thumbnail_path = isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0 ? handleFileUpload($_FILES['thumbnail'], 'thumbnails') : null;
$game_file_path = isset($_FILES['game_file']) && $_FILES['game_file']['error'] == 0 ? handleFileUpload($_FILES['game_file'], 'game_files') : null;

if (!$thumbnail_path || !$game_file_path) {
    $_SESSION['upload_error'] = "Both a thumbnail and a game file are required.";
    header("Location: upload_game.php");
    exit();
}

$screenshot_paths = [];
if (isset($_FILES['screenshots'])) {
    foreach ($_FILES['screenshots']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['screenshots']['error'][$key] == 0) {
            $file = [
                'name' => $_FILES['screenshots']['name'][$key],
                'tmp_name' => $tmp_name
            ];
            $path = handleFileUpload($file, 'screenshots');
            if ($path) $screenshot_paths[] = $path;
        }
    }
}


$conn->begin_transaction();
try {
    
    $stmt_game = $conn->prepare("INSERT INTO games (title, description, price, developer_id, category_id, thumbnail, file_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_game->bind_param("ssdiisss", $title, $description, $price, $developer_id, $category_id, $thumbnail_path, $game_file_path, $status);
    $stmt_game->execute();
    $new_game_id = $conn->insert_id;
    $stmt_game->close();

   
    if (!empty($screenshot_paths)) {
        $stmt_images = $conn->prepare("INSERT INTO game_images (game_id, image_url) VALUES (?, ?)");
        foreach ($screenshot_paths as $path) {
            $stmt_images->bind_param("is", $new_game_id, $path);
            $stmt_images->execute();
        }
        $stmt_images->close();
    }
    
    $conn->commit();
    $_SESSION['upload_success'] = "Congratulations! Your game has been submitted successfully.";
    header("Location: dev_dashboard.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['upload_error'] = "A database error occurred. Please try again. " . $e->getMessage();
    header("Location: upload_game.php");
    exit();
}

$conn->close();
// Process Upload Game Ending
?>