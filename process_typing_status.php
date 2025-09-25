<?php
// Process Typing Status Starting
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['typing_status'][$_POST['receiver_id']] = time();
} else {
    $friend_id = $_GET['friend_id'];
    $is_typing = false;
    if (isset($_SESSION['typing_status'][$_SESSION['user_id']]) && (time() - $_SESSION['typing_status'][$_SESSION['user_id']]) < 3) {
        $is_typing = true;
    }
    header('Content-Type: application/json');
    echo json_encode(['is_typing' => $is_typing]);
}
// Process Typing Status Ending
?>