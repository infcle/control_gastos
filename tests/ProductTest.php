<?php
require_once __DIR__ . '/../model/product/Product.php';
require_once __DIR__ . '/../config/database.php';

class ProductTest
{
    private $product;
    private $testProductId;
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
            // First delete prices for test products (FK constraint)
            $conn->query("DELETE FROM prices WHERE id_product IN (SELECT id_product FROM products WHERE name LIKE 'test_%')");
            // Then delete the test products
            $conn->query("DELETE FROM products WHERE name LIKE 'test_%'");
            $conn->close();
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }

    public function runAllTests()
    {
        echo "🧪 Running Product Model Tests...\n\n";

        $this->testGetAllProducts();
        $this->testCreateProduct();
        $this->testSoftDelete();
        $this->testUpdatePrice();
        $this->testGetPriceHistory();

        echo "\n✅ Product Model Tests Completed!\n";
    }

    public function testGetAllProducts()
    {
        echo "📋 Testing getAllProducts()...\n";

        $product = new Product();
        $products = $product->getAllProducts();

        if (is_array($products)) {
            echo "✅ getAllProducts() returns array\n";
            if (count($products) > 0) {
                $firstProduct = $products[0];
                if (isset($firstProduct['id_product']) && isset($firstProduct['name']) && isset($firstProduct['price'])) {
                    echo "✅ getAllProducts() returns products with correct structure\n";
                } else {
                    echo "❌ getAllProducts() missing required fields (id_product, name, price)\n";
                }
            } else {
                echo "⚠️  No products found in database\n";
            }
        } else {
            echo "❌ getAllProducts() does not return array\n";
        }

        echo "\n";
    }

    public function testCreateProduct()
    {
        echo "📦 Testing createProduct()...\n";

        $product = new Product();

        // Test valid product creation
        $result = $product->createProduct('test_producto', 'desc', 10.50);

        if ($result) {
            echo "✅ createProduct() with valid data successful\n";

            // Get the created product ID by querying the DB directly
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $row = $conn->query("SELECT id_product, price_id FROM products WHERE name = 'test_producto' ORDER BY id_product DESC LIMIT 1")->fetch_assoc();
            $this->testProductId = $row ? $row['id_product'] : null;
            $price_id_in_product = $row ? $row['price_id'] : null;

            if ($this->testProductId) {
                echo "✅ Test product created with ID: {$this->testProductId}\n";

                // Verify a record exists in prices for the created product
                $priceRow = $conn->query("SELECT id_price FROM prices WHERE id_product = {$this->testProductId} LIMIT 1")->fetch_assoc();
                if ($priceRow) {
                    echo "✅ A record exists in prices for the created product\n";

                    // Verify price_id in product points to the inserted id_price
                    if ($price_id_in_product == $priceRow['id_price']) {
                        echo "✅ price_id in product correctly references the inserted id_price\n";
                    } else {
                        echo "❌ price_id in product does not match the inserted id_price\n";
                    }
                } else {
                    echo "❌ No record found in prices for the created product\n";
                }
            }

            $conn->close();
        } else {
            echo "❌ createProduct() failed with valid data\n";
            if (!empty($product->errors)) {
                echo "   Errors: " . implode(', ', $product->errors) . "\n";
            }
        }

        // Test empty name
        $product->errors = array();
        $emptyNameResult = $product->createProduct('', null, 10.50);
        if (!$emptyNameResult && !empty($product->errors)) {
            echo "✅ createProduct() correctly rejects empty name and sets \$errors\n";
        } else {
            echo "❌ createProduct() should reject empty name and set \$errors\n";
        }

        // Test price = 0
        $product->errors = array();
        $zeroPriceResult = $product->createProduct('test_prod', null, 0);
        if (!$zeroPriceResult) {
            echo "✅ createProduct() correctly rejects price = 0\n";
        } else {
            echo "❌ createProduct() should reject price = 0\n";
        }

        // Test negative price
        $product->errors = array();
        $negativePriceResult = $product->createProduct('test_prod', null, -5);
        if (!$negativePriceResult) {
            echo "✅ createProduct() correctly rejects negative price\n";
        } else {
            echo "❌ createProduct() should reject negative price\n";
        }

        echo "\n";
    }

    public function testSoftDelete()
    {
        echo "🗑️  Testing deleteProduct() (soft delete)...\n";

        // Create a test product for deletion
        $product = new Product();
        $createResult = $product->createProduct('test_softdelete_' . time(), 'desc for delete', 5.00);

        if (!$createResult) {
            echo "❌ Could not create product for soft delete test\n\n";
            return;
        }

        // Get the created product ID
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $row = $conn->query("SELECT id_product FROM products WHERE name LIKE 'test_softdelete_%' ORDER BY id_product DESC LIMIT 1")->fetch_assoc();
        $tempProductId = $row ? $row['id_product'] : null;

        if (!$tempProductId) {
            echo "❌ Could not retrieve created product ID for soft delete test\n";
            $conn->close();
            echo "\n";
            return;
        }

        // Call deleteProduct and verify return true
        $deleteResult = $product->deleteProduct($tempProductId);
        if ($deleteResult) {
            echo "✅ deleteProduct() returned true\n";
        } else {
            echo "❌ deleteProduct() should return true\n";
        }

        // Query DB directly and verify record exists with status = 0
        $statusRow = $conn->query("SELECT status FROM products WHERE id_product = $tempProductId")->fetch_assoc();
        if ($statusRow && $statusRow['status'] == 0) {
            echo "✅ Product record still exists in DB with status = 0\n";
        } else {
            echo "❌ Product record should exist with status = 0 after soft delete\n";
        }

        // Verify prices records still exist (count > 0)
        $pricesResult = $conn->query("SELECT COUNT(*) as cnt FROM prices WHERE id_product = $tempProductId");
        $pricesRow = $pricesResult->fetch_assoc();
        if ($pricesRow && $pricesRow['cnt'] > 0) {
            echo "✅ Prices records still exist after soft delete (count = {$pricesRow['cnt']})\n";
        } else {
            echo "❌ Prices records should still exist after soft delete\n";
        }

        $conn->close();
        echo "\n";
    }

    public function testUpdatePrice()
    {
        echo "💰 Testing updatePrice()...\n";

        // Create a test product with initial price
        $product = new Product();
        $createResult = $product->createProduct('test_updateprice_' . time(), 'desc for price update', 15.00);

        if (!$createResult) {
            echo "❌ Could not create product for updatePrice test\n\n";
            return;
        }

        // Get the created product ID
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $row = $conn->query("SELECT id_product FROM products WHERE name LIKE 'test_updateprice_%' ORDER BY id_product DESC LIMIT 1")->fetch_assoc();
        $tempProductId = $row ? $row['id_product'] : null;

        if (!$tempProductId) {
            echo "❌ Could not retrieve created product ID for updatePrice test\n";
            $conn->close();
            echo "\n";
            return;
        }

        // Call updatePrice with a new price
        $updateResult = $product->updatePrice($tempProductId, 25.00);
        if ($updateResult) {
            echo "✅ updatePrice() returned true\n";
        } else {
            echo "❌ updatePrice() should return true\n";
            if (!empty($product->errors)) {
                echo "   Errors: " . implode(', ', $product->errors) . "\n";
            }
        }

        // Verify a new record was inserted in prices (count = 2: initial + update)
        $pricesResult = $conn->query("SELECT COUNT(*) as cnt FROM prices WHERE id_product = $tempProductId");
        $pricesRow = $pricesResult->fetch_assoc();
        if ($pricesRow && $pricesRow['cnt'] == 2) {
            echo "✅ A new record was inserted in prices (count = 2)\n";
        } else {
            $cnt = $pricesRow ? $pricesRow['cnt'] : 0;
            echo "❌ Expected 2 price records, found $cnt\n";
        }

        // Verify price_id in product was updated to the new id_price
        $productRow = $conn->query("SELECT price_id FROM products WHERE id_product = $tempProductId")->fetch_assoc();
        $latestPriceRow = $conn->query("SELECT id_price FROM prices WHERE id_product = $tempProductId ORDER BY id_price DESC LIMIT 1")->fetch_assoc();

        if ($productRow && $latestPriceRow && $productRow['price_id'] == $latestPriceRow['id_price']) {
            echo "✅ price_id in product was updated to the new id_price\n";
        } else {
            echo "❌ price_id in product should reference the latest id_price\n";
        }

        $conn->close();
        echo "\n";
    }

    public function testGetPriceHistory()
    {
        echo "📈 Testing getPriceHistory()...\n";

        // Create a test product and insert 2 additional prices
        $product = new Product();
        $createResult = $product->createProduct('test_pricehistory_' . time(), 'desc for history', 10.00);

        if (!$createResult) {
            echo "❌ Could not create product for getPriceHistory test\n\n";
            return;
        }

        // Get the created product ID
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $row = $conn->query("SELECT id_product FROM products WHERE name LIKE 'test_pricehistory_%' ORDER BY id_product DESC LIMIT 1")->fetch_assoc();
        $tempProductId = $row ? $row['id_product'] : null;
        $conn->close();

        if (!$tempProductId) {
            echo "❌ Could not retrieve created product ID for getPriceHistory test\n\n";
            return;
        }

        // Insert 2 additional prices via updatePrice
        $product->updatePrice($tempProductId, 20.00);
        $product->updatePrice($tempProductId, 30.00);

        // Verify getPriceHistory returns 3 records (1 initial + 2 updates)
        $history = $product->getPriceHistory($tempProductId);

        if (count($history) == 3) {
            echo "✅ getPriceHistory() returns 3 records (1 initial + 2 updates)\n";
        } else {
            echo "❌ getPriceHistory() should return 3 records, got " . count($history) . "\n";
        }

        // Verify the first element has the most recent price (descending order)
        if (count($history) >= 1 && (float)$history[0]['price'] == 30.00) {
            echo "✅ getPriceHistory() first element has the most recent price (30.00)\n";
        } else {
            $firstPrice = count($history) >= 1 ? $history[0]['price'] : 'N/A';
            echo "❌ getPriceHistory() first element should be the most recent price (30.00), got $firstPrice\n";
        }

        // Verify getPriceHistory returns empty array for non-existent product
        $emptyHistory = $product->getPriceHistory(99999);
        if (is_array($emptyHistory) && count($emptyHistory) == 0) {
            echo "✅ getPriceHistory(99999) returns empty array\n";
        } else {
            echo "❌ getPriceHistory(99999) should return empty array\n";
        }

        echo "\n";
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new ProductTest();
    $test->runAllTests();
}
?>
