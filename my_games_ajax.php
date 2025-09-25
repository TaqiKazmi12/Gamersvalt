<?php
// My Games AJAX Starting
session_start();
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit();
}
$user_id = $_SESSION['user_id'];

$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'recent';

$sql_select = "SELECT g.id, g.title, g.thumbnail, p.purchase_date, p.playtime_hours, p.last_played";
$sql_from = " FROM purchases p JOIN games g ON p.game_id = g.id";
$sql_where = " WHERE p.user_id = ? AND g.title LIKE ?";
$params = [$user_id, $search];
$param_types = 'is';

$order_by = " ORDER BY p.purchase_date DESC";
if ($sort === 'played') {
    $order_by = " ORDER BY p.last_played DESC, p.purchase_date DESC";
} elseif ($sort === 'az') {
    $order_by = " ORDER BY g.title ASC";
} elseif ($sort === 'za') {
    $order_by = " ORDER BY g.title DESC";
}

$sql = $sql_select . $sql_from . $sql_where . $order_by;

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$owned_games = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

if (!empty($owned_games)) {
    foreach ($owned_games as $game) {
        $playtime_text = $game['playtime_hours'] > 0 ? $game['playtime_hours'] . ' hours' : 'Not Played';
        $last_played_text = $game['last_played'] ? date("M j, Y", strtotime($game['last_played'])) : 'Never';

        echo '
        <div class="game-card">
            <a href="innergamepage.php?id=' . $game['id'] . '" class="card-image-link">
                <img src="' . htmlspecialchars($game['thumbnail']) . '" alt="' . htmlspecialchars($game['title']) . '">
            </a>
            <div class="card-content">
                <div class="card-info-main">
                    <h3 class="card-title">' . htmlspecialchars($game['title']) . '</h3>
                    <div class="card-stats">
                        <div class="stat-item"><span>Play Time</span><strong>' . $playtime_text . '</strong></div>
                        <div class="stat-item"><span>Last Played</span><strong>' . $last_played_text . '</strong></div>
                    </div>
                </div>
                <div class="card-actions">
                    <a href="innergamepage.php?id=' . $game['id'] . '" class="action-button view-button">Store Page</a>
                    <button class="action-button download-button">Download</button>
                </div>
            </div>
        </div>';
    }
} else {
    echo '<div class="empty-library full-span">
            <h2>No Games Match Your Search</h2>
            <p>Try clearing your search or exploring the store to find new adventures!</p>
            <a href="explore.php" class="explore-button">Explore Games</a>
          </div>';
}
// My Games AJAX Ending
?>