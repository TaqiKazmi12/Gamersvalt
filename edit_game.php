<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'developer' || !isset($_SESSION['developer_id'])) { header("Location: dev_auth.php"); exit(); }
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: dev_dashboard.php"); exit(); }

$developer_id = $_SESSION['developer_id'];
$game_id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'main_details') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    
    $stmt_get_paths = $conn->prepare("SELECT thumbnail, file_path FROM games WHERE id = ? AND developer_id = ?");
    $stmt_get_paths->bind_param("ii", $game_id, $developer_id);
    $stmt_get_paths->execute();
    $current_game = $stmt_get_paths->get_result()->fetch_assoc();
    $stmt_get_paths->close();

    $thumbnail_path = $current_game['thumbnail'];
    $game_file_path = $current_game['file_path'];
    $upload_dir = "uploads/";

    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $thumbnail_name = uniqid('thumb_', true) . '_' . basename($_FILES["thumbnail"]["name"]);
        $thumbnail_path = $upload_dir . $thumbnail_name;
        move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $thumbnail_path);
    }
    if (isset($_FILES['game_file']) && $_FILES['game_file']['error'] == 0) {
        $game_file_name = uniqid('game_', true) . '_' . basename($_FILES["game_file"]["name"]);
        $game_file_path = $upload_dir . $game_file_name;
        move_uploaded_file($_FILES["game_file"]["tmp_name"], $game_file_path);
    }
    
    $stmt_update = $conn->prepare("UPDATE games SET title = ?, description = ?, price = ?, category_id = ?, thumbnail = ?, file_path = ? WHERE id = ? AND developer_id = ?");
    $stmt_update->bind_param("ssdiisii", $title, $description, $price, $category_id, $thumbnail_path, $game_file_path, $game_id, $developer_id);
    if ($stmt_update->execute()) { $_SESSION['message'] = "Game '".htmlspecialchars($title)."' updated successfully!"; } 
    else { $_SESSION['message'] = "Error: Could not update game."; }
    $stmt_update->close();
    header("Location: dev_dashboard.php");
    exit();
}

$stmt_verify = $conn->prepare("SELECT * FROM games WHERE id = ? AND developer_id = ?");
$stmt_verify->bind_param("ii", $game_id, $developer_id);
$stmt_verify->execute();
$game = $stmt_verify->get_result()->fetch_assoc();
$stmt_verify->close();

if (!$game) { $_SESSION['message'] = "Error: You do not have permission to edit this game."; header("Location: dev_dashboard.php"); exit(); }

$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

$stmt_images = $conn->prepare("SELECT id, image_url FROM game_images WHERE game_id = ?");
$stmt_images->bind_param("i", $game_id);
$stmt_images->execute();
$screenshots = $stmt_images->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_images->close();

