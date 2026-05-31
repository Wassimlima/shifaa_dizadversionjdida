<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') sendError('Method not allowed', 405);
$id = intval($_GET['id'] ?? 0);
if (!$id) sendError('id required');

$db = getDB();
$db->query("DELETE FROM inventory WHERE id=$id");
if ($db->affected_rows === 0) sendError('Item not found', 404);
$db->close();
sendJSON(['success' => true]);
