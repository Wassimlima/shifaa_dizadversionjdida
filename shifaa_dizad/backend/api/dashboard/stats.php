<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

$pharmacyId = intval($_GET['pharmacyId'] ?? 0);
if (!$pharmacyId) sendError('pharmacyId required');

$db = getDB();
$total = $db->query("SELECT COUNT(*) as c FROM inventory WHERE pharmacy_id=$pharmacyId")->fetch_assoc()['c'];
$lowStock = $db->query("SELECT COUNT(*) as c FROM inventory WHERE pharmacy_id=$pharmacyId AND status='limited'")->fetch_assoc()['c'];
$outOfStock = $db->query("SELECT COUNT(*) as c FROM inventory WHERE pharmacy_id=$pharmacyId AND status='unavailable'")->fetch_assoc()['c'];
$todayRes = $db->query("SELECT COUNT(*) as c FROM reservations WHERE pharmacy_id=$pharmacyId AND DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
$available = $total > 0 ? round((($total - $outOfStock) / $total) * 100) : 0;
$db->close();
sendJSON(['totalProducts' => intval($total), 'lowStockCount' => intval($lowStock), 'todayReservations' => intval($todayRes), 'availabilityRate' => $available]);
