<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH' && $_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed', 405);
$id = intval($_GET['id'] ?? 0);
if (!$id) sendError('id required');
$body = getBody();
$status = $db_status = $body['status'] ?? '';
if (!in_array($status, ['confirmed','sent','rejected'])) sendError('Invalid status');

$db = getDB();
$db->query("UPDATE resupply_requests SET status='$status' WHERE id=$id");
if ($db->affected_rows === 0) sendError('Request not found', 404);
$req = $db->query("SELECT * FROM resupply_requests WHERE id=$id")->fetch_assoc();
$db->close();
sendJSON($req);
