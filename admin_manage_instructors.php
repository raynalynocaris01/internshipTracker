<?php
require_once 'config.php';
if (!isAdmin()) { header("Location: login.php"); exit; }

$scroll_to = isset($_GET['scroll_to']) ? (int)$_GET['scroll_to'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle Add Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $name = trim($_POST['name']);
    $hours = intval($_POST['required_hours']);
    if ($name && $hours > 0) {
        $pdo->prepare("INSERT INTO subjects (name, required_hours) VALUES (?, ?)")->execute([$name, $hours]);
        $_SESSION['msg'] = "Subject added successfully!";
    }
    header("Location: admin_manage_instructors.php");
    exit();
}

// Handle Add Section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_section'])) {
    $name = trim($_POST['section_name']);
    $subject_id = intval($_POST['subject_id']);
    if ($name && $subject_id) {
        $pdo->prepare("INSERT INTO sections (name, subject_id) VALUES (?, ?)")->execute([$name, $subject_id]);
        $_SESSION['msg'] = "Section added successfully!";
    }
    header("Location: admin_manage_instructors.php");
    exit();
}

// Add Instructor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_instructor'])) {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role, ref_id) VALUES (?, ?, ?, 'instructor', NULL)");
    $stmt->execute([$full_name, $username, $password]);
    $_SESSION['msg'] = "Instructor added. Username: $username, Name: $full_name";
    header("Location: admin_manage_instructors.php");
    exit;
}

// Edit Instructor Name
if (isset($_POST['edit_instructor'])) {
    $instructor_id = (int)$_POST['instructor_id'];
    $full_name = trim($_POST['full_name']);
    $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ? AND role = 'instructor'");
    $stmt->execute([$full_name, $instructor_id]);
    $_SESSION['msg'] = "Instructor name updated.";
    header("Location: admin_manage_instructors.php");
    exit;
}

// Edit Instructor Username
if (isset($_POST['edit_username'])) {
    $instructor_id = (int)$_POST['instructor_id'];
    $username = trim($_POST['username']);
    $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ? AND role = 'instructor'");
    $stmt->execute([$username, $instructor_id]);
    $_SESSION['msg'] = "Instructor username updated.";
    header("Location: admin_manage_instructors.php");
    exit;
}

// Reset Instructor Password
if (isset($_POST['reset_password'])) {
    $instructor_id = (int)$_POST['instructor_id'];
    $new_password = $_POST['new_password'];
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'instructor'");
    $stmt->execute([$hash, $instructor_id]);
    $_SESSION['msg'] = "Password reset successfully. New password: $new_password";
    header("Location: admin_manage_instructors.php");
    exit;
}

// Delete Instructor
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $pdo->prepare("DELETE FROM users WHERE id = ? AND role='instructor'")->execute([$id]);
    $_SESSION['msg'] = "Instructor deleted.";
    header("Location: admin_manage_instructors.php");
    exit;
}

// Multiple Assign Subjects
if (isset($_POST['assign_multiple_subjects']) && isset($_POST['instructor_id'])) {
    $instructor_id = (int)$_POST['instructor_id'];
    $selected_subjects = isset($_POST['selected_subjects']) ? $_POST['selected_subjects'] : [];
    foreach($selected_subjects as $subject_id) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO instructor_subjects (instructor_id, subject_id) VALUES (?, ?)");
        $stmt->execute([$instructor_id, $subject_id]);
    }
    $_SESSION['msg'] = "Subjects assigned to instructor.";
    header("Location: admin_manage_instructors.php?scroll_to=" . $instructor_id . "#inst_" . $instructor_id);
    exit;
}

// Remove Subject
if (isset($_GET['remove_subject']) && isset($_GET['instructor_id'])) {
    $instructor_id = (int)$_GET['instructor_id'];
    $subject_id = (int)$_GET['remove_subject'];
    $pdo->prepare("DELETE FROM instructor_subjects WHERE instructor_id = ? AND subject_id = ?")->execute([$instructor_id, $subject_id]);
    $_SESSION['msg'] = "Subject removed from instructor.";
    header("Location: admin_manage_instructors.php?scroll_to=" . $instructor_id . "#inst_" . $instructor_id);
    exit;
}

