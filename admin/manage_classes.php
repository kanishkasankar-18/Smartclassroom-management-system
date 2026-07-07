<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');

$pageTitle = 'Manage Classes';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $name       = trim($_POST['name']);
    $section    = trim($_POST['section']);
    $teacher_id = (int)$_POST['teacher_id'] ?: null;
    $pdo->prepare("INSERT INTO classes (name, section, teacher_id) VALUES (?,?,?)")
        ->execute([$name, $section, $teacher_id]);
    $success = "Class '$name' created successfully.";
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM classes WHERE id = ?")->execute([$_GET['delete']]);
    $success = 'Class deleted.';
}

$classes = $pdo->query("SELECT c.id, c.name, c.section, c.created_at, u.name AS teacher_name,
    (SELECT COUNT(*) FROM student_classes sc WHERE sc.class_id = c.id) AS student_count
    FROM classes c LEFT JOIN users u ON u.id = c.teacher_id ORDER BY c.name")->fetchAll();

$teachers = $pdo->query("SELECT id, name FROM users WHERE role='teacher' ORDER BY name")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="section-title"><i class="bi bi-building me-2 text-info"></i>Manage Classes</h4>
    <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#addClassModal">
        <i class="bi bi-plus-circle me-2"></i>Create Class
    </button>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show"><?= $success ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-4">
    <?php foreach ($classes as $c): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-3 rounded-3 bg-primary bg-opacity-10">
                        <i class="bi bi-building text-primary fs-4"></i>
                    </div>
                    <a href="manage_classes.php?delete=<?= $c['id'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Delete this class?')">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($c['name']) ?></h5>
                <p class="text-muted small mb-2">Section: <strong><?= htmlspecialchars($c['section']) ?></strong></p>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-success"><i class="bi bi-person-workspace me-1"></i><?= htmlspecialchars($c['teacher_name'] ?? 'Unassigned') ?></span>
                    <span class="badge bg-primary"><i class="bi bi-people me-1"></i><?= $c['student_count'] ?> students</span>
                </div>
                <p class="text-muted small mt-2 mb-0">Created: <?= date('d M Y', strtotime($c['created_at'])) ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($classes)): ?>
    <div class="col-12 text-center py-5 text-muted">No classes found. Create one!</div>
    <?php endif; ?>
</div>

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-3">
            <div class="modal-header" style="background:#4cc9f0">
                <h5 class="modal-title text-white"><i class="bi bi-plus-circle me-2"></i>Create New Class</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Class Name *</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Computer Science">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Section *</label>
                        <input type="text" class="form-control" name="section" required placeholder="A, B, C...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign Teacher</label>
                        <select class="form-select" name="teacher_id">
                            <option value="">-- Select Teacher --</option>
                            <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background:#4cc9f0;color:#fff">Create Class</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
