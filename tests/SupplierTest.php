<?php
require_once __DIR__ . '/../model/supplier/Supplier.php';
require_once __DIR__ . '/../config/database.php';

class SupplierTest
{
    private $testSupplierId;
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
            $conn->query("DELETE FROM suppliers WHERE name LIKE 'test_%'");
            $conn->close();
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }

    public function runAllTests()
    {
        echo "🏪 Running Supplier Model Tests...\n\n";

        $this->testCreateSupplier();
        $this->testListSuppliers();
        $this->testUpdateSupplier();
        $this->testDeleteSupplier();

        echo "\n✅ Supplier Model Tests Completed!\n";
    }

    public function testCreateSupplier()
    {
        echo "📝 Testing create()...\n";

        $supplier = new Supplier();
        $testName = 'test_supplier_' . time();
        $testLocation = 'Test Location City';

        // Test valid supplier creation
        $result = $supplier->create($testName, $testLocation);
        if ($result) {
            echo "✅ create() with valid data successful\n";

            // Get the created supplier ID by querying the DB directly
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $row = $conn->query("SELECT id_supplier FROM suppliers WHERE name = '$testName' LIMIT 1")->fetch_assoc();
            $this->testSupplierId = $row ? $row['id_supplier'] : null;
            if ($this->testSupplierId) {
                echo "✅ Test supplier created with ID: {$this->testSupplierId}\n";
            }
            $conn->close();
        } else {
            echo "❌ create() failed with valid data\n";
            if (!empty($supplier->errors)) {
                echo "   Errors: " . implode(', ', $supplier->errors) . "\n";
            }
        }

        // Test duplicate name rejection
        $supplier->errors = array();
        $duplicateResult = $supplier->create($testName, 'Another location');
        if (!$duplicateResult && !empty($supplier->errors)) {
            echo "✅ create() correctly rejects duplicate name and sets \$errors\n";
        } else {
            echo "❌ create() should reject duplicate name and set \$errors\n";
        }

        // Test empty name
        $supplier->errors = array();
        $emptyNameResult = $supplier->create('', 'Some location');
        if (!$emptyNameResult && !empty($supplier->errors)) {
            echo "✅ create() correctly rejects empty name and sets \$errors\n";
        } else {
            echo "❌ create() should reject empty name and set \$errors\n";
        }

        echo "\n";
    }

    public function testListSuppliers()
    {
        echo "📋 Testing getAll()...\n";

        $supplier = new Supplier();
        $suppliers = $supplier->getAll();

        if (is_array($suppliers)) {
            echo "✅ getAll() returns array\n";
            if (count($suppliers) > 0) {
                $firstSupplier = $suppliers[0];
                if (isset($firstSupplier['id_supplier']) && isset($firstSupplier['name']) && isset($firstSupplier['location'])) {
                    echo "✅ getAll() returns suppliers with correct structure (id_supplier, name, location)\n";
                } else {
                    echo "❌ getAll() missing required fields (id_supplier, name, location)\n";
                }
            } else {
                echo "⚠️  No suppliers found in database\n";
            }
        } else {
            echo "❌ getAll() does not return array\n";
        }

        echo "\n";
    }

    public function testUpdateSupplier()
    {
        echo "✏️  Testing update()...\n";

        if (!$this->testSupplierId) {
            echo "⚠️  No test supplier ID available for update test\n\n";
            return;
        }

        $supplier = new Supplier();
        $newName = 'test_supplier_updated_' . time();
        $newLocation = 'Updated Location';

        $updateResult = $supplier->update($this->testSupplierId, $newName, $newLocation);
        if ($updateResult) {
            echo "✅ update() returned true\n";

            // Verify update in database
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $row = $conn->query("SELECT name, location FROM suppliers WHERE id_supplier = {$this->testSupplierId}")->fetch_assoc();
            $conn->close();

            if ($row && $row['name'] == $newName && $row['location'] == $newLocation) {
                echo "✅ Supplier data updated correctly in database\n";
            } else {
                echo "❌ Supplier data not updated correctly\n";
            }
        } else {
            echo "❌ update() should return true\n";
            if (!empty($supplier->errors)) {
                echo "   Errors: " . implode(', ', $supplier->errors) . "\n";
            }
        }

        // Test empty name rejection
        $supplier->errors = array();
        $emptyNameResult = $supplier->update($this->testSupplierId, '', 'desc');
        if (!$emptyNameResult && !empty($supplier->errors)) {
            echo "✅ update() correctly rejects empty name and sets \$errors\n";
        } else {
            echo "❌ update() should reject empty name and set \$errors\n";
        }

        echo "\n";
    }

    public function testDeleteSupplier()
    {
        echo "🗑️  Testing delete() (soft delete)...\n";

        // Create a fresh supplier for deletion test
        $supplier = new Supplier();
        $deleteTestName = 'test_softdelete_sup_' . time();
        $createResult = $supplier->create($deleteTestName, 'Supplier to be soft deleted');

        if (!$createResult) {
            echo "❌ Could not create supplier for soft delete test\n\n";
            return;
        }

        // Get the created supplier ID
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $row = $conn->query("SELECT id_supplier FROM suppliers WHERE name = '$deleteTestName' LIMIT 1")->fetch_assoc();
        $tempSupplierId = $row ? $row['id_supplier'] : null;

        if (!$tempSupplierId) {
            echo "❌ Could not retrieve created supplier ID for soft delete test\n";
            $conn->close();
            echo "\n";
            return;
        }

        // Call delete and verify return true
        $deleteResult = $supplier->delete($tempSupplierId);
        if ($deleteResult) {
            echo "✅ delete() returned true\n";
        } else {
            echo "❌ delete() should return true\n";
            if (!empty($supplier->errors)) {
                echo "   Errors: " . implode(', ', $supplier->errors) . "\n";
            }
        }

        // Verify deleted_at is set in database
        $statusRow = $conn->query("SELECT deleted_at FROM suppliers WHERE id_supplier = $tempSupplierId")->fetch_assoc();
        if ($statusRow && $statusRow['deleted_at'] != null) {
            echo "✅ Supplier record has deleted_at set (soft delete)\n";
        } else {
            echo "❌ Supplier record should have deleted_at set after soft delete\n";
        }

        $conn->close();
        echo "\n";
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new SupplierTest();
    $test->runAllTests();
}
?>
