<?php
// Process Update Dev Profile Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['developer_id'])) {
    http_response_code(403); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit();
}

$developer_id = $_SESSION['developer_id'];
$user_id = $_SESSION['user_id'];
$studio_name = trim($_POST['studio_name']);
$bio = trim($_POST['bio']);
$portfolio_link = trim($_POST['portfolio_link']);

if (empty($studio_name) || empty($bio)) {
    echo json_encode(['success' => false, 'message' => 'Studio Name and Bio cannot be empty.']); exit();
}
if (!empty($portfolio_link) && !filter_var($portfolio_link, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'message' => 'Portfolio link must be a valid URL.']); exit();
}

$conn->begin_transaction();
try {
    $stmt_user = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
    $stmt_user->bind_param("si", $studio_name, $user_id);
    $stmt_user->execute();
    $stmt_user->close();

    $stmt_dev = $conn->prepare("UPDATE developers SET bio = ?, portfolio_link = ? WHERE id = ?");
    $stmt_dev->bind_param("ssi", $bio, $portfolio_link, $developer_id);
    $stmt_dev->execute();
    $stmt_dev->close();

    $conn->commit();
    $_SESSION['username'] = $studio_name;
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}

$conn->close();
// Process Update Dev Profile Ending
?>