<?php
$pageTitle = 'Logout';
ob_start();
?>
<div class="logout-container">
    <div class="logout-card">
        <div class="card-header">
            <h2>Confirm Logout</h2>
        </div>
        <div class="card-body">
            <p>Are you sure you want to logout?</p>
            <div class="btn-group">
                <a href="/logout?confirm=yes" class="btn-yes">Yes, Logout</a>
                <a href="javascript:history.back()" class="btn-no">Cancel</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$extraCSS = '
.logout-container {
    width: 100%;
    max-width: 450px;
    margin: 100px auto;
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
    padding: 25px 20px;
    border-bottom: 3px solid #ede432;
}
.card-header h2 {
    color: white;
}
.card-body {
    padding: 35px 30px;
}
.card-body p {
    color: #555;
    margin-bottom: 25px;
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
    text-decoration: none;
    display: inline-block;
}
.btn-no {
    background: #216699;
    color: white;
    border: 2px solid #ede432;
    border-radius: 40px;
    padding: 10px 30px;
    text-decoration: none;
    display: inline-block;
}
';
include __DIR__ . '/../layouts/header.php';
echo $content;
include __DIR__ . '/../layouts/footer.php';
?>