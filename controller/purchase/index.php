<?php
require_once __DIR__ . '/../../config/app_config.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_login_status']) || $_SESSION['user_login_status'] != 1) {
    header("location: " . BASE_URL . "controller/login/");
    exit();
}

// Verificar si el usuario tiene permisos (solo admin)
if ($_SESSION['rol'] != 'Administrator') {
    header("location: " . BASE_URL);
    exit();
}

require_once MODEL_PATH . 'purchase/Purchase.php';
require_once MODEL_PATH . 'product/Product.php';
require_once MODEL_PATH . 'supplier/Supplier.php';

$purchase = new Purchase();
$productModel = new Product();
$supplierModel = new Supplier();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Set page title and breadcrumb
$pageTitle = 'Gestión de Compras';
$breadcrumb = [
    ['name' => 'Dashboard', 'url' => BASE_URL],
    ['name' => 'Compras', 'url' => CONTROLLER_URL . 'purchase/']
];

switch ($action) {
    case 'list':
        $purchases = $purchase->getAll();
        $pageTitle = 'Lista de Compras';
        $breadcrumb[] = ['name' => 'Lista', 'url' => ''];
        $content = VIEW_PATH . 'purchase/content-list.php';
        break;

    case 'create':
        $pageTitle = 'Nueva Compra';
        $breadcrumb[] = ['name' => 'Crear', 'url' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $purchase_date = $_POST['purchase_date'];
            $observation = isset($_POST['observation']) ? $_POST['observation'] : '';
            $id_user = $_SESSION['user_id'];

            // Parsear el formato anidado suppliers[] del POST
            $details = array();

            if (isset($_POST['suppliers']) && is_array($_POST['suppliers'])) {
                foreach ($_POST['suppliers'] as $supplier_group) {
                    $id_supplier = $supplier_group['id_supplier'];

                    if (isset($supplier_group['products']) && is_array($supplier_group['products'])) {
                        foreach ($supplier_group['products'] as $product_item) {
                            $details[] = array(
                                'id_product' => $product_item['id_product'],
                                'id_supplier' => $id_supplier,
                                'quantity' => $product_item['quantity'],
                                'unit_price' => $product_item['unit_price'],
                                'observation' => isset($product_item['observation']) ? $product_item['observation'] : ''
                            );
                        }
                    }
                }
            }

            if ($purchase->create($purchase_date, $id_user, $observation, $details)) {
                header("location: " . CONTROLLER_URL . "purchase/?success=created");
                exit();
            }
        }

        $products = $productModel->getAllProducts();
        $suppliers = $supplierModel->getAll();
        $content = VIEW_PATH . 'purchase/content-form.php';
        break;

    case 'view':
        $id_purchase = isset($_GET['id']) ? $_GET['id'] : 0;
        $purchaseData = $purchase->getById($id_purchase);

        if (!$purchaseData) {
            header("location: " . CONTROLLER_URL . "purchase/?error=not_found");
            exit();
        }

        $pageTitle = 'Detalle de Compra';
        $breadcrumb[] = ['name' => 'Detalle', 'url' => ''];
        $content = VIEW_PATH . 'purchase/content-view.php';
        break;

    case 'delete':
        $id_purchase = isset($_GET['id']) ? $_GET['id'] : 0;

        if ($purchase->delete($id_purchase)) {
            header("location: " . CONTROLLER_URL . "purchase/?success=deleted");
        } else {
            header("location: " . CONTROLLER_URL . "purchase/?error=delete_failed");
        }
        exit();

    default:
        header("location: " . CONTROLLER_URL . "purchase/");
        exit();
}

// Include the template layout
require_once VIEW_PATH . 'template/layout.php';
?>
