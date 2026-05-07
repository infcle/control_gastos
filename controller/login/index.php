<?php

require_once '../../config/app_config.php';
require_once(MODEL_PATH . 'login/Login.php');

$mlogin = new Login();

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnSingIn'])) {
    // El Login() ya procesó el POST en su constructor
    if ($mlogin->isConected() == true) {
        header("location: " . BASE_URL);
        exit();
    }
}

// Si ya está conectado, redirigir
if ($mlogin->isConected() == true) {
    header("location: " . BASE_URL);
    exit();
} else {
    // Si hay errores de login, mostrarlos
    if (!empty($mlogin->errors)) {
        $error_message = implode('<br>', $mlogin->errors);
    }
    if (!empty($mlogin->messages)) {
        $success_message = implode('<br>', $mlogin->messages);
    }
    require_once(VIEW_PATH . 'auth/sign-in.php');
}
