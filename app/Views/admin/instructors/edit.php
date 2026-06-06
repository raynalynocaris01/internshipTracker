<?php
$pageTitle = 'Edit Instructor';
$currentPage = 'instructors';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Edit Instructor</h1>
            <a href="/admin/instructors" class="btn-warning">← Back to Instructors</a>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/instructors/update" class="form-container">
                <input type="hidden" name="instructor_id" value="<?= $instructor['id'] ?>">
                
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required value="<?= htmlspecialchars($instructor['full_name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required value="<?= htmlspecialchars($instructor['username']) ?>">
                    <small>Username must be unique</small>
                </div>
                
                <div class="form-group">
                    <label for="password">New Password (optional)</label>
                    <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
                    <small>Only fill if you want to change the password</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Update Instructor</button>
                    <a href="/admin/instructors" class="btn-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Assigned Subjects Section -->
    <div class="card">
        <div class="card-header">
            <h3>Assigned Subjects</h3>
        </div>
        <div class="card-body">
            <div class="assigned-list">
                <?php if (count($assignedSubjects) > 0): ?>
                    <div class="items-grid">
                        <?php foreach ($assignedSubjects as $subj): ?>
                            <div class="assigned-item">
                                <span><?= htmlspecialchars($subj['name']) ?></span>
                                <a href="/admin/instructors/remove-subject/<?= $instructor['id'] ?>/<?= $subj['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Remove this subject?')">Remove</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-message">No subjects assigned yet.</p>
                <?php endif; ?>
            </div>
            
            <div class="assign-section">
                <h4>Assign New Subjects</h4>
                <form method="POST" action="/admin/instructors/assign-subjects" class="inline-form">
                    <input type="hidden" name="instructor_id" value="<?= $instructor['id'] ?>">
                    <select name="selected_subjects[]" multiple size="5" class="multi-select">
                        <?php foreach ($allSubjects as $subj): ?>
                            <option value="<?= $subj['id'] ?>"><?= htmlspecialchars($subj['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-primary">Assign Selected</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Assigned Sections Section -->
    <div class="card">
        <div class="card-header">
            <h3>Assigned Sections</h3>
        </div>
        <div class="card-body">
            <div class="assigned-list">
                <?php if (count($assignedSections) > 0): ?>
                    <div class="items-grid">
                        <?php foreach ($assignedSections as $sec): ?>
                            <div class="assigned-item">
                                <span><?= htmlspecialchars($sec['name']) ?> (<?= htmlspecialchars($sec['subject_name'] ?? 'N/A') ?>)</span>
                                <a href="/admin/instructors/remove-section/<?= $instructor['id'] ?>/<?= $sec['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Remove this section?')">Remove</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-message">No sections assigned yet.</p>
                <?php endif; ?>
            </div>
            
            <div class="assign-section">
                <h4>Assign New Sections</h4>
                <form method="POST" action="/admin/instructors/assign-sections" class="inline-form">
                    <input type="hidden" name="instructor_id" value="<?= $instructor['id'] ?>">
                    <select name="selected_sections[]" multiple size="5" class="multi-select">
                        <?php foreach ($allSections as $sec): ?>
                            <option value="<?= $sec['id'] ?>"><?= htmlspecialchars($sec['name']) ?> (<?= htmlspecialchars($sec['subject_name'] ?? 'N/A') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-primary">Assign Selected</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.form-container {
    max-width: 600px;
    margin: 0 auto;
}
.form-group {
    margin-bottom: 25px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #216699;
}
.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 40px;
    font-size: 1rem;
}
.form-group input:focus {
    outline: none;
    border-color: #216699;
}
.form-group small {
    display: block;
    margin-top: 5px;
    color: #888;
    font-size: 0.8rem;
}
.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
}
.assigned-list {
    margin-bottom: 30px;
}
.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}
.assigned-item {
    background: #fef9e3;
    padding: 10px 15px;
    border-radius: 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.assign-section {
    border-top: 1px solid #e2e8f0;
    padding-top: 20px;
    margin-top: 10px;
}
.assign-section h4 {
    color: #216699;
    margin-bottom: 15px;
}
.multi-select {
    width: 100%;
    padding: 10px;
    border: 2px solid #216699;
    border-radius: 20px;
    margin-bottom: 15px;
}
.inline-form {
    max-width: 400px;
}
.empty-message {
    text-align: center;
    padding: 20px;
    color: #888;
}
';
$extraJS = '
document.querySelector("form").onsubmit = function(e) {
    var password = document.getElementById("password").value;
    var confirm = document.getElementById("confirm_password").value;
    if (password !== confirm) {
        e.preventDefault();
        alert("Passwords do not match!");
        return false;
    }
};
';
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/../../layouts/navbar.php';
echo $content;
include __DIR__ . '/../../layouts/footer.php';
?>