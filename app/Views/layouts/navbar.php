<?php
$role = $_SESSION['role'] ?? '';
$fullName = $_SESSION['full_name'] ?? '';
$displayName = $fullName ?: ($_SESSION['username'] ?? 'User');

$navItems = [];
if ($role === 'admin') {
    $navItems = [
        'Dashboard' => '/admin/dashboard',
        'Instructors' => '/admin/instructors',
        'Subjects' => '/admin/subjects',
        'Sections' => '/admin/sections',
        'Students' => '/admin/students'
    ];
} elseif ($role === 'teacher' || $role === 'instructor') {
    $navItems = [
        'Dashboard' => '/teacher/dashboard',
        'Subjects' => '/teacher/subjects',
        'Sections' => '/teacher/sections',
        'Students' => '/teacher/students'
    ];
} elseif ($role === 'student') {
    $navItems = [
        'Dashboard' => '/student/dashboard'
    ];
}
?>
<div class="navbar">
    <div>
        <h2>Internship Tracker 
            <span class="badge-<?= $role ?>">
                <?= strtoupper($role === 'instructor' ? 'TEACHER' : $role) ?>
                <?php if ($role !== 'student'): ?>: <?= htmlspecialchars($displayName) ?><?php endif; ?>
            </span>
        </h2>
    </div>
    <?php if ($role): ?>
        <a href="/logout" class="btn-logout">Logout</a>
    <?php endif; ?>
</div>

<?php if ($role && $role !== 'student'): ?>
<div class="<?= $role === 'admin' ? 'admin-nav' : 'teacher-nav' ?>">
    <?php foreach ($navItems as $name => $url): ?>
        <a href="<?= $url ?>" class="<?= ($currentPage === strtolower($name)) ? 'active' : '' ?>">
            <?= $name ?>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>