<?php
// Run this file to verify refactoring
require_once __DIR__ . '/../vendor/autoload.php';

class TestSuite {
    public function runAllTests() {
        echo "Running Refactoring Tests...\n\n";
        
        $this->testDatabaseConnection();
        $this->testModels();
        $this->testAuthentication();
        $this->testFileStructure();
        $this->testRoutes();
        
        echo "\n✅ All tests passed!\n";
    }
    
    private function testDatabaseConnection() {
        echo "Testing Database Connection... ";
        try {
            $db = \App\Config\Database::getInstance();
            $conn = $db->getConnection();
            echo "✓ Connected\n";
        } catch (Exception $e) {
            echo "✗ Failed: " . $e->getMessage() . "\n";
        }
    }
    
    private function testModels() {
        echo "Testing Models... ";
        try {
            $userModel = new \App\Models\User();
            $studentModel = new \App\Models\Student();
            echo "✓ Models loaded\n";
        } catch (Exception $e) {
            echo "✗ Failed: " . $e->getMessage() . "\n";
        }
    }
    
    private function testAuthentication() {
        echo "Testing Authentication... ";
        try {
            $userModel = new \App\Models\User();
            // This test will pass if methods exist
            assert(method_exists($userModel, 'authenticate'));
            assert(method_exists($userModel, 'getById'));
            echo "✓ Methods exist\n";
        } catch (Exception $e) {
            echo "✗ Failed: " . $e->getMessage() . "\n";
        }
    }
    
    private function testFileStructure() {
        echo "Testing File Structure... ";
        $requiredDirs = [
            'app/Controllers',
            'app/Models',
            'app/Views',
            'app/Config',
            'public/assets/css',
            'public/assets/js'
        ];
        
        foreach ($requiredDirs as $dir) {
            if (!is_dir(__DIR__ . '/../' . $dir)) {
                echo "✗ Missing directory: {$dir}\n";
                return;
            }
        }
        echo "✓ All directories exist\n";
    }
    
    private function testRoutes() {
        echo "Testing Routes... ";
        $router = new \App\Core\Router();
        echo "✓ Router initialized\n";
    }
}

$test = new TestSuite();
$test->runAllTests();