<?php
require_once __DIR__ . '/../model/purchase/Purchase.php';
require_once __DIR__ . '/../model/supplier/Supplier.php';
require_once __DIR__ . '/../config/database.php';

class PurchaseTest
{
    private $testPurchaseId;
    private $tempSupplierId;
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
            // Delete details for test purchases (cascade FK)
            $conn->query("DELETE FROM purchase_details WHERE id_purchase IN (SELECT id_purchase FROM purchases WHERE observation LIKE 'test_%')");
            // Delete test purchases
            $conn->query("DELETE FROM purchases WHERE observation LIKE 'test_%'");
            // Delete test suppliers created by this test
            if ($this->tempSupplierId) {
                $conn->query("DELETE FROM suppliers WHERE id_supplier = {$this->tempSupplierId}");
            }
            $conn->close();
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }

    public function runAllTests()
    {
        echo "🛒 Running Purchase Model Tests...\n\n";

        $this->testCreatePurchase();
        $this->testListPurchases();
        $this->testDeletePurchase();

        echo "\n✅ Purchase Model Tests Completed!\n";
    }

    public function testCreatePurchase()
    {
        echo "📝 Testing create()...\n";

        $purchase = new Purchase();
        $observation = 'test_purchase_' . time();

        // Check for existing products — we need at least one
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $productsResult = $conn->query("SELECT id_product FROM products WHERE deleted_at IS NULL LIMIT 2");
        $existingProducts = array();
        while ($row = $productsResult->fetch_assoc()) {
            $existingProducts[] = $row['id_product'];
        }

        if (count($existingProducts) < 1) {
            echo "❌ No products found in database. Cannot test purchase creation.\n";
            $conn->close();
            echo "\n";
            return;
        }

        // Create a supplier for this test (no existing suppliers)
        $supplier = new Supplier();
        $supplierName = 'test_supplier_for_purchase_' . time();
        $supplierResult = $supplier->create($supplierName, 'Test Supplier Location');
        if (!$supplierResult) {
            echo "❌ Could not create supplier for purchase test\n";
            if (!empty($supplier->errors)) {
                echo "   Errors: " . implode(', ', $supplier->errors) . "\n";
            }
            $conn->close();
            echo "\n";
            return;
        }

        // Get the supplier ID
        $row = $conn->query("SELECT id_supplier FROM suppliers WHERE name = '$supplierName' LIMIT 1")->fetch_assoc();
        $this->tempSupplierId = $row ? $row['id_supplier'] : null;

        if (!$this->tempSupplierId) {
            echo "❌ Could not retrieve supplier ID\n";
            $conn->close();
            echo "\n";
            return;
        }
        echo "✅ Test supplier created with ID: {$this->tempSupplierId}\n";

        // Build details array with multiple products
        $details = array();
        foreach ($existingProducts as $index => $prodId) {
            $details[] = array(
                'id_product' => $prodId,
                'id_supplier' => $this->tempSupplierId,
                'quantity' => ($index + 1) * 5,
                'unit_price' => 10.50 + ($index * 5),
                'observation' => 'test detail for product ' . $prodId
            );
        }

        // Create the purchase
        $result = $purchase->create(date('Y-m-d'), 1, $observation, $details);

        if ($result) {
            echo "✅ create() with valid data successful\n";
            $this->testPurchaseId = $result; // create() returns the purchase ID
            echo "✅ Test purchase created with ID: {$this->testPurchaseId}\n";

            // Verify details in database
            $detailsResult = $conn->query("SELECT id_purchase_detail, quantity, unit_price FROM purchase_details WHERE id_purchase = {$this->testPurchaseId} AND deleted_at IS NULL");
            if ($detailsResult) {
                $detailCount = $detailsResult->num_rows;
                echo "✅ Purchase has {$detailCount} detail(s) in database\n";

                if ($detailCount == count($existingProducts)) {
                    echo "✅ All expected details created correctly\n";
                } else {
                    echo "⚠️  Expected " . count($existingProducts) . " details, found {$detailCount}\n";
                }
            } else {
                echo "❌ Could not verify purchase details in database\n";
            }
        } else {
            echo "❌ create() failed with valid data\n";
            if (!empty($purchase->errors)) {
                echo "   Errors: " . implode(', ', $purchase->errors) . "\n";
            }
        }

        $conn->close();
        echo "\n";
    }

    public function testListPurchases()
    {
        echo "📋 Testing getAll()...\n";

        $purchase = new Purchase();
        $purchases = $purchase->getAll();

        if (is_array($purchases)) {
            echo "✅ getAll() returns array\n";
            if (count($purchases) > 0) {
                $firstPurchase = $purchases[0];
                if (isset($firstPurchase['id_purchase']) && isset($firstPurchase['purchase_date']) && isset($firstPurchase['user_name'])) {
                    echo "✅ getAll() returns purchases with correct structure (id_purchase, purchase_date, user_name)\n";
                } else {
                    echo "❌ getAll() missing required fields (id_purchase, purchase_date, user_name)\n";
                }

                // Verify items_count field
                if (isset($firstPurchase['items_count'])) {
                    echo "✅ getAll() includes items_count field\n";
                } else {
                    echo "❌ getAll() should include items_count field\n";
                }

                // Check if the test purchase appears in the list
                if ($this->testPurchaseId) {
                    $found = false;
                    foreach ($purchases as $p) {
                        if ($p['id_purchase'] == $this->testPurchaseId) {
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        echo "✅ Test purchase found in purchase list\n";
                    } else {
                        echo "❌ Test purchase should appear in purchase list\n";
                    }
                }
            } else {
                echo "⚠️  No purchases found in database\n";
            }
        } else {
            echo "❌ getAll() does not return array\n";
        }

        echo "\n";
    }

    public function testDeletePurchase()
    {
        echo "🗑️  Testing delete() (soft delete with cascade)...\n";

        if (!$this->testPurchaseId) {
            echo "⚠️  No test purchase ID available for delete test\n\n";
            return;
        }

        $purchase = new Purchase();

        // Get the details count before delete
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $beforeDetails = $conn->query("SELECT COUNT(*) AS total FROM purchase_details WHERE id_purchase = {$this->testPurchaseId} AND deleted_at IS NULL")->fetch_assoc();
        $detailsCount = $beforeDetails ? $beforeDetails['total'] : 0;
        echo "   Purchase has {$detailsCount} active detail(s) before delete\n";

        // Call delete and verify return true
        $deleteResult = $purchase->delete($this->testPurchaseId);
        if ($deleteResult) {
            echo "✅ delete() returned true\n";
        } else {
            echo "❌ delete() should return true\n";
            if (!empty($purchase->errors)) {
                echo "   Errors: " . implode(', ', $purchase->errors) . "\n";
            }
        }

        // Verify purchase soft delete
        $purchaseRow = $conn->query("SELECT deleted_at FROM purchases WHERE id_purchase = {$this->testPurchaseId}")->fetch_assoc();
        if ($purchaseRow && $purchaseRow['deleted_at'] != null) {
            echo "✅ Purchase record has deleted_at set (soft delete)\n";
        } else {
            echo "❌ Purchase record should have deleted_at set after soft delete\n";
        }

        // Verify cascade soft delete on details
        $detailsAfterDelete = $conn->query("SELECT COUNT(*) AS total FROM purchase_details WHERE id_purchase = {$this->testPurchaseId} AND deleted_at IS NULL")->fetch_assoc();
        $activeDetailsAfter = $detailsAfterDelete ? $detailsAfterDelete['total'] : 0;
        if ($activeDetailsAfter == 0) {
            echo "✅ All purchase details cascaded soft delete (0 active details remaining)\n";
        } else {
            echo "❌ Purchase details should also have deleted_at set\n";
            echo "   Still {$activeDetailsAfter} active detail(s) remaining\n";
        }

        $conn->close();
        echo "\n";
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new PurchaseTest();
    $test->runAllTests();
}
?>
