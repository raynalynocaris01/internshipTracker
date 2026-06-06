<?php
require_once 'config.php';
echo "Session role: " . ($_SESSION['role'] ?? 'NOT SET') . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    echo "Database role: " . $user['role'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
}
?>