// Multiple Assign Sections
if (isset($_POST['assign_multiple_sections']) && isset($_POST['instructor_id'])) {
    $instructor_id = (int)$_POST['instructor_id'];
    $selected_sections = isset($_POST['selected_sections']) ? $_POST['selected_sections'] : [];
    foreach($selected_sections as $section_id) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO instructor_sections (instructor_id, section_id) VALUES (?, ?)");
        $stmt->execute([$instructor_id, $section_id]);
    }
    $_SESSION['msg'] = "Sections assigned to instructor.";
    header("Location: admin_manage_instructors.php?scroll_to=" . $instructor_id . "#inst_" . $instructor_id);
    exit;
}

// Remove Section
if (isset($_GET['remove_section']) && isset($_GET['instructor_id'])) {
    $instructor_id = (int)$_GET['instructor_id'];
    $section_id = (int)$_GET['remove_section'];
    $pdo->prepare("DELETE FROM instructor_sections WHERE instructor_id = ? AND section_id = ?")->execute([$instructor_id, $section_id]);
    $_SESSION['msg'] = "Section removed from instructor.";
    header("Location: admin_manage_instructors.php?scroll_to=" . $instructor_id . "#inst_" . $instructor_id);
    exit;
}

// Get all instructors with search filter
if (!empty($search)) {
    $instructors = $pdo->prepare("SELECT id, full_name, username FROM users WHERE role='instructor' AND (full_name LIKE ? OR username LIKE ?) ORDER BY full_name");
    $instructors->execute(["%$search%", "%$search%"]);
    $instructors = $instructors->fetchAll();
} else {
    $instructors = $pdo->query("SELECT id, full_name, username FROM users WHERE role='instructor' ORDER BY full_name")->fetchAll();
}

