<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed', 405);
$body = getBody();
if (empty($body['repId']) || empty($body['pharmacyId']) || empty($body['productName'])) sendError('Missing required fields');

$db = getDB();
$active = $db->query("SELECT id FROM partnership_requests WHERE rep_id={$body['repId']} AND pharmacy_id={$body['pharmacyId']} AND status='accepted'")->fetch_assoc();
if (!$active) { http_response_code(403); sendJSON(['error' => 'No active partnership']); }

$stmt = $db->prepare("INSERT INTO resupply_requests (rep_id,pharmacy_id,product_name,requested_quantity,message,status) VALUES (?,?,?,?,'pending','pending')");
$qty = intval($body['requestedQuantity'] ?? 1);
$msg = $body['message'] ?? null;
$stmt->bind_param('iisi', $body['repId'], $body['pharmacyId'], $body['productName'], $qty);
$stmt->execute();
$id = $db->insert_id;
// Update message separately if provided
if ($msg) $db->query("UPDATE resupply_requests SET message='" . $db->real_escape_string($msg) . "' WHERE id=$id");
$req = $db->query("SELECT * FROM resupply_requests WHERE id=$id")->fetch_assoc();
$db->close();
sendJSON($req, 201);
