<?php
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Raw prompt API
    $id = $_GET['id'] ?? null;
    $token = $_GET['token'] ?? null;
    if (!$id || !$token) { http_response_code(400); echo json_encode(['error'=>'Missing id or token']); exit; }
    if (!validate_token($token, $id)) { http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }
    $prompt = get_prompt($id);
    if (!$prompt) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }
    echo json_encode($prompt);
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add prompt via API
    $token = $_POST['token'] ?? null;
    if (!$token || !validate_token($token, null, true)) { http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }
    $fields = ['prompt','title','topic','tags','favorite','stage','llm_params','is_public'];
    $data = [];
    foreach ($fields as $f) $data[$f] = $_POST[$f] ?? '';
    $id = add_prompt($data);
    http_response_code(201);
    echo json_encode(['id'=>$id]);
    exit;
} else {
    http_response_code(405);
    echo json_encode(['error'=>'Method not allowed']);
    exit;
}