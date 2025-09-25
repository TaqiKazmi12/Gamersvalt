<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: devlogin.php");
    exit();
}

if ($_SESSION['user_role'] !== 'developer') {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin_dashboard.php"); 
    } else {
        header("Location: home.php"); 
    }
    exit();
}

$developer_id = $_SESSION['developer_id'];

$sql_stats = "SELECT COUNT(g.id) as total_games, COUNT(p.id) as total_sales, SUM(p.price_paid) as total_revenue, AVG(r.rating) as avg_rating FROM games g LEFT JOIN purchases p ON g.id = p.game_id LEFT JOIN reviews r ON g.id = r.game_id WHERE g.developer_id = ?";
$stmt = $conn->prepare($sql_stats);
$stmt->bind_param("i", $developer_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$monthly_revenue = array_fill(0, 12, 0);
$sql_chart = "SELECT MONTH(p.purchase_date) as month, SUM(p.price_paid) as revenue FROM purchases p JOIN games g ON p.game_id = g.id WHERE g.developer_id = ? AND p.purchase_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY MONTH(p.purchase_date)";
$stmt_chart = $conn->prepare($sql_chart);
$stmt_chart->bind_param("i", $developer_id);
$stmt_chart->execute();
$chart_data = $stmt_chart->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($chart_data as $data) {
    $monthly_revenue[$data['month'] - 1] = $data['revenue'];
}
$stmt_chart->close();
$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Dashboard - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="dev_dashboard.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Dev Dashboard Page Starting -->
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <p>This is your mission control. Let's see how your games are performing.</p>
        </header>

        <section class="stats-grid">
            <div class="stat-card"><h3>Total Games</h3><span class="stat-value"><?php echo $stats['total_games'] ?? 0; ?></span></div>
            <div class="stat-card"><h3>Total Sales</h3><span class="stat-value"><?php echo $stats['total_sales'] ?? 0; ?></span></div>
            <div class="stat-card"><h3>Gross Revenue</h3><span class="stat-value">$<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></span></div>
            <div class="stat-card"><h3>Avg. Rating</h3><span class="stat-value"><?php echo number_format($stats['avg_rating'] ?? 0, 2); ?> ★</span></div>
        </section>

        <section class="dashboard-section">
            <h2>Monthly Revenue (Last 12 Months)</h2>
            <div class="chart-container"><canvas id="revenueChart"></canvas></div>
        </section>

        <section class="dashboard-section">
            <div class="section-header">
                <h2>My Games</h2>
                <a href="upload_game.php" class="action-button">Upload New Game</a>
            </div>
            <div class="table-controls">
                <input type="text" id="game-search-input" placeholder="Search your games...">
                <select id="game-sort-select">
                    <option value="created_at_desc">Sort by: Newest</option>
                    <option value="sales_desc">Sort by: Most Sales</option>
                    <option value="rating_desc">Sort by: Highest Rating</option>
                    <option value="title_asc">Sort by: A-Z</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="games-table">
                    <thead><tr><th>Title</th><th>Price</th><th>Status</th><th>Sales</th><th>Avg. Rating</th><th>Actions</th></tr></thead>
                    <tbody id="games-table-body"></tbody>
                </table>
            </div>
        </section>
    </div>
    <!-- Dev Dashboard Page Ending -->
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Dev Dashboard Page Starting
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart');
        if (ctx) {
            const revenueData = <?php echo json_encode(array_values($monthly_revenue)); ?>;
            const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            new Chart(ctx, { type: 'bar', data: { labels: monthLabels, datasets: [{ label: 'Revenue ($)', data: revenueData, backgroundColor: 'rgba(255, 140, 0, 0.6)', borderColor: 'rgba(255, 140, 0, 1)', borderWidth: 1 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' } } }, plugins: { legend: { display: false } } } });
        }
        const searchInput = document.getElementById('game-search-input');
        const sortSelect = document.getElementById('game-sort-select');
        const tableBody = document.getElementById('games-table-body');
        function fetchAndRenderGames() {
            const searchTerm = searchInput.value;
            const sortTerm = sortSelect.value;
            tableBody.innerHTML = '<tr><td colspan="6" class="loading-row">Loading games...</td></tr>';
            fetch(`ajax_get_dev_games.php?search=${encodeURIComponent(searchTerm)}&sort=${sortTerm}`)
                .then(res => res.json())
                .then(games => {
                    tableBody.innerHTML = '';
                    if (games.length > 0) {
                        games.forEach(game => {
                            const row = document.createElement('tr');
                            const statusClass = game.status.toLowerCase();
                            const avgRating = game.avg_rating ? parseFloat(game.avg_rating).toFixed(2) + ' ★' : 'N/A';
                            const price = parseFloat(game.price) > 0 ? '$' + parseFloat(game.price).toFixed(2) : 'Free';
                            row.innerHTML = `<td>${game.title}</td><td>${price}</td><td><span class="status-badge ${statusClass}">${game.status}</span></td><td>${game.sales}</td><td>${avgRating}</td><td class="actions-cell"><a href="edit_game.php?id=${game.id}" class="action-button-sm">Edit</a><a href="innergamepage.php?id=${game.id}" class="action-button-sm view">View</a></td>`;
                            tableBody.appendChild(row);
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="6" class="loading-row">No games found.</td></tr>';
                    }
                });
        }
        searchInput.addEventListener('keyup', fetchAndRenderGames);
        sortSelect.addEventListener('change', fetchAndRenderGames);
        fetchAndRenderGames();
    });
    // Dev Dashboard Page Ending
    </script>
    <?php include "footer.php" ?>
</body>
</html>