$conn->close();
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Game - <?php echo htmlspecialchars($game['title']); ?></title>
    <link rel="stylesheet" href="navbar.css">
    <style>
    
    :root { --primary-neon: #FF8C00; --primary-neon-rgb: 255, 140, 0; --background-station: #0d0d10; --background-panel: #1a1a1e; --text-primary: #e8e8e8; --text-secondary: #a0a8b4; --border-color-faint: rgba(var(--primary-neon-rgb), 0.15); --border-color-strong: rgba(var(--primary-neon-rgb), 0.4); --font-primary: 'Inter', system-ui, sans-serif; --success-color: #4CAF50; --error-color: #ff4d4d; }
    @keyframes slideUpIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    body { background-color: var(--background-station); font-family: var(--font-primary); color: var(--text-primary); margin: 0; }
    .dashboard-container { max-width: 1200px; margin: 0 auto; padding: clamp(20px, 5vw, 40px); }
    .dashboard-header { padding-top: 100px; padding-bottom: 40px; text-align: center; border-bottom: 1px solid var(--border-color-faint); margin-bottom: 40px; animation: slideUpIn 0.6s backwards; }
    .dashboard-header h1 { font-size: clamp(2.5rem, 6vw, 3.5rem); font-weight: 800; margin: 0; }
    .dashboard-header p { font-size: clamp(1rem, 2.5vw, 1.1rem); color: var(--text-secondary); margin: 10px 0 0; }
    .edit-layout { display: grid; grid-template-columns: 1fr 350px; gap: 40px; align-items: flex-start; }
    .main-column, .sidebar-column { display: flex; flex-direction: column; gap: 30px; }
    .form-section { background: var(--background-panel); padding: clamp(20px, 4vw, 30px); border-radius: 12px; border: 1px solid var(--border-color-faint); animation: slideUpIn 0.6s 0.2s backwards; }
    .form-section h3 { font-size: 1.8rem; margin: 0 0 25px 0; color: var(--primary-neon); border-bottom: 1px solid var(--border-color-faint); padding-bottom: 15px; }
    .form-group { margin-bottom: 25px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 10px; font-size: 1.1rem; }
    .form-group input, .form-group textarea, .form-group select { width: 100%; box-sizing: border-box; padding: 14px; background: #2a2a2a; border: 1px solid #444; border-radius: 6px; color: var(--text-primary); font-size: 1rem; transition: all 0.2s ease; }
    .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: var(--primary-neon); box-shadow: 0 0 10px rgba(var(--primary-neon-rgb), 0.5); }
    .thumbnail-preview { width: 100%; aspect-ratio: 16/9; background: #111; border-radius: 8px; overflow: hidden; border: 1px solid #444; margin-bottom: 15px; }
    .thumbnail-preview img { width: 100%; height: 100%; object-fit: cover; }
    .file-input-wrapper { position: relative; }
    .file-input-label { display: block; background-color: #333; color: var(--text-primary); padding: 12px 20px; border-radius: 6px; cursor: pointer; transition: background-color 0.2s ease; text-align: center; }
    .file-input-label:hover { background-color: #444; }
    .file-input-wrapper input[type="file"] { display: none; }
    .file-name { color: var(--text-secondary); font-size: 0.9rem; margin-top: 10px; text-align: center; word-break: break-all; }
    .status-display { text-align: center; }
    .status-display h4 { margin: 0 0 15px; font-size: 1.2rem; color: var(--text-secondary); }
    .status-pill { padding: 8px 15px; border-radius: 20px; font-weight: 700; display: inline-block; }
    .status-published { background-color: rgba(76, 175, 80, 0.2); color: #4CAF50; }
    .status-draft { background-color: rgba(255, 140, 0, 0.2); color: var(--primary-neon); }
    .publish-button { width: 100%; margin-top: 15px; background-color: var(--success-color); color: white; }
    .form-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 20px; }
    .action-button { background-color: var(--primary-neon); color: var(--text-dark); padding: 12px 25px; border-radius: 6px; text-decoration: none; font-weight: 700; transition: all 0.2s ease; border: none; font-size: 1rem; cursor: pointer; }
    .action-button:hover { background-color: var(--secondary-neon); transform: translateY(-2px); }
    .action-button.secondary { background: #333; color: var(--text-primary); } .action-button.secondary:hover { background: #444; }
    .screenshot-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; }
    .screenshot-item { position: relative; }
    .screenshot-item img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; }
    .delete-screenshot-btn { position: absolute; top: 5px; right: 5px; background: rgba(0,0,0,0.7); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; opacity: 0; transition: opacity 0.2s; }
    .screenshot-item:hover .delete-screenshot-btn { opacity: 1; }
    .danger-zone { border-color: var(--error-color); } .danger-zone h3 { color: var(--error-color); }
    .danger-zone p { color: var(--text-secondary); font-size: 0.9rem; }
    .delete-button { background-color: var(--error-color); color: white; }
    @media (max-width: 992px) { .edit-layout { grid-template-columns: 1fr; } .sidebar-column { order: -1; } }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <!-- Dev Edit Game Page Starting -->
    <div class="dashboard-container">
        <header class="dashboard-header"><h1>Edit Game</h1><p>Refine the details for "<?php echo htmlspecialchars($game['title']); ?>"</p></header>
        <div class="edit-layout">
            <main class="main-column">
                <form action="edit_game.php?id=<?php echo $game_id; ?>" method="POST" enctype="multipart/form-data" class="form-section">
                    <input type="hidden" name="form_type" value="main_details">
                    <h3>Core Information & Files</h3>
                    <div class="form-group"><label for="title">Game Title</label><input type="text" id="title" name="title" value="<?php echo htmlspecialchars($game['title']); ?>" required></div>
                    <div class="form-group"><label for="description">Description</label><textarea id="description" name="description" rows="8" required><?php echo htmlspecialchars($game['description']); ?></textarea></div>
                    <div class="form-group"><label for="price">Price (USD)</label><input type="number" id="price" name="price" value="<?php echo $game['price']; ?>" step="0.01" min="0" required></div>
                    <div class="form-group"><label for="category_id">Category</label><select id="category_id" name="category_id" required><?php foreach ($categories as $cat): ?><option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $game['category_id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label>Game Thumbnail</label><div class="thumbnail-preview"><img id="thumbnail-preview-img" src="<?php echo htmlspecialchars($game['thumbnail']); ?>" alt="Current thumbnail"></div><div class="file-input-wrapper"><label for="thumbnail" class="file-input-label">Change Image</label><input type="file" id="thumbnail" name="thumbnail" accept="image/*"><div id="thumbnail-file-name" class="file-name"></div></div></div>
                    <div class="form-group"><label>Game File (.zip)</label><div class="file-input-wrapper"><label for="game_file" class="file-input-label">Upload New Version</label><input type="file" id="game_file" name="game_file" accept=".zip"><div id="game-file-name" class="file-name">Current: <?php echo basename($game['file_path']); ?></div></div></div>
                    <div class="form-actions"><a href="dev_dashboard.php" class="action-button secondary">Cancel</a><button type="submit" class="action-button">Save Changes</button></div>
                </form>
            </main>
            <aside class="sidebar-column">
                <div class="form-section">
                    <h3>Game Status</h3>
                    <div class="status-display">
                        <h4>Current Status</h4>
                        <span class="status-pill status-<?php echo $game['status']; ?>"><?php echo ucfirst($game['status']); ?></span>
                        <?php if ($game['status'] === 'draft'): ?>
                            <form action="process_publish_game.php" method="POST" style="margin-top: 15px;"><input type="hidden" name="game_id" value="<?php echo $game_id; ?>"><button type="submit" class="action-button publish-button">Publish Game</button></form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-section">
                    <h3>Screenshots</h3>
                    <form id="screenshots-form" enctype="multipart/form-data">
                        <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">
                        <div class="form-group"><label for="screenshots">Add More Screenshots</label><div class="file-input-wrapper"><label for="screenshots" class="file-input-label">Choose Files</label><input type="file" id="screenshots" name="screenshots[]" accept="image/*" multiple><div id="screenshots-file-name" class="file-name"></div></div></div>
                        <button type="submit" class="action-button" style="width: 100%;">Upload Screenshots</button>
                    </form>
                    <hr style="border-color: var(--border-color-faint); margin: 30px 0;">
                    <div class="screenshot-gallery" id="screenshot-gallery-container">
                        <?php foreach($screenshots as $ss): ?>
                        <div class="screenshot-item" data-image-id="<?php echo $ss['id']; ?>"><img src="<?php echo htmlspecialchars($ss['image_url']); ?>"><button type="button" class="delete-screenshot-btn">×</button></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-section danger-zone">
                    <h3>Danger Zone</h3>
                    <p>Deleting a game is permanent and cannot be undone.</p>
                    <button class="action-button delete-button">Delete This Game</button>
                </div>
            </aside>
        </div>
    </div>
    <!-- Dev Edit Game Page Ending -->
    <script>
    // Dev Edit Game Script Starting
    document.addEventListener('DOMContentLoaded', function() {
        const createFileListText = (files) => { if (!files || files.length === 0) return ''; return files.length > 1 ? `${files.length} files selected` : files[0].name; };
        const thumbnailInput = document.getElementById('thumbnail'), thumbnailPreview = document.getElementById('thumbnail-preview-img'), thumbnailFileName = document.getElementById('thumbnail-file-name');
        const gameFileInput = document.getElementById('game_file'), gameFileName = document.getElementById('game-file-name');
        const screenshotsInput = document.getElementById('screenshots'), screenshotsFileName = document.getElementById('screenshots-file-name');
        const screenshotsForm = document.getElementById('screenshots-form'), galleryContainer = document.getElementById('screenshot-gallery-container');
        
        thumbnailInput.addEventListener('change', () => {
            thumbnailFileName.textContent = createFileListText(thumbnailInput.files);
            if (thumbnailInput.files && thumbnailInput.files[0]) {
                const reader = new FileReader();
                reader.onload = e => { thumbnailPreview.src = e.target.result; }
                reader.readAsDataURL(thumbnailInput.files[0]);
            }
        });
        gameFileInput.addEventListener('change', () => gameFileName.textContent = createFileListText(gameFileInput.files));
        screenshotsInput.addEventListener('change', () => screenshotsFileName.textContent = createFileListText(screenshotsInput.files));
        
        screenshotsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('process_upload_screenshots.php', { method: 'POST', body: formData })
            .then(res => res.json()).then(data => { if(data.success) { window.location.reload(); } else { alert(data.message || 'Upload failed.'); }});
        });

        galleryContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-screenshot-btn')) {
                if (!confirm('Are you sure you want to delete this screenshot?')) return;
                const item = e.target.closest('.screenshot-item'), imageId = item.dataset.imageId;
                const formData = new FormData(); formData.append('image_id', imageId);
                fetch('process_delete_screenshot.php', { method: 'POST', body: formData })
                .then(res => res.json()).then(data => { if(data.success) { item.remove(); } else { alert('Failed to delete screenshot.'); }});
            }
        });

        document.querySelector('.delete-button')?.addEventListener('click', () => {
            if(confirm('This action is IRREVERSIBLE. Are you sure you want to permanently delete this game and all associated data?')) {
                alert('Game deletion initiated (simulation).');
            }
        });
    });
    // Dev Edit Game Script Ending
    </script>
    <?php include "footer.php" ?>
</body>
</html>