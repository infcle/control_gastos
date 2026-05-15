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
         $this->testGetAllPrices();
         $this->testCreateProduct();
         $this->testUpdateProduct();
         $this->testToggleStatus();
         $this->testDeleteProduct();
         $this->testGetProductPriceHistory();

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

public function testGetAllPrices()
     {
         echo "💲 Testing getAllPrices()...\n";

         $product = new Product();
         $prices = $product->getAllPrices();

         if (is_array($prices)) {
             echo "✅ getAllPrices() returns array\n";
             if (count($prices) > 0) {
                 $firstPrice = $prices[0];
                 if (isset($firstPrice['id_price']) && isset($firstPrice['price'])) {
                     echo "✅ getAllPrices() returns prices with correct structure\n";
                 } else {
                     echo "❌ getAllPrices() missing required fields (id_price, price)\n";
                 }
             } else {
                 echo "⚠️  No prices found in database\n";
             }
         } else {
             echo "❌ getAllPrices() does not return array\n";
         }

         echo "\n";
     }

     public function testCreateProduct()
     {
         echo "📦 Testing createProduct()...\n";

         $product = new Product();

         // First, get or create a price ID for testing
         $prices = $product->getAllPrices();
         $price_id = !empty($prices) ? $prices[0]['id_price'] : null;

         // If no prices exist, create one directly in DB
         if (!$price_id) {
             $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
             $conn->query("INSERT INTO prices (price, created_at) VALUES (99.99, NOW())");
             $price_id = $conn->insert_id;
             $conn->close();
         }

         // Test valid product creation
         $result = $product->createProduct('test_producto_' . time(), 'desc', $price_id);

         if ($result) {
             echo "✅ createProduct() with valid data successful\n";

             // Get the created product ID by querying the DB directly
             $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
             $row = $conn->query("SELECT id_product FROM products WHERE name LIKE 'test_producto_%' ORDER BY id_product DESC LIMIT 1")->fetch_assoc();
             $this->testProductId = $row ? $row['id_product'] : null;

             if ($this->testProductId) {
                 echo "✅ Test product created with ID: {$this->testProductId}\n";
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
         $emptyNameResult = $product->createProduct('', null, $price_id);
         if (!$emptyNameResult && !empty($product->errors)) {
             echo "✅ createProduct() correctly rejects empty name and sets \$errors\n";
         } else {
             echo "❌ createProduct() should reject empty name and set \$errors\n";
         }

         // Test empty price_id
         $product->errors = array();
         $emptyPriceResult = $product->createProduct('test_prod', null, '');
         if (!$emptyPriceResult && !empty($product->errors)) {
             echo "✅ createProduct() correctly rejects empty price_id and sets \$errors\n";
         } else {
             echo "❌ createProduct() should reject empty price_id and set \$errors\n";
         }

         echo "\n";
     }

     public function testUpdateProduct()
     {
         echo "📝 Testing updateProduct()...\n";

         $product = new Product();

         // Create a test product first
         $prices = $product->getAllPrices();
         $price_id = !empty($prices) ? $prices[0]['id_price'] : null;

         if (!$price_id) {
             $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
             $conn->query("INSERT INTO prices (price, created_at) VALUES (88.88, NOW())");
             $price_id = $conn->insert_id;
             $conn->close();
         }

         $createResult = $product->createProduct('test_update_' . time(), 'original desc', $price_id);
         if (!$createResult) {
             echo "❌ Could not create product for update test\n\n";
             return;
         }

         // Get the created product ID
         $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
         $row = $conn->query("SELECT id_product FROM products WHERE name LIKE 'test_update_%' ORDER BY id_product DESC LIMIT 1")->fetch_assoc();
         $tempProductId = $row ? $row['id_product'] : null;
         $conn->close();

         if (!$tempProductId) {
             echo "❌ Could not retrieve created product ID\n\n";
             return;
         }

         // Test update
         $updateResult = $product->updateProduct($tempProductId, 'updated_name', 'updated desc', $price_id, 1);
         if ($updateResult) {
             echo "✅ updateProduct() returned true\n";

             // Verify update
             $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
             $row = $conn->query("SELECT name, description FROM products WHERE id_product = $tempProductId")->fetch_assoc();
             $conn->close();

             if ($row && $row['name'] == 'updated_name' && $row['description'] == 'updated desc') {
                 echo "✅ Product data updated correctly in database\n";
             } else {
                 echo "❌ Product data not updated correctly\n";
             }
         } else {
             echo "❌ updateProduct() should return true\n";
         }

         echo "\n";
     }

     public function testToggleStatus()
     {
         echo "🔄 Testing toggleProductStatus()...\n";

         $product = new Product();

         // Create a test product
         $prices = $product->getAllPrices();
         $price_id = !empty($prices) ? $prices[0]['id_price'] : null;

         if (!$price_id) {
             $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
             $conn->query("INSERT INTO prices (price, created_at) VALUES (77.77, NOW())");
             $price_id = $conn->insert_id;
             $conn->close();
         }

         $createResult = $product->createProduct('test_toggle_' . time(), 'desc', $price_id);

         $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
         $row = $conn->query("SELECT id_product, status FROM products WHERE name LIKE 'test_toggle_%' ORDER BY id_product DESC LIMIT 1")->fetch_assoc();
         $tempProductId = $row ? $row['id_product'] : null;
         $initialStatus = $row ? $row['status'] : null;
         $conn->close();

         if (!$tempProductId) {
             echo "❌ Could not create product for toggle test\n\n";
             return;
         }

         // Toggle status
         $toggleResult = $product->toggleProductStatus($tempProductId);
         if ($toggleResult) {
             echo "✅ toggleProductStatus() returned true\n";

             $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
             $row = $conn->query("SELECT status FROM products WHERE id_product = $tempProductId")->fetch_assoc();
             $conn->close();

             if ($row && $row['status'] != $initialStatus) {
                 echo "✅ Product status toggled correctly\n";
             } else {
                 echo "❌ Product status not toggled correctly\n";
             }
         } else {
             echo "❌ toggleProductStatus() should return true\n";
         }

         echo "\n";
     }

     public function testDeleteProduct()
     {
         echo "🗑️  Testing deleteProduct() (soft delete)...\n";

         // Create a test product for deletion
         $product = new Product();
         $prices = $product->getAllPrices();
         $price_id = !empty($prices) ? $prices[0]['id_price'] : null;

         if (!$price_id) {
             $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
             $conn->query("INSERT INTO prices (price, created_at) VALUES (66.66, NOW())");
             $price_id = $conn->insert_id;
             $conn->close();
         }

         $createResult = $product->createProduct('test_softdelete_' . time(), 'desc for delete', $price_id);

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

         // Query DB directly and verify deleted_at is set
         $statusRow = $conn->query("SELECT deleted_at FROM products WHERE id_product = $tempProductId")->fetch_assoc();
         if ($statusRow && $statusRow['deleted_at'] != null) {
             echo "✅ Product record has deleted_at set (soft delete)\n";
         } else {
             echo "❌ Product record should have deleted_at set after soft delete\n";
         }

         $conn->close();
         echo "\n";
     }

     public function testGetProductPriceHistory()
     {
         echo "📈 Testing getProductPriceHistory()...\n";

         $product = new Product();

         // Create a test product
         $prices = $product->getAllPrices();
         $price_id = !empty($prices) ? $prices[0]['id_price'] : null;

         if (!$price_id) {
             $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
             $conn->query("INSERT INTO prices (price, created_at) VALUES (55.55, NOW())");
             $price_id = $conn->insert_id;
             $conn->close();
         }

         $createResult = $product->createProduct('test_pricehistory_' . time(), 'desc', $price_id);

         $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
         $row = $conn->query("SELECT id_product FROM products WHERE name LIKE 'test_pricehistory_%' ORDER BY id_product DESC LIMIT 1")->fetch_assoc();
         $tempProductId = $row ? $row['id_product'] : null;
         $conn->close();

         if (!$tempProductId) {
             echo "❌ Could not create product for price history test\n\n";
             return;
         }

         // Verify getProductPriceHistory returns array
         $history = $product->getProductPriceHistory($tempProductId);

         if (is_array($history)) {
             echo "✅ getProductPriceHistory() returns array\n";
         } else {
             echo "❌ getProductPriceHistory() should return array\n";
         }

         // Verify getProductPriceHistory returns empty array for non-existent product
         $emptyHistory = $product->getProductPriceHistory(99999);
         if (is_array($emptyHistory) && count($emptyHistory) == 0) {
             echo "✅ getProductPriceHistory(99999) returns empty array\n";
         } else {
             echo "❌ getProductPriceHistory(99999) should return empty array\n";
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
