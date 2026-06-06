<?php
namespace App\Controllers;

use App\Models\User;

class AuthController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
        }
        
        $qrRedirect = isset($_GET['redirect']) && $_GET['redirect'] === 'qr';
        $qrToken = $_SESSION['qr_token'] ?? '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $user = $this->userModel->authenticate($username, $password);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['ref_id'] = $user['ref_id'];
                
                if ($qrRedirect && $qrToken && $user['role'] === 'student') {
                    unset($_SESSION['qr_token']);
                    $this->redirect("scan_qr.php?token={$qrToken}");
                }
                
                $this->redirectBasedOnRole();
            } else {
                $error = "Invalid username or password!";
            }
        }
        
        $this->render('auth/login', [
            'error' => $error ?? null,
            'qr_redirect' => $qrRedirect
        ]);
    }
    
    private function redirectBasedOnRole() {
        if ($this->isAdmin()) {
            $this->redirect('admin/dashboard');
        } elseif ($this->isTeacher()) {
            $this->redirect('teacher/dashboard');
        } elseif ($this->isStudent()) {
            $this->redirect('student/dashboard');
        }
    }
    
    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['confirm'])) {
            session_destroy();
            $this->redirect('login');
        }
        
        $this->render('auth/logout');
    }
}
