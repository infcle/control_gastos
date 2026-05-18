<?php
require_once __DIR__ . '/../../config/app_config.php';
require_once CONFIG_PATH . 'auth_helper.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
requireAuthWithAction($action, 'Administrator');

require_once MODEL_PATH . 'user/User.php';

$user = new User();

// Set page title and breadcrumb
$pageTitle = 'Gestión de Usuarios';
$breadcrumb = [
    ['name' => 'Panel Principal', 'url' => BASE_URL],
    ['name' => 'Usuarios', 'url' => CONTROLLER_URL . 'user/']
];

switch ($action) {
    case 'profile':
        $id_user = $_SESSION['id_user'];
        $pageTitle = 'Mi Perfil';
        $breadcrumb = [
            ['name' => 'Panel Principal', 'url' => BASE_URL],
            ['name' => 'Mi Perfil', 'url' => '']
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
            $username = $_POST['username'];
            $email = $_POST['email'];

            if ($user->updateProfile($id_user, $username, $email)) {
                // Actualizar sesión
                $_SESSION['user_name'] = $username;
                header("location: " . CONTROLLER_URL . "user/?action=profile&success=updated");
                exit();
            }
        }

        $userData = $user->getUserById($id_user);
        $content = VIEW_PATH . 'user/content-profile.php';
        break;

    case 'upload_profile_picture':
        $id_user = $_SESSION['id_user'];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
            $file = $_FILES['profile_picture'];
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $user->errors[] = "Error al subir el archivo.";
            } elseif (!in_array($ext, $allowed)) {
                $user->errors[] = "Solo se permiten imágenes (jpg, jpeg, png, gif, webp).";
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $user->errors[] = "La imagen no debe superar los 2MB.";
            } else {
                // Eliminar foto anterior si existe
                $currentUser = $user->getUserById($id_user);
                if (!empty($currentUser['profile_picture'])) {
                    $oldFile = ASSETS_PATH . 'uploads/profiles/' . $currentUser['profile_picture'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $filename = 'user_' . $id_user . '_' . time() . '.' . $ext;
                $destination = ASSETS_PATH . 'uploads/profiles/' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    if ($user->updateProfilePicture($id_user, $filename)) {
                        $_SESSION['profile_picture'] = $filename;
                        header("location: " . CONTROLLER_URL . "user/?action=profile&success=photo_updated");
                        exit();
                    }
                } else {
                    $user->errors[] = "Error al guardar la imagen.";
                }
            }
            
            // Si hay error, volver al perfil
            $userData = $user->getUserById($id_user);
            $content = VIEW_PATH . 'user/content-profile.php';
        } else {
            header("location: " . CONTROLLER_URL . "user/?action=profile");
            exit();
        }
        break;

    case 'list':
        $users = $user->getAllUsers();
        $pageTitle = 'Lista de Usuarios';
        $breadcrumb[] = ['name' => 'Lista', 'url' => ''];
        $content = VIEW_PATH . 'user/content-list.php';
        break;
        
    case 'create':
        $pageTitle = 'Nuevo Usuario';
        $breadcrumb[] = ['name' => 'Crear', 'url' => ''];
        
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
        $pageTitle = 'Editar Usuario';
        $breadcrumb[] = ['name' => 'Editar', 'url' => ''];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $id_rol = $_POST['id_rol'];
            $status = $_POST['status'];
            
            if ($user->updateUser($id_user, $username, $email, $id_rol, $status)) {
                // Si el admin se editó a sí mismo, actualizar sesión
                if ($id_user == $_SESSION['id_user']) {
                    $_SESSION['user_name'] = $username;
                }
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
        $pageTitle = 'Cambiar Contraseña';
        $breadcrumb[] = ['name' => 'Cambiar Contraseña', 'url' => ''];
        
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
