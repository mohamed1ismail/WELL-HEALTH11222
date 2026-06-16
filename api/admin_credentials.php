<?php
require_once 'db.php';
corsHeaders();

$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $db->query("SELECT adminUsername, adminPassword FROM settings LIMIT 1");
        $settings = $stmt->fetch();
        
        $username = ($settings && !empty($settings['adminUsername'])) ? $settings['adminUsername'] : 'admin';
        $password = ($settings && !empty($settings['adminPassword'])) ? $settings['adminPassword'] : 'wellhealth123';
        
        sendJSON([
            'username' => $username,
            'password' => $password
        ]);
    } catch (Exception $e) {
        // في حال حدوث خطأ أو عدم وجود الجدول بعد، نرجع القيم الافتراضية
        sendJSON([
            'username' => 'admin',
            'password' => 'wellhealth123'
        ]);
    }
} else {
    sendJSON(['error' => 'Method not allowed'], 405);
}
