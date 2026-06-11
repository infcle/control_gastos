<?php
require_once __DIR__ . '/../../config/app_config.php';
require_once CONFIG_PATH . 'auth_helper.php';
requireAuthWithAction(isset($_GET['action']) ? $_GET['action'] : 'list', 'Administrator');

require_once MODEL_PATH . 'category/Category.php';

$category = new Category();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Set page title and breadcrumb
$pageTitle = 'Gestión de Categorías';
$breadcrumb = [
    ['name' => 'Dashboard', 'url' => BASE_URL],
    ['name' => 'Categorías', 'url' => CONTROLLER_URL . 'category/']
];

switch ($action) {
    case 'list':
        $categories = $category->getAll();
        $pageTitle = 'Lista de Categorías';
        $breadcrumb[] = ['name' => 'Lista', 'url' => ''];
        $content = VIEW_PATH . 'category/content-list.php';
        break;

    case 'create':
        $pageTitle = 'Nueva Categoría';
        $breadcrumb[] = ['name' => 'Crear', 'url' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];

            if ($category->create($name, $description)) {
                header("location: " . CONTROLLER_URL . "category/?success=created");
                exit();
            }
        }

        $content = VIEW_PATH . 'category/content-form.php';
        break;

    case 'edit':
        $id_category = isset($_GET['id']) ? $_GET['id'] : 0;
        $pageTitle = 'Editar Categoría';
        $breadcrumb[] = ['name' => 'Editar', 'url' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];

            if ($category->update($id_category, $name, $description)) {
                header("location: " . CONTROLLER_URL . "category/?success=updated");
                exit();
            }
        }

        $categoryData = $category->getById($id_category);

        if (!$categoryData) {
            header("location: " . CONTROLLER_URL . "category/?error=not_found");
            exit();
        }

        $content = VIEW_PATH . 'category/content-form.php';
        break;

    case 'delete':
        $id_category = isset($_GET['id']) ? $_GET['id'] : 0;

        if ($category->delete($id_category)) {
            header("location: " . CONTROLLER_URL . "category/?success=deleted");
        } else {
            header("location: " . CONTROLLER_URL . "category/?error=delete_failed");
        }
        exit();

    default:
        header("location: " . CONTROLLER_URL . "category/");
        exit();
}

// Include the template layout
require_once VIEW_PATH . 'template/layout.php';
?>
