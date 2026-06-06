<?php
require_once 'config.php';
if ($_SESSION['role'] !== 'teacher' && $_SESSION['role'] !== 'instructor') die("Access denied");
$section_id = (int)$_GET['section_id'];
$section = $pdo->prepare("SELECT s.*, subj.name as subject_name, subj.required_hours, subj.id as subject_id FROM sections s JOIN subjects subj ON s.subject_id = subj.id WHERE s.id = ?");
$section->execute([$section_id]);
$sec = $section->fetch();
if(!$sec) die("Section not found");

function hasUserAccount($pdo, $student_id) {
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE role='student' AND ref_id = ?");
    $stmt->execute([$student_id]);
    return $stmt->fetch();
}

// Get instructor name for navbar
$instructor_id = $_SESSION['user_id'];
$nameStmt = $pdo->prepare("SELECT full_name, username FROM users WHERE id = ?");
$nameStmt->execute([$instructor_id]);
$instructor = $nameStmt->fetch();
$displayName = $instructor['full_name'] ?? $instructor['username'];

// Edit Subject (Name and Hours)
if(isset($_POST['edit_subject'])) {
    $subject_id = (int)$_POST['subject_id'];
    $subject_name = trim($_POST['subject_name']);
    $required_hours = (int)$_POST['required_hours'];
    if(!empty($subject_name) && $required_hours > 0) {
        $pdo->prepare("UPDATE subjects SET name = ?, required_hours = ? WHERE id = ?")->execute([$subject_name, $required_hours, $subject_id]);
        $_SESSION['msg'] = "Subject updated successfully!";
    } else {
        $_SESSION['msg'] = "Please enter valid subject name and hours.";
    }
    header("Location: teacher_students.php?section_id=$section_id");
    exit;
}

// Create login
if(isset($_GET['create_login']) && is_numeric($_GET['create_login'])) {
    $student_id = (int)$_GET['create_login'];
    $check = $pdo->prepare("SELECT id, name FROM students WHERE id = ? AND section_id = ?");
    $check->execute([$student_id, $section_id]);
    $student = $check->fetch();
    if($student) {
        if(!hasUserAccount($pdo, $student_id)) {
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '.', $student['name'])) . rand(100,999);
            $default_password = 'student123';
            $hash = password_hash($default_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, ref_id) VALUES (?, ?, 'student', ?)");
            $stmt->execute([$username, $hash, $student_id]);
            $_SESSION['msg'] = "Login created. Username: $username, Password: $default_password";
        } else {
            $_SESSION['msg'] = "Student already has a login account.";
        }
    }
    header("Location: teacher_students.php?section_id=$section_id");
    exit;
}

// Delete login
if(isset($_GET['delete_login']) && is_numeric($_GET['delete_login'])) {
    $student_id = (int)$_GET['delete_login'];
    $pdo->prepare("DELETE FROM users WHERE role='student' AND ref_id = ?")->execute([$student_id]);
    $_SESSION['msg'] = "Student login removed.";
    header("Location: teacher_students.php?section_id=$section_id");
    exit;
}

// Edit username
if(isset($_POST['edit_username']) && isset($_POST['student_id']) && isset($_POST['new_username'])) {
    $student_id = (int)$_POST['student_id'];
    $new_username = trim($_POST['new_username']);
    if(!empty($new_username)) {
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE role='student' AND ref_id = ?");
        $stmt->execute([$new_username, $student_id]);
        $_SESSION['msg'] = "Username updated to: $new_username";
    } else {
        $_SESSION['msg'] = "Username cannot be empty.";
    }
    header("Location: teacher_students.php?section_id=$section_id");
    exit;
}

// Edit password
if(isset($_POST['edit_password']) && isset($_POST['student_id']) && isset($_POST['new_password'])) {
    $student_id = (int)$_POST['student_id'];
    $new_password = $_POST['new_password'];
    if(strlen($new_password) >= 4) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE role='student' AND ref_id = ?");
        $stmt->execute([$hash, $student_id]);
        $_SESSION['msg'] = "Password updated successfully.";
    } else {
        $_SESSION['msg'] = "Password must be at least 4 characters.";
    }
    header("Location: teacher_students.php?section_id=$section_id");
    exit;
}

