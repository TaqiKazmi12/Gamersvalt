<?php
// Process Update Profile Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);

if (empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Name and email cannot be empty.']);
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit();
}

$stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt_check->bind_param("si", $email, $user_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This email is already in use by another account.']);
    $stmt_check->close();
    exit();
}
$stmt_check->close();

$stmt_update = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
$stmt_update->bind_param("ssi", $name, $email, $user_id);

if ($stmt_update->execute()) {
    $_SESSION['username'] = $name;
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!', 'newName' => $name, 'newEmail' => $email]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
}

$stmt_update->close();
$conn->close();
// Process Update Profile Ending
?>