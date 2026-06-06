<?php
$pageTitle = 'Add New Section';
$currentPage = 'sections';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Add New Section</h1>
            <a href="/admin/sections" class="btn-warning">← Back to Sections</a>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/sections/add" class="form-container">
                <div class="form-group">
                    <label for="subject_id">Select Subject *</label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">-- Select a Subject --</option>
                        <?php foreach ($subjects as $subj): ?>
                            <option value="<?= $subj['id'] ?>"><?= htmlspecialchars($subj['name']) ?> (<?= $subj['required_hours'] ?> hours)</option>
                        <?php endforeach; ?>
                    </select>
                    <small>The subject this section belongs to</small>
                </div>
                
                <div class="form-group">
                    <label for="section_name">Section Name *</label>
                    <input type="text" id="section_name" name="section_name" required placeholder="e.g., BSIT-3A, CS-2B, Section A">
                    <small>Unique identifier for this class section</small>
                </div>
                
                <div class="form-group">
                    <label for="code">Section Code (optional)</label>
                    <input type="text" id="code" name="code" placeholder="e.g., IT301, CS202">
                    <small>Alternative identifier or room code</small>
                </div>
                
                <div class="form-group">
                    <label for="max_students">Maximum Students (optional)</label>
                    <input type="number" id="max_students" name="max_students" min="1" placeholder="Leave empty for unlimited">
                    <small>Limit the number of students in this section</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Section</button>
                    <a href="/admin/sections" class="btn-danger">Cancel</a>
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