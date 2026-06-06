<?php
$pageTitle = 'Add New Subject';
$currentPage = 'subjects';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Add New Subject</h1>
            <a href="/admin/subjects" class="btn-warning">← Back to Subjects</a>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/subjects/add" class="form-container">
                <div class="form-group">
                    <label for="subject_name">Subject Name *</label>
                    <input type="text" id="subject_name" name="subject_name" required placeholder="e.g., Web Development, Data Structures, etc.">
                </div>
                
                <div class="form-group">
                    <label for="required_hours">Required Hours *</label>
                    <input type="number" id="required_hours" name="required_hours" required min="1" step="1" placeholder="e.g., 160, 200, 240">
                    <small>Total internship hours required for this subject</small>
                </div>
                
                <div class="form-group">
                    <label for="description">Description (optional)</label>
                    <textarea id="description" name="description" rows="4" placeholder="Brief description of the subject"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Subject</button>
                    <a href="/admin/subjects" class="btn-danger">Cancel</a>
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
.form-group input, .form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 20px;
    font-size: 1rem;
    font-family: inherit;
}
.form-group input:focus, .form-group textarea:focus {
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