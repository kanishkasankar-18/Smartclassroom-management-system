<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('student');

$pageTitle = 'My Timetable';
$studentId = $_SESSION['user_id'];
$classId   = $_SESSION['class_id'];

$timetable = [];
if ($classId) {
    $stmt = $pdo->prepare("SELECT t.*, u.name AS teacher_name
        FROM timetable t LEFT JOIN users u ON u.id = t.teacher_id
        WHERE t.class_id = ?
        ORDER BY FIELD(t.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), t.start_time");
    $stmt->execute([$classId]);
    $timetable = $stmt->fetchAll();
}

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$byDay = [];
foreach ($timetable as $t) {
    $byDay[$t['day']][] = $t;
}

$today = date('l');

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="section-title"><i class="bi bi-clock me-2 text-warning"></i>My Class Timetable</h4>
    <span class="badge bg-warning text-dark fs-6"><?= $today ?></span>
</div>

<?php if (!empty($timetable)): ?>

<!-- Today's Classes Highlight -->
<?php if (!empty($byDay[$today])): ?>
<div class="card border-0 shadow-sm rounded-3 mb-4" style="border-left:4px solid #ffc107 !important">
    <div class="card-header bg-warning bg-opacity-10 border-0 pt-3 px-4">
        <h6 class="fw-bold mb-0"><i class="bi bi-sun me-2 text-warning"></i>Today's Classes — <?= $today ?></h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($byDay[$today] as $t): ?>
            <div class="col-md-4">
                <div class="card border-0 bg-warning bg-opacity-10 rounded-3 p-3">
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($t['subject']) ?></h6>
                    <p class="text-muted mb-1 small">
                        <i class="bi bi-clock me-1"></i>
                        <?= date('h:i A', strtotime($t['start_time'])) ?> – <?= date('h:i A', strtotime($t['end_time'])) ?>
                    </p>
                    <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($t['teacher_name'] ?? 'TBA') ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>No classes scheduled for today (<?= $today ?>).
</div>
<?php endif; ?>

<!-- Full Timetable -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h6 class="fw-bold mb-0"><i class="bi bi-calendar3 me-2 text-primary"></i>Full Weekly Timetable</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr><th>Day</th><th>Subject</th><th>Time</th><th>Teacher</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($days as $day): ?>
                    <?php if (!empty($byDay[$day])): ?>
                        <?php foreach ($byDay[$day] as $i => $t): ?>
                        <tr class="<?= $day === $today ? 'table-warning' : '' ?>">
                            <?php if ($i === 0): ?>
                            <td rowspan="<?= count($byDay[$day]) ?>">
                                <span class="badge bg-<?= $day === $today ? 'warning text-dark' : 'primary' ?> fs-7">
                                    <?= $day ?> <?= $day === $today ? '(Today)' : '' ?>
                                </span>
                            </td>
                            <?php endif; ?>
                            <td><strong><?= htmlspecialchars($t['subject']) ?></strong></td>
                            <td>
                                <i class="bi bi-clock me-1 text-muted"></i>
                                <?= date('h:i A', strtotime($t['start_time'])) ?> – <?= date('h:i A', strtotime($t['end_time'])) ?>
                            </td>
                            <td><?= htmlspecialchars($t['teacher_name'] ?? 'TBA') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td><span class="badge bg-light text-dark"><?= $day ?></span></td>
                            <td colspan="3" class="text-muted"><em>No classes</em></td>
                        </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else: ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-calendar-x fs-1"></i>
    <p class="mt-2">No timetable available for your class yet.</p>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
