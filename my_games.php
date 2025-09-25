<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php?redirect=my_games.php");
    exit();
}
$user_id = $_SESSION['user_id'];

$sql = "SELECT g.id, g.title, g.thumbnail, p.purchase_date 
        FROM purchases p
        JOIN games g ON p.game_id = g.id
        WHERE p.user_id = ?
        ORDER BY p.purchase_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$owned_games = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Game Library - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="my_games.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- My Games Page Starting -->
    <div class="library-container">
        <header class="library-header">
            <h1>My Library</h1>
            <p>You own <?php echo count($owned_games); ?> games. Ready to play?</p>
        </header>

        <main class="library-content">
            <?php if (!empty($owned_games)): ?>
                <div class="library-grid">
                    <?php foreach ($owned_games as $game): ?>
                        <div class="game-card" data-game-id="<?php echo $game['id']; ?>">
                            <a href="innergamepage.php?id=<?php echo $game['id']; ?>" class="card-image-link">
                                <img src="<?php echo htmlspecialchars($game['thumbnail']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" loading="lazy">
                            </a>
                            <div class="card-content">
                                <h3 class="card-title"><?php echo htmlspecialchars($game['title']); ?></h3>
                                <div class="card-actions">
                                    <a href="innergamepage.php?id=<?php echo $game['id']; ?>" class="action-button view-button">Store Page</a>
                                   <button class="action-button download-button" 
                                            data-game-id="<?php echo $game['id']; ?>" 
                                            data-game-title="<?php echo htmlspecialchars($game['title']); ?>">
                                        <span class="button-text">Download</span>
                                        <span class="progress-bar"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-library">
                    <h2>Your Library is Empty</h2>
                    <p>Start exploring to find your next favorite game!</p>
                    <a href="explore.php" class="explore-button">Explore Games</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <!-- My Games Page Ending -->

    <script>
    // My Games Page Starting
    document.addEventListener('DOMContentLoaded', function () {
        function initializeButtonStates() {
            const downloadedGames = JSON.parse(localStorage.getItem('downloadedGames') || '{}');
            document.querySelectorAll('.download-button').forEach(button => {
                const gameId = button.closest('.game-card').dataset.gameId;
                if (downloadedGames[gameId]) {
                    button.classList.add('is-complete');
                    button.querySelector('.button-text').textContent = 'Play';
                }
            });
        }

        document.querySelectorAll('.download-button').forEach(button => {
            button.addEventListener('click', function(e) {
                const gameId = this.dataset.gameId;
                const gameTitle = this.dataset.gameTitle;
                const downloadUrl = `download_game.php?id=${gameId}&title=${encodeURIComponent(gameTitle)}`;

                if (this.classList.contains('is-complete')) {
                    window.location.href = downloadUrl;
                    return;
                }
                
                if (this.classList.contains('is-downloading')) {
                    e.preventDefault();
                    return;
                }

                this.classList.add('is-downloading');
                const progressBar = this.querySelector('.progress-bar');
                const buttonText = this.querySelector('.button-text');
                
                if (progressBar) progressBar.style.width = '100%';
                if (buttonText) buttonText.textContent = 'Downloading...';

                setTimeout(() => {
                    this.classList.remove('is-downloading');
                    this.classList.add('is-complete');
                    if (buttonText) buttonText.textContent = 'Play';

                    const downloadedGames = JSON.parse(localStorage.getItem('downloadedGames') || '{}');
                    downloadedGames[gameId] = true;
                    localStorage.setItem('downloadedGames', JSON.stringify(downloadedGames));

                    window.location.href = downloadUrl;
                }, 3000); 
            });
        });

        initializeButtonStates();
    });
    // My Games Page Ending
    </script>
    <?php include "footer.php" ?>
</body>
</html>