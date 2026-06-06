<?php
$pageTitle = 'Login - Internship Tracker';
ob_start();
?>
<div class="login-container">
    <div class="login-card">
        <div class="card-header">
            <h2>Internship Tracker</h2>
            <p>Student & Teacher Portal</p>
        </div>
        <div class="card-body">
            <?php if (isset($error) && $error): ?>
                <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if (isset($qr_redirect) && $qr_redirect): ?>
                <div class="qr-info">📱 Please login to complete your time request.</div>
            <?php endif; ?>
            
            <form method="POST" action="/login">
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
            
            <div class="footer-note">Accounts are managed by your administrator</div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.login-container {
    width: 100%;
    max-width: 450px;
    margin: 50px auto;
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
}
.form-control:focus {
    outline: none;
    border-color: #ede432;
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
}
.btn-login:hover {
    background: #ede432;
    color: #216699;
}
.alert-danger {
    background: #f8d7da;
    color: #721c24;
    padding: 12px;
    border-radius: 20px;
    margin-bottom: 20px;
    text-align: center;
}
.qr-info {
    background: #e8f4f8;
    padding: 10px;
    border-radius: 20px;
    text-align: center;
    margin-bottom: 20px;
    color: #216699;
}
.footer-note {
    margin-top: 25px;
    text-align: center;
    font-size: 0.75rem;
    color: #888;
}
';
include __DIR__ . '/../layouts/header.php';
echo $content;
include __DIR__ . '/../layouts/footer.php';
?>