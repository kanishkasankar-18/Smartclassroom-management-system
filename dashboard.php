<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('teacher');

$pageTitle = 'Teacher Dashboard';
$teacherId = $_SESSION['user_id'];

// My classes
$myClasses = $pdo->prepare("SELECT c.id, c.name, c.section,
    (SELECT COUNT(*) FROM student_classes sc WHERE sc.class_id = c.id) AS student_count
    FROM classes c WHERE c.teacher_id = ?");
$myClasses->execute([$teacherId]);
$myClasses = $myClasses->fetchAll();

// Recent announcements
$announcements = $pdo->prepare("SELECT title, message, created_at FROM announcements WHERE teacher_id = ? ORDER BY created_at DESC LIMIT 3");
$announcements->execute([$teacherId]);
$announcements = $announcements->fetchAll();

// My assignments
$assignments = $pdo->prepare("SELECT a.title, a.due_date, c.name AS class_name,
    (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id) AS submission_count
    FROM assignments a JOIN classes c ON c.id = a.class_id
    WHERE a.teacher_id = ? ORDER BY a.created_at DESC LIMIT 5");
$assignments->execute([$teacherId]);
$assignments = $assignments->fetchAll();

// Today's attendance summary
$today = date('Y-m-d');
$todayAttendance = $pdo->prepare("SELECT COUNT(*) as marked FROM attendance WHERE marked_by = ? AND date = ?");
$todayAttendance->execute([$teacherId, $today]);
$todayCount = $todayAttendance->fetchColumn();

include '../includes/header.php';
?>

<div class="mb-4">
    <h4 class="fw-bold">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>! 👋</h4>
    <p class="text-muted">Here's your classroom overview for today, <?= date('l, d F Y') ?></p>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card card-gradient-1">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="mb-1 opacity-75 small">My Classes</p>
                    <h2 class="fw-bold mb-0"><?= count($myClasses) ?></h2>
                    <small class="opacity-75">Active Classes</small>
                </div>
                <i class="bi bi-building fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card card-gradient-2">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="mb-1 opacity-75 small">Assignments Posted</p>
                    <h2 class="fw-bold mb-0"><?= count($assignments) ?></h2>
                    <small class="opacity-75">Recent 5</small>
                </div>
                <i class="bi bi-journal-text fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card card-gradient-5">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="mb-1 opacity-75 small">Attendance Marked Today</p>
                    <h2 class="fw-bold mb-0"><?= $todayCount ?></h2>
                    <small class="opacity-75">Student Records</small>
                </div>
                <i class="bi bi-check2-square fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3"><i class="bi bi-lightning-fill text-warning me-2"></i>Quick Actions</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>teacher/attendance.php" class="btn btn-primary">
                <i class="bi bi-check2-square me-2"></i>Mark Attendance
            </a>
            <a href="<?= BASE_URL ?>teacher/assignments.php" class="btn btn-secondary">
                <i class="bi bi-journal-plus me-2"></i>Post Assignment
            </a>
            <a href="<?= BASE_URL ?>teacher/marks.php" class="btn btn-success">
                <i class="bi bi-award me-2"></i>Enter Marks
            </a>
            <a href="<?= BASE_URL ?>teacher/announcements.php" class="btn btn-warning text-dark">
                <i class="bi bi-megaphone me-2"></i>Post Announcement
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- My Classes -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white border-0 pt-3 px-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-building me-2 text-primary"></i>My Classes</h6>
            </div>
            <div class="card-body">
                <?php foreach ($myClasses as $c): ?>
                <div class="d-flex align-items-center p-3 mb-2 rounded-3 bg-light">
                    <div class="p-2 rounded-2 bg-primary bg-opacity-10 me-3">
                        <i class="bi bi-building text-primary"></i>
                    </div>
                    <div>
                        <p class="fw-bold mb-0 small"><?= htmlspecialchars($c['name']) ?></p>
                        <small class="text-muted">Section <?= $c['section'] ?> | <?= $c['student_count'] ?> students</small>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($myClasses)): ?>
                <p class="text-muted text-center py-3">No classes assigned yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Assignments -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-secondary"></i>Recent Assignments</h6>
                <a href="<?= BASE_URL ?>teacher/assignments.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Title</th><th>Class</th><th>Due Date</th><th>Submissions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td><?= htmlspecialchars($a['title']) ?></td>
                                <td><?= htmlspecialchars($a['class_name']) ?></td>
                                <td><?= date('d M Y', strtotime($a['due_date'])) ?></td>
                                <td><span class="badge bg-success"><?= $a['submission_count'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($assignments)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No assignments yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Announcements -->
        <div class="mt-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-megaphone me-2 text-warning"></i>My Recent Announcements</h6>
            <?php foreach ($announcements as $ann): ?>
            <div class="announcement-card">
                <h6 class="fw-bold mb-1"><?= htmlspecialchars($ann['title']) ?></h6>
                <p class="text-muted small mb-1"><?= htmlspecialchars(substr($ann['message'], 0, 100)) ?>...</p>
                <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('d M Y', strtotime($ann['created_at'])) ?></small>
            </div>
            <?php endforeach; ?>
            <?php if (empty($announcements)): ?>
            <p class="text-muted">No announcements yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
