// Dev Profile Page Starting
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-link'), panes = document.querySelectorAll('.tab-pane');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.dataset.tab).classList.add('active');
        });
    });

    const showMessage = (el, message, isSuccess) => {
        el.textContent = message;
        el.className = 'form-message';
        el.classList.add('visible', isSuccess ? 'success' : 'error');
        setTimeout(() => el.classList.remove('visible'), 4000);
    };

    const profileForm = document.getElementById('edit-profile-form');
    profileForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button');
        const msgEl = document.getElementById('profile-form-message');
        btn.disabled = true; btn.textContent = 'Saving...';
        fetch('process_update_dev_profile.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.json()).then(data => {
            showMessage(msgEl, data.message, data.success);
            if(data.success) {
                document.querySelector('.profile-header h1').textContent = `${data.newName}'s Profile`;
            }
        }).finally(() => { btn.disabled = false; btn.textContent = 'Save Profile Changes'; });
    });

    const passwordForm = document.getElementById('change-password-form');
    passwordForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button');
        const msgEl = document.getElementById('password-form-message');
        btn.disabled = true; btn.textContent = 'Updating...';
        fetch('process_change_password.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.json()).then(data => {
            showMessage(msgEl, data.message, data.success);
            if(data.success) this.reset();
        }).finally(() => { btn.disabled = false; btn.textContent = 'Update Password'; });
    });

    const withdrawForm = document.getElementById('withdraw-form');
    withdrawForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button');
        const msgEl = document.getElementById('withdraw-form-message');
        btn.disabled = true; btn.textContent = 'Processing...';
        fetch('process_withdraw_funds.php', { method: 'POST', body: new FormData(this) })
        .then(res => res.json()).then(data => {
            showMessage(msgEl, data.message, data.success);
            if(data.success) this.reset();
        }).finally(() => { btn.disabled = true; btn.textContent = 'Withdrawal Initiated'; });
    });
});
// Dev Profile Page Ending