<?php
require_once '../../utils/cors.php';
require_once '../../utils/response.php';
require_once '../../config/database.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $pdo = getDbConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT setting_key, setting_value, setting_type FROM platform_settings");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($rows as $row) {
            $val = $row['setting_value'];
            if ($row['setting_type'] === 'integer') $val = (int)$val;
            if ($row['setting_type'] === 'boolean') $val = (bool)(int)$val;
            if ($row['setting_type'] === 'json')    $val = json_decode($val, true);
            $settings[$row['setting_key']] = $val;
        }

        sendSuccess($settings);

    } else {
        sendError('Method not allowed', 405);
    }

} catch (PDOException $e) {
    sendError('خطأ في قاعدة البيانات: ' . $e->getMessage(), 500);
}
