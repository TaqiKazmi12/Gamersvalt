<?php
// Download Game Starting
session_start();
require_once 'connection.php';
if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['title'])) {
    http_response_code(403);
    die("ACCESS DENIED: Invalid request.");
}
$user_id = $_SESSION['user_id'];
$game_id = (int)$_GET['id'];
$game_title = $_GET['title'];
$stmt_check = $conn->prepare("SELECT p.id FROM purchases p WHERE p.user_id = ? AND p.game_id = ?");
if (!$stmt_check) {
    die("Database query failed: " . $conn->error);
}
$stmt_check->bind_param("ii", $user_id, $game_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    die("ACCESS DENIED: You do not own this game.");
}
$stmt_check->close();
$ip_address = $_SERVER['REMOTE_ADDR'];
$stmt_log = $conn->prepare("INSERT INTO downloads (user_id, game_id, ip_address) VALUES (?, ?, ?)");
$stmt_log->bind_param("iis", $user_id, $game_id, $ip_address);
$stmt_log->execute();
$stmt_log->close();
$conn->close();
$zip = new ZipArchive();
$zip_filename = sys_get_temp_dir() . '/' . uniqid('game_', true) . '.zip';
$sanitized_game_title = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $game_title);
$download_filename = $sanitized_game_title . '.zip';

if ($zip->open($zip_filename, ZipArchive::CREATE) === TRUE) {
    $zip->addFromString('readme.txt', 'Thank you for downloading ' . $game_title . ' from Gamer\'s Valt!');
    $zip->close();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $download_filename . '"');
    header('Content-Length: ' . filesize($zip_filename));
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($zip_filename);
    unlink($zip_filename);
    exit;
} else {
    http_response_code(500);
    die('Failed to create zip file.');
}
// Download Game Ending
?>