<?php
require_once 'config.php';
if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'instructor') { header("Location: login.php"); exit; }

$instructor_id = $_SESSION['user_id'];

$nameStmt = $pdo->prepare("SELECT full_name, username FROM users WHERE id = ?");
$nameStmt->execute([$instructor_id]);
$instructor = $nameStmt->fetch();
$displayName = $instructor['full_name'] ?? $instructor['username'];

// Get teacher's assigned subjects
$subjects = $pdo->prepare("
    SELECT s.id, s.name, s.required_hours 
    FROM subjects s
    JOIN instructor_subjects ins ON s.id = ins.subject_id
    WHERE ins.instructor_id = ?
    ORDER BY s.name
");
$subjects->execute([$instructor_id]);
$subjectList = $subjects->fetchAll();

// Get teacher's assigned sections
$sections = $pdo->prepare("
    SELECT s.id, s.name, subj.name as subject_name 
    FROM sections s
    JOIN instructor_sections ins ON s.id = ins.section_id
    JOIN subjects subj ON s.subject_id = subj.id
    WHERE ins.instructor_id = ?
    ORDER BY subj.name, s.name
");
$sections->execute([$instructor_id]);
$sectionList = $sections->fetchAll();

// Get total students count
$section_ids = array_column($sectionList, 'id');
$totalStudents = 0;
if (count($section_ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($section_ids), '?'));
    $studentStmt = $pdo->prepare("SELECT COUNT(*) as total FROM students WHERE section_id IN ($placeholders)");
    $studentStmt->execute($section_ids);
    $totalStudents = $studentStmt->fetch()['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:linear-gradient(135deg,#c5e0f4 0%,#a8d0e6 100%);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;}
        
        .navbar{background:#216699;color:white;padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #ede432;}
        .navbar h2{color:white;font-size:1.3rem;}
        .badge-teacher{background:#ede432;color:#1e293b;padding:4px 12px;border-radius:40px;margin-left:12px;font-weight:bold;font-size:0.8rem;}
        .btn-logout{background:#e2362c;border:none;padding:8px 20px;border-radius:40px;color:white;cursor:pointer;font-weight:bold;text-decoration:none;display:inline-block;transition:all 0.3s;}
        .btn-logout:hover{background:#c92a20;transform:scale(1.02);}
        
        .teacher-nav{background:#1a4f77;padding:0.8rem 2rem;display:flex;justify-content:center;gap:20px;flex-wrap:wrap;border-bottom:1px solid #ede432;}
        .teacher-nav a{background:transparent;color:white;text-decoration:none;padding:8px 20px;border-radius:40px;font-weight:bold;transition:all 0.3s;}
        .teacher-nav a:hover{background:#ede432;color:#1a4f77;}
        .teacher-nav a.active{background:#ede432;color:#1a4f77;}
        
        .container{max-width:1400px;margin:30px auto;padding:0 24px;}
        .card{background:white;border-radius:28px;box-shadow:0 8px 20px rgba(0,0,0,0.15);overflow:hidden;margin-bottom:28px;}
        .card-header{background:#216699;padding:20px 25px;border-bottom:3px solid #ede432;text-align:left;}
        .card-header h1{color:white;font-size:1.5rem;}
        .card-body{padding:25px;}
        .btn-primary{background:#216699;color:white;border:none;padding:8px 18px;border-radius:40px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block;transition:all 0.3s;}
        .btn-primary:hover{background:#1a4f77;transform:scale(1.02);}
        .dashboard-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;justify-content:center;}
        .dashboard-card{background:#fef9e3;border-radius:20px;padding:25px;border-left:6px solid #e2362c;transition:all 0.3s;text-align:center;}
        .dashboard-card:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,0,0,0.1);}
        .dashboard-card h3{font-size:1.3rem;margin-bottom:15px;color:#216699;}
        .dashboard-card .number{font-size:2.8rem;font-weight:bold;color:#216699;margin:15px 0;}
    </style>
</head>
<body>
<div class="navbar">
    <div><h2>Internship Tracker <span class="badge-teacher">INSTRUCTOR: <?= htmlspecialchars($displayName) ?></span></h2></div>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>

<div class="teacher-nav">
    <a href="teacher_dashboard.php" class="active">Dashboard</a>
    <a href="teacher_subjects.php">Subjects</a>
    <a href="teacher_sections.php">Sections</a>
    <a href="teacher_students_list.php">Students</a>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Dashboard Overview</h1>
        </div>
        <div class="card-body">
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>My Subjects</h3>
                    <div class="number"><?= count($subjectList) ?></div>
                    <a href="teacher_subjects.php" class="btn-primary">View Subjects</a>
                </div>
                <div class="dashboard-card">
                    <h3>My Sections</h3>
                    <div class="number"><?= count($sectionList) ?></div>
                    <a href="teacher_sections.php" class="btn-primary">View Sections</a>
                </div>
                <div class="dashboard-card">
                    <h3>My Students</h3>
                    <div class="number"><?= $totalStudents ?></div>
                    <a href="teacher_students_list.php" class="btn-primary">View Students</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body></html>