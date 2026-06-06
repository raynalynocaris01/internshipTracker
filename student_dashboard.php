<?php
require_once 'config.php';
if ($_SESSION['role'] !== 'student') die("Access denied");
$student_id = $_SESSION['ref_id'];

// Get the student's section and all subjects they are enrolled in
$subjects = $pdo->prepare("
    SELECT DISTINCT subj.id, subj.name, subj.required_hours
    FROM students s
    JOIN sections sec ON s.section_id = sec.id
    JOIN subjects subj ON sec.subject_id = subj.id
    WHERE s.id = ?
");
$subjects->execute([$student_id]);
$subjectList = $subjects->fetchAll();

if (empty($subjectList)) {
    die("No subject assigned. Contact teacher.");
}

// If a subject is selected via GET
$selected_subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;
$selected_subject = null;
$completed = 0;
$required = 0;
$remaining = 0;
$attRecords = [];

if ($selected_subject_id) {
    foreach ($subjectList as $subj) {
        if ($subj['id'] == $selected_subject_id) {
            $selected_subject = $subj;
            break;
        }
    }
    if ($selected_subject) {
        $required = (float)$selected_subject['required_hours'];
        $totalStmt = $pdo->prepare("
            SELECT COALESCE(SUM(a.total_hours), 0) as completed
            FROM attendance a
            WHERE a.student_id = ?
        ");
        $totalStmt->execute([$student_id]);
        $completed = round($totalStmt->fetch()['completed'], 2);
        $remaining = max(0, $required - $completed);

        $attStmt = $pdo->prepare("
            SELECT date, morning_in, morning_out, afternoon_in, afternoon_out, total_hours
            FROM attendance
            WHERE student_id = ?
            ORDER BY date DESC
        ");
        $attStmt->execute([$student_id]);
        $attRecords = $attStmt->fetchAll();
    }
}

// Get instructor name for navbar
$instructor_id = $_SESSION['user_id'];
$nameStmt = $pdo->prepare("SELECT full_name, username FROM users WHERE id = ?");
$nameStmt->execute([$instructor_id]);
$instructor = $nameStmt->fetch();
$displayName = $instructor['full_name'] ?? $instructor['username'];
?>
<!DOCTYPE html>
<html><head><title>Student Dashboard</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:linear-gradient(135deg, #c5e0f4 0%, #a8d0e6 100%);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;}
.navbar{background:#216699;padding:1rem 5%;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 10px rgba(0,0,0,0.2);}
.navbar h2{color:#ede432;margin:0;}
.badge-student{background:#ede432;color:#216699;padding:4px 12px;border-radius:40px;font-size:0.8rem;font-weight:bold;margin-left:12px;}
.btn-logout{background:transparent;border:2px solid #ede432;color:#ede432;border-radius:40px;padding:6px 18px;transition:0.3s;text-decoration:none;}
.btn-logout:hover{background:#ede432;color:#216699;}
.container{max-width:1200px;margin:30px auto;padding:0 20px;}
.card{background:white;border-radius:28px;box-shadow:0 8px 20px rgba(0,0,0,0.08);margin-bottom:25px;overflow:hidden;}
.card-header{background:#216699;color:#ede432;padding:15px 20px;font-weight:bold;border-bottom:3px solid #ede432;}
.card-header h1{margin:0;font-size:1.5rem;}
.btn-primary{background:#216699;color:white;border:2px solid #ede432;border-radius:40px;padding:8px 20px;cursor:pointer;transition:0.3s;font-weight:bold;text-decoration:none;display:inline-block;}
.btn-primary:hover{background:#ede432;color:#216699;}
.btn-warning{background:#ede432;color:#216699;border:none;border-radius:40px;padding:8px 20px;cursor:pointer;transition:0.3s;text-decoration:none;display:inline-block;font-weight:bold;}
.btn-warning:hover{background:#d4c920;}
.alert-info{background:#d1ecf1;color:#0c5460;padding:12px;border-radius:20px;margin-bottom:20px;}
.subject-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px;padding:20px;}
.subject-card{background:#f8fafc;border-radius:20px;padding:24px;transition:0.2s;border:1px solid #e2e8f0;}
.subject-card:hover{transform:translateY(-3px);box-shadow:0 8px 16px rgba(0,0,0,0.1);border-color:#ede432;}
.subject-card h3{color:#216699;font-size:1.5rem;margin-bottom:10px;}
.stats-panel{background:#f8fafc;border-radius:24px;padding:30px;margin:20px 0;text-align:center;}
.stat-card{background:white;border-radius:20px;padding:20px 30px;display:inline-block;margin:0 15px;box-shadow:0 2px 8px rgba(0,0,0,0.05);}
.stat-card h3{color:#216699;margin-bottom:10px;}
.stat-number{font-size:48px;font-weight:bold;color:#216699;}
table{width:100%;border-collapse:collapse;}
th{background:#216699;color:#ede432;padding:12px;text-align:left;}
td{padding:10px;border-bottom:1px solid #ddd;}
</style>
</head>
<body>
<div class="navbar">
    <div><h2>Internship Tracker</h2><span class="badge-student">STUDENT VIEW</span></div>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>
<div class="container">
    <?php if (!$selected_subject_id): ?>
        <!-- Subject list view -->
        <div class="card">
            <div class="card-header"><h1>My Internship Subjects</h1></div>
            <div class="subject-grid">
                <?php foreach ($subjectList as $subj): ?>
                    <div class="subject-card">
                        <h3><?= htmlspecialchars($subj['name']) ?></h3>
                        <p>Required Hours: <strong><?= $subj['required_hours'] ?></strong></p>
                        <div style="margin-top:20px;">
                            <a href="?subject_id=<?= $subj['id'] ?>" class="btn-primary">View My Hours</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Hours view for selected subject -->
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h1><?= htmlspecialchars($selected_subject['name']) ?></h1>
                <a href="student_dashboard.php" class="btn-warning">← Back to Subjects</a>
            </div>
            <div class="stats-panel">
                <div style="display:flex; justify-content:center; gap:30px; flex-wrap:wrap;">
                    <div class="stat-card">
                        <h3>Completed Hours</h3>
                        <div class="stat-number"><?= $completed ?></div>
                        <span>/ <?= $required ?> hrs</span>
                    </div>
                    <div class="stat-card">
                        <h3>Remaining Hours</h3>
                        <div class="stat-number"><?= $remaining ?></div>
                        <span>to complete OJT</span>
                    </div>
                </div>
            </div>

            <div class="card" style="margin:20px;">
                <div class="card-header"><h3>Your Daily Time Records</h3></div>
                <?php if(count($attRecords) > 0): ?>
                <div style="padding:20px; overflow-x:auto;">
                    <table>
                        <thead>
                            <tr><th>Date</th><th>Morning In</th><th>Morning Out</th><th>Afternoon In</th><th>Afternoon Out</th><th>Total Hours</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($attRecords as $rec): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($rec['date'])) ?></td>
                                <td><?= $rec['morning_in'] ? htmlspecialchars($rec['morning_in']) : '-' ?></td>
                                <td><?= $rec['morning_out'] ? htmlspecialchars($rec['morning_out']) : '-' ?></td>
                                <td><?= $rec['afternoon_in'] ? htmlspecialchars($rec['afternoon_in']) : '-' ?></td>
                                <td><?= $rec['afternoon_out'] ? htmlspecialchars($rec['afternoon_out']) : '-' ?></td>
                                <td><strong><?= $rec['total_hours'] ?> hrs</strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="alert-info" style="margin:20px;">No attendance records yet. Your teacher will record your time.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</body></html>