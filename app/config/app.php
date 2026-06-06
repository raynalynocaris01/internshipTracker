<?php
return [
    'app' => [
        'name' => 'Internship Tracker',
        'version' => '2.0.0',
        'debug' => true,
        'url' => 'http://localhost/internshipTracker'
    ],
    'db' => [
        'host' => 'localhost',
        'name' => 'internship_tracker',
        'user' => 'root',
        'pass' => ''
    ],
    'security' => [
        'csrf_token_name' => 'csrf_token',
        'session_expiry' => 7200 // 2 hours
    ]
];