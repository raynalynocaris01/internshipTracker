<?php
namespace App\Controllers;

use App\Models\Student;
use App\Services\AttendanceService;

class StudentController extends BaseController {
    private $studentModel;
    private $attendanceService;

    public function __construct() {
        parent::__construct();
        $this->studentModel = new Student();
        $this->attendanceService = new AttendanceService();
    }

    public function dashboard() {
        $this->requireStudent();
        $studentId = $this->sanitizeInt($_SESSION['ref_id'] ?? 0);
        if ($studentId <= 0) {
            $this->redirect('/login');
        }

        $selectedSubjectId = $this->sanitizeInt($_GET['subject_id'] ?? 0);
        $student = $this->studentModel->getById($studentId);
        $subjectList = [];
        $attendanceRecords = [];
        $selectedSubject = null;
        $completedHours = 0;
        $requiredHours = 0;

        if ($student) {
            $sectionStmt = $this->pdo->prepare("SELECT sec.*, subj.name AS subject_name, subj.required_hours FROM sections sec JOIN subjects subj ON sec.subject_id = subj.id WHERE sec.id = ?");
            $sectionStmt->execute([$student['section_id']]);
            $section = $sectionStmt->fetch();
            if ($section) {
                $subjectList = [
                    [
                        'id' => $section['subject_id'],
                        'name' => $section['subject_name'],
                        'required_hours' => $section['required_hours'],
                        'completed_hours' => 0
                    ]
                ];
            }
        }

        if ($selectedSubjectId > 0) {
            $selectedSubjectStmt = $this->pdo->prepare("SELECT subj.* FROM subjects subj WHERE subj.id = ?");
            $selectedSubjectStmt->execute([$selectedSubjectId]);
            $selectedSubject = $selectedSubjectStmt->fetch();
            $attendanceStmt = $this->pdo->prepare("SELECT a.* FROM attendance a JOIN students s ON a.student_id = s.id JOIN sections sec ON s.section_id = sec.id WHERE a.student_id = ? AND sec.subject_id = ? ORDER BY a.date DESC");
            $attendanceStmt->execute([$studentId, $selectedSubjectId]);
            $attendanceRecords = $attendanceStmt->fetchAll();
            $completedHours = array_reduce($attendanceRecords, function($sum, $rec) {
                return $sum + floatval($rec['total_hours']);
            }, 0);
            $requiredHours = $selectedSubject['required_hours'] ?? 0;
        }

        $this->render('student/dashboard', [
            'studentName' => $_SESSION['full_name'] ?? null,
            'subjectList' => $subjectList,
            'selectedSubjectId' => $selectedSubjectId,
            'selectedSubject' => $selectedSubject,
            'attendanceRecords' => $attendanceRecords,
            'completedHours' => $completedHours,
            'requiredHours' => $requiredHours
        ]);
    }

    public function qrAttendance() {
        $this->requireStudent();
        $this->render('student/qr_attendance', []);
    }

    public function recordAttendance() {
        $this->requireStudent();
        $this->ensureMethod('POST');
        $studentId = $this->sanitizeInt($_POST['student_id'] ?? 0);
        $date = $this->sanitizeString($_POST['date'] ?? date('Y-m-d'));
        $sessionType = $this->sanitizeString($_POST['session_type'] ?? '');
        if ($studentId <= 0 || !$sessionType) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid attendance data'], 400);
        }
        $time = date('H:i:s');
        $result = $this->attendanceService->recordAttendance($studentId, $date, $sessionType, $time);
        $this->jsonResponse($result);
    }
}
