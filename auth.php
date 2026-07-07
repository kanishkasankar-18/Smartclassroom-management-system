<?php
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header('Location: ' . BASE_URL . 'index.php?error=unauthorized');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getDashboardUrl($role) {
    switch ($role) {
        case 'admin':   return BASE_URL . 'admin/dashboard.php';
        case 'teacher': return BASE_URL . 'teacher/dashboard.php';
        case 'student': return BASE_URL . 'student/dashboard.php';
        default:        return BASE_URL . 'index.php';
    }
}
?>
