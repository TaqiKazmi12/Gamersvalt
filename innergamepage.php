<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: explore.php"); exit(); }
$game_id = (int)$_GET['id'];
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

$sql = "SELECT g.*, c.name as category_name, d_user.name as developer_name, d.bio as developer_bio
        FROM games g
        LEFT JOIN categories c ON g.category_id = c.id
        LEFT JOIN developers d ON g.developer_id = d.id
        LEFT JOIN users d_user ON d.user_id = d_user.id
        WHERE g.id = ? AND g.status = 'published'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$game = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$game) { header("Location: explore.php"); exit(); }

$stmt_images = $conn->prepare("SELECT image_url FROM game_images WHERE game_id = ?");
$stmt_images->bind_param("i", $game_id);
$stmt_images->execute();
$screenshots = $stmt_images->get_result()->fetch_all(MYSQLI_ASSOC);
array_unshift($screenshots, ['image_url' => $game['thumbnail']]);
$stmt_images->close();

$user_owns_game = false;
$user_has_reviewed = false;
$user_has_reported = false;
if ($is_logged_in) {
    $stmt_purchase = $conn->prepare("SELECT id FROM purchases WHERE user_id = ? AND game_id = ?");
    $stmt_purchase->bind_param("ii", $user_id, $game_id);
    $stmt_purchase->execute();
    if ($stmt_purchase->get_result()->num_rows > 0) { $user_owns_game = true; }
    $stmt_purchase->close();

    if ($user_owns_game) {
        $stmt_reviewed = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND game_id = ?");
        $stmt_reviewed->bind_param("ii", $user_id, $game_id);
        $stmt_reviewed->execute();
        if ($stmt_reviewed->get_result()->num_rows > 0) { $user_has_reviewed = true; }
        $stmt_reviewed->close();
    }

    $stmt_report = $conn->prepare("SELECT id FROM reports WHERE user_id = ? AND game_id = ?");
    $stmt_report->bind_param("ii", $user_id, $game_id);
    $stmt_report->execute();
    if ($stmt_report->get_result()->num_rows > 0) { $user_has_reported = true; }
    $stmt_report->close();
}

