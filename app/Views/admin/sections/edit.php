<?php
$pageTitle = 'Edit Section';
$currentPage = 'sections';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Edit Section</h1>
            <a href="/admin/sections" class="btn-warning">← Back to Sections</a>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/sections/update" class="form-container">
                <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
                
                <div class="form-group">
                    <label for="subject_id">Select Subject *</label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">-- Select a Subject --</option>
                        <?php foreach ($subjects as $subj): ?>
                            <option value="<?= $subj['id'] ?>" <?= ($section['subject_id'] == $subj['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subj['name']) ?> (<?= $subj['required_hours'] ?> hours)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="section_name">Section Name *</label>
                    <input type="text" id="section_name" name="section_name" required value="<?= htmlspecialchars($section['name']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="code">Section Code</label>
                    <input type="text" id="code" name="code" value="<?= htmlspecialchars($section['code'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="max_students">Maximum Students</label>
                    <input type="number" id="max_students" name="max_students" min="1" value="<?= $section['max_students'] ?? '' ?>" placeholder="Leave empty for unlimited">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Update Section</button>
                    <a href="/admin/sections" class="btn-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Students in this section -->
    <div class="card">
        <div class="card-header">
            <h3>Students in This Section (<?= count($students) ?> students)</h3>
        </div>
        <div class="card-body">
            <?php if (count($students) > 0): ?>
                <div class="table-responsive">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($students as $stu): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($stu['name']) ?></td>
                                <td>
                                    <?php if ($stu['has_account']): ?>
                                        <span class="badge-success">Has Account</span>
                                    <?php else: ?>
                                        <span class="badge-warning">No Account</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/admin/students/edit/<?= $stu['id'] ?>" class="btn-warning btn-sm">Edit</a>
                                    <a href="/admin/students/move/<?= $stu['id'] ?>?section_id=<?= $section['id'] ?>" class="btn-primary btn-sm">Move to Another Section</a>
                                </span><input type="hidden" name="total_hours[]" value="<?= $total ?>">
                             </span><input type="hidden" name="total_hours[]" value="<?= $total ?>">
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="empty-message">No students in this section yet.</p>
            <?php endif; ?>
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
.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
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
.badge-success {
    background: #d4edda;
    color: #155724;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
}
.badge-warning {
    background: #fff3cd;
    color: #856404;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
}
.empty-message {
    text-align: center;
    padding: 40px;
    color: #888;
}
';
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/../../layouts/navbar.php';
echo $content;
include __DIR__ . '/../../layouts/footer.php';
?>