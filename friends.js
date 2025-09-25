// Friends Page Starting
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.social-tab-link');
    const panes = document.querySelectorAll('.social-tab-pane');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });

    const socialContent = document.querySelector('.social-content');
    socialContent.addEventListener('click', function(e) {
        const target = e.target;
        const userCard = target.closest('.user-card');
        if (!userCard) return;

        const friendId = target.dataset.friendId;
        if (!friendId) return;

        userCard.style.opacity = '0.5';
        let url = '';
        const formData = new FormData();
        formData.append('friend_id', friendId);

        if (target.classList.contains('accept-btn')) {
            url = 'process_accept_friend.php';
        } else if (target.classList.contains('remove-btn') || target.classList.contains('decline-btn')) {
            url = 'process_remove_friend.php';
        } else if (target.classList.contains('add-friend-btn')) {
            url = 'process_add_friend.php';
        } else {
            userCard.style.opacity = '1';
            return;
        }

        fetch(url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                userCard.classList.add('removing');
                userCard.addEventListener('transitionend', () => userCard.remove());
                if (url !== 'process_add_friend.php') {
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    target.textContent = 'Sent!';
                    target.disabled = true;
                }
            } else {
                userCard.style.opacity = '1';
                alert(data.message || 'An error occurred.');
            }
        });
    });

    const searchFriendForm = document.getElementById('search-friend-form');
    const searchResultsList = document.getElementById('search-results-list');
    searchFriendForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const searchTerm = this.querySelector('input[name="search_term"]').value;
        if (searchTerm.length < 2) {
            searchResultsList.innerHTML = '<p class="empty-message">Please enter at least 2 characters.</p>';
            return;
        }

        searchResultsList.innerHTML = '<p class="empty-message">Searching...</p>';
        
        fetch(`search_users.php?term=${encodeURIComponent(searchTerm)}`)
        .then(res => res.json())
        .then(users => {
            searchResultsList.innerHTML = '';
            if (users.length > 0) {
                users.forEach(user => {
                    const userCardHTML = `
                        <div class="user-card" data-user-id="${user.id}">
                            <div class="user-info"><span class="user-name">${user.name}</span></div>
                            <div class="user-actions">
                                <button class="action-button-sm add-friend-btn" data-friend-id="${user.id}">Add Friend</button>
                            </div>
                        </div>`;
                    searchResultsList.insertAdjacentHTML('beforeend', userCardHTML);
                });
            } else {
                searchResultsList.innerHTML = '<p class="empty-message">No users found matching that name.</p>';
            }
        });
    });
});
// Friends Page Ending