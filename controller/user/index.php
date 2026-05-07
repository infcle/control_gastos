<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_login_status']) || $_SESSION['user_login_status'] != 1) {
    header("location: " . BASE_URL . "controller/login/");
    exit();
}

// Verificar si el usuario tiene permisos (solo admin puede gestionar usuarios)
if ($_SESSION['rol'] != 'Administrator') {
    header("location: " . BASE_URL);
    exit();
}

require_once __DIR__ . '/../../config/app_config.php';
require_once MODEL_PATH . 'user/User.php';

$user = new User();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Set page title and breadcrumb
$pageTitle = 'Users Management';
$breadcrumb = [
    ['name' => 'Dashboard', 'url' => BASE_URL],
    ['name' => 'Users', 'url' => CONTROLLER_URL . 'user/']
];

switch ($action) {
    case 'list':
        $users = $user->getAllUsers();
        $pageTitle = 'Users List';
        $breadcrumb[] = ['name' => 'List', 'url' => ''];
        $content = VIEW_PATH . 'user/content-list.php';
        break;
        
    case 'create':
        $pageTitle = 'Create User';
        $breadcrumb[] = ['name' => 'Create', 'url' => ''];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $id_rol = $_POST['id_rol'];
            
            if ($user->createUser($username, $email, $password, $id_rol)) {
                header("location: " . CONTROLLER_URL . "user/?success=created");
                exit();
            }
        }
        
        $roles = $user->getAllRoles();
        $content = VIEW_PATH . 'user/content-form.php';
        break;
        
    case 'edit':
        $id_user = isset($_GET['id']) ? $_GET['id'] : 0;
        $pageTitle = 'Edit User';
        $breadcrumb[] = ['name' => 'Edit', 'url' => ''];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $id_rol = $_POST['id_rol'];
            $status = $_POST['status'];
            
            if ($user->updateUser($id_user, $username, $email, $id_rol, $status)) {
                header("location: " . CONTROLLER_URL . "user/?success=updated");
                exit();
            }
        }
        
        $userData = $user->getUserById($id_user);
        $roles = $user->getAllRoles();
        $content = VIEW_PATH . 'user/content-form.php';
        break;
        
    case 'delete':
        $id_user = isset($_GET['id']) ? $_GET['id'] : 0;
        
        if ($user->deleteUser($id_user)) {
            header("location: " . CONTROLLER_URL . "user/?success=deleted");
        } else {
            header("location: " . CONTROLLER_URL . "user/?error=delete_failed");
        }
        exit();
        
    case 'toggle_status':
        $id_user = isset($_GET['id']) ? $_GET['id'] : 0;
        
        if ($user->toggleUserStatus($id_user)) {
            header("location: " . CONTROLLER_URL . "user/?success=status_toggled");
        } else {
            header("location: " . CONTROLLER_URL . "user/?error=status_failed");
        }
        exit();
        
    case 'change_password':
        $id_user = isset($_GET['id']) ? $_GET['id'] : 0;
        $pageTitle = 'Change Password';
        $breadcrumb[] = ['name' => 'Change Password', 'url' => ''];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = $_POST['new_password'];
            
            if ($user->updatePassword($id_user, $new_password)) {
                header("location: " . CONTROLLER_URL . "user/?success=password_changed");
                exit();
            }
        }
        
        $userData = $user->getUserById($id_user);
        $content = VIEW_PATH . 'user/content-change-password.php';
        break;
        
    default:
        header("location: " . CONTROLLER_URL . "user/");
        exit();
}

// Include the template layout
require_once VIEW_PATH . 'template/layout.php';
?>
