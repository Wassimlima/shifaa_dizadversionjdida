<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed', 405);
$body = getBody();
if (empty($body['pharmacyId']) || empty($body['productName']) || !isset($body['quantity']) || empty($body['category'])) sendError('Missing required fields');

$qty = intval($body['quantity']);
$status = $qty > 10 ? 'available' : ($qty > 0 ? 'limited' : 'unavailable');
$price = $body['price'] ?? null;

$db = getDB();
$stmt = $db->prepare("INSERT INTO inventory (pharmacy_id,product_name,quantity,status,price,category) VALUES (?,?,?,?,?,?)");
$stmt->bind_param('isisds', $body['pharmacyId'],$body['productName'],$qty,$status,$price,$body['category']);
$stmt->execute();
$id = $db->insert_id;
$item = $db->query("SELECT * FROM inventory WHERE id=$id")->fetch_assoc();
$db->close();
sendJSON($item, 201);
