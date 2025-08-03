<?php
require_once 'functions.php';
session_start();
if (!isset($_SESSION['dashboard_token']) || !validate_token($_SESSION['dashboard_token'], null, true)) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) die("Prompt ID required.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        delete_prompt($id);
        header('Location: dashboard.php');
        exit;
    } else {
        header("Location: prompt.php?id=$id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= APP_NAME ?> - Delete Prompt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <img src="assets/logo_small.png" alt="Logo" class="logo d-inline-block align-middle">
        <span class="navbar-brand ms-2"><?= APP_NAME ?> - Delete Prompt</span>
    </div>
</nav>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="post">
                        <p class="mb-4">Are you sure you want to delete this prompt?</p>
                        <div class="d-flex gap-2">
                            <button name="confirm" value="1" class="btn btn-danger">Yes, Delete</button>
                            <button name="cancel" value="1" class="btn btn-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>