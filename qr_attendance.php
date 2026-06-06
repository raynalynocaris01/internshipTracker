<?php
require_once 'config.php';

// Check if student is logged in
if (!isStudent()) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['ref_id'];
$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    die("Invalid QR code.");
}

// Verify the QR token and get session info
$stmt = $pdo->prepare("
    SELECT qs.*, s.name as section_name, subj.name as subject_name 
    FROM qr_sessions qs
    JOIN sections s ON qs.section_id = s.id
    JOIN subjects subj ON s.subject_id = subj.id
    WHERE qs.qr_token = ? AND qs.is_active = 1
");
$stmt->execute([$token]);
$qrSession = $stmt->fetch();

if (!$qrSession) {
    die("QR code is invalid or expired.");
}

// Check if QR code has expired
if ($qrSession['expires_at'] && strtotime($qrSession['expires_at']) < time()) {
    die("QR code has expired.");
}

// Get student's section
$student = $pdo->prepare("SELECT section_id, name FROM students WHERE id = ?");
$student->execute([$student_id]);
$studentData = $student->fetch();

// Verify student belongs to this section
if ($studentData['section_id'] != $qrSession['section_id']) {
    die("You are not enrolled in this section.");
}

$date = $qrSession['date'];
$session_type = $qrSession['session_type'];
$current_time = date('H:i:s');

// Map session type to database column
$column_map = [
    'morning_in' => 'morning_in',
    'morning_out' => 'morning_out',
    'afternoon_in' => 'afternoon_in',
    'afternoon_out' => 'afternoon_out'
];

$column = $column_map[$session_type];

// Check if attendance already exists for this student on this date
$check = $pdo->prepare("SELECT id, $column FROM attendance WHERE student_id = ? AND date = ?");
$check->execute([$student_id, $date]);
$existing = $check->fetch();

$message = "";
$success = false;

if ($existing) {
    // Check if this session already has a time recorded
    if (!empty($existing[$column])) {
        $message = "You have already recorded your " . str_replace('_', ' ', $session_type) . " time today.";
    } else {
        // Update the specific column
        $update = $pdo->prepare("UPDATE attendance SET $column = ? WHERE student_id = ? AND date = ?");
        $update->execute([$current_time, $student_id, $date]);
        
        // Recalculate total hours
        $attRecord = $pdo->prepare("SELECT morning_in, morning_out, afternoon_in, afternoon_out FROM attendance WHERE student_id = ? AND date = ?");
        $attRecord->execute([$student_id, $date]);
        $record = $attRecord->fetch();
        
        $morning_hours = 0;
        $afternoon_hours = 0;
        if ($record['morning_in'] && $record['morning_out']) {
            $morning_hours = round((strtotime($record['morning_out']) - strtotime($record['morning_in'])) / 3600, 2);
        }
        if ($record['afternoon_in'] && $record['afternoon_out']) {
            $afternoon_hours = round((strtotime($record['afternoon_out']) - strtotime($record['afternoon_in'])) / 3600, 2);
        }
        $total = $morning_hours + $afternoon_hours;
        
        $updateTotal = $pdo->prepare("UPDATE attendance SET total_hours = ? WHERE student_id = ? AND date = ?");
        $updateTotal->execute([$total, $student_id, $date]);
        
        $message = "Success! Your " . str_replace('_', ' ', $session_type) . " time has been recorded at " . date('h:i A', strtotime($current_time));
        $success = true;
    }
} else {
    // Create new attendance record with only this session filled
    $insert = $pdo->prepare("INSERT INTO attendance (student_id, date, $column) VALUES (?, ?, ?)");
    $insert->execute([$student_id, $date, $current_time]);
    $message = "Success! Your " . str_replace('_', ' ', $session_type) . " time has been recorded at " . date('h:i A', strtotime($current_time));
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Attendance</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:linear-gradient(135deg,#c5e0f4 0%,#a8d0e6 100%);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;display:flex;justify-content:center;align-items:center;}
        .container{max-width:500px;margin:20px;padding:20px;}
        .card{background:white;border-radius:28px;box-shadow:0 8px 20px rgba(0,0,0,0.15);overflow:hidden;text-align:center;}
        .card-header{background:#216699;padding:25px;border-bottom:3px solid #ede432;}
        .card-header h1{color:white;font-size:1.5rem;}
        .card-body{padding:30px;}
        .success-icon{font-size:64px;color:#216699;margin-bottom:20px;}
        .error-icon{font-size:64px;color:#e2362c;margin-bottom:20px;}
        .message{font-size:1.2rem;margin-bottom:20px;}
        .details{background:#f5f5f5;padding:15px;border-radius:20px;margin-top:20px;}
        .details p{margin:5px 0;color:#555;}
        .btn-home{background:#216699;color:white;border:none;padding:12px 30px;border-radius:40px;font-weight:bold;cursor:pointer;text-decoration:none;display:inline-block;margin-top:20px;}
        .btn-home:hover{background:#1a4f77;}
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>QR Attendance</h1>
        </div>
        <div class="card-body">
            <?php if($success): ?>
                <div class="success-icon">✓</div>
                <div class="message" style="color:#216699;"><?= htmlspecialchars($message) ?></div>
                <div class="details">
                    <p><strong>Student:</strong> <?= htmlspecialchars($studentData['name']) ?></p>
                    <p><strong>Subject:</strong> <?= htmlspecialchars($qrSession['subject_name']) ?></p>
                    <p><strong>Section:</strong> <?= htmlspecialchars($qrSession['section_name']) ?></p>
                    <p><strong>Date:</strong> <?= date('F d, Y', strtotime($date)) ?></p>
                    <p><strong>Time:</strong> <?= date('h:i A', strtotime($current_time)) ?></p>
                </div>
            <?php else: ?>
                <div class="error-icon">✗</div>
                <div class="message" style="color:#e2362c;"><?= htmlspecialchars($message) ?></div>
                <div class="details">
                    <p><strong>Student:</strong> <?= htmlspecialchars($studentData['name']) ?></p>
                    <p><strong>Date:</strong> <?= date('F d, Y', strtotime($date)) ?></p>
                </div>
            <?php endif; ?>
            <a href="student_dashboard.php" class="btn-home">Back to Dashboard</a>
        </div>
    </div>
</div>
</body>
</html>