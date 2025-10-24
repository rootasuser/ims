document.getElementById('loginForm').addEventListener('submit', async e => {
    e.preventDefault();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const tkn_csrf = document.getElementById('tkn_csrf').value;
    const msg = document.getElementById('msg');

    try {
        const res = await fetch('api/auth.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'login', username, password, tkn_csrf }),
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = data.location.redirect;
        } else {
            msg.textContent = data.error || 'Login failed.';
        }
    } catch {
        // msg.textContent = 'Server error.';
    }
});