<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDB();
$pharmacyId = intval($_GET['pharmacyId'] ?? 0);
if (!$pharmacyId) sendError('pharmacyId required');

$search = isset($_GET['search']) ? '%' . $db->real_escape_string($_GET['search']) . '%' : '%';
$status = isset($_GET['status']) ? $db->real_escape_string($_GET['status']) : '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = min(100, intval($_GET['limit'] ?? 20));
$offset = ($page - 1) * $limit;

$where = "WHERE pharmacy_id = $pharmacyId AND product_name LIKE '$search'";
if ($status) $where .= " AND status = '$status'";

$result = $db->query("SELECT * FROM inventory $where ORDER BY product_name LIMIT $limit OFFSET $offset");
$items = [];
while ($row = $result->fetch_assoc()) $items[] = $row;
$total = $db->query("SELECT COUNT(*) as c FROM inventory $where")->fetch_assoc()['c'];
$db->close();
sendJSON(['items' => $items, 'total' => intval($total)]);
