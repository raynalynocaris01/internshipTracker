<?php
require_once 'config.php';
if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'instructor') { header("Location: login.php"); exit; }

$instructor_id = $_SESSION['user_id'];

$nameStmt = $pdo->prepare("SELECT full_name, username FROM users WHERE id = ?");
$nameStmt->execute([$instructor_id]);
$instructor = $nameStmt->fetch();
$displayName = $instructor['full_name'] ?? $instructor['username'];

// Get teacher's assigned subjects for dropdown
$subjects = $pdo->prepare("
    SELECT s.id, s.name, s.required_hours 
    FROM subjects s
    JOIN instructor_subjects ins ON s.id = ins.subject_id
    WHERE ins.instructor_id = ?
    ORDER BY s.name
");
$subjects->execute([$instructor_id]);
$subjectList = $subjects->fetchAll();

// Get selected subject from URL
$selected_subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;
$selected_subject = null;
$sections = [];

if ($selected_subject_id) {
    foreach ($subjectList as $subj) {
        if ($subj['id'] == $selected_subject_id) {
            $selected_subject = $subj;
            break;
        }
    }
    if ($selected_subject) {
        $sections = $pdo->prepare("
            SELECT s.id, s.name, s.subject_id 
            FROM sections s
            JOIN instructor_sections ins ON s.id = ins.section_id
            WHERE s.subject_id = ? AND ins.instructor_id = ?
            ORDER BY s.name
        ");
        $sections->execute([$selected_subject_id, $instructor_id]);
        $sections = $sections->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor - Sections</title>
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
        
        .subject-selector{text-align:center;margin-bottom:30px;padding:20px;background:#f8fafc;border-radius:20px;}
        .subject-selector label{font-weight:bold;color:#216699;margin-right:10px;font-size:1rem;}
        .subject-selector select{padding:12px 25px;border-radius:40px;border:2px solid #216699;background:white;font-size:1rem;min-width:250px;cursor:pointer;}
        
        .section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;justify-content:center;}
        .section-card{background:#fef9e3;border-radius:20px;padding:20px;border-left:6px solid #e2362c;transition:all 0.3s;text-align:center;}
        .section-card:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,0,0,0.1);}
        .section-card h3{font-size:1.4rem;margin-bottom:10px;color:#216699;}
        .section-card p{font-size:1rem;color:#555;margin-bottom:15px;}
        
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
    <a href="teacher_subjects.php">Subjects</a>
    <a href="teacher_sections.php" class="active">Sections</a>
    <a href="teacher_students_list.php">Students</a>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>My Sections</h1>
        </div>
        <div class="card-body">
            <div class="subject-selector">
                <label>Select Subject:</label>
                <select id="subject_select" onchange="window.location.href='?subject_id='+this.value">
                    <option value="">-- Select a Subject --</option>
                    <?php foreach($subjectList as $subj): ?>
                        <option value="<?= $subj['id'] ?>" <?= ($selected_subject_id == $subj['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subj['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if($selected_subject_id && !$selected_subject): ?>
                <div class="empty-message">Subject not found or not assigned to you.</div>
            <?php elseif($selected_subject_id && $selected_subject): ?>
                <?php if(count($sections) > 0): ?>
                    <div class="section-grid">
                        <?php foreach($sections as $sec): ?>
                        <div class="section-card">
                            <h3><?= htmlspecialchars($sec['name']) ?></h3>
                            <p><strong>Subject:</strong> <?= htmlspecialchars($selected_subject['name']) ?></p>
                            <div style="margin-top:15px;">
                                <a href="teacher_students.php?section_id=<?= $sec['id'] ?>" class="btn-primary btn-sm">Manage Students</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-message">No sections assigned for this subject yet.</div>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-message">Select a subject above to view its sections.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body></html>