$stmt_reviews = $conn->prepare("SELECT r.rating, r.review_text, r.created_at, u.name as reviewer_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.game_id = ? ORDER BY r.created_at DESC");
$stmt_reviews->bind_param("i", $game_id);
$stmt_reviews->execute();
$reviews = $stmt_reviews->get_result()->fetch_all(MYSQLI_ASSOC);
$total_reviews = count($reviews);
$average_rating = 0;
if ($total_reviews > 0) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $average_rating = round($total_rating / $total_reviews, 1);
}
$stmt_reviews->close();
$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['title']); ?> - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="innergamepage.css">
    <style>
     
        .action-button.tertiary-action { background-color: transparent; color: var(--text-secondary); border: 2px solid #444; }
        .action-button.tertiary-action:hover:not(:disabled) { color: var(--error-color); border-color: var(--error-color); background-color: transparent; }
        .action-button.tertiary-action.success { color: var(--success-color); border-color: var(--success-color); cursor: default; }
        .report-options { display: flex; flex-direction: column; gap: 15px; margin: 25px 0; text-align: left; }
        .report-options label { display: flex; align-items: center; padding: 15px; background-color: #2a2a2a; border-radius: 8px; border: 2px solid transparent; cursor: pointer; transition: all 0.2s ease; }
        .report-options label:hover { background-color: #333; }
        .report-options input[type="radio"] { display: none; }
        .report-options input[type="radio"]:checked + span { color: var(--primary-neon); }
        .report-options label:has(input:checked) { border-color: var(--primary-neon); }
        .report-options span { font-weight: 500; font-size: 1.1rem; }
        #report-form textarea { width: 100%; box-sizing: border-box; padding: 15px; background: #2a2a2a; border: 1px solid #444; border-radius: 8px; color: var(--text-primary); font-size: 1rem; resize: vertical; min-height: 120px; margin-bottom: 20px; }
 
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Inner Game Page Starting -->
    <header class="game-header" style="background-image: url('<?php echo htmlspecialchars($game['thumbnail']); ?>');"><div class="header-overlay"></div></header>
    
    <div class="main-content-wrapper">
        <div class="game-purchase-bar scroll-animated-element">
            <h1 class="purchase-bar-title"><?php echo htmlspecialchars($game['title']); ?></h1>
            <div class="purchase-actions">
                <span class="purchase-price"><?php echo $game['price'] > 0 ? '$' . number_format($game['price'], 2) : 'FREE'; ?></span>
                <?php if ($user_owns_game): ?>
                    <button class="action-button primary-action in-library" disabled>In Your Library</button>
                <?php elseif ($game['price'] == 0): ?>
                    <a href="process_add_to_library.php?id=<?php echo $game_id; ?>" class="action-button primary-action <?php if (!$is_logged_in) echo 'login-required'; ?>">Add to Library</a>
                <?php else: ?>
                    <a href="checkout.php?id=<?php echo $game_id; ?>" class="action-button primary-action <?php if (!$is_logged_in) echo 'login-required'; ?>">Buy Now</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="game-layout-container">
            <div class="game-main-column">
                <section class="gallery-section scroll-animated-element">
                    <div class="main-image-container"><img id="main-gallery-image" src="<?php echo htmlspecialchars($screenshots[0]['image_url']); ?>" alt="Main game view"></div>
                    <div class="thumbnail-strip">
                        <?php foreach($screenshots as $index => $screenshot): ?>
                            <img src="<?php echo htmlspecialchars($screenshot['image_url']); ?>" alt="Thumbnail <?php echo $index + 1; ?>" class="thumbnail-image <?php if ($index === 0) echo 'active'; ?>">
                        <?php endforeach; ?>
                    </div>
                </section>
                <section class="description-section scroll-animated-element">
                    <h2>About This Game</h2>
                    <p><?php echo nl2br(htmlspecialchars($game['description'])); ?></p>
                </section>
            </div>
            <aside class="game-sidebar-column">
                <section class="actions-panel scroll-animated-element">
                    <?php if (!$user_owns_game && $game['price'] > 0): ?>
                        <button data-action="cart" data-game-id="<?php echo $game_id; ?>" class="action-button secondary-action <?php if (!$is_logged_in) echo 'login-required'; ?>">Add to Cart</button>
                    <?php endif; ?>
                    <button data-action="wishlist" data-game-id="<?php echo $game_id; ?>" class="action-button secondary-action <?php if (!$is_logged_in) echo 'login-required'; ?>">Add to Wishlist</button>
                    <button id="report-game-btn" class="action-button tertiary-action <?php if (!$is_logged_in) echo 'login-required'; ?>" <?php if ($user_has_reported) echo 'disabled'; ?>>
                        <?php echo $user_has_reported ? '✔ Reported' : 'Report this Game'; ?>
                    </button>
                </section>
                <section class="details-panel scroll-animated-element">
                    <h3>Game Details</h3>
                    <ul>
                        <li><strong>Developer:</strong> <span><?php echo htmlspecialchars($game['developer_name'] ?? 'N/A'); ?></span></li>
                        <li><strong>Category:</strong> <span><?php echo htmlspecialchars($game['category_name'] ?? 'N/A'); ?></span></li>
                        <li><strong>Release Date:</strong> <span><?php echo date("M j, Y", strtotime($game['created_at'])); ?></span></li>
                    </ul>
                </section>
            </aside>
        </div>
        
        <section class="reviews-section scroll-animated-element">
            <h2>User Reviews</h2>
            <div class="review-summary">
                <div class="average-rating-box">
                    <span class="average-rating-score"><?php echo $average_rating; ?></span>
                    <div class="stars-container"><div class="stars-outer"><div class="stars-inner" style="width: <?php echo ($average_rating / 5) * 100; ?>%;"></div></div></div>
                    <span class="total-reviews">(<?php echo $total_reviews; ?> reviews)</span>
                </div>
            </div>

            <?php if ($user_owns_game && !$user_has_reviewed): ?>
                <div class="review-form-container">
                    <h3>Write a review for <?php echo htmlspecialchars($game['title']); ?></h3>
                    <?php if(isset($_SESSION['review_error'])): ?><p class="form-message error"><?php echo $_SESSION['review_error']; unset($_SESSION['review_error']); ?></p><?php endif; ?>
                    <?php if(isset($_SESSION['review_success'])): ?><p class="form-message success"><?php echo $_SESSION['review_success']; unset($_SESSION['review_success']); ?></p><?php endif; ?>
                    <form action="process_submit_review.php" method="POST" id="review-form">
                        <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
                        <div class="rating-input">
                            <input type="radio" id="star5" name="rating" value="5" required><label for="star5" title="5 stars">★</label>
                            <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="4 stars">★</label>
                            <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="3 stars">★</label>
                            <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="2 stars">★</label>
                            <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="1 star">★</label>
                        </div>
                        <textarea name="review_text" rows="5" placeholder="Share your thoughts on the game..." required></textarea>
                        <button type="submit" class="action-button primary-action">Submit Review</button>
                    </form>
                </div>
            <?php elseif ($user_has_reviewed): ?>
                 <div class="form-message success">Thank you, you have already reviewed this game.</div>
            <?php endif; ?>

            <div class="review-list">
                <?php if(!empty($reviews)): foreach($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <span class="reviewer-name"><?php echo htmlspecialchars($review['reviewer_name']); ?></span>
                            <div class="review-stars"><div class="stars-outer"><div class="stars-inner" style="width: <?php echo ($review['rating'] / 5) * 100; ?>%;"></div></div></div>
                        </div>
                        <p class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                        <span class="review-date"><?php echo date("M j, Y", strtotime($review['created_at'])); ?></span>
                    </div>
                <?php endforeach; else: ?>
                    <p class="no-reviews">Be the first to review this game!</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div id="login-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-modal">×</button>
            <h3>Login Required</h3>
            <p>You need to be logged in to perform this action.</p>
            <div class="modal-actions">
                <a href="userlogin.php" class="modal-button primary">Login</a>
                <a href="usersignup.php" class="modal-button secondary">Sign Up</a>
            </div>
        </div>
    </div>

    <div id="report-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-modal">×</button>
            <form id="report-form">
                <div id="report-step-1">
                    <h3>Report "<?php echo htmlspecialchars($game['title']); ?>"</h3>
                    <p>Please select a reason for your report. Your feedback helps keep Gamer's Valt safe.</p>
                    <div class="report-options">
                        <label><input type="radio" name="report_type" value="Hate Speech or Harassment" required><span>Hate Speech or Harassment</span></label>
                        <label><input type="radio" name="report_type" value="Scam or Fraud"><span>Scam or Fraud</span></label>
                        <label><input type="radio" name="report_type" value="Malware or Harmful Content"><span>Malware or Harmful Content</span></label>
                        <label><input type="radio" name="report_type" value="Copyright Infringement"><span>Copyright Infringement</span></label>
                        <label><input type="radio" name="report_type" value="Other"><span>Other</span></label>
                    </div>
                    <button type="button" id="report-next-btn" class="modal-button primary">Next</button>
                </div>
                <div id="report-step-2" style="display: none;">
                    <h3>Provide Additional Details</h3>
                    <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
                    <textarea name="comments" rows="5" placeholder="Please provide any additional information or context that could help our moderation team (optional)."></textarea>
                    <div class="modal-actions">
                        <button type="button" id="report-back-btn" class="modal-button secondary">Back</button>
                        <button type="submit" class="modal-button primary">Submit Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Inner Game Page Ending -->

    <script>
        // Inner Game Page Starting
        document.addEventListener('DOMContentLoaded', function() {
          

            const reportModal = document.getElementById('report-modal');
            const reportGameBtn = document.getElementById('report-game-btn');
            
            if(reportGameBtn) {
                reportGameBtn.addEventListener('click', function() {
                    if (!this.classList.contains('login-required') && !this.disabled) {
                        reportModal.classList.add('visible');
                    }
                });
            }

            const reportForm = document.getElementById('report-form');
            const step1 = document.getElementById('report-step-1');
            const step2 = document.getElementById('report-step-2');

            document.getElementById('report-next-btn').addEventListener('click', () => {
                if (reportForm.report_type.value) {
                    step1.style.display = 'none';
                    step2.style.display = 'block';
                } else {
                    alert('Please select a reason for your report.');
                }
            });

            document.getElementById('report-back-btn').addEventListener('click', () => {
                step2.style.display = 'none';
                step1.style.display = 'block';
            });

            reportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';

                const formData = new FormData(this);
                fetch('process_report_game.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        reportGameBtn.textContent = '✔ Reported';
                        reportGameBtn.disabled = true;
                        reportGameBtn.classList.add('success');
                    } else {
                        alert(data.message || 'Could not submit report.');
                    }
                    reportModal.classList.remove('visible');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Report';
                });
            });
            
            reportModal?.querySelector('.close-modal').addEventListener('click', () => reportModal.classList.remove('visible'));
        });
        // Inner Game Page Ending
    </script>
     <script>
        // Inner Game Page Starting
        document.addEventListener('DOMContentLoaded', function() {
            const mainImage = document.getElementById('main-gallery-image');
            const thumbnails = document.querySelectorAll('.thumbnail-image');
            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() { mainImage.src = this.src; thumbnails.forEach(t => t.classList.remove('active')); this.classList.add('active'); });
            });

            const loginModal = document.getElementById('login-modal');
            document.querySelectorAll('.login-required').forEach(button => {
                button.addEventListener('click', function(e) { e.preventDefault(); if (loginModal) loginModal.classList.add('visible'); });
            });
            document.querySelector('.close-modal')?.addEventListener('click', () => loginModal.classList.remove('visible'));
            loginModal?.addEventListener('click', (e) => { if (e.target === loginModal) loginModal.classList.remove('visible'); });

            document.querySelectorAll('.actions-panel button[data-action]').forEach(button => {
                button.addEventListener('click', function(e) {
                    if (this.classList.contains('login-required')) { return; }
                    this.disabled = true;
                    const action = this.dataset.action;
                    const gameId = this.dataset.gameId;
                    this.innerHTML = '<span>Processing...</span>';

                    fetch(`process_add_to_${action}.php?id=${gameId}`)
                        .then(response => {
                            if (!response.ok) { throw new Error(`Network response was not ok: ${response.statusText}`); }
                            return response.json();
                        })
                        .then(data => {
                            if(data.success) {
                                this.innerHTML = `<span>✔ ${data.message}</span>`;
                                this.classList.add('success');
                            } else {
                                this.innerHTML = `<span>! ${data.message}</span>`;
                                this.disabled = true; 
                            }
                        })
                        .catch(error => {
                            console.error('There was a problem with the fetch operation:', error);
                            this.innerHTML = `<span>Error! Refresh Page.</span>`;
                            this.disabled = false;
                        });
                });
            });

            const animatedElements = document.querySelectorAll('.scroll-animated-element');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => { if (entry.isIntersecting) { entry.target.classList.add('in-view'); observer.unobserve(entry.target); }});
            }, { threshold: 0.1 });
            animatedElements.forEach(el => observer.observe(el));
        });
        // Inner Game Page Ending
    </script>
    <?php include "footer.php" ?>
</body>
</html>