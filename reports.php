<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireRole('admin');

$pageTitle = 'Reports';

// Attendance summary per class
$attendanceReport = $pdo->query("
    SELECT c.name AS class_name, c.section,
        COUNT(DISTINCT a.student_id) AS total_students,
        SUM(a.status = 'present') AS present_count,
        SUM(a.status = 'absent') AS absent_count,
        COUNT(a.id) AS total_records
    FROM classes c
    LEFT JOIN attendance a ON a.class_id = c.id
    GROUP BY c.id, c.name, c.section
")->fetchAll();

// Performance summary per class
$performanceReport = $pdo->query("
    SELECT c.name AS class_name, c.section,
        COUNT(DISTINCT m.student_id) AS students_assessed,
        ROUND(AVG(m.marks_obtained), 1) AS avg_marks,
        MAX(m.marks_obtained) AS max_marks,
        MIN(m.marks_obtained) AS min_marks
    FROM classes c
    LEFT JOIN marks m ON m.class_id = c.id
    GROUP BY c.id, c.name, c.section
")->fetchAll();

// Top students
$topStudents = $pdo->query("
    SELECT u.name, c.name AS class_name, ROUND(AVG(m.marks_obtained), 1) AS avg_score
    FROM users u
    JOIN marks m ON m.student_id = u.id
    JOIN classes c ON c.id = m.class_id
    WHERE u.role = 'student'
    GROUP BY u.id, u.name, c.name
    ORDER BY avg_score DESC LIMIT 5
")->fetchAll();

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="section-title"><i class="bi bi-bar-chart-line me-2 text-success"></i>Reports & Analytics</h4>
    <button class="btn btn-outline-success" onclick="window.print()">
        <i class="bi bi-printer me-2"></i>Print Report
    </button>
</div>

<!-- Attendance Report -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h5 class="fw-bold mb-0"><i class="bi bi-calendar-check me-2 text-primary"></i>Attendance Summary</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr><th>Class</th><th>Section</th><th>Students</th><th>Present</th><th>Absent</th><th>Attendance %</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($attendanceReport as $r): 
                        $pct = $r['total_records'] > 0 ? round(($r['present_count'] / $r['total_records']) * 100) : 0;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($r['class_name']) ?></strong></td>
                        <td><?= htmlspecialchars($r['section']) ?></td>
                        <td><?= $r['total_students'] ?></td>
                        <td><span class="text-success fw-bold"><?= $r['present_count'] ?></span></td>
                        <td><span class="text-danger fw-bold"><?= $r['absent_count'] ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px">
                                    <div class="progress-bar bg-<?= $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') ?>"
                                         style="width:<?= $pct ?>%"></div>
                                </div>
                                <span class="fw-bold small"><?= $pct ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Performance Report -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h5 class="fw-bold mb-0"><i class="bi bi-graph-up me-2 text-secondary"></i>Academic Performance Summary</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr><th>Class</th><th>Section</th><th>Students Assessed</th><th>Avg Marks</th><th>Highest</th><th>Lowest</th><th>Grade</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($performanceReport as $r): 
                        $grade = $r['avg_marks'] >= 90 ? 'A+' : ($r['avg_marks'] >= 75 ? 'A' : ($r['avg_marks'] >= 60 ? 'B' : ($r['avg_marks'] >= 50 ? 'C' : 'F')));
                        $gradeClass = str_starts_with($grade,'A') ? 'grade-A' : ($grade === 'B' ? 'grade-B' : ($grade === 'C' ? 'grade-C' : 'grade-F'));
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($r['class_name']) ?></strong></td>
                        <td><?= htmlspecialchars($r['section']) ?></td>
                        <td><?= $r['students_assessed'] ?></td>
                        <td><strong><?= $r['avg_marks'] ?? '-' ?></strong></td>
                        <td class="text-success fw-bold"><?= $r['max_marks'] ?? '-' ?></td>
                        <td class="text-danger fw-bold"><?= $r['min_marks'] ?? '-' ?></td>
                        <td><span class="<?= $gradeClass ?>"><?= $grade ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Top Students -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h5 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Top Performing Students</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($topStudents as $i => $s): 
                $medals = ['🥇','🥈','🥉','4️⃣','5️⃣'];
            ?>
            <div class="col-md-4">
                <div class="card border-0 bg-light rounded-3 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <span style="font-size:1.8rem"><?= $medals[$i] ?? ($i+1) ?></span>
                        <div>
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($s['name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($s['class_name']) ?></small>
                        </div>
                        <span class="ms-auto fw-bold text-primary"><?= $s['avg_score'] ?>%</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($topStudents)): ?>
            <div class="text-center text-muted py-3">No marks data available.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
