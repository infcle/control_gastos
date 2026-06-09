<?php

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        // Extraer variables para la vista (compatibilidad con vistas existentes)
        extract($data);

        // Las vistas esperan $pageTitle, $breadcrumb, $content como variables sueltas
        $content = VIEW_PATH . $view . '.php';

        require VIEW_PATH . 'template/layout.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit();
    }

    protected function redirectRaw(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }

    protected function requireAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_login_status']) || $_SESSION['user_login_status'] != 1) {
            $this->redirect('login');
        }
    }

    protected function requireRole(string $role): void
    {
        $this->requireAuth();

        if ($_SESSION['rol'] !== $role) {
            $this->redirect('');
        }
    }

    protected function requireAuthWithAction(?string $action = null, ?string $role = null): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_login_status']) || $_SESSION['user_login_status'] != 1) {
            $this->redirect('login');
        }

        // Si la acción es 'profile' o 'change_password', permitir acceso sin rol
        $publicActions = ['profile', 'change_password'];
        if ($action !== null && in_array($action, $publicActions)) {
            return;
        }

        if ($role !== null && $_SESSION['rol'] !== $role) {
            $this->redirect('');
        }
    }

    protected function getAuthUser(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            return [
                'id'              => $_SESSION['user_id'],
                'username'        => $_SESSION['user_name'] ?? '',
                'email'           => $_SESSION['user_email'] ?? '',
                'rol'             => $_SESSION['rol'] ?? '',
                'profile_picture' => $_SESSION['profile_picture'] ?? '',
            ];
        }

        return null;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function getParam(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    protected function getPost(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
}