// Edit student name
if(isset($_POST['update_student'])) {
    $id = (int)$_POST['student_id'];
    $name = trim($_POST['student_name']);
    $pdo->prepare("UPDATE students SET name = ? WHERE id = ? AND section_id = ?")->execute([$name, $id, $section_id]);
    $_SESSION['msg'] = "Student name updated.";
    header("Location: teacher_students.php?section_id=$section_id");
    exit;
}

// Delete student
if(isset($_POST['delete_student'])) {
    $id = (int)$_POST['student_id'];
    $pdo->prepare("DELETE FROM users WHERE role='student' AND ref_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM students WHERE id = ? AND section_id = ?")->execute([$id, $section_id]);
    $_SESSION['msg'] = "Student deleted.";
    header("Location: teacher_students.php?section_id=$section_id");
    exit;
}

// Add student
if(isset($_POST['add_student'])) {
    $name = trim($_POST['student_name']);
    $pdo->prepare("INSERT INTO students (name, section_id) VALUES (?, ?)")->execute([$name, $section_id]);
    $_SESSION['msg'] = "Student added.";
    header("Location: teacher_students.php?section_id=$section_id");
    exit;
}

// Save attendance
if(isset($_POST['save_attendance'])) {
    $date = $_POST['att_date'];
    $modal_month = $_POST['modal_month'];
    foreach($_POST['student_id'] as $idx => $sid) {
        $morning_in = !empty($_POST['morning_in'][$idx]) ? $_POST['morning_in'][$idx] : null;
        $morning_out = !empty($_POST['morning_out'][$idx]) ? $_POST['morning_out'][$idx] : null;
        $afternoon_in = !empty($_POST['afternoon_in'][$idx]) ? $_POST['afternoon_in'][$idx] : null;
        $afternoon_out = !empty($_POST['afternoon_out'][$idx]) ? $_POST['afternoon_out'][$idx] : null;
        
        $morning_hours = 0;
        $afternoon_hours = 0;
        if($morning_in && $morning_out) {
            $morning_hours = round((strtotime($morning_out) - strtotime($morning_in)) / 3600, 2);
        }
        if($afternoon_in && $afternoon_out) {
            $afternoon_hours = round((strtotime($afternoon_out) - strtotime($afternoon_in)) / 3600, 2);
        }
        $total = $morning_hours + $afternoon_hours;
        
        $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, morning_in, morning_out, afternoon_in, afternoon_out, total_hours) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE morning_in=VALUES(morning_in), morning_out=VALUES(morning_out), afternoon_in=VALUES(afternoon_in), afternoon_out=VALUES(afternoon_out), total_hours=VALUES(total_hours)");
        $stmt->execute([$sid, $date, $morning_in, $morning_out, $afternoon_in, $afternoon_out, $total]);
    }
    $_SESSION['msg'] = "Attendance saved for $date";
    $scroll_pos = isset($_POST['scroll_pos']) ? (int)$_POST['scroll_pos'] : 0;
    header("Location: teacher_students.php?section_id=$section_id&modal_month=$modal_month&modal_date=$date&stay=1&scroll=$scroll_pos");
    exit;
}

$students = $pdo->prepare("SELECT * FROM students WHERE section_id = ? ORDER BY name ASC");
$students->execute([$section_id]);
$studentList = $students->fetchAll();
$today = date('Y-m-d');

$modal_month = isset($_GET['modal_month']) ? $_GET['modal_month'] : date('Y-m');
$modal_selected_date = isset($_GET['modal_date']) ? $_GET['modal_date'] : $today;
$display_month = new DateTime($modal_month . '-01');
$prev_month = clone $display_month;
$prev_month->modify('-1 month');
$next_month = clone $display_month;
$next_month->modify('+1 month');

