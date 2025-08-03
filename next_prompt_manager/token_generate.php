<?php
require_once 'functions.php';
session_start();

$allow = false;
if (isset($_SESSION['dashboard_token']) && validate_token($_SESSION['dashboard_token'], null, true)) {
    $allow = true;
} elseif (isset($_GET['token']) && validate_token($_GET['token'], null, true)) {
    $allow = true;
}

if (!$allow) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = generate_token();
    store_token($token, null, true, null);
    $generated = $token;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= APP_NAME ?> - Generate Token</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    function copyToken() {
        var token = document.getElementById('token');
        token.select();
        document.execCommand('copy');
        alert('Token copied!');
    }
    </script>
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
                    <h2 class="card-title mb-4">Generate Dashboard Access Token</h2>
                    <form method="post">
                        <button type="submit" class="btn btn-primary w-100 mb-3">Generate Token</button>
                    </form>
                    <?php if (!empty($generated)): ?>
                        <div class="input-group mb-3">
                            <input id="token" class="form-control" value="<?= htmlspecialchars($generated) ?>" readonly>
                            <button type="button" class="btn btn-outline-secondary" onclick="copyToken()">Copy</button>
                        </div>
                    <?php endif; ?>
                    <a href="login.php" class="btn btn-link w-100">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>