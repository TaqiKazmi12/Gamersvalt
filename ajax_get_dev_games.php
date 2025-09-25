<?php
// Get Dev Games Starting
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'developer') {
    http_response_code(403); echo json_encode([]); exit();
}

$developer_id = $_SESSION['developer_id'];
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at_desc';

$sql_select = "SELECT 
                    g.id, g.title, g.price, g.status, g.created_at,
                    COUNT(p.id) as sales,
                    AVG(r.rating) as avg_rating
               FROM games g
               LEFT JOIN purchases p ON g.id = p.game_id
               LEFT JOIN reviews r ON g.id = r.game_id
               WHERE g.developer_id = ? AND g.title LIKE ?";

$sql_group_by = " GROUP BY g.id, g.title, g.price, g.status, g.created_at";

$order_by_options = [
    'created_at_desc' => 'g.created_at DESC',
    'sales_desc' => 'sales DESC',
    'rating_desc' => 'avg_rating DESC',
    'title_asc' => 'g.title ASC'
];
$sql_order_by = " ORDER BY " . ($order_by_options[$sort] ?? $order_by_options['created_at_desc']);

$sql = $sql_select . $sql_group_by . $sql_order_by;

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $developer_id, $search);
$stmt->execute();
$games = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

echo json_encode($games);
// Get Dev Games Ending
?>