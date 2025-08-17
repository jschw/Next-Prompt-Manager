<?php
require_once 'functions.php';
session_start();
if (!isset($_SESSION['dashboard_token']) || !validate_token($_SESSION['dashboard_token'], null, true)) {
    header('Location: login.php');
    exit;
}

$page = max(1, intval($_GET['page'] ?? 1));
$search = $_GET['search'] ?? '';
$tag_filter = [];
if (isset($_GET['tags']) && $_GET['tags'] !== '') {
    $tag_filter = explode(',', $_GET['tags']);
}
$favorite = (isset($_GET['favorite']) && $_GET['favorite'] == '1') ? 1 : null;

$prompts = get_prompts($page, $search, $tag_filter, $favorite);
$total = get_prompts_count($search, $tag_filter, $favorite);
$pages = ceil($total / 5);
$all_tags = get_all_tags();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= APP_NAME ?> - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text);
        alert('Copied!');
    }
    function exportPrompts() {
        var format = prompt('Export format: csv or json?', 'json');
        if (format) window.location = 'prompt_export.php?format=' + encodeURIComponent(format);
    }
    </script>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <img src="assets/logo_small.png" alt="Logo" class="logo d-inline-block align-middle">
        <span class="navbar-brand ms-2"><?= APP_NAME ?> - Dashboard</span>
    </div>
</nav>
<div class="container">
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search...">
        </div>
        <div class="col-md-3">
            <select name="tags" class="form-select">
                <option value="">All Tags</option>
                <?php foreach ($all_tags as $tag): ?>
                    <option value="<?= htmlspecialchars($tag) ?>"<?= in_array($tag, $tag_filter) ? ' selected' : '' ?>><?= htmlspecialchars($tag) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="favorite" value="1"<?= $favorite ? ' checked' : '' ?> id="favoriteCheck">
                <label class="form-check-label" for="favoriteCheck">Favorites</label>
            </div>
        </div>
        <div class="col-md-3 d-flex">
            <button type="submit" class="btn btn-primary me-2">Filter</button>
            <a href="dashboard.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
    <div class="d-flex justify-content-between mb-3">
        <a href="prompt_edit.php" class="btn btn-success">+ New Prompt</a>
        <button type="button" class="btn btn-outline-info" onclick="exportPrompts()">Export All</button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Prompt</th>
                    <th>Topic</th>
                    <th>Tags</th>
                    <th>Stage</th>
                    <th>Favorite</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($prompts as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['title']) ?></td>
                    <td><?= htmlspecialchars(substr($p['prompt'], 0, 40)) ?>...</td>
                    <td><?= htmlspecialchars($p['topic']) ?></td>
                    <td><?= htmlspecialchars($p['tags']) ?></td>
                    <td><?= htmlspecialchars($p['stage']) ?></td>
                    <td><?= $p['favorite'] ? '★' : '' ?></td>
                    <td>
                        <a href="prompt.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">View</a>
                        <a href="prompt_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end gap-2">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&tags=<?= urlencode(implode(',', $tag_filter)) ?>&favorite=<?= $favorite ?>" class="btn btn-outline-secondary">Previous</a>
        <?php endif; ?>
        <?php if ($page < $pages): ?>
            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&tags=<?= urlencode(implode(',', $tag_filter)) ?>&favorite=<?= $favorite ?>" class="btn btn-outline-secondary">Next</a>
        <?php endif; ?>
    </div>
    <div class="mt-3">
        <a href="token_generate.php">Generate Token</a> | <a href="login.php?logout=1">Logout</a>
    </div>
</div>
</body>
</html>