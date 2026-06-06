<?php
$pageTitle = 'My Sections';
$currentPage = 'sections';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>My Sections</h1>
        </div>
        <div class="card-body">
            <div class="subject-selector">
                <label>Select Subject:</label>
                <select id="subject_select" onchange="window.location.href='?subject_id='+this.value">
                    <option value="">-- Select a Subject --</option>
                    <?php foreach ($subjectList as $subj): ?>
                        <option value="<?= $subj['id'] ?>" <?= ($selectedSubjectId == $subj['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($subj['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if ($selectedSubjectId && $selectedSubject): ?>
                <?php if (count($sections) > 0): ?>
                    <div class="section-grid">
                        <?php foreach ($sections as $sec): ?>
                        <div class="section-card">
                            <h3><?= htmlspecialchars($sec['name']) ?></h3>
                            <p><strong>Subject:</strong> <?= htmlspecialchars($selectedSubject['name']) ?></p>
                            <a href="/teacher/students?section_id=<?= $sec['id'] ?>" class="btn-primary">Manage Students</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-message">No sections assigned for this subject.</div>
                <?php endif; ?>
            <?php elseif ($selectedSubjectId && !$selectedSubject): ?>
                <div class="empty-message">Subject not found or not assigned to you.</div>
            <?php else: ?>
                <div class="empty-message">Select a subject above to view its sections.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.subject-selector {
    text-align: center;
    margin-bottom: 30px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 20px;
}
.subject-selector select {
    padding: 12px 25px;
    border-radius: 40px;
    border: 2px solid #216699;
    font-size: 1rem;
    min-width: 250px;
}
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
    text-align: center;
}
.section-card h3 {
    font-size: 1.4rem;
    margin-bottom: 10px;
    color: #216699;
}
.empty-message {
    text-align: center;
    padding: 40px;
    color: #888;
}
';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
echo $content;
include __DIR__ . '/../layouts/footer.php';
?>