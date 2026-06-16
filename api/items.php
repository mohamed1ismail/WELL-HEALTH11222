<?php
require_once 'db.php';
corsHeaders();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

// دالة لتنسيق العنصر ليتطابق مع واجهة المونغو دي بي (MongoDB format)
function formatItem($item) {
    if (!$item) return null;
    $item['visible'] = isset($item['visible']) ? (bool)$item['visible'] : true;
    if (isset($item['portions']) && is_string($item['portions'])) {
        $item['portions'] = json_decode($item['portions'], true) ?? [];
    } else if (!isset($item['portions'])) {
        $item['portions'] = [];
    }
    if (isset($item['discountValue'])) {
        $item['discountValue'] = (float)$item['discountValue'];
    }
    return $item;
}

if ($method === 'GET') {
    if ($id) {
        // جلب عنصر واحد
        $stmt = $db->prepare("SELECT * FROM items WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();
        if ($item) {
            sendJSON(formatItem($item));
        } else {
            sendJSON(['error' => 'Item not found'], 404);
        }
    } else {
        // جلب كل العناصر
        $stmt = $db->query("SELECT * FROM items ORDER BY createdAt DESC");
        $items = $stmt->fetchAll();
        $formatted = array_map('formatItem', $items);
        sendJSON($formatted);
    }
}

elseif ($method === 'POST') {
    $body = getBody();
    
    // التعامل مع المزامنة الكلية (Bulk Sync)
    if ($action === 'sync' || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/sync') !== false)) {
        try {
            $db->beginTransaction();
            $db->exec("DELETE FROM items");
            
            $items = is_array($body) ? $body : [];
            $stmt = $db->prepare("INSERT INTO items (
                id, nameEn, nameAr, descEn, descAr, category, price, priceSm, priceMd, priceLg, 
                img, cal, protein, carbs, fats, discountType, discountValue, imageSize, visible, portions
            ) VALUES (
                :id, :nameEn, :nameAr, :descEn, :descAr, :category, :price, :priceSm, :priceMd, :priceLg, 
                :img, :cal, :protein, :carbs, :fats, :discountType, :discountValue, :imageSize, :visible, :portions
            )");
            
            $savedItems = [];
            foreach ($items as $item) {
                $itemId = $item['id'] ?? uniqid('item_');
                $portionsJson = isset($item['portions']) ? json_encode($item['portions'], JSON_UNESCAPED_UNICODE) : '[]';
                $visibleVal = isset($item['visible']) ? ($item['visible'] ? 1 : 0) : 1;
                
                $params = [
                    'id' => $itemId,
                    'nameEn' => $item['nameEn'] ?? '',
                    'nameAr' => $item['nameAr'] ?? '',
                    'descEn' => $item['descEn'] ?? '',
                    'descAr' => $item['descAr'] ?? '',
                    'category' => $item['category'] ?? '',
                    'price' => $item['price'] ?? '',
                    'priceSm' => $item['priceSm'] ?? '',
                    'priceMd' => $item['priceMd'] ?? '',
                    'priceLg' => $item['priceLg'] ?? '',
                    'img' => $item['img'] ?? '',
                    'cal' => $item['cal'] ?? '',
                    'protein' => $item['protein'] ?? '',
                    'carbs' => $item['carbs'] ?? '',
                    'fats' => $item['fats'] ?? '',
                    'discountType' => $item['discountType'] ?? '',
                    'discountValue' => $item['discountValue'] ?? 0,
                    'imageSize' => $item['imageSize'] ?? 'medium',
                    'visible' => $visibleVal,
                    'portions' => $portionsJson
                ];
                
                $stmt->execute($params);
                $item['id'] = $itemId;
                $savedItems[] = $item;
            }
            $db->commit();
            sendJSON($savedItems);
        } catch (Exception $e) {
            $db->rollBack();
            sendJSON(['error' => $e->getMessage()], 500);
        }
    } else {
        // إنشاء عنصر جديد منفرد
        try {
            $itemId = $body['id'] ?? uniqid('item_');
            $portionsJson = isset($body['portions']) ? json_encode($body['portions'], JSON_UNESCAPED_UNICODE) : '[]';
            $visibleVal = isset($body['visible']) ? ($body['visible'] ? 1 : 0) : 1;
            
            $stmt = $db->prepare("INSERT INTO items (
                id, nameEn, nameAr, descEn, descAr, category, price, priceSm, priceMd, priceLg, 
                img, cal, protein, carbs, fats, discountType, discountValue, imageSize, visible, portions
            ) VALUES (
                :id, :nameEn, :nameAr, :descEn, :descAr, :category, :price, :priceSm, :priceMd, :priceLg, 
                :img, :cal, :protein, :carbs, :fats, :discountType, :discountValue, :imageSize, :visible, :portions
            )");
            
            $stmt->execute([
                'id' => $itemId,
                'nameEn' => $body['nameEn'] ?? '',
                'nameAr' => $body['nameAr'] ?? '',
                'descEn' => $body['descEn'] ?? '',
                'descAr' => $body['descAr'] ?? '',
                'category' => $body['category'] ?? '',
                'price' => $body['price'] ?? '',
                'priceSm' => $body['priceSm'] ?? '',
                'priceMd' => $body['priceMd'] ?? '',
                'priceLg' => $body['priceLg'] ?? '',
                'img' => $body['img'] ?? '',
                'cal' => $body['cal'] ?? '',
                'protein' => $body['protein'] ?? '',
                'carbs' => $body['carbs'] ?? '',
                'fats' => $body['fats'] ?? '',
                'discountType' => $body['discountType'] ?? '',
                'discountValue' => $body['discountValue'] ?? 0,
                'imageSize' => $body['imageSize'] ?? 'medium',
                'visible' => $visibleVal,
                'portions' => $portionsJson
            ]);
            
            $body['id'] = $itemId;
            sendJSON($body);
        } catch (Exception $e) {
            sendJSON(['error' => $e->getMessage()], 500);
        }
    }
}

elseif ($method === 'PUT') {
    if (!$id) {
        sendJSON(['error' => 'Item ID is required for update'], 400);
    }
    
    $body = getBody();
    
    // جلب البيانات الحالية لدمجها مع التعديلات
    $stmt = $db->prepare("SELECT * FROM items WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();
    
    if (!$existing) {
        sendJSON(['error' => 'Item not found'], 404);
    }
    
    // دمج التعديلات
    $nameEn = $body['nameEn'] ?? $existing['nameEn'];
    $nameAr = $body['nameAr'] ?? $existing['nameAr'];
    $descEn = $body['descEn'] ?? $existing['descEn'];
    $descAr = $body['descAr'] ?? $existing['descAr'];
    $category = $body['category'] ?? $existing['category'];
    $price = $body['price'] ?? $existing['price'];
    $priceSm = $body['priceSm'] ?? $existing['priceSm'];
    $priceMd = $body['priceMd'] ?? $existing['priceMd'];
    $priceLg = $body['priceLg'] ?? $existing['priceLg'];
    $img = $body['img'] ?? $existing['img'];
    $cal = $body['cal'] ?? $existing['cal'];
    $protein = $body['protein'] ?? $existing['protein'];
    $carbs = $body['carbs'] ?? $existing['carbs'];
    $fats = $body['fats'] ?? $existing['fats'];
    $discountType = $body['discountType'] ?? $existing['discountType'];
    $discountValue = isset($body['discountValue']) ? $body['discountValue'] : $existing['discountValue'];
    $imageSize = $body['imageSize'] ?? $existing['imageSize'];
    $visibleVal = isset($body['visible']) ? ($body['visible'] ? 1 : 0) : $existing['visible'];
    
    if (isset($body['portions'])) {
        $portionsJson = json_encode($body['portions'], JSON_UNESCAPED_UNICODE);
    } else {
        $portionsJson = $existing['portions'];
    }
    
    try {
        $stmt = $db->prepare("UPDATE items SET 
            nameEn = :nameEn, nameAr = :nameAr, descEn = :descEn, descAr = :descAr, 
            category = :category, price = :price, priceSm = :priceSm, priceMd = :priceMd, priceLg = :priceLg, 
            img = :img, cal = :cal, protein = :protein, carbs = :carbs, fats = :fats, 
            discountType = :discountType, discountValue = :discountValue, imageSize = :imageSize, 
            visible = :visible, portions = :portions 
            WHERE id = :id");
            
        $stmt->execute([
            'id' => $id,
            'nameEn' => $nameEn,
            'nameAr' => $nameAr,
            'descEn' => $descEn,
            'descAr' => $descAr,
            'category' => $category,
            'price' => $price,
            'priceSm' => $priceSm,
            'priceMd' => $priceMd,
            'priceLg' => $priceLg,
            'img' => $img,
            'cal' => $cal,
            'protein' => $protein,
            'carbs' => $carbs,
            'fats' => $fats,
            'discountType' => $discountType,
            'discountValue' => $discountValue,
            'imageSize' => $imageSize,
            'visible' => $visibleVal,
            'portions' => $portionsJson
        ]);
        
        // جلب العنصر المحدث لإرساله
        $stmt = $db->prepare("SELECT * FROM items WHERE id = :id");
        $stmt->execute(['id' => $id]);
        sendJSON(formatItem($stmt->fetch()));
    } catch (Exception $e) {
        sendJSON(['error' => $e->getMessage()], 500);
    }
}

elseif ($method === 'DELETE') {
    if (!$id) {
        sendJSON(['error' => 'Item ID is required for delete'], 400);
    }
    
    try {
        $stmt = $db->prepare("DELETE FROM items WHERE id = :id");
        $stmt->execute(['id' => $id]);
        sendJSON(['message' => 'Item deleted']);
    } catch (Exception $e) {
        sendJSON(['error' => $e->getMessage()], 500);
    }
}
