<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else if ($_SESSION['role'] == 'instructor') {
        header("Location: teacher_subjects.php");
    } else if ($_SESSION['role'] == 'student') {
        header("Location: student_dashboard.php");
    } else {
        header("Location: login.php");
    }
} else {
    header("Location: login.php");
}
exit;
?>