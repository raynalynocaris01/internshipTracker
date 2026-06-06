<?php
require_once 'config.php';
if (!isAdmin()) { header("Location: login.php"); exit; }

// Add Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $name = trim($_POST['subject_name']);
    $required_hours = (int)$_POST['required_hours'];
    if (!empty($name) && $required_hours > 0) {
        $pdo->prepare("INSERT INTO subjects (name, required_hours) VALUES (?, ?)")->execute([$name, $required_hours]);
        $_SESSION['msg'] = "Subject added successfully!";
    }
    header("Location: admin_subjects.php");
    exit;
}

// Delete Subject
if (isset($_GET['delete'])) {
    $subject_id = (int)$_GET['delete'];
    // Check if subject has sections
    $check = $pdo->prepare("SELECT COUNT(*) FROM sections WHERE subject_id = ?");
    $check->execute([$subject_id]);
    $count = $check->fetchColumn();
    if ($count == 0) {
        $pdo->prepare("DELETE FROM subjects WHERE id = ?")->execute([$subject_id]);
        $_SESSION['msg'] = "Subject deleted successfully!";
    } else {
        $_SESSION['msg'] = "Cannot delete subject. It has $count section(s) assigned.";
    }
    header("Location: admin_subjects.php");
    exit;
}

// Edit Subject
if (isset($_POST['edit_subject'])) {
    $subject_id = (int)$_POST['subject_id'];
    $name = trim($_POST['subject_name']);
    $required_hours = (int)$_POST['required_hours'];
    $pdo->prepare("UPDATE subjects SET name = ?, required_hours = ? WHERE id = ?")->execute([$name, $required_hours, $subject_id]);
    $_SESSION['msg'] = "Subject updated successfully!";
    header("Location: admin_subjects.php");
    exit;
}

