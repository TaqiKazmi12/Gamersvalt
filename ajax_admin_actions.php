<?php
// AJAX Admin Actions Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403); echo json_encode(['success' => false, 'message' => 'Forbidden']); exit();
}

$admin_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$value = $_POST['value'] ?? '';

function log_admin_action($conn, $admin_id, $action_description) {
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $admin_id, $action_description);
    $stmt->execute();
    $stmt->close();
}

function send_notification($conn, $user_id, $type, $related_id, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, related_id, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $user_id, $type, $related_id, $message);
    $stmt->execute();
    $stmt->close();
}

switch ($action) {
    case 'delete_user':
        if ($id === $admin_id) { echo json_encode(['success' => false, 'message' => 'Cannot delete your own account.']); exit(); }
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) { log_admin_action($conn, $admin_id, "Deleted user ID $id"); echo json_encode(['success' => true]); } 
        else { echo json_encode(['success' => false]); }
        $stmt->close();
        break;

    case 'update_game_status':
        $stmt_game = $conn->prepare("SELECT g.title, d.user_id FROM games g JOIN developers d ON g.developer_id = d.id WHERE g.id = ?");
        $stmt_game->bind_param("i", $id);
        $stmt_game->execute();
        $game_info = $stmt_game->get_result()->fetch_assoc();
        $stmt_game->close();

        if ($game_info) {
            $stmt = $conn->prepare("UPDATE games SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $value, $id);
            if ($stmt->execute()) {
                log_admin_action($conn, $admin_id, "Changed game ID $id ('{$game_info['title']}') status to $value");
                if ($value === 'banned') {
                    $message = "Your game, '{$game_info['title']}', has been banned for violating platform policies.";
                    send_notification($conn, $game_info['user_id'], 'game_banned', $id, $message);
                }
                echo json_encode(['success' => true]);
            } else { echo json_encode(['success' => false]); }
            $stmt->close();
        }
        break;
    
    case 'send_warning':
        $stmt_user = $conn->prepare("SELECT d.user_id, g.title FROM reports r JOIN games g ON r.game_id = g.id JOIN developers d ON g.developer_id = d.id WHERE r.id = ?");
        $stmt_user->bind_param("i", $id);
        $stmt_user->execute();
        $report_info = $stmt_user->get_result()->fetch_assoc();
        $stmt_user->close();
        if ($report_info) {
            $message = "You have received a warning regarding your game '{$report_info['title']}' based on a user report.";
            send_notification($conn, $report_info['user_id'], 'developer_warning', $id, $message);
            log_admin_action($conn, $admin_id, "Sent warning for report ID $id");
            echo json_encode(['success' => true, 'message' => 'Warning sent!']);
        } else { echo json_encode(['success' => false]); }
        break;

    case 'resolve_report':
        $stmt_user = $conn->prepare("SELECT user_id, game_id FROM reports WHERE id = ?");
        $stmt_user->bind_param("i", $id);
        $stmt_user->execute();
        $report_info = $stmt_user->get_result()->fetch_assoc();
        $stmt_user->close();

        $stmt = $conn->prepare("UPDATE reports SET is_resolved = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if ($report_info) {
                $message = "Thank you for your feedback. The report you submitted has been reviewed and closed.";
                send_notification($conn, $report_info['user_id'], 'report_resolved', $report_info['game_id'], $message);
            }
            log_admin_action($conn, $admin_id, "Resolved report ID $id");
            echo json_encode(['success' => true]);
        } else { echo json_encode(['success' => false]); }
        $stmt->close();
        break;


    default:
        http_response_code(400); echo json_encode(['success' => false, 'message' => 'Invalid Action']); break;
}
$conn->close();
// AJAX Admin Actions Ending
?>