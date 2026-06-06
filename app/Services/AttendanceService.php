<?php
namespace App\Services;

use App\Config\Database;

class AttendanceService {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function recordAttendance($studentId, $date, $sessionType, $time) {
        $columnMap = [
            'morning_in' => 'morning_in',
            'morning_out' => 'morning_out',
            'afternoon_in' => 'afternoon_in',
            'afternoon_out' => 'afternoon_out'
        ];
        
        $column = $columnMap[$sessionType] ?? null;
        if (!$column) {
            return ['success' => false, 'message' => 'Invalid session type'];
        }
        
        // Check if record exists
        $stmt = $this->pdo->prepare("SELECT id, {$column} FROM attendance WHERE student_id = ? AND date = ?");
        $stmt->execute([$studentId, $date]);
        $existing = $stmt->fetch();
        
        if ($existing && !empty($existing[$column])) {
            return ['success' => false, 'message' => 'Attendance already recorded for this session'];
        }
        
        // Begin transaction
        $this->pdo->beginTransaction();
        
        try {
            if ($existing) {
                $stmt = $this->pdo->prepare("UPDATE attendance SET {$column} = ? WHERE student_id = ? AND date = ?");
                $stmt->execute([$time, $studentId, $date]);
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO attendance (student_id, date, {$column}) VALUES (?, ?, ?)");
                $stmt->execute([$studentId, $date, $time]);
            }
            
            // Recalculate total hours
            $this->calculateTotalHours($studentId, $date);
            
            $this->pdo->commit();
            return ['success' => true, 'message' => 'Attendance recorded successfully'];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    private function calculateTotalHours($studentId, $date) {
        $stmt = $this->pdo->prepare("
            SELECT morning_in, morning_out, afternoon_in, afternoon_out 
            FROM attendance 
            WHERE student_id = ? AND date = ?
        ");
        $stmt->execute([$studentId, $date]);
        $record = $stmt->fetch();
        
        $morningHours = 0;
        $afternoonHours = 0;
        
        if ($record['morning_in'] && $record['morning_out']) {
            $morningHours = round(
                (strtotime($record['morning_out']) - strtotime($record['morning_in'])) / 3600, 
                2
            );
        }
        
        if ($record['afternoon_in'] && $record['afternoon_out']) {
            $afternoonHours = round(
                (strtotime($record['afternoon_out']) - strtotime($record['afternoon_in'])) / 3600, 
                2
            );
        }
        
        $total = $morningHours + $afternoonHours;
        
        $stmt = $this->pdo->prepare("UPDATE attendance SET total_hours = ? WHERE student_id = ? AND date = ?");
        $stmt->execute([$total, $studentId, $date]);
    }
}