<?php
require_once __DIR__ . '/../model/user/User.php';
require_once __DIR__ . '/../config/database.php';

class UserTest
{
    private $user;
    private $testUserId;
    private $originalErrorReporting;

    public function __construct()
    {
        // Disable error reporting for clean test output
        $this->originalErrorReporting = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
        
        // Start session for tests that need it
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function __destruct()
    {
        // Restore original error reporting
        error_reporting($this->originalErrorReporting);
        
        // Clean up test data
        $this->cleanupTestData();
    }

    private function cleanupTestData()
    {
        if ($this->testUserId) {
            try {
                $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                $conn->query("DELETE FROM users WHERE username LIKE 'testuser_%'");
                $conn->close();
            } catch (Exception $e) {
                // Ignore cleanup errors
            }
        }
    }

    public function runAllTests()
    {
        echo "🧪 Running User Model Tests...\n\n";
        
        $this->testGetAllUsers();
        $this->testCreateUser();
        $this->testGetUserById();
        $this->testUpdateUser();
        $this->testDeleteUser();
        $this->testToggleUserStatus();
        $this->testChangePassword();
        $this->testGetAllRoles();
        $this->testValidation();
        
        echo "\n✅ User Model Tests Completed!\n";
    }

    public function testGetAllUsers()
    {
        echo "📋 Testing getAllUsers()...\n";
        
        $user = new User();
        $users = $user->getAllUsers();
        
        if (is_array($users)) {
            echo "✅ getAllUsers() returns array\n";
            if (count($users) > 0) {
                $firstUser = $users[0];
                if (isset($firstUser['id_user']) && isset($firstUser['username'])) {
                    echo "✅ getAllUsers() returns users with correct structure\n";
                } else {
                    echo "❌ getAllUsers() missing required fields\n";
                }
            } else {
                echo "⚠️  No users found in database\n";
            }
        } else {
            echo "❌ getAllUsers() does not return array\n";
        }
        
        echo "\n";
    }

    public function testCreateUser()
    {
        echo "👤 Testing createUser()...\n";
        
        $user = new User();
        $testUsername = 'testuser_' . time();
        $testEmail = $testUsername . '@test.com';
        
        // Test valid user creation
        $result = $user->createUser($testUsername, $testEmail, 'test123', 2);
        
        if ($result) {
            echo "✅ createUser() with valid data successful\n";
            
            // Get the created user ID for cleanup by searching in all users
            $allUsers = $user->getAllUsers();
            $this->testUserId = null;
            foreach ($allUsers as $userData) {
                if ($userData['username'] === $testUsername) {
                    $this->testUserId = $userData['id_user'];
                    break;
                }
            }
            if ($this->testUserId) {
                echo "✅ Test user created with ID: {$this->testUserId}\n";
            }
        } else {
            echo "❌ createUser() failed\n";
            if (!empty($user->errors)) {
                echo "   Errors: " . implode(', ', $user->errors) . "\n";
            }
        }
        
        // Test duplicate username
        $user->errors = array();
        $duplicateResult = $user->createUser($testUsername, 'different@test.com', 'test123', 2);
        if (!$duplicateResult) {
            echo "✅ createUser() correctly rejects duplicate username\n";
        } else {
            echo "❌ createUser() should reject duplicate username\n";
        }
        
        // Test invalid email
        $user->errors = array();
        $invalidEmailResult = $user->createUser('newuser_' . time(), 'invalid-email', 'test123', 2);
        if (!$invalidEmailResult) {
            echo "✅ createUser() correctly rejects invalid email\n";
        } else {
            echo "❌ createUser() should reject invalid email\n";
        }
        
        echo "\n";
    }

    public function testGetUserById()
    {
        echo "🔍 Testing getUserById()...\n";
        
        $user = new User();
        
        if ($this->testUserId) {
            $userData = $user->getUserById($this->testUserId);
            
            if ($userData && isset($userData['id_user'])) {
                echo "✅ getUserById() with valid ID successful\n";
                if ($userData['id_user'] == $this->testUserId) {
                    echo "✅ getUserById() returns correct user\n";
                } else {
                    echo "❌ getUserById() returns wrong user\n";
                }
            } else {
                echo "❌ getUserById() failed for valid ID\n";
            }
        } else {
            echo "⚠️  No test user ID available for getUserById test\n";
        }
        
        // Test invalid ID
        $invalidUser = $user->getUserById(999999);
        if (!$invalidUser) {
            echo "✅ getUserById() correctly returns false for invalid ID\n";
        } else {
            echo "❌ getUserById() should return false for invalid ID\n";
        }
        
        echo "\n";
    }

    public function testUpdateUser()
    {
        echo "✏️  Testing updateUser()...\n";
        
        if (!$this->testUserId) {
            echo "⚠️  No test user ID available for updateUser test\n\n";
            return;
        }
        
        $user = new User();
        $newUsername = 'updated_user_' . time();
        $newEmail = $newUsername . '@test.com';
        
        $result = $user->updateUser($this->testUserId, $newUsername, $newEmail, 1, 1);
        
        if ($result) {
            echo "✅ updateUser() successful\n";
            
            // Verify the update
            $updatedUser = $user->getUserById($this->testUserId);
            if ($updatedUser && $updatedUser['username'] === $newUsername) {
                echo "✅ updateUser() correctly updated user data\n";
            } else {
                echo "❌ updateUser() data not correctly updated\n";
            }
        } else {
            echo "❌ updateUser() failed\n";
            if (!empty($user->errors)) {
                echo "   Errors: " . implode(', ', $user->errors) . "\n";
            }
        }
        
        echo "\n";
    }

    public function testDeleteUser()
    {
        echo "🗑️  Testing deleteUser()...\n";
        
        // Create a temporary user for deletion test
        $user = new User();
        $tempUsername = 'temp_delete_' . time();
        $tempEmail = $tempUsername . '@test.com';
        
        $createResult = $user->createUser($tempUsername, $tempEmail, 'test123', 2);
        if ($createResult) {
            // Get the created user ID by searching in all users
            $allUsers = $user->getAllUsers();
            $tempUserId = null;
            foreach ($allUsers as $userData) {
                if ($userData['username'] === $tempUsername) {
                    $tempUserId = $userData['id_user'];
                    break;
                }
            }
            if ($tempUserId) {
                $deleteResult = $user->deleteUser($tempUserId);
                
                if ($deleteResult) {
                    echo "✅ deleteUser() successful\n";
                    
                    // Verify deletion
                    $deletedUser = $user->getUserById($tempUserId);
                    if (!$deletedUser) {
                        echo "✅ deleteUser() correctly removed user\n";
                    } else {
                        echo "❌ deleteUser() user still exists\n";
                    }
                } else {
                    echo "❌ deleteUser() failed\n";
                }
            }
        } else {
            echo "❌ Could not create user for deletion test\n";
        }
        
        // Test deleting admin user (should fail)
        $adminDeleteResult = $user->deleteUser(1);
        if (!$adminDeleteResult) {
            echo "✅ deleteUser() correctly prevents admin deletion\n";
        } else {
            echo "❌ deleteUser() should prevent admin deletion\n";
        }
        
        echo "\n";
    }

    public function testToggleUserStatus()
    {
        echo "🔄 Testing toggleUserStatus()...\n";
        
        if (!$this->testUserId) {
            echo "⚠️  No test user ID available for toggleUserStatus test\n\n";
            return;
        }
        
        $user = new User();
        
        // Get current status
        $currentUser = $user->getUserById($this->testUserId);
        $currentStatus = $currentUser ? $currentUser['status'] : 1;
        
        $result = $user->toggleUserStatus($this->testUserId);
        
        if ($result) {
            echo "✅ toggleUserStatus() successful\n";
            
            // Verify status change
            $updatedUser = $user->getUserById($this->testUserId);
            if ($updatedUser && $updatedUser['status'] != $currentStatus) {
                echo "✅ toggleUserStatus() correctly changed status\n";
            } else {
                echo "❌ toggleUserStatus() status not changed\n";
            }
        } else {
            echo "❌ toggleUserStatus() failed\n";
        }
        
        // Test toggling admin user (should fail)
        $adminToggleResult = $user->toggleUserStatus(1);
        if (!$adminToggleResult) {
            echo "✅ toggleUserStatus() correctly prevents admin status change\n";
        } else {
            echo "❌ toggleUserStatus() should prevent admin status change\n";
        }
        
        echo "\n";
    }

    public function testChangePassword()
    {
        echo "🔐 Testing changePassword()...\n";
        
        if (!$this->testUserId) {
            echo "⚠️  No test user ID available for changePassword test\n\n";
            return;
        }
        
        $user = new User();
        
        $result = $user->updatePassword($this->testUserId, 'newpassword123');
        
        if ($result) {
            echo "✅ changePassword() successful\n";
        } else {
            echo "❌ changePassword() failed\n";
            if (!empty($user->errors)) {
                echo "   Errors: " . implode(', ', $user->errors) . "\n";
            }
        }
        
        // Test invalid user ID
        $invalidResult = $user->updatePassword(999999, 'password123');
        if (!$invalidResult) {
            echo "✅ changePassword() correctly handles invalid user ID\n";
        } else {
            echo "❌ changePassword() should fail for invalid user ID\n";
        }
        
        echo "\n";
    }

    public function testGetAllRoles()
    {
        echo "🎭 Testing getAllRoles()...\n";
        
        $user = new User();
        $roles = $user->getAllRoles();
        
        if (is_array($roles)) {
            echo "✅ getAllRoles() returns array\n";
            if (count($roles) > 0) {
                $firstRole = $roles[0];
                if (isset($firstRole['id_rol']) && isset($firstRole['name'])) {
                    echo "✅ getAllRoles() returns roles with correct structure\n";
                } else {
                    echo "❌ getAllRoles() missing required fields\n";
                }
            } else {
                echo "⚠️  No roles found in database\n";
            }
        } else {
            echo "❌ getAllRoles() does not return array\n";
        }
        
        echo "\n";
    }

    public function testValidation()
    {
        echo "🔍 Testing input validation...\n";
        
        // Test empty username via createUser
        $user = new User();
        $user->errors = array();
        $result1 = $user->createUser('', 'test@test.com', 'password123', 1);
        if (!$result1 && !empty($user->errors)) {
            echo "✅ Validation correctly rejects empty username\n";
        } else {
            echo "❌ Validation should reject empty username\n";
        }
        
        // Test short password via createUser
        $user->errors = array();
        $result2 = $user->createUser('testuser2', 'test2@test.com', '123', 1);
        if (!$result2 && !empty($user->errors)) {
            echo "✅ Validation correctly rejects short password\n";
        } else {
            echo "❌ Validation should reject short password\n";
        }
        
        // Test short password via updatePassword
        $user->errors = array();
        $result3 = $user->updatePassword(1, '123');
        if (!$result3 && !empty($user->errors)) {
            echo "✅ updatePassword correctly rejects short password\n";
        } else {
            echo "❌ updatePassword should reject short password\n";
        }
        
        echo "\n";
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new UserTest();
    $test->runAllTests();
}
?>
