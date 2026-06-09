<?php

class ProductController extends BaseController
{
    private function getModel(): Product
    {
        return new Product();
    }

    public function listAction(): void
    {
        $this->requireAuth();

        $product = $this->getModel();
        $products = $product->getAllProducts();

        $this->render('product/content-list', [
            'pageTitle' => 'Lista de Productos',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Productos', 'url' => ''],
            ],
            'products' => $products,
        ]);
    }

    public function createAction(): void
    {
        $this->requireAuth();

        $product = $this->getModel();

        if ($this->isPost()) {
            $name = $this->getPost('name');
            $description = $this->getPost('description');
            $price_id = $this->getPost('price_id');
            $id_category = $this->getPost('id_category');

            if ($id_category === '') {
                $id_category = null;
            }

            if ($product->createProduct($name, $description, $price_id, $id_category)) {
                $this->redirect('product?success=created');
            }
        }

        $prices = $product->getAllPrices();
        $categories = $product->getAllCategories();

        $this->render('product/content-form', [
            'pageTitle' => 'Nuevo Producto',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Productos', 'url' => BASE_URL . 'product'],
                ['name' => 'Crear', 'url' => ''],
            ],
            'prices' => $prices,
            'categories' => $categories,
            'product' => $product,
        ]);
    }

    public function editAction(): void
    {
        $this->requireAuth();

        $id_product = (int) $this->getParam('id', 0);
        $product = $this->getModel();

        $productData = $product->getProductById($id_product);

        if (!$productData) {
            $this->redirect('product?error=not_found');
        }

        if ($this->isPost()) {
            $name = $this->getPost('name');
            $description = $this->getPost('description');
            $price_id = $this->getPost('price_id');
            $status = $this->getPost('status', 1);
            $id_category = $this->getPost('id_category');

            if ($id_category === '') {
                $id_category = null;
            }

            if ($product->updateProduct($id_product, $name, $description, $price_id, $status, $id_category)) {
                $this->redirect('product?success=updated');
            }
        }

        $prices = $product->getAllPrices();
        $categories = $product->getAllCategories();

        $this->render('product/content-form', [
            'pageTitle' => 'Editar Producto',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Productos', 'url' => BASE_URL . 'product'],
                ['name' => 'Editar', 'url' => ''],
            ],
            'productData' => $productData,
            'prices' => $prices,
            'categories' => $categories,
            'product' => $product,
        ]);
    }

    public function deleteAction(): void
    {
        $this->requireAuth();

        $id_product = (int) $this->getParam('id', 0);
        $product = $this->getModel();

        if ($product->deleteProduct($id_product)) {
            $this->redirect('product?success=deleted');
        } else {
            $this->redirect('product?error=delete_failed');
        }
    }

    public function toggleStatusAction(): void
    {
        $this->requireAuth();

        $id_product = (int) $this->getParam('id', 0);
        $product = $this->getModel();

        if ($product->toggleProductStatus($id_product)) {
            $this->redirect('product?success=status_toggled');
        } else {
            $this->redirect('product?error=status_failed');
        }
    }

    public function priceHistoryAction(): void
    {
        $this->requireAuth();

        $id_product = (int) $this->getParam('id', 0);
        $product = $this->getModel();

        $priceHistory = $product->getProductPriceHistory($id_product);
        $productData = $product->getProductById($id_product);

        $this->render('product/content-price', [
            'pageTitle' => 'Historial de Precios',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Productos', 'url' => BASE_URL . 'product'],
                ['name' => 'Historial de Precios', 'url' => ''],
            ],
            'priceHistory' => $priceHistory,
            'productData' => $productData,
            'product' => $product,
        ]);
    }
}
