<?php
session_start();
// PHP Backend Starting
require_once 'connection.php';
if (!isset($_SESSION['user_id'])) { header("Location: userlogin.php?redirect=chat.php"); exit(); }
$user_id = $_SESSION['user_id'];
$sql_friends = "SELECT u.id, u.name FROM friendships f JOIN users u ON u.id = IF(f.user_one_id = ?, f.user_two_id, f.user_one_id) WHERE (f.user_one_id = ? OR f.user_two_id = ?) AND f.status = 'accepted' ORDER BY u.name ASC";
$stmt_friends = $conn->prepare($sql_friends);
$stmt_friends->bind_param("iii", $user_id, $user_id, $user_id);
$stmt_friends->execute();
$conversations = $stmt_friends->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_friends->close();
$stmt_games = $conn->prepare("SELECT g.id, g.title, g.thumbnail FROM purchases p JOIN games g ON p.game_id = g.id WHERE p.user_id = ?");
$stmt_games->bind_param("i", $user_id);
$stmt_games->execute();
$library_games = $stmt_games->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_games->close();
$conn->close();
$open_chat_with_id = isset($_GET['user']) && is_numeric($_GET['user']) ? (int)$_GET['user'] : null;
// PHP Backend Ending
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Gamer's Valt</title>
    <link rel="stylesheet" href="navbar.css">
    <style>
    /* Chat Page Starting */
    :root { --primary-neon: #FF8C00; --background-station: #0d0d10; --background-panel: #1a1a1e; --background-panel-light: #2c2c31; --text-primary: #e8e8e8; --text-secondary: #a0a8b4; --border-color-faint: rgba(255, 140, 0, 0.15); --font-primary: 'Inter', system-ui, sans-serif; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes popIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    html, body { height: 100%; overflow: hidden; }
    body { background-color: var(--background-station); font-family: var(--font-primary); color: var(--text-primary); margin: 0; }
    .chat-layout-container { display: flex; height: 100vh; }
    .conversations-sidebar { display: flex; flex-direction: column; width: 320px; flex-shrink: 0; background-color: var(--background-panel); border-right: 1px solid var(--border-color-faint); animation: slideIn 0.5s ease-out; }
    .sidebar-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid var(--border-color-faint); height: var(--navbar-height); box-sizing: border-box; }
    .sidebar-header h2 { margin: 0; font-size: 1.5rem; }
    .new-chat-btn { background: none; border: 2px solid var(--text-secondary); color: var(--text-secondary); width: 30px; height: 30px; border-radius: 50%; font-size: 20px; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; }
    .conversation-list { overflow-y: auto; flex-grow: 1; }
    .conversation-item { padding: 15px 20px; cursor: pointer; border-left: 4px solid transparent; transition: background-color 0.2s, border-color 0.2s; }
    .conversation-item:hover { background-color: var(--background-panel-light); }
    .conversation-item.active { background-color: var(--background-panel-light); border-left-color: var(--primary-neon); }
    .user-name { font-weight: 600; }
    .empty-message { padding: 20px; color: var(--text-secondary); } .empty-message a { color: var(--primary-neon); text-decoration: none; }
    .chat-main-window { display: flex; flex-direction: column; flex-grow: 1; background: var(--background-station); }
    .chat-area { flex-grow: 1; display: none; flex-direction: column; }
    .chat-area.active { display: flex; }
    .welcome-content { margin: auto; text-align: center; color: var(--text-secondary); animation: popIn 0.5s; }
    .welcome-content h3 { font-size: 2rem; color: var(--text-primary); }
    .chat-header { display: flex; align-items: center; padding: 0 20px; background-color: var(--background-panel); border-bottom: 1px solid var(--border-color-faint); box-shadow: 0 2px 10px rgba(0,0,0,0.2); z-index: 2; height: var(--navbar-height); flex-shrink: 0; }
    .chat-header h3 { margin: 0; font-size: 1.5rem; }
    .message-list { flex-grow: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 5px; }
    .msg { display: flex; flex-direction: column; max-width: 70%; margin-top: 10px; animation: popIn 0.3s ease-out; }
    .msg.sent { align-self: flex-end; } .msg.received { align-self: flex-start; }
    .msg-content { padding: 8px 12px; border-radius: 18px; word-wrap: break-word; }
    .msg.sent .msg-content { background-color: var(--primary-neon); color: var(--text-dark); border-bottom-right-radius: 4px; }
    .msg.received .msg-content { background-color: var(--background-panel-light); border-bottom-left-radius: 4px; }
    .msg-text { line-height: 1.5; }
    .msg-time { font-size: 0.7rem; color: var(--text-secondary); margin-top: 4px; }
    .msg.sent .msg-time { align-self: flex-end; }
    .chat-image { max-width: 100%; max-height: 250px; border-radius: 15px; cursor: pointer; display: block; object-fit: cover; }
    .shared-game-link { display: flex; align-items: center; gap: 10px; background-color: rgba(0,0,0,0.2); padding: 8px; border-radius: 10px; text-decoration: none; color: inherit; }
    .shared-game-link img { width: 60px; height: 45px; object-fit: cover; border-radius: 4px; }
    .shared-game-link span { font-weight: 600; }
    .chat-footer { padding: 15px 20px; background-color: var(--background-panel); border-top: 1px solid var(--border-color-faint); z-index: 2; position: relative; }
    #message-form { display: flex; align-items: flex-end; gap: 10px; }
    .chat-input-wrapper { display: flex; flex-direction: column; flex-grow: 1; background-color: var(--background-panel-light); border: 1px solid var(--border-color-faint); border-radius: 24px; }
    #message-input { width: 100%; padding: 12px 20px; background: transparent; border: none; color: var(--text-primary); font-size: 1rem; resize: none; max-height: 120px; outline: none; }
    .form-actions { display: flex; gap: 10px; }
    #share-game-btn, #send-message-btn, #emoji-btn, #image-btn { background: var(--primary-neon); color: var(--text-dark); border: none; width: 48px; height: 48px; border-radius: 50%; font-size: 1.5rem; cursor: pointer; flex-shrink: 0; display: flex; align-items: center; justify-content: center; transition: background-color 0.2s; }
    .attachment-preview-area { padding: 8px 8px 0 20px; }
    .image-preview, .game-share-preview { display: inline-flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.2); padding: 5px; border-radius: 6px; font-size: 0.9rem; }
    .image-preview img { height: 40px; border-radius: 4px; }
    .cancel-preview { background: #555; border: none; color: white; border-radius: 50%; width: 18px; height: 18px; cursor: pointer; }
    .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); display: none; justify-content: center; align-items: center; z-index: 2000; opacity: 0; transition: opacity 0.3s ease; }
    .modal-overlay.visible { display: flex; opacity: 1; }
    .modal-content { background: var(--background-panel); padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; border: 1px solid var(--border-color-strong); position: relative; transform: scale(0.95); transition: transform 0.3s ease; }
    .modal-overlay.visible .modal-content { transform: scale(1); }
    .close-modal { position: absolute; top: 15px; right: 15px; background: none; border: none; color: var(--text-secondary); font-size: 2rem; cursor: pointer; }
    .image-viewer { background: rgba(0,0,0,0.9); backdrop-filter: blur(10px); }
    #fullscreen-image { max-width: 90vw; max-height: 90vh; object-fit: contain; }
    .game-share-list { display: flex; flex-direction: column; gap: 10px; max-height: 50vh; overflow-y: auto; }
    .game-share-item { display: flex; align-items: center; gap: 15px; padding: 10px; border-radius: 6px; } .game-share-item:hover { background-color: var(--background-panel-light); }
    .game-share-item img { width: 96px; height: 54px; object-fit: cover; border-radius: 4px; }
    .game-share-item span { flex-grow: 1; font-weight: 500; }
    .share-this-game-btn { background: var(--primary-neon); color: var(--text-dark); border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; }
    @media(max-width: 900px) { .conversations-sidebar { width: 80px; } .sidebar-header h2, .conversation-item .user-name { display: none; } .sidebar-header { justify-content: center; } .conversation-item { border-left: none; border-bottom: 4px solid transparent; justify-content: center; } .conversation-item::before { content: '👤'; font-size: 24px; } }
    @media(max-width: 600px) { body { overflow: auto; } .chat-layout-container { flex-direction: column; height: calc(100vh - 70px); } .conversations-sidebar { width: 100%; height: auto; border-right: none; border-bottom: 1px solid var(--border-color-faint); flex-direction: row; align-items: center; } .conversation-list { display: flex; overflow-x: auto; flex-grow: 1; } .sidebar-header { border-bottom: none; } }
    /* Chat Page Ending */
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <!-- Chat Page Starting -->
    <div class="chat-layout-container">
        <aside class="conversations-sidebar">
            <header class="sidebar-header">
                <h2>Friends</h2>
                <a href="friends.php" class="new-chat-btn" title="Manage Friends">⚙️</a>
            </header>
            <div class="conversation-list">
                <?php if(!empty($conversations)): foreach($conversations as $convo): ?>
                <div class="conversation-item" data-friend-id="<?php echo $convo['id']; ?>" data-friend-name="<?php echo htmlspecialchars($convo['name']); ?>">
                    <span class="user-name"><?php echo htmlspecialchars($convo['name']); ?></span>
                </div>
                <?php endforeach; else: ?>
                <p class="empty-message">No friends yet. <a href="friends.php">Add some</a> to start chatting.</p>
                <?php endif; ?>
            </div>
        </aside>

        <main class="chat-main-window">
            <div id="chat-welcome-screen" class="chat-area active">
                <div class="welcome-content"><h3>Welcome to Gamer's Valt Chat</h3><p>Select a friend from the left to start a conversation.</p></div>
            </div>
            <div id="chat-area-dynamic" class="chat-area">
                <header class="chat-header"><h3 id="chat-with-name"></h3></header>
                <div class="message-list" id="message-list"></div>
                <footer class="chat-footer">
                    <form id="message-form" enctype="multipart/form-data">
                        <input type="hidden" id="receiver-id-input" name="receiver_id">
                        <input type="file" id="image-file-input" name="image_file" accept="image/*" style="display: none;">
                        <div class="chat-input-wrapper">
                            <div id="attachment-preview-area"></div>
                            <textarea id="message-input" name="message_text" placeholder="Type a message..." rows="1"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="button" id="image-btn" title="Send Image">🖼️</button>
                            <button type="button" id="share-game-btn" title="Share a Game">🎮</button>
                            <button type="submit" id="send-message-btn" title="Send Message">➤</button>
                        </div>
                    </form>
                </footer>
            </div>
        </main>
    </div>

    <div id="share-game-modal" class="modal-overlay"><div class="modal-content"><button class="close-modal">×</button><h3>Share a Game</h3><div class="game-share-list"></div></div></div>
    <div id="image-viewer-modal" class="modal-overlay image-viewer"><button class="close-modal full-screen-close">×</button><img src="" alt="Full screen image view" id="fullscreen-image"></div>
    <!-- Chat Page Ending -->

    <script>
    // Chat Page Starting
    document.addEventListener('DOMContentLoaded', function() {
        const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
        const openChatWithId = <?php echo json_encode($open_chat_with_id); ?>;
        const convoList = document.querySelector('.conversation-list'), welcomeScreen = document.getElementById('chat-welcome-screen'), chatArea = document.getElementById('chat-area-dynamic');
        const messageList = document.getElementById('message-list'), messageForm = document.getElementById('message-form'), messageInput = document.getElementById('message-input');
        const receiverIdInput = document.getElementById('receiver-id-input'), chatWithName = document.getElementById('chat-with-name');
        const shareGameModal = document.getElementById('share-game-modal'), imageViewerModal = document.getElementById('image-viewer-modal');
        const imageFileInput = document.getElementById('image-file-input'), attachmentPreviewArea = document.getElementById('attachment-preview-area');
        let activeFriendId = null, messagePollingInterval, lastMessageId = 0;

        function appendMessage(msg) {
            const msgDiv = document.createElement('div');
            msgDiv.className = `msg ${msg.sender_id == currentUserId ? 'sent' : 'received'}`;
            let content = '<div class="msg-content">';
            if (msg.image_url) content += `<img src="${msg.image_url}" class="chat-image" alt="User image">`;
            if (msg.shared_game_id) content += `<a href="innergamepage.php?id=${msg.shared_game_id}" class="shared-game-link"><img src="${msg.shared_game_thumbnail}" alt=""><span>${msg.shared_game_title}</span></a>`;
            if (msg.message_text) content += `<div class="msg-text">${msg.message_text.replace(/\n/g, '<br>')}</div>`;
            content += '</div>';
            content += `<span class="msg-time">${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>`;
            msgDiv.innerHTML = content;
            messageList.appendChild(msgDiv);
        }

        function fetchNewMessages() {
            if (!activeFriendId) return;
            fetch(`ajax_get_chat_messages.php?friend_id=${activeFriendId}&last_id=${lastMessageId}`).then(res => res.json()).then(messages => {
                if (messages.length > 0) {
                    const isScrolledToBottom = messageList.scrollHeight - messageList.clientHeight <= messageList.scrollTop + 50;
                    messages.forEach(msg => { appendMessage(msg); lastMessageId = msg.id; });
                    if (isScrolledToBottom) messageList.scrollTop = messageList.scrollHeight;
                }
            });
        }
        
        function openChat(friendId, friendName) {
            document.querySelectorAll('.conversation-item').forEach(item => item.classList.remove('active'));
            document.querySelector(`.conversation-item[data-friend-id="${friendId}"]`)?.classList.add('active');
            activeFriendId = friendId; lastMessageId = 0;
            welcomeScreen.classList.remove('active'); chatArea.classList.add('active');
            chatWithName.textContent = friendName; receiverIdInput.value = friendId;
            messageList.innerHTML = '<p class="loading-messages">Loading...</p>';
            clearInterval(messagePollingInterval);
            fetch(`ajax_get_chat_messages.php?friend_id=${friendId}&last_id=0`).then(res => res.json()).then(messages => {
                messageList.innerHTML = '';
                if (messages.length === 0) { messageList.innerHTML = '<p class="empty-message">This is the beginning of your conversation. Say hi!</p>'; } 
                else { messages.forEach(msg => { appendMessage(msg); lastMessageId = msg.id; }); }
                messageList.scrollTop = messageList.scrollHeight;
            });
            messagePollingInterval = setInterval(fetchNewMessages, 3000);
        }

        convoList.addEventListener('click', e => { const item = e.target.closest('.conversation-item'); if (item) openChat(item.dataset.friendId, item.dataset.friendName); });

        messageForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(messageForm);
            if (!formData.get('message_text').trim() && (!formData.get('image_file') || formData.get('image_file').size === 0) && !formData.has('shared_game_id')) return;
            fetch('process_send_message.php', { method: 'POST', body: formData }).then(res => res.json()).then(data => { if (data.success) fetchNewMessages(); });
            messageForm.reset(); attachmentPreviewArea.innerHTML = ''; attachmentPreviewArea.style.display = 'none'; messageInput.style.height = 'auto';
        });

        document.getElementById('image-btn').addEventListener('click', () => imageFileInput.click());
        imageFileInput.addEventListener('change', function() { if(this.files && this.files[0]) { const reader = new FileReader(); reader.onload = e => { attachmentPreviewArea.innerHTML = `<div class="image-preview"><img src="${e.target.result}" alt="Preview"><button type="button" class="cancel-preview">×</button></div>`; attachmentPreviewArea.style.display = 'block'; }; reader.readAsDataURL(this.files[0]); } });

        document.getElementById('share-game-btn').addEventListener('click', () => {
            fetch('ajax_get_library.php').then(res=>res.json()).then(games => {
                const list = shareGameModal.querySelector('.game-share-list');
                list.innerHTML = games.length ? games.map(g => `<div class="game-share-item" data-game-id="${g.id}" data-game-title="${g.title}" data-game-thumbnail="${g.thumbnail}"><img src="${g.thumbnail}" alt=""><span>${g.title}</span><button type="button" class="share-this-game-btn">Share</button></div>`).join('') : '<p class="empty-message">Your library is empty.</p>';
                shareGameModal.classList.add('visible');
                list.querySelectorAll('.share-this-game-btn').forEach(btn => btn.onclick = () => {
                    const item = btn.closest('.game-share-item');
                    const hiddenInput = document.createElement('input'); hiddenInput.type = 'hidden'; hiddenInput.name = 'shared_game_id'; hiddenInput.value = item.dataset.gameId;
                    messageForm.appendChild(hiddenInput);
                    attachmentPreviewArea.innerHTML = `<div class="game-share-preview"><span>Sharing: ${item.dataset.gameTitle}</span><button type="button" class="cancel-preview">×</button></div>`;
                    attachmentPreviewArea.style.display = 'block';
                    shareGameModal.classList.remove('visible');
                });
            });
        });

        attachmentPreviewArea.addEventListener('click', e => { if (e.target.id === 'cancel-preview') { imageFileInput.value = ''; messageForm.querySelectorAll('input[name="shared_game_id"]').forEach(el => el.remove()); attachmentPreviewArea.innerHTML = ''; attachmentPreviewArea.style.display = 'none'; }});
        
        messageList.addEventListener('click', e => { if (e.target.classList.contains('chat-image')) { document.getElementById('fullscreen-image').src = e.target.src; imageViewerModal.classList.add('visible'); }});
        imageViewerModal.addEventListener('click', () => imageViewerModal.classList.remove('visible'));
        shareGameModal.querySelector('.close-modal').addEventListener('click', () => shareGameModal.classList.remove('visible'));
        messageInput.addEventListener('input', () => { messageInput.style.height = 'auto'; messageInput.style.height = `${messageInput.scrollHeight}px`; });

        if (openChatWithId) { const friendItem = document.querySelector(`.conversation-item[data-friend-id="${openChatWithId}"]`); if (friendItem) friendItem.click(); }
    });
    // Chat Page Ending
    </script>
    
</body>
</html>