<?php

class LoginController extends BaseController
{
    public function indexAction(): void
    {
        $mlogin = new Login();

        if ($this->isPost() && isset($_POST['btnSingIn'])) {
            if ($mlogin->isConected() == true) {
                $this->redirect('');
            }
        }

        if ($mlogin->isConected() == true) {
            $this->redirect('');
        }

        if (!empty($mlogin->errors)) {
            $error_message = implode('<br>', $mlogin->errors);
        }
        if (!empty($mlogin->messages)) {
            $success_message = implode('<br>', $mlogin->messages);
        }

        // Login no usa layout — es página standalone
        require VIEW_PATH . 'auth/sign-in.php';
    }

    public function logoutAction(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        session_destroy();
        $this->redirect('login');
    }
}
