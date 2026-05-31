<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed', 405);
$body = getBody();
if (empty($body['repId']) || empty($body['pharmacyId'])) sendError('repId and pharmacyId required');

$db = getDB();
$existing = $db->query("SELECT id FROM partnership_requests WHERE rep_id={$body['repId']} AND pharmacy_id={$body['pharmacyId']} AND status IN ('pending','accepted')")->fetch_assoc();
if ($existing) sendError('Partnership request already exists');

$stmt = $db->prepare("INSERT INTO partnership_requests (rep_id,pharmacy_id,status,message) VALUES (?,?,'pending',?)");
$msg = $body['message'] ?? null;
$stmt->bind_param('iis', $body['repId'], $body['pharmacyId'], $msg);
$stmt->execute();
$id = $db->insert_id;
$req = $db->query("SELECT * FROM partnership_requests WHERE id=$id")->fetch_assoc();
$db->close();
sendJSON($req, 201);
