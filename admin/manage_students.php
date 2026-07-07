<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');

$pageTitle = 'Manage Students';
$success = $error = '';

// ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $class_id = (int)$_POST['class_id'];
    $pass     = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $error = 'Email already exists.';
    } else {
        $pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?,?,?,'student',?)")
            ->execute([$name, $email, $pass, $phone]);
        $newId = $pdo->lastInsertId();
        if ($class_id) {
            $pdo->prepare("INSERT INTO student_classes (student_id, class_id) VALUES (?,?)")
                ->execute([$newId, $class_id]);
        }
        $success = "Student '$name' added successfully.";
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'student'")->execute([$_GET['delete']]);
    $success = 'Student removed.';
}

$students = $pdo->query("SELECT u.id, u.name, u.email, u.phone, u.created_at,
    c.name AS class_name
    FROM users u
    LEFT JOIN student_classes sc ON sc.student_id = u.id
    LEFT JOIN classes c ON c.id = sc.class_id
    WHERE u.role = 'student' ORDER BY u.name")->fetchAll();

$classes = $pdo->query("SELECT id, name, section FROM classes")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="section-title"><i class="bi bi-people me-2 text-secondary"></i>Manage Students</h4>
    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="bi bi-plus-circle me-2"></i>Add Student
    </button>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i><?= $success ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-x-circle me-2"></i><?= $error ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Class</th><th>Joined</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $i => $s): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle" style="width:34px;height:34px;font-size:0.75rem">
                                    <?= strtoupper(substr($s['name'], 0, 1)) ?>
                                </div>
                                <strong><?= htmlspecialchars($s['name']) ?></strong>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= htmlspecialchars($s['phone'] ?? '-') ?></td>
                        <td><?= $s['class_name'] ? htmlspecialchars($s['class_name']) : '<span class="text-muted">Unassigned</span>' ?></td>
                        <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                        <td>
                            <a href="manage_students.php?delete=<?= $s['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Remove this student?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($students)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No students found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-3">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New Student</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="name" required placeholder="Student Name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required placeholder="student@student.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" placeholder="9876543210">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign Class</label>
                        <select class="form-select" name="class_id">
                            <option value="">-- Select Class --</option>
                            <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> - <?= htmlspecialchars($c['section']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="text" class="form-control" name="password" required value="password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary"><i class="bi bi-plus me-1"></i>Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
