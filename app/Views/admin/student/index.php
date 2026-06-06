<?php
$pageTitle = 'Manage Students';
$currentPage = 'students';
ob_start();
?>

<div class="container">
    <?php if (isset($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3>Manage Students</h3>
            <button id="openAddStudentModal" class="btn-primary">+ Add Student</button>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <div class="filter-section">
                <label>Filter by Section:</label>
                <select id="section_filter" onchange="filterBySection()">
                    <option value="0">-- All Sections --</option>
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= $sec['id'] ?>" <?= ($sectionFilter == $sec['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sec['subject_name']) ?> - <?= htmlspecialchars($sec['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button onclick="clearFilter()" class="btn-warning btn-sm">Clear Filter</button>
            </div>
            
            <!-- Students Table -->
            <table width="100%">
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
                    <?php $i = 1; foreach ($studentList as $stu): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($stu['name']) ?></td>
                        <td><?= htmlspecialchars($stu['section_name']) ?></td>
                        <td><?= htmlspecialchars($stu['subject_name']) ?></td>
                        <td>
                            <button onclick="editStudent(<?= $stu['id'] ?>, '<?= htmlspecialchars($stu['name']) ?>', <?= $stu['section_id'] ?>)" class="btn-warning btn-sm">Edit</button>
                            <a href="/admin/students/delete/<?= $stu['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Delete this student?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div id="addStudentModal" class="modal">
    <div class="modal-content" style="width:400px;">
        <div class="modal-header"><h3>Add New Student</h3><span class="close-modal" onclick="closeAddModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/admin/students/add">
                <input type="text" name="student_name" placeholder="Student Full Name" required>
                <select name="section_id" required>
                    <option value="">-- Select Section --</option>
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= $sec['id'] ?>"><?= htmlspecialchars($sec['subject_name']) ?> - <?= htmlspecialchars($sec['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="flex-btns">
                    <button type="button" onclick="closeAddModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.filter-section {
    text-align: center;
    margin: 20px 0 25px 0;
    padding-bottom: 15px;
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
    overflow: hidden;
}
.modal-header {
    background: #216699;
    padding: 20px;
    border-bottom: 3px solid #ede432;
    position: relative;
}
.modal-header h3 {
    color: white;
    margin: 0;
}
.close-modal {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #e2362c;
}
.modal-body {
    padding: 25px;
}
.modal-body input, .modal-body select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #216699;
    border-radius: 40px;
    margin: 10px 0;
}
.flex-btns {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 20px;
}
';
$extraJS = '
function filterBySection() {
    var filter = document.getElementById("section_filter").value;
    window.location.href = "/admin/students?section_filter=" + filter;
}
function clearFilter() {
    window.location.href = "/admin/students";
}
document.getElementById("openAddStudentModal").onclick = function() {
    document.getElementById("addStudentModal").style.display = "flex";
};
function closeAddModal() {
    document.getElementById("addStudentModal").style.display = "none";
}
function editStudent(id, name, sectionId) {
    window.location.href = "/admin/students/edit/" + id;
}
window.onclick = function(e) {
    if (e.target === document.getElementById("addStudentModal")) closeAddModal();
};
';
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/../../layouts/navbar.php';
echo $content;
include __DIR__ . '/../../layouts/footer.php';
?>