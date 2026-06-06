<?php
require_once 'config.php';
if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'instructor') { header("Location: login.php"); exit; }

$instructor_id = $_SESSION['user_id'];

$nameStmt = $pdo->prepare("SELECT full_name, username FROM users WHERE id = ?");
$nameStmt->execute([$instructor_id]);
$instructor = $nameStmt->fetch();
$displayName = $instructor['full_name'] ?? $instructor['username'];

// Get all sections assigned to this teacher
$sections = $pdo->prepare("
    SELECT s.*, subj.name as subject_name 
    FROM sections s
    JOIN instructor_sections ins ON s.id = ins.section_id
    JOIN subjects subj ON s.subject_id = subj.id
    WHERE ins.instructor_id = ?
    ORDER BY subj.name, s.name
");
$sections->execute([$instructor_id]);
$sectionList = $sections->fetchAll();

// Get pending count for badge
$section_ids = array_column($sectionList, 'id');
$pendingCount = 0;
if (count($section_ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($section_ids), '?'));
    $pendingStmt = $pdo->prepare("SELECT COUNT(*) as total FROM pending_attendance WHERE section_id IN ($placeholders) AND status = 'pending'");
    $pendingStmt->execute($section_ids);
    $pendingCount = $pendingStmt->fetch()['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Section - QR Attendance</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:linear-gradient(135deg,#c5e0f4 0%,#a8d0e6 100%);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;}
        
        .navbar{background:#216699;color:white;padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #ede432;}
        .navbar h2{color:white;font-size:1.3rem;}
        .badge-teacher{background:#ede432;color:#1e293b;padding:4px 12px;border-radius:40px;margin-left:12px;font-weight:bold;font-size:0.8rem;}
        .btn-logout{background:#e2362c;border:none;padding:8px 20px;border-radius:40px;color:white;cursor:pointer;font-weight:bold;text-decoration:none;display:inline-block;}
        .btn-logout:hover{background:#c92a20;}
        
        .teacher-nav{background:#1a4f77;padding:0.8rem 2rem;display:flex;justify-content:center;gap:20px;flex-wrap:wrap;border-bottom:1px solid #ede432;}
        .teacher-nav a{background:transparent;color:white;text-decoration:none;padding:8px 20px;border-radius:40px;font-weight:bold;}
        .teacher-nav a:hover{background:#ede432;color:#1a4f77;}
        .teacher-nav a.active{background:#ede432;color:#1a4f77;}
        
        .container{max-width:1200px;margin:30px auto;padding:0 24px;}
        .card{background:white;border-radius:28px;box-shadow:0 8px 20px rgba(0,0,0,0.15);overflow:hidden;margin-bottom:28px;}
        .card-header{background:#216699;padding:20px 25px;border-bottom:3px solid #ede432;text-align:center;}
        .card-header h1{color:white;font-size:1.5rem;}
        .card-body{padding:25px;}
        
        .btn-primary{background:#216699;color:white;border:none;padding:8px 18px;border-radius:40px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block;}
        .btn-primary:hover{background:#1a4f77;}
        
        .section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;}
        .section-card{background:#fef9e3;border-radius:20px;padding:20px;border-left:6px solid #e2362c;transition:all 0.3s;text-align:center;}
        .section-card:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,0,0,0.1);}
        .section-card h3{font-size:1.3rem;margin-bottom:10px;color:#216699;}
        .section-card p{color:#555;margin-bottom:15px;}
        
        .empty-message{text-align:center;padding:40px;color:#888;font-size:1.1rem;}
        .pending-badge{background:#e2362c;color:white;border-radius:20px;padding:2px 8px;font-size:0.7rem;margin-left:5px;}
    </style>
</head>
<body>
<div class="navbar">
    <div><h2>Internship Tracker <span class="badge-teacher">INSTRUCTOR: <?= htmlspecialchars($displayName) ?></span></h2></div>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>

<div class="teacher-nav">
    <a href="teacher_dashboard.php">Dashboard</a>
    <a href="teacher_subjects.php">Subjects</a>
    <a href="teacher_sections.php">Sections</a>
    <a href="teacher_students_list.php">Students</a>
    <a href="teacher_select_section.php" class="active">QR Attendance</a>
    <a href="teacher_pending_approvals.php">Pending Approvals <?php if($pendingCount > 0): ?><span class="pending-badge"><?= $pendingCount ?></span><?php endif; ?></a>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Select Section for QR Attendance</h1>
        </div>
        <div class="card-body">
            <?php if(count($sectionList) > 0): ?>
                <div class="section-grid">
                    <?php foreach($sectionList as $sec): ?>
                    <div class="section-card">
                        <h3><?= htmlspecialchars($sec['name']) ?></h3>
                        <p><strong>Subject:</strong> <?= htmlspecialchars($sec['subject_name']) ?></p>
                        <div style="margin-top:15px;">
                            <a href="teacher_qr_attendance.php?section_id=<?= $sec['id'] ?>" class="btn-primary">Generate QR Codes</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-message">No sections assigned to you yet. Contact administrator.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body></html>