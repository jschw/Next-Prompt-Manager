<?php
require_once 'functions.php';
session_start();
if (!isset($_SESSION['dashboard_token']) || !validate_token($_SESSION['dashboard_token'], null, true)) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
$editing = false;
if ($id) {
    $prompt = get_prompt($id);
    if (!$prompt) die("Prompt not found.");
    $editing = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'prompt' => $_POST['prompt'],
        'title' => $_POST['title'],
        'topic' => $_POST['topic'],
        'tags' => $_POST['tags'],
        'favorite' => isset($_POST['favorite']),
        'stage' => $_POST['stage'],
        'llm_params' => $_POST['llm_params'],
        'is_public' => isset($_POST['is_public'])
    ];
    if ($editing) {
        $change_desc = $_POST['change_desc'] ?? '';
        update_prompt($id, $data, $change_desc);
        header("Location: prompt.php?id=$id");
        exit;
    } else {
        $new_id = add_prompt($data);
        header("Location: prompt.php?id=$new_id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= APP_NAME ?> - <?= $editing ? 'Edit' : 'New' ?> Prompt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <img src="assets/logo_small.png" alt="Logo" class="logo d-inline-block align-middle">
        <span class="navbar-brand ms-2"><?= APP_NAME ?> - <?= $editing ? 'Edit' : 'New' ?> Prompt</span>
    </div>
</nav>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($prompt['title'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prompt Text</label>
                            <textarea name="prompt" class="form-control" required><?= htmlspecialchars($prompt['prompt'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Topic</label>
                            <input type="text" name="topic" class="form-control" value="<?= htmlspecialchars($prompt['topic'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tags (comma-separated)</label>
                            <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($prompt['tags'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stage</label>
                            <select name="stage" class="form-select">
                                <?php foreach (['draft','review','final','archived'] as $stage): ?>
                                    <option value="<?= $stage ?>"<?= ($prompt['stage'] ?? '') == $stage ? ' selected' : '' ?>><?= ucfirst($stage) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">LLM Parameters (JSON or text)</label>
                            <textarea name="llm_params" class="form-control"><?= htmlspecialchars($prompt['llm_params'] ?? '') ?></textarea>
                        </div>
                        <div class="form-check form-check-inline mb-3">
                            <input class="form-check-input" type="checkbox" name="favorite" id="favorite"<?= !empty($prompt['favorite']) ? ' checked' : '' ?>>
                            <label class="form-check-label" for="favorite">Favorite</label>
                        </div>
                        <div class="form-check form-check-inline mb-3">
                            <input class="form-check-input" type="checkbox" name="is_public" id="is_public"<?= !empty($prompt['is_public']) ? ' checked' : '' ?>>
                            <label class="form-check-label" for="is_public">Public</label>
                        </div>
                        <?php if ($editing): ?>
                            <div class="mb-3">
                                <label class="form-label">Change Description (for version control)</label>
                                <input type="text" name="change_desc" class="form-control" required>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><?= $editing ? 'Save Changes' : 'Create Prompt' ?></button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>