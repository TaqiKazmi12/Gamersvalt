<?php
// Process Change Password Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}
if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters.']);
    exit();
}
if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
    exit();
}

$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($current_password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
    exit();
}

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt_update->bind_param("si", $hashed_password, $user_id);

if ($stmt_update->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
}

$stmt_update->close();
$conn->close();
// Process Change Password Ending
?>