<?php
$pageTitle = 'Edit Student';
$currentPage = 'students';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Edit Student</h1>
            <a href="/admin/students" class="btn-warning">← Back to Students</a>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/students/update" class="form-container">
                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                
                <div class="form-group">
                    <label for="student_name">Student Full Name *</label>
                    <input type="text" id="student_name" name="student_name" required value="<?= htmlspecialchars($student['name']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="section_id">Select Section *</label>
                    <select id="section_id" name="section_id" required>
                        <option value="">-- Select a Section --</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['id'] ?>" <?= ($student['section_id'] == $sec['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sec['subject_name']) ?> - <?= htmlspecialchars($sec['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="student_id_number">Student ID Number</label>
                    <input type="text" id="student_id_number" name="student_id_number" value="<?= htmlspecialchars($student['student_id_number'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Contact Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Update Student</button>
                    <a href="/admin/students" class="btn-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Account Management Section -->
    <div class="card">
        <div class="card-header">
            <h3>Account Management</h3>
        </div>
        <div class="card-body">
            <?php if ($studentAccount): ?>
                <div class="account-info">
                    <p><strong>Username:</strong> <?= htmlspecialchars($studentAccount['username']) ?></p>
                    <p><strong>Account Status:</strong> <span class="badge-success">Active</span></p>
                    <div class="button-group">
                        <button onclick="resetStudentPassword(<?= $student['id'] ?>)" class="btn-warning">Reset Password</button>
                        <button onclick="changeStudentUsername(<?= $student['id'] ?>)" class="btn-warning">Change Username</button>
                        <a href="/admin/students/remove-account/<?= $student['id'] ?>" class="btn-danger" onclick="return confirm('Remove login account?')">Remove Account</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="account-info">
                    <p>No login account exists for this student.</p>
                    <a href="/admin/students/create-account/<?= $student['id'] ?>" class="btn-primary">Create Login Account</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Attendance Summary -->
    <div class="card">
        <div class="card-header">
            <h3>Attendance Summary</h3>
        </div>
        <div class="card-body">
            <div class="stats-summary">
                <div class="stat-item">
                    <span class="stat-label">Total Hours Completed:</span>
                    <span class="stat-value"><?= $totalHours ?> hrs</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Total Days Present:</span>
                    <span class="stat-value"><?= $totalDays ?> days</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Last Attendance:</span>
                    <span class="stat-value"><?= $lastAttendance ?? 'Never' ?></span>
                </div>
            </div>
            <a href="/admin/students/attendance/<?= $student['id'] ?>" class="btn-primary">View Full Attendance Record</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.form-container {
    max-width: 600px;
    margin: 0 auto;
}
.form-group {
    margin-bottom: 25px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #216699;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 40px;
    font-size: 1rem;
}
.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: #216699;
}
.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
}
.account-info {
    text-align: center;
    padding: 20px;
}
.account-info p {
    margin: 10px 0;
}
.badge-success {
    background: #d4edda;
    color: #155724;
    padding: 4px 10px;
    border-radius: 20px;
    display: inline-block;
}
.button-group {
    display: flex;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
}
.stats-summary {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 20px;
}
.stat-item {
    text-align: center;
}
.stat-label {
    display: block;
    color: #666;
    margin-bottom: 5px;
}
.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #216699;
}
';
$extraJS = '
function resetStudentPassword(studentId) {
    var newPass = prompt("Enter new password (minimum 4 characters):");
    if (newPass && newPass.length >= 4) {
        fetch("/admin/students/reset-password", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "student_id=" + studentId + "&new_password=" + encodeURIComponent(newPass)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Password reset successfully!");
            } else {
                alert("Error: " + data.message);
            }
        });
    } else if (newPass) {
        alert("Password must be at least 4 characters.");
    }
}
function changeStudentUsername(studentId) {
    var newUsername = prompt("Enter new username:");
    if (newUsername && newUsername.trim()) {
        fetch("/admin/students/change-username", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "student_id=" + studentId + "&new_username=" + encodeURIComponent(newUsername)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Username changed successfully!");
                location.reload();
            } else {
                alert("Error: " + data.message);
            }
        });
    }
}
';
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/../../layouts/navbar.php';
echo $content;
include __DIR__ . '/../../layouts/footer.php';
?>