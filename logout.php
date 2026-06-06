<?php
// Check if confirmation is received
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    session_start();
    session_destroy();
    header("Location: index.php");
    exit();
}

// If no confirmation, show confirmation page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Internship Tracker</title>
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
        
        .logout-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .logout-card {
            background: white;
            border-radius: 28px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            overflow: hidden;
            text-align: center;
        }
        
        .card-header {
            background: #216699;
            color: #ede432;
            padding: 25px 20px;
            border-bottom: 3px solid #ede432;
        }
        
        .card-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .card-body {
            padding: 35px 30px;
        }
        
        .card-body p {
            color: #555;
            margin-bottom: 25px;
            font-size: 1rem;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-yes {
            background: #e2362c;
            color: white;
            border: none;
            border-radius: 40px;
            padding: 10px 30px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-yes:hover {
            background: #c92a20;
        }
        
        .btn-no {
            background: #216699;
            color: white;
            border: 2px solid #ede432;
            border-radius: 40px;
            padding: 10px 30px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-no:hover {
            background: #ede432;
            color: #216699;
            border-color: #216699;
        }
    </style>
</head>
<body>
<div class="logout-container">
    <div class="logout-card">
        <div class="card-header">
            <h2>Confirm Logout</h2>
        </div>
        <div class="card-body">
            <p>Are you sure you want to logout?</p>
            <div class="btn-group">
                <a href="logout.php?confirm=yes" class="btn-yes">Yes, Logout</a>
                <a href="javascript:history.back()" class="btn-no">Cancel</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>