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
    SELECT s.id, s.name, subj.name as subject_name 
    FROM sections s
    JOIN instructor_sections ins ON s.id = ins.section_id
    JOIN subjects subj ON s.subject_id = subj.id
    WHERE ins.instructor_id = ?
    ORDER BY subj.name, s.name
");
$sections->execute([$instructor_id]);
$sectionList = $sections->fetchAll();

// Get filter parameters
$selected_section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get students based on filter
$section_ids = array_column($sectionList, 'id');
if ($selected_section_id > 0) {
    $students = $pdo->prepare("
        SELECT s.*, sec.name as section_name, subj.name as subject_name 
        FROM students s
        JOIN sections sec ON s.section_id = sec.id
        JOIN subjects subj ON sec.subject_id = subj.id
        WHERE s.section_id = ?
        ORDER BY s.name
    ");
    $students->execute([$selected_section_id]);
} else {
    if (count($section_ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($section_ids), '?'));
        $students = $pdo->prepare("
            SELECT s.*, sec.name as section_name, subj.name as subject_name 
            FROM students s
            JOIN sections sec ON s.section_id = sec.id
            JOIN subjects subj ON sec.subject_id = subj.id
            WHERE s.section_id IN ($placeholders)
            ORDER BY s.name
        ");
        $students->execute($section_ids);
    } else {
        $students = [];
    }
}
$studentList = ($students instanceof PDOStatement) ? $students->fetchAll() : [];

// Apply search filter
if (!empty($search) && count($studentList) > 0) {
    $filtered = [];
    foreach ($studentList as $stu) {
        if (stripos($stu['name'], $search) !== false) {
            $filtered[] = $stu;
        }
    }
    $studentList = $filtered;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor - Students</title>
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
        .card-header{background:#216699;padding:20px 25px;border-bottom:3px solid #ede432;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;}
        .card-header h1{color:white;font-size:1.5rem;margin:0;}
        .card-body{padding:25px;}
        
        .btn-primary{background:#216699;color:white;border:none;padding:8px 18px;border-radius:40px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block;}
        .btn-primary:hover{background:#1a4f77;}
        .btn-warning{background:#ede432;color:#1e293b;border:none;padding:6px 14px;border-radius:40px;cursor:pointer;font-weight:bold;text-decoration:none;display:inline-block;}
        .btn-warning:hover{background:#d4cc2a;}
        .btn-sm{padding:6px 14px;font-size:0.85rem;}
        
        .filter-section{display:flex;gap:15px;align-items:center;flex-wrap:wrap;justify-content:center;margin-bottom:25px;padding:20px;background:#f8fafc;border-radius:20px;}
        .filter-section label{font-weight:bold;color:#216699;}
        .filter-section select{padding:10px 20px;border-radius:40px;border:2px solid #216699;background:white;font-size:0.9rem;min-width:200px;}
        .filter-section input{padding:10px 20px;border-radius:40px;border:2px solid #216699;background:white;font-size:0.9rem;width:250px;}
        
        .students-table{width:100%;border-collapse:collapse;margin-top:15px;}
        .students-table th,.students-table td{padding:14px 12px;border-bottom:1px solid #e2e8f0;vertical-align:middle;}
        .students-table th{background:#216699;color:#ede432;font-weight:600;text-align:left;}
        .students-table td{color:#333;}
        .students-table tr:hover{background:#f8fafc;}
        
        .empty-message{text-align:center;padding:60px;color:#888;font-size:1.1rem;background:#f8fafc;border-radius:20px;margin-top:20px;}
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
    <a href="teacher_students_list.php" class="active">Students</a>
</div>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>My Students</h1>
        </div>
        <div class="card-body">
            <div class="filter-section">
                <label>Filter by Section:</label>
                <select id="section_filter" onchange="window.location.href='?section_id='+this.value">
                    <option value="0">-- All Sections --</option>
                    <?php foreach($sectionList as $sec): ?>
                        <option value="<?= $sec['id'] ?>" <?= ($selected_section_id == $sec['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sec['subject_name']) ?> - <?= htmlspecialchars($sec['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <form method="GET" style="display:inline-flex; gap:10px;">
                    <input type="hidden" name="section_id" value="<?= $selected_section_id ?>">
                    <input type="text" name="search" placeholder="Search student name..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn-primary btn-sm">Search</button>
                    <?php if(!empty($search) || $selected_section_id > 0): ?>
                        <a href="teacher_students_list.php" class="btn-warning btn-sm">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if(count($studentList) > 0): ?>
                <table class="students-table">
                    <thead>
                        <tr>
                            <th style="width:5%;">#</th>
                            <th style="width:35%;">Student Name</th>
                            <th style="width:25%;">Section</th>
                            <th style="width:25%;">Subject</th>
                            <th style="width:10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach($studentList as $stu): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($stu['name']) ?></td>
                            <td><?= htmlspecialchars($stu['section_name']) ?></td>
                            <td><?= htmlspecialchars($stu['subject_name']) ?></td>
                            <td><a href="teacher_students.php?section_id=<?= $stu['section_id'] ?>&student_id=<?= $stu['id'] ?>" class="btn-primary btn-sm">View Attendance</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif(count($sectionList) == 0): ?>
                <div class="empty-message">No sections assigned to you yet. Contact administrator.</div>
            <?php elseif($selected_section_id > 0 && count($studentList) == 0): ?>
                <div class="empty-message">No students found in this section.</div>
            <?php elseif(empty($search)): ?>
                <div class="empty-message">No students assigned to your sections yet.</div>
            <?php else: ?>
                <div class="empty-message">No students found matching "<strong><?= htmlspecialchars($search) ?></strong>".</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body></html>