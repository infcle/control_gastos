<?php
require_once("../../config/app_config.php");
session_start();
if (!isset($_SESSION['user_login_status']) 
    and $_SESSION['user_login_status'] != 1) {
    header("location: " . CONTROLLER_URL . 'login/');
    exit;
}

$titulo = "Bienvenido";
$contenido = "inicio.php";
$sub_directory = "";
//$menu_a = $menus['INICIO'];
$subTitulo = "Inicio";
$pie_class = "si";
require_once(VIEW_PATH . 'template/layout.php');
