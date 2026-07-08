<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('student');

$pageTitle = 'My Attendance';
$studentId = $_SESSION['user_id'];
$classId   = $_SESSION['class_id'];

$records = [];
if ($classId) {
    $stmt = $pdo->prepare("SELECT a.date, a.status, c.name AS class_name
        FROM attendance a JOIN classes c ON c.id = a.class_id
        WHERE a.student_id = ? AND a.class_id = ?
        ORDER BY a.date DESC");
    $stmt->execute([$studentId, $classId]);
    $records = $stmt->fetchAll();
}

// Summary
$present = count(array_filter($records, fn($r) => $r['status'] === 'present'));
$absent  = count(array_filter($records, fn($r) => $r['status'] === 'absent'));
$late    = count(array_filter($records, fn($r) => $r['status'] === 'late'));
$total   = count($records);
$pct     = $total > 0 ? round(($present / $total) * 100) : 0;

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="section-title"><i class="bi bi-calendar-check me-2 text-primary"></i>My Attendance</h4>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 text-center p-3">
            <h2 class="fw-bold text-success"><?= $present ?></h2>
            <p class="text-muted mb-0 small">Present</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 text-center p-3">
            <h2 class="fw-bold text-danger"><?= $absent ?></h2>
            <p class="text-muted mb-0 small">Absent</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 text-center p-3">
            <h2 class="fw-bold text-warning"><?= $late ?></h2>
            <p class="text-muted mb-0 small">Late</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 text-center p-3">
            <h2 class="fw-bold text-<?= $pct >= 75 ? 'success' : 'danger' ?>"><?= $pct ?>%</h2>
            <p class="text-muted mb-0 small">Percentage</p>
        </div>
    </div>
</div>

<!-- Attendance Progress -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-semibold">Attendance Progress</span>
            <span class="fw-bold text-<?= $pct >= 75 ? 'success' : 'danger' ?>"><?= $pct ?>%</span>
        </div>
        <div class="progress mb-2" style="height:20px">
            <div class="progress-bar bg-<?= $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') ?>"
                 style="width:<?= $pct ?>%"><?= $pct ?>%</div>
        </div>
        <p class="text-muted small mb-0">
            <?= $total ?> total classes | Required: 75% | Your attendance: <?= $pct ?>%
            <?= $pct >= 75 ? '✅ Good standing' : '⚠️ Below required — attend more classes!' ?>
        </p>
    </div>
</div>

<!-- Records Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h6 class="fw-bold mb-0"><i class="bi bi-table me-2 text-primary"></i>Attendance Records</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr><th>#</th><th>Date</th><th>Day</th><th>Class</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $i => $r): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= date('d M Y', strtotime($r['date'])) ?></td>
                        <td><?= date('l', strtotime($r['date'])) ?></td>
                        <td><?= htmlspecialchars($r['class_name']) ?></td>
                        <td>
                            <?php if ($r['status'] === 'present'): ?>
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Present</span>
                            <?php elseif ($r['status'] === 'absent'): ?>
                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Absent</span>
                            <?php else: ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Late</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($records)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No attendance records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
