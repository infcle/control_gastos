<?php
/**
 * Auth Helper — Punto único de autenticación y control de acceso
 *
 * Uso:
 *   require_once CONFIG_PATH . 'auth_helper.php';
 *   requireAuth();                    // Solo login requerido
 *   requireRole('Administrator');     // Login + rol específico
 *
 * Las acciones de perfil están excluidas automáticamente del check de rol
 * cuando se usa requireRoleFromAction().
 */

/**
 * Verifica que el usuario esté autenticado.
 * Redirige al login si no lo está.
 */
function requireAuth()
{
    if (!isset($_SESSION['user_login_status']) || $_SESSION['user_login_status'] != 1) {
        header("location: " . BASE_URL . "controller/login/");
        exit();
    }
}

/**
 * Verifica que el usuario tenga el rol requerido.
 * Redirige al inicio si no lo tiene.
 *
 * @param string|array $roles Rol o array de roles permitidos
 */
function requireRole($roles)
{
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $roles)) {
        header("location: " . BASE_URL);
        exit();
    }
}

/**
 * Versión combinada: requiere login + rol, pero excluye acciones públicas
 * como 'profile', 'change_password', 'upload_profile_picture'.
 *
 * @param string $currentAction La acción actual (ej: $_GET['action'] ?? 'list')
 * @param string|array $requiredRole Rol o roles requeridos para acciones administrativas
 * @param array $publicActions Acciones que no requieren rol (default: profile, etc.)
 */
function requireAuthWithAction($currentAction, $requiredRole = 'Administrator', $publicActions = [])
{
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $defaultPublic = ['profile', 'change_password', 'upload_profile_picture'];
    $publicActions = array_merge($defaultPublic, $publicActions);

    requireAuth();

    if (!in_array($currentAction, $publicActions)) {
        requireRole($requiredRole);
    }
}
