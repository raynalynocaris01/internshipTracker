<?php
echo "<h2>Connection Test</h2>";
echo "Server IP: " . $_SERVER['SERVER_ADDR'] . "<br>";
echo "Your IP: " . $_SERVER['REMOTE_ADDR'] . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Port: " . $_SERVER['SERVER_PORT'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Test database connection
require_once 'config.php';
echo "Database: Connected successfully<br>";

// List all files in current directory
echo "<h3>Files in directory:</h3><ul>";
$files = scandir(__DIR__);
foreach($files as $file) {
    if(!is_dir($file)) {
        echo "<li>$file</li>";
    }
}
echo "</ul>";
?>