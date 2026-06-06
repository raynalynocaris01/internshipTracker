<?php
$pageTitle = 'Add New Instructor';
$currentPage = 'instructors';
ob_start();
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Add New Instructor</h1>
            <a href="/admin/instructors" class="btn-warning">← Back to Instructors</a>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/instructors/add" class="form-container">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required placeholder="Enter instructor's full name">
                </div>
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required placeholder="Enter username for login">
                    <small>Username must be unique</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required placeholder="Enter password">
                    <small>Minimum 6 characters recommended</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter password">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Instructor</button>
                    <a href="/admin/instructors" class="btn-danger">Cancel</a>
                </div>
            </form>
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
.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 40px;
    font-size: 1rem;
    transition: all 0.3s;
}
.form-group input:focus {
    outline: none;
    border-color: #216699;
}
.form-group small {
    display: block;
    margin-top: 5px;
    color: #888;
    font-size: 0.8rem;
}
.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 30px;
}
';
$extraJS = '
document.querySelector("form").onsubmit = function(e) {
    var password = document.getElementById("password").value;
    var confirm = document.getElementById("confirm_password").value;
    if (password !== confirm) {
        e.preventDefault();
        alert("Passwords do not match!");
        return false;
    }
    if (password.length < 6) {
        e.preventDefault();
        alert("Password must be at least 6 characters!");
        return false;
    }
};
';
include __DIR__ . '/../../layouts/header.php';
include __DIR__ . '/../../layouts/navbar.php';
echo $content;
include __DIR__ . '/../../layouts/footer.php';
?>