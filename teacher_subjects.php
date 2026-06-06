<?php
require_once 'config.php';
if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'instructor') { header("Location: login.php"); exit; }

$instructor_id = $_SESSION['user_id'];

$nameStmt = $pdo->prepare("SELECT full_name, username FROM users WHERE id = ?");
$nameStmt->execute([$instructor_id]);
$instructor = $nameStmt->fetch();
$displayName = $instructor['full_name'] ?? $instructor['username'];

$subjects = $pdo->prepare("
    SELECT s.id, s.name, s.required_hours 
    FROM subjects s
    JOIN instructor_subjects ins ON s.id = ins.subject_id
    WHERE ins.instructor_id = ?
    ORDER BY s.name
");
$subjects->execute([$instructor_id]);
$subjectList = $subjects->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor - Subjects</title>
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
        
        .container{max-width:1400px;margin:30px auto;padding:0 24px;}
        .card{background:white;border-radius:28px;box-shadow:0 8px 20px rgba(0,0,0,0.15);overflow:hidden;margin-bottom:28px;}
        .card-header{background:#216699;padding:20px 25px;border-bottom:3px solid #ede432;text-align:left;}
        .card-header h1{color:white;font-size:1.5rem;}
        .card-body{padding:25px;}
        
        .btn-primary{background:#216699;color:white;border:none;padding:8px 18px;border-radius:40px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block;}
        .btn-primary:hover{background:#1a4f77;}
        .btn-sm{padding:6px 14px;font-size:0.85rem;}
        
        .subject-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;}
        .subject-card{background:#fef9e3;border-radius:20px;padding:20px;border-left:6px solid #e2362c;transition:all 0.3s;}
        .subject-card:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,0,0,0.1);}
        .subject-card h3{font-size:1.4rem;margin-bottom:10px;color:#216699;}
        .subject-card p{font-size:1rem;color:#555;margin-bottom:15px;}
        
        .empty-message{text-align:center;padding:40px;color:#888;font-size:1.1rem;}
    </style>
</head>
<body>
<div class="navbar">
    <div><h2>Internship Tracker <span class="badge-teacher">INSTRUCTOR: <?= htmlspecialchars($displayName) ?></span></h2></div>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>

<div class="teacher-nav">
    <a href="teacher_dashboard.php">Dashboard</a>
    <a href="teacher_subjects.php" class="active">Subjects</a>
    <a href="teacher_sections.php">Sections</a>
    <a href="teacher_students_list.php">Students</a>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>My Subjects</h1>
        </div>
        <div class="card-body">
            <div class="subject-grid">
                <?php foreach($subjectList as $subj): ?>
                <div class="subject-card">
                    <h3><?= htmlspecialchars($subj['name']) ?></h3>
                    <p>Required Hours: <strong><?= $subj['required_hours'] ?></strong> hrs</p>
                    <div style="margin-top:15px;">
                        <a href="teacher_sections.php?subject_id=<?= $subj['id'] ?>" class="btn-primary btn-sm">View Sections</a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(count($subjectList)==0): ?>
                    <div class="empty-message" style="grid-column:1/-1;">No subjects assigned. Contact administrator.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body></html>