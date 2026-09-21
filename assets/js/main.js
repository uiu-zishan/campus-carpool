// assets/js/main.js — Shared across all pages

// ============ LOGOUT HANDLER ============
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
            try {
                await fetch('api/logout.php', { method: 'POST' });
            } catch (err) {
                console.error('Logout API error:', err);
            }
            window.location.href = 'login.html';
        }
    });
}

// ============ LOGIN FORM ============
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorMsg = document.getElementById('errorMsg');

            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = 'dashboard.html';
                } else {
                    errorMsg.innerText = data.message;
                }
            } catch (error) {
                errorMsg.innerText = "Server error. Please try again.";
                console.error(error);
            }
        });
    }

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const full_name = document.getElementById('full_name').value;
            const gender = document.getElementById('gender').value;
            const role = document.getElementById('role').value;
            const errorMsg = document.getElementById('errorMsg');

            try {
                const response = await fetch('api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password, full_name, gender, role })
                });
                const data = await response.json();
                if (data.success) {
                    alert('Registration Successful! Please log in.');
                    window.location.href = 'login.html';
                } else {
                    errorMsg.innerText = data.message;
                }
            } catch (error) {
                errorMsg.innerText = "Server error. Please try again.";
                console.error(error);
            }
        });
    }
});