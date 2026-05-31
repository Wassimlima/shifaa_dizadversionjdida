<?php
define('DB_SOCK', '/home/runner/mysql-run/mysql.sock');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'shifaa_dizad');

function getDB() {
    $conn = new mysqli(null, DB_USER, DB_PASS, DB_NAME, null, DB_SOCK);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function getDbConnection() {
    $dsn = 'mysql:unix_socket=' . DB_SOCK . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
}
