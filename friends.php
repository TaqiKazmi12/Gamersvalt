<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php?redirect=friends.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$stmt_friends = $conn->prepare("SELECT u.id, u.name FROM friendships f JOIN users u ON u.id = IF(f.user_one_id = ?, f.user_two_id, f.user_one_id) WHERE (f.user_one_id = ? OR f.user_two_id = ?) AND f.status = 'accepted'");
$stmt_friends->bind_param("iii", $user_id, $user_id, $user_id);
$stmt_friends->execute();
$friends = $stmt_friends->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_friends->close();

$stmt_pending = $conn->prepare("SELECT u.id, u.name FROM friendships f JOIN users u ON u.id = f.action_user_id WHERE (f.user_one_id = ? OR f.user_two_id = ?) AND f.status = 'pending' AND f.action_user_id != ?");
$stmt_pending->bind_param("iii", $user_id, $user_id, $user_id);
$stmt_pending->execute();
$pending_requests = $stmt_pending->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_pending->close();
$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="friends.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Friends Page Starting -->
    <div class="social-container">
        <header class="social-header">
            <h1>Community Hub</h1>
        </header>

        <div class="social-layout">
            <aside class="social-sidebar">
                <nav class="social-nav">
                    <button class="social-tab-link active" data-tab="friends-list">All Friends <span class="count"><?php echo count($friends); ?></span></button>
                    <button class="social-tab-link" data-tab="pending-requests">Pending <span class="count"><?php echo count($pending_requests); ?></span></button>
                    <button class="social-tab-link" data-tab="add-friend">Add Friend</button>
                </nav>
            </aside>

            <main class="social-content">
                <div id="friends-list" class="social-tab-pane active">
                    <h3>All Friends</h3>
                    <div class="user-list" id="friend-user-list">
                        <?php if(!empty($friends)): foreach($friends as $friend): ?>
                        <div class="user-card" data-user-id="<?php echo $friend['id']; ?>">
                            <div class="user-info"><span class="user-name"><?php echo htmlspecialchars($friend['name']); ?></span></div>
                            <div class="user-actions">
            
                              
                                <button class="action-button-sm remove-btn" data-friend-id="<?php echo $friend['id']; ?>">Remove</button>
                            </div>
                        </div>
                        <?php endforeach; else: ?>
                        <p class="empty-message">You haven't added any friends yet. Find some in the "Add Friend" tab!</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="pending-requests" class="social-tab-pane">
                    <h3>Pending Friend Requests</h3>
                    <div class="user-list" id="pending-user-list">
                        <?php if(!empty($pending_requests)): foreach($pending_requests as $request): ?>
                        <div class="user-card" data-user-id="<?php echo $request['id']; ?>">
                            <div class="user-info"><span class="user-name"><?php echo htmlspecialchars($request['name']); ?></span></div>
                            <div class="user-actions">
                                <button class="action-button-sm accept-btn" data-friend-id="<?php echo $request['id']; ?>">Accept</button>
                                <button class="action-button-sm decline-btn" data-friend-id="<?php echo $request['id']; ?>">Decline</button>
                            </div>
                        </div>
                        <?php endforeach; else: ?>
                        <p class="empty-message">No pending friend requests.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="add-friend" class="social-tab-pane">
                    <h3>Add a New Friend</h3>
                    <form id="search-friend-form" class="add-friend-form">
                        <input type="text" name="search_term" placeholder="Enter Username..." required>
                        <button type="submit">Search</button>
                    </form>
                    <div id="search-results-list" class="user-list search-results"></div>
                </div>
            </main>
        </div>
    </div>
    <!-- Friends Page Ending -->
    <script src="friends.js"></script>
    <?php include "footer.php" ?>
</body>
</html>