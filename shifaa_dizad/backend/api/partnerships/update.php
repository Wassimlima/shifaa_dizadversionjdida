<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH' && $_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed', 405);
$id = intval($_GET['id'] ?? 0);
if (!$id) sendError('id required');
$body = getBody();
$status = $body['status'] ?? '';
if (!in_array($status, ['accepted','rejected','revoked'])) sendError('Invalid status');

$db = getDB();
$db->query("UPDATE partnership_requests SET status='$status' WHERE id=$id");
if ($db->affected_rows === 0) sendError('Request not found', 404);
if ($status === 'revoked') {
    $pr = $db->query("SELECT rep_id, pharmacy_id FROM partnership_requests WHERE id=$id")->fetch_assoc();
    if ($pr) $db->query("UPDATE resupply_requests SET status='rejected' WHERE rep_id={$pr['rep_id']} AND pharmacy_id={$pr['pharmacy_id']} AND status='pending'");
}
$req = $db->query("SELECT * FROM partnership_requests WHERE id=$id")->fetch_assoc();
$db->close();
sendJSON($req);
