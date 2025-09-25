<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['game_id']) || !is_numeric($_GET['game_id'])) {
    header("Location: explore.php");
    exit();
}

$game_id = (int)$_GET['game_id'];
$stmt = $conn->prepare("SELECT title FROM games WHERE id = ?");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$game = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Successful - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="checkout.css"> 
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Thank You Page Starting -->
    <div class="checkout-wrapper">
        <div class="checkout-container">
            <div class="order-summary-panel" style="text-align: center;">
                <div class="success-icon">✔</div>
                <h1>Purchase Complete!</h1>
                <p>
                    Thank you! <strong><?php echo htmlspecialchars($game['title']); ?></strong> is now in your library.
                    You can download it from "My Games" and write a review.
                </p>
                <div class="summary-totals" style="margin-top: 40px;">
                    <a href="my_games.php" class="purchase-button" style="width: auto; padding: 12px 30px;">Go to My Library</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Thank You Page Ending -->
     <?php include "footer.php" ?>
</body>
</html>