<?php
$pageTitle = 'Manage Students - ' . htmlspecialchars($section['name']);
$currentPage = 'students';
ob_start();
?>

<div class="container">
    <?php if (isset($message)): ?>
        <div class="alert"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="subject-info">
            <h1><?= htmlspecialchars($section['subject_name']) ?></h1>
            <p>Required Hours: <strong><?= $section['required_hours'] ?></strong> hrs</p>
            <button onclick="openEditSubjectModal()" class="edit-btn">Edit Subject</button>
        </div>
    </div>
    
    <div class="button-group">
        <button id="openStudentModalBtn" class="btn-middle btn-yellow">+ Add Student</button>
        <button onclick="openAttendanceModal()" class="btn-middle btn-yellow">View Attendance</button>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>Students</h3>
            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search student..." class="search-bar">
        </div>
        <div class="card-body">
            <table id="studentTable" class="student-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Login Account</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($studentList as $stu): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($stu['name']) ?></td>
                        <td>
                            <?php if ($stu['has_account']): ?>
                                <span style="color:green;">Username: <?= htmlspecialchars($stu['username']) ?></span><br>
                                <button onclick="editUsername(<?= $stu['id'] ?>, '<?= htmlspecialchars($stu['username']) ?>')" class="btn-warning btn-sm">Edit User</button>
                                <button onclick="editPassword(<?= $stu['id'] ?>)" class="btn-warning btn-sm">Edit Pass</button>
                                <a href="/teacher/students/<?= $sectionId ?>/remove-login/<?= $stu['id'] ?>" class="btn-danger btn-sm" onclick="return confirm('Remove login?')">Remove Login</a>
                            <?php else: ?>
                                <span style="color:gray;">No account</span>
                                <a href="/teacher/students/<?= $sectionId ?>/create-login/<?= $stu['id'] ?>" class="btn-primary btn-sm">Create Login</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="editStudent(<?= $stu['id'] ?>, '<?= htmlspecialchars($stu['name']) ?>')" class="btn-warning btn-sm">Edit</button>
                            <button onclick="deleteStudent(<?= $stu['id'] ?>)" class="btn-danger btn-sm">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.button-group {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin: 30px 0;
}
.btn-middle {
    padding: 12px 40px;
    font-size: 1.2rem;
    border-radius: 40px;
    cursor: pointer;
}
.btn-yellow {
    background: #ede432;
    color: #1e293b;
}
.subject-info {
    text-align: center;
    padding: 20px;
}
.subject-info h1 {
    color: #216699;
    font-size: 2rem;
}
.edit-btn {
    background: #ede432;
    padding: 8px 20px;
    border-radius: 40px;
    margin-top: 10px;
    cursor: pointer;
}
';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
echo $content;
include __DIR__ . '/../layouts/footer.php';
?>