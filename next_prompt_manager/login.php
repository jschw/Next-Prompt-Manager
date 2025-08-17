<?php
require_once 'functions.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token']);
    if (validate_token($token, null, true)) {
        $_SESSION['dashboard_token'] = $token;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid token.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= APP_NAME ?> - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <img src="assets/logo_small.png" alt="Logo" class="logo d-inline-block align-middle">
        <span class="navbar-brand ms-2"><?= APP_NAME ?></span>
    </div>
</nav>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-4">Enter Access Token</h2>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <input type="text" name="token" class="form-control" placeholder="Access Token" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Enter</button>
                    </form>
                    <div class="mt-3 small text-center">
                        Need a token? <a href="token_generate.php">Generate one</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>