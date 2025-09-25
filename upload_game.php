<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'developer') {
    header("Location: devlogin.php");
    exit();
}

$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);
$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Game - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <link rel="stylesheet" href="upload_game.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Upload Game Page Starting -->
    <div class="upload-container">
        <header class="upload-header">
            <h1>Submit Your Game</h1>
            <p>Follow the steps below to get your creation ready for the Valt.</p>
        </header>

        <div class="upload-wizard">
            <div class="wizard-progress">
                <div class="progress-bar-fill"></div>
                <div class="step active" data-step="1"><span>1</span> Details</div>
                <div class="step" data-step="2"><span>2</span> Pricing</div>
                <div class="step" data-step="3"><span>3</span> Media</div>
                <div class="step" data-step="4"><span>4</span> Review</div>
            </div>
            
            <form action="process_upload_game.php" method="POST" id="upload-form" enctype="multipart/form-data">
                <!-- Step 1: Game Details -->
                <div class="wizard-step active" data-step="1">
                    <h2>Game Details</h2>
                    <div class="form-group">
                        <label for="title">Game Title</label>
                        <input type="text" id="title" name="title" required placeholder="e.g., Cyber Ronin">
                    </div>
                    <div class="form-group">
                        <label for="description">Full Description</label>
                        <textarea id="description" name="description" rows="8" required placeholder="Tell the world about your game..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select a category...</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="wizard-nav"><button type="button" class="nav-btn next">Next: Pricing</button></div>
                </div>

                <!-- Step 2: Pricing & Status -->
                <div class="wizard-step" data-step="2">
                    <h2>Pricing & Status</h2>
                    <div class="form-group">
                        <label for="price">Price (USD)</label>
                        <input type="number" id="price" name="price" required min="0.00" step="0.01" value="0.00">
                        <small>Enter 0.00 for a Free to Play game.</small>
                    </div>
                     <div class="form-group">
                        <label>Initial Status</label>
                        <div class="radio-group">
                            <label><input type="radio" name="status" value="published" checked><span>Published</span></label>
                            <label><input type="radio" name="status" value="draft"><span>Draft</span></label>
                        </div>
                        <small>'Published' will be visible in the store immediately. 'Draft' will save it for later.</small>
                    </div>
                    <div class="wizard-nav"><button type="button" class="nav-btn prev">Back</button><button type="button" class="nav-btn next">Next: Media</button></div>
                </div>

                <!-- Step 3: Media -->
                <div class="wizard-step" data-step="3">
                    <h2>Media & Game Files</h2>
                    <div class="form-group">
                        <label>Store Thumbnail (Required, 4:3 Ratio)</label>
                        <div class="file-drop-area" id="thumbnail-drop-area"><p>Drag & Drop a single image here, or click to select.</p></div>
                        <input type="file" id="thumbnail" name="thumbnail" accept="image/*" required style="display:none;">
                    </div>
                    <div class="form-group">
                        <label>Screenshots (Optional, up to 5)</label>
                        <div class="file-drop-area" id="screenshots-drop-area"><p>Drag & Drop up to 5 images here, or click to select multiple.</p></div>
                        <input type="file" id="screenshots" name="screenshots[]" accept="image/*" multiple style="display:none;">
                    </div>
                    <div class="form-group">
                        <label>Game File (Required, .zip recommended)</label>
                        <div class="file-drop-area" id="game-file-drop-area"><p>Drag & Drop your game's ZIP file here, or click to select.</p></div>
                        <input type="file" id="game_file" name="game_file" required style="display:none;">
                        <div id="game-file-name" class="file-name-display"></div>
                    </div>
                    <div class="wizard-nav"><button type="button" class="nav-btn prev">Back</button><button type="button" class="nav-btn next">Review & Submit</button></div>
                </div>

                <!-- Step 4: Review -->
                <div class="wizard-step" data-step="4">
                    <h2>Review & Submit</h2>
                    <p class="review-text">Please review all the information below before submitting your game to the Valt.</p>
                    <div class="live-preview-container">
                        <div class="game-card-preview">
                            <div class="card-image-preview"><img></div>
                            <div class="card-content-preview">
                                <div class="card-header-preview"><h4 class="card-title-preview">Your Game Title</h4><span class="card-price-preview">FREE</span></div>
                                <p class="card-developer-preview">by <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div id="review-details"></div>
                    <div class="wizard-nav"><button type="button" class="nav-btn prev">Back</button><button type="submit" class="nav-btn submit-btn">Submit to the Valt</button></div>
                </div>
            </form>
        </div>
    </div>
    <!-- Upload Game Page Ending -->

    <script src="upload_game.js"></script>
    <?php include "footer.php" ?>
</body>

</html>