<?php
require_once 'config.php';

// Fix admin password
$adminHash = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([$adminHash]);
echo "Admin password updated to: admin123<br>";

// Fix teacher password
$teacherHash = password_hash('teacher123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'teacher'");
$stmt->execute([$teacherHash]);
echo "Teacher password updated to: teacher123<br>";

// Fix student password
$studentHash = password_hash('student123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'john.doe'");
$stmt->execute([$studentHash]);
echo "Student password updated to: student123<br>";

echo "<br>Done! You can now login with:<br>";
echo "Admin: admin / admin123<br>";
echo "Teacher: teacher / teacher123<br>";
echo "Student: john.doe / student123<br>";
?>