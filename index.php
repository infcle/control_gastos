<?php
/**
 * Front Controller — Punto único de entrada
 *
 * Todas las solicitudes pasan por aquí vía .htaccess.
 * Durante la transición soporta URLs antiguas (controller/*/) y nuevas (/*).
 */

require_once __DIR__ . '/config/app_config.php';

// Cargar librerías base
require_once __DIR__ . '/lib/Router.php';
require_once __DIR__ . '/lib/BaseController.php';

// Cargar modelos base
require_once MODEL_PATH . 'login/Login.php';
require_once MODEL_PATH . 'user/User.php';
require_once MODEL_PATH . 'product/Product.php';
require_once MODEL_PATH . 'category/Category.php';
require_once MODEL_PATH . 'supplier/Supplier.php';
require_once MODEL_PATH . 'purchase/Purchase.php';

try {
    // Obtener la URI
    $uri = '';

    if (isset($_GET['url']) && $_GET['url'] !== '') {
        $uri = $_GET['url'];
    } elseif (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
        $basePath = parse_url(BASE_URL, PHP_URL_PATH);
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = strtok($uri, '?');
    }

    $uri = trim($uri, '/');

    // ── Soporte de transición: URLs antiguas controller/{modulo}/ ──
    if (preg_match('#^controller/([^/]+)#', $uri, $matches)) {
        $oldController = $matches[1];
        $oldFile = CONTROLLER_PATH . $oldController . '/index.php';
        if (file_exists($oldFile)) {
            require $oldFile;
            exit;
        }
        throw new Exception("Controller not found: $oldController", 404);
    }

    // ── Cargar rutas nuevas ──
    $routes = require __DIR__ . '/config/routes.php';
    $router = new Router($routes);
    $route = $router->resolve($uri);

    $controllerName = $route['controller'];
    $actionName = $route['action'];
    $params = $route['params'];

    // Cargar y ejecutar el controlador nuevo
    $controllerFile = CONTROLLER_PATH . $controllerName . '.php';

    if (!file_exists($controllerFile)) {
        throw new Exception("Controller file not found: $controllerName", 500);
    }

    require_once $controllerFile;

    if (!class_exists($controllerName)) {
        throw new Exception("Controller class not found: $controllerName", 500);
    }

    $controller = new $controllerName();

    if (!method_exists($controller, $actionName)) {
        throw new Exception("Action not found: $controllerName::$actionName", 500);
    }

    call_user_func_array([$controller, $actionName], $params);

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);

    if ($code === 404) {
        echo '<h1>404 — Página no encontrada</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<a href="' . BASE_URL . '">Volver al inicio</a>';
    } else {
        echo '<h1>500 — Error interno</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    exit;
}
