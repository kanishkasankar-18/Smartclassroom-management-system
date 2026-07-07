<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');

$pageTitle = 'Manage Teachers';
$success = $error = '';

// ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $pass  = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $error = 'Email already exists.';
    } else {
        $pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?,?,?,'teacher',?)")
            ->execute([$name, $email, $pass, $phone]);
        $success = "Teacher '$name' added successfully.";
    }
}

// DELETE
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'teacher'")->execute([$_GET['delete']]);
    $success = 'Teacher removed.';
}

$teachers = $pdo->query("SELECT u.id, u.name, u.email, u.phone, u.created_at,
    (SELECT COUNT(*) FROM classes c WHERE c.teacher_id = u.id) as class_count
    FROM users u WHERE u.role = 'teacher' ORDER BY u.name")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="section-title"><i class="bi bi-person-workspace me-2 text-primary"></i>Manage Teachers</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
        <i class="bi bi-plus-circle me-2"></i>Add Teacher
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
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Classes</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $i => $t): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle" style="width:34px;height:34px;background:#198754">
                                    <?= strtoupper(substr($t['name'], 0, 1)) ?>
                                </div>
                                <strong><?= htmlspecialchars($t['name']) ?></strong>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($t['email']) ?></td>
                        <td><?= htmlspecialchars($t['phone'] ?? '-') ?></td>
                        <td><span class="badge bg-primary"><?= $t['class_count'] ?> class(es)</span></td>
                        <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                        <td>
                            <a href="manage_teachers.php?delete=<?= $t['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Remove this teacher?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($teachers)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No teachers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-3">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New Teacher</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="name" required placeholder="Prof. John Doe">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required placeholder="teacher@school.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" placeholder="9876543210">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="text" class="form-control" name="password" required value="password" placeholder="Set initial password">
                        <div class="form-text">Share this with the teacher for first login.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus me-1"></i>Add Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
