<?php
require_once __DIR__ . '/../../utils/cors.php';
require_once __DIR__ . '/../../utils/response.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendError('Method not allowed', 405);

if (!isset($_FILES['prescription']) || $_FILES['prescription']['error'] !== UPLOAD_ERR_OK) {
    sendError('No file uploaded or upload error');
}

$file = $_FILES['prescription'];
$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
if (!in_array($file['type'], $allowed)) sendError('Invalid file type. Use JPG, PNG, or WebP.');
if ($file['size'] > 5 * 1024 * 1024) sendError('File too large. Max 5MB.');

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('rx_', true) . '.' . $ext;
$uploadDir = __DIR__ . '/../../uploads/';
$uploadPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) sendError('Upload failed');

$imageUrl = '/shifaa-dizad/backend/uploads/' . $filename;
$patientName = $_POST['patientName'] ?? 'مجهول';
$notes = $_POST['notes'] ?? null;

$db = getDB();
$stmt = $db->prepare("INSERT INTO prescriptions (image_url, patient_name, notes, status) VALUES (?,?,'pending',?)");
$stmt->bind_param('sss', $imageUrl, $patientName, $notes);
$stmt->execute();
$id = $db->insert_id;
$rx = $db->query("SELECT * FROM prescriptions WHERE id=$id")->fetch_assoc();
$db->close();
sendJSON($rx, 201);
