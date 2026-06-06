<?php
session_start();

$host = 'localhost';
$dbname = 'internship_tracker';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isTeacher() {
    return isset($_SESSION['role']) && ($_SESSION['role'] == 'teacher' || $_SESSION['role'] == 'instructor');
}

function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'student';
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

function getCompletedHours($pdo, $student_id, $subject_id) {
    $stmt = $pdo->prepare("SELECT SUM(total_hours) as total FROM attendance WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return round($result['total'] ?? 0, 2);
}

function getActiveSession($pdo, $student_id, $subject_id) {
    // For compatibility - returns null as attendance is teacher-recorded
    return null;
}

function hasPendingRequest($pdo, $student_id, $subject_id) {
    $stmt = $pdo->prepare("SELECT id FROM pending_attendance WHERE student_id = ? AND status = 'pending'");
    $stmt->execute([$student_id]);
    return $stmt->fetch() !== false;
}

function formatTimeNoAmPm($datetime) {
    if (empty($datetime)) return '';
    return date('g:i', strtotime($datetime));
}
?>