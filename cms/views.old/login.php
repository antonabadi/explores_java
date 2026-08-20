<?php
// views/login.php
// Simple login page for AdminController::login (POST /admins/login)

session_start();

// If already logged in, redirect to dashboard
if (!empty($_SESSION['admin_id'])) {
    header('Location: /dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Explores Java</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: #1a3c34;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .login-box {
        background: #fff;
        border-radius: 10px;
        padding: 40px;
        width: 100%;
        max-width: 380px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .login-box h1 {
        font-size: 22px;
        margin-bottom: 6px;
        color: #1a3c34;
    }
    .login-box p.subtitle {
        color: #777;
        margin-bottom: 24px;
        font-size: 14px;
    }
    .field {
        margin-bottom: 16px;
    }
    .field label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        color: #333;
    }
    .field input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }
    .field input:focus {
        outline: none;
        border-color: #1a3c34;
    }
    button {
        width: 100%;
        padding: 11px;
        background: #1a3c34;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 8px;
    }
    button:hover {
        background: #14302a;
    }
    button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .error-msg {
        background: #fdecea;
        color: #b3261e;
        padding: 10px 12px;
        border-radius: 6px;
        font-size: 13px;
        margin-bottom: 16px;
        display: none;
    }
</style>
</head>
<body>

<div class="login-box">
    <h1>Admin Login</h1>
    <p class="subtitle">Explores Java — Dashboard Access</p>

    <div class="error-msg" id="errorMsg"></div>

    <form id="loginForm">
        <div class="field">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" required autofocus>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" id="submitBtn">Login</button>
    </form>
</div>

<script>
const form = document.getElementById('loginForm');
const errorMsg = document.getElementById('errorMsg');
const submitBtn = document.getElementById('submitBtn');

form.addEventListener('submit', async function (e) {
    e.preventDefault();

    errorMsg.style.display = 'none';
    submitBtn.disabled = true;
    submitBtn.textContent = 'Logging in...';

    const payload = {
        username: document.getElementById('username').value.trim(),
        password: document.getElementById('password').value,
    };

    try {
        const res = await fetch('/admins/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        const result = await res.json();

        if (result.success) {
            window.location.href = '/dashboard.php';
        } else {
            errorMsg.textContent = result.message || 'Login failed. Please try again.';
            errorMsg.style.display = 'block';
        }
    } catch (err) {
        errorMsg.textContent = 'Something went wrong. Please try again.';
        errorMsg.style.display = 'block';
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Login';
    }
});
</script>

</body>
</html>
