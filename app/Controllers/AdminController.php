<?php
namespace App\Controllers;

use App\Models\Student;
use App\Models\User;

class AdminController extends BaseController {
    private $studentModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->studentModel = new Student();
        $this->userModel = new User();
    }

    public function dashboard() {
        $this->requireAdmin();
        $this->render('admin/dashboard', []);
    }

    public function students() {
        $this->requireAdmin();
        $sectionFilter = $this->sanitizeInt($_GET['section_filter'] ?? 0);
        $students = $this->studentModel->getAll($sectionFilter ?: null);
        $sections = $this->pdo->query("SELECT sec.*, subj.name as subject_name FROM sections sec LEFT JOIN subjects subj ON sec.subject_id = subj.id ORDER BY subj.name, sec.name")->fetchAll();
        $this->render('admin/student/index', [
            'studentList' => $students,
            'sections' => $sections,
            'sectionFilter' => $sectionFilter
        ]);
    }

    public function addStudent() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $name = $this->sanitizeString($_POST['student_name'] ?? '');
        $section = $this->sanitizeInt($_POST['section_id'] ?? 0);
        if ($name && $section > 0) {
            $this->studentModel->create($name, $section);
        }
        $this->redirect('/admin/students');
    }

    public function editStudent() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $id = $this->sanitizeInt($_POST['student_id'] ?? 0);
        $name = $this->sanitizeString($_POST['student_name'] ?? '');
        $section = $this->sanitizeInt($_POST['section_id'] ?? 0);
        if ($id > 0 && $name && $section > 0) {
            $this->studentModel->update($id, $name, $section);
        }
        $this->redirect('/admin/students');
    }

    public function updateStudent() {
        $this->editStudent();
    }

    public function deleteStudent($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        if ($id > 0) {
            $this->studentModel->delete($id);
        }
        $this->redirect('/admin/students');
    }

    public function getStudent($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        if ($id <= 0) {
            $this->redirect('/admin/students');
        }
        $student = $this->studentModel->getById($id);
        if (!$student) {
            $this->redirect('/admin/students');
        }
        $account = $this->pdo->prepare("SELECT * FROM users WHERE role = 'student' AND ref_id = ?");
        $account->execute([$id]);
        $accountData = $account->fetch();
        $this->render('admin/student/edit', [
            'student' => $student,
            'account' => $accountData
        ]);
    }

    public function removeStudentAccount($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        if ($id > 0) {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE role = 'student' AND ref_id = ?");
            $stmt->execute([$id]);
        }
        $this->redirect("/admin/students/edit/{$id}");
    }

    public function createStudentAccount($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        if ($id > 0) {
            $student = $this->studentModel->getById($id);
            if ($student) {
                $username = strtolower(preg_replace('/\s+/', '.', $student['name'])) . $id;
                $password = 'internship123';
                $this->userModel->create([
                    'full_name' => $student['name'],
                    'username' => $username,
                    'password' => $password,
                    'role' => 'student',
                    'ref_id' => $id
                ]);
            }
        }
        $this->redirect("/admin/students/edit/{$id}");
    }

    public function studentAttendance($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        if ($id <= 0) {
            $this->redirect('/admin/students');
        }
        $student = $this->studentModel->getById($id);
        if (!$student) {
            $this->redirect('/admin/students');
        }
        $attendance = $this->pdo->prepare("SELECT a.*, subj.name AS subject_name FROM attendance a JOIN students s ON a.student_id = s.id JOIN sections sec ON s.section_id = sec.id JOIN subjects subj ON sec.subject_id = subj.id WHERE a.student_id = ? ORDER BY a.date DESC");
        $attendance->execute([$id]);
        $records = $attendance->fetchAll();
        $this->render('admin/student/attendance', [
            'student' => $student,
            'attendanceRecords' => $records
        ]);
    }

    public function moveStudent($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        $sectionId = $this->sanitizeInt($_GET['section_id'] ?? 0);
        if ($id > 0 && $sectionId > 0) {
            $this->studentModel->update($id, $_POST['student_name'] ?? $this->studentModel->getById($id)['name'], $sectionId);
        }
        $this->redirect('/admin/students');
    }

    public function resetStudentPassword() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $id = $this->sanitizeInt($_POST['student_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        if ($id > 0 && $newPassword) {
            $this->userModel->updateByRefId($id, ['password' => $newPassword, 'role' => 'student']);
        }
        $this->redirect("/admin/students/edit/{$id}");
    }

    public function changeStudentUsername() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $id = $this->sanitizeInt($_POST['student_id'] ?? 0);
        $username = $this->sanitizeString($_POST['new_username'] ?? '');
        if ($id > 0 && $username) {
            $this->userModel->updateByRefId($id, ['username' => $username, 'role' => 'student']);
        }
        $this->redirect("/admin/students/edit/{$id}");
    }

    public function instructors() {
        $this->requireAdmin();
        $search = $this->sanitizeString($_GET['search'] ?? '');
        if ($search) {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = 'instructor' AND (full_name LIKE ? OR username LIKE ?) ORDER BY full_name");
            $stmt->execute(["%{$search}%", "%{$search}%"]);
            $instructors = $stmt->fetchAll();
        } else {
            $instructors = $this->userModel->getAllByRole('instructor');
        }

        $allSubjects = $this->pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
        $allSections = $this->pdo->query("SELECT sec.*, subj.name as subject_name FROM sections sec LEFT JOIN subjects subj ON sec.subject_id = subj.id ORDER BY subj.name, sec.name")->fetchAll();

        $this->render('admin/instructors/index', [
            'instructors' => $instructors,
            'allSubjects' => $allSubjects,
            'allSections' => $allSections,
            'search' => $search
        ]);
    }

    public function addInstructor() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $full = $this->sanitizeString($_POST['full_name'] ?? '');
        $username = $this->sanitizeString($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($full && $username && $password) {
            $this->userModel->create([
                'full_name' => $full,
                'username' => $username,
                'password' => $password,
                'role' => 'instructor'
            ]);
        }
        $this->redirect('/admin/instructors');
    }

    public function getInstructor($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        if ($id <= 0) {
            $this->redirect('/admin/instructors');
        }
        $instructor = $this->userModel->getById($id);
        if (!$instructor || $instructor['role'] !== 'instructor') {
            $this->redirect('/admin/instructors');
        }
        $assignedSubjects = $this->pdo->prepare("SELECT subj.* FROM instructor_subjects ins JOIN subjects subj ON ins.subject_id = subj.id WHERE ins.instructor_id = ?");
        $assignedSubjects->execute([$id]);
        $assignedSections = $this->pdo->prepare("SELECT sec.*, subj.name as subject_name FROM instructor_sections ins JOIN sections sec ON ins.section_id = sec.id JOIN subjects subj ON sec.subject_id = subj.id WHERE ins.instructor_id = ?");
        $assignedSections->execute([$id]);
        $allSubjects = $this->pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
        $allSections = $this->pdo->query("SELECT sec.*, subj.name as subject_name FROM sections sec LEFT JOIN subjects subj ON sec.subject_id = subj.id ORDER BY subj.name, sec.name")->fetchAll();
        $this->render('admin/instructors/edit', [
            'instructor' => $instructor,
            'assignedSubjects' => $assignedSubjects->fetchAll(),
            'assignedSections' => $assignedSections->fetchAll(),
            'allSubjects' => $allSubjects,
            'allSections' => $allSections
        ]);
    }

    public function editInstructor() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $id = $this->sanitizeInt($_POST['instructor_id'] ?? 0);
        $data = [];
        $full = $this->sanitizeString($_POST['full_name'] ?? '');
        $username = $this->sanitizeString($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($full) {
            $data['full_name'] = $full;
        }
        if ($username) {
            $data['username'] = $username;
        }
        if (!empty($password)) {
            $data['password'] = $password;
        }
        if ($id > 0 && count($data) > 0) {
            $this->userModel->update($id, $data);
        }
        $this->redirect('/admin/instructors');
    }

    public function updateInstructor() {
        $this->editInstructor();
    }

    public function deleteInstructor($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        if ($id > 0) {
            $this->userModel->delete($id);
        }
        $this->redirect('/admin/instructors');
    }

    public function editUsername() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $id = $this->sanitizeInt($_POST['instructor_id'] ?? 0);
        $username = $this->sanitizeString($_POST['username'] ?? '');
        if ($id > 0 && $username) {
            $this->userModel->update($id, ['username' => $username]);
        }
        $this->redirect('/admin/instructors');
    }

    public function resetPassword() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $id = $this->sanitizeInt($_POST['instructor_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        if ($id > 0 && $newPassword) {
            $this->userModel->update($id, ['password' => $newPassword]);
        }
        $this->redirect('/admin/instructors');
    }

    public function assignSubjects() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $id = $this->sanitizeInt($_POST['instructor_id'] ?? 0);
        $selected = $_POST['selected_subjects'] ?? [];
        if ($id > 0 && is_array($selected)) {
            $stmt = $this->pdo->prepare("INSERT IGNORE INTO instructor_subjects (instructor_id, subject_id) VALUES (?, ?)");
            foreach ($selected as $subjectId) {
                $subjectId = $this->sanitizeInt($subjectId);
                if ($subjectId > 0) {
                    $stmt->execute([$id, $subjectId]);
                }
            }
        }
        $this->redirect('/admin/instructors');
    }

    public function assignSections() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $id = $this->sanitizeInt($_POST['instructor_id'] ?? 0);
        $selected = $_POST['selected_sections'] ?? [];
        if ($id > 0 && is_array($selected)) {
            $stmt = $this->pdo->prepare("INSERT IGNORE INTO instructor_sections (instructor_id, section_id) VALUES (?, ?)");
            foreach ($selected as $sectionId) {
                $sectionId = $this->sanitizeInt($sectionId);
                if ($sectionId > 0) {
                    $stmt->execute([$id, $sectionId]);
                }
            }
        }
        $this->redirect('/admin/instructors');
    }

    public function removeSubject($id = null, $subjectId = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        $subjectId = $this->sanitizeInt($subjectId);
        if ($id > 0 && $subjectId > 0) {
            $stmt = $this->pdo->prepare("DELETE FROM instructor_subjects WHERE instructor_id = ? AND subject_id = ?");
            $stmt->execute([$id, $subjectId]);
        }
        $this->redirect('/admin/instructors');
    }

    public function removeSection($id = null, $sectionId = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        $sectionId = $this->sanitizeInt($sectionId);
        if ($id > 0 && $sectionId > 0) {
            $stmt = $this->pdo->prepare("DELETE FROM instructor_sections WHERE instructor_id = ? AND section_id = ?");
            $stmt->execute([$id, $sectionId]);
        }
        $this->redirect('/admin/instructors');
    }

    public function subjects() {
        $this->requireAdmin();
        $subjects = $this->pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
        $this->render('admin/subjects/index', ['subjects' => $subjects]);
    }

    public function getSubject($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        if ($id <= 0) {
            $this->redirect('/admin/subjects');
        }
        $stmt = $this->pdo->prepare("SELECT * FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        $subject = $stmt->fetch();
        if (!$subject) {
            $this->redirect('/admin/subjects');
        }
        $this->render('admin/subjects/edit', ['subject' => $subject]);
    }

    public function updateSubject() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $id = $this->sanitizeInt($_POST['subject_id'] ?? 0);
        $name = $this->sanitizeString($_POST['subject_name'] ?? '');
        $hours = $this->sanitizeInt($_POST['required_hours'] ?? 0);
        if ($id > 0 && $name) {
            $stmt = $this->pdo->prepare("UPDATE subjects SET name = ?, required_hours = ? WHERE id = ?");
            $stmt->execute([$name, $hours, $id]);
        }
        $this->redirect('/admin/subjects');
    }

    public function deleteSubject($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        if ($id > 0) {
            $stmt = $this->pdo->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->execute([$id]);
        }
        $this->redirect('/admin/subjects');
    }

    public function sections() {
        $this->requireAdmin();
        $sections = $this->pdo->query("SELECT sec.*, subj.name as subject_name FROM sections sec LEFT JOIN subjects subj ON sec.subject_id = subj.id ORDER BY subj.name, sec.name")->fetchAll();
        $subjects = $this->pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
        $this->render('admin/sections/index', ['sections' => $sections, 'subjects' => $subjects]);
    }

    public function addSection() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $subjectId = $this->sanitizeInt($_POST['subject_id'] ?? 0);
        $name = $this->sanitizeString($_POST['section_name'] ?? '');
        if ($subjectId > 0 && $name) {
            $stmt = $this->pdo->prepare("INSERT INTO sections (subject_id, name) VALUES (?, ?)");
            $stmt->execute([$subjectId, $name]);
        }
        $this->redirect('/admin/sections');
    }

    public function getSection($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        if ($id <= 0) {
            $this->redirect('/admin/sections');
        }
        $stmt = $this->pdo->prepare("SELECT sec.*, subj.name as subject_name FROM sections sec LEFT JOIN subjects subj ON sec.subject_id = subj.id WHERE sec.id = ?");
        $stmt->execute([$id]);
        $section = $stmt->fetch();
        if (!$section) {
            $this->redirect('/admin/sections');
        }
        $subjects = $this->pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
        $this->render('admin/sections/edit', ['section' => $section, 'subjects' => $subjects]);
    }

    public function updateSection() {
        $this->requireAdmin();
        $this->ensureMethod('POST');
        $id = $this->sanitizeInt($_POST['section_id'] ?? 0);
        $name = $this->sanitizeString($_POST['section_name'] ?? '');
        if ($id > 0 && $name) {
            $stmt = $this->pdo->prepare("UPDATE sections SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
        }
        $this->redirect('/admin/sections');
    }

    public function deleteSection($id = null) {
        $this->requireAdmin();
        $id = $this->sanitizeInt($id);
        if ($id > 0) {
            $stmt = $this->pdo->prepare("DELETE FROM sections WHERE id = ?");
            $stmt->execute([$id]);
        }
        $this->redirect('/admin/sections');
    }
}
