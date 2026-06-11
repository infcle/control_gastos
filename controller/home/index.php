<?php
require_once __DIR__ . '/../../config/app_config.php';
require_once CONFIG_PATH . 'auth_helper.php';
requireAuth();

$titulo = "Bienvenido";
$contenido = "inicio.php";
$sub_directory = "";
//$menu_a = $menus['INICIO'];
$subTitulo = "Inicio";
$pie_class = "si";
require_once(VIEW_PATH . 'template/layout.php');
