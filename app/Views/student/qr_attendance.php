<?php
$pageTitle = 'QR Attendance';
ob_start();

$success = $success ?? false;
$message = $message ?? '';
$studentData = $studentData ?? null;
$qrSession = $qrSession ?? null;
$date = $date ?? date('Y-m-d');
$current_time = $current_time ?? date('H:i:s');
?>

<div class="container">
    <div class="attendance-card">
        <div class="card-header">
            <h1>QR Attendance</h1>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="success-icon">✓</div>
                <div class="message success"><?= htmlspecialchars($message) ?></div>
                <div class="details-card">
                    <h3>Attendance Recorded</h3>
                    <div class="detail-row">
                        <span class="detail-label">Student:</span>
                        <span class="detail-value"><?= htmlspecialchars($studentData['name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Subject:</span>
                        <span class="detail-value"><?= htmlspecialchars($qrSession['subject_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Section:</span>
                        <span class="detail-value"><?= htmlspecialchars($qrSession['section_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value"><?= date('F d, Y', strtotime($date)) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Time:</span>
                        <span class="detail-value"><?= date('h:i A', strtotime($current_time)) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Session:</span>
                        <span class="detail-value"><?= ucfirst(str_replace('_', ' ', $qrSession['session_type'] ?? 'N/A')) ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="error-icon">✗</div>
                <div class="message error"><?= htmlspecialchars($message) ?></div>
                <div class="details-card">
                    <h3>Attendance Failed</h3>
                    <div class="detail-row">
                        <span class="detail-label">Student:</span>
                        <span class="detail-value"><?= htmlspecialchars($studentData['name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value"><?= date('F d, Y', strtotime($date)) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="button-group">
                <a href="/student/dashboard" class="btn-primary">Back to Dashboard</a>
                <?php if (!$success): ?>
                    <a href="javascript:history.back()" class="btn-warning">Try Again</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.container {
    max-width: 600px;
    margin: 50px auto;
    padding: 20px;
}
.attendance-card {
    background: white;
    border-radius: 28px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    overflow: hidden;
}
.card-header {
    background: #216699;
    padding: 25px;
    text-align: center;
    border-bottom: 3px solid #ede432;
}
.card-header h1 {
    color: white;
    margin: 0;
}
.card-body {
    padding: 30px;
    text-align: center;
}
.success-icon, .error-icon {
    font-size: 64px;
    margin-bottom: 20px;
}
.success-icon {
    color: #216699;
}
.error-icon {
    color: #e2362c;
}
.message {
    font-size: 1.2rem;
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 20px;
}
.message.success {
    background: #e6fffa;
    color: #216699;
}
.message.error {
    background: #f8d7da;
    color: #721c24;
}
.details-card {
    background: #f8fafc;
    border-radius: 20px;
    padding: 20px;
    margin: 20px 0;
    text-align: left;
}
.details-card h3 {
    color: #216699;
    margin-bottom: 15px;
    text-align: center;
}
.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e2e8f0;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    font-weight: bold;
    color: #216699;
}
.detail-value {
    color: #333;
}
.button-group {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 20px;
}
.btn-primary, .btn-warning {
    padding: 10px 25px;
    border-radius: 40px;
    text-decoration: none;
    display: inline-block;
    font-weight: bold;
}
.btn-primary {
    background: #216699;
    color: white;
}
.btn-warning {
    background: #ede432;
    color: #1e293b;
}
';
include __DIR__ . '/../layouts/header.php';
echo $content;
include __DIR__ . '/../layouts/footer.php';
?>