<?php


require_once '../../config/app_config.php';
require_once(MODEL_PATH . 'login/Login.php');

$mlogin = new Login();


if ($mlogin->isConected() == true)
    header("location: " . BASE_URL);
else {
    require_once(VIEW_PATH . 'auth/sign-in.php');
}
