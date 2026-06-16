<?php
require_once 'db.php';
corsHeaders();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if ($method === 'GET') {
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM guidelines WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $guideline = $stmt->fetch();
        if ($guideline) {
            sendJSON($guideline);
        } else {
            sendJSON(['error' => 'Guideline not found'], 404);
        }
    } else {
        $stmt = $db->query("SELECT * FROM guidelines ORDER BY createdAt ASC");
        sendJSON($stmt->fetchAll());
    }
}

elseif ($method === 'POST') {
    $body = getBody();
    
    if ($action === 'sync' || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/sync') !== false)) {
        try {
            $db->beginTransaction();
            $db->exec("DELETE FROM guidelines");
            
            $guidelines = is_array($body) ? $body : [];
            $stmt = $db->prepare("INSERT INTO guidelines (id, emoji, titleEn, titleAr, descEn, descAr) VALUES (:id, :emoji, :titleEn, :titleAr, :descEn, :descAr)");
            
            $saved = [];
            foreach ($guidelines as $g) {
                // قد لا يحتوي المرشد الصحي القادم من المزامنة المحلية على id، ننشئ له واحدًا
                $gId = $g['id'] ?? $g['_id'] ?? uniqid('guide_');
                $stmt->execute([
                    'id' => $gId,
                    'emoji' => $g['emoji'] ?? '',
                    'titleEn' => $g['titleEn'] ?? '',
                    'titleAr' => $g['titleAr'] ?? '',
                    'descEn' => $g['descEn'] ?? '',
                    'descAr' => $g['descAr'] ?? ''
                ]);
                $g['id'] = $gId;
                $saved[] = $g;
            }
            $db->commit();
            sendJSON($saved);
        } catch (Exception $e) {
            $db->rollBack();
            sendJSON(['error' => $e->getMessage()], 500);
        }
    } else {
        try {
            $gId = $body['id'] ?? $body['_id'] ?? uniqid('guide_');
            $stmt = $db->prepare("INSERT INTO guidelines (id, emoji, titleEn, titleAr, descEn, descAr) VALUES (:id, :emoji, :titleEn, :titleAr, :descEn, :descAr)");
            $stmt->execute([
                'id' => $gId,
                'emoji' => $body['emoji'] ?? '',
                'titleEn' => $body['titleEn'] ?? '',
                'titleAr' => $body['titleAr'] ?? '',
                'descEn' => $body['descEn'] ?? '',
                'descAr' => $body['descAr'] ?? ''
            ]);
            $body['id'] = $gId;
            sendJSON($body);
        } catch (Exception $e) {
            sendJSON(['error' => $e->getMessage()], 500);
        }
    }
}

elseif ($method === 'PUT') {
    if (!$id) {
        sendJSON(['error' => 'Guideline ID is required'], 400);
    }
    
    $body = getBody();
    
    $stmt = $db->prepare("SELECT * FROM guidelines WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();
    
    if (!$existing) {
        sendJSON(['error' => 'Guideline not found'], 404);
    }
    
    $emoji = $body['emoji'] ?? $existing['emoji'];
    $titleEn = $body['titleEn'] ?? $existing['titleEn'];
    $titleAr = $body['titleAr'] ?? $existing['titleAr'];
    $descEn = $body['descEn'] ?? $existing['descEn'];
    $descAr = $body['descAr'] ?? $existing['descAr'];
    
    try {
        $stmt = $db->prepare("UPDATE guidelines SET emoji = :emoji, titleEn = :titleEn, titleAr = :titleAr, descEn = :descEn, descAr = :descAr WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'emoji' => $emoji,
            'titleEn' => $titleEn,
            'titleAr' => $titleAr,
            'descEn' => $descEn,
            'descAr' => $descAr
        ]);
        
        $stmt = $db->prepare("SELECT * FROM guidelines WHERE id = :id");
        $stmt->execute(['id' => $id]);
        sendJSON($stmt->fetch());
    } catch (Exception $e) {
        sendJSON(['error' => $e->getMessage()], 500);
    }
}

elseif ($method === 'DELETE') {
    if (!$id) {
        sendJSON(['error' => 'Guideline ID is required'], 400);
    }
    
    try {
        $stmt = $db->prepare("DELETE FROM guidelines WHERE id = :id");
        $stmt->execute(['id' => $id]);
        sendJSON(['message' => 'Guideline deleted']);
    } catch (Exception $e) {
        sendJSON(['error' => $e->getMessage()], 500);
    }
}
