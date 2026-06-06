<?php
require_once 'config.php';
if (!isAdmin()) { header("Location: login.php"); exit; }

// Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $name = trim($_POST['student_name']);
    $section_id = (int)$_POST['section_id'];
    
    $check = $pdo->prepare("SELECT id FROM sections WHERE id = ?");
    $check->execute([$section_id]);
    if($check->fetch() && !empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO students (name, section_id) VALUES (?, ?)");
        $stmt->execute([$name, $section_id]);
        $_SESSION['msg'] = "Student added successfully!";
    } else {
        $_SESSION['msg'] = "Failed to add student. Please check section exists.";
    }
    header("Location: admin_students.php");
    exit;
}

// Delete Student
if (isset($_GET['delete_student'])) {
    $student_id = (int)$_GET['delete_student'];
    $pdo->prepare("DELETE FROM users WHERE role='student' AND ref_id = ?")->execute([$student_id]);
    $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$student_id]);
    $_SESSION['msg'] = "Student deleted successfully!";
    header("Location: admin_students.php");
    exit;
}

// Edit Student
if (isset($_POST['edit_student'])) {
    $student_id = (int)$_POST['student_id'];
    $name = trim($_POST['student_name']);
    $section_id = (int)$_POST['section_id'];
    $stmt = $pdo->prepare("UPDATE students SET name = ?, section_id = ? WHERE id = ?");
    $stmt->execute([$name, $section_id, $student_id]);
    $_SESSION['msg'] = "Student updated successfully!";
    header("Location: admin_students.php");
    exit;
}

// Get all sections for dropdown
$sections = $pdo->query("SELECT s.id, s.name, subj.name as subject_name FROM sections s JOIN subjects subj ON s.subject_id = subj.id ORDER BY subj.name, s.name")->fetchAll();

// Get filter parameters
$section_filter = isset($_GET['section_filter']) ? (int)$_GET['section_filter'] : 0;

