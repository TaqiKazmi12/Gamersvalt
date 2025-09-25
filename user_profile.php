<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';
if (!isset($_SESSION['user_id'])) { header("Location: userlogin.php?redirect=user_profile.php"); exit(); }
$user_id = $_SESSION['user_id']; $user_role = $_SESSION['user_role'];
if ($user_role !== 'user') { $dashboard_page = ($user_role === 'developer') ? 'dev_dashboard.php' : 'admin_dashboard.php'; header("Location: $dashboard_page"); exit(); }
$stmt_user = $conn->prepare("SELECT name, email, created_at FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id); $stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc(); $stmt_user->close();
$stmt_cards = $conn->prepare("SELECT id, card_type, card_number_last4, expiry_month, expiry_year, is_default FROM user_payment_methods WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$stmt_cards->bind_param("i", $user_id); $stmt_cards->execute();
$saved_cards = $stmt_cards->get_result()->fetch_all(MYSQLI_ASSOC); $stmt_cards->close();
$stmt_games = $conn->prepare("SELECT g.id, g.title, g.thumbnail FROM purchases p JOIN games g ON p.game_id = g.id WHERE p.user_id = ? ORDER BY p.purchase_date DESC LIMIT 4");
$stmt_games->bind_param("i", $user_id); $stmt_games->execute();
$recent_games = $stmt_games->get_result()->fetch_all(MYSQLI_ASSOC); $stmt_games->close();
$conn->close();
$card_image_urls = [ 'visa' => 'https://cdn4.iconfinder.com/data/icons/flat-brand-logo-2/512/visa-512.png', 'mastercard' => 'https://e7.pngegg.com/pngimages/910/492/png-clipart-mastercard-logo-credit-card-visa-brand-mastercard-text-label-thumbnail.png', 'amex' => 'https://example.com/amex.png' ];
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <style>
    :root { --primary-neon: #FF8C00; --background-station: #0d0d10; --background-panel: #1a1a1e; --text-primary: #e8e8e8; --text-secondary: #a0a8b4; --text-dark: #020205; --border-color-faint: rgba(255, 140, 0, 0.15); --border-color-strong: rgba(255, 140, 0, 0.4); --success-color: #4CAF50; --error-color: #ff4d4d; --font-primary: 'Inter', system-ui, sans-serif; }
    @keyframes slideUpIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    body { background-color: var(--background-station); font-family: var(--font-primary); color: var(--text-primary); margin: 0; }
    .profile-container { max-width: 1200px; margin: 0 auto; padding: clamp(20px, 5vw, 40px); }
    .profile-header { padding: 100px 0 40px; text-align: center; border-bottom: 1px solid var(--border-color-faint); margin-bottom: 40px; animation: slideUpIn 0.6s backwards; }
    .profile-header h1 { font-size: clamp(2.5rem, 6vw, 3.5rem); font-weight: 800; margin: 0; }
    .profile-header p { font-size: clamp(1rem, 2.5vw, 1.1rem); color: var(--text-secondary); margin: 10px 0 0; }
    .profile-content { display: flex; flex-direction: column; gap: 30px; background: var(--background-panel); border-radius: 12px; overflow: hidden; border: 1px solid var(--border-color-faint); animation: slideUpIn 0.6s 0.2s backwards; }
    .profile-tabs { display: flex; background-color: #111; padding: 0 20px; border-bottom: 1px solid var(--border-color-faint); }
    .tab-link { background: none; border: none; color: var(--text-secondary); padding: 15px 25px; cursor: pointer; font-size: 1.1rem; font-weight: 600; position: relative; border-bottom: 3px solid transparent; transition: color 0.3s ease; }
    .tab-link:hover { color: var(--text-primary); }
    .tab-link.active { color: var(--primary-neon); border-bottom-color: var(--primary-neon); }
    .tab-content-area { padding: 30px; } .tab-pane { display: none; } .tab-pane.active { display: block; animation: slideUpIn 0.5s; }
    .profile-section { padding: 30px; border: 1px solid var(--border-color-faint); border-radius: 8px; }
    .profile-section:not(:last-child) { margin-bottom: 30px; }
    .profile-section h3 { font-size: 1.8rem; margin: 0 0 25px 0; color: var(--primary-neon); }
    .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .section-header h3 { margin: 0; }
    .action-button-sm { background: #333; color: var(--text-primary); padding: 8px 15px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; transition: background-color 0.2s ease; }
    .action-button-sm:hover { background-color: #444; }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
    .info-item label { display: block; color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 5px; }
    .info-item span { font-size: 1.1rem; font-weight: 500; }
    .hidden { display: none; }
    #edit-profile-form, #change-password-form { max-width: 500px; }
    .form-actions { display: flex; gap: 15px; margin-top: 20px; }
    .action-button { padding: 12px 25px; background: var(--primary-neon); color: var(--text-dark); border: none; border-radius: 6px; font-size: 1rem; font-weight: 700; cursor: pointer; transition: all 0.2s ease; }
    .action-button.secondary { background: #333; color: var(--text-primary); }
    .action-button:hover { background-color: var(--secondary-neon); }
    .action-button.secondary:hover { background-color: #444; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
    .form-group input { width: 100%; padding: 12px; background: #2a2a2a; border: 1px solid #444; border-radius: 6px; color: var(--text-primary); font-size: 1rem; box-sizing: border-box; }
    .form-message { margin-top: 15px; padding: 12px; border-radius: 6px; font-weight: 500; display: none; }
    .form-message.visible { display: block; }
    .form-message.success { background-color: rgba(76, 175, 80, 0.2); color: var(--success-color); }
    .form-message.error { background-color: rgba(255, 77, 77, 0.2); color: var(--error-color); }
    #payment-methods-list { display: flex; flex-direction: column; gap: 15px; }
    .payment-card { display: flex; align-items: center; gap: 20px; background: #2a2a2a; padding: 15px; border-radius: 8px; }
    .payment-card img { height: 24px; }
    .card-number { font-weight: 500; flex-grow: 1; }
    .card-expiry { color: var(--text-secondary); }
    .remove-card-btn { background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer; }
    .remove-card-btn:hover { color: var(--error-color); }
    .no-items-message { color: var(--text-secondary); }
    .no-items-message a { color: var(--primary-neon); text-decoration: none; }
    .no-items-message a:hover { text-decoration: underline; }
    .library-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; }
    .game-preview-card { display: block; text-decoration: none; color: var(--text-primary); transition: transform 0.2s ease; }
    .game-preview-card:hover { transform: translateY(-5px); }
    .game-preview-card img { width: 100%; aspect-ratio: 4/3; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
    .game-preview-card span { font-weight: 500; }
    .full-library-btn { display: inline-block; margin-top: 30px; }
    .cta-section { text-align: center; background: linear-gradient(45deg, rgba(var(--primary-neon-rgb), 0.1), rgba(var(--primary-neon-rgb), 0.05)); margin-top: 40px; }
    .cta-section h3 { color: var(--text-primary); }
    .cta-section p { max-width: 600px; margin: 0 auto 25px; color: var(--text-secondary); }
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); display: none; justify-content: center; align-items: center; z-index: 2000; opacity: 0; transition: opacity 0.3s ease; }
    .modal-overlay.visible { display: flex; opacity: 1; }
    .modal-content { background: var(--background-panel); padding: 30px; border-radius: 12px; width: 90%; max-width: 600px; border: 1px solid var(--border-color-strong); position: relative; transform: scale(0.95); transition: transform 0.3s ease; }
    .modal-overlay.visible .modal-content { transform: scale(1); }
    .close-modal { position: absolute; top: 15px; right: 15px; background: none; border: none; color: var(--text-secondary); font-size: 2rem; cursor: pointer; }
    .modal-content h3 { font-size: 1.8rem; margin: 0 0 20px 0; color: var(--primary-neon); }
    .modal-warning { background-color: rgba(255, 140, 0, 0.1); border-left: 4px solid var(--primary-neon); padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    .terms-box { background-color: #111; border: 1px solid #333; border-radius: 6px; padding: 15px; max-height: 150px; overflow-y: auto; margin-bottom: 20px; font-size: 0.9rem; color: var(--text-secondary); }
    .terms-box h4 { margin: 0 0 10px; color: var(--text-primary); }
    .checkbox-group { display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px; }
    .checkbox-group label { display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .checkbox-group input { accent-color: var(--primary-neon); }
    .action-button.upgrade-btn:disabled { background-color: #555; cursor: not-allowed; opacity: 0.6; }
    @media(max-width: 768px) { .profile-tabs { flex-wrap: wrap; } .tab-link { flex-grow: 1; } .profile-section { padding: 20px; } }

    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- User Profile Page Starting -->
    <div class="profile-container">
        <header class="profile-header"><h1><?php echo htmlspecialchars($user['name']); ?>'s Profile</h1><p>Member since <?php echo date("F Y", strtotime($user['created_at'])); ?></p></header>
        <div class="profile-content">
            <nav class="profile-tabs"><button class="tab-link active" data-tab="profile-details">Profile</button><button class="tab-link" data-tab="payment-methods">Payment</button><button class="tab-link" data-tab="my-library">Library</button></nav>
            <div class="tab-content-area">
                <div id="profile-details" class="tab-pane active">
                    <div class="profile-section"><div class="section-header"><h3>Account Information</h3><button id="edit-profile-btn" class="action-button-sm">Edit</button></div><div id="profile-display-mode"><div class="info-grid"><div class="info-item"><label>Username</label><span id="display-name"><?php echo htmlspecialchars($user['name']); ?></span></div><div class="info-item"><label>Email</label><span id="display-email"><?php echo htmlspecialchars($user['email']); ?></span></div></div></div><form id="edit-profile-form" class="hidden"><div id="profile-form-message" class="form-message"></div><div class="form-group"><label for="name">Username</label><input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required></div><div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></div><div class="form-actions"><button type="button" id="cancel-edit-btn" class="action-button secondary">Cancel</button><button type="submit" class="action-button">Save</button></div></form></div>
                    <div class="profile-section"><h3>Change Password</h3><form id="change-password-form"><div id="password-form-message" class="form-message"></div><div class="form-group"><label for="current_password">Current Password</label><input type="password" id="current_password" name="current_password" required></div><div class="form-group"><label for="new_password">New Password</label><input type="password" id="new_password" name="new_password" required></div><div class="form-group"><label for="confirm_password">Confirm New Password</label><input type="password" id="confirm_password" name="confirm_password" required></div><button type="submit" class="action-button">Update Password</button></form></div>
                </div>
                <div id="payment-methods" class="tab-pane">
                     <div class="profile-section"><h3>Saved Payment Methods</h3><div id="payment-methods-list"><?php if(!empty($saved_cards)): foreach($saved_cards as $card): ?><div class="payment-card" data-card-id="<?php echo $card['id']; ?>"><img src="<?php echo $card_image_urls[$card['card_type']]; ?>" alt="<?php echo $card['card_type']; ?>"><span class="card-number">**** **** **** <?php echo $card['card_number_last4']; ?></span><span class="card-expiry">Expires <?php echo $card['expiry_month'] . '/' . substr($card['expiry_year'], -2); ?></span><button class="remove-card-btn" data-card-id="<?php echo $card['id']; ?>">×</button></div><?php endforeach; else: ?><p class="no-items-message">You have no saved payment methods.</p><?php endif; ?></div></div>
                </div>
                <div id="my-library" class="tab-pane">
                     <div class="profile-section"><h3>Recently Added</h3><div class="library-preview-grid"><?php if(!empty($recent_games)): foreach($recent_games as $game): ?><a href="innergamepage.php?id=<?php echo $game['id']; ?>" class="game-preview-card"><img src="<?php echo htmlspecialchars($game['thumbnail']); ?>" alt=""><span><?php echo htmlspecialchars($game['title']); ?></span></a><?php endforeach; else: ?><p class="no-items-message">Your library is empty. <a href="explore.php">Start exploring</a>!</p><?php endif; ?></div><a href="my_games.php" class="action-button full-library-btn">View Full Library</a></div>
                </div>
            </div>
        </div>
        <div class="profile-section cta-section">
            <h3>Ready to Share Your Creations?</h3>
            <p>Join the ranks of developers on Gamer's Valt. Upload your games, reach a global audience, and turn your passion into a career.</p>
            <button id="open-dev-upgrade-modal-btn" class="action-button">Become a Developer</button>
        </div>
    </div>

    <div id="dev-upgrade-modal" class="modal-overlay">
        <div class="modal-content">
            <button class="close-modal">×</button>
            <h3>Upgrade to a Developer Account</h3>
            <div class="modal-warning"><strong>Warning:</strong> This action is permanent. Once you become a developer, you will no longer be able to purchase games. Your account will be dedicated to uploading and managing your own titles.</div>
            <div class="terms-box"><h4>Developer Guidelines & Terms</h4><p>1. Content Policy: You agree not to upload any games containing explicit nudity, hate speech, or illegal content. All submissions are subject to review.</p><p>2. Ownership: You must be the rightful owner or have explicit permission to distribute any content you upload.</p><p>3. Revenue Share: Gamer's Valt operates on a competitive revenue share model.</p></div>
            <form id="dev-upgrade-form">
                <div id="upgrade-form-message" class="form-message"></div>
                <div class="checkbox-group">
                    <label><input type="checkbox" name="agree_terms" required> I have read and agree to the Developer Guidelines & Terms.</label>
                    <label><input type="checkbox" name="agree_content" required> I understand I am responsible for the content I upload and will adhere to the content policy.</label>
                </div>
                <button type="submit" class="action-button upgrade-btn" disabled>Confirm Account Upgrade</button>
            </form>
        </div>
    </div>
    <!-- User Profile Page Ending -->

    <script>
    // User Profile Page Starting
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab-link'), panes = document.querySelectorAll('.tab-pane');
        tabs.forEach(tab => tab.addEventListener('click', function() { tabs.forEach(t => t.classList.remove('active')); panes.forEach(p => p.classList.remove('active')); this.classList.add('active'); document.getElementById(this.dataset.tab).classList.add('active'); }));
        
        const editProfileBtn = document.getElementById('edit-profile-btn'), cancelEditBtn = document.getElementById('cancel-edit-btn');
        const displayMode = document.getElementById('profile-display-mode'), editForm = document.getElementById('edit-profile-form');
        editProfileBtn?.addEventListener('click', () => { displayMode.classList.add('hidden'); editForm.classList.remove('hidden'); });
        cancelEditBtn?.addEventListener('click', () => { displayMode.classList.remove('hidden'); editForm.classList.add('hidden'); });
        
        editForm?.addEventListener('submit', function(e) {  });
        document.getElementById('change-password-form')?.addEventListener('submit', function(e) {  });
        document.getElementById('payment-methods-list')?.addEventListener('click', function(e) {  });

        const upgradeModal = document.getElementById('dev-upgrade-modal');
        const openModalBtn = document.getElementById('open-dev-upgrade-modal-btn');
        const closeModalBtn = upgradeModal.querySelector('.close-modal');
        const upgradeForm = document.getElementById('dev-upgrade-form');
        const upgradeCheckboxes = upgradeForm.querySelectorAll('input[type="checkbox"]');
        const upgradeSubmitBtn = upgradeForm.querySelector('.upgrade-btn');
        const upgradeMessageEl = document.getElementById('upgrade-form-message');

        openModalBtn.addEventListener('click', () => upgradeModal.classList.add('visible'));
        closeModalBtn.addEventListener('click', () => upgradeModal.classList.remove('visible'));
        upgradeModal.addEventListener('click', e => { if (e.target === upgradeModal) upgradeModal.classList.remove('visible'); });

        const checkUpgradeFormValidity = () => { upgradeSubmitBtn.disabled = !Array.from(upgradeCheckboxes).every(c => c.checked); };
        upgradeCheckboxes.forEach(c => c.addEventListener('change', checkUpgradeFormValidity));

        upgradeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            upgradeSubmitBtn.disabled = true;
            upgradeSubmitBtn.textContent = 'Processing...';
            upgradeMessageEl.textContent = '';
            upgradeMessageEl.className = 'form-message';

            fetch('process_upgrade_to_developer.php', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                upgradeMessageEl.textContent = data.message;
                upgradeMessageEl.classList.add('visible', data.success ? 'success' : 'error');
                if (data.success) {
                    setTimeout(() => { window.location.href = 'devlogin.php'; }, 2500);
                } else {
                    upgradeSubmitBtn.disabled = false;
                    upgradeSubmitBtn.textContent = 'Confirm Account Upgrade';
                }
            });
        });
    });
    // User Profile Page Ending
    </script>
    <?php include "footer.php" ?>
</body>
</html>