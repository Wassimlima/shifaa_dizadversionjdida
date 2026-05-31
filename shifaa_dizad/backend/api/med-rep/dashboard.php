<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

$repId = intval($_GET['repId'] ?? 0);
if (!$repId) sendError('repId required');

$db = getDB();
$rep = $db->query("SELECT * FROM med_reps WHERE id=$repId")->fetch_assoc();
if (!$rep) sendError('Rep not found', 404);

$productsResult = $db->query("SELECT * FROM rep_products WHERE rep_id=$repId");
$products = [];
while ($r = $productsResult->fetch_assoc()) $products[] = $r;

$alertsResult = $db->query("SELECT * FROM rep_alerts WHERE rep_id=$repId ORDER BY FIELD(severity,'high','medium','low'), created_at DESC");
$alerts = [];
while ($r = $alertsResult->fetch_assoc()) $alerts[] = $r;

$partnersResult = $db->query("SELECT pr.*, p.name as pharmacy_name, p.city, p.phone as pharmacy_phone FROM partnership_requests pr LEFT JOIN pharmacies p ON pr.pharmacy_id=p.id WHERE pr.rep_id=$repId ORDER BY pr.created_at DESC");
$partners = [];
while ($r = $partnersResult->fetch_assoc()) $partners[] = $r;

$acceptedCount = count(array_filter($partners, fn($p) => $p['status'] === 'accepted'));
$stats = ['totalProducts' => count($products), 'partnerPharmacies' => $acceptedCount, 'urgentAlerts' => count(array_filter($alerts, fn($a) => $a['severity'] === 'high')), 'pendingResupply' => 0];

$db->close();
sendJSON(['rep' => $rep, 'stats' => $stats, 'products' => $products, 'alerts' => $alerts, 'partnerPharmacies' => $partners]);
