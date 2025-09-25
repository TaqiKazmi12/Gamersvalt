<?php

if (isset($_POST['action'])) {
    session_start();
    require_once 'connection.php';
    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit();
    }

    $admin_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $value = $_POST['value'] ?? null;
    $data = isset($_POST['data']) ? $_POST['data'] : [];

    function log_admin_action($conn, $admin_id, $action_description) {
        $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action) VALUES (?, ?)");
        $stmt->bind_param("is", $admin_id, $action_description);
        $stmt->execute();
        $stmt->close();
    }

    function send_notification($conn, $user_id, $type, $related_id, $message) {
       
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, related_id, message, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        $stmt->bind_param("isss", $user_id, $type, $related_id, $message);
        $stmt->execute();
        $stmt->close();
    }

    switch ($action) {
        case 'delete_user':
            if ($id === $admin_id) { echo json_encode(['success' => false, 'message' => 'Cannot delete your own account.']); exit(); }
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) { log_admin_action($conn, $admin_id, "Deleted user ID: $id"); echo json_encode(['success' => true]); } 
            else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
            $stmt->close();
            break;

        case 'update_user_role':
            if ($id === $admin_id) { echo json_encode(['success' => false, 'message' => 'Cannot change your own role.']); exit(); }
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $value, $id);
            if ($stmt->execute()) { log_admin_action($conn, $admin_id, "Updated role for user ID: $id to '$value'"); echo json_encode(['success' => true]); }
            else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
            $stmt->close();
            break;

        case 'update_game_status':
            $stmt_game = $conn->prepare("SELECT g.title, d.user_id FROM games g JOIN developers d ON g.developer_id = d.id WHERE g.id = ?");
            $stmt_game->bind_param("i", $id); $stmt_game->execute(); $game_info = $stmt_game->get_result()->fetch_assoc(); $stmt_game->close();
            if ($game_info) {
                $stmt = $conn->prepare("UPDATE games SET status = ? WHERE id = ?");
                $stmt->bind_param("si", $value, $id);
                if ($stmt->execute()) {
                    log_admin_action($conn, $admin_id, "Changed game ID $id ('{$game_info['title']}') status to $value");
                    if ($value === 'banned') { send_notification($conn, $game_info['user_id'], 'game_banned', $id, "Your game, '{$game_info['title']}', has been banned for violating platform policies."); }
                    echo json_encode(['success' => true]);
                } else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
                $stmt->close();
            }
            break;

        case 'delete_category':
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) { log_admin_action($conn, $admin_id, "Deleted category ID: $id"); echo json_encode(['success' => true]); }
            else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
            $stmt->close();
            break;

        case 'add_category':
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $value);
            if ($stmt->execute()) { log_admin_action($conn, $admin_id, "Added new category: '$value'"); echo json_encode(['success' => true, 'new_id' => $conn->insert_id]); }
            else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
            $stmt->close();
            break;

        case 'delete_review':
            $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) { log_admin_action($conn, $admin_id, "Deleted review ID: $id"); echo json_encode(['success' => true]); }
            else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
            $stmt->close();
            break;

        case 'send_warning':
            $stmt_user = $conn->prepare("SELECT d.user_id, g.title FROM reports r JOIN games g ON r.game_id = g.id JOIN developers d ON g.developer_id = d.id WHERE r.id = ?");
            $stmt_user->bind_param("i", $id); $stmt_user->execute(); $report_info = $stmt_user->get_result()->fetch_assoc(); $stmt_user->close();
            if ($report_info) {
                send_notification($conn, $report_info['user_id'], 'developer_warning', $id, "You have received a warning regarding your game '{$report_info['title']}' based on a user report.");
                log_admin_action($conn, $admin_id, "Sent warning for report ID $id");
                echo json_encode(['success' => true, 'message' => 'Warning sent!']);
            } else { echo json_encode(['success' => false, 'message' => 'Could not find developer to warn.']); }
            break;

        case 'resolve_report':
            $stmt_user = $conn->prepare("SELECT user_id, game_id FROM reports WHERE id = ?");
            $stmt_user->bind_param("i", $id); $stmt_user->execute(); $report_info = $stmt_user->get_result()->fetch_assoc(); $stmt_user->close();
            $stmt = $conn->prepare("UPDATE reports SET is_resolved = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if ($report_info) { send_notification($conn, $report_info['user_id'], 'report_resolved', $report_info['game_id'], "Thank you for your feedback. The report you submitted has been reviewed and closed."); }
                log_admin_action($conn, $admin_id, "Resolved report ID $id");
                echo json_encode(['success' => true]);
            } else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
            $stmt->close();
            break;
        
        case 'add_discount':
            $stmt = $conn->prepare("INSERT INTO discounts (code, type, value, start_date, end_date, active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdssi", $data['code'], $data['type'], $data['value'], $data['start_date'], $data['end_date'], $data['active']);
            if ($stmt->execute()) { log_admin_action($conn, $admin_id, "Added new discount code: {$data['code']}"); echo json_encode(['success' => true]); }
            else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
            $stmt->close();
            break;

        case 'toggle_discount_status':
            $stmt = $conn->prepare("UPDATE discounts SET active = ? WHERE id = ?");
            $stmt->bind_param("ii", $value, $id);
            if ($stmt->execute()) { log_admin_action($conn, $admin_id, "Toggled active status for discount ID: $id to $value"); echo json_encode(['success' => true]); }
            else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
            $stmt->close();
            break;

        case 'delete_discount':
            $stmt = $conn->prepare("DELETE FROM discounts WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) { log_admin_action($conn, $admin_id, "Deleted discount ID: $id"); echo json_encode(['success' => true]); }
            else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
            $stmt->close();
            break;

        case 'mark_payout_paid':
          
            $stmt = $conn->prepare("UPDATE payouts SET status = 'completed', paid_at = NOW() WHERE id = ? AND status = 'pending'");
            $stmt->bind_param("i", $id);
            if ($stmt->execute() && $stmt->affected_rows > 0) { log_admin_action($conn, $admin_id, "Marked payout ID: $id as paid."); echo json_encode(['success' => true]); }
            else { echo json_encode(['success' => false, 'message' => $stmt->error ? $stmt->error : 'Payout already marked or not found.']); }
            $stmt->close();
            break;
        
        case 'send_global_notification':
            $users_result = $conn->query("SELECT id FROM users");
            $user_ids = $users_result->fetch_all(MYSQLI_ASSOC);
            $users_result->close();
            
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message, is_read, created_at) VALUES (?, 'global', ?, 0, NOW())");
                foreach ($user_ids as $user) {
                    $stmt->bind_param("is", $user['id'], $value);
                    $stmt->execute();
                }
                $stmt->close();
                $conn->commit();
                log_admin_action($conn, $admin_id, "Sent global notification: " . substr($value, 0, 50) . "...");
                echo json_encode(['success' => true, 'message' => 'Global notification sent to all users.']);
            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to send notifications.']);
            }
            break;

        default:
            http_response_code(400); echo json_encode(['success' => false, 'message' => 'Invalid Action']); break;
    }
    $conn->close();
    exit();
}


