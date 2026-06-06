<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action == 'time_in') {
    if (!isStudent()) {
        echo json_encode(['success' => false, 'message' => 'Only students can time in']);
        exit();
    }
    
    $subject_id = $_POST['subject_id'];
    $student_id = $_SESSION['user_id'];
    
    // Check if subject belongs to student
    $stmt = $pdo->prepare("SELECT id FROM subjects WHERE id = ? AND student_id = ?");
    $stmt->execute([$subject_id, $student_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid subject']);
        exit();
    }
    
    // Check for existing active or pending sessions
    $stmt = $pdo->prepare("SELECT id FROM timesheet 
                           WHERE student_id = ? AND subject_id = ? 
                           AND status IN ('active', 'time_in_requested', 'time_out_requested')");
    $stmt->execute([$student_id, $subject_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have an active or pending session for this subject']);
        exit();
    }
    
    // Create time in request
    $stmt = $pdo->prepare("INSERT INTO timesheet (student_id, subject_id, time_in, status) 
                           VALUES (?, ?, NOW(), 'time_in_requested')");
    $stmt->execute([$student_id, $subject_id]);
    
    echo json_encode(['success' => true, 'message' => 'Time in request submitted for approval']);
    
} elseif ($action == 'time_out') {
    if (!isStudent()) {
        echo json_encode(['success' => false, 'message' => 'Only students can time out']);
        exit();
    }
    
    $subject_id = $_POST['subject_id'];
    $student_id = $_SESSION['user_id'];
    
    // Find active session (status = 'active')
    $stmt = $pdo->prepare("SELECT id FROM timesheet 
                           WHERE student_id = ? AND subject_id = ? AND status = 'active'");
    $stmt->execute([$student_id, $subject_id]);
    $session = $stmt->fetch();
    
    if (!$session) {
        echo json_encode(['success' => false, 'message' => 'No active session found']);
        exit();
    }
    
    // Request time out (set time_out = NOW, status = 'time_out_requested')
    $stmt = $pdo->prepare("UPDATE timesheet SET time_out = NOW(), status = 'time_out_requested' 
                           WHERE id = ? AND student_id = ? AND subject_id = ?");
    $stmt->execute([$session['id'], $student_id, $subject_id]);
    
    echo json_encode(['success' => true, 'message' => 'Time out request submitted for approval']);
    
} elseif ($action == 'approve_request') {
    if (!isTeacher()) {
        echo json_encode(['success' => false, 'message' => 'Only teachers can approve requests']);
        exit();
    }
    
    $request_id = $_POST['request_id'];
    $type = $_POST['type'];
    
    if ($type == 'Time In') {
        $stmt = $pdo->prepare("UPDATE timesheet SET status = 'active' WHERE id = ?");
        $stmt->execute([$request_id]);
        echo json_encode(['success' => true, 'message' => 'Time in approved. Student is now active.']);
    } elseif ($type == 'Time Out') {
        $stmt = $pdo->prepare("UPDATE timesheet SET status = 'approved' WHERE id = ? AND status = 'time_out_requested'");
        $stmt->execute([$request_id]);
        echo json_encode(['success' => true, 'message' => 'Time out approved. Hours recorded.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request type']);
    }
    
} elseif ($action == 'reject_request') {
    if (!isTeacher()) {
        echo json_encode(['success' => false, 'message' => 'Only teachers can reject requests']);
        exit();
    }
    
    $request_id = $_POST['request_id'];
    $stmt = $pdo->prepare("UPDATE timesheet SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$request_id]);
    
    echo json_encode(['success' => true, 'message' => 'Request rejected']);
    
} elseif ($action == 'get_subject_students') {
    if (!isTeacher()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    
    $subject_name = trim($_POST['subject_name']);
    
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, s.id as subject_id, s.subject_name, s.required_hours
        FROM subjects s
        JOIN users u ON u.id = s.student_id
        WHERE s.subject_name = ?
        ORDER BY u.full_name
    ");
    $stmt->execute([$subject_name]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = [];
    foreach ($students as $student) {
        $completed = getCompletedHours($pdo, $student['id'], $student['subject_id']);
        
        // Active session?
        $stmt2 = $pdo->prepare("SELECT id, time_in FROM timesheet 
                                WHERE student_id = ? AND subject_id = ? AND status = 'active'");
        $stmt2->execute([$student['id'], $student['subject_id']]);
        $active = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        // Pending requests?
        $stmt3 = $pdo->prepare("
            SELECT id, time_in, status,
            CASE 
                WHEN status = 'time_in_requested' THEN 'Time In'
                WHEN status = 'time_out_requested' THEN 'Time Out'
            END as request_type,
            time_in as request_time
            FROM timesheet 
            WHERE student_id = ? AND subject_id = ? 
            AND status IN ('time_in_requested', 'time_out_requested')
        ");
        $stmt3->execute([$student['id'], $student['subject_id']]);
        $pending = $stmt3->fetchAll(PDO::FETCH_ASSOC);
        
        // Get activity dates for calendar filter
        $stmt4 = $pdo->prepare("SELECT DISTINCT DATE(time_in) as d FROM timesheet WHERE student_id = ? AND subject_id = ?");
        $stmt4->execute([$student['id'], $student['subject_id']]);
        $activity_dates = $stmt4->fetchAll(PDO::FETCH_COLUMN);
        
        // Format times without AM/PM
        $active_formatted = null;
        if ($active) {
            $active_formatted = [
                'id' => $active['id'],
                'time_in' => formatTimeNoAmPm($active['time_in'])
            ];
        }
        
        $pending_formatted = [];
        foreach ($pending as $req) {
            $pending_formatted[] = [
                'id' => $req['id'],
                'request_type' => $req['request_type'],
                'request_time' => formatTimeNoAmPm($req['request_time'])
            ];
        }
        
        $result[] = [
            'full_name' => $student['full_name'],
            'required_hours' => (float)$student['required_hours'],
            'completed_hours' => $completed,
            'active_session' => $active_formatted,
            'pending_requests' => $pending_formatted,
            'activity_dates' => $activity_dates
        ];
    }
    
    if (empty($result)) {
        echo json_encode([
            'success' => false,
            'message' => 'No students found for this subject.'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => [
                'subject_name' => $subject_name,
                'students' => $result
            ]
        ]);
    }
    
} elseif ($action == 'get_attendance_by_date') {
    if (!isTeacher()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    
    $subject_name = trim($_POST['subject_name']);
    $date = trim($_POST['date']);
    
    // Fetch all time entries for the given subject and date
    $stmt = $pdo->prepare("
        SELECT u.full_name as student_name, 
               t.time_in,
               t.time_out,
               t.status
        FROM timesheet t
        JOIN subjects s ON t.subject_id = s.id
        JOIN users u ON t.student_id = u.id
        WHERE s.subject_name = ? AND DATE(t.time_in) = ?
        ORDER BY t.time_in ASC
    ");
    $stmt->execute([$subject_name, $date]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format times without AM/PM and prepare output
    $attendance = [];
    foreach ($records as $record) {
        $attendance[] = [
            'student_name' => $record['student_name'],
            'time_in' => $record['time_in'] ? formatTimeNoAmPm($record['time_in']) : '-',
            'time_out' => $record['time_out'] ? formatTimeNoAmPm($record['time_out']) : '-',
            'status' => $record['status'] === 'approved' ? 'Completed' : $record['status']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => ['attendance' => $attendance]
    ]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>