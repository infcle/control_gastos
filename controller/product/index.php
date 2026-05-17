<?php
require_once __DIR__ . '/../../config/app_config.php';
require_once __DIR__ . '/../../model/login/Login.php';
require_once __DIR__ . '/../../model/product/Product.php';

// Check authentication
$login = new Login();
if (!$login->isConected()) {
    header("location: " . CONTROLLER_URL . "login");
    exit();
}

// Initialize Product model
$product = new Product();

// Get action from URL
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Set page title and breadcrumb
$pageTitle = 'Products Management';
$breadcrumb = [
    ['name' => 'Dashboard', 'url' => BASE_URL],
    ['name' => 'Products', 'url' => CONTROLLER_URL . 'product/']
];

switch ($action) {
    case 'list':
        $products = $product->getAllProducts();
        $pageTitle = 'Products List';
        $breadcrumb[] = ['name' => 'List', 'url' => ''];
        $content = VIEW_PATH . 'product/content-list.php';
        break;
        
    case 'create':
        $pageTitle = 'Create Product';
        $breadcrumb[] = ['name' => 'Create', 'url' => ''];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price_id = $_POST['price_id'];
            $id_category = isset($_POST['id_category']) && $_POST['id_category'] !== '' ? $_POST['id_category'] : null;
            
            if ($product->createProduct($name, $description, $price_id, $id_category)) {
                header("location: " . CONTROLLER_URL . "product/?success=created");
                exit();
            }
        }
        
        $prices = $product->getAllPrices();
        $categories = $product->getAllCategories();
        $content = VIEW_PATH . 'product/content-form.php';
        break;
        
    case 'edit':
        $id_product = isset($_GET['id']) ? $_GET['id'] : 0;
        $productData = $product->getProductById($id_product);
        
        if (!$productData) {
            header("location: " . CONTROLLER_URL . "product/?error=not_found");
            exit();
        }
        
        $pageTitle = 'Edit Product';
        $breadcrumb[] = ['name' => 'Edit', 'url' => ''];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price_id = $_POST['price_id'];
            $status = isset($_POST['status']) ? $_POST['status'] : 1;
            $id_category = isset($_POST['id_category']) && $_POST['id_category'] !== '' ? $_POST['id_category'] : null;
            
            if ($product->updateProduct($id_product, $name, $description, $price_id, $status, $id_category)) {
                header("location: " . CONTROLLER_URL . "product/?success=updated");
                exit();
            }
        }
        
        $prices = $product->getAllPrices();
        $categories = $product->getAllCategories();
        $content = VIEW_PATH . 'product/content-form.php';
        break;
        
    case 'delete':
        $id_product = isset($_GET['id']) ? $_GET['id'] : 0;
        
        if ($product->deleteProduct($id_product)) {
            header("location: " . CONTROLLER_URL . "product/?success=deleted");
        } else {
            header("location: " . CONTROLLER_URL . "product/?error=delete_failed");
        }
        exit();
        
    case 'toggle_status':
        $id_product = isset($_GET['id']) ? $_GET['id'] : 0;
        
        if ($product->toggleProductStatus($id_product)) {
            header("location: " . CONTROLLER_URL . "product/?success=status_toggled");
        } else {
            header("location: " . CONTROLLER_URL . "product/?error=status_failed");
        }
        exit();
        
    case 'price_history':
        $id_product = isset($_GET['id']) ? $_GET['id'] : 0;
        $priceHistory = $product->getProductPriceHistory($id_product);
        $productData = $product->getProductById($id_product);
        
        $pageTitle = 'Price History';
        $breadcrumb[] = ['name' => 'Price History', 'url' => ''];
        $content = VIEW_PATH . 'product/content-price-history.php';
        break;
        
    default:
        header("location: " . CONTROLLER_URL . "product/?action=list");
        exit();
}

// Include the main template
require_once VIEW_PATH . 'template/layout.php';
?>
