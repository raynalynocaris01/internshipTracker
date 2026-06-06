<?php
namespace App\Controllers;

use App\Models\Student;
use App\Models\User;

class TeacherController extends BaseController {
    private $studentModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->studentModel = new Student();
        $this->userModel = new User();
    }

    public function dashboard() {
        $this->requireTeacher();
        $sections = $this->pdo->query("SELECT sec.*, subj.name as subject_name FROM sections sec JOIN subjects subj ON sec.subject_id = subj.id ORDER BY subj.name, sec.name")->fetchAll();
        $this->render('teacher/dashboard', ['sections' => $sections]);
    }

    public function students() {
        $this->requireTeacher();
        $sectionId = $this->sanitizeInt($_GET['section_id'] ?? 0);
        $studentList = $this->studentModel->getAll($sectionId ?: null);
        $sections = $this->pdo->query("SELECT sec.*, subj.name as subject_name, subj.required_hours FROM sections sec JOIN subjects subj ON sec.subject_id = subj.id ORDER BY subj.name, sec.name")->fetchAll();

        if ($sectionId > 0) {
            $sectionStmt = $this->pdo->prepare("SELECT sec.*, subj.name as subject_name, subj.required_hours FROM sections sec JOIN subjects subj ON sec.subject_id = subj.id WHERE sec.id = ?");
            $sectionStmt->execute([$sectionId]);
            $section = $sectionStmt->fetch();
            $this->render('teacher/students', [
                'section' => $section,
                'sectionId' => $sectionId,
                'studentList' => $studentList,
                'sections' => $sections
            ]);
            return;
        }

        $this->render('teacher/students_list', [
            'studentList' => $studentList,
            'sectionList' => $sections,
            'selectedSectionId' => 0
        ]);
    }

    public function subjects() {
        $this->requireTeacher();
        $subjects = $this->pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
        $this->render('teacher/subjects', ['subjects' => $subjects]);
    }

    public function editSubject() {
        $this->requireTeacher();
        $this->ensureMethod('POST');
        $subjectId = $this->sanitizeInt($_POST['subject_id'] ?? 0);
        $name = $this->sanitizeString($_POST['subject_name'] ?? '');
        $hours = $this->sanitizeInt($_POST['required_hours'] ?? 0);
        if ($subjectId > 0 && $name) {
            $stmt = $this->pdo->prepare("UPDATE subjects SET name = ?, required_hours = ? WHERE id = ?");
            $stmt->execute([$name, $hours, $subjectId]);
        }
        $this->redirect('/teacher/subjects');
    }

    public function sections() {
        $this->requireTeacher();
        $sections = $this->pdo->query("SELECT sec.*, subj.name as subject_name FROM sections sec JOIN subjects subj ON sec.subject_id = subj.id ORDER BY subj.name, sec.name")->fetchAll();
        $this->render('teacher/sections', ['sections' => $sections]);
    }

    public function attendance() {
        $this->requireTeacher();
        $sections = $this->pdo->query("SELECT sec.*, subj.name as subject_name FROM sections sec JOIN subjects subj ON sec.subject_id = subj.id ORDER BY subj.name, sec.name")->fetchAll();
        $this->render('teacher/attendance', ['sections' => $sections]);
    }

    public function loadAttendance() {
        $this->requireTeacher();
        $sectionId = $this->sanitizeInt($_GET['section_id'] ?? 0);
        $date = $this->sanitizeString($_GET['date'] ?? date('Y-m-d'));
        if ($sectionId <= 0) {
            echo '<p>No section selected.</p>';
            return;
        }
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE section_id = ? ORDER BY name");
        $stmt->execute([$sectionId]);
        $students = $stmt->fetchAll();

        echo '<form id="attendance-table">';
        echo '<table class="students-table"><thead><tr><th>#</th><th>Name</th><th>Action</th></tr></thead><tbody>';
        $i = 1;
        foreach ($students as $stu) {
            echo '<tr><td>' . $i++ . '</td><td>' . htmlspecialchars($stu['name']) . '</td>';
            echo '<td>';
            echo '<select id="session_type_' . $stu['id'] . '">';
            echo '<option value="morning_in">Morning In</option>';
            echo '<option value="morning_out">Morning Out</option>';
            echo '<option value="afternoon_in">Afternoon In</option>';
            echo '<option value="afternoon_out">Afternoon Out</option>';
            echo '</select> ';
            echo '<button type="button" onclick="record(' . $stu['id'] . ')">Record</button>';
            echo '</td></tr>';
        }
        echo '</tbody></table>';
        echo '<script>function record(id){var select=document.getElementById("session_type_"+id);var sessionType=select.value;fetch("/teacher/attendance/save",{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},body:"student_id="+id+"&date="+encodeURIComponent("' . $date . '")+"&session_type="+encodeURIComponent(sessionType)}).then(r=>r.json()).then(j=>{alert(j.message||j.success?"Saved":"Error");});}</script>';
        echo '</form>';
    }

    public function saveAttendance() {
        $this->requireTeacher();
        $this->ensureMethod('POST');
        $studentId = $this->sanitizeInt($_POST['student_id'] ?? 0);
        $date = $this->sanitizeString($_POST['date'] ?? date('Y-m-d'));
        $sessionType = $this->sanitizeString($_POST['session_type'] ?? '');
        if ($studentId <= 0 || !$sessionType) {
            $this->jsonResponse(['success' => false, 'message' => 'Missing parameters'], 400);
        }
        $attendanceService = new \App\Services\AttendanceService();
        $res = $attendanceService->recordAttendance($studentId, $date, $sessionType, date('H:i:s'));
        $this->jsonResponse($res);
    }

    public function addStudent($sectionId = null) {
        $this->requireTeacher();
        $this->ensureMethod('POST');
        $sectionId = $this->sanitizeInt($sectionId);
        $name = $this->sanitizeString($_POST['student_name'] ?? '');
        if ($sectionId > 0 && $name) {
            $this->studentModel->create($name, $sectionId);
        }
        $this->redirect('/teacher/students?section_id=' . $sectionId);
    }

    public function editStudent($sectionId = null) {
        $this->requireTeacher();
        $this->ensureMethod('POST');
        $sectionId = $this->sanitizeInt($sectionId);
        $studentId = $this->sanitizeInt($_POST['student_id'] ?? 0);
        $name = $this->sanitizeString($_POST['student_name'] ?? '');
        if ($studentId > 0 && $name) {
            $this->studentModel->update($studentId, $name, $sectionId);
        }
        $this->redirect('/teacher/students?section_id=' . $sectionId);
    }

    public function deleteStudent($sectionId = null, $studentId = null) {
        $this->requireTeacher();
        $sectionId = $this->sanitizeInt($sectionId);
        $studentId = $this->sanitizeInt($studentId);
        if ($studentId > 0) {
            $this->studentModel->delete($studentId);
        }
        $this->redirect('/teacher/students?section_id=' . $sectionId);
    }

    public function editUsername($sectionId = null) {
        $this->requireTeacher();
        $this->ensureMethod('POST');
        $sectionId = $this->sanitizeInt($sectionId);
        $studentId = $this->sanitizeInt($_POST['student_id'] ?? 0);
        $username = $this->sanitizeString($_POST['new_username'] ?? '');
        if ($studentId > 0 && $username) {
            $stmt = $this->pdo->prepare("UPDATE users SET username = ? WHERE role = 'student' AND ref_id = ?");
            $stmt->execute([$username, $studentId]);
        }
        $this->redirect('/teacher/students?section_id=' . $sectionId);
    }

    public function editPassword($sectionId = null) {
        $this->requireTeacher();
        $this->ensureMethod('POST');
        $sectionId = $this->sanitizeInt($sectionId);
        $studentId = $this->sanitizeInt($_POST['student_id'] ?? 0);
        $password = $_POST['new_password'] ?? '';
        if ($studentId > 0 && $password) {
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE role = 'student' AND ref_id = ?");
            $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $studentId]);
        }
        $this->redirect('/teacher/students?section_id=' . $sectionId);
    }

    public function removeLogin($sectionId = null, $studentId = null) {
        $this->requireTeacher();
        $studentId = $this->sanitizeInt($studentId);
        if ($studentId > 0) {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE role = 'student' AND ref_id = ?");
            $stmt->execute([$studentId]);
        }
        $this->redirect('/teacher/students?section_id=' . $this->sanitizeInt($sectionId));
    }

    public function createLogin($sectionId = null, $studentId = null) {
        $this->requireTeacher();
        $studentId = $this->sanitizeInt($studentId);
        if ($studentId > 0) {
            $student = $this->studentModel->getById($studentId);
            if ($student) {
                $username = strtolower(str_replace(' ', '.', $student['name'])) . $studentId;
                $password = 'student1234';
                $this->userModel->create([
                    'full_name' => $student['name'],
                    'username' => $username,
                    'password' => $password,
                    'role' => 'student',
                    'ref_id' => $studentId
                ]);
            }
        }
        $this->redirect('/teacher/students?section_id=' . $this->sanitizeInt($sectionId));
    }
}
