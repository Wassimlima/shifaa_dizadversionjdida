<?php
require_once __DIR__ . '/config.php';

function loginUser($email, $password) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, email, password, role, full_name, subscription_status FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['subscription_status'] = $user['subscription_status'];
            $stmt->close();
            $conn->close();
            return true;
        }
    }
    $stmt->close();
    $conn->close();
    return false;
}

function logoutUser() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

function getCurrentUser() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) return null;
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'],
        'full_name' => $_SESSION['full_name'] ?? '',
        'subscription_status' => $_SESSION['subscription_status'] ?? 'pending'
    ];
}

function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['user_id']);
}

function redirectToDashboard() {
    if (!isLoggedIn()) return;
    $role = $_SESSION['role'];
    switch ($role) {
        case 'admin':
            header("Location: admin/admin_dashboard.php");
            break;
        case 'pharmacist':
            header("Location: dashboards/pharmacy_dashboard.php");
            break;
        case 'med_rep':
            header("Location: dashboards/medrep_dashboard.php");
            break;
        case 'laboratory':
            header("Location: dashboards/laboratory_dashboard.php");
            break;
        case 'medical_services':
            header("Location: dashboards/medical_services_dashboard.php");
            break;
        default:
            header("Location: index.php");
    }
    exit();
}
?>