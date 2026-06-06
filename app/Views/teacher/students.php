<?php
$pageTitle = 'Manage Students - ' . htmlspecialchars($section['name']);
$currentPage = 'students';
ob_start();
?>

<div class="container">
    <?php if (isset($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="subject-info">
            <h1><?= htmlspecialchars($section['subject_name']) ?></h1>
            <p>Required Hours: <strong><?= $section['required_hours'] ?></strong> hrs</p>
            <button onclick="openEditSubjectModal()" class="edit-btn">Edit Subject</button>
        </div>
    </div>
    
    <div class="button-group">
        <button id="openStudentModalBtn" class="btn-middle btn-yellow">+ Add Student</button>
        <button onclick="openAttendanceModal()" class="btn-middle btn-yellow">Record Attendance</button>
        <button onclick="openQRModal()" class="btn-middle btn-yellow">Generate QR Code</button>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>Students</h3>
            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search student..." class="search-bar">
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="studentTable" class="students-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Login Account</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($studentList as $stu): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($stu['name']) ?></td>
                            <td>
                                <?php if ($stu['has_account']): ?>
                                    <span style="color:green;">Username: <?= htmlspecialchars($stu['username']) ?></span><br>
                                    <button onclick="editUsername(<?= $stu['id'] ?>, '<?= htmlspecialchars($stu['username']) ?>')" class="btn-warning btn-sm">Edit User</button>
                                    <button onclick="editPassword(<?= $stu['id'] ?>)" class="btn-warning btn-sm">Edit Pass</button>
                                    <a href="/teacher/students/<?= $sectionId ?>/remove-login/<?= $stu['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Remove login?')">Remove Login</a>
                                <?php else: ?>
                                    <span style="color:gray;">No account</span><br>
                                    <a href="/teacher/students/<?= $sectionId ?>/create-login/<?= $stu['id'] ?>" class="btn-primary btn-sm">Create Login</a>
                                <?php endif; ?>
                            </span><input type="hidden" name="total_hours[]" value="<?= $total ?>">
                             </span><input type="hidden" name="total_hours[]" value="<?= $total ?>">
                            </td>
                            <td>
                                <button onclick="editStudent(<?= $stu['id'] ?>, '<?= htmlspecialchars($stu['name']) ?>')" class="btn-warning btn-sm">Edit</button>
                                <button onclick="deleteStudent(<?= $stu['id'] ?>)" class="btn-danger btn-sm">Delete</button>
                             </span><input type="hidden" name="total_hours[]" value="<?= $total ?>">
                         </span><input type="hidden" name="total_hours[]" value="<?= $total ?>">
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div id="editSubjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Edit Subject</h3><span class="close-modal" onclick="closeEditSubjectModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/teacher/subjects/edit">
                <input type="hidden" name="subject_id" value="<?= $section['subject_id'] ?>">
                <input type="text" name="subject_name" value="<?= htmlspecialchars($section['subject_name']) ?>" required>
                <input type="number" name="required_hours" value="<?= $section['required_hours'] ?>" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeEditSubjectModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div id="studentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Add New Student</h3><span class="close-modal" onclick="closeStudentModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/teacher/students/<?= $sectionId ?>/add">
                <input type="text" name="student_name" placeholder="Full Name" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeStudentModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Attendance Modal -->
<div id="attendanceModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2>Record Attendance</h2>
            <span class="close-modal" onclick="closeAttendanceModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="date-selector">
                <label>Select Date:</label>
                <input type="date" id="attendanceDate" value="<?= date('Y-m-d') ?>">
                <button onclick="loadAttendance()" class="btn-primary">Load</button>
            </div>
            
            <div id="attendanceFormContainer">
                <!-- Attendance form will be loaded via AJAX -->
                <p>Select a date to record attendance.</p>
            </div>
        </div>
    </div>
</div>

<!-- QR Modal -->
<div id="qrModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Generate QR Code for Attendance</h3>
            <span class="close-modal" onclick="closeQRModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="qrForm">
                <input type="hidden" name="section_id" value="<?= $sectionId ?>">
                <label>Date:</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
                <label>Session Type:</label>
                <select name="session_type" required>
                    <option value="morning_in">Morning In</option>
                    <option value="morning_out">Morning Out</option>
                    <option value="afternoon_in">Afternoon In</option>
                    <option value="afternoon_out">Afternoon Out</option>
                </select>
                <div class="flex-btns">
                    <button type="button" onclick="closeQRModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Generate QR Code</button>
                </div>
            </form>
            <div id="qrResult" style="margin-top: 20px; text-align: center;"></div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.button-group {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin: 30px 0;
    flex-wrap: wrap;
}
.btn-middle {
    padding: 12px 30px;
    font-size: 1rem;
    border-radius: 40px;
    cursor: pointer;
    border: none;
}
.btn-yellow {
    background: #ede432;
    color: #1e293b;
}
.subject-info {
    text-align: center;
    padding: 20px;
}
.subject-info h1 {
    color: #216699;
    font-size: 2rem;
}
.edit-btn {
    background: #ede432;
    padding: 8px 20px;
    border-radius: 40px;
    margin-top: 10px;
    cursor: pointer;
    border: none;
}
.students-table {
    width: 100%;
    border-collapse: collapse;
}
.students-table th {
    background: #f1f5f9;
    color: #216699;
    padding: 12px;
    text-align: left;
}
.students-table td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
}
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.modal-content {
    background: white;
    border-radius: 28px;
    max-width: 90%;
    max-height: 85%;
    overflow-y: auto;
}
.modal-large {
    width: 90%;
    max-width: 1200px;
}
.modal-header {
    background: #216699;
    padding: 20px;
    border-bottom: 3px solid #ede432;
    position: relative;
}
.modal-header h2, .modal-header h3 {
    color: white;
    margin: 0;
}
.close-modal {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    cursor: pointer;
    color: #e2362c;
}
.modal-body {
    padding: 25px;
}
.modal-body input, .modal-body select {
    width: 100%;
    padding: 12px;
    border: 2px solid #216699;
    border-radius: 40px;
    margin: 10px 0;
}
.date-selector {
    margin-bottom: 20px;
    text-align: center;
}
.date-selector input {
    width: auto;
    display: inline-block;
    margin: 0 10px;
}
.flex-btns {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 20px;
}
';
$extraJS = '
function filterTable() {
    var input = document.getElementById("searchInput");
    var filter = input.value.toUpperCase();
    var table = document.getElementById("studentTable");
    var tr = table.getElementsByTagName("tr");
    for (var i = 1; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName("td")[1];
        if (td) {
            var txtValue = td.textContent || td.innerText;
            tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
        }
    }
}
function openEditSubjectModal() {
    document.getElementById("editSubjectModal").style.display = "flex";
}
function closeEditSubjectModal() {
    document.getElementById("editSubjectModal").style.display = "none";
}
document.getElementById("openStudentModalBtn").onclick = function() {
    document.getElementById("studentModal").style.display = "flex";
};
function closeStudentModal() {
    document.getElementById("studentModal").style.display = "none";
}
function openAttendanceModal() {
    document.getElementById("attendanceModal").style.display = "flex";
    loadAttendance();
}
function closeAttendanceModal() {
    document.getElementById("attendanceModal").style.display = "none";
}
function openQRModal() {
    document.getElementById("qrModal").style.display = "flex";
}
function closeQRModal() {
    document.getElementById("qrModal").style.display = "none";
    document.getElementById("qrResult").innerHTML = "";
}
function loadAttendance() {
    var date = document.getElementById("attendanceDate").value;
    fetch("/teacher/attendance/load?section_id=<?= $sectionId ?>&date=" + date)
        .then(response => response.text())
        .then(html => {
            document.getElementById("attendanceFormContainer").innerHTML = html;
        });
}
document.getElementById("qrForm").onsubmit = function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch("/qr/generate", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var qrUrl = "/qr/show?token=" + data.token;
            document.getElementById("qrResult").innerHTML = \'<div style="background:#f0f0f0; padding:20px; border-radius:20px;">\' +
                \'<p><strong>QR Code Generated!</strong></p>\' +
                \'<p>Token: \' + data.token + \'</p>\' +
                \'<p>Expires: \' + data.expires_at + \'</p>\' +
                \'<a href="\' + qrUrl + \'" target="_blank" class="btn-primary">View QR Code</a>\' +
                \'</div>\';
        } else {
            alert("Error: " + data.message);
        }
    });
};
function editStudent(id, name) {
    var newName = prompt("Enter new name:", name);
    if (newName && newName.trim()) {
        fetch("/teacher/students/<?= $sectionId ?>/edit", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "student_id=" + id + "&student_name=" + encodeURIComponent(newName)
        }).then(() => location.reload());
    }
}
function deleteStudent(id) {
    if (confirm("Delete this student?")) {
        window.location.href = "/teacher/students/<?= $sectionId ?>/delete/" + id;
    }
}
function editUsername(id, currentUsername) {
    var newUsername = prompt("Enter new username:", currentUsername);
    if (newUsername && newUsername.trim()) {
        fetch("/teacher/students/<?= $sectionId ?>/edit-username", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "student_id=" + id + "&new_username=" + encodeURIComponent(newUsername)
        }).then(() => location.reload());
    }
}
function editPassword(id) {
    var newPass = prompt("Enter new password (min 4 characters):");
    if (newPass && newPass.length >= 4) {
        fetch("/teacher/students/<?= $sectionId ?>/edit-password", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "student_id=" + id + "&new_password=" + encodeURIComponent(newPass)
        }).then(() => location.reload());
    } else if (newPass) {
        alert("Password must be at least 4 characters.");
    }
}
window.onclick = function(e) {
    if (e.target.classList.contains("modal")) {
        e.target.style.display = "none";
    }
};
';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
echo $content;
include __DIR__ . '/../layouts/footer.php';
?>