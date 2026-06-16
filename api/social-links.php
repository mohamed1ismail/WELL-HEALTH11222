<?php
require_once 'db.php';
corsHeaders();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if ($method === 'GET') {
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM social_links WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $link = $stmt->fetch();
        if ($link) {
            sendJSON($link);
        } else {
            sendJSON(['error' => 'Social link not found'], 404);
        }
    } else {
        $stmt = $db->query("SELECT * FROM social_links ORDER BY createdAt ASC");
        sendJSON($stmt->fetchAll());
    }
}

elseif ($method === 'POST') {
    $body = getBody();
    
    if ($action === 'sync' || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/sync') !== false)) {
        try {
            $db->beginTransaction();
            $db->exec("DELETE FROM social_links");
            
            $links = is_array($body) ? $body : [];
            $stmt = $db->prepare("INSERT INTO social_links (id, label, url, icon, color) VALUES (:id, :label, :url, :icon, :color)");
            
            $saved = [];
            foreach ($links as $link) {
                $linkId = $link['id'] ?? $link['_id'] ?? uniqid('link_');
                $stmt->execute([
                    'id' => $linkId,
                    'label' => $link['label'] ?? '',
                    'url' => $link['url'] ?? '',
                    'icon' => $link['icon'] ?? '',
                    'color' => $link['color'] ?? ''
                ]);
                $link['id'] = $linkId;
                $saved[] = $link;
            }
            $db->commit();
            sendJSON($saved);
        } catch (Exception $e) {
            $db->rollBack();
            sendJSON(['error' => $e->getMessage()], 500);
        }
    } else {
        try {
            $linkId = $body['id'] ?? $body['_id'] ?? uniqid('link_');
            $stmt = $db->prepare("INSERT INTO social_links (id, label, url, icon, color) VALUES (:id, :label, :url, :icon, :color)");
            $stmt->execute([
                'id' => $linkId,
                'label' => $body['label'] ?? '',
                'url' => $body['url'] ?? '',
                'icon' => $body['icon'] ?? '',
                'color' => $body['color'] ?? ''
            ]);
            $body['id'] = $linkId;
            sendJSON($body);
        } catch (Exception $e) {
            sendJSON(['error' => $e->getMessage()], 500);
        }
    }
}

elseif ($method === 'PUT') {
    if (!$id) {
        sendJSON(['error' => 'Social link ID is required'], 400);
    }
    
    $body = getBody();
    
    $stmt = $db->prepare("SELECT * FROM social_links WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();
    
    if (!$existing) {
        sendJSON(['error' => 'Social link not found'], 404);
    }
    
    $label = $body['label'] ?? $existing['label'];
    $url = $body['url'] ?? $existing['url'];
    $icon = $body['icon'] ?? $existing['icon'];
    $color = $body['color'] ?? $existing['color'];
    
    try {
        $stmt = $db->prepare("UPDATE social_links SET label = :label, url = :url, icon = :icon, color = :color WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'label' => $label,
            'url' => $url,
            'icon' => $icon,
            'color' => $color
        ]);
        
        $stmt = $db->prepare("SELECT * FROM social_links WHERE id = :id");
        $stmt->execute(['id' => $id]);
        sendJSON($stmt->fetch());
    } catch (Exception $e) {
        sendJSON(['error' => $e->getMessage()], 500);
    }
}

elseif ($method === 'DELETE') {
    if (!$id) {
        sendJSON(['error' => 'Social link ID is required'], 400);
    }
    
    try {
        $stmt = $db->prepare("DELETE FROM social_links WHERE id = :id");
        $stmt->execute(['id' => $id]);
        sendJSON(['message' => 'Social link deleted']);
    } catch (Exception $e) {
        sendJSON(['error' => $e->getMessage()], 500);
    }
}
