<?php
$pageTitle = 'Manage Instructors';
$currentPage = 'instructors';
ob_start();
?>

<div class="container">
    <?php if (isset($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3>Add New Instructor</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/instructors/add">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="btn-primary">Add Instructor</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Existing Instructors</h3>
            <form method="GET" style="margin:0;">
                <input type="text" name="search" class="search-bar" placeholder="Search..." value="<?= htmlspecialchars($search ?? '') ?>">
                <?php if (!empty($search)): ?>
                    <a href="/admin/instructors" class="btn-warning btn-sm">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-body">
            <?php if (count($instructors) > 0): ?>
                <?php foreach ($instructors as $inst): ?>
                <div class="instructor-card" id="inst_<?= $inst['id'] ?>">
                    <h3><?= htmlspecialchars($inst['full_name'] ?? $inst['username']) ?></h3>
                    <p><strong>Username:</strong> <?= htmlspecialchars($inst['username']) ?></p>
                    
                    <div class="flex-btns">
                        <button onclick='editInstructor(<?= $inst['id'] ?>, "<?= htmlspecialchars(addslashes($inst['full_name'] ?? $inst['username'])) ?>")' class="btn-warning btn-sm">Edit Name</button>
                        <button onclick='editUsername(<?= $inst['id'] ?>, "<?= htmlspecialchars($inst['username']) ?>")' class="btn-warning btn-sm">Edit Username</button>
                        <button onclick="resetPassword(<?= $inst['id'] ?>)" class="btn-warning btn-sm">Reset Password</button>
                        <a href="/admin/instructors/delete/<?= $inst['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Delete this instructor?')">Delete</a>
                    </div>
                    
                    <div class="assignment-section">
                        <div class="assignment-title">Assigned Subjects:</div>
                        <div class="section-list">
                            <?php if (count($inst['subjects'] ?? []) > 0): ?>
                                <?php foreach ($inst['subjects'] as $subj): ?>
                                    <span class="badge"><?= htmlspecialchars($subj['name']) ?> 
                                        <a href="/admin/instructors/remove-subject/<?= $inst['id'] ?>/<?= $subj['id'] ?>" style="color:#e2362c;">✕</a>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?> 
                                <span style="color:gray;">No subjects assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="assignment-section">
                        <div class="assignment-title">Assigned Sections:</div>
                        <div class="section-list">
                            <?php if (count($inst['sections'] ?? []) > 0): ?>
                                <?php foreach ($inst['sections'] as $sec): ?>
                                    <span class="badge"><?= htmlspecialchars($sec['name']) ?>
                                        <a href="/admin/instructors/remove-section/<?= $inst['id'] ?>/<?= $sec['id'] ?>" style="color:#e2362c;">✕</a>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?> 
                                <span style="color:gray;">No sections assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex-btns">
                        <button onclick="openAssignSubjectsModal(<?= $inst['id'] ?>)" class="btn-primary btn-sm">Assign Subjects</button>
                        <button onclick="openAssignSectionsModal(<?= $inst['id'] ?>)" class="btn-primary btn-sm">Assign Sections</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-message">No instructors found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modals -->
<div id="editNameModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Edit Instructor Name</h3><span class="close-modal" onclick="closeEditNameModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/admin/instructors/edit">
                <input type="hidden" name="instructor_id" id="edit_name_id">
                <input type="text" name="full_name" id="edit_full_name" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeEditNameModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editUsernameModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Edit Username</h3><span class="close-modal" onclick="closeEditUsernameModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/admin/instructors/edit-username">
                <input type="hidden" name="instructor_id" id="edit_username_id">
                <input type="text" name="username" id="edit_username" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeEditUsernameModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="resetPasswordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Reset Password</h3><span class="close-modal" onclick="closeResetPasswordModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/admin/instructors/reset-password">
                <input type="hidden" name="instructor_id" id="reset_id">
                <input type="password" name="new_password" id="new_password" required>
                <div class="flex-btns">
                    <button type="button" onclick="closeResetPasswordModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="assignSubjectsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Assign Subjects</h3><span class="close-modal" onclick="closeAssignSubjectsModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/admin/instructors/assign-subjects">
                <input type="hidden" name="instructor_id" id="assign_subjects_instructor_id">
                <div class="checkbox-group">
                    <?php foreach ($allSubjects ?? [] as $subj): ?>
                        <label><input type="checkbox" name="selected_subjects[]" value="<?= $subj['id'] ?>"> <?= htmlspecialchars($subj['name']) ?></label>
                    <?php endforeach; ?>
                </div>
                <div class="flex-btns">
                    <button type="button" onclick="closeAssignSubjectsModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Assign Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="assignSectionsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header"><h3>Assign Sections</h3><span class="close-modal" onclick="closeAssignSectionsModal()">&times;</span></div>
        <div class="modal-body">
            <form method="POST" action="/admin/instructors/assign-sections">
                <input type="hidden" name="instructor_id" id="assign_sections_instructor_id">
                <div class="checkbox-group">
                    <?php foreach ($allSections ?? [] as $sec): ?>
                        <label><input type="checkbox" name="selected_sections[]" value="<?= $sec['id'] ?>"> <?= htmlspecialchars($sec['name']) ?></label>
                    <?php endforeach; ?>
                </div>
                <div class="flex-btns">
                    <button type="button" onclick="closeAssignSectionsModal()" class="btn-danger">Cancel</button>
                    <button type="submit" class="btn-primary">Assign Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.instructor-card {
    background: #fef9e3;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 20px;
    border-left: 6px solid #e2362c;
}
.assignment-section {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e2e8f0;
}
.section-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 5px;
}
.badge {
    background: #ede432;
    padding: 4px 10px;
    border-radius: 40px;
    font-size: 0.75rem;
}
.flex-btns {
    display: flex;
    gap: 10px;
    margin: 15px 0;
    flex-wrap: wrap;
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
.checkbox-group {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 15px;
    margin: 10px 0;
}
.checkbox-group label {
    display: block;
    padding: 5px 0;
    cursor: pointer;
}
';
$extraJS = '
function editInstructor(id, name) {
    document.getElementById("edit_name_id").value = id;
    document.getElementById("edit_full_name").value = name;
    document.getElementById("editNameModal").style.display = "flex";
}
function closeEditNameModal() { document.getElementById("editNameModal").style.display = "none"; }
function editUsername(id, username) {
    document.getElementById("edit_username_id").value = id;
    document.getElementById("edit_username").value = username;
    document.getElementById("editUsernameModal").style.display = "flex";
}
function closeEditUsernameModal() { document.getElementById("editUsernameModal").style.display = "none"; }
function resetPassword(id) {
    document.getElementById("reset_id").value = id;
    document.getElementById("resetPasswordModal").style.display = "flex";
}
function closeResetPasswordModal() { document.getElementById("resetPasswordModal").style.display = "none"; }
function openAssignSubjectsModal(id) {
    document.getElementById("assign_subjects_instructor_id").value = id;
    document.getElementById("assignSubjectsModal").style.display = "flex";
}
function closeAssignSubjectsModal() { document.getElementById("assignSubjectsModal").style.display = "none"; }
function openAssignSectionsModal(id) {
    document.getElementById("assign_sections_instructor_id").value = id;
    document.getElementById("assignSectionsModal").style.display = "flex";
}
function closeAssignSectionsModal() { document.getElementById("assignSectionsModal").style.display = "none"; }
window.onclick = function(e) {
    if(e.target.classList.contains("modal")) e.target.style.display = "none";
};
';
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/../../layouts/navbar.php';
echo $content;
include __DIR__ . '/../../layouts/footer.php';
?>