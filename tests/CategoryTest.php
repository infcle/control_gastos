<?php
require_once __DIR__ . '/../model/category/Category.php';
require_once __DIR__ . '/../model/product/Product.php';
require_once __DIR__ . '/../config/database.php';

class CategoryTest
{
    private $testCategoryId;
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
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            // Delete products created by FK guard test
            $conn->query("DELETE FROM prices WHERE id_product IN (SELECT id_product FROM products WHERE name LIKE 'test_catfk_%')");
            $conn->query("DELETE FROM products WHERE name LIKE 'test_catfk_%'");
            // Delete test categories
            $conn->query("DELETE FROM categories WHERE name LIKE 'test_%'");
            $conn->close();
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }

    public function runAllTests()
    {
        echo "🗂️  Running Category Model Tests...\n\n";

        $this->testCreateCategory();
        $this->testListCategories();
        $this->testUpdateCategory();
        $this->testDeleteCategory();
        $this->testCascadeDeleteProductGuard();

        echo "\n✅ Category Model Tests Completed!\n";
    }

    public function testCreateCategory()
    {
        echo "📝 Testing create()...\n";

        $category = new Category();
        $testName = 'test_cat_' . time();

        // Test valid category creation
        $result = $category->create($testName, 'Test description for category');
        if ($result) {
            echo "✅ create() with valid data successful\n";

            // Get the created category ID by querying the DB directly
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $row = $conn->query("SELECT id_category FROM categories WHERE name = '$testName' LIMIT 1")->fetch_assoc();
            $this->testCategoryId = $row ? $row['id_category'] : null;
            if ($this->testCategoryId) {
                echo "✅ Test category created with ID: {$this->testCategoryId}\n";
            }
            $conn->close();
        } else {
            echo "❌ create() failed with valid data\n";
            if (!empty($category->errors)) {
                echo "   Errors: " . implode(', ', $category->errors) . "\n";
            }
        }

        // Test duplicate name rejection
        $category->errors = array();
        $duplicateResult = $category->create($testName, 'Another description');
        if (!$duplicateResult && !empty($category->errors)) {
            echo "✅ create() correctly rejects duplicate name and sets \$errors\n";
        } else {
            echo "❌ create() should reject duplicate name and set \$errors\n";
        }

        // Test empty name
        $category->errors = array();
        $emptyNameResult = $category->create('', 'Test description');
        if (!$emptyNameResult && !empty($category->errors)) {
            echo "✅ create() correctly rejects empty name and sets \$errors\n";
        } else {
            echo "❌ create() should reject empty name and set \$errors\n";
        }

        echo "\n";
    }

    public function testListCategories()
    {
        echo "📋 Testing getAll()...\n";

        $category = new Category();
        $categories = $category->getAll();

        if (is_array($categories)) {
            echo "✅ getAll() returns array\n";
            if (count($categories) > 0) {
                $firstCategory = $categories[0];
                if (isset($firstCategory['id_category']) && isset($firstCategory['name']) && isset($firstCategory['description'])) {
                    echo "✅ getAll() returns categories with correct structure (id_category, name, description)\n";
                } else {
                    echo "❌ getAll() missing required fields (id_category, name, description)\n";
                }
            } else {
                echo "⚠️  No categories found in database\n";
            }
        } else {
            echo "❌ getAll() does not return array\n";
        }

        echo "\n";
    }

    public function testUpdateCategory()
    {
        echo "✏️  Testing update()...\n";

        if (!$this->testCategoryId) {
            echo "⚠️  No test category ID available for update test\n\n";
            return;
        }

        $category = new Category();
        $newName = 'test_cat_updated_' . time();
        $newDescription = 'Updated description for category';

        $updateResult = $category->update($this->testCategoryId, $newName, $newDescription);
        if ($updateResult) {
            echo "✅ update() returned true\n";

            // Verify update in database
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $row = $conn->query("SELECT name, description FROM categories WHERE id_category = {$this->testCategoryId}")->fetch_assoc();
            $conn->close();

            if ($row && $row['name'] == $newName && $row['description'] == $newDescription) {
                echo "✅ Category data updated correctly in database\n";
            } else {
                echo "❌ Category data not updated correctly\n";
            }
        } else {
            echo "❌ update() should return true\n";
            if (!empty($category->errors)) {
                echo "   Errors: " . implode(', ', $category->errors) . "\n";
            }
        }

        // Test empty name rejection
        $category->errors = array();
        $emptyNameResult = $category->update($this->testCategoryId, '', 'desc');
        if (!$emptyNameResult && !empty($category->errors)) {
            echo "✅ update() correctly rejects empty name and sets \$errors\n";
        } else {
            echo "❌ update() should reject empty name and set \$errors\n";
        }

        echo "\n";
    }

    public function testDeleteCategory()
    {
        echo "🗑️  Testing delete() (soft delete)...\n";

        // Create a fresh category for deletion test
        $category = new Category();
        $deleteTestName = 'test_softdelete_cat_' . time();
        $createResult = $category->create($deleteTestName, 'Category to be soft deleted');

        if (!$createResult) {
            echo "❌ Could not create category for soft delete test\n\n";
            return;
        }

        // Get the created category ID
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $row = $conn->query("SELECT id_category FROM categories WHERE name = '$deleteTestName' LIMIT 1")->fetch_assoc();
        $tempCategoryId = $row ? $row['id_category'] : null;

        if (!$tempCategoryId) {
            echo "❌ Could not retrieve created category ID for soft delete test\n";
            $conn->close();
            echo "\n";
            return;
        }

        // Call delete and verify return true
        $deleteResult = $category->delete($tempCategoryId);
        if ($deleteResult) {
            echo "✅ delete() returned true\n";
        } else {
            echo "❌ delete() should return true\n";
            if (!empty($category->errors)) {
                echo "   Errors: " . implode(', ', $category->errors) . "\n";
            }
        }

        // Verify deleted_at is set in database
        $statusRow = $conn->query("SELECT deleted_at FROM categories WHERE id_category = $tempCategoryId")->fetch_assoc();
        if ($statusRow && $statusRow['deleted_at'] != null) {
            echo "✅ Category record has deleted_at set (soft delete)\n";
        } else {
            echo "❌ Category record should have deleted_at set after soft delete\n";
        }

        $conn->close();
        echo "\n";
    }

    public function testCascadeDeleteProductGuard()
    {
        echo "🔒 Testing cascade delete guard (product references category)...\n";

        // Create a category for this test
        $category = new Category();
        $guardCatName = 'test_guard_cat_' . time();
        $createResult = $category->create($guardCatName, 'Category for FK guard test');

        if (!$createResult) {
            echo "❌ Could not create category for FK guard test\n\n";
            return;
        }

        // Get the category ID
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $row = $conn->query("SELECT id_category FROM categories WHERE name = '$guardCatName' LIMIT 1")->fetch_assoc();
        $guardCategoryId = $row ? $row['id_category'] : null;

        if (!$guardCategoryId) {
            echo "❌ Could not retrieve category ID for FK guard test\n";
            $conn->close();
            echo "\n";
            return;
        }

        // Create a product that references this category
        $product = new Product();
        $price_id = 1; // Use existing price ID 1 (10.00)
        $productName = 'test_catfk_prod_' . time();
        $productResult = $product->createProduct($productName, 'Product for FK guard test', $price_id, $guardCategoryId);

        if (!$productResult) {
            echo "❌ Could not create product for FK guard test\n";
            echo "   Errors: " . implode(', ', $product->errors) . "\n";
            // Clean up the category
            $category->delete($guardCategoryId);
            $conn->close();
            echo "\n";
            return;
        }

        // Now try to delete the category — should be blocked by FK guard
        $category->errors = array();
        $deleteAttempt = $category->delete($guardCategoryId);
        if (!$deleteAttempt && !empty($category->errors)) {
            echo "✅ delete() correctly blocked: category has active products\n";
            echo "   Error: " . $category->errors[0] . "\n";
        } else {
            echo "❌ delete() should be blocked when category has active products\n";
        }

        // Delete the test product manually so cleanup can proceed
        $conn->query("DELETE FROM prices WHERE id_product IN (SELECT id_product FROM products WHERE name = '$productName')");
        $conn->query("DELETE FROM products WHERE name = '$productName'");
        // Now category can be deleted (cleanup will handle it)
        $category->delete($guardCategoryId);

        $conn->close();
        echo "\n";
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new CategoryTest();
    $test->runAllTests();
}
?>