// PHP Backend Starting
session_start();
require_once 'connection.php';

$_SESSION['user_id'] = 1; 
$_SESSION['user_role'] = 'admin';
$_SESSION['username'] = 'Admin';

$page = $_GET['page'] ?? 'dashboard';
$data = [];

switch($page) {
    case 'users':
        $stmt = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
        $data = $stmt->fetch_all(MYSQLI_ASSOC);
        break;
    case 'games':
        $stmt = $conn->query("SELECT g.id, g.title, g.price, g.status, u.name as developer_name FROM games g LEFT JOIN developers d ON g.developer_id = d.id LEFT JOIN users u ON d.user_id = u.id ORDER BY g.created_at DESC");
        $data = $stmt->fetch_all(MYSQLI_ASSOC);
        break;
    case 'categories':
        $data = $conn->query("SELECT id, name FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'reviews':
        $stmt = $conn->query("SELECT r.id, r.review_text, r.rating, g.title as game_title, u.name as user_name FROM reviews r JOIN games g ON r.game_id = g.id JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC");
        $data = $stmt->fetch_all(MYSQLI_ASSOC);
        break;
    case 'reports':
        $stmt = $conn->query("SELECT r.id, r.game_id, r.user_id as reporter_id, r.report_type, r.is_resolved, g.title as game_title, u.name as reporter_name FROM reports r JOIN games g ON r.game_id = g.id JOIN users u ON r.user_id = u.id ORDER BY r.is_resolved ASC, r.created_at DESC");
        $data = $stmt->fetch_all(MYSQLI_ASSOC);
        break;
    case 'discounts':
        $data = $conn->query("SELECT id, code, type, value, start_date, end_date, active FROM discounts ORDER BY active DESC, end_date DESC")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'payouts':
        $data = $conn->query("SELECT p.id, p.amount, p.status, p.requested_at, p.paid_at, u.name as developer_name FROM payouts p JOIN developers d ON p.developer_id = d.id JOIN users u ON d.user_id = u.id ORDER BY p.status ASC, p.requested_at DESC")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'notifications':
    
        break;
    case 'logs':
        $stmt = $conn->query("SELECT a.id, a.action, a.log_time, u.name as admin_name FROM admin_logs a JOIN users u ON a.admin_id = u.id ORDER BY a.log_time DESC LIMIT 250");
        $data = $stmt->fetch_all(MYSQLI_ASSOC);
        break;
    default:
        $page = 'dashboard';
        $stats_query = $conn->query("SELECT 
            (SELECT COUNT(*) FROM users) as total_users, 
            (SELECT COUNT(*) FROM games) as total_games, 
            (SELECT SUM(price_paid) FROM purchases) as total_revenue, 
            (SELECT COUNT(*) FROM reports WHERE is_resolved = 0) as pending_reports,
            (SELECT SUM(price_paid) FROM purchases WHERE purchase_date >= CURDATE() - INTERVAL 30 DAY) as revenue_last_30_days,
            (SELECT COUNT(*) FROM users WHERE created_at >= CURDATE() - INTERVAL 30 DAY) as new_users_last_30_days
        ");
        $data['stats'] = $stats_query->fetch_assoc();
        
        $revenue_chart_query = $conn->query("SELECT DATE_FORMAT(purchase_date, '%Y-%m-%d') as date, SUM(price_paid) as daily_revenue FROM purchases WHERE purchase_date >= CURDATE() - INTERVAL 30 DAY GROUP BY date ORDER BY date ASC");
        $data['revenue_chart'] = $revenue_chart_query->fetch_all(MYSQLI_ASSOC);
        
        $top_games_query = $conn->query("SELECT g.title, COUNT(p.id) as sales_count FROM purchases p JOIN games g ON p.game_id = g.id GROUP BY g.id, g.title ORDER BY sales_count DESC LIMIT 5");
        $data['top_games'] = $top_games_query->fetch_all(MYSQLI_ASSOC);

        $roles_query = $conn->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
        $data['user_roles'] = $roles_query->fetch_all(MYSQLI_ASSOC);

        $logs_query = $conn->query("SELECT a.action, a.log_time, u.name as admin_name FROM admin_logs a JOIN users u ON a.admin_id = u.id ORDER BY a.log_time DESC LIMIT 5");
        $data['recent_logs'] = $logs_query->fetch_all(MYSQLI_ASSOC);
        break;
}
$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Gamer's Valt</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>

    :root { --primary-neon: #FF8C00; --background-station: #0d0d10; --background-panel: #1a1a1e; --text-primary: #e8e8e8; --text-secondary: #a0a8b4; --border-color-faint: rgba(255, 140, 0, 0.15); --font-primary: 'Inter', system-ui, sans-serif; --error-color: #ff4d4d; --success-color: #4CAF50; --info-color: #2196F3; }
    @keyframes slideUpIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    body { background-color: var(--background-station); font-family: var(--font-primary); color: var(--text-primary); margin: 0; }
    .admin-layout { display: flex; }
    .admin-sidebar { width: 250px; background: #000; height: 100vh; position: fixed; display: flex; flex-direction: column; }
    .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid var(--border-color-faint); }
    .sidebar-header a { color: var(--primary-neon); text-decoration: none; font-size: 1.5rem; font-weight: bold; }
    .sidebar-nav { display: flex; flex-direction: column; padding: 20px 0; flex-grow: 1; overflow-y: auto; }
    .sidebar-nav a { color: var(--text-secondary); text-decoration: none; padding: 15px 20px; font-weight: 500; transition: all 0.2s; border-left: 3px solid transparent; }
    .sidebar-nav a:hover { color: var(--text-primary); background: var(--background-panel); }
    .sidebar-nav a.active { color: var(--primary-neon); background: var(--background-panel); border-left-color: var(--primary-neon); }
    .sidebar-nav .back-to-site { margin-top: auto; border-top: 1px solid var(--border-color-faint); }
    .admin-main-content { margin-left: 250px; width: calc(100% - 250px); padding: 40px; }
    .admin-header { animation: slideUpIn 0.6s backwards; margin-bottom: 40px; }
    .admin-header h1 { font-size: 2.5rem; margin: 0; } .admin-header p { color: var(--text-secondary); margin: 5px 0 0; }
    .dashboard-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 30px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; margin-bottom: 40px; grid-column: 1 / -1; }
    .stat-card { background: var(--background-panel); padding: 25px; border-radius: 8px; border: 1px solid var(--border-color-faint); }
    .stat-card h3 { font-size: 1rem; color: var(--text-secondary); text-transform: uppercase; margin: 0 0 10px; }
    .stat-card .stat-value { font-size: 2.5rem; font-weight: 800; color: var(--primary-neon); }
    .stat-card.alert .stat-value { color: var(--error-color); }
    .chart-container { background: var(--background-panel); padding: 25px; border-radius: 8px; border: 1px solid var(--border-color-faint); }
    .chart-container.full-width { grid-column: 1 / -1; }
    .chart-container.half-width { grid-column: span 6; }
    .recent-logs-container { grid-column: 1 / -1; }
    .table-controls { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px; align-items: center; }
    .table-controls input, .table-controls select { padding: 12px; background: var(--background-panel); border: 1px solid var(--border-color-faint); border-radius: 6px; color: var(--text-primary); font-size: 1rem; flex-grow: 1; }
    .table-controls button { background-color: var(--primary-neon); color: black; font-weight: bold; cursor: pointer; border: none; padding: 12px 20px; border-radius: 6px; }
    .table-responsive { overflow-x: auto; background: var(--background-panel); border-radius: 8px; border: 1px solid var(--border-color-faint); }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 15px 20px; text-align: left; white-space: nowrap; }
    thead { background-color: #111; border-bottom: 2px solid var(--primary-neon); }
    tbody tr { border-bottom: 1px solid var(--border-color-faint); transition: background-color 0.2s, opacity 0.4s, transform 0.4s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background-color: #2c2c31; }
    tbody tr.filtered-out { display: none; }
    .action-button-sm { background: #333; color: var(--text-primary); padding: 8px 15px; border-radius: 6px; border: none; cursor: pointer; transition: background-color 0.2s; }
    .action-button-sm:hover { background: #444; }
    .action-button-sm.delete { background-color: var(--error-color); color: white; }
    .action-button-sm.resolve { background-color: var(--success-color); color: white; }
    .action-button-sm.payout { background-color: var(--info-color); color: white; }
    .action-select { background: #333; color: var(--text-primary); padding: 8px; border-radius: 6px; border: 1px solid #555; }
    .status-pill { padding: 5px 12px; border-radius: 15px; font-weight: 600; font-size: 0.8rem; display: inline-block; text-align: center; }
    .status-active, .status-completed, .status-published { background-color: rgba(76, 175, 80, 0.2); color: var(--success-color); }
    .status-inactive, .status-pending, .status-draft { background-color: rgba(255, 140, 0, 0.2); color: var(--primary-neon); }
    .status-banned { background-color: rgba(255, 77, 77, 0.2); color: var(--error-color); }
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); display: none; justify-content: center; align-items: center; z-index: 2000; opacity: 0; transition: opacity 0.3s ease; }
    .modal-overlay.visible { display: flex; opacity: 1; }
    .modal-content { background: var(--background-panel); padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; border: 1px solid var(--border-color-faint); position: relative; transform: scale(0.95); transition: transform 0.3s ease; }
    .modal-overlay.visible .modal-content { transform: scale(1); }
    .modal-header { text-align: center; margin-bottom: 20px; }
    .modal-header h3 { font-size: 1.8rem; margin: 0; } .modal-header p { color: var(--text-secondary); }
    .modal-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; flex-wrap: wrap; }
    .modal-actions.vertical { flex-direction: column; align-items: stretch; }
    .modal-button { padding: 10px 20px; border-radius: 6px; font-weight: 600; border: none; cursor: pointer; transition: transform 0.2s; }
    .modal-button:hover { transform: scale(1.05); }
    .modal-button.primary { background-color: var(--primary-neon); color: black; }
    .modal-button.secondary { background-color: #333; color: var(--text-primary); }
    .modal-button.danger { background-color: var(--error-color); color: white; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;}
    .form-grid .full-width { grid-column: 1 / -1; }
    .form-group { display: flex; flex-direction: column; }
    .form-group label { margin-bottom: 8px; color: var(--text-secondary); font-size: 0.9rem; }
    .form-group input, .form-group select { width: 100%; box-sizing: border-box; }
    textarea#notification-message { background: var(--background-panel); border: 1px solid var(--border-color-faint); border-radius: 6px; color: var(--text-primary); font-size: 1rem; padding: 12px; min-height: 150px; resize: vertical; }
    .toast { position: fixed; bottom: 20px; right: 20px; background-color: var(--background-panel); color: var(--text-primary); padding: 15px 25px; border-radius: 8px; border-left: 5px solid var(--primary-neon); z-index: 3000; opacity: 0; transform: translateX(100%); transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55); box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
    .toast.show { opacity: 1; transform: translateX(0); }
    .toast.success { border-left-color: var(--success-color); }
    .toast.error { border-left-color: var(--error-color); }
  
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-header"><a href="admin.php">Gamer's Valt</a></div>
            <nav class="sidebar-nav">
                <a href="admin.php?page=dashboard" class="<?php if($page === 'dashboard') echo 'active'; ?>">Dashboard</a>
                <a href="admin.php?page=users" class="<?php if($page === 'users') echo 'active'; ?>">Users</a>
                <a href="admin.php?page=games" class="<?php if($page === 'games') echo 'active'; ?>">Games</a>
                <a href="admin.php?page=categories" class="<?php if($page === 'categories') echo 'active'; ?>">Categories</a>
                <a href="admin.php?page=reviews" class="<?php if($page === 'reviews') echo 'active'; ?>">Reviews</a>
                <a href="admin.php?page=reports" class="<?php if($page === 'reports') echo 'active'; ?>">Reports</a>
                <a href="admin.php?page=discounts" class="<?php if($page === 'discounts') echo 'active'; ?>">Discounts</a>
                <a href="admin.php?page=notifications" class="<?php if($page === 'notifications') echo 'active'; ?>">Global Notifications</a>
                <a href="admin.php?page=logs" class="<?php if($page === 'logs') echo 'active'; ?>">Admin Logs</a>
                <a href="home.php" class="back-to-site">Back to Site</a>
            </nav>
        </aside>
        <main class="admin-main-content">
            <?php if ($page === 'dashboard'): ?>
                <header class="admin-header"><h1>Dashboard</h1><p>Platform-wide overview and key metrics.</p></header>
                <div class="stats-grid">
                    <div class="stat-card"><h3>Total Revenue</h3><span class="stat-value">$<?php echo number_format($data['stats']['total_revenue'] ?? 0, 2); ?></span></div>
                    <div class="stat-card"><h3>Revenue (30d)</h3><span class="stat-value">$<?php echo number_format($data['stats']['revenue_last_30_days'] ?? 0, 2); ?></span></div>
                    <div class="stat-card"><h3>Total Users</h3><span class="stat-value"><?php echo $data['stats']['total_users'] ?? 0; ?></span></div>
                    <div class="stat-card"><h3>New Users (30d)</h3><span class="stat-value"><?php echo $data['stats']['new_users_last_30_days'] ?? 0; ?></span></div>
                    <div class="stat-card"><h3>Total Games</h3><span class="stat-value"><?php echo $data['stats']['total_games'] ?? 0; ?></span></div>
                    <div class="stat-card alert"><h3>Pending Reports</h3><span class="stat-value"><?php echo $data['stats']['pending_reports'] ?? 0; ?></span></div>
                </div>
                <div class="dashboard-grid">
                    <div class="chart-container full-width"><canvas id="revenueChart"></canvas></div>
                    <div class="chart-container half-width"><canvas id="topGamesChart"></canvas></div>
                    <div class="chart-container half-width"><canvas id="userRolesChart"></canvas></div>
                    <div class="chart-container recent-logs-container">
                        <header class="admin-header" style="margin-bottom: 20px;"><h3>Recent Admin Activity</h3></header>
                        <div class="table-responsive"><table><thead><tr><th>Admin</th><th>Action</th><th>Timestamp</th></tr></thead><tbody>
                            <?php foreach($data['recent_logs'] as $row): echo "<tr><td>".htmlspecialchars($row['admin_name'])."</td><td>".htmlspecialchars($row['action'])."</td><td>".date("M j, Y, g:i a", strtotime($row['log_time']))."</td></tr>"; endforeach; ?>
                        </tbody></table></div>
                    </div>
                </div>

            <?php elseif ($page === 'users'): ?>
                <header class="admin-header"><h1>Manage Users</h1></header>
                <div class="table-controls"><input type="text" id="user-search" class="table-filter" placeholder="Search by name or email..." data-table="users-table"></div>
                <div class="table-responsive">
                    <table id="users-table"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
                    <?php foreach($data as $row): ?>
                        <tr data-id="<?php echo $row['id']; ?>">
                            <td><?php echo $row['id']; ?></td><td data-filter-col><?php echo htmlspecialchars($row['name']); ?></td><td data-filter-col><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><select class="action-select user-role-select" data-id="<?php echo $row['id']; ?>" <?php if($row['id'] == $_SESSION['user_id']) echo 'disabled'; ?>><option value="user" <?php if($row['role']=='user') echo 'selected'; ?>>User</option><option value="developer" <?php if($row['role']=='developer') echo 'selected'; ?>>Developer</option><option value="admin" <?php if($row['role']=='admin') echo 'selected'; ?>>Admin</option></select></td>
                            <td><?php echo date("M j, Y", strtotime($row['created_at'])); ?></td>
                            <td><button class="action-button-sm delete" data-action="delete_user" data-id="<?php echo $row['id']; ?>" <?php if($row['id'] == $_SESSION['user_id']) echo 'disabled'; ?>>Delete</button></td></tr>
                    <?php endforeach; ?>
                    </tbody></table>
                </div>

            <?php elseif ($page === 'games'): ?>
                <header class="admin-header"><h1>Manage Games</h1></header>
                <div class="table-controls"><input type="text" id="game-search" class="table-filter" placeholder="Search by title or developer..." data-table="games-table"></div>
                <div class="table-responsive">
                    <table id="games-table"><thead><tr><th>ID</th><th>Title</th><th>Developer</th><th>Price</th><th>Status</th></tr></thead><tbody>
                    <?php foreach($data as $row): ?>
                        <tr data-id="<?php echo $row['id']; ?>"><td><?php echo $row['id']; ?></td><td data-filter-col><?php echo htmlspecialchars($row['title']); ?></td><td data-filter-col><?php echo htmlspecialchars($row['developer_name'] ?? 'N/A'); ?></td><td>$<?php echo number_format($row['price'], 2); ?></td>
                        <td><select class="action-select game-status-select" data-id="<?php echo $row['id']; ?>"><option value="draft" <?php if($row['status']=='draft') echo 'selected'; ?>>Draft</option><option value="published" <?php if($row['status']=='published') echo 'selected'; ?>>Published</option><option value="banned" <?php if($row['status']=='banned') echo 'selected'; ?>>Banned</option></select></td></tr>
                    <?php endforeach; ?>
                    </tbody></table>
                </div>

            <?php elseif ($page === 'categories'): ?>
                <header class="admin-header"><h1>Manage Categories</h1></header>
                <div class="table-controls"><input type="text" id="new-category-name" placeholder="New category name..."><button id="add-category-btn">Add Category</button></div>
                <div class="table-responsive"><table><thead><tr><th>ID</th><th>Name</th><th>Actions</th></tr></thead><tbody id="categories-table-body">
                    <?php foreach($data as $row): ?>
                        <tr data-id="<?php echo $row['id']; ?>"><td><?php echo $row['id']; ?></td><td><?php echo htmlspecialchars($row['name']); ?></td><td><button class="action-button-sm delete" data-action="delete_category" data-id="<?php echo $row['id']; ?>">Delete</button></td></tr>
                    <?php endforeach; ?>
                </tbody></table></div>
            
            <?php elseif ($page === 'reviews'): ?>
                <header class="admin-header"><h1>Manage Reviews</h1></header>
                <div class="table-controls"><input type="text" id="review-search" class="table-filter" placeholder="Search by game, user, or review text..." data-table="reviews-table"></div>
                <div class="table-responsive">
                    <table id="reviews-table"><thead><tr><th>ID</th><th>Review (excerpt)</th><th>Game</th><th>User</th><th>Rating</th><th>Actions</th></tr></thead><tbody>
                    <?php foreach($data as $row): ?>
                        <tr data-id="<?php echo $row['id']; ?>"><td><?php echo $row['id']; ?></td><td data-filter-col><?php echo htmlspecialchars(substr($row['review_text'], 0, 50)); ?>...</td><td data-filter-col><?php echo htmlspecialchars($row['game_title']); ?></td><td data-filter-col><?php echo htmlspecialchars($row['user_name']); ?></td><td><?php echo str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']); ?></td><td><button class="action-button-sm delete" data-action="delete_review" data-id="<?php echo $row['id']; ?>">Delete</button></td></tr>
                    <?php endforeach; ?>
                    </tbody></table>
                </div>

            <?php elseif ($page === 'reports'): ?>
                <header class="admin-header"><h1>Manage Reports</h1></header>
                <div class="table-responsive"><table><thead><tr><th>ID</th><th>Game</th><th>Reporter</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead><tbody id="reports-table-body">
                    <?php foreach($data as $row): ?>
                        <tr data-id="<?php echo $row['id']; ?>" data-game-id="<?php echo $row['game_id']; ?>">
                            <td><?php echo $row['id']; ?></td><td><?php echo htmlspecialchars($row['game_title']); ?></td><td><?php echo htmlspecialchars($row['reporter_name']); ?></td><td><?php echo htmlspecialchars($row['report_type']); ?></td>
                            <td class="report-status"><span class="status-pill <?php echo ($row['is_resolved'] ? 'status-active' : 'status-pending'); ?>"><?php echo ($row['is_resolved'] ? 'Resolved' : 'Pending'); ?></span></td>
                            <td class="report-actions"><?php if(!$row['is_resolved']): ?><button class="action-button-sm resolve resolve-btn" data-id="<?php echo $row['id']; ?>">Review</button><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody></table></div>

            <?php elseif ($page === 'discounts'): ?>
                <header class="admin-header"><h1>Manage Discounts</h1></header>
                <div class="table-controls"><button id="add-discount-btn">Add New Discount</button></div>
                <div class="table-responsive"><table><thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                    <?php foreach($data as $row): ?>
                        <tr data-id="<?php echo $row['id']; ?>">
                            <td><?php echo htmlspecialchars($row['code']); ?></td><td><?php echo ucfirst($row['type']); ?></td><td><?php echo $row['type'] == 'percentage' ? $row['value'].'%' : '$'.number_format($row['value'], 2); ?></td>
                            <td><?php echo date("M j, Y", strtotime($row['start_date'])); ?></td><td><?php echo date("M j, Y", strtotime($row['end_date'])); ?></td>
                            <td><span class="status-pill <?php echo $row['active'] ? 'status-active' : 'status-inactive'; ?>"><?php echo $row['active'] ? 'Active' : 'Inactive'; ?></span></td>
                            <td>
                                <button class="action-button-sm toggle-status" data-action="toggle_discount_status" data-id="<?php echo $row['id']; ?>" data-value="<?php echo $row['active'] ? '0' : '1'; ?>"><?php echo $row['active'] ? 'Deactivate' : 'Activate'; ?></button>
                                <button class="action-button-sm delete" data-action="delete_discount" data-id="<?php echo $row['id']; ?>">Delete</button>
                            </td></tr>
                    <?php endforeach; ?>
                </tbody></table></div>

            <?php elseif ($page === 'payouts'): ?>
                <header class="admin-header"><h1>Developer Payouts</h1></header>
                <div class="table-responsive"><table><thead><tr><th>ID</th><th>Developer</th><th>Amount</th><th>Requested</th><th>Paid On</th><th>Status</th><th>Actions</th></tr></thead><tbody>
                    <?php foreach($data as $row): ?>
                        <tr data-id="<?php echo $row['id']; ?>">
                            <td><?php echo $row['id']; ?></td><td><?php echo htmlspecialchars($row['developer_name']); ?></td><td>$<?php echo number_format($row['amount'], 2); ?></td><td><?php echo date("M j, Y", strtotime($row['requested_at'])); ?></td><td><?php echo $row['paid_at'] ? date("M j, Y", strtotime($row['paid_at'])) : 'N/A'; ?></td>
                            <td><span class="status-pill <?php echo 'status-'.strtolower($row['status']); ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            <td><?php if($row['status'] == 'pending'): ?><button class="action-button-sm payout" data-action="mark_payout_paid" data-id="<?php echo $row['id']; ?>">Mark as Paid</button><?php endif; ?></td></tr>
                    <?php endforeach; ?>
                </tbody></table></div>

            <?php elseif ($page === 'notifications'): ?>
                <header class="admin-header"><h1>Send Global Notification</h1><p>Broadcast a message to all users on the platform.</p></header>
                <div class="form-group"><label for="notification-message">Message</label><textarea id="notification-message" placeholder="Enter the notification message..."></textarea></div>
                <div class="modal-actions" style="justify-content:flex-start; margin-top: 20px;"><button id="send-global-notification-btn" class="modal-button primary">Send to All Users</button></div>

            <?php elseif ($page === 'logs'): ?>
                <header class="admin-header"><h1>Admin Activity Logs</h1></header>
                <div class="table-responsive"><table><thead><tr><th>ID</th><th>Admin</th><th>Action</th><th>Timestamp</th></tr></thead><tbody>
                    <?php foreach($data as $row): echo "<tr><td>".$row['id']."</td><td>".htmlspecialchars($row['admin_name'])."</td><td>".htmlspecialchars($row['action'])."</td><td>".date("M j, Y, g:i a", strtotime($row['log_time']))."</td></tr>"; endforeach; ?>
                </tbody></table></div>
            <?php endif; ?>
        </main>
    </div>

    <div id="confirmation-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header"><h3>Are you sure?</h3><p id="confirmation-text"></p></div>
            <div class="modal-actions"><button class="modal-button secondary" data-close>Cancel</button><button id="confirm-btn" class="modal-button danger">Confirm</button></div>
        </div>
    </div>
    <div id="resolve-report-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header"><h3>Resolve Report</h3><p id="resolve-game-title"></p></div>
            <div class="modal-actions vertical">
                <a href="#" id="resolve-visit-game" target="_blank" class="modal-button secondary">Visit Game Page</a>
                <button id="resolve-ban-game" class="modal-button danger">Ban Game & Resolve</button>
                <button id="resolve-send-warning" class="modal-button">Send Warning & Resolve</button>
                <button id="resolve-only" class="modal-button primary">Just Mark as Resolved</button>
                <button data-close class="modal-button secondary" style="margin-top: 20px;">Cancel</button>
            </div>
        </div>
    </div>
    <div id="add-discount-modal" class="modal-overlay">
        <div class="modal-content">
            <form id="add-discount-form">
                <div class="modal-header"><h3>Add New Discount</h3></div>
                <div class="form-grid">
                    <div class="form-group full-width"><label for="discount-code">Discount Code</label><input type="text" id="discount-code" required></div>
                    <div class="form-group"><label for="discount-type">Type</label><select id="discount-type"><option value="percentage">Percentage</option><option value="fixed">Fixed Amount</option></select></div>
                    <div class="form-group"><label for="discount-value">Value</label><input type="number" id="discount-value" step="0.01" required></div>
                    <div class="form-group"><label for="discount-start">Start Date</label><input type="date" id="discount-start" required></div>
                    <div class="form-group"><label for="discount-end">End Date</label><input type="date" id="discount-end" required></div>
                </div>
                <div class="modal-actions"><button type="button" class="modal-button secondary" data-close>Cancel</button><button type="submit" class="modal-button primary">Add Discount</button></div>
            </form>
        </div>
    </div>
    <div id="toast-container"></div>

    <script>
    // Admin JS Starting
    document.addEventListener('DOMContentLoaded', function() {
        const modals = {
            confirmation: document.getElementById('confirmation-modal'),
            resolve: document.getElementById('resolve-report-modal'),
            addDiscount: document.getElementById('add-discount-modal')
        };
        let currentActionInfo = {};

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                toast.addEventListener('transitionend', () => toast.remove());
            }, 4000);
        }

        function handleAjaxAction(action, id = 0, value = null, payload = {}) {
            const formData = new FormData();
            formData.append('action', action);
            if (id) formData.append('id', id);
            if (value !== null) formData.append('value', value);
            if (Object.keys(payload).length > 0) {
                for (const key in payload) {
                    formData.append(`data[${key}]`, payload[key]);
                }
            }
            return fetch('admin.php', { method: 'POST', body: formData }).then(res => res.json());
        }

        function showModal(modal, text, action, id, extra = {}) {
            currentActionInfo = { action, id, ...extra };
            if (modal === modals.confirmation && text) document.getElementById('confirmation-text').textContent = text;
            modal.classList.add('visible');
        }

        function hideAllModals() {
            Object.values(modals).forEach(m => m && m.classList.remove('visible'));
        }

        document.body.addEventListener('click', function(e) {
            const target = e.target;
            if (target.closest('[data-close]')) hideAllModals();

            if (target.matches('.delete')) {
                const action = target.dataset.action;
                const id = target.dataset.id;
                let message = 'This will permanently delete the item. This action cannot be undone.';
                if(action === 'delete_user') message = 'This will permanently delete the user and all associated data. This is irreversible.';
                showModal(modals.confirmation, message, action, id);
            }
            if (target.matches('.toggle-status')) {
                const row = target.closest('tr');
                handleAjaxAction(target.dataset.action, target.dataset.id, target.dataset.value).then(res => {
                    if (res.success) { showToast('Status updated successfully.'); setTimeout(()=>window.location.reload(), 500); }
                    else { showToast(res.message || 'Action failed.', 'error'); }
                });
            }
            if (target.matches('.payout')) {
                currentActionInfo = { action: target.dataset.action, id: target.dataset.id };
                const row = target.closest('tr');
                const devName = row.children[1].textContent;
                const amount = row.children[2].textContent;
                showModal(modals.confirmation, `Mark payout of ${amount} to ${devName} as completed? This cannot be undone.`, 'mark_payout_paid', currentActionInfo.id);
            }
            if (target.matches('.resolve-btn')) {
                const row = target.closest('tr');
                document.getElementById('resolve-game-title').textContent = `Report for: ${row.children[1].textContent}`;
                document.getElementById('resolve-visit-game').href = `innergamepage.php?id=${row.dataset.gameId}`;
                showModal(modals.resolve, null, 'resolve_report', target.dataset.id, { gameId: row.dataset.gameId });
            }
            if (target.id === 'add-discount-btn') {
                showModal(modals.addDiscount);
            }
        });

        document.getElementById('confirm-btn')?.addEventListener('click', function() {
            if (currentActionInfo.action && currentActionInfo.id) {
                const row = document.querySelector(`tr[data-id='${currentActionInfo.id}']`);
                handleAjaxAction(currentActionInfo.action, currentActionInfo.id).then(res => {
                    if (res.success) {
                        if (currentActionInfo.action === 'mark_payout_paid') {
                             showToast('Payout marked as paid.');
                             setTimeout(()=>window.location.reload(), 500);
                        } else {
                            if(row) {
                                row.style.opacity = 0;
                                setTimeout(() => row.remove(), 400);
                            }
                            showToast('Item deleted successfully.');
                        }
                    } else { showToast(res.message || 'Action failed.', 'error'); }
                    hideAllModals();
                });
            }
        });
        
        modals.resolve?.addEventListener('click', function(e) {
            const target = e.target.closest('button, a');
            if(!target || target.hasAttribute('data-close') || !modals.resolve.classList.contains('visible')) return;
            e.preventDefault();

            const { id: reportId, gameId } = currentActionInfo;
            if(!reportId) return;

            let mainActionPromise = Promise.resolve({success: true});
            let logMessage = '';

            if (target.id === 'resolve-ban-game') {
                mainActionPromise = handleAjaxAction('update_game_status', gameId, 'banned');
                logMessage = 'Game banned & report resolved.';
            } else if (target.id === 'resolve-send-warning') {
                mainActionPromise = handleAjaxAction('send_warning', reportId);
                logMessage = 'Warning sent & report resolved.';
            } else if (target.id === 'resolve-only') {
                 logMessage = 'Report marked as resolved.';
            } else if (target.id === 'resolve-visit-game') {
                window.open(target.href, '_blank');
                return;
            } else {
                return;
            }
            
            mainActionPromise.then(res => {
                if(res.success) {
                    handleAjaxAction('resolve_report', reportId).then(data => {
                        if (data.success) {
                            showToast(logMessage);
                            const row = document.querySelector(`tr[data-id='${reportId}']`);
                            row.querySelector('.report-status span').textContent = 'Resolved';
                            row.querySelector('.report-status span').className = 'status-pill status-active';
                            row.querySelector('.report-actions').innerHTML = '';
                        } else { showToast(data.message || 'Could not resolve report.', 'error'); }
                    });
                } else { showToast(res.message || 'Initial action failed.', 'error'); }
            });
            hideAllModals();
        });

        document.body.addEventListener('change', e => {
            const target = e.target;
            let action = '';
            if (target.matches('.user-role-select')) action = 'update_user_role';
            if (target.matches('.game-status-select')) action = 'update_game_status';
            
            if(action) {
                handleAjaxAction(action, target.dataset.id, target.value).then(res => {
                    if (res.success) showToast('Update successful.');
                    else showToast(res.message || 'Update failed.', 'error');
                });
            }
        });

        document.getElementById('add-category-btn')?.addEventListener('click', () => {
            const input = document.getElementById('new-category-name');
            if (input.value.trim()) {
                handleAjaxAction('add_category', 0, input.value.trim()).then(res => { 
                    if(res.success) { showToast('Category added!'); setTimeout(()=>window.location.reload(), 500); } 
                    else { showToast(res.message, 'error'); }
                });
            }
        });

        document.getElementById('add-discount-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const payload = {
                code: document.getElementById('discount-code').value,
                type: document.getElementById('discount-type').value,
                value: document.getElementById('discount-value').value,
                start_date: document.getElementById('discount-start').value,
                end_date: document.getElementById('discount-end').value,
                active: 1
            };
            handleAjaxAction('add_discount', 0, null, payload).then(res => {
                if (res.success) { showToast('Discount added successfully.'); setTimeout(()=>window.location.reload(), 500); }
                else { showToast(res.message || 'Failed to add discount.', 'error'); }
                hideAllModals();
            });
        });

        document.getElementById('send-global-notification-btn')?.addEventListener('click', function() {
            const message = document.getElementById('notification-message').value.trim();
            if(message) {
                showModal(modals.confirmation, 'Send this notification to every single user?', 'send_global_notification', 0, { message: message });
                document.getElementById('confirm-btn').onclick = () => {
                    handleAjaxAction('send_global_notification', 0, message).then(res => {
                        if (res.success) { showToast(res.message, 'success'); document.getElementById('notification-message').value = ''; }
                        else { showToast(res.message, 'error'); }
                        hideAllModals();
                    });
                };
            } else {
                showToast('Message cannot be empty.', 'error');
            }
        });

        document.querySelectorAll('.table-filter').forEach(input => {
            input.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const tableId = this.dataset.table;
                const table = document.getElementById(tableId);
                table.querySelectorAll('tbody tr').forEach(row => {
                    let match = false;
                    row.querySelectorAll('[data-filter-col]').forEach(cell => {
                        if (cell.textContent.toLowerCase().includes(searchTerm)) {
                            match = true;
                        }
                    });
                    row.style.display = match ? '' : 'none';
                });
            });
        });

        <?php if ($page === 'dashboard'): ?>
        const chartColors = { primary: 'rgba(255, 140, 0, 0.7)', primaryBorder: '#FF8C00', grid: 'rgba(255, 255, 255, 0.1)', text: '#e8e8e8' };
        
        const revenueData = <?php echo json_encode($data['revenue_chart'] ?? []); ?>;
        const revenueLabels = revenueData.map(d => d.date);
        const revenueValues = revenueData.map(d => d.daily_revenue);
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: { labels: revenueLabels, datasets: [{ label: 'Revenue (Last 30 Days)', data: revenueValues, backgroundColor: chartColors.primary, borderColor: chartColors.primaryBorder, tension: 0.3, fill: true }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { ticks: { color: chartColors.text }, grid: { color: chartColors.grid } }, x: { ticks: { color: chartColors.text }, grid: { color: chartColors.grid } } } }
        });

        const topGamesData = <?php echo json_encode($data['top_games'] ?? []); ?>;
        new Chart(document.getElementById('topGamesChart'), {
            type: 'bar',
            data: { labels: topGamesData.map(d => d.title), datasets: [{ label: 'Top 5 Games by Sales', data: topGamesData.map(d => d.sales_count), backgroundColor: chartColors.primary, borderColor: chartColors.primaryBorder }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, title: { display: true, text: 'Top Selling Games', color: chartColors.text, font: {size: 16}} }, scales: { y: { ticks: { color: chartColors.text }, grid: { color: chartColors.grid } }, x: { ticks: { color: chartColors.text }, grid: { display: false } } } }
        });

        const userRolesData = <?php echo json_encode($data['user_roles'] ?? []); ?>;
        new Chart(document.getElementById('userRolesChart'), {
            type: 'doughnut',
            data: { labels: userRolesData.map(d => d.role.charAt(0).toUpperCase() + d.role.slice(1)), datasets: [{ data: userRolesData.map(d => d.count), backgroundColor: ['#FF8C00', '#2196F3', '#4CAF50'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: {color: chartColors.text} }, title: { display: true, text: 'User Role Distribution', color: chartColors.text, font: {size: 16}} } }
        });
        <?php endif; ?>
    });
    // Admin JS Ending
    </script>
</body>
</html>