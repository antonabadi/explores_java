<?php

/**
 * Login Page
 *
 * This file handles user authentication for the POS application.
 * It expects a POST request to verify credentials against the database.
 */

// Include necessary configurations or bootstrap files if available
// require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/KaryawanController.php';

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
    $auth = new AdminController();
    return $auth->login();
    /*
        if ($auth->handleLogin($username, $password)) {
            header("Location: /../index.php?page=dashboard.");
            exit();
        } else {
            $error = "Invalid username or password.";
    }
    */
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
        <div class="row justify-content-center m-auto">
            <div class="col-md">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Login</h3>

                        <?php if ($error) : ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 justify-content-center">Sign In</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
