<?php
require_once 'config.php';

// Check if coming from QR scan
$qr_redirect = isset($_GET['redirect']) && $_GET['redirect'] == 'qr';
$qr_token = isset($_SESSION['qr_token']) ? $_SESSION['qr_token'] : '';

// If already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: admin_dashboard.php");
        exit();
    } elseif (isTeacher()) {
        header("Location: teacher_dashboard.php");
        exit();
    } elseif (isStudent()) {
        // If coming from QR scan, redirect to QR scan page
        if ($qr_redirect && $qr_token) {
            unset($_SESSION['qr_token']);
            header("Location: scan_qr.php?token=" . $qr_token);
            exit();
        }
        header("Location: student_dashboard.php");
        exit();
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['ref_id'] = $user['ref_id'];
        
        // If student, get student id from students table
        if ($user['role'] == 'student' && !$_SESSION['ref_id']) {
            $stmt2 = $pdo->prepare("SELECT id FROM students WHERE user_id = ?");
            $stmt2->execute([$user['id']]);
            $student = $stmt2->fetch();
            if ($student) {
                $_SESSION['ref_id'] = $student['id'];
                $pdo->prepare("UPDATE users SET ref_id = ? WHERE id = ?")->execute([$student['id'], $user['id']]);
            }
        }
        
        // Check if coming from QR scan
        if ($qr_redirect && $qr_token && $user['role'] == 'student') {
            unset($_SESSION['qr_token']);
            header("Location: scan_qr.php?token=" . $qr_token);
            exit();
        }
        
        // Redirect based on role
        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] == 'teacher' || $user['role'] == 'instructor') {
            header("Location: teacher_dashboard.php");
        } else {
            header("Location: student_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Tracker - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #c5e0f4 0%, #a8d0e6 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 28px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .card-header {
            background: #216699;
            color: #ede432;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 3px solid #ede432;
        }
        
        .card-header h2 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .card-header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 35px 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #216699;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 18px;
            border: 1px solid #ccc;
            border-radius: 40px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #ede432;
            box-shadow: 0 0 0 3px rgba(237, 228, 50, 0.2);
        }
        
        .btn-login {
            width: 100%;
            background: #216699;
            color: white;
            border: 2px solid #ede432;
            border-radius: 40px;
            padding: 12px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #ede432;
            color: #216699;
            border-color: #216699;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .footer-note {
            margin-top: 25px;
            text-align: center;
            font-size: 0.75rem;
            color: #888;
        }
        
        .qr-info {
            margin-top: 15px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.85rem;
            color: #216699;
            background: #e8f4f8;
            padding: 10px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <div class="card-header">
            <h2>Internship Tracker</h2>
            <p>Student & Teacher Portal</p>
        </div>
        <div class="card-body">
            <?php if($error): ?>
                <div class="alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($qr_redirect): ?>
                <div class="qr-info">
                    📱 Please login to complete your time request.
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
            
            <div class="footer-note">
                Accounts are managed by your administrator
            </div>
        </div>
    </div>
</div>
</body></html>