<?php
require_once 'config.php';
if (!isAdmin()) { header("Location: login.php"); exit; }

// Add Section (NO SUBJECT SELECTION - just section name)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_section'])) {
    $name = trim($_POST['section_name']);
    if (!empty($name)) {
        // You need to assign a default subject or handle this differently
        // For now, let's get the first subject or you can set a default subject_id
        $default_subject = $pdo->query("SELECT id FROM subjects LIMIT 1")->fetch();
        if ($default_subject) {
            $pdo->prepare("INSERT INTO sections (name, subject_id) VALUES (?, ?)")->execute([$name, $default_subject['id']]);
            $_SESSION['msg'] = "Section added successfully!";
        } else {
            $_SESSION['msg'] = "Please create a subject first before adding sections.";
        }
    }
    header("Location: admin_sections.php");
    exit();
}

// Edit Section
if (isset($_POST['edit_section'])) {
    $section_id = (int)$_POST['section_id'];
    $name = trim($_POST['section_name']);
    $pdo->prepare("UPDATE sections SET name = ? WHERE id = ?")->execute([$name, $section_id]);
    $_SESSION['msg'] = "Section updated successfully!";
    header("Location: admin_sections.php");
    exit();
}

// Delete Section
if (isset($_GET['delete_section'])) {
    $delete_id = (int)$_GET['delete_section'];
    $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE section_id = ?");
    $check->execute([$delete_id]);
    $count = $check->fetchColumn();
    if ($count == 0) {
        $pdo->prepare("DELETE FROM sections WHERE id = ?")->execute([$delete_id]);
        $_SESSION['msg'] = "Section deleted successfully!";
    } else {
        $_SESSION['msg'] = "Cannot delete section. It has $count student(s) assigned.";
    }
    header("Location: admin_sections.php");
    exit();
}

// Get ALL sections
$sections = $pdo->query("SELECT s.* FROM sections s ORDER BY s.name")->fetchAll();
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Sections</title>
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
        
        .section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;}
        .section-card{background:#fef9e3;border-radius:20px;padding:20px;border-left:6px solid #e2362c;}
        .section-card h3{font-size:1.3rem;margin-bottom:10px;color:#216699;}
        
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;z-index:1000;}
        .modal-content{background:white;border-radius:28px;width:400px;overflow:hidden;}
        .modal-header{background:#216699;padding:20px;border-bottom:3px solid #ede432;}
        .modal-header h3{color:white;}
        .modal-body{padding:25px;}
        .modal-body input{width:100%;padding:12px 16px;border:2px solid #216699;border-radius:40px;margin:10px 0;}
        .flex-btns{display:flex;gap:12px;justify-content:flex-end;margin-top:20px;}
        .alert{background:#e6fffa;padding:12px 20px;margin-bottom:20px;border-radius:40px;border-left:4px solid #216699;color:#216699;}
        .warning-text{color:#e2362c;font-size:0.8rem;margin-top:5px;}
    </style>
</head>
<body>
<div class="navbar">
    <div><h2>Internship Tracker <span class="badge-admin">ADMIN - SECTIONS</span></h2></div>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>

<div class="admin-nav">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_manage_instructors.php">Instructors</a>
    <a href="admin_subjects.php">Subjects</a>
    <a href="admin_sections.php" class="active">Sections</a>
    <a href="admin_students.php">Students</a>
</div>

<div class="container">
    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert"><?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3>All Sections</h3>
            <button onclick="openAddSectionModal()" class="btn-primary">+ Add Section</button>
        </div>
        <div class="card-body">
            <div class="section-grid">
                <?php if(count($sections) > 0): ?>
                    <?php foreach($sections as $sec): ?>
                    <div class="section-card">
                        <h3><?= htmlspecialchars($sec['name']) ?></h3>
                        <div style="margin-top:15px;">
                            <button onclick="editSection(<?= $sec['id'] ?>, '<?= htmlspecialchars($sec['name']) ?>')" class="btn-warning btn-sm">Edit</button>
                            <a href="?delete_section=<?= $sec['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Delete this section?')">Delete</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align:center; padding:40px; color:#888;">No sections yet. Click "Add Section".</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Section Modal - NO SUBJECT DROPDOWN -->
<div id="addSectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Add New Section</h3></div>
        <div class="modal-body">
            <form method="POST">
                <input type="text" name="section_name" placeholder="Section Name" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeAddSectionModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="add_section" class="btn-primary">Add Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Section Modal -->
<div id="editSectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Edit Section</h3></div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="section_id" id="edit_section_id">
                <input type="text" name="section_name" id="edit_section_name" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeEditSectionModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="edit_section" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddSectionModal() {
    document.getElementById('addSectionModal').style.display = 'flex';
}
function closeAddSectionModal() {
    document.getElementById('addSectionModal').style.display = 'none';
}
function editSection(id, name) {
    document.getElementById('edit_section_id').value = id;
    document.getElementById('edit_section_name').value = name;
    document.getElementById('editSectionModal').style.display = 'flex';
}
function closeEditSectionModal() {
    document.getElementById('editSectionModal').style.display = 'none';
}
window.onclick = function(e) {
    if(e.target === document.getElementById('addSectionModal')) closeAddSectionModal();
    if(e.target === document.getElementById('editSectionModal')) closeEditSectionModal();
}
</script>
</body></html>