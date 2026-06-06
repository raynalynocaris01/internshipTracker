<?php
$pageTitle = 'Manage Sections';
$currentPage = 'sections';
ob_start();
?>

<div class="container">
    <?php if (isset($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3>All Sections</h3>
            <button onclick="openAddSectionModal()" class="btn-primary">+ Add Section</button>
        </div>
        <div class="card-body">
            <div class="section-grid">
                <?php if (count($sections) > 0): ?>
                    <?php foreach ($sections as $sec): ?>
                    <div class="section-card">
                        <h3><?= htmlspecialchars($sec['name']) ?></h3>
                        <p><strong>Subject:</strong> <?= htmlspecialchars($sec['subject_name'] ?? 'N/A') ?></p>
                        <div class="btn-group">
                            <button onclick='editSection(<?= $sec['id'] ?>, "<?= htmlspecialchars($sec['name']) ?>")' class="btn-warning btn-sm">Edit</button>
                            <a href="/admin/sections/delete/<?= $sec['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Delete this section?')">Delete</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-message">No sections yet. Click "Add Section".</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="addSectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Add New Section</h3><span class="close-modal" onclick="closeAddSectionModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/admin/sections/add">
                <select name="subject_id" required>
                    <option value="">-- Select Subject --</option>
                    <?php foreach ($subjects as $subj): ?>
                        <option value="<?= $subj['id'] ?>"><?= htmlspecialchars($subj['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="section_name" placeholder="Section Name" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeAddSectionModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Add Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editSectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Edit Section</h3><span class="close-modal" onclick="closeEditSectionModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/admin/sections/edit">
                <input type="hidden" name="section_id" id="edit_section_id">
                <input type="text" name="section_name" id="edit_section_name" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeEditSectionModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.section-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}
.section-card {
    background: #fef9e3;
    border-radius: 20px;
    padding: 20px;
    border-left: 6px solid #e2362c;
}
.section-card h3 {
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
.modal-body input, .modal-body select {
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
function openAddSectionModal() {
    document.getElementById("addSectionModal").style.display = "flex";
}
function closeAddSectionModal() {
    document.getElementById("addSectionModal").style.display = "none";
}
function editSection(id, name) {
    document.getElementById("edit_section_id").value = id;
    document.getElementById("edit_section_name").value = name;
    document.getElementById("editSectionModal").style.display = "flex";
}
function closeEditSectionModal() {
    document.getElementById("editSectionModal").style.display = "none";
}
window.onclick = function(e) {
    if (e.target === document.getElementById("addSectionModal")) closeAddSectionModal();
    if (e.target === document.getElementById("editSectionModal")) closeEditSectionModal();
};
';
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/../../layouts/navbar.php';
echo $content;
include __DIR__ . '/../../layouts/footer.php';
?>