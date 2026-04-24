<?php
// Rutas del sistema de archivos
define('BASE_PATH', dirname(dirname(__FILE__)) . '/');
define('CONFIG_PATH', BASE_PATH . 'config/');
define('CONTROLLER_PATH', BASE_PATH . 'controller/');
define('MODEL_PATH', BASE_PATH . 'model/');
define('VIEW_PATH', BASE_PATH . 'view/');
define('ASSETS_PATH', BASE_PATH . 'view/assets/');

// URLs
define('BASE_URL', 'http://localhost/control_gastos/');
define('CONTROLLER_URL', BASE_URL . 'controller/');
define('MODEL_URL', BASE_URL . 'model/');
define('VIEW_URL', BASE_URL . 'view/');
define('ASSETS_URL', VIEW_URL . 'assets/');
