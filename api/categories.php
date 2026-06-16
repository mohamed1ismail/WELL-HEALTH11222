<?php
require_once 'db.php';
corsHeaders();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if ($method === 'GET') {
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $category = $stmt->fetch();
        if ($category) {
            sendJSON($category);
        } else {
            sendJSON(['error' => 'Category not found'], 404);
        }
    } else {
        $stmt = $db->query("SELECT * FROM categories ORDER BY createdAt ASC");
        sendJSON($stmt->fetchAll());
    }
}

elseif ($method === 'POST') {
    $body = getBody();
    
    if ($action === 'sync' || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/sync') !== false)) {
        try {
            $db->beginTransaction();
            $db->exec("DELETE FROM categories");
            
            $categories = is_array($body) ? $body : [];
            $stmt = $db->prepare("INSERT INTO categories (id, nameEn, nameAr, icon) VALUES (:id, :nameEn, :nameAr, :icon)");
            
            $saved = [];
            foreach ($categories as $cat) {
                $catId = $cat['id'] ?? uniqid('cat_');
                $stmt->execute([
                    'id' => $catId,
                    'nameEn' => $cat['nameEn'] ?? '',
                    'nameAr' => $cat['nameAr'] ?? '',
                    'icon' => $cat['icon'] ?? ''
                ]);
                $cat['id'] = $catId;
                $saved[] = $cat;
            }
            $db->commit();
            sendJSON($saved);
        } catch (Exception $e) {
            $db->rollBack();
            sendJSON(['error' => $e->getMessage()], 500);
        }
    } else {
        try {
            $catId = $body['id'] ?? uniqid('cat_');
            $stmt = $db->prepare("INSERT INTO categories (id, nameEn, nameAr, icon) VALUES (:id, :nameEn, :nameAr, :icon)");
            $stmt->execute([
                'id' => $catId,
                'nameEn' => $body['nameEn'] ?? '',
                'nameAr' => $body['nameAr'] ?? '',
                'icon' => $body['icon'] ?? ''
            ]);
            $body['id'] = $catId;
            sendJSON($body);
        } catch (Exception $e) {
            sendJSON(['error' => $e->getMessage()], 500);
        }
    }
}

elseif ($method === 'PUT') {
    if (!$id) {
        sendJSON(['error' => 'Category ID is required'], 400);
    }
    
    $body = getBody();
    
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();
    
    if (!$existing) {
        sendJSON(['error' => 'Category not found'], 404);
    }
    
    $nameEn = $body['nameEn'] ?? $existing['nameEn'];
    $nameAr = $body['nameAr'] ?? $existing['nameAr'];
    $icon = $body['icon'] ?? $existing['icon'];
    
    try {
        $stmt = $db->prepare("UPDATE categories SET nameEn = :nameEn, nameAr = :nameAr, icon = :icon WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'nameEn' => $nameEn,
            'nameAr' => $nameAr,
            'icon' => $icon
        ]);
        
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        sendJSON($stmt->fetch());
    } catch (Exception $e) {
        sendJSON(['error' => $e->getMessage()], 500);
    }
}

elseif ($method === 'DELETE') {
    if (!$id) {
        sendJSON(['error' => 'Category ID is required'], 400);
    }
    
    try {
        $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        sendJSON(['message' => 'Category deleted']);
    } catch (Exception $e) {
        sendJSON(['error' => $e->getMessage()], 500);
    }
}
