<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Smart Classroom Management' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
</head>
<body>

<?php
$role = $_SESSION['role'] ?? '';
$name = $_SESSION['name'] ?? '';

if ($role === 'admin') {
    $menuItems = [
        ['url' => 'dashboard.php',          'icon' => 'bi-speedometer2',    'label' => 'Dashboard'],
        ['url' => 'manage_teachers.php',     'icon' => 'bi-person-workspace','label' => 'Manage Teachers'],
        ['url' => 'manage_students.php',     'icon' => 'bi-people',          'label' => 'Manage Students'],
        ['url' => 'manage_classes.php',      'icon' => 'bi-building',        'label' => 'Manage Classes'],
        ['url' => 'timetable.php',           'icon' => 'bi-calendar3',       'label' => 'Timetable'],
        ['url' => 'reports.php',             'icon' => 'bi-bar-chart-line',  'label' => 'Reports'],
    ];
    $baseFolder = 'admin';
} elseif ($role === 'teacher') {
    $menuItems = [
        ['url' => 'dashboard.php',      'icon' => 'bi-speedometer2',  'label' => 'Dashboard'],
        ['url' => 'attendance.php',     'icon' => 'bi-check2-square', 'label' => 'Attendance'],
        ['url' => 'assignments.php',    'icon' => 'bi-journal-text',  'label' => 'Assignments'],
        ['url' => 'marks.php',          'icon' => 'bi-award',         'label' => 'Enter Marks'],
        ['url' => 'announcements.php',  'icon' => 'bi-megaphone',     'label' => 'Announcements'],
    ];
    $baseFolder = 'teacher';
} else {
    $menuItems = [
        ['url' => 'dashboard.php',      'icon' => 'bi-speedometer2',  'label' => 'Dashboard'],
        ['url' => 'attendance.php',     'icon' => 'bi-calendar-check','label' => 'My Attendance'],
        ['url' => 'assignments.php',    'icon' => 'bi-journal-text',  'label' => 'Assignments'],
        ['url' => 'performance.php',    'icon' => 'bi-graph-up',      'label' => 'My Performance'],
        ['url' => 'timetable.php',      'icon' => 'bi-clock',         'label' => 'Timetable'],
    ];
    $baseFolder = 'student';
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-mortarboard-fill fs-3 me-2"></i>
            <span>SmartClass</span>
        </div>
        <hr class="border-secondary">
        <ul class="nav flex-column px-2 flex-grow-1">
            <?php foreach ($menuItems as $item): ?>
            <li class="nav-item">
                <a class="nav-link sidebar-link <?= $currentPage === $item['url'] ? 'active' : '' ?>"
                   href="<?= BASE_URL . $baseFolder . '/' . $item['url'] ?>">
                    <i class="<?= $item['icon'] ?> me-2"></i><?= $item['label'] ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="p-3">
            <a href="<?= BASE_URL ?>logout.php" class="btn btn-outline-light btn-sm w-100">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-area flex-grow-1">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg top-navbar px-4">
            <button class="btn btn-sm btn-outline-secondary me-3" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <span class="navbar-text fw-semibold text-muted">
                <?= $pageTitle ?? 'Dashboard' ?>
            </span>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="badge role-badge-<?= $role ?> px-3 py-2 text-capitalize"><?= $role ?></span>
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle"><?= strtoupper(substr($name, 0, 1)) ?></div>
                    <span class="fw-semibold text-dark small"><?= htmlspecialchars($name) ?></span>
                </div>
            </div>
        </nav>

        <!-- Page Content Starts Here -->
        <div class="main-content p-4">
