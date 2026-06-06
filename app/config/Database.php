<?php
namespace App\Config;

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        // config is located at app/config/app.php
        $config = require_once __DIR__ . '/app.php';
        try {
            $this->pdo = new \PDO(
                "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8",
                $config['db']['user'],
                $config['db']['pass']
            );
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch(\PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}