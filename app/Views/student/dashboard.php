<?php
$pageTitle = 'Student Dashboard';
$currentPage = 'dashboard';
ob_start();
?>

<div class="container">
    <?php if (!$selectedSubjectId): ?>
        <!-- Subject Selection View -->
        <div class="welcome-section">
            <h1>Welcome, <?= htmlspecialchars($studentName ?? 'Student') ?>!</h1>
            <p>Select a subject to view your internship hours</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h1>My Internship Subjects</h1>
            </div>
            <div class="subject-grid">
                <?php foreach ($subjectList as $subj): ?>
                    <div class="subject-card">
                        <h3><?= htmlspecialchars($subj['name']) ?></h3>
                        <p>Required Hours: <strong><?= $subj['required_hours'] ?></strong> hrs</p>
                        <div class="progress-bar">
                            <?php 
                            $percentage = ($subj['completed_hours'] / $subj['required_hours']) * 100;
                            $percentage = min(100, round($percentage));
                            ?>
                            <div class="progress" style="width: <?= $percentage ?>%;"></div>
                        </div>
                        <p class="progress-text">Completed: <?= $subj['completed_hours'] ?> / <?= $subj['required_hours'] ?> hrs (<?= $percentage ?>%)</p>
                        <a href="/student/dashboard?subject_id=<?= $subj['id'] ?>" class="btn-primary">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Hours Detail View -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
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
                    <div class="stat-card">
                        <h3>Progress</h3>
                        <div class="stat-number"><?= round(($completedHours / $requiredHours) * 100) ?>%</div>
                        <span>complete</span>
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
                                    <th>Morning Hours</th>
                                    <th>Afternoon In</th>
                                    <th>Afternoon Out</th>
                                    <th>Afternoon Hours</th>
                                    <th>Total Hours</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceRecords as $rec): ?>
                                <?php
                                $morningHours = 0;
                                if ($rec['morning_in'] && $rec['morning_out']) {
                                    $morningHours = round((strtotime($rec['morning_out']) - strtotime($rec['morning_in'])) / 3600, 2);
                                }
                                $afternoonHours = 0;
                                if ($rec['afternoon_in'] && $rec['afternoon_out']) {
                                    $afternoonHours = round((strtotime($rec['afternoon_out']) - strtotime($rec['afternoon_in'])) / 3600, 2);
                                }
                                ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($rec['date'])) ?></td>
                                    <td><?= $rec['morning_in'] ?? '-' ?></td>
                                    <td><?= $rec['morning_out'] ?? '-' ?></td>
                                    <td><?= $morningHours > 0 ? $morningHours . ' hrs' : '-' ?></td>
                                    <td><?= $rec['afternoon_in'] ?? '-' ?></td>
                                    <td><?= $rec['afternoon_out'] ?? '-' ?></td>
                                    <td><?= $afternoonHours > 0 ? $afternoonHours . ' hrs' : '-' ?></td>
                                    <td><strong><?= $rec['total_hours'] ?> hrs</strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-message">No attendance records yet. Your teacher will record your time.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.welcome-section {
    text-align: center;
    margin-bottom: 30px;
}
.welcome-section h1 {
    color: #216699;
    font-size: 1.8rem;
}
.subject-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
    padding: 20px;
}
.subject-card {
    background: #fef9e3;
    border-radius: 20px;
    padding: 24px;
    border-left: 6px solid #e2362c;
    transition: transform 0.3s;
}
.subject-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
.subject-card h3 {
    color: #216699;
    font-size: 1.5rem;
    margin-bottom: 10px;
}
.progress-bar {
    background: #e2e8f0;
    border-radius: 20px;
    height: 20px;
    margin: 15px 0;
    overflow: hidden;
}
.progress {
    background: #216699;
    height: 100%;
    border-radius: 20px;
    transition: width 0.5s;
}
.progress-text {
    font-size: 0.85rem;
    color: #555;
    margin-bottom: 15px;
}
.stats-panel {
    padding: 30px;
    background: #f8fafc;
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
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    min-width: 180px;
}
.stat-card h3 {
    color: #216699;
    margin-bottom: 10px;
    font-size: 1rem;
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
    color: #ede432;
    padding: 12px;
    text-align: left;
}
.attendance-table td {
    padding: 10px;
    border-bottom: 1px solid #e2e8f0;
}
.table-responsive {
    overflow-x: auto;
    padding: 20px;
}
.empty-message {
    text-align: center;
    padding: 60px;
    color: #888;
}
';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navbar.php';
echo $content;
include __DIR__ . '/../layouts/footer.php';
?>