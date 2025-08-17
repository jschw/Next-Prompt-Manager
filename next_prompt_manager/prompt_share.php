<?php
require_once 'functions.php';

$id = $_GET['id'] ?? null;
$token = $_GET['token'] ?? null;
if (!$id) die("Prompt ID required.");

$prompt = get_prompt($id);
if (!$prompt) die("Prompt not found.");

if (!$prompt['is_public']) {
    if (!$token || !validate_token($token, $id)) {
        die("Access denied.");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Prompt Share - <?= APP_NAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <img src="assets/logo_small.png" alt="Logo" class="logo d-inline-block align-middle">
        <span class="navbar-brand ms-2"><?= APP_NAME ?> - Shared Prompt</span>
    </div>
</nav>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2><?= htmlspecialchars($prompt['title']) ?></h2>
                    <pre class="bg-light p-2 rounded"><?= htmlspecialchars($prompt['prompt']) ?></pre>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Topic:</strong> <?= htmlspecialchars($prompt['topic']) ?></p>
                            <p><strong>Tags:</strong> <?= htmlspecialchars($prompt['tags']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Stage:</strong> <?= htmlspecialchars($prompt['stage']) ?></p>
                            <p><strong>LLM Params:</strong> <code><?= htmlspecialchars($prompt['llm_params']) ?></code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>