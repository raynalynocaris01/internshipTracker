<?php
$pageTitle = 'My Students';
$currentPage = 'students';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>My Students</h1>
        </div>
        <div class="card-body">
            <div class="filter-section">
                <label>Filter by Section:</label>
                <select id="section_filter" onchange="filterBySection()">
                    <option value="0">-- All Sections --</option>
                    <?php foreach ($sectionList as $sec): ?>
                        <option value="<?= $sec['id'] ?>" <?= ($selectedSectionId == $sec['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sec['subject_name']) ?> - <?= htmlspecialchars($sec['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <form method="GET" style="display:inline-flex; gap:10px;">
                    <input type="hidden" name="section_id" value="<?= $selectedSectionId ?>">
                    <input type="text" name="search" placeholder="Search student..." value="<?= htmlspecialchars($search ?? '') ?>">
                    <button type="submit" class="btn-primary btn-sm">Search</button>
                    <?php if (!empty($search) || $selectedSectionId > 0): ?>
                        <a href="/teacher/students" class="btn-warning btn-sm">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if (count($studentList) > 0): ?>
                <div class="table-responsive">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Section</th>
                                <th>Subject</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($studentList as $stu): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($stu['name']) ?></td>
                                <td><?= htmlspecialchars($stu['section_name']) ?></td>
                                <td><?= htmlspecialchars($stu['subject_name']) ?>
                                                                
                                <td>
                                    <a href="/teacher/students?section_id=<?= $stu['section_id'] ?>" class="btn-primary btn-sm">View Attendance</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (count($sectionList) == 0): ?>
                <div class="empty-message">No sections assigned to you yet. Contact administrator.</div>
            <?php elseif ($selectedSectionId > 0 && count($studentList) == 0): ?>
                <div class="empty-message">No students found in this section.</div>
            <?php else: ?>
                <div class="empty-message">No students assigned to your sections yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.filter-section {
    text-align: center;
    margin: 20px 0 25px 0;
    padding: 20px;
    background: #f8fafc;
    border-radius: 20px;
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}
.filter-section select, .filter-section input {
    padding: 8px 16px;
    border-radius: 40px;
    border: 2px solid #216699;
}
.students-table {
    width: 100%;
    border-collapse: collapse;
}
.students-table th {
    background: #216699;
    color: #ede432;
    padding: 12px;
    text-align: left;
}
.students-table td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
}
.students-table tr:hover {
    background: #f8fafc;
}
.empty-message {
    text-align: center;
    padding: 60px;
    color: #888;
}
';
$extraJS = '
function filterBySection() {
    var filter = document.getElementById("section_filter").value;
    window.location.href = "/teacher/students?section_id=" + filter;
}
';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
echo $content;
include __DIR__ . '/../layouts/footer.php';
?>