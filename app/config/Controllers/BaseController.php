<?php
namespace App\Controllers;

use App\Config\Database;

abstract class BaseController {
    protected $db;
    protected $pdo;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
        $this->startSession();
    }
    
    protected function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    protected function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    protected function isTeacher() {
        $role = $_SESSION['role'] ?? '';
        return $role === 'teacher' || $role === 'instructor';
    }
    
    protected function isStudent() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
    }
    
    protected function render($view, $data = []) {
        extract($data);
        require_once __DIR__ . "/../Views/{$view}.php";
    }
    
    protected function redirect($url) {
        header("Location: {$url}");
        exit();
    }
    
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}