$subjects = $pdo->query("SELECT id, name FROM subjects ORDER BY name")->fetchAll();
$sections = $pdo->query("SELECT id, name FROM sections ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Instructors</title>
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
        
        .search-bar{padding:8px 16px;border-radius:40px;border:2px solid white;background:rgba(255,255,255,0.2);color:white;width:250px;}
        .search-bar::placeholder{color:rgba(255,255,255,0.7);}
        .search-bar:focus{outline:none;border-color:#ede432;background:rgba(255,255,255,0.3);}
        
        .instructor-card{background:#fef9e3;border-radius:20px;padding:20px;margin-bottom:20px;border-left:6px solid #e2362c;transition:all 0.3s;scroll-margin-top:80px;}
        .instructor-card:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,0,0,0.1);}
        .instructor-card h3{font-size:1.4rem;margin-bottom:10px;color:#216699;}
        .instructor-card p{margin:5px 0;color:#555;}
        .badge{background:#ede432;color:#1e293b;padding:4px 10px;border-radius:40px;font-size:0.75rem;display:inline-block;margin:3px;}
        
        .alert{background:#e6fffa;padding:12px 20px;margin:0 25px 20px 25px;border-radius:40px;border-left:4px solid #216699;color:#216699;}
        .flex-btns{display:flex;gap:12px;margin-top:15px;flex-wrap:wrap;align-items:center;}
        select{padding:8px 16px;border-radius:40px;border:2px solid #216699;font-size:0.9rem;background:white;}
        
        .assignment-section{margin-top:10px;padding-top:10px;border-top:1px solid #e2e8f0;}
        .assignment-title{font-weight:bold;color:#216699;margin:10px 0 5px 0;}
        .section-list{display:flex;flex-wrap:wrap;gap:5px;margin-top:5px;}
        
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;z-index:1000;}
        .modal-content{background:white;border-radius:28px;width:500px;max-width:90%;max-height:80%;overflow-y:auto;}
        .modal-header{background:#216699;padding:20px;border-bottom:3px solid #ede432;display:flex;justify-content:space-between;align-items:center;}
        .modal-header h3{color:white;margin:0;}
        .modal-header .close-modal{color:white;font-size:28px;font-weight:bold;cursor:pointer;background:transparent;border:none;}
        .modal-header .close-modal:hover{color:#ede432;}
        .modal-body{padding:25px;}
        .modal-body input[type="checkbox"]{width:auto;margin-right:10px;}
        .checkbox-group{max-height:300px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:20px;padding:15px;margin:10px 0;}
        .checkbox-group label{display:block;padding:8px 0;cursor:pointer;}
        .modal-body input[type="text"],.modal-body input[type="password"],.modal-body input[type="number"]{width:100%;padding:12px 16px;border:2px solid #216699;border-radius:40px;margin:10px 0;}
        .modal-body select{width:100%;padding:12px 16px;border:2px solid #216699;border-radius:40px;margin:10px 0;}
        
        .no-results{text-align:center;padding:40px;color:#888;font-size:1.1rem;}
        
        input, select, textarea, button {
            pointer-events: auto !important;
        }
    </style>
</head>
<body>
<div class="navbar">
    <div><h2>Internship Tracker <span class="badge-teacher">ADMIN - INSTRUCTORS</span></h2></div>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>

<div class="admin-nav">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_manage_instructors.php" class="active">Instructors</a>
    <a href="admin_subjects.php">Subjects</a>
    <a href="admin_sections.php">Sections</a>
    <a href="admin_students.php">Students</a>
</div>

<div class="container">
    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert"><?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header"><h3>Add New Instructor</h3></div>
        <div class="card-body">
            <form method="POST">
                <input type="text" name="full_name" placeholder="Full Name" required style="width:auto; display:inline-block; margin-right:10px; padding:8px 16px; border-radius:40px; border:2px solid #216699;">
                <input type="text" name="username" placeholder="Username" required style="width:auto; display:inline-block; margin-right:10px; padding:8px 16px; border-radius:40px; border:2px solid #216699;">
                <input type="password" name="password" placeholder="Password" required style="width:auto; display:inline-block; margin-right:10px; padding:8px 16px; border-radius:40px; border:2px solid #216699;">
                <button type="submit" name="add_instructor" class="btn-primary">Add Instructor</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Existing Instructors</h3>
            <form method="GET" style="margin:0;">
                <input type="text" name="search" class="search-bar" placeholder="Search by name or username..." value="<?= htmlspecialchars($search) ?>">
                <?php if(!empty($search)): ?>
                    <a href="admin_manage_instructors.php" class="btn-warning btn-sm" style="margin-left:5px;">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-body">
            <?php if(count($instructors) > 0): ?>
                <?php foreach($instructors as $inst): 
                    $assignedSubjects = $pdo->prepare("SELECT s.id, s.name FROM instructor_subjects ins JOIN subjects s ON ins.subject_id = s.id WHERE ins.instructor_id = ?");
                    $assignedSubjects->execute([$inst['id']]);
                    $subjList = $assignedSubjects->fetchAll();
                    
                    // FIXED: Get only section name (no subject name)
                    $assignedSections = $pdo->prepare("SELECT s.id, s.name FROM instructor_sections ins JOIN sections s ON ins.section_id = s.id WHERE ins.instructor_id = ? ORDER BY s.name");
                    $assignedSections->execute([$inst['id']]);
                    $secList = $assignedSections->fetchAll();
                ?>
                <div class="instructor-card" id="inst_<?= $inst['id'] ?>">
                    <h3><?= htmlspecialchars($inst['full_name'] ?? $inst['username']) ?></h3>
                    <p><strong>Username:</strong> <?= htmlspecialchars($inst['username']) ?></p>
                    
                    <div class="flex-btns" style="margin-bottom:10px;">
                        <button type="button" onclick="editInstructor(<?= $inst['id'] ?>, '<?= htmlspecialchars(addslashes($inst['full_name'] ?? $inst['username'])) ?>')" class="btn-warning btn-sm">Edit Name</button>
                        <button type="button" onclick="editUsername(<?= $inst['id'] ?>, '<?= htmlspecialchars($inst['username']) ?>')" class="btn-warning btn-sm">Edit Username</button>
                        <button type="button" onclick="resetPassword(<?= $inst['id'] ?>)" class="btn-warning btn-sm">Reset Password</button>
                        <a href="?delete_id=<?= $inst['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Delete this instructor?')">Delete Instructor</a>
                    </div>
                    
                    <div class="assignment-section">
                        <div class="assignment-title">Assigned Subjects (Multiple allowed):</div>
                        <div class="section-list">
                            <?php if(count($subjList) > 0): ?>
                                <?php foreach($subjList as $as): ?>
                                    <span class="badge"><?= htmlspecialchars($as['name']) ?> 
                                        <a href="?remove_subject=<?= $as['id'] ?>&instructor_id=<?= $inst['id'] ?>" style="color:#e2362c; text-decoration:none; margin-left:5px;">✕</a>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?> 
                                <span style="color:gray;">No subjects assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- FIXED: Show ONLY section name (no subject prefix) -->
                    <div class="assignment-section">
                        <div class="assignment-title">Assigned Sections (Multiple allowed):</div>
                        <div class="section-list">
                            <?php if(count($secList) > 0): ?>
                                <?php foreach($secList as $as): ?>
                                    <span class="badge"><?= htmlspecialchars($as['name']) ?>
                                        <a href="?remove_section=<?= $as['id'] ?>&instructor_id=<?= $inst['id'] ?>" style="color:#e2362c; text-decoration:none; margin-left:5px;">✕</a>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?> 
                                <span style="color:gray;">No sections assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex-btns">
                        <button type="button" onclick="openAssignSubjectsModal(<?= $inst['id'] ?>)" class="btn-primary btn-sm">Assign Multiple Subjects</button>
                        <button type="button" onclick="openAssignSectionsModal(<?= $inst['id'] ?>)" class="btn-primary btn-sm">Assign Multiple Sections</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <?php if(!empty($search)): ?>
                        No instructors found matching "<strong><?= htmlspecialchars($search) ?></strong>".
                        <br><a href="admin_manage_instructors.php" class="btn-warning btn-sm" style="margin-top:10px; display:inline-block;">Clear Search</a>
                    <?php else: ?>
                        No instructors yet. Click "Add Instructor" to create one.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Name Modal -->
<div id="editNameModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Edit Instructor Name</h3><button class="close-modal" onclick="closeEditNameModal()">&times;</button></div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="instructor_id" id="edit_name_id">
                <label>Full Name:</label>
                <input type="text" name="full_name" id="edit_full_name" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeEditNameModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="edit_instructor" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Username Modal -->
<div id="editUsernameModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Edit Instructor Username</h3><button class="close-modal" onclick="closeEditUsernameModal()">&times;</button></div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="instructor_id" id="edit_username_id">
                <label>Username:</label>
                <input type="text" name="username" id="edit_username" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeEditUsernameModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="edit_username" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Reset Instructor Password</h3><button class="close-modal" onclick="closeResetPasswordModal()">&times;</button></div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="instructor_id" id="reset_id">
                <label>New Password:</label>
                <input type="password" name="new_password" id="new_password" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeResetPasswordModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="reset_password" class="btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Multiple Subjects Modal -->
<div id="assignSubjectsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Assign Multiple Subjects</h3><button class="close-modal" onclick="closeAssignSubjectsModal()">&times;</button></div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="instructor_id" id="assign_subjects_instructor_id">
                <label>Select Subjects to Assign:</label>
                <div class="checkbox-group">
                    <?php foreach($subjects as $subj): ?>
                        <label><input type="checkbox" name="selected_subjects[]" value="<?= $subj['id'] ?>"> <?= htmlspecialchars($subj['name']) ?></label>
                    <?php endforeach; ?>
                    <?php if(count($subjects) == 0): ?>
                        <p style="color:#999;">No subjects available. <a href="#" onclick="openAddSubjectModal(); return false;">Click here to add a subject</a></p>
                    <?php endif; ?>
                </div>
                <div class="flex-btns">
                    <button type="button" onclick="closeAssignSubjectsModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="assign_multiple_subjects" class="btn-primary">Assign Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Multiple Sections Modal (SECTION NAME ONLY) -->
<div id="assignSectionsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Assign Multiple Sections</h3><button class="close-modal" onclick="closeAssignSectionsModal()">&times;</button></div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="instructor_id" id="assign_sections_instructor_id">
                <label>Select Sections to Assign:</label>
                <div class="checkbox-group">
                    <?php foreach($sections as $sec): ?>
                        <label><input type="checkbox" name="selected_sections[]" value="<?= $sec['id'] ?>"> <?= htmlspecialchars($sec['name']) ?></label>
                    <?php endforeach; ?>
                    <?php if(count($sections) == 0): ?>
                        <p style="color:#999;">No sections available. <a href="#" onclick="openAddSectionModal(); return false;">Click here to add a section</a></p>
                    <?php endif; ?>
                </div>
                <div class="flex-btns">
                    <button type="button" onclick="closeAssignSectionsModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="assign_multiple_sections" class="btn-primary">Assign Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Subject Modal -->
<div id="addSubjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Add New Subject</h3><button class="close-modal" onclick="closeAddSubjectModal()">&times;</button></div>
        <div class="modal-body">
            <form method="POST">
                <label>Subject Name:</label>
                <input type="text" name="name" required>
                <label>Required Hours:</label>
                <input type="number" name="required_hours" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeAddSubjectModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="add_subject" class="btn-primary">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div id="addSectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Add New Section</h3><button class="close-modal" onclick="closeAddSectionModal()">&times;</button></div>
        <div class="modal-body">
            <form method="POST">
                <label>Select Subject:</label>
                <select name="subject_id" required>
                    <option value="">-- Select Subject --</option>
                    <?php foreach($subjects as $sub): ?>
                        <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Section Name:</label>
                <input type="text" name="section_name" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeAddSectionModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="add_section" class="btn-primary">Add Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAssignSubjectsModal(id) {
    document.getElementById('assign_subjects_instructor_id').value = id;
    document.getElementById('assignSubjectsModal').style.display = 'flex';
}
function closeAssignSubjectsModal() { document.getElementById('assignSubjectsModal').style.display = 'none'; }

function openAssignSectionsModal(id) {
    document.getElementById('assign_sections_instructor_id').value = id;
    document.getElementById('assignSectionsModal').style.display = 'flex';
}
function closeAssignSectionsModal() { document.getElementById('assignSectionsModal').style.display = 'none'; }

function editInstructor(id, name) {
    document.getElementById('edit_name_id').value = id;
    document.getElementById('edit_full_name').value = name;
    document.getElementById('editNameModal').style.display = 'flex';
}
function closeEditNameModal() { document.getElementById('editNameModal').style.display = 'none'; }

function editUsername(id, username) {
    document.getElementById('edit_username_id').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('editUsernameModal').style.display = 'flex';
}
function closeEditUsernameModal() { document.getElementById('editUsernameModal').style.display = 'none'; }

function resetPassword(id) {
    document.getElementById('reset_id').value = id;
    document.getElementById('resetPasswordModal').style.display = 'flex';
}
function closeResetPasswordModal() { document.getElementById('resetPasswordModal').style.display = 'none'; }

function openAddSubjectModal() {
    document.getElementById('addSubjectModal').style.display = 'flex';
}
function closeAddSubjectModal() { document.getElementById('addSubjectModal').style.display = 'none'; }

function openAddSectionModal() {
    document.getElementById('addSectionModal').style.display = 'flex';
}
function closeAddSectionModal() { document.getElementById('addSectionModal').style.display = 'none'; }

window.onclick = function(e) {
    if(e.target === document.getElementById('editNameModal')) closeEditNameModal();
    if(e.target === document.getElementById('editUsernameModal')) closeEditUsernameModal();
    if(e.target === document.getElementById('resetPasswordModal')) closeResetPasswordModal();
    if(e.target === document.getElementById('assignSubjectsModal')) closeAssignSubjectsModal();
    if(e.target === document.getElementById('assignSectionsModal')) closeAssignSectionsModal();
    if(e.target === document.getElementById('addSubjectModal')) closeAddSubjectModal();
    if(e.target === document.getElementById('addSectionModal')) closeAddSectionModal();
};

<?php if($scroll_to > 0): ?>
window.onload = function() {
    var element = document.getElementById('inst_<?= $scroll_to ?>');
    if(element) element.scrollIntoView({ behavior: 'smooth', block: 'center' });
};
<?php endif; ?>
</script>
</body></html>