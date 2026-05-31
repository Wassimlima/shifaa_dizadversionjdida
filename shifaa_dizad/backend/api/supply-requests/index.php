<?php
require_once '../../utils/cors.php';
require_once '../../utils/response.php';
require_once '../../config/database.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDbConnection();

    if ($method === 'GET') {
        $pharmacyId = $_GET['pharmacy_id'] ?? null;
        $repId      = $_GET['rep_id']      ?? null;
        $status     = $_GET['status']      ?? '';
        $urgency    = $_GET['urgency']     ?? '';
        $limit      = min((int)($_GET['limit'] ?? 20), 100);

        $where  = [];
        $params = [];

        if ($pharmacyId) {
            $where[] = 'sr.pharmacy_id = :pharmacy_id';
            $params[':pharmacy_id'] = (int)$pharmacyId;
        }
        if ($repId) {
            $where[] = '(sr.target_rep_id = :rep_id OR sr.assigned_rep_id = :rep_id2)';
            $params[':rep_id']  = (int)$repId;
            $params[':rep_id2'] = (int)$repId;
        }
        if ($status) {
            $where[] = 'sr.status = :status';
            $params[':status'] = $status;
        }
        if ($urgency) {
            $where[] = 'sr.urgency = :urgency';
            $params[':urgency'] = $urgency;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $pdo->prepare("
            SELECT
                sr.*,
                p.name AS pharmacy_name,
                p.wilaya AS pharmacy_wilaya,
                p.phone AS pharmacy_phone
            FROM supply_requests sr
            LEFT JOIN pharmacies p ON p.id = sr.pharmacy_id
            $whereClause
            ORDER BY
                FIELD(sr.urgency,'critical','high','medium','low'),
                sr.created_at DESC
            LIMIT :limit
        ");

        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendSuccess([
            'requests' => $requests,
            'total'    => count($requests)
        ]);

    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $required = ['pharmacy_id', 'product_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                sendError("حقل $field مطلوب", 400);
                return;
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO supply_requests
                (pharmacy_id, pharmacy_user_id, product_name, product_type,
                 requested_quantity, urgency, notes, target_rep_id, status)
            VALUES
                (:pharmacy_id, :user_id, :product, :type,
                 :qty, :urgency, :notes, :rep_id, 'open')
        ");

        $stmt->execute([
            ':pharmacy_id' => (int)$data['pharmacy_id'],
            ':user_id'     => $data['pharmacy_user_id'] ?? null,
            ':product'     => $data['product_name'],
            ':type'        => $data['product_type'] ?? 'medicine',
            ':qty'         => (int)($data['requested_quantity'] ?? 1),
            ':urgency'     => $data['urgency'] ?? 'medium',
            ':notes'       => $data['notes'] ?? null,
            ':rep_id'      => $data['target_rep_id'] ?? null,
        ]);

        $newId = (int)$pdo->lastInsertId();

        sendSuccess([
            'id'      => $newId,
            'message' => 'تم إرسال طلب التوريد بنجاح'
        ], 201);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id   = $_GET['id'] ?? null;

        if (!$id) {
            sendError('معرف الطلب مطلوب', 400);
            return;
        }

        $validStatuses = ['open','assigned','accepted','rejected','forwarded','fulfilled','cancelled'];
        $newStatus = $data['status'] ?? null;

        if ($newStatus && !in_array($newStatus, $validStatuses)) {
            sendError('حالة غير صحيحة', 400);
            return;
        }

        $sets = [];
        $params = [':id' => (int)$id];

        if ($newStatus) {
            $sets[] = 'status = :status';
            $params[':status'] = $newStatus;
        }
        if (!empty($data['assigned_rep_id'])) {
            $sets[] = 'assigned_rep_id = :rep_id';
            $sets[] = 'assigned_at = NOW()';
            $params[':rep_id'] = (int)$data['assigned_rep_id'];
        }
        if ($newStatus === 'fulfilled') {
            $sets[] = 'fulfilled_at = NOW()';
        }

        if (empty($sets)) {
            sendError('لا يوجد بيانات للتحديث', 400);
            return;
        }

        $stmt = $pdo->prepare("UPDATE supply_requests SET " . implode(', ', $sets) . " WHERE id = :id");
        $stmt->execute($params);

        sendSuccess(['message' => 'تم تحديث الطلب بنجاح']);

    } else {
        sendError('Method not allowed', 405);
    }

} catch (PDOException $e) {
    sendError('خطأ في قاعدة البيانات: ' . $e->getMessage(), 500);
}
