<?php
require_once 'db.php';

// السماح بالوصول من أي مكان لتسهيل التثبيت
header('Access-Control-Allow-Origin: *');

$message = '';
$status = 'pending';

try {
    $pdo = getDB();

    // 1. جدول التصنيفات (categories)
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id VARCHAR(255) PRIMARY KEY,
        nameEn VARCHAR(255) NOT NULL,
        nameAr VARCHAR(255) NOT NULL,
        icon TEXT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 2. جدول العناصر (items)
    $pdo->exec("CREATE TABLE IF NOT EXISTS items (
        id VARCHAR(255) PRIMARY KEY,
        nameEn VARCHAR(255) NOT NULL,
        nameAr VARCHAR(255) NOT NULL,
        descEn TEXT NULL,
        descAr TEXT NULL,
        category VARCHAR(255) NOT NULL,
        price VARCHAR(50) NULL,
        priceSm VARCHAR(50) NULL,
        priceMd VARCHAR(50) NULL,
        priceLg VARCHAR(50) NULL,
        img LONGTEXT NULL,
        cal VARCHAR(50) NULL,
        protein VARCHAR(50) NULL,
        carbs VARCHAR(50) NULL,
        fats VARCHAR(50) NULL,
        discountType VARCHAR(50) NULL,
        discountValue DECIMAL(10,2) NULL,
        imageSize VARCHAR(50) DEFAULT 'medium',
        visible TINYINT(1) DEFAULT 1,
        portions TEXT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 3. جدول الإرشادات الصحية (guidelines)
    $pdo->exec("CREATE TABLE IF NOT EXISTS guidelines (
        id VARCHAR(255) PRIMARY KEY,
        emoji VARCHAR(50) NULL,
        titleEn VARCHAR(255) NOT NULL,
        titleAr VARCHAR(255) NOT NULL,
        descEn TEXT NULL,
        descAr TEXT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 4. جدول روابط التواصل الاجتماعي (social_links)
    $pdo->exec("CREATE TABLE IF NOT EXISTS social_links (
        id VARCHAR(255) PRIMARY KEY,
        label VARCHAR(255) NOT NULL,
        url TEXT NOT NULL,
        icon VARCHAR(255) NULL,
        color VARCHAR(50) NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 5. جدول الإعدادات وحساب المسؤول (settings)
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        captionEn VARCHAR(255) NULL,
        captionAr VARCHAR(255) NULL,
        bannerEnabled TINYINT(1) DEFAULT 0,
        bannerText TEXT NULL,
        menuUrl TEXT NULL,
        appDownloadEnabled TINYINT(1) DEFAULT 1,
        androidAppUrl TEXT NULL,
        iosAppUrl TEXT NULL,
        logo LONGTEXT NULL,
        coverImage LONGTEXT NULL,
        aboutTextEn TEXT NULL,
        aboutTextAr TEXT NULL,
        adminUsername VARCHAR(255) DEFAULT 'admin',
        adminPassword VARCHAR(255) DEFAULT 'wellhealth123',
        updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // تحقق من وجود إعدادات افتراضية، وإن لم توجد قم بإنشائها
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO settings (
            captionEn, captionAr, bannerEnabled, bannerText, menuUrl, 
            appDownloadEnabled, androidAppUrl, iosAppUrl, 
            adminUsername, adminPassword, aboutTextEn, aboutTextAr
        ) VALUES (
            'HEALTHY CHOICES HAPPY LIVES', 'خيارات صحية، حياة سعيدة', 0, '', '', 
            1, '', '', 
            'admin', 'wellhealth123', 'About Well Health', 'عن ويل هيلث'
        )");
    }

    $status = 'success';
    $message = 'تم إنشاء جداول قاعدة البيانات MySQL وإعداد قيم التثبيت الافتراضية بنجاح!';
} catch (PDOException $e) {
    $status = 'error';
    $message = 'حدث خطأ أثناء تهيئة قاعدة البيانات: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تهيئة قاعدة البيانات - Well Health</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Cairo:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --error: #ef4444;
        }
        body {
            font-family: 'Cairo', 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background-color: var(--card-bg);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .logo {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 2.5rem;
        }
        .status-icon.success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 2px solid var(--success);
        }
        .status-icon.error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error);
            border: 2px solid var(--error);
        }
        h2 {
            font-weight: 700;
            margin-bottom: 15px;
        }
        p {
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            color: white;
            padding: 12px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.4);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 20px -3px rgba(16, 185, 129, 0.6);
        }
        .error-details {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 12px;
            font-family: monospace;
            text-align: left;
            direction: ltr;
            font-size: 0.9rem;
            color: var(--error);
            overflow-x: auto;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">WELL HEALTH</div>
        
        <?php if ($status === 'success'): ?>
            <div class="status-icon success">✓</div>
            <h2>اكتملت التهيئة!</h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            <p style="font-size: 0.9rem; color: #f59e0b;">⚠️ تنبيه أمني: يرجى حذف ملف <code>install.php</code> من السيرفر بعد الانتهاء لحماية قاعدة بياناتك.</p>
            <a href="../admin.html" class="btn">الانتقال للوحة التحكم</a>
        <?php else: ?>
            <div class="status-icon error">✗</div>
            <h2>فشلت التهيئة!</h2>
            <p>تعذر الاتصال بقاعدة البيانات أو إنشاء الجداول. يرجى التحقق من إعدادات قاعدة البيانات في ملف <code>api/db.php</code>.</p>
            <div class="error-details">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <a href="install.php" class="btn">إعادة المحاولة</a>
        <?php endif; ?>
    </div>
</body>
</html>
