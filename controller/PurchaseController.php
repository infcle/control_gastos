<?php

class PurchaseController extends BaseController
{
    public function listAction(): void
    {
        $this->requireRole('Administrator');

        $purchase = new Purchase();
        $purchases = $purchase->getAll();

        $this->render('purchase/content-list', [
            'pageTitle' => 'Lista de Compras',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Compras', 'url' => ''],
            ],
            'purchases' => $purchases,
            'purchase' => $purchase,
        ]);
    }

    public function createAction(): void
    {
        $this->requireRole('Administrator');

        $purchase = new Purchase();
        $productModel = new Product();
        $supplierModel = new Supplier();

        if ($this->isPost()) {
            $purchase_date = $this->getPost('purchase_date');
            $observation = $this->getPost('observation', '');
            $id_user = $_SESSION['user_id'];

            // Parsear el formato anidado suppliers[] del POST
            $details = [];

            if (isset($_POST['suppliers']) && is_array($_POST['suppliers'])) {
                foreach ($_POST['suppliers'] as $supplier_group) {
                    $id_supplier = $supplier_group['id_supplier'];

                    if (isset($supplier_group['products']) && is_array($supplier_group['products'])) {
                        foreach ($supplier_group['products'] as $product_item) {
                            $details[] = [
                                'id_product'  => $product_item['id_product'],
                                'id_supplier' => $id_supplier,
                                'quantity'    => $product_item['quantity'],
                                'unit_price'  => $product_item['unit_price'],
                                'observation' => $product_item['observation'] ?? '',
                            ];
                        }
                    }
                }
            }

            if ($purchase->create($purchase_date, $id_user, $observation, $details)) {
                $this->redirect('purchase?success=created');
            }
        }

        $products = $productModel->getAllProducts();
        $suppliers = $supplierModel->getAll();

        $this->render('purchase/content-form', [
            'pageTitle' => 'Nueva Compra',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Compras', 'url' => BASE_URL . 'purchase'],
                ['name' => 'Crear', 'url' => ''],
            ],
            'products' => $products,
            'suppliers' => $suppliers,
            'purchase' => $purchase,
        ]);
    }

    public function viewAction(): void
    {
        $this->requireRole('Administrator');

        $id_purchase = (int) $this->getParam('id', 0);
        $purchase = new Purchase();

        $purchaseData = $purchase->getById($id_purchase);

        if (!$purchaseData) {
            $this->redirect('purchase?error=not_found');
        }

        $this->render('purchase/content-view', [
            'pageTitle' => 'Detalle de Compra',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Compras', 'url' => BASE_URL . 'purchase'],
                ['name' => 'Detalle', 'url' => ''],
            ],
            'purchaseData' => $purchaseData,
            'purchase' => $purchase,
        ]);
    }

    public function deleteAction(): void
    {
        $this->requireRole('Administrator');

        $id_purchase = (int) $this->getParam('id', 0);
        $purchase = new Purchase();

        if ($purchase->delete($id_purchase)) {
            $this->redirect('purchase?success=deleted');
        } else {
            $this->redirect('purchase?error=delete_failed');
        }
    }
}
