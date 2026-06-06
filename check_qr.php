<?php
require_once 'config.php';

echo "<h2>QR System Check</h2>";

// Check if table exists
$tables = $pdo->query("SHOW TABLES LIKE 'qr_sessions'");
if ($tables->rowCount() > 0) {
    echo "✅ qr_sessions table exists<br>";
    
    // Count records
    $count = $pdo->query("SELECT COUNT(*) FROM qr_sessions")->fetchColumn();
    echo "Total QR records: $count<br>";
    
    // Show latest QR
    $qr = $pdo->query("SELECT * FROM qr_sessions ORDER BY id DESC LIMIT 1")->fetch();
    if ($qr) {
        echo "<pre>";
        print_r($qr);
        echo "</pre>";
    } else {
        echo "No QR records found.<br>";
    }
} else {
    echo "❌ qr_sessions table MISSING! Run the SQL to create it.<br>";
}

// Check your IP
echo "<br>Your current IP: " . $_SERVER['SERVER_ADDR'] . "<br>";
echo "Use this IP in simple_qr.php";
?>