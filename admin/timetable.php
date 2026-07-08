<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');

$pageTitle = 'Timetable Management';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $pdo->prepare("INSERT INTO timetable (class_id, subject, teacher_id, day, start_time, end_time) VALUES (?,?,?,?,?,?)")
        ->execute([$_POST['class_id'], $_POST['subject'], $_POST['teacher_id'], $_POST['day'], $_POST['start_time'], $_POST['end_time']]);
    $success = 'Timetable entry added.';
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM timetable WHERE id = ?")->execute([$_GET['delete']]);
    $success = 'Entry deleted.';
}

$selectedClass = $_GET['class_id'] ?? null;
$classes  = $pdo->query("SELECT id, name, section FROM classes ORDER BY name")->fetchAll();
$teachers = $pdo->query("SELECT id, name FROM users WHERE role='teacher' ORDER BY name")->fetchAll();

$timetable = [];
if ($selectedClass) {
    $stmt = $pdo->prepare("SELECT t.*, u.name AS teacher_name FROM timetable t
        LEFT JOIN users u ON u.id = t.teacher_id
        WHERE t.class_id = ? ORDER BY FIELD(t.day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), t.start_time");
    $stmt->execute([$selectedClass]);
    $timetable = $stmt->fetchAll();
}

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="section-title"><i class="bi bi-calendar3 me-2 text-warning"></i>Timetable Management</h4>
    <button class="btn btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#addTimetableModal">
        <i class="bi bi-plus-circle me-2"></i>Add Entry
    </button>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<!-- Filter by class -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Filter by Class</label>
                <select class="form-select" name="class_id">
                    <option value="">-- All Classes --</option>
                    <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $selectedClass == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?> (<?= $c['section'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedClass && !empty($timetable)): ?>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr><th>Day</th><th>Subject</th><th>Time</th><th>Teacher</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($timetable as $t): ?>
                    <tr>
                        <td><span class="badge bg-primary"><?= $t['day'] ?></span></td>
                        <td><strong><?= htmlspecialchars($t['subject']) ?></strong></td>
                        <td><i class="bi bi-clock me-1 text-muted"></i><?= date('h:i A', strtotime($t['start_time'])) ?> – <?= date('h:i A', strtotime($t['end_time'])) ?></td>
                        <td><?= htmlspecialchars($t['teacher_name'] ?? '-') ?></td>
                        <td>
                            <a href="timetable.php?delete=<?= $t['id'] ?>&class_id=<?= $selectedClass ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete entry?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php elseif ($selectedClass): ?>
<div class="text-center py-5 text-muted"><i class="bi bi-calendar-x fs-1"></i><p class="mt-2">No timetable entries for this class.</p></div>
<?php else: ?>
<div class="text-center py-5 text-muted"><i class="bi bi-calendar3 fs-1"></i><p class="mt-2">Select a class to view its timetable.</p></div>
<?php endif; ?>

<!-- Add Modal -->
<div class="modal fade" id="addTimetableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-3">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-plus me-2"></i>Add Timetable Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Class *</label>
                        <select class="form-select" name="class_id" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= $c['section'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject *</label>
                        <input type="text" class="form-control" name="subject" required placeholder="e.g. Data Structures">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teacher *</label>
                        <select class="form-select" name="teacher_id" required>
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Day *</label>
                        <select class="form-select" name="day" required>
                            <?php foreach ($days as $d): ?>
                            <option value="<?= $d ?>"><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Start Time *</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">End Time *</label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark">Add Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
