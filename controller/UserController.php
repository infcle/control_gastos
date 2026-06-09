<?php

class UserController extends BaseController
{
    private function getModel(): User
    {
        return new User();
    }

    public function listAction(): void
    {
        $this->requireRole('Administrator');

        $user = $this->getModel();
        $users = $user->getAllUsers();

        $this->render('user/content-list', [
            'pageTitle' => 'Lista de usuarios',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Usuarios', 'url' => ''],
            ],
            'users' => $users,
            'user' => $user,
        ]);
    }

    public function createAction(): void
    {
        $this->requireRole('Administrator');

        $user = $this->getModel();

        if ($this->isPost()) {
            $username = $this->getPost('username');
            $email = $this->getPost('email');
            $password = $this->getPost('password');
            $id_rol = $this->getPost('id_rol');

            if ($user->createUser($username, $email, $password, $id_rol)) {
                $this->redirect('user?success=created');
            }
        }

        $this->render('user/content-form', [
            'pageTitle' => 'Nuevo Usuario',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Usuarios', 'url' => BASE_URL . 'user'],
                ['name' => 'Crear', 'url' => ''],
            ],
            'user' => $user,
        ]);
    }

    public function editAction(): void
    {
        $this->requireRole('Administrator');

        $id_user = (int) $this->getParam('id', 0);
        $user = $this->getModel();

        if ($this->isPost()) {
            $username = $this->getPost('username');
            $email = $this->getPost('email');
            $id_rol = $this->getPost('id_rol');
            $status = $this->getPost('status');

            if ($user->updateUser($id_user, $username, $email, $id_rol, $status)) {
                $this->redirect('user?success=updated');
            }
        }

        $userData = $user->getUserById($id_user);
        $roles = $user->getAllRoles();

        $this->render('user/content-form', [
            'pageTitle' => 'Editar Usuario',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Usuarios', 'url' => BASE_URL . 'user'],
                ['name' => 'Editar', 'url' => ''],
            ],
            'userData' => $userData,
            'roles' => $roles,
            'user' => $user,
        ]);
    }

    public function deleteAction(): void
    {
        $this->requireRole('Administrator');

        $id_user = (int) $this->getParam('id', 0);
        $user = $this->getModel();

        if ($user->deleteUser($id_user)) {
            $this->redirect('user?success=deleted');
        } else {
            $this->redirect('user?error=delete_failed');
        }
    }

    public function toggleStatusAction(): void
    {
        $this->requireRole('Administrator');

        $id_user = (int) $this->getParam('id', 0);
        $user = $this->getModel();

        if ($user->toggleUserStatus($id_user)) {
            $this->redirect('user?success=status_toggled');
        } else {
            $this->redirect('user?error=status_failed');
        }
    }

    public function changePasswordAction(): void
    {
        $this->requireAuthWithAction('change_password', 'Administrator');

        $id_user = (int) $this->getParam('id', 0);
        $user = $this->getModel();

        if ($this->isPost()) {
            $new_password = $this->getPost('new_password');

            if ($user->updatePassword($id_user, $new_password)) {
                $this->redirect('user?success=password_changed');
            }
        }

        $userData = $user->getUserById($id_user);

        $this->render('user/content-change-password', [
            'pageTitle' => 'Cambiar Contraseña',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Usuarios', 'url' => BASE_URL . 'user'],
                ['name' => 'Cambiar Contraseña', 'url' => ''],
            ],
            'userData' => $userData,
            'user' => $user,
        ]);
    }

    public function profileAction(): void
    {
        $this->requireAuth();

        $authUser = $this->getAuthUser();

        $this->render('user/content-profile', [
            'pageTitle' => 'Mi Perfil',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Perfil', 'url' => ''],
            ],
            'userData' => $authUser,
        ]);
    }
}