// Get all subjects
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Subjects</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:linear-gradient(135deg,#c5e0f4 0%,#a8d0e6 100%);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;}
        
        .navbar{background:#216699;color:white;padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #ede432;}
        .navbar h2{color:white;font-size:1.3rem;}
        .badge-admin{background:#ede432;color:#1e293b;padding:4px 12px;border-radius:40px;margin-left:12px;font-weight:bold;font-size:0.8rem;}
        .btn-logout{background:#e2362c;border:none;padding:8px 20px;border-radius:40px;color:white;cursor:pointer;font-weight:bold;text-decoration:none;display:inline-block;}
        .btn-logout:hover{background:#c92a20;}
        
        .admin-nav{background:#1a4f77;padding:0.8rem 2rem;display:flex;justify-content:center;gap:20px;flex-wrap:wrap;border-bottom:1px solid #ede432;}
        .admin-nav a{background:transparent;color:white;text-decoration:none;padding:8px 20px;border-radius:40px;font-weight:bold;}
        .admin-nav a:hover{background:#ede432;color:#1a4f77;}
        .admin-nav a.active{background:#ede432;color:#1a4f77;}
        
        .container{max-width:1400px;margin:30px auto;padding:0 24px;}
        .card{background:white;border-radius:28px;box-shadow:0 8px 20px rgba(0,0,0,0.15);overflow:hidden;margin-bottom:28px;}
        .card-header{background:#216699;padding:20px 25px;border-bottom:3px solid #ede432;display:flex;justify-content:space-between;align-items:center;}
        .card-header h3{color:white;font-size:1.3rem;}
        .card-body{padding:25px;}
        
        .btn-primary{background:#216699;color:white;border:none;padding:8px 18px;border-radius:40px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block;}
        .btn-primary:hover{background:#1a4f77;}
        .btn-danger{background:#e2362c;color:white;border:none;padding:6px 14px;border-radius:40px;cursor:pointer;text-decoration:none;display:inline-block;}
        .btn-danger:hover{background:#c92a20;}
        .btn-warning{background:#ede432;color:#1e293b;border:none;padding:6px 14px;border-radius:40px;cursor:pointer;font-weight:bold;text-decoration:none;display:inline-block;}
        .btn-warning:hover{background:#d4cc2a;}
        .btn-sm{padding:6px 14px;font-size:0.85rem;}
        
        .subject-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;}
        .subject-card{background:#fef9e3;border-radius:20px;padding:20px;border-left:6px solid #e2362c;}
        .subject-card h3{font-size:1.3rem;margin-bottom:10px;color:#216699;}
        
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;z-index:1000;}
        .modal-content{background:white;border-radius:28px;width:400px;overflow:hidden;}
        .modal-header{background:#216699;padding:20px;border-bottom:3px solid #ede432;}
        .modal-header h3{color:white;}
        .modal-body{padding:25px;}
        .modal-body input{width:100%;padding:12px 16px;border:2px solid #216699;border-radius:40px;margin:10px 0;}
        .flex-btns{display:flex;gap:12px;justify-content:flex-end;margin-top:20px;}
        .alert{background:#e6fffa;padding:12px 20px;margin-bottom:20px;border-radius:40px;border-left:4px solid #216699;color:#216699;}
    </style>
</head>
<body>
<div class="navbar">
    <div><h2>Internship Tracker <span class="badge-admin">ADMIN - SUBJECTS</span></h2></div>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>

<div class="admin-nav">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_manage_instructors.php">Instructors</a>
    <a href="admin_subjects.php" class="active">Subjects</a>
    <a href="admin_sections.php">Sections</a>
    <a href="admin_students.php">Students</a>
</div>

<div class="container">
    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert"><?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3>All Subjects</h3>
            <button onclick="openAddSubjectModal()" class="btn-primary">+ Add Subject</button>
        </div>
        <div class="card-body">
            <div class="subject-grid">
                <?php if(count($subjects) > 0): ?>
                    <?php foreach($subjects as $subj): ?>
                    <div class="subject-card">
                        <h3><?= htmlspecialchars($subj['name']) ?></h3>
                        <div style="margin-top:15px;">
                            <a href="admin_sections.php?subject_id=<?= $subj['id'] ?>" class="btn-primary btn-sm">View Sections</a>
                            <button onclick="editSubject(<?= $subj['id'] ?>, '<?= htmlspecialchars($subj['name']) ?>', <?= $subj['required_hours'] ?>)" class="btn-warning btn-sm">Edit</button>
                            <a href="?delete=<?= $subj['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Delete this subject?')">Delete</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center; padding:40px; color:#888;">No subjects yet. Click "Add Subject".</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Subject Modal -->
<div id="addSubjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Add New Subject</h3></div>
        <div class="modal-body">
            <form method="POST">
                <input type="text" name="subject_name" placeholder="Subject Name" required>
                <input type="number" name="required_hours" placeholder="Required Hours" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeAddSubjectModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="add_subject" class="btn-primary">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div id="editSubjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Edit Subject</h3></div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="subject_id" id="edit_subject_id">
                <input type="text" name="subject_name" id="edit_subject_name" required>
                <input type="number" name="required_hours" id="edit_required_hours" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeEditSubjectModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="edit_subject" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddSubjectModal() {
    document.getElementById('addSubjectModal').style.display = 'flex';
}
function closeAddSubjectModal() {
    document.getElementById('addSubjectModal').style.display = 'none';
}
function editSubject(id, name, hours) {
    document.getElementById('edit_subject_id').value = id;
    document.getElementById('edit_subject_name').value = name;
    document.getElementById('edit_required_hours').value = hours;
    document.getElementById('editSubjectModal').style.display = 'flex';
}
function closeEditSubjectModal() {
    document.getElementById('editSubjectModal').style.display = 'none';
}
window.onclick = function(e) {
    if(e.target === document.getElementById('addSubjectModal')) closeAddSubjectModal();
    if(e.target === document.getElementById('editSubjectModal')) closeEditSubjectModal();
}
</script>
</body></html>