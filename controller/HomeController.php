<?php

class HomeController extends BaseController
{
    public function indexAction(): void
    {
        $this->requireAuth();

        $pageTitle = 'Panel Principal';
        $breadcrumb = [
            ['name' => 'Dashboard', 'url' => BASE_URL],
            ['name' => 'Inicio', 'url' => ''],
        ];

        // Render template sin contenido específico (dashboard)
        $content = VIEW_PATH . 'admin.php';
        require VIEW_PATH . 'template/layout.php';
    }
}
