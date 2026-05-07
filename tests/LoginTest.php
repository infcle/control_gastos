<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/login/Login.php';

class LoginTest
{
    private $testDb;
    private $originalDb;
    
    public function __construct()
    {
        // Backup original database config
        $this->originalDb = [
            'host' => DB_HOST,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'name' => DB_NAME
        ];
        
        // Setup test database
        $this->setupTestDatabase();
    }
    
    private function setupTestDatabase()
    {
        // Create test database connection
        $this->testDb = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Create test tables if they don't exist
        $this->testDb->query("
            CREATE TABLE IF NOT EXISTS roles (
                id_rol INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL UNIQUE,
                description TEXT,
                status TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->testDb->query("
            CREATE TABLE IF NOT EXISTS users (
                id_user INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(100) UNIQUE,
                status TINYINT(1) DEFAULT 1,
                id_rol INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
            )
        ");
        
        // Insert test data
        $this->insertTestData();
    }
    
    private function insertTestData()
    {
        // Insert test role
        $this->testDb->query("INSERT IGNORE INTO roles (name, description) VALUES ('Test User', 'Test role')");
        
        // Insert test user with known password: 'test123'
        $testPassword = password_hash('test123', PASSWORD_DEFAULT);
        $this->testDb->query("
            INSERT IGNORE INTO users (username, password, email, id_rol) 
            VALUES ('testuser', '$testPassword', 'test@example.com', 1)
        ");
    }
    
    public function testValidLogin()
    {
        echo "🧪 Testing valid login...\n";
        
        // Mock POST data
        $_POST['user_name'] = 'testuser';
        $_POST['password'] = 'test123';
        $_POST['btnSingIn'] = true;
        
        // Start session for testing
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $login = new Login();
        
        // Check if login was successful
        $result = $login->isConected();
        
        if ($result) {
            echo "✅ Valid login test PASSED\n";
            echo "   Session user: " . $_SESSION['user_name'] . "\n";
            echo "   Session role: " . $_SESSION['rol'] . "\n";
        } else {
            echo "❌ Valid login test FAILED\n";
            if (!empty($login->errors)) {
                echo "   Errors: " . implode(', ', $login->errors) . "\n";
            }
        }
        
        // Clean up session
        $_SESSION = array();
        session_destroy();
        
        return $result;
    }
    
    public function testInvalidPassword()
    {
        echo "\n🧪 Testing invalid password...\n";
        
        // Mock POST data with wrong password
        $_POST['user_name'] = 'testuser';
        $_POST['password'] = 'wrongpassword';
        $_POST['btnSingIn'] = true;
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $login = new Login();
        $result = $login->isConected();
        
        if (!$result && !empty($login->errors)) {
            echo "✅ Invalid password test PASSED\n";
            echo "   Error: " . $login->errors[0] . "\n";
        } else {
            echo "❌ Invalid password test FAILED\n";
        }
        
        $_SESSION = array();
        session_destroy();
        
        return !$result;
    }
    
    public function testNonExistentUser()
    {
        echo "\n🧪 Testing non-existent user...\n";
        
        // Mock POST data with non-existent user
        $_POST['user_name'] = 'nonexistent';
        $_POST['password'] = 'anypassword';
        $_POST['btnSingIn'] = true;
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $login = new Login();
        $result = $login->isConected();
        
        if (!$result && !empty($login->errors)) {
            echo "✅ Non-existent user test PASSED\n";
            echo "   Error: " . $login->errors[0] . "\n";
        } else {
            echo "❌ Non-existent user test FAILED\n";
        }
        
        $_SESSION = array();
        session_destroy();
        
        return !$result;
    }
    
    public function testEmptyFields()
    {
        echo "\n🧪 Testing empty fields...\n";
        
        // Test empty username
        $_POST['user_name'] = '';
        $_POST['password'] = 'password123';
        $_POST['btnSingIn'] = true;
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $login = new Login();
        
        if (!empty($login->errors)) {
            echo "✅ Empty username test PASSED\n";
            echo "   Error: " . $login->errors[0] . "\n";
        } else {
            echo "❌ Empty username test FAILED\n";
        }
        
        $_SESSION = array();
        session_destroy();
        
        return !empty($login->errors);
    }
    
    public function runAllTests()
    {
        echo "🚀 Running Login System Tests\n";
        echo "================================\n";
        
        $results = [];
        $results[] = $this->testValidLogin();
        $results[] = $this->testInvalidPassword();
        $results[] = $this->testNonExistentUser();
        $results[] = $this->testEmptyFields();
        
        $passed = array_sum($results);
        $total = count($results);
        
        echo "\n📊 Test Results Summary\n";
        echo "========================\n";
        echo "Passed: $passed/$total tests\n";
        
        if ($passed === $total) {
            echo "🎉 All tests PASSED!\n";
        } else {
            echo "⚠️  Some tests FAILED!\n";
        }
        
        return $passed === $total;
    }
    
    public function __destruct()
    {
        if ($this->testDb) {
            $this->testDb->close();
        }
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new LoginTest();
    $test->runAllTests();
}
?>
