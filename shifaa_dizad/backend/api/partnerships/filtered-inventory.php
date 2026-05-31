<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

$repId = intval($_GET['repId'] ?? 0);
$pharmacyId = intval($_GET['pharmacyId'] ?? 0);
if (!$repId || !$pharmacyId) sendError('repId and pharmacyId required');

$db = getDB();
$active = $db->query("SELECT id FROM partnership_requests WHERE rep_id=$repId AND pharmacy_id=$pharmacyId AND status='accepted'")->fetch_assoc();
if (!$active) { http_response_code(403); sendJSON(['error' => 'No active partnership']); }

$prods = $db->query("SELECT name FROM rep_products WHERE rep_id=$repId");
$names = [];
while ($r = $prods->fetch_assoc()) $names[] = "'" . $db->real_escape_string($r['name']) . "'";
if (empty($names)) { sendJSON(['items' => [], 'total' => 0, 'pharmacyId' => $pharmacyId]); }

$inClause = implode(',', $names);
$result = $db->query("SELECT * FROM inventory WHERE pharmacy_id=$pharmacyId AND product_name IN ($inClause)");
$items = [];
while ($r = $result->fetch_assoc()) $items[] = $r;
$db->close();
sendJSON(['items' => $items, 'total' => count($items), 'pharmacyId' => $pharmacyId]);
