<?php
$pageTitle = 'Add New Student';
$currentPage = 'students';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Add New Student</h1>
            <a href="/admin/students" class="btn-warning">← Back to Students</a>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/students/add" class="form-container">
                <div class="form-group">
                    <label for="student_name">Student Full Name *</label>
                    <input type="text" id="student_name" name="student_name" required placeholder="e.g., Juan Dela Cruz">
                </div>
                
                <div class="form-group">
                    <label for="section_id">Select Section *</label>
                    <select id="section_id" name="section_id" required>
                        <option value="">-- Select a Section --</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['id'] ?>">
                                <?= htmlspecialchars($sec['subject_name']) ?> - <?= htmlspecialchars($sec['name']) ?>
                                <?php if ($sec['current_students'] && $sec['max_students']): ?>
                                    (<?= $sec['current_students'] ?>/<?= $sec['max_students'] ?> students)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Choose the section this student belongs to</small>
                </div>
                
                <div class="form-group">
                    <label for="student_id_number">Student ID Number (optional)</label>
                    <input type="text" id="student_id_number" name="student_id_number" placeholder="e.g., 2021-0001">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address (optional)</label>
                    <input type="email" id="email" name="email" placeholder="student@example.com">
                </div>
                
                <div class="form-group">
                    <label for="phone">Contact Number (optional)</label>
                    <input type="tel" id="phone" name="phone" placeholder="e.g., 09123456789">
                </div>
                
                <div class="form-checkbox">
                    <label>
                        <input type="checkbox" name="create_account" value="1">
                        Create login account automatically
                    </label>
                    <small>If checked, a login account will be created with default password</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Add Student</button>
                    <a href="/admin/students" class="btn-danger">Cancel</a>
                </div>
            </form>
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
.form-group input, .form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 40px;
    font-size: 1rem;
}
.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: #216699;
}
.form-group small {
    display: block;
    margin-top: 5px;
    color: #888;
    font-size: 0.8rem;
}
.form-checkbox {
    margin: 20px 0;
}
.form-checkbox label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}
.form-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
}
.form-checkbox small {
    display: block;
    margin-left: 30px;
    color: #888;
}
.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
}
';
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/../../layouts/navbar.php';
echo $content;
include __DIR__ . '/../../layouts/footer.php';
?>