<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isInstructor() && !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$section_id = (int)$_POST['section_id'];
$date = $_POST['date'];
$session_type = $_POST['session_type'];

// Validate session type
$valid_types = ['morning_in', 'morning_out', 'afternoon_in', 'afternoon_out'];
if (!in_array($session_type, $valid_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid session type']);
    exit;
}

// Generate unique token
$token = bin2hex(random_bytes(32));

// Set expiration time (30 minutes from now)
$expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));

// Check if a QR code already exists for this section, date, and session type
$check = $pdo->prepare("SELECT id FROM qr_sessions WHERE section_id = ? AND date = ? AND session_type = ? AND is_active = 1");
$check->execute([$section_id, $date, $session_type]);
$existing = $check->fetch();

if ($existing) {
    // Deactivate old QR code
    $deactivate = $pdo->prepare("UPDATE qr_sessions SET is_active = 0 WHERE section_id = ? AND date = ? AND session_type = ?");
    $deactivate->execute([$section_id, $date, $session_type]);
}

// Insert new QR session
$stmt = $pdo->prepare("INSERT INTO qr_sessions (section_id, date, session_type, qr_token, expires_at) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$section_id, $date, $session_type, $token, $expires_at]);

echo json_encode(['success' => true, 'token' => $token, 'expires_at' => $expires_at]);
?>