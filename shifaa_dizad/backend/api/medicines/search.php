<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDB();
$q = isset($_GET['q']) ? '%' . $db->real_escape_string($_GET['q']) . '%' : '%';
$wilaya = isset($_GET['wilaya']) ? $db->real_escape_string($_GET['wilaya']) : '';
$type = isset($_GET['type']) ? $db->real_escape_string($_GET['type']) : '';
$availability = isset($_GET['availability']) ? $db->real_escape_string($_GET['availability']) : '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = min(20, intval($_GET['limit'] ?? 10));
$offset = ($page - 1) * $limit;

$where = "WHERE (m.name LIKE ? OR m.name_ar LIKE ? OR m.active_ingredient LIKE ?)";
$params = [$q, $q, $q];
$types = 'sss';

if ($wilaya) { $where .= " AND m.wilaya = ?"; $params[] = $wilaya; $types .= 's'; }
if ($type) { $where .= " AND m.type = ?"; $params[] = $type; $types .= 's'; }
if ($availability) { $where .= " AND m.availability = ?"; $params[] = $availability; $types .= 's'; }

$stmt = $db->prepare("SELECT m.*, p.name AS pharmacy_name, p.phone AS pharmacy_phone FROM medicines m LEFT JOIN pharmacies p ON m.pharmacy_id = p.id $where ORDER BY m.availability ASC, m.rating DESC LIMIT ? OFFSET ?");
$params[] = $limit; $params[] = $offset; $types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$medicines = [];
while ($row = $result->fetch_assoc()) $medicines[] = $row;

$countStmt = $db->prepare("SELECT COUNT(*) as total FROM medicines m $where");
array_pop($params); array_pop($params); $types = substr($types, 0, -2);
if (count($params) > 0) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$count = $countStmt->get_result()->fetch_assoc()['total'];

$db->close();
sendJSON(['medicines' => $medicines, 'total' => $count, 'page' => $page, 'limit' => $limit]);
