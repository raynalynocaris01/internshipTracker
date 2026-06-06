<?php
$pageTitle = 'Manage Subjects';
$currentPage = 'subjects';
ob_start();
?>

<div class="container">
    <?php if (isset($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3>All Subjects</h3>
            <button onclick="openAddSubjectModal()" class="btn-primary">+ Add Subject</button>
        </div>
        <div class="card-body">
            <div class="subject-grid">
                <?php if (count($subjects) > 0): ?>
                    <?php foreach ($subjects as $subj): ?>
                    <div class="subject-card">
                        <h3><?= htmlspecialchars($subj['name']) ?></h3>
                        <p>Required Hours: <strong><?= $subj['required_hours'] ?></strong> hrs</p>
                        <div class="btn-group">
                            <button onclick='editSubject(<?= $subj['id'] ?>, "<?= htmlspecialchars($subj['name']) ?>", <?= $subj['required_hours'] ?> )' class="btn-warning btn-sm">Edit</button>
                            <a href="/admin/subjects/delete/<?= $subj['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Delete this subject?')">Delete</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-message">No subjects yet. Click "Add Subject".</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="addSubjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Add New Subject</h3><span class="close-modal" onclick="closeAddSubjectModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/admin/subjects/add">
                <input type="text" name="subject_name" placeholder="Subject Name" required>
                <input type="number" name="required_hours" placeholder="Required Hours" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeAddSubjectModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editSubjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Edit Subject</h3><span class="close-modal" onclick="closeEditSubjectModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/admin/subjects/edit">
                <input type="hidden" name="subject_id" id="edit_subject_id">
                <input type="text" name="subject_name" id="edit_subject_name" required>
                <input type="number" name="required_hours" id="edit_required_hours" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeEditSubjectModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.subject-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}
.subject-card {
    background: #fef9e3;
    border-radius: 20px;
    padding: 20px;
    border-left: 6px solid #e2362c;
}
.subject-card h3 {
    font-size: 1.3rem;
    margin-bottom: 10px;
    color: #216699;
}
.btn-group {
    margin-top: 15px;
    display: flex;
    gap: 10px;
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
    width: 400px;
    max-width: 90%;
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
    cursor: pointer;
    color: #e2362c;
}
.modal-body {
    padding: 25px;
}
.modal-body input {
    width: 100%;
    padding: 12px;
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
function openAddSubjectModal() {
    document.getElementById("addSubjectModal").style.display = "flex";
}
function closeAddSubjectModal() {
    document.getElementById("addSubjectModal").style.display = "none";
}
function editSubject(id, name, hours) {
    document.getElementById("edit_subject_id").value = id;
    document.getElementById("edit_subject_name").value = name;
    document.getElementById("edit_required_hours").value = hours;
    document.getElementById("editSubjectModal").style.display = "flex";
}
function closeEditSubjectModal() {
    document.getElementById("editSubjectModal").style.display = "none";
}
window.onclick = function(e) {
    if (e.target === document.getElementById("addSubjectModal")) closeAddSubjectModal();
    if (e.target === document.getElementById("editSubjectModal")) closeEditSubjectModal();
};
';
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/../../layouts/navbar.php';
echo $content;
include __DIR__ . '/../../layouts/footer.php';
?>