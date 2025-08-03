<?php
// --- Requirement: Export Prompts (CSV/JSON) ---
require_once 'functions.php';
session_start();
if (!isset($_SESSION['dashboard_token']) || !validate_token($_SESSION['dashboard_token'], null, true)) {
    die("Access denied.");
}

$format = $_GET['format'] ?? 'json';
$id = $_GET['id'] ?? null;

if ($id) {
    $prompts = [get_prompt($id)];
} else {
    $prompts = get_prompts(1, '', [], null);
    // For all prompts, get all (not just page 1)
    $db = get_db();
    $stmt = $db->query("SELECT * FROM prompts");
    $prompts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="prompts.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array_keys($prompts[0]));
    foreach ($prompts as $row) fputcsv($out, $row);
    fclose($out);
    exit;
} else {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="prompts.json"');
    echo json_encode($prompts);
    exit;
}