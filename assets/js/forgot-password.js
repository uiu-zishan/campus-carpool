// assets/js/forgot-password.js

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('forgotForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const msgEl = document.getElementById('message');

        try {
            const response = await fetch('api/forgot_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            });
            const data = await response.json();

            if (data.success) {
                msgEl.style.color = '#00875a';
                msgEl.innerText = data.message;
                if (data.reset_link) {
                    // DEMO ONLY — in production this comes via email
                    msgEl.innerHTML += `<br><br><a href="${data.reset_link}">Click here to reset your password</a>`;
                }
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