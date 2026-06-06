<?php
require_once 'config.php';
if (!isAdmin()) { header("Location: login.php"); exit; }

$totalInstructors = $pdo->query("SELECT COUNT(*) FROM users WHERE role='instructor'")->fetchColumn();
$totalSubjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$totalSections = $pdo->query("SELECT COUNT(*) FROM sections")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:linear-gradient(135deg,#c5e0f4 0%,#a8d0e6 100%);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;min-height:100vh;}
        
        /* Navbar */
        .navbar{background:#216699;color:white;padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #ede432;}
        .navbar h2{color:white;font-size:1.3rem;}
        .badge-teacher{background:#ede432;color:#1e293b;padding:4px 12px;border-radius:40px;margin-left:12px;font-weight:bold;font-size:0.8rem;}
        .btn-logout{background:#e2362c;border:none;padding:8px 20px;border-radius:40px;color:white;cursor:pointer;font-weight:bold;text-decoration:none;display:inline-block;transition:all 0.3s;}
        .btn-logout:hover{background:#c92a20;transform:scale(1.02);}
        
        /* Admin Navigation Bar - CENTERED */
        .admin-nav{background:#1a4f77;padding:0.8rem 2rem;display:flex;justify-content:center;gap:20px;flex-wrap:wrap;border-bottom:1px solid #ede432;}
        .admin-nav a{background:transparent;color:white;text-decoration:none;padding:8px 20px;border-radius:40px;font-weight:bold;transition:all 0.3s;}
        .admin-nav a:hover{background:#ede432;color:#1a4f77;}
        .admin-nav a.active{background:#ede432;color:#1a4f77;}
        
        .container{max-width:1400px;margin:30px auto;padding:0 24px;}
        
        .card{background:white;border-radius:28px;box-shadow:0 8px 20px rgba(0,0,0,0.15);overflow:hidden;margin-bottom:28px;}
        .card-header{background:#216699;padding:20px 25px;border-bottom:3px solid #ede432;}
        .card-header h1{color:white;font-size:1.5rem;}
        .card-body{padding:25px;}
        
        .btn-primary{background:#216699;color:white;border:none;padding:8px 18px;border-radius:40px;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block;transition:all 0.3s;}
        .btn-primary:hover{background:#1a4f77;transform:scale(1.02);}
        
        .dashboard-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;}
        .dashboard-card{background:#fef9e3;border-radius:20px;padding:20px;border-left:6px solid #e2362c;text-align:center;transition:all 0.3s;}
        .dashboard-card:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,0,0,0.1);}
        .dashboard-card h3{font-size:1.5rem;margin-bottom:15px;color:#216699;}
        .dashboard-card p{font-size:2.5rem;font-weight:bold;color:#216699;margin:15px 0;}
    </style>
</head>
<body>
<div class="navbar">
    <div><h2>Internship Tracker <span class="badge-teacher">ADMIN PANEL</span></h2></div>
    <a href="logout.php" class="btn-logout">Logout</a>
</div>

<!-- Admin Navigation Bar - CENTERED -->
<div class="admin-nav">
    <a href="admin_dashboard.php" class="active">Dashboard</a>
    <a href="admin_manage_instructors.php">Instructors</a>
    <a href="admin_subjects.php">Subjects</a>
    <a href="admin_sections.php">Sections</a>
    <a href="admin_students.php">Students</a>
</div>

<div class="container">
    <div class="card">
        <div class="card-header"><h1>Admin Dashboard</h1></div>
        <div class="card-body">
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h3>Instructors</h3>
                    <p><?= $totalInstructors ?></p>
                    <a href="admin_manage_instructors.php" class="btn-primary">Manage Instructors</a>
                </div>
                <div class="dashboard-card">
                    <h3>Subjects</h3>
                    <p><?= $totalSubjects ?></p>
                    <a href="admin_subjects.php" class="btn-primary">Manage Subjects</a>
                </div>
                <div class="dashboard-card">
                    <h3>Sections</h3>
                    <p><?= $totalSections ?></p>
                    <a href="admin_sections.php" class="btn-primary">Manage Sections</a>
                </div>
                <div class="dashboard-card">
                    <h3>Students</h3>
                    <p><?= $totalStudents ?></p>
                    <a href="admin_students.php" class="btn-primary">View All Students</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>