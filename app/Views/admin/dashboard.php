<?php
$pageTitle = 'Admin Dashboard';
$currentPage = 'dashboard';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Admin Dashboard</h1>
        </div>
        <div class="card-body">
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>Instructors</h3>
                    <div class="count"><?= $totalInstructors ?></div>
                    <a href="/admin/instructors" class="btn-primary">Manage Instructors</a>
                </div>
                <div class="dashboard-card">
                    <h3>Subjects</h3>
                    <div class="count"><?= $totalSubjects ?></div>
                    <a href="/admin/subjects" class="btn-primary">Manage Subjects</a>
                </div>
                <div class="dashboard-card">
                    <h3>Sections</h3>
                    <div class="count"><?= $totalSections ?></div>
                    <a href="/admin/sections" class="btn-primary">Manage Sections</a>
                </div>
                <div class="dashboard-card">
                    <h3>Students</h3>
                    <div class="count"><?= $totalStudents ?></div>
                    <a href="/admin/students" class="btn-primary">View All Students</a>
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
    transition: all 0.3s;
}
.dashboard-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.dashboard-card h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: #216699;
}
.dashboard-card .count {
    font-size: 2.5rem;
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