$show_modal = isset($_GET['modal_month']) || isset($_GET['modal_date']) || isset($_GET['stay']);
$scroll_position = isset($_GET['scroll']) ? (int)$_GET['scroll'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - <?= htmlspecialchars($sec['name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Teacher Navigation Bar */
        .teacher-nav{background:#1a4f77;padding:0.8rem 2rem;display:flex;justify-content:center;gap:20px;flex-wrap:wrap;border-bottom:1px solid #ede432;}
        .teacher-nav a{background:transparent;color:white;text-decoration:none;padding:8px 20px;border-radius:40px;font-weight:bold;transition:all 0.3s;}
        .teacher-nav a:hover{background:#ede432;color:#1a4f77;}
        .teacher-nav a.active{background:#ede432;color:#1a4f77;}
        
        .button-group{display:flex;justify-content:center;gap:30px;margin:30px 0;}
        .btn-middle{padding:12px 40px;font-size:1.2rem;border-radius:40px;font-weight:bold;cursor:pointer;transition:all 0.2s ease;border:none;}
        .btn-yellow{background:#ede432;color:#1e293b;}
        .btn-yellow:hover{background:#d4cc2a;transform:scale(1.02);}
        
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);justify-content:center;align-items:center;z-index:1000;}
        .modal-content{background:white;border-radius:28px;width:90%;max-width:1300px;max-height:85%;overflow-y:auto;}
        .modal-header{background:#216699;padding:20px;border-bottom:3px solid #ede432;position:relative;text-align:center;}
        .modal-header h2,.modal-header h3{color:white;margin:0;}
        .close-modal{position:absolute;top:15px;right:20px;font-size:28px;font-weight:bold;cursor:pointer;color:#e2362c;background:transparent;width:30px;height:30px;display:flex;align-items:center;justify-content:center;line-height:1;}
        .close-modal:hover{color:#c92a20;transform:scale(1.1);}
        
        .calendar{display:grid;grid-template-columns:repeat(7,1fr);gap:8px;margin:20px 0;}
        .calendar-day{background:#f1f5f9;border-radius:12px;padding:12px 6px;text-align:center;cursor:pointer;transition:0.2s;font-weight:500;}
        .calendar-day:hover{background:#ede432;transform:scale(1.02);}
        .calendar-day.selected{background:#216699;color:white;}
        .calendar-day.empty{background:transparent;cursor:default;}
        .calendar-weekday{font-weight:bold;background:#e2e8f0;text-align:center;padding:8px;border-radius:12px;color:#216699;}
        
        .month-nav{display:flex;justify-content:center;align-items:center;gap:20px;margin:15px 0;}
        .month-nav button{background:#ede432;color:#1e293b;border:none;width:40px;height:40px;border-radius:50%;cursor:pointer;font-weight:bold;font-size:1.2rem;}
        .month-nav button:hover{background:#d4cc2a;}
        .month-nav span{font-size:1.3rem;font-weight:bold;color:#216699;}
        
        .attendance-table{width:100%;border-collapse:collapse;margin-top:20px;}
        .attendance-table th,.attendance-table td{border:1px solid #ddd;padding:10px;text-align:left;}
        .attendance-table th{background-color:#216699;color:white;}
        .attendance-table input{width:110px;padding:6px;border:2px solid #216699;border-radius:40px;}
        .attendance-table input:focus{outline:none;border-color:#e2362c;}
        .total-hours{background:#e6f7ff;padding:6px 12px;border-radius:40px;display:inline-block;text-align:center;font-weight:bold;border:none;width:80px;}
        
        .subject-info{text-align:center;margin-bottom:20px;}
        .subject-info h1{font-size:2rem;margin-bottom:5px;color:#216699;}
        .subject-info p{font-size:1.2rem;color:#e2362c;}
        
        .edit-btn{background:#ede432;color:#1e293b;border:none;padding:8px 20px;border-radius:40px;cursor:pointer;font-weight:bold;margin-left:20px;transition:all 0.2s;}
        .edit-btn:hover{background:#d4cc2a;transform:scale(1.02);}
        
        .alert{background:#e6fffa;padding:12px 20px;margin-bottom:20px;border-radius:40px;border-left:4px solid #216699;}
        .empty-message{text-align:center;padding:40px;color:#888;}
        .flex-btns{display:flex;gap:12px;justify-content:flex-end;margin-top:20px;}
    </style>
</head>
<body>
<div class="navbar">
    <div><h2>Internship Tracker <span class="badge-teacher">INSTRUCTOR: <?= htmlspecialchars($displayName) ?></span></h2></div>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>

<!-- NAVBAR - ONLY 4 LINKS -->
<div class="teacher-nav">
    <a href="teacher_dashboard.php">Dashboard</a>
    <a href="teacher_subjects.php">Subjects</a>
    <a href="teacher_sections.php">Sections</a>
    <a href="teacher_students_list.php">Students</a>
</div>

<div class="container">
    <?php if(isset($_SESSION['msg'])): ?>
        <div class="alert"><?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; padding: 20px 25px;">
            <div class="subject-info" style="flex:1; text-align:center;">
                <h1><?= htmlspecialchars($sec['subject_name']) ?></h1>
                <p>Required Hours: <strong><?= $sec['required_hours'] ?></strong> hrs</p>
            </div>
            <div>
                <button onclick="openEditSubjectModal()" class="edit-btn">Edit</button>
            </div>
        </div>
    </div>
    
    <div class="button-group">
        <button id="openStudentModalBtn" class="btn-middle btn-yellow">+ Add Student</button>
        <button onclick="openAttendanceModal()" class="btn-middle btn-yellow">View Attendance</button>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>Students (Alphabetical)</h3>
            <div>
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search student name..." class="search-bar">
            </div>
        </div>
        <div class="card-body">
            <table id="studentTable" class="student-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Login Account</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter=1; foreach($studentList as $stu): $user = hasUserAccount($pdo, $stu['id']); ?>
                    <tr id="student_row_<?= $stu['id'] ?>">
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($stu['name']) ?></td>
                        <td>
                            <?php if($user): ?>
                                <span style="color:green;">Username: <?= htmlspecialchars($user['username']) ?></span><br>
                                <a href="?section_id=<?= $section_id ?>&delete_login=<?= $stu['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Remove login?')">Remove Login</a>
                                <button type="button" onclick="editUsername(<?= $stu['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')" class="btn-warning btn-sm">Edit User</button>
                                <button type="button" onclick="editPassword(<?= $stu['id'] ?>)" class="btn-warning btn-sm">Edit Pass</button>
                            <?php else: ?>
                                <span style="color:gray;">No account</span>
                                <a href="?section_id=<?= $section_id ?>&create_login=<?= $stu['id'] ?>" class="btn-primary btn-sm">Create Login</a>
                            <?php endif; ?>
                         </span><input type="hidden" name="total_hours[]" value="<?= $total ?>">
                         </span><input type="hidden" name="total_hours[]" value="<?= $total ?>">
                        </td>
                        <td>
                            <button type="button" onclick="editStudent(<?= $stu['id'] ?>, '<?= htmlspecialchars($stu['name']) ?>')" class="btn-warning btn-sm">Update</button>
                            <button type="button" onclick="deleteStudent(<?= $stu['id'] ?>)" class="btn-danger btn-sm">Delete</button>
                         </span><input type="hidden" name="total_hours[]" value="<?= $total ?>">
                     </span><input type="hidden" name="total_hours[]" value="<?= $total ?>">
                    <?php endforeach; ?>
                    <?php if(count($studentList)==0): ?>
                        <tr><td colspan="4" class="empty-message">No students yet. Click "Add Student".</span><input type="hidden" name="total_hours[]" value="<?= $total ?>"></td><?
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div id="studentModal" class="modal">
    <div class="modal-content" style="width:400px;">
        <div class="modal-header"><h3>Add New Student</h3><span class="close-modal" onclick="closeStudentModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST">
                <input type="text" name="student_name" placeholder="Full Name" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeStudentModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="add_student" class="btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="modal">
    <div class="modal-content" style="width:400px;">
        <div class="modal-header"><h3>Edit Student Name</h3><span class="close-modal" onclick="closeEditStudentModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="student_id" id="edit_student_id">
                <input type="text" name="student_name" id="edit_student_name" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeEditStudentModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="update_student" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Student Form -->
<form id="deleteStudentForm" method="POST" style="display:none;">
    <input type="hidden" name="student_id" id="delete_student_id">
    <input type="hidden" name="delete_student" value="1">
</form>

<!-- Edit Subject Modal -->
<div id="editSubjectModal" class="modal">
    <div class="modal-content" style="width:400px;">
        <div class="modal-header"><h3>Edit Subject</h3><span class="close-modal" onclick="closeEditSubjectModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="subject_id" value="<?= $sec['subject_id'] ?>">
                <label>Subject Name:</label>
                <input type="text" name="subject_name" value="<?= htmlspecialchars($sec['subject_name']) ?>" required>
                <label>Required Hours:</label>
                <input type="number" name="required_hours" value="<?= $sec['required_hours'] ?>" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeEditSubjectModal()" class="btn-danger">Cancel</button>
                    <button type="submit" name="edit_subject" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div id="attendanceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Record Attendance</h2>
            <span class="close-modal" onclick="closeAttendanceModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="month-nav">
                <button onclick="goToMonth('<?= $prev_month->format('Y-m') ?>')">◀</button>
                <span><?= $display_month->format('F Y') ?></span>
                <button onclick="goToMonth('<?= $next_month->format('Y-m') ?>')">▶</button>
            </div>
            
            <?php
            $calendarYear = $display_month->format('Y');
            $calendarMonth = $display_month->format('m');
            $firstDayOfMonth = new DateTime("$calendarYear-$calendarMonth-01");
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $calendarMonth, $calendarYear);
            $startDayOfWeek = (int)$firstDayOfMonth->format('N');
            $calendarDays = [];
            for($i = 1; $i < $startDayOfWeek; $i++) $calendarDays[] = null;
            for($d = 1; $d <= $daysInMonth; $d++) $calendarDays[] = "$calendarYear-$calendarMonth-" . str_pad($d, 2, '0', STR_PAD_LEFT);
            $weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            ?>
            
            <div class="calendar">
                <?php foreach($weekdays as $wd): ?>
                    <div class="calendar-weekday"><?= $wd ?></div>
                <?php endforeach; ?>
                <?php foreach($calendarDays as $dateStr): ?>
                    <?php if($dateStr === null): ?>
                        <div class="calendar-day empty"></div>
                    <?php else: ?>
                        <?php $dayNum = (int)substr($dateStr, -2); $isSelected = ($dateStr === $modal_selected_date); ?>
                        <div class="calendar-day <?= $isSelected ? 'selected' : '' ?>" onclick="selectDate('<?= $dateStr ?>')">
                            <?= $dayNum ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <form method="POST" id="attendanceForm">
                <input type="hidden" name="att_date" id="att_date" value="<?= $modal_selected_date ?>">
                <input type="hidden" name="modal_month" value="<?= $modal_month ?>">
                <input type="hidden" name="scroll_pos" id="scroll_pos" value="0">
                
                <h3 style="color:#216699;">Time In/Out for <?= date('F d, Y', strtotime($modal_selected_date)) ?></h3>
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Morning In</th>
                            <th>Morning Out</th>
                            <th>Afternoon In</th>
                            <th>Afternoon Out</th>
                            <th>Total Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $idx = 1; foreach($studentList as $stu): 
                            $att = $pdo->prepare("SELECT * FROM attendance WHERE student_id = ? AND date = ?");
                            $att->execute([$stu['id'], $modal_selected_date]);
                            $record = $att->fetch();
                            $morning_in = $record['morning_in'] ?? '';
                            $morning_out = $record['morning_out'] ?? '';
                            $afternoon_in = $record['afternoon_in'] ?? '';
                            $afternoon_out = $record['afternoon_out'] ?? '';
                            $total = $record['total_hours'] ?? '0';
                        ?>
                        <tr>
                            <td><?= $idx++ ?></td>
                            <td><?= htmlspecialchars($stu['name']) ?><input type="hidden" name="student_id[]" value="<?= $stu['id'] ?>"></td>
                            <td><input type="time" name="morning_in[]" value="<?= $morning_in ?>" step="60"></td>
                            <td><input type="time" name="morning_out[]" value="<?= $morning_out ?>" step="60"></td>
                            <td><input type="time" name="afternoon_in[]" value="<?= $afternoon_in ?>" step="60"></td>
                            <td><input type="time" name="afternoon_out[]" value="<?= $afternoon_out ?>" step="60"></td>
                            <td><input type="text" class="total-hours" value="<?= $total ?> hrs" readonly style="background:#e6f7ff; border:none; width:80px;"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top:20px; text-align:right;">
                    <button type="submit" name="save_attendance" class="btn-primary">Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ========== MODAL FUNCTIONS ==========
function closeStudentModal() {
    document.getElementById('studentModal').style.display = 'none';
}

function closeEditStudentModal() {
    document.getElementById('editStudentModal').style.display = 'none';
}

function closeEditSubjectModal() {
    document.getElementById('editSubjectModal').style.display = 'none';
}

function closeAttendanceModal() {
    document.getElementById('attendanceModal').style.display = 'none';
}

function openEditSubjectModal() {
    document.getElementById('editSubjectModal').style.display = 'flex';
}

function openAttendanceModal() {
    document.getElementById('attendanceModal').style.display = 'flex';
}

// ========== FILTER TABLE FUNCTION ==========
function filterTable() {
    var input = document.getElementById('searchInput');
    var filter = input.value.toUpperCase();
    var table = document.getElementById('studentTable');
    var tr = table.getElementsByTagName('tr');
    for (var i = 1; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName('td')[1];
        if (td) {
            var txtValue = td.textContent || td.innerText;
            tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
        }
    }
}

// ========== EDIT STUDENT FUNCTION ==========
function editStudent(id, name) {
    document.getElementById('edit_student_id').value = id;
    document.getElementById('edit_student_name').value = name;
    document.getElementById('editStudentModal').style.display = 'flex';
}

// ========== DELETE STUDENT FUNCTION ==========
function deleteStudent(id) {
    if (confirm('Delete this student? This will also delete their login account if exists.')) {
        document.getElementById('delete_student_id').value = id;
        document.getElementById('deleteStudentForm').submit();
    }
}

// ========== EDIT USERNAME FUNCTION ==========
function editUsername(studentId, currentUsername) {
    var newUsername = prompt("Enter new username:", currentUsername);
    if (newUsername && newUsername.trim() != "") {
        var form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        form.innerHTML = '<input type="hidden" name="student_id" value="' + studentId + '">' +
                        '<input type="hidden" name="new_username" value="' + newUsername.trim() + '">' +
                        '<input type="hidden" name="edit_username" value="1">';
        document.body.appendChild(form);
        form.submit();
    }
}

// ========== EDIT PASSWORD FUNCTION ==========
function editPassword(studentId) {
    var newPass = prompt("Enter new password (minimum 4 characters):");
    if (newPass && newPass.length >= 4) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        form.innerHTML = '<input type="hidden" name="student_id" value="' + studentId + '">' +
                        '<input type="hidden" name="new_password" value="' + newPass + '">' +
                        '<input type="hidden" name="edit_password" value="1">';
        document.body.appendChild(form);
        form.submit();
    } else if (newPass) {
        alert("Password must be at least 4 characters.");
    }
}

// ========== CALENDAR FUNCTIONS ==========
function goToMonth(month) {
    var scrollPos = window.scrollY;
    window.location.href = "?section_id=<?= $section_id ?>&modal_month=" + month + "&modal_date=<?= $modal_selected_date ?>&stay=1&scroll=" + scrollPos;
}

function selectDate(date) {
    var scrollPos = window.scrollY;
    window.location.href = "?section_id=<?= $section_id ?>&modal_month=<?= $modal_month ?>&modal_date=" + date + "&stay=1&scroll=" + scrollPos;
}

// ========== EVENT LISTENERS ==========
document.getElementById('openStudentModalBtn').onclick = function() {
    document.getElementById('studentModal').style.display = 'flex';
};

// Set scroll position before form submit
var attendanceForm = document.getElementById('attendanceForm');
if (attendanceForm) {
    attendanceForm.addEventListener('submit', function() {
        document.getElementById('scroll_pos').value = window.scrollY;
    });
}

// ========== RESTORE MODAL AND SCROLL POSITION ==========
<?php if($show_modal): ?>
window.onload = function() {
    document.getElementById('attendanceModal').style.display = 'flex';
    var scrollPos = <?= $scroll_position ?>;
    if(scrollPos > 0) {
        window.scrollTo(0, scrollPos);
    }
};
<?php endif; ?>

// Close modals when clicking outside
window.onclick = function(e) {
    var studentModal = document.getElementById('studentModal');
    var editStudentModal = document.getElementById('editStudentModal');
    var editSubjectModal = document.getElementById('editSubjectModal');
    var attendanceModal = document.getElementById('attendanceModal');
    
    if (e.target === studentModal) closeStudentModal();
    if (e.target === editStudentModal) closeEditStudentModal();
    if (e.target === editSubjectModal) closeEditSubjectModal();
    if (e.target === attendanceModal) closeAttendanceModal();
};
</script>
</body>
</html>