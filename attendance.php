<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('teacher');

$pageTitle = 'Mark Attendance';
$teacherId = $_SESSION['user_id'];
$success = $error = '';

// Get teacher's classes
$classes = $pdo->prepare("SELECT id, name, section FROM classes WHERE teacher_id = ?");
$classes->execute([$teacherId]);
$classes = $classes->fetchAll();

$selectedClass = $_GET['class_id'] ?? null;
$selectedDate  = $_GET['date'] ?? date('Y-m-d');
$students = [];

if ($selectedClass) {
    $stmt = $pdo->prepare("SELECT u.id, u.name,
        (SELECT status FROM attendance a WHERE a.student_id = u.id AND a.class_id = ? AND a.date = ? LIMIT 1) AS today_status
        FROM users u
        JOIN student_classes sc ON sc.student_id = u.id
        WHERE sc.class_id = ? ORDER BY u.name");
    $stmt->execute([$selectedClass, $selectedDate, $selectedClass]);
    $students = $stmt->fetchAll();
}

// Save attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'save') {
    $classId = (int)$_POST['class_id'];
    $date    = $_POST['date'];
    $statuses = $_POST['status'] ?? [];

    foreach ($statuses as $studentId => $status) {
        $check = $pdo->prepare("SELECT id FROM attendance WHERE student_id=? AND class_id=? AND date=?");
        $check->execute([$studentId, $classId, $date]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE attendance SET status=? WHERE student_id=? AND class_id=? AND date=?")
                ->execute([$status, $studentId, $classId, $date]);
        } else {
            $pdo->prepare("INSERT INTO attendance (student_id, class_id, date, status, marked_by) VALUES (?,?,?,?,?)")
                ->execute([$studentId, $classId, $date, $status, $teacherId]);
        }
    }
    $success = "Attendance saved for " . date('d M Y', strtotime($date));
    header("Location: attendance.php?class_id=$classId&date=$date&saved=1");
    exit;
}

if (isset($_GET['saved'])) $success = "Attendance saved successfully!";

// Attendance history
$history = [];
if ($selectedClass) {
    $histStmt = $pdo->prepare("
        SELECT a.date,
            SUM(a.status='present') AS present,
            SUM(a.status='absent') AS absent,
            SUM(a.status='late') AS late,
            COUNT(*) AS total
        FROM attendance a WHERE a.class_id = ? GROUP BY a.date ORDER BY a.date DESC LIMIT 10");
    $histStmt->execute([$selectedClass]);
    $history = $histStmt->fetchAll();
}

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="section-title"><i class="bi bi-check2-square me-2 text-primary"></i>Mark Attendance</h4>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= $success ?></div>
<?php endif; ?>

<!-- Filter -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Select Class *</label>
                <select class="form-select" name="class_id" required>
                    <option value="">-- Select Class --</option>
                    <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $selectedClass == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?> (<?= $c['section'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date *</label>
                <input type="date" class="form-control" name="date" value="<?= $selectedDate ?>" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Load
                </button>
            </div>
            <?php if ($selectedClass && !empty($students)): ?>
            <div class="col-md-3">
                <button type="button" class="btn btn-outline-success w-100" onclick="markAll('present')">
                    <i class="bi bi-check-all me-1"></i>Mark All Present
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Student List for Attendance -->
<?php if ($selectedClass && !empty($students)): ?>
<form method="POST">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="class_id" value="<?= $selectedClass ?>">
    <input type="hidden" name="date" value="<?= $selectedDate ?>">

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-people me-2"></i>
                Students — <?= date('l, d M Y', strtotime($selectedDate)) ?>
            </h6>
            <span class="badge bg-primary"><?= count($students) ?> students</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead>
                        <tr><th>#</th><th>Student Name</th><th>Present</th><th>Absent</th><th>Late</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $i => $s): 
                            $status = $s['today_status'] ?? 'absent';
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle" style="width:30px;height:30px;font-size:0.7rem">
                                        <?= strtoupper(substr($s['name'], 0, 1)) ?>
                                    </div>
                                    <?= htmlspecialchars($s['name']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input attendance-radio" type="radio"
                                           name="status[<?= $s['id'] ?>]"
                                           value="present"
                                           <?= $status === 'present' ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input attendance-radio" type="radio"
                                           name="status[<?= $s['id'] ?>]"
                                           value="absent"
                                           <?= $status === 'absent' ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input attendance-radio" type="radio"
                                           name="status[<?= $s['id'] ?>]"
                                           value="late"
                                           <?= $status === 'late' ? 'checked' : '' ?>>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 p-3">
            <button type="submit" class="btn btn-success px-4">
                <i class="bi bi-save me-2"></i>Save Attendance
            </button>
        </div>
    </div>
</form>
<?php elseif ($selectedClass): ?>
<div class="text-center py-5 text-muted"><i class="bi bi-people fs-1"></i><p class="mt-2">No students in this class.</p></div>
<?php endif; ?>

<!-- Attendance History -->
<?php if (!empty($history)): ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-secondary"></i>Recent Attendance History</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Date</th><th>Present</th><th>Absent</th><th>Late</th><th>Rate</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $h): 
                        $pct = round(($h['present'] / $h['total']) * 100);
                    ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($h['date'])) ?></td>
                        <td><span class="attendance-present"><?= $h['present'] ?></span></td>
                        <td><span class="attendance-absent"><?= $h['absent'] ?></span></td>
                        <td><span class="attendance-late"><?= $h['late'] ?></span></td>
                        <td>
                            <div class="progress" style="height:6px;width:80px">
                                <div class="progress-bar bg-<?= $pct >= 75 ? 'success' : 'warning' ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function markAll(status) {
    document.querySelectorAll(`input[value="${status}"]`).forEach(r => r.checked = true);
}
</script>

<?php include '../includes/footer.php'; ?>
