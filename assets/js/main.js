// assets/js/main.js
// P4: Frontend JavaScript Integration (covered by P1 as Team Lead)

document.addEventListener('DOMContentLoaded', () => {

    // 1. Handle LOGIN Form Submission
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
                    alert('Login Successful! Welcome ' + data.user.full_name);
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

    // 2. Handle REGISTER Form Submission
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