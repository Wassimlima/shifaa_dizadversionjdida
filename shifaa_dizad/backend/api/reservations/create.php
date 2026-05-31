<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed', 405);
$body = getBody();
$required = ['medicineId','pharmacyId','medicineName','pharmacyName','patientName','patientPhone'];
foreach ($required as $f) { if (empty($body[$f])) sendError("$f is required"); }

$db = getDB();
$stmt = $db->prepare("INSERT INTO reservations (medicine_id,pharmacy_id,medicine_name,pharmacy_name,patient_name,patient_phone,quantity,notes) VALUES (?,?,?,?,?,?,?,?)");
$qty = intval($body['quantity'] ?? 1);
$notes = $body['notes'] ?? null;
$stmt->bind_param('iissssss', $body['medicineId'],$body['pharmacyId'],$body['medicineName'],$body['pharmacyName'],$body['patientName'],$body['patientPhone'],$qty,$notes);
// Fix binding - qty is int
$stmt = $db->prepare("INSERT INTO reservations (medicine_id,pharmacy_id,medicine_name,pharmacy_name,patient_name,patient_phone,quantity,notes) VALUES (?,?,?,?,?,?,?,?)");
$stmt->bind_param('iissssis', $body['medicineId'],$body['pharmacyId'],$body['medicineName'],$body['pharmacyName'],$body['patientName'],$body['patientPhone'],$qty,$notes);
$stmt->execute();
$id = $db->insert_id;
$res = $db->query("SELECT * FROM reservations WHERE id=$id")->fetch_assoc();
$db->close();
sendJSON($res, 201);
