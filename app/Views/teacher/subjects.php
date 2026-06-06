<?php
$pageTitle = 'My Subjects';
$currentPage = 'subjects';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>My Subjects</h1>
        </div>
        <div class="card-body">
            <div class="subject-grid">
                <?php foreach ($subjectList as $subj): ?>
                <div class="subject-card">
                    <h3><?= htmlspecialchars($subj['name']) ?></h3>
                    <p>Required Hours: <strong><?= $subj['required_hours'] ?></strong> hrs</p>
                    <a href="/teacher/sections?subject_id=<?= $subj['id'] ?>" class="btn-primary">View Sections</a>
                </div>
                <?php endforeach; ?>
                <?php if (count($subjectList) == 0): ?>
                    <div class="empty-message">No subjects assigned. Contact administrator.</div>
                <?php endif; ?>
            </div>
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
    font-size: 1.4rem;
    margin-bottom: 10px;
    color: #216699;
}
.subject-card p {
    margin-bottom: 15px;
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