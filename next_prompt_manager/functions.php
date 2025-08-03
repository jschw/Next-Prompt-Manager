<?php
require_once 'db.php';

// --- Requirement: Generate UID Access Token ---
function generate_token($length = 64) {
    return bin2hex(random_bytes($length / 2));
}

// --- Requirement: Store token in DB ---
function store_token($token, $prompt_id = null, $is_dashboard = false, $expires = null) {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO access_tokens (token, prompt_id, is_dashboard_token, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$token, $prompt_id, $is_dashboard ? 1 : 0, $expires]);
}

// --- Requirement: Access Control via Token ---
function validate_token($token, $prompt_id = null, $dashboard = false) {
    $db = get_db();
    if ($dashboard) {
        $stmt = $db->prepare("SELECT * FROM access_tokens WHERE token = ? AND is_dashboard_token = 1");
        $stmt->execute([$token]);
    } else if ($prompt_id !== null) {
        $stmt = $db->prepare("SELECT * FROM access_tokens WHERE token = ? AND (prompt_id = ? OR is_dashboard_token = 1)");
        $stmt->execute([$token, $prompt_id]);
    } else {
        $stmt = $db->prepare("SELECT * FROM access_tokens WHERE token = ?");
        $stmt->execute([$token]);
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && (!$row['expires_at'] || strtotime($row['expires_at']) > time())) {
        return true;
    }
    return false;
}

// --- Requirement: Store Prompts with Metadata ---
function add_prompt($data) {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO prompts (prompt, title, topic, tags, favorite, stage, llm_params, version, is_public) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)");
    $stmt->execute([
        $data['prompt'], $data['title'], $data['topic'], $data['tags'],
        !empty($data['favorite']) ? 1 : 0,
        $data['stage'], $data['llm_params'],
        !empty($data['is_public']) ? 1 : 0
    ]);
    return $db->lastInsertId();
}

// --- Requirement: Retrieve and Display All Prompts (with pagination, search, filters) ---
function get_prompts($page = 1, $search = '', $tags = [], $favorite = null) {
    $db = get_db();
    $limit = 5;
    $offset = ($page - 1) * $limit;
    $where = [];
    $params = [];

    if ($search) {
        // Use fulltext if search is long enough, else fallback to LIKE
        if (mb_strlen($search) >= 3) {
            $where[] = "MATCH(prompt, title, tags) AGAINST (?)";
            $params[] = $search;
        } else {
            $where[] = "(prompt LIKE ? OR title LIKE ? OR tags LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
    }
    if ($tags && count($tags) && $tags[0] !== '') {
        $tag_like = [];
        foreach ($tags as $tag) {
            $tag_like[] = "FIND_IN_SET(?, tags)";
            $params[] = $tag;
        }
        $where[] = '(' . implode(' OR ', $tag_like) . ')';
    }
    if ($favorite !== null && $favorite !== '') {
        $where[] = "favorite = ?";
        $params[] = $favorite ? 1 : 0;
    }
    $sql = "SELECT * FROM prompts";
    if ($where) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " ORDER BY updated_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_prompts_count($search = '', $tags = [], $favorite = null) {
    $db = get_db();
    $where = [];
    $params = [];
    if ($search) {
        if (mb_strlen($search) >= 3) {
            $where[] = "MATCH(prompt, title, tags) AGAINST (?)";
            $params[] = $search;
        } else {
            $where[] = "(prompt LIKE ? OR title LIKE ? OR tags LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
    }
    if ($tags && count($tags) && $tags[0] !== '') {
        $tag_like = [];
        foreach ($tags as $tag) {
            $tag_like[] = "FIND_IN_SET(?, tags)";
            $params[] = $tag;
        }
        $where[] = '(' . implode(' OR ', $tag_like) . ')';
    }
    if ($favorite !== null && $favorite !== '') {
        $where[] = "favorite = ?";
        $params[] = $favorite ? 1 : 0;
    }
    $sql = "SELECT COUNT(*) FROM prompts";
    if ($where) $sql .= " WHERE " . implode(' AND ', $where);
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

// --- Requirement: Retrieve Single Prompt ---
function get_prompt($id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM prompts WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- Requirement: Edit Prompt (with version control) ---
function update_prompt($id, $data, $change_desc) {
    $db = get_db();
    $prompt = get_prompt($id);

    // Store old version
    $stmt = $db->prepare("INSERT INTO prompt_versions (prompt_id, version, prompt, title, topic, tags, favorite, stage, llm_params, change_desc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $id, $prompt['version'] + 1, $data['prompt'], $data['title'], $data['topic'], $data['tags'],
        !empty($data['favorite']) ? 1 : 0, $data['stage'], $data['llm_params'], $change_desc
    ]);

    // Update prompt
    $stmt = $db->prepare("UPDATE prompts SET prompt=?, title=?, topic=?, tags=?, favorite=?, stage=?, llm_params=?, version=version+1 WHERE id=?");
    $stmt->execute([
        $data['prompt'], $data['title'], $data['topic'], $data['tags'],
        !empty($data['favorite']) ? 1 : 0, $data['stage'], $data['llm_params'], $id
    ]);
}

// --- Requirement: Delete Prompt ---
function delete_prompt($id) {
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM prompts WHERE id = ?");
    $stmt->execute([$id]);
}

// --- Requirement: Version History ---
function get_prompt_versions($prompt_id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM prompt_versions WHERE prompt_id = ? ORDER BY version DESC");
    $stmt->execute([$prompt_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_prompt_version($version_id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM prompt_versions WHERE id = ?");
    $stmt->execute([$version_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- Requirement: Toggle Favorite Flag ---
function toggle_favorite($id, $is_favorite) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE prompts SET favorite = ? WHERE id = ?");
    $stmt->execute([$is_favorite ? 1 : 0, $id]);
}

// --- Requirement: Share/Unshare Prompt ---
function set_public($id, $is_public) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE prompts SET is_public = ? WHERE id = ?");
    $stmt->execute([$is_public ? 1 : 0, $id]);
}

// --- Requirement: Get all unique tags for filter dropdown ---
function get_all_tags() {
    $db = get_db();
    $stmt = $db->query("SELECT tags FROM prompts");
    $tags = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $tagstr) {
        foreach (explode(',', $tagstr) as $tag) {
            $tag = trim($tag);
            if ($tag) $tags[$tag] = true;
        }
    }
    return array_keys($tags);
}
?>