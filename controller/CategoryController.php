<?php

class CategoryController extends BaseController
{
    private function getModel(): Category
    {
        return new Category();
    }

    public function listAction(): void
    {
        $this->requireRole('Administrator');

        $category = $this->getModel();
        $categories = $category->getAll();

        $this->render('category/content-list', [
            'pageTitle' => 'Lista de Categorías',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Categorías', 'url' => ''],
            ],
            'categories' => $categories,
            'category' => $category,
        ]);
    }

    public function createAction(): void
    {
        $this->requireRole('Administrator');

        $category = $this->getModel();

        if ($this->isPost()) {
            $name = $this->getPost('name');
            $description = $this->getPost('description');

            if ($category->create($name, $description)) {
                $this->redirect('category?success=created');
            }
        }

        $this->render('category/content-form', [
            'pageTitle' => 'Nueva Categoría',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Categorías', 'url' => BASE_URL . 'category'],
                ['name' => 'Crear', 'url' => ''],
            ],
            'category' => $category,
        ]);
    }

    public function editAction(): void
    {
        $this->requireRole('Administrator');

        $id_category = (int) $this->getParam('id', 0);
        $category = $this->getModel();

        if ($this->isPost()) {
            $name = $this->getPost('name');
            $description = $this->getPost('description');

            if ($category->update($id_category, $name, $description)) {
                $this->redirect('category?success=updated');
            }
        }

        $categoryData = $category->getById($id_category);

        if (!$categoryData) {
            $this->redirect('category?error=not_found');
        }

        $this->render('category/content-form', [
            'pageTitle' => 'Editar Categoría',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Categorías', 'url' => BASE_URL . 'category'],
                ['name' => 'Editar', 'url' => ''],
            ],
            'categoryData' => $categoryData,
            'category' => $category,
        ]);
    }

    public function deleteAction(): void
    {
        $this->requireRole('Administrator');

        $id_category = (int) $this->getParam('id', 0);
        $category = $this->getModel();

        if ($category->delete($id_category)) {
            $this->redirect('category?success=deleted');
        } else {
            $this->redirect('category?error=delete_failed');
        }
    }
}
