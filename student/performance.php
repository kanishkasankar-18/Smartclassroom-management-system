<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('student');

$pageTitle = 'My Performance';
$studentId = $_SESSION['user_id'];
$classId   = $_SESSION['class_id'];

$marks = [];
if ($classId) {
    $stmt = $pdo->prepare("SELECT * FROM marks WHERE student_id = ? AND class_id = ? ORDER BY subject, exam_type");
    $stmt->execute([$studentId, $classId]);
    $marks = $stmt->fetchAll();
}

// Group by subject
$bySubject = [];
foreach ($marks as $m) {
    $bySubject[$m['subject']][] = $m;
}

// Overall average
$overall = count($marks) > 0 ? round(array_sum(array_column($marks, 'marks_obtained')) / count($marks), 1) : null;

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="section-title"><i class="bi bi-graph-up me-2 text-success"></i>My Performance</h4>
    <?php if ($overall): ?>
    <div class="text-end">
        <span class="fw-bold text-muted small">Overall Average</span><br>
        <?php
            $grade = $overall >= 90 ? 'A+' : ($overall >= 75 ? 'A' : ($overall >= 60 ? 'B' : ($overall >= 50 ? 'C' : 'F')));
            $gc    = str_starts_with($grade,'A') ? 'grade-A' : ($grade === 'B' ? 'grade-B' : ($grade === 'C' ? 'grade-C' : 'grade-F'));
        ?>
        <span class="fs-3 fw-bold text-primary"><?= $overall ?>%</span>
        <span class="<?= $gc ?> ms-2"><?= $grade ?></span>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($marks)): ?>

<!-- Subject-wise Performance -->
<?php foreach ($bySubject as $subject => $subjectMarks): 
    $subAvg = round(array_sum(array_column($subjectMarks, 'marks_obtained')) / count($subjectMarks), 1);
    $subPct = round(($subAvg / $subjectMarks[0]['max_marks']) * 100);
    $subGrade = $subPct >= 90 ? 'A+' : ($subPct >= 75 ? 'A' : ($subPct >= 60 ? 'B' : ($subPct >= 50 ? 'C' : 'F')));
    $subGc    = str_starts_with($subGrade,'A') ? 'grade-A' : ($subGrade === 'B' ? 'grade-B' : ($subGrade === 'C' ? 'grade-C' : 'grade-F'));
?>
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-book me-2 text-primary"></i><?= htmlspecialchars($subject) ?></h6>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Avg: <strong><?= $subAvg ?></strong></span>
                <span class="<?= $subGc ?>"><?= $subGrade ?></span>
            </div>
        </div>
        <div class="progress mt-2" style="height:8px">
            <div class="progress-bar bg-<?= $subPct >= 75 ? 'success' : ($subPct >= 50 ? 'warning' : 'danger') ?>"
                 style="width:<?= $subPct ?>%"></div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Exam Type</th><th>Marks Obtained</th><th>Max Marks</th><th>Percentage</th><th>Grade</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($subjectMarks as $m): 
                        $pct = round(($m['marks_obtained'] / $m['max_marks']) * 100);
                        $g   = $pct >= 90 ? 'A+' : ($pct >= 75 ? 'A' : ($pct >= 60 ? 'B' : ($pct >= 50 ? 'C' : 'F')));
                        $gc  = str_starts_with($g,'A') ? 'grade-A' : ($g === 'B' ? 'grade-B' : ($g === 'C' ? 'grade-C' : 'grade-F'));
                    ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($m['exam_type']) ?></span></td>
                        <td><strong><?= $m['marks_obtained'] ?></strong></td>
                        <td><?= $m['max_marks'] ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px;max-width:80px">
                                    <div class="progress-bar bg-<?= $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') ?>"
                                         style="width:<?= $pct ?>%"></div>
                                </div>
                                <span><?= $pct ?>%</span>
                            </div>
                        </td>
                        <td><span class="<?= $gc ?>"><?= $g ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Summary Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-bar-graph me-2 text-warning"></i>Performance Summary</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr><th>Subject</th><th>Tests Taken</th><th>Average</th><th>Grade</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($bySubject as $subject => $subjectMarks): 
                        $avg   = round(array_sum(array_column($subjectMarks, 'marks_obtained')) / count($subjectMarks), 1);
                        $pct   = round(($avg / $subjectMarks[0]['max_marks']) * 100);
                        $grade = $pct >= 90 ? 'A+' : ($pct >= 75 ? 'A' : ($pct >= 60 ? 'B' : ($pct >= 50 ? 'C' : 'F')));
                        $gc    = str_starts_with($grade,'A') ? 'grade-A' : ($grade === 'B' ? 'grade-B' : ($grade === 'C' ? 'grade-C' : 'grade-F'));
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($subject) ?></strong></td>
                        <td><?= count($subjectMarks) ?></td>
                        <td><?= $avg ?> / <?= $subjectMarks[0]['max_marks'] ?></td>
                        <td><span class="<?= $gc ?>"><?= $grade ?></span></td>
                        <td><span class="badge bg-<?= $pct >= 50 ? 'success' : 'danger' ?>">
                            <?= $pct >= 50 ? 'Pass' : 'Fail' ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else: ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-bar-chart fs-1"></i>
    <p class="mt-2">No marks records found yet.</p>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