if($section_filter > 0) {
    $students = $pdo->prepare("SELECT s.*, sec.name as section_name, subj.name as subject_name, subj.id as subject_id 
                               FROM students s 
                               JOIN sections sec ON s.section_id = sec.id 
                               JOIN subjects subj ON sec.subject_id = subj.id 
                               WHERE s.section_id = ? 
                               ORDER BY s.name");
    $students->execute([$section_filter]);
} else {
    $students = $pdo->query("SELECT s.*, sec.name as section_name, subj.name as subject_name, subj.id as subject_id 
                             FROM students s 
                             JOIN sections sec ON s.section_id = sec.id 
                             JOIN subjects subj ON sec.subject_id = subj.id 
                             ORDER BY s.name");
}
$studentList = $students->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Students</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:linear-gradient(135deg,#c5e0f4 0%,#a8d0e6 100%);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;}
        
        .navbar{background:#216699;color:white;padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #ede432;}
        .navbar h2{color:white;font-size:1.3rem;}
        .badge-teacher{background:#ede432;color:#1e293b;padding:4px 12px;border-radius:40px;margin-left:12px;font-weight:bold;font-size:0.8rem;}
        .btn-logout{background:#e2362c;border:none;padding:8px 20px;border-radius:40px;color:white;cursor:pointer;font-weight:bold;text-decoration:none;display:inline-block;transition:all 0.3s;}
        .btn-logout:hover{background:#c92a20;transform:scale(1.02);}
        
        .admin-nav{background:#1a4f77;padding:0.8rem 2rem;display:flex;justify-content:center;gap:20px;flex-wrap:wrap;border-bottom:1px solid #ede432;}
        .admin-nav a{background:transparent;color:white;text-decoration:none;padding:8px 20px;border-radius:40px;font-weight:bold;transition:all 0.3s;}
        .admin-nav a:hover{background:#ede432;color:#1a4f77;}
        .admin-nav a.active{background:#ede432;color:#1a4f77;}
        
        .container{max-width:1400px;margin:30px auto;padding:0 24px;}
        
        .card{background:white;border-radius:28px;box-shadow:0 8px 20px rgba(0,0,0,0.15);overflow:hidden;margin-bottom:28px;}
        .card-header{background:#216699;padding:20px 25px;border-bottom:3px solid #ede432;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;}
        .card-header h3{color:white;font-size:1.3rem;}
        .card-body{padding:25px;}
        
        .btn-primary{background:#216699;color:white;border:none;padding:8px 18px;border-radius:40px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block;transition:all 0.3s;}
        .btn-primary:hover{background:#1a4f77;transform:scale(1.02);}
        .btn-danger{background:#e2362c;color:white;border:none;padding:6px 14px;border-radius:40px;cursor:pointer;text-decoration:none;display:inline-block;transition:all 0.3s;}
        .btn-danger:hover{background:#c92a20;transform:scale(1.02);}
        .btn-warning{background:#ede432;color:#1e293b;border:none;padding:6px 14px;border-radius:40px;cursor:pointer;font-weight:bold;text-decoration:none;display:inline-block;transition:all 0.3s;}
        .btn-warning:hover{background:#d4cc2a;transform:scale(1.02);}
        .btn-sm{padding:6px 14px;font-size:0.85rem;}
        
        table{width:100%;border-collapse:collapse;}
        th,td{padding:12px 8px;border-bottom:1px solid #e2e8f0;vertical-align:middle;}
        th{background:#f1f5f9;color:#216699;font-weight:600;}
        
        /* Centered Filter Bar */
        .filter-section{text-align:center;margin:20px 0 25px 0;padding-bottom:15px;border-bottom:1px solid #e2e8f0;}
        .filter-label{font-weight:bold;color:#216699;margin-right:10px;}
        .filter-select{padding:8px 16px;border-radius:40px;border:2px solid #216699;background:white;margin-right:10px;}
        .filter-select:focus{outline:none;border-color:#e2362c;}
        
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;z-index:1000;}
        .modal-content{background:white;border-radius:28px;width:400px;overflow:hidden;}
        .modal-header{background:#216699;padding:20px;border-bottom:3px solid #ede432;}
        .modal-header h3{color:white;}
        .modal-body{padding:25px;}
        .modal-body input, .modal-body select{width:100%;padding:12px 16px;border:2px solid #216699;border-radius:40px;margin:10px 0;}
        .modal-body input:focus, .modal-body select:focus{outline:none;border-color:#e2362c;}
        .flex-btns{display:flex;gap:12px;justify-content:flex-end;margin-top:20px;}
        .alert{background:#e6fffa;padding:12px 20px;margin:0 25px 20px 25px;border-radius:40px;border-left:4px solid #216699;color:#216699;}
    </style>
</head>
<body>
<div class="navbar">
    <div><h2>Internship Tracker <span class="badge-teacher">ADMIN - STUDENTS</span></h2></div>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>

<div class="admin-nav">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_manage_instructors.php">Instructors</a>
    <a href="admin_subjects.php">Subjects</a>
    <a href="admin_sections.php">Sections</a>
    <a href="admin_students.php" class="active">Students</a>
</div>

<div class="container">
    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert"><?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <!-- Header with Title on LEFT and Add Button on RIGHT -->
        <div class="card-header">
            <h3>Manage Students</h3>
            <button id="openAddStudentModal" class="btn-primary">+ Add Student</button>
        </div>
        <div class="card-body">
            <!-- Filter Bar - CENTERED -->
            <div class="filter-section">
                <span class="filter-label">Filter by Section:</span>
                <select id="section_filter" class="filter-select" onchange="filterBySection()">
                    <option value="0">-- All Sections --</option>
                    <?php foreach($sections as $sec): ?>
                        <option value="<?= $sec['id'] ?>" <?= ($section_filter == $sec['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sec['subject_name']) ?> - <?= htmlspecialchars($sec['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button onclick="clearFilter()" class="btn-warning btn-sm">Clear Filter</button>
            </div>
            
            <!-- Students Table -->
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach($studentList as $stu): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($stu['name']) ?></td>
                        <td><?= htmlspecialchars($stu['section_name']) ?></td>
                        <td><?= htmlspecialchars($stu['subject_name']) ?></td>
                        <td>
                            <button onclick="editStudent(<?= $stu['id'] ?>, '<?= htmlspecialchars($stu['name']) ?>', <?= $stu['section_id'] ?>)" class="btn-warning btn-sm">Edit</button>
                            <a href="?delete_student=<?= $stu['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Delete this student? This will also delete their login account if exists.')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($studentList) == 0): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No students found. Click "Add Student" to create one.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div id="addStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Add New Student</h3></div>
        <div class="modal-body">
            <form method="POST">
                <input type="text" name="student_name" placeholder="Student Full Name" required>
                <select name="section_id" required>
                    <option value="">-- Select Section --</option>
                    <?php foreach($sections as $sec): ?>
                        <option value="<?= $sec['id'] ?>"><?= htmlspecialchars($sec['subject_name']) ?> - <?= htmlspecialchars($sec['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="flex-btns">
                    <button type="button" id="closeAddModal" class="btn-danger">Cancel</button>
                    <button type="submit" name="add_student" class="btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Edit Student</h3></div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="student_id" id="edit_student_id">
                <input type="text" name="student_name" id="edit_student_name" placeholder="Student Full Name" required>
                <select name="section_id" id="edit_section_id" required>
                    <option value="">-- Select Section --</option>
                    <?php foreach($sections as $sec): ?>
                        <option value="<?= $sec['id'] ?>"><?= htmlspecialchars($sec['subject_name']) ?> - <?= htmlspecialchars($sec['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="flex-btns">
                    <button type="button" id="closeEditModal" class="btn-danger">Cancel</button>
                    <button type="submit" name="edit_student" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('openAddStudentModal').onclick = () => document.getElementById('addStudentModal').style.display = 'flex';
    document.getElementById('closeAddModal').onclick = () => document.getElementById('addStudentModal').style.display = 'none';
    document.getElementById('closeEditModal').onclick = () => document.getElementById('editStudentModal').style.display = 'none';
    
    window.onclick = (e) => {
        if(e.target === document.getElementById('addStudentModal')) document.getElementById('addStudentModal').style.display = 'none';
        if(e.target === document.getElementById('editStudentModal')) document.getElementById('editStudentModal').style.display = 'none';
    };
    
    function editStudent(id, name, sectionId) {
        document.getElementById('edit_student_id').value = id;
        document.getElementById('edit_student_name').value = name;
        document.getElementById('edit_section_id').value = sectionId;
        document.getElementById('editStudentModal').style.display = 'flex';
    }
    
    function filterBySection() {
        var filter = document.getElementById('section_filter').value;
        window.location.href = "?section_filter=" + filter;
    }
    
    function clearFilter() {
        window.location.href = "admin_students.php";
    }
</script>
</body></html>