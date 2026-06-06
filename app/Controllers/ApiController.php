<?php
namespace App\Controllers;

use App\Models\Student;

class ApiController extends BaseController {
    public function getSubjects() {
        $subjects = $this->pdo->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
        $this->jsonResponse(['success' => true, 'subjects' => $subjects]);
    }

    public function getStudents() {
        $students = $this->pdo->query("SELECT s.*, sec.name as section_name FROM students s LEFT JOIN sections sec ON s.section_id = sec.id ORDER BY s.name")->fetchAll();
        $this->jsonResponse(['success' => true, 'students' => $students]);
    }

    public function saveAttendance() {
        $payload = json_decode(file_get_contents('php://input'), true);
        // Minimal passthrough - not performing validation here
        if (!$payload) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid payload'], 400);
        }
        $this->jsonResponse(['success' => true, 'received' => $payload]);
    }
}
