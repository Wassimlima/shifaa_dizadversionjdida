<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDB();
$repId = isset($_GET['repId']) ? intval($_GET['repId']) : 0;
$pharmacyId = isset($_GET['pharmacyId']) ? intval($_GET['pharmacyId']) : 0;

$where = 'WHERE 1=1';
if ($repId) $where .= " AND rr.rep_id=$repId";
if ($pharmacyId) $where .= " AND rr.pharmacy_id=$pharmacyId";

$result = $db->query("SELECT rr.*, p.name as pharmacy_name, r.name as rep_name, r.phone as rep_phone FROM resupply_requests rr LEFT JOIN pharmacies p ON rr.pharmacy_id=p.id LEFT JOIN med_reps r ON rr.rep_id=r.id $where ORDER BY rr.created_at DESC");
$list = [];
while ($row = $result->fetch_assoc()) $list[] = $row;
$db->close();
sendJSON($list);
