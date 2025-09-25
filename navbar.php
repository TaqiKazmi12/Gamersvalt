<!-- Navbar Starting -->
<?php
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : 'guest';
$username = $is_logged_in ? $_SESSION['username'] : 'Profile';
?>
<nav id="main-navbar">
    <div class="navbar-container">
        <a href="home.php" class="navbar-brand"><img src="logo.png" alt="Gamer's Valt Logo"><span>Gamer's Valt</span></a>
        <div class="desktop-nav-menu">
            <ul class="nav-links">
                <?php if ($user_role === 'user'): ?><li><a href="home.php">Home</a></li><li><a href="explore.php">Explore</a></li><li><a href="my_games.php">My Games</a></li><?php elseif ($user_role === 'developer'): ?><li><a href="dev_dashboard.php">Dashboard</a></li><li><a href="upload_game.php">Upload Game</a></li><?php elseif ($user_role === 'admin'): ?><li><a href="admin_dashboard.php">Admin Dashboard</a></li><?php else: ?><li><a href="home.php">Home</a></li><li><a href="explore.php">Explore</a></li><?php endif; ?>
            </ul>
        </div>
        <div class="nav-actions">
            <?php if ($is_logged_in): ?><a href="friends.php" class="action-icon" title="Friends & Community"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></a><?php endif; ?>
            <?php if ($is_logged_in && $user_role === 'user'): ?><a href="wishlist.php" class="action-icon" title="Wishlist"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg></a><a href="cart.php" class="action-icon" title="Cart"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg></a><?php endif; ?>
            <div class="profile-dropdown">
                <button class="profile-btn"><span><?php echo htmlspecialchars($username); ?></span><svg class="profile-arrow" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg></button>
                <ul class="dropdown-menu">
                    <?php if ($is_logged_in): ?><li><a href="<?php echo $user_role; ?>_profile.php">My Profile</a></li><hr><li><a href="logout.php">Logout</a></li><?php else: ?><li><a href="userlogin.php">Login</a></li><li><a href="usersignup.php">Sign Up</a></li><?php endif; ?>
                </ul>
            </div>
        </div>
        <button class="hamburger-menu"><span></span><span></span><span></span></button>
    </div>
</nav>
<div class="mobile-nav-menu">
    <ul class="mobile-nav-links">
        <?php if ($user_role === 'user'): ?><li><a href="home.php">Home</a></li><li><a href="explore.php">Explore</a></li><li><a href="my_games.php">My Games</a></li><?php elseif ($user_role === 'developer'): ?><li><a href="dev_dashboard.php">Dashboard</a></li><li><a href="upload_game.php">Upload Game</a></li><?php elseif ($user_role === 'admin'): ?><li><a href="admin_dashboard.php">Admin Dashboard</a></li><?php else: ?><li><a href="home.php">Home</a></li><li><a href="explore.php">Explore</a></li><?php endif; ?>
    </ul>
</div>
<?php if ($is_logged_in): ?>
<div id="social-hub">
    <div id="chat-windows-container"></div>
    <div class="social-bar">
        <button class="social-bar-btn" id="notifications-toggle" title="Notifications"><svg viewBox="0 0 24 24"><path d="M10 21H14C14 22.1 13.1 23 12 23S10 22.1 10 21M21 19V20H3V19L5 17V11C5 7.9 7 5.2 10 4.3V4C10 2.9 10.9 2 12 2S14 2.9 14 4V4.3C17 5.2 19 7.9 19 11V17L21 19Z"/></svg><span class="notification-count" id="notification-count"></span></button>
        <button class="social-bar-btn" id="friends-toggle" title="Friends"><svg viewBox="0 0 24 24"><path d="M12 5.5A3.5 3.5 0 0 1 15.5 9A3.5 3.5 0 0 1 12 12.5A3.5 3.5 0 0 1 8.5 9A3.5 3.5 0 0 1 12 5.5M5 8C5.56 8 6.08 7.56 6.31 7H9.17C8.75 6.14 8.04 5.5 7.19 5.25C6.4 5 5.5 5.14 4.8 5.6C3.5 6.5 3 8 4 9.4C4.2 9.7 4.5 10 5 10M19 8C19.56 8 20.08 7.56 20.31 7H23.17C22.75 6.14 22.04 5.5 21.19 5.25C20.4 5 19.5 5.14 18.8 5.6C17.5 6.5 17 8 18 9.4C18.2 9.7 18.5 10 19 10M12 14C14.67 14 20 15.34 20 18V20H4V18C4 15.34 9.33 14 12 14Z"/></svg><span>Friends</span></button>
    </div>
    <div class="social-popup" id="notifications-popup"><div class="popup-header"><h4>Notifications</h4></div><div class="popup-content" id="notifications-list-container"></div></div>
    <div class="social-popup" id="friends-popup"><div class="popup-header"><h4>Friends</h4><a href="friends.php">Manage</a></div><div class="popup-content" id="friends-list-container"></div></div>
