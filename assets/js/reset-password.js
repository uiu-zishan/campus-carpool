// assets/js/reset-password.js

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token');

    if (!token) {
        document.getElementById('resetForm').innerHTML = '<p class="error-message">Invalid reset link</p>';
        return;
    }

    document.getElementById('resetForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const password = document.getElementById('password').value;
        const msgEl = document.getElementById('message');

        try {
            const response = await fetch('api/reset_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token, password })
            });
            const data = await response.json();

            if (data.success) {
                alert('Password reset! Please log in.');
                window.location.href = 'login.html';
            } else {
                msgEl.style.color = '#cc0000';
                msgEl.innerText = data.message;
            }
        } catch (err) {
            msgEl.style.color = '#cc0000';
            msgEl.innerText = 'Server error';
        }
    });
});