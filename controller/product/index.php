<?php
require_once __DIR__ . '/../../config/app_config.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_login_status']) || $_SESSION['user_login_status'] != 1) {
    header("location: " . BASE_URL . "controller/login/");
    exit();
}

// Verificar si el usuario tiene permisos (solo admin puede gestionar productos)
if ($_SESSION['rol'] != 'Administrator') {
    header("location: " . BASE_URL);
    exit();
}

require_once MODEL_PATH . 'product/Product.php';

$product = new Product();
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
        $pageTitle = 'Gestión de Productos';
        $breadcrumb[] = ['name' => 'List', 'url' => ''];
        $content = VIEW_PATH . 'product/content-list.php';
        break;

    case 'create':
        $pageTitle = 'Nuevo Producto';
        $breadcrumb[] = ['name' => 'Crear', 'url' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name        = $_POST['name'];
            $description = $_POST['description'];
            $price       = $_POST['price'];

            if ($product->createProduct($name, $description, $price)) {
                header("location: " . CONTROLLER_URL . "product/?success=created");
                exit();
            }
        }

        $content = VIEW_PATH . 'product/content-form.php';
        break;

    case 'edit':
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $pageTitle = 'Editar Producto';
        $breadcrumb[] = ['name' => 'Editar', 'url' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id          = $_POST['id'];
            $name        = $_POST['name'];
            $description = $_POST['description'];

            if ($product->updateProduct($id, $name, $description)) {
                header("location: " . CONTROLLER_URL . "product/?success=updated");
                exit();
            }
        }

        $productData = $product->getProductById($id);
        $content = VIEW_PATH . 'product/content-form.php';
        break;

    case 'update_price':
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $pageTitle = 'Actualizar Precio';
        $breadcrumb[] = ['name' => 'Precio', 'url' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id    = $_POST['id'];
            $price = $_POST['price'];

            if ($product->updatePrice($id, $price)) {
                header("location: " . CONTROLLER_URL . "product/?success=price_updated");
                exit();
            }

            $productData  = $product->getProductById($id);
            $priceHistory = $product->getPriceHistory($id);
        } else {
            $productData  = $product->getProductById($id);
            $priceHistory = $product->getPriceHistory($id);
        }

        $content = VIEW_PATH . 'product/content-price.php';
        break;

    case 'delete':
        $id = isset($_GET['id']) ? $_GET['id'] : 0;

        if ($product->deleteProduct($id)) {
            header("location: " . CONTROLLER_URL . "product/?success=deleted");
        } else {
            header("location: " . CONTROLLER_URL . "product/?error=delete_failed");
        }
        exit();

    default:
        header("location: " . CONTROLLER_URL . "product/");
        exit();
}

// Include the template layout
require_once VIEW_PATH . 'template/layout.php';
?>