</div>
<div id="share-game-modal" class="modal-overlay"><div class="modal-content"><button class="close-modal">×</button><h3>Share a Game</h3><div class="game-share-list"></div></div></div>
<div id="image-viewer-modal" class="modal-overlay image-viewer"><button class="close-modal full-screen-close">×</button><img src="" alt="Full screen image view" id="fullscreen-image"></div>
<?php endif; ?>

<style>
button:hover{
    background: none !important;
    color:white !important;
    border: 2px solid #FF8C00 !important;
}
:root { --primary-neon: #FF8C00; --primary-neon-rgb: 255, 140, 0; --background-dark: #0d0d0d; --background-panel: #1a1a1e; --background-panel-light: #2c2c31; --text-primary: #e8e8e8; --text-secondary: #a0a8b4; --border-color: rgba(var(--primary-neon-rgb), 0.2); --success-color: #4CAF50; --navbar-height: 70px; --transition-fast: 0.2s cubic-bezier(0.25, 1, 0.5, 1); --transition-medium: 0.4s cubic-bezier(0.25, 1, 0.5, 1); }
@keyframes slideDown { from { transform: translateY(-100%); } to { transform: translateY(0); } }
@keyframes popIn { from { opacity: 0; transform: translateY(10px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
#main-navbar { background: transparent; width: 100%; height: var(--navbar-height); position: absolute; top: 0; left: 0; z-index: 1000; transition: all var(--transition-medium); }
#main-navbar.sticky { position: fixed; background-color: rgba(13, 13, 13, 0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4); }
#main-navbar.sticky.hidden { top: -90px; }
.navbar-container { max-width: 1600px; margin: 0 auto; padding: 0 20px; height: 100%; display: flex; justify-content: space-between; align-items: center; }
.navbar-brand { display: flex; align-items: center; color: var(--text-primary); text-decoration: none; font-size: 1.5em; font-weight: bold; }
.navbar-brand img { height: 60px; margin-right: 10px; } .navbar-brand span { text-shadow: 0 0 10px var(--primary-neon); }
.desktop-nav-menu .nav-links { display: flex; list-style: none; margin: 0; padding: 0; gap: 30px; }
.nav-links a { color: var(--text-primary); text-decoration: none; font-size: 1.1em; font-weight: 500; position: relative; padding: 5px 0; transition: color 0.3s ease; }
.nav-links a::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 2px; background-color: var(--primary-neon); transform: scaleX(0); transform-origin: right; transition: transform 0.3s ease-out; }
.nav-links a:hover { color: var(--primary-neon); } .nav-links a:hover::after { transform: scaleX(1); transform-origin: left; }
.nav-actions { display: flex; align-items: center; gap: 20px; } .action-icon { color: var(--text-primary); transition: all 0.2s; } .action-icon:hover { color: var(--primary-neon); transform: scale(1.1); }
.action-icon svg { stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.profile-dropdown { position: relative; }
.profile-btn { background: none; border: 1px solid var(--border-color); border-radius: 20px; color: var(--text-primary); cursor: pointer; display: flex; align-items: center; padding: 5px 15px; transition: all 0.2s; }
.profile-btn:hover { background-color: var(--border-color); } .profile-btn span { margin-right: 8px; font-weight: 500; }
.profile-arrow { transition: transform 0.3s ease; width: 18px; height: 18px; stroke: currentColor; stroke-width: 2; fill: none; }
.profile-dropdown.open .profile-arrow { transform: rotate(180deg); }
.dropdown-menu { position: absolute; top: calc(100% + 10px); right: 0; background-color: var(--background-dark); border: 1px solid var(--border-color); border-radius: 8px; list-style: none; padding: 10px 0; margin: 0; width: 180px; box-shadow: 0 5px 25px rgba(0, 0, 0, 0.5); opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.3s ease; }
.profile-dropdown.open .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
.dropdown-menu li a { display: block; padding: 10px 20px; color: var(--text-primary); text-decoration: none; transition: all 0.2s; }
.dropdown-menu li a:hover { background-color: var(--primary-neon); color: var(--background-dark); }
.dropdown-menu hr { border: none; border-top: 1px solid var(--border-color); margin: 5px 0; }
.hamburger-menu { display: none; background: none; border: none; cursor: pointer; z-index: 1001; }
.hamburger-menu span { display: block; width: 25px; height: 3px; background-color: var(--text-primary); margin: 5px 0; transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55); }
.hamburger-menu.active span:nth-child(1) { transform: rotate(45deg) translate(5px, 6px); } .hamburger-menu.active span:nth-child(2) { opacity: 0; } .hamburger-menu.active span:nth-child(3) { transform: rotate(-45deg) translate(5px, -6px); }
.mobile-nav-menu { position: fixed; top: 0; left: -100%; width: 100%; height: 100%; background-color: var(--background-dark); display: flex; flex-direction: column; justify-content: center; align-items: center; transition: left 0.5s ease-in-out; z-index: 999; }
.mobile-nav-menu.active { left: 0; }
.mobile-nav-links { list-style: none; padding: 0; margin: 0; text-align: center; } .mobile-nav-links li { margin: 20px 0; }
.mobile-nav-links a { color: var(--text-primary); text-decoration: none; font-size: 2em; font-weight: bold; }
#social-hub { position: fixed; bottom: 0; right: 20px; z-index: 2000; display: flex; align-items: flex-end; gap: 15px; }
.social-bar { display: flex; gap: 10px; background-color: var(--background-panel); padding: 8px; border-radius: 10px 10px 0 0; border: 1px solid var(--border-color); border-bottom: none; box-shadow: 0 -5px 20px rgba(0,0,0,0.3); }
.social-bar-btn { background: none; border: none; color: var(--text-secondary); font-size: 1rem; font-weight: 600; padding: 8px 15px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px; position: relative; transition: all 0.2s ease; }
.social-bar-btn:hover { background-color: var(--background-panel-light); color: var(--text-primary); }
.social-bar-btn svg { width: 24px; height: 24px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.notification-count { background-color: var(--primary-neon); color: black; font-size: 0.8rem; font-weight: bold; border-radius: 50%; width: 20px; height: 20px; position: absolute; top: 0; right: 0; display: none; align-items: center; justify-content: center; transform: translate(50%, -50%); }
.social-popup { position: absolute; bottom: calc(100% + 10px); right: 0; width: 320px; background-color: var(--background-panel); border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 0 30px rgba(0,0,0,0.5); max-height: 400px; display: flex; flex-direction: column; opacity: 0; visibility: hidden; transform: translateY(10px); transition: all 0.3s ease; }
.social-popup.active { opacity: 1; visibility: visible; transform: translateY(0); }
.popup-header { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid var(--border-color); }
.popup-header h4 { margin: 0; font-size: 1.2rem; } .popup-header a { color: var(--primary-neon); text-decoration: none; font-weight: 500; }
.popup-content { overflow-y: auto; padding: 5px; } .popup-empty { padding: 20px; text-align: center; color: var(--text-secondary); }
.friend-item, .notification-item { padding: 12px 15px; display: flex; align-items: center; gap: 10px; border-radius: 6px; cursor: pointer; transition: background-color 0.2s; }
.friend-item:hover, .notification-item:hover { background-color: var(--background-panel-light); }
.status-dot { width: 10px; height: 10px; border-radius: 50%; background-color: #555; } .status-dot.online { background-color: var(--success-color); }
.notification-item.unread { font-weight: bold; background-color: rgba(var(--primary-neon-rgb), 0.1); }
#chat-windows-container { display: flex; align-items: flex-end; gap: 15px; }
.chat-window { width: 320px; height: 420px; background-color: var(--background-panel); border: 1px solid var(--border-color); border-radius: 8px 8px 0 0; box-shadow: 0 0 30px rgba(0,0,0,0.5); display: flex; flex-direction: column; transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1); animation: popIn 0.4s var(--transition-medium); }
.chat-window.minimized { height: 48px; }
.chat-window-header { background-color: #111; padding: 0 15px; height: 48px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; border-bottom: 1px solid var(--border-color); flex-shrink: 0; }
.chat-window-header .friend-name { font-weight: 600; }
.window-actions button { background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer; }
.chat-window-body { flex-grow: 1; padding: 10px; overflow-y: auto; display: flex; flex-direction: column; }
.msg { display: flex; flex-direction: column; max-width: 80%; margin-top: 10px; animation: popIn 0.3s var(--transition-fast); }
.msg.sent { align-self: flex-end; } .msg.received { align-self: flex-start; }
.msg-content { padding: 8px 12px; border-radius: 18px; word-wrap: break-word; }
.msg.sent .msg-content { background-color: var(--primary-neon); color: var(--text-dark); border-bottom-right-radius: 4px; }
.msg.received .msg-content { background-color: var(--background-panel-light); border-bottom-left-radius: 4px; }
.msg-text { line-height: 1.5; }
.msg-time { font-size: 0.7rem; color: var(--text-secondary); margin-top: 4px; }
.msg.sent .msg-time { align-self: flex-end; }
.chat-image { max-width: 100%; max-height: 200px; border-radius: 15px; cursor: pointer; display: block; object-fit: cover; }
.shared-game-link { display: flex; align-items: center; gap: 10px; background-color: rgba(0,0,0,0.2); padding: 8px; border-radius: 10px; text-decoration: none; color: inherit; }
.shared-game-link img { width: 60px; height: 45px; object-fit: cover; border-radius: 4px; }
.shared-game-link span { font-weight: 600; }
.chat-window-form { display: flex; flex-direction: column; padding: 10px; border-top: 1px solid var(--border-color); }
.chat-input-wrapper { display: flex; flex-direction: column; background: var(--background-panel-light); border-radius: 20px; border: 1px solid var(--border-color); }
.attachment-preview-area { padding: 8px 8px 0; }
.image-preview, .game-share-preview { display: inline-flex; align-items: center; gap: 5px; background: rgba(0,0,0,0.2); padding: 5px; border-radius: 6px; font-size: 0.9rem; }
.image-preview img { height: 40px; border-radius: 4px; }
.cancel-preview { background: #555; border: none; color: white; border-radius: 50%; width: 18px; height: 18px; cursor: pointer; }
.chat-input-wrapper textarea { width: 100%; padding: 10px 15px; background: transparent; border: none; color: var(--text-primary); font-size: 1rem; resize: none; max-height: 100px; outline: none; }
.form-actions { display: flex; justify-content: flex-start; padding: 0 5px 5px; gap: 5px; }
.action-btn { background: none; border: none; color: var(--text-secondary); font-size: 1.4rem; cursor: pointer; padding: 5px; transition: color 0.2s; }
.action-btn:hover { color: var(--primary-neon); }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); display: none; justify-content: center; align-items: center; z-index: 3000; opacity: 0; transition: opacity 0.3s ease; }
.modal-overlay.visible { display: flex; opacity: 1; }
.modal-content { background: var(--background-panel); padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; border: 1px solid var(--border-color); position: relative; transform: scale(0.95); transition: transform 0.3s ease; }
.modal-overlay.visible .modal-content { transform: scale(1); }
.close-modal { position: absolute; top: 15px; right: 15px; background: none; border: none; color: var(--text-secondary); font-size: 2rem; cursor: pointer; }
.image-viewer { background: rgba(0,0,0,0.9); backdrop-filter: blur(10px); }
#fullscreen-image { max-width: 90vw; max-height: 90vh; object-fit: contain; }
.game-share-list { display: flex; flex-direction: column; gap: 10px; max-height: 50vh; overflow-y: auto; }
.game-share-item { display: flex; align-items: center; gap: 15px; padding: 10px; border-radius: 6px; } .game-share-item:hover { background-color: var(--background-panel-light); }
.game-share-item img { width: 96px; height: 54px; object-fit: cover; border-radius: 4px; }
.game-share-item span { flex-grow: 1; font-weight: 500; }
.share-this-game-btn { background: var(--primary-neon); color: var(--text-dark); border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
@media (max-width: 992px) { .desktop-nav-menu { display: none; } .hamburger-menu { display: block; } }
@media (max-width: 768px) { #social-hub { right: 0; left: 0; justify-content: center; } .social-bar { border-radius: 0; width: 100%; justify-content: space-around; } .social-popup { right: 5%; width: 90%; } #chat-windows-container { position: fixed; bottom: 0; right: 0; left: 0; justify-content: center; padding: 0; } .chat-window { width: 100%; border-radius: 0; } }

</style>
<script>
// Navbar Starting
document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.getElementById('main-navbar'), hamburger = document.querySelector('.hamburger-menu'), mobileNavMenu = document.querySelector('.mobile-nav-menu'), profileDropdown = document.querySelector('.profile-dropdown');
    if(navbar) { let lastScrollTop = 0; window.addEventListener('scroll', () => { let scrollTop = window.pageYOffset || document.documentElement.scrollTop; navbar.classList.toggle('sticky', scrollTop > 100); navbar.classList.toggle('hidden', scrollTop > lastScrollTop && scrollTop > 100); lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; }); }
    hamburger?.addEventListener('click', () => { hamburger.classList.toggle('active'); mobileNavMenu.classList.toggle('active'); });
    profileDropdown?.querySelector('.profile-btn').addEventListener('click', e => { e.stopPropagation(); profileDropdown.classList.toggle('open'); });
    document.addEventListener('click', e => { if (profileDropdown && !profileDropdown.contains(e.target)) profileDropdown.classList.remove('open'); });

    const socialHub = document.getElementById('social-hub');
    if(socialHub) {
        const currentUserId = <?php echo json_encode($is_logged_in ? $_SESSION['user_id'] : null); ?>;
        const friendsToggle = document.getElementById('friends-toggle'), notificationsToggle = document.getElementById('notifications-toggle'), friendsPopup = document.getElementById('friends-popup'), notificationsPopup = document.getElementById('notifications-popup'), chatContainer = document.getElementById('chat-windows-container'), imageViewerModal = document.getElementById('image-viewer-modal'), shareGameModal = document.getElementById('share-game-modal');
        let activeChatWindows = {};

        const togglePopup = popup => { const isActive = popup.classList.contains('active'); document.querySelectorAll('.social-popup').forEach(p => p.classList.remove('active')); if (!isActive) popup.classList.add('active'); };
        friendsToggle.addEventListener('click', e => { e.stopPropagation(); togglePopup(friendsPopup); });
        notificationsToggle.addEventListener('click', e => { e.stopPropagation(); togglePopup(notificationsPopup); });
        document.addEventListener('click', e => { if (!socialHub.contains(e.target)) document.querySelectorAll('.social-popup').forEach(p => p.classList.remove('active')); });

        const createChatWindow = (friendId, friendName) => {
            if (activeChatWindows[friendId]) { activeChatWindows[friendId].window.classList.remove('minimized'); return; }
            const chatWindow = document.createElement('div');
            chatWindow.className = 'chat-window';
            chatWindow.dataset.friendId = friendId;
            chatWindow.innerHTML = `<div class="chat-window-header"><span class="friend-name">${friendName}</span><div class="window-actions"><button class="minimize-btn">_</button><button class="close-btn">×</button></div></div><div class="chat-window-body"></div><form class="chat-window-form" enctype="multipart/form-data"><input type="hidden" name="receiver_id" value="${friendId}"><input type="file" name="image_file" accept="image/*" style="display:none;"><div class="chat-input-wrapper"><div class="attachment-preview-area"></div><textarea name="message_text" placeholder="Type a message..." rows="1"></textarea></div><div class="form-actions"><button type="button" class="action-btn share-game-btn" title="Share Game">🎮</button><button type="button" class="action-btn send-image-btn" title="Send Image">🖼️</button><button type="submit" class="action-btn send-btn" title="Send">➤</button></div></form>`;
            chatContainer.appendChild(chatWindow);
            const body = chatWindow.querySelector('.chat-window-body'), form = chatWindow.querySelector('.chat-window-form'), input = form.querySelector('textarea'), imageInput = form.querySelector('input[type="file"]');
            
            let lastMessageId = 0;
            const fetchAndRenderMessages = (initialLoad = false) => {
                fetch(`ajax_get_chat_messages.php?friend_id=${friendId}&last_id=${lastMessageId}`).then(res => res.json()).then(messages => {
                    if (messages.length > 0) {
                        const isScrolledToBottom = body.scrollHeight - body.clientHeight <= body.scrollTop + 50;
                        if(initialLoad) body.innerHTML = '';
                        messages.forEach(msg => { appendMessage(msg, body); lastMessageId = msg.id; });
                        if (isScrolledToBottom || initialLoad) body.scrollTop = body.scrollHeight;
                    }
                });
            };
            
            const pollingInterval = setInterval(fetchAndRenderMessages, 3000);
            activeChatWindows[friendId] = { window: chatWindow, interval: pollingInterval };
            fetchAndRenderMessages(true);

            form.addEventListener('submit', e => { e.preventDefault(); sendMessage(form); });
            form.querySelector('.send-image-btn').addEventListener('click', () => imageInput.click());
            imageInput.addEventListener('change', () => handleAttachmentPreview(form, 'image'));
            form.querySelector('.share-game-btn').addEventListener('click', () => openShareModal(form));
        };
        
        const appendMessage = (msg, body) => {
            const msgDiv = document.createElement('div');
            msgDiv.className = `msg ${msg.sender_id == currentUserId ? 'sent' : 'received'}`;
            let content = '<div class="msg-content">';
            if (msg.image_url) content += `<img src="${msg.image_url}" class="chat-image" alt="User image">`;
            if (msg.shared_game_id) content += `<a href="innergamepage.php?id=${msg.shared_game_id}" class="shared-game-link"><img src="${msg.shared_game_thumbnail}" alt=""><span>${msg.shared_game_title}</span></a>`;
            if (msg.message_text) content += `<div class="msg-text">${msg.message_text.replace(/\n/g, '<br>')}</div>`;
            content += '</div>';
            content += `<span class="msg-time">${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>`;
            msgDiv.innerHTML = content;
            body.appendChild(msgDiv);
        };

        const sendMessage = (form) => {
            const formData = new FormData(form);
            const friendId = formData.get('receiver_id');
            if (!formData.get('message_text').trim() && (!formData.get('image_file') || formData.get('image_file').size === 0) && !formData.get('shared_game_id')) return;
            fetch('ajax_send_chat_message.php', { method: 'POST', body: formData }).then(res => res.json()).then(data => {
                if (data.success) {
                    const body = form.previousElementSibling;
                    const lastId = Array.from(body.querySelectorAll('.msg')).length > 0 ? body.lastElementChild.dataset.messageId : 0;
                    fetchAndRenderMessages(friendId, body, lastId);
                }
            });
            form.reset();
            form.querySelector('.attachment-preview-area').innerHTML = '';
            form.querySelector('textarea').style.height = 'auto';
        };

        const handleAttachmentPreview = (form, type, data = {}) => {
            const previewContainer = form.querySelector('.attachment-preview-area');
            form.querySelectorAll('input[name="shared_game_id"]').forEach(el => el.remove());
            if (type === 'image') {
                const fileInput = form.querySelector('input[type="file"]');
                if(fileInput.files && fileInput.files[0]) {
                    const reader = new FileReader();
                    reader.onload = e => { previewContainer.innerHTML = `<div class="image-preview"><img src="${e.target.result}" alt="Preview"><button type="button" class="cancel-preview">×</button></div>`; }
                    reader.readAsDataURL(fileInput.files[0]);
                }
            } else if (type === 'game') {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden'; hiddenInput.name = 'shared_game_id'; hiddenInput.value = data.id;
                form.appendChild(hiddenInput);
                previewContainer.innerHTML = `<div class="game-share-preview"><span>Sharing: ${data.title}</span><button type="button" class="cancel-preview">×</button></div>`;
            }
        };

        const openShareModal = (form) => {
            fetch('ajax_get_library.php').then(res=>res.json()).then(games => {
                const list = shareGameModal.querySelector('.game-share-list');
                list.innerHTML = games.length ? games.map(g => `<div class="game-share-item" data-game-id="${g.id}" data-game-title="${g.title}" data-game-thumbnail="${g.thumbnail}"><img src="${g.thumbnail}" alt=""><span>${g.title}</span><button type="button" class="share-this-game-btn">Share</button></div>`).join('') : '<p class="popup-empty">Your library is empty.</p>';
                shareGameModal.classList.add('visible');
                list.querySelectorAll('.share-this-game-btn').forEach(btn => btn.onclick = () => {
                    const item = btn.closest('.game-share-item');
                    handleAttachmentPreview(form, 'game', {id: item.dataset.gameId, title: item.dataset.gameTitle});
                    shareGameModal.classList.remove('visible');
                });
            });
        };

        document.body.addEventListener('click', e => {
            if (e.target.classList.contains('cancel-preview')) {
                const form = e.target.closest('.chat-window-form');
                form.querySelector('.attachment-preview-area').innerHTML = '';
                form.querySelector('input[type="file"]').value = '';
                form.querySelectorAll('input[name="shared_game_id"]').forEach(el => el.remove());
            }
        });

        chatContainer.addEventListener('click', e => {
            const chatWindow = e.target.closest('.chat-window');
            if(!chatWindow) return;
            if (e.target.classList.contains('close-btn')) { clearInterval(activeChatWindows[chatWindow.dataset.friendId].interval); delete activeChatWindows[chatWindow.dataset.friendId]; chatWindow.remove(); }
            if (e.target.classList.contains('minimize-btn') || (e.target.closest('.chat-window-header') && !e.target.closest('.window-actions'))) { chatWindow.classList.toggle('minimized'); }
            if (e.target.classList.contains('chat-image')) { document.getElementById('fullscreen-image').src = e.target.src; imageViewerModal.classList.add('visible'); }
        });

        friendsPopup.addEventListener('click', e => {
            const friendItem = e.target.closest('.friend-item');
            if (friendItem) { createChatWindow(friendItem.dataset.friendId, friendItem.dataset.friendName); togglePopup(friendsPopup); }
        });

        imageViewerModal.addEventListener('click', () => imageViewerModal.classList.remove('visible'));
        shareGameModal.querySelector('.close-modal').addEventListener('click', () => shareGameModal.classList.remove('visible'));

        const fetchSocialData = () => {
            fetch('ajax_get_social_data.php').then(res => res.json()).then(data => {
                const friendsList = document.getElementById('friends-list-container');
                const notifsList = document.getElementById('notifications-list-container');
                const notifCount = document.getElementById('notification-count');
                if(data.friends && data.friends.length) friendsList.innerHTML = data.friends.map(f => `<div class="friend-item" data-friend-id="${f.id}" data-friend-name="${f.name}"><span class="status-dot ${f.is_online ? 'online' : ''}"></span>${f.name}</div>`).join('');
                else friendsList.innerHTML = '<div class="popup-empty">No friends to show.</div>';
                if(data.notifications) {
                    const unreadCount = data.notifications.filter(n => !n.is_read).length;
                    if (unreadCount > 0) { notifCount.textContent = unreadCount; notifCount.style.display = 'flex'; } else { notifCount.style.display = 'none'; }
                    if(data.notifications.length) notifsList.innerHTML = data.notifications.map(n => `<div class="notification-item ${n.is_read ? '' : 'unread'}">${n.message}</div>`).join('');
                    else notifsList.innerHTML = '<div class="popup-empty">No new notifications.</div>';
                }
            }).catch(console.error);
        };
        fetchSocialData();
        setInterval(fetchSocialData, 10000);
    }
});
// Navbar Ending
</script>
