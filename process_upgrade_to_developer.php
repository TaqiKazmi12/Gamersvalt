<?php
// Process Upgrade to Developer Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($user_role !== 'user') {
    echo json_encode(['success' => false, 'message' => 'This account is already a developer or admin.']);
    exit();
}

$conn->begin_transaction();
try {
    $stmt_update = $conn->prepare("UPDATE users SET role = 'developer' WHERE id = ? AND role = 'user'");
    $stmt_update->bind_param("i", $user_id);
    $stmt_update->execute();

    if ($stmt_update->affected_rows === 0) {
        throw new Exception("Could not update user role.");
    }
    $stmt_update->close();

    $default_bio = "This is a new developer on Gamer's Valt!";
    $stmt_insert = $conn->prepare("INSERT INTO developers (user_id, bio) VALUES (?, ?)");
    $stmt_insert->bind_param("is", $user_id, $default_bio);
    $stmt_insert->execute();
    $stmt_insert->close();
    
    $conn->commit();
    session_destroy();

    echo json_encode(['success' => true, 'message' => 'Account upgraded successfully! You will be logged out and redirected.']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred during the upgrade. Please try again.']);
}

$conn->close();
// Process Upgrade to Developer Ending
?>