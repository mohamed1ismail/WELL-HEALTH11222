<?php
require_once 'db.php';
corsHeaders();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// دالة لتنسيق قيم الإعدادات لتطابق الأنماط المتوقعة في واجهة المستخدم
function formatSettings($settings, $excludePassword = true) {
    if (!$settings) return new stdClass();
    
    $settings['bannerEnabled'] = isset($settings['bannerEnabled']) ? (bool)$settings['bannerEnabled'] : false;
    $settings['appDownloadEnabled'] = isset($settings['appDownloadEnabled']) ? (bool)$settings['appDownloadEnabled'] : true;
    
    if ($excludePassword) {
        unset($settings['adminPassword']);
    }
    
    return $settings;
}

if ($method === 'GET') {
    try {
        $stmt = $db->query("SELECT * FROM settings LIMIT 1");
        $settings = $stmt->fetch();
        sendJSON(formatSettings($settings, true));
    } catch (Exception $e) {
        sendJSON(['error' => $e->getMessage()], 500);
    }
}

elseif ($method === 'POST') {
    $body = getBody();
    
    try {
        $stmt = $db->query("SELECT * FROM settings LIMIT 1");
        $existing = $stmt->fetch();
        
        if ($existing) {
            // تحديث الصف الحالي
            $captionEn = $body['captionEn'] ?? $existing['captionEn'];
            $captionAr = $body['captionAr'] ?? $existing['captionAr'];
            $bannerEnabled = isset($body['bannerEnabled']) ? ($body['bannerEnabled'] ? 1 : 0) : $existing['bannerEnabled'];
            $bannerText = $body['bannerText'] ?? $existing['bannerText'];
            $menuUrl = $body['menuUrl'] ?? $existing['menuUrl'];
            $appDownloadEnabled = isset($body['appDownloadEnabled']) ? ($body['appDownloadEnabled'] ? 1 : 0) : $existing['appDownloadEnabled'];
            $androidAppUrl = $body['androidAppUrl'] ?? $existing['androidAppUrl'];
            $iosAppUrl = $body['iosAppUrl'] ?? $existing['iosAppUrl'];
            $logo = $body['logo'] ?? $existing['logo'];
            $coverImage = $body['coverImage'] ?? $existing['coverImage'];
            $aboutTextEn = $body['aboutTextEn'] ?? $existing['aboutTextEn'];
            $aboutTextAr = $body['aboutTextAr'] ?? $existing['aboutTextAr'];
            $adminUsername = $body['adminUsername'] ?? $existing['adminUsername'];
            $adminPassword = $body['adminPassword'] ?? $existing['adminPassword'];
            
            $stmtUpdate = $db->prepare("UPDATE settings SET 
                captionEn = :captionEn, captionAr = :captionAr, 
                bannerEnabled = :bannerEnabled, bannerText = :bannerText, 
                menuUrl = :menuUrl, appDownloadEnabled = :appDownloadEnabled, 
                androidAppUrl = :androidAppUrl, iosAppUrl = :iosAppUrl, 
                logo = :logo, coverImage = :coverImage, 
                aboutTextEn = :aboutTextEn, aboutTextAr = :aboutTextAr, 
                adminUsername = :adminUsername, adminPassword = :adminPassword
                WHERE id = :id");
                
            $stmtUpdate->execute([
                'id' => $existing['id'],
                'captionEn' => $captionEn,
                'captionAr' => $captionAr,
                'bannerEnabled' => $bannerEnabled,
                'bannerText' => $bannerText,
                'menuUrl' => $menuUrl,
                'appDownloadEnabled' => $appDownloadEnabled,
                'androidAppUrl' => $androidAppUrl,
                'iosAppUrl' => $iosAppUrl,
                'logo' => $logo,
                'coverImage' => $coverImage,
                'aboutTextEn' => $aboutTextEn,
                'aboutTextAr' => $aboutTextAr,
                'adminUsername' => $adminUsername,
                'adminPassword' => $adminPassword
            ]);
            
            // جلب الإعدادات المحدثة
            $stmtGet = $db->prepare("SELECT * FROM settings WHERE id = :id");
            $stmtGet->execute(['id' => $existing['id']]);
            sendJSON(formatSettings($stmtGet->fetch(), false));
        } else {
            // إدراج صف جديد في حال عدم وجوده
            $captionEn = $body['captionEn'] ?? 'HEALTHY CHOICES HAPPY LIVES';
            $captionAr = $body['captionAr'] ?? 'خيارات صحية، حياة سعيدة';
            $bannerEnabled = isset($body['bannerEnabled']) ? ($body['bannerEnabled'] ? 1 : 0) : 0;
            $bannerText = $body['bannerText'] ?? '';
            $menuUrl = $body['menuUrl'] ?? '';
            $appDownloadEnabled = isset($body['appDownloadEnabled']) ? ($body['appDownloadEnabled'] ? 1 : 0) : 1;
            $androidAppUrl = $body['androidAppUrl'] ?? '';
            $iosAppUrl = $body['iosAppUrl'] ?? '';
            $logo = $body['logo'] ?? '';
            $coverImage = $body['coverImage'] ?? '';
            $aboutTextEn = $body['aboutTextEn'] ?? '';
            $aboutTextAr = $body['aboutTextAr'] ?? '';
            $adminUsername = $body['adminUsername'] ?? 'admin';
            $adminPassword = $body['adminPassword'] ?? 'wellhealth123';
            
            $stmtInsert = $db->prepare("INSERT INTO settings (
                captionEn, captionAr, bannerEnabled, bannerText, menuUrl, 
                appDownloadEnabled, androidAppUrl, iosAppUrl, logo, coverImage, 
                aboutTextEn, aboutTextAr, adminUsername, adminPassword
            ) VALUES (
                :captionEn, :captionAr, :bannerEnabled, :bannerText, :menuUrl, 
                :appDownloadEnabled, :androidAppUrl, :iosAppUrl, :logo, :coverImage, 
                :aboutTextEn, :aboutTextAr, :adminUsername, :adminPassword
            )");
            
            $stmtInsert->execute([
                'captionEn' => $captionEn,
                'captionAr' => $captionAr,
                'bannerEnabled' => $bannerEnabled,
                'bannerText' => $bannerText,
                'menuUrl' => $menuUrl,
                'appDownloadEnabled' => $appDownloadEnabled,
                'androidAppUrl' => $androidAppUrl,
                'iosAppUrl' => $iosAppUrl,
                'logo' => $logo,
                'coverImage' => $coverImage,
                'aboutTextEn' => $aboutTextEn,
                'aboutTextAr' => $aboutTextAr,
                'adminUsername' => $adminUsername,
                'adminPassword' => $adminPassword
            ]);
            
            $newId = $db->lastInsertId();
            $stmtGet = $db->prepare("SELECT * FROM settings WHERE id = :id");
            $stmtGet->execute(['id' => $newId]);
            sendJSON(formatSettings($stmtGet->fetch(), false));
        }
    } catch (Exception $e) {
        sendJSON(['error' => $e->getMessage()], 500);
    }
}
