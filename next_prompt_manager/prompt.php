<?php
require_once 'functions.php';
include 'phpqrcode/qrlib.php';

session_start();

$id = $_GET['id'] ?? null;
if (!$id) die("Prompt ID required.");
$prompt = get_prompt($id);
if (!$prompt) die("Prompt not found.");

$can_edit = isset($_SESSION['dashboard_token']) && validate_token($_SESSION['dashboard_token'], null, true);

$versions = get_prompt_versions($id);
$selected_version = null;
if (isset($_GET['version_id'])) {
    $selected_version = get_prompt_version($_GET['version_id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_edit) {
    if (isset($_POST['toggle_favorite'])) {
        toggle_favorite($id, !$prompt['favorite']);
        header("Location: prompt.php?id=$id");
        exit;
    }
    if (isset($_POST['toggle_public'])) {
        set_public($id, !$prompt['is_public']);
        header("Location: prompt.php?id=$id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= APP_NAME ?> - Prompt Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        alert('Copied!');
    }
    </script>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <img src="assets/logo_small.png" alt="Logo" class="logo d-inline-block align-middle">
        <span class="navbar-brand ms-2"><?= APP_NAME ?> - Prompt Details</span>
    </div>
</nav>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                <?php if ($selected_version): ?>
                    <h3>Viewing Version <?= $selected_version['version'] ?></h3>
                    <pre class="bg-light p-2 rounded"><?= htmlspecialchars($selected_version['prompt']) ?></pre>
                    <p><strong>Title:</strong> <?= htmlspecialchars($selected_version['title']) ?></p>
                    <p><strong>Change:</strong> <?= htmlspecialchars($selected_version['change_desc']) ?></p>
                    <a href="prompt.php?id=<?= $id ?>" class="btn btn-secondary">Back to Current</a>
                <?php else: ?>
                    <h2><?= htmlspecialchars($prompt['title']) ?></h2>
                    <pre id="promptText" class="bg-light p-2 rounded"><?= htmlspecialchars($prompt['prompt']) ?></pre>
                    <button class="btn btn-outline-secondary mb-2" onclick="copyToClipboard(document.getElementById('promptText').innerText)">Copy Prompt</button>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Topic:</strong> <?= htmlspecialchars($prompt['topic']) ?></p>
                            <p><strong>Tags:</strong> <?= htmlspecialchars($prompt['tags']) ?></p>
                            <p><strong>Stage:</strong> <?= htmlspecialchars($prompt['stage']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>LLM Params:</strong> <code><?= htmlspecialchars($prompt['llm_params']) ?></code></p>
                            <p><strong>Favorite:</strong> <?= $prompt['favorite'] ? '★' : '' ?></p>
                            <p><strong>Public:</strong> <?= $prompt['is_public'] ? 'Yes' : 'No' ?></p>
                        </div>
                    </div>
                    <form method="post" class="mb-3 d-inline">
                        <?php if ($can_edit): ?>
                            <button name="toggle_favorite" class="btn btn-outline-warning"><?= $prompt['favorite'] ? 'Unfavorite' : 'Favorite' ?></button>
                            <button name="toggle_public" class="btn btn-outline-info"><?= $prompt['is_public'] ? 'Unshare' : 'Share Publicly' ?></button>
                        <?php endif; ?>
                    </form>
                    <div class="d-flex gap-2 mb-3 flex-wrap">
                        <?php if ($can_edit): ?>
                            <a href="prompt_edit.php?id=<?= $id ?>" class="btn btn-warning">Edit</a>
                            <a href="prompt_delete.php?id=<?= $id ?>" class="btn btn-danger" onclick="return confirm('Delete this prompt?')">Delete</a>
                        <?php endif; ?>
                        <a href="prompt_export.php?id=<?= $id ?>&format=json" class="btn btn-outline-success">Export JSON</a>
                        <a href="prompt_export.php?id=<?= $id ?>&format=csv" class="btn btn-outline-success">Export CSV</a>
                    </div>
                    <h5>Shareable URLs</h5>
                    <div class="input-group mb-3">
                        <input value="<?= $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/prompt_share.php?id='.$id.($prompt['is_public'] ? '' : '&token='.($_SESSION['dashboard_token'] ?? '')) ?>" readonly id="shareUrl" class="form-control">
                        <button class="btn btn-outline-secondary" onclick="copyToClipboard(document.getElementById('shareUrl').value)">Copy URL</button>
                    </div>
                    <?php
                        if ($prompt['is_public']):
                            // Display only if prompt was public shared
                            echo '<h5>Shareable URL as QR Code</h5>';
                            echo '<div class="input-group mb-3">';

                            $shareurl = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/prompt_share.php?id='.$id.($prompt['is_public'] ? '' : '&token='.($_SESSION['dashboard_token'] ?? ''));

                            echo '<img src="qr_generate.php?id='.$shareurl.'" />';
                            echo '</div>';
                        endif;
                    ?>
                    <h5>Version History</h5>
                    <ul class="list-group">
                        <?php foreach ($versions as $v): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="prompt.php?id=<?= $id ?>&version_id=<?= $v['id'] ?>">Version <?= $v['version'] ?></a>
                                <span class="small text-muted"><?= htmlspecialchars($v['change_desc']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                </div>
            </div>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</div>
</body>
</html>