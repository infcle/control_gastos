<?php
require_once __DIR__ . '/../../config/app_config.php';
require_once CONFIG_PATH . 'auth_helper.php';
requireAuthWithAction(isset($_GET['action']) ? $_GET['action'] : 'list', 'Administrator');

require_once MODEL_PATH . 'supplier/Supplier.php';

$supplier = new Supplier();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Set page title and breadcrumb
$pageTitle = 'Gestión de Proveedores';
$breadcrumb = [
    ['name' => 'Dashboard', 'url' => BASE_URL],
    ['name' => 'Proveedores', 'url' => CONTROLLER_URL . 'supplier/']
];

switch ($action) {
    case 'list':
        $suppliers = $supplier->getAll();
        $pageTitle = 'Lista de Proveedores';
        $breadcrumb[] = ['name' => 'Lista', 'url' => ''];
        $content = VIEW_PATH . 'supplier/content-list.php';
        break;

    case 'create':
        $pageTitle = 'Nuevo Proveedor';
        $breadcrumb[] = ['name' => 'Crear', 'url' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $location = $_POST['location'];

            if ($supplier->create($name, $location)) {
                header("location: " . CONTROLLER_URL . "supplier/?success=created");
                exit();
            }
        }

        $content = VIEW_PATH . 'supplier/content-form.php';
        break;

    case 'edit':
        $id_supplier = isset($_GET['id']) ? $_GET['id'] : 0;
        $pageTitle = 'Editar Proveedor';
        $breadcrumb[] = ['name' => 'Editar', 'url' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $location = $_POST['location'];

            if ($supplier->update($id_supplier, $name, $location)) {
                header("location: " . CONTROLLER_URL . "supplier/?success=updated");
                exit();
            }
        }

        $supplierData = $supplier->getById($id_supplier);

        if (!$supplierData) {
            header("location: " . CONTROLLER_URL . "supplier/?error=not_found");
            exit();
        }

        $content = VIEW_PATH . 'supplier/content-form.php';
        break;

    case 'delete':
        $id_supplier = isset($_GET['id']) ? $_GET['id'] : 0;

        if ($supplier->delete($id_supplier)) {
            header("location: " . CONTROLLER_URL . "supplier/?success=deleted");
        } else {
            header("location: " . CONTROLLER_URL . "supplier/?error=delete_failed");
        }
        exit();

    default:
        header("location: " . CONTROLLER_URL . "supplier/");
        exit();
}

// Include the template layout
require_once VIEW_PATH . 'template/layout.php';
?>
