<?php
namespace App\Models;

use App\Config\Database;

class Student {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function getAll($sectionId = null) {
        if ($sectionId) {
            $stmt = $this->pdo->prepare("
                SELECT s.*, sec.name as section_name, subj.name as subject_name 
                FROM students s
                JOIN sections sec ON s.section_id = sec.id
                JOIN subjects subj ON sec.subject_id = subj.id
                WHERE s.section_id = ?
                ORDER BY s.name
            ");
            $stmt->execute([$sectionId]);
        } else {
            $stmt = $this->pdo->query("
                SELECT s.*, sec.name as section_name, subj.name as subject_name 
                FROM students s
                JOIN sections sec ON s.section_id = sec.id
                JOIN subjects subj ON sec.subject_id = subj.id
                ORDER BY s.name
            ");
        }
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($name, $sectionId) {
        $stmt = $this->pdo->prepare("INSERT INTO students (name, section_id) VALUES (?, ?)");
        return $stmt->execute([$name, $sectionId]);
    }
    
    public function update($id, $name, $sectionId) {
        $stmt = $this->pdo->prepare("UPDATE students SET name = ?, section_id = ? WHERE id = ?");
        return $stmt->execute([$name, $sectionId, $id]);
    }
    
    public function delete($id) {
        // Delete associated user account first
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE role = 'student' AND ref_id = ?");
        $stmt->execute([$id]);
        
        // Delete student
        $stmt = $this->pdo->prepare("DELETE FROM students WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getAttendance($studentId, $subjectId = null) {
        $sql = "
            SELECT a.*, subj.name as subject_name 
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            JOIN sections sec ON s.section_id = sec.id
            JOIN subjects subj ON sec.subject_id = subj.id
            WHERE a.student_id = ?
        ";
        $params = [$studentId];
        
        if ($subjectId) {
            $sql .= " AND subj.id = ?";
            $params[] = $subjectId;
        }
        
        $sql .= " ORDER BY a.date DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}