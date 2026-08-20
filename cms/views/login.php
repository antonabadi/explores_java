<?php
if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php?page=dashboard');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare(
            'SELECT id, username, password FROM admins WHERE username = ? OR email = ? LIMIT 1'
        );
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect('dashboard.php?page=dashboard');
        }

        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Explores Java CMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body class="login-page">
<div class="login-wrapper">
    <div class="login-card glass-card">
        <div class="login-brand">
            <div class="brand-logo">EJ</div>
            <div>
                <h1>Explores Java</h1>
                <p class="text-muted">Admin Dashboard</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert-box alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus
                       value="<?= e($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-submit btn-block">Sign In</button>
        </form>
    </div>
</div>
</body>
</html>
