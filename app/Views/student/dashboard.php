<?php
$pageTitle = 'Student Dashboard';
$currentPage = 'dashboard';
ob_start();
?>

<div class="container">
    <?php if (!$selectedSubjectId): ?>
        <!-- Subject Selection View -->
        <div class="card">
            <div class="card-header">
                <h1>My Internship Subjects</h1>
            </div>
            <div class="subject-grid">
                <?php foreach ($subjectList as $subj): ?>
                    <div class="subject-card">
                        <h3><?= htmlspecialchars($subj['name']) ?></h3>
                        <p>Required Hours: <strong><?= $subj['required_hours'] ?></strong></p>
                        <a href="/student/dashboard?subject_id=<?= $subj['id'] ?>" class="btn-primary">View My Hours</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Hours View -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between;">
                <h1><?= htmlspecialchars($selectedSubject['name']) ?></h1>
                <a href="/student/dashboard" class="btn-warning">← Back to Subjects</a>
            </div>
            
            <div class="stats-panel">
                <div class="stats-container">
                    <div class="stat-card">
                        <h3>Completed Hours</h3>
                        <div class="stat-number"><?= $completedHours ?></div>
                        <span>/ <?= $requiredHours ?> hrs</span>
                    </div>
                    <div class="stat-card">
                        <h3>Remaining Hours</h3>
                        <div class="stat-number"><?= $remainingHours ?></div>
                        <span>to complete OJT</span>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Your Daily Time Records</h3>
                </div>
                <?php if (count($attendanceRecords) > 0): ?>
                    <div class="table-responsive">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Morning In</th>
                                    <th>Morning Out</th>
                                    <th>Afternoon In</th>
                                    <th>Afternoon Out</th>
                                    <th>Total Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceRecords as $rec): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($rec['date'])) ?></td>
                                    <td><?= $rec['morning_in'] ?? '-' ?></td>
                                    <td><?= $rec['morning_out'] ?? '-' ?></td>
                                    <td><?= $rec['afternoon_in'] ?? '-' ?></td>
                                    <td><?= $rec['afternoon_out'] ?? '-' ?></td>
                                    <td><strong><?= $rec['total_hours'] ?> hrs</strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-message">No attendance records yet.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.subject-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
    padding: 20px;
}
.subject-card {
    background: #fef9e3;
    border-radius: 20px;
    padding: 24px;
    border-left: 6px solid #e2362c;
}
.subject-card h3 {
    color: #216699;
    font-size: 1.5rem;
    margin-bottom: 10px;
}
.stats-panel {
    padding: 30px;
    text-align: center;
}
.stats-container {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
}
.stat-card {
    background: white;
    border-radius: 20px;
    padding: 20px 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.stat-number {
    font-size: 48px;
    font-weight: bold;
    color: #216699;
}
.attendance-table {
    width: 100%;
    border-collapse: collapse;
}
.attendance-table th {
    background: #216699;
    color: white;
    padding: 12px;
}
.attendance-table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}
';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
echo $content;
include __DIR__ . '/../layouts/footer.php';
?>