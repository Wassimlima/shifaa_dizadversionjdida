<?php
// إعدادات قاعدة البيانات - لـ WampServer
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shifaa_db');

// اتصال قاعدة البيانات
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("\u0641\u0634\u0644 \u0627\u0644\u0627\u062a\u0635\u0627\u0644 \u0628\u0642\u0627\u0639\u062f\u0629 \u0627\u0644\u0628\u064a\u0627\u0646\u0627\u062a: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// دالة للتحقق من الجلسة
function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: /login.php");
        exit();
    }
}

// دالة للتحقق من دور معين
function requireRole($allowedRoles) {
    requireLogin();
    if (!in_array($_SESSION['role'], (array)$allowedRoles)) {
        header("Location: /index.php?error=access_denied");
        exit();
    }
}
?>