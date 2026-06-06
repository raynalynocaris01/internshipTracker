<?php
$pageTitle = 'Teacher Dashboard';
$currentPage = 'dashboard';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Dashboard Overview</h1>
        </div>
        <div class="card-body">
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>My Subjects</h3>
                    <div class="count"><?= count($subjectList) ?></div>
                    <a href="/teacher/subjects" class="btn-primary">View Subjects</a>
                </div>
                <div class="dashboard-card">
                    <h3>My Sections</h3>
                    <div class="count"><?= count($sectionList) ?></div>
                    <a href="/teacher/sections" class="btn-primary">View Sections</a>
                </div>
                <div class="dashboard-card">
                    <h3>My Students</h3>
                    <div class="count"><?= $totalStudents ?></div>
                    <a href="/teacher/students" class="btn-primary">View Students</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}
.dashboard-card {
    background: #fef9e3;
    border-radius: 20px;
    padding: 25px;
    border-left: 6px solid #e2362c;
    text-align: center;
}
.dashboard-card h3 {
    font-size: 1.3rem;
    margin-bottom: 15px;
    color: #216699;
}
.dashboard-card .count {
    font-size: 2.8rem;
    font-weight: bold;
    color: #216699;
    margin: 15px 0;
}
';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
echo $content;
include __DIR__ . '/../layouts/footer.php';
?>