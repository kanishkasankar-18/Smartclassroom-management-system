<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('teacher');

$pageTitle = 'Enter Marks';
$teacherId = $_SESSION['user_id'];
$success = $error = '';

$classes = $pdo->prepare("SELECT id, name, section FROM classes WHERE teacher_id = ?");
$classes->execute([$teacherId]);
$classes = $classes->fetchAll();

// Save marks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'save') {
    $classId   = (int)$_POST['class_id'];
    $subject   = trim($_POST['subject']);
    $examType  = $_POST['exam_type'];
    $maxMarks  = (float)$_POST['max_marks'];
    $marksData = $_POST['marks'] ?? [];

    foreach ($marksData as $studentId => $obtained) {
        if ($obtained === '') continue;
        // Check existing
        $check = $pdo->prepare("SELECT id FROM marks WHERE student_id=? AND class_id=? AND subject=? AND exam_type=?");
        $check->execute([$studentId, $classId, $subject, $examType]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE marks SET marks_obtained=?, max_marks=?, entered_by=? WHERE student_id=? AND class_id=? AND subject=? AND exam_type=?")
                ->execute([$obtained, $maxMarks, $teacherId, $studentId, $classId, $subject, $examType]);
        } else {
            $pdo->prepare("INSERT INTO marks (student_id, subject, class_id, exam_type, marks_obtained, max_marks, entered_by) VALUES (?,?,?,?,?,?,?)")
                ->execute([$studentId, $subject, $classId, $examType, $obtained, $maxMarks, $teacherId]);
        }
    }
    $success = "Marks saved successfully for $subject – $examType";
}

$selectedClass = $_GET['class_id'] ?? null;
$students = [];
if ($selectedClass) {
    $stmt = $pdo->prepare("SELECT u.id, u.name FROM users u JOIN student_classes sc ON sc.student_id = u.id WHERE sc.class_id = ? ORDER BY u.name");
    $stmt->execute([$selectedClass]);
    $students = $stmt->fetchAll();
}

// View existing marks for selected class
$existingMarks = [];
if ($selectedClass) {
    $mStmt = $pdo->prepare("SELECT m.*, u.name AS student_name FROM marks m JOIN users u ON u.id = m.student_id WHERE m.class_id = ? ORDER BY u.name, m.subject, m.exam_type");
    $mStmt->execute([$selectedClass]);
    $existingMarks = $mStmt->fetchAll();
}

$examTypes = ['Unit Test 1','Unit Test 2','Mid Term','Final Exam','Assignment'];

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="section-title"><i class="bi bi-award me-2 text-success"></i>Enter Marks</h4>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= $success ?></div>
<?php endif; ?>

<!-- Class Filter -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Select Class</label>
                <select class="form-select" name="class_id">
                    <option value="">-- Select --</option>
                    <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $selectedClass == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?> (<?= $c['section'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Load</button>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedClass && !empty($students)): ?>
<!-- Enter Marks Form -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h6 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-success"></i>Enter / Update Marks</h6>
    </div>
    <form method="POST">
        <div class="card-body">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="class_id" value="<?= $selectedClass ?>">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Subject *</label>
                    <input type="text" class="form-control" name="subject" required placeholder="e.g. Data Structures">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Exam Type *</label>
                    <select class="form-select" name="exam_type" required>
                        <?php foreach ($examTypes as $e): ?>
                        <option value="<?= $e ?>"><?= $e ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Maximum Marks *</label>
                    <input type="number" class="form-control" name="max_marks" value="100" min="1" max="1000" required>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr><th>#</th><th>Student Name</th><th>Marks Obtained</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $i => $s): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td style="width:200px">
                                <input type="number" class="form-control form-control-sm"
                                       name="marks[<?= $s['id'] ?>]"
                                       min="0" max="1000" step="0.5"
                                       placeholder="Enter marks">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 p-3">
            <button type="submit" class="btn btn-success px-4">
                <i class="bi bi-save me-2"></i>Save Marks
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Existing Marks Records -->
<?php if (!empty($existingMarks)): ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h6 class="fw-bold mb-0"><i class="bi bi-list-check me-2 text-primary"></i>Marks Records</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr><th>Student</th><th>Subject</th><th>Exam Type</th><th>Marks</th><th>Percentage</th><th>Grade</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($existingMarks as $m): 
                        $pct = round(($m['marks_obtained'] / $m['max_marks']) * 100);
                        if ($pct >= 90) { $grade = 'A+'; $gc = 'grade-A'; }
                        elseif ($pct >= 75) { $grade = 'A'; $gc = 'grade-A'; }
                        elseif ($pct >= 60) { $grade = 'B'; $gc = 'grade-B'; }
                        elseif ($pct >= 50) { $grade = 'C'; $gc = 'grade-C'; }
                        else { $grade = 'F'; $gc = 'grade-F'; }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($m['student_name']) ?></td>
                        <td><?= htmlspecialchars($m['subject']) ?></td>
                        <td><span class="badge bg-secondary"><?= $m['exam_type'] ?></span></td>
                        <td><strong><?= $m['marks_obtained'] ?> / <?= $m['max_marks'] ?></strong></td>
                        <td><?= $pct ?>%</td>
                        <td><span class="<?= $gc ?>"><?= $grade ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
