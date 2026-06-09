<?php

class SupplierController extends BaseController
{
    private function getModel(): Supplier
    {
        return new Supplier();
    }

    public function listAction(): void
    {
        $this->requireRole('Administrator');

        $supplier = $this->getModel();
        $suppliers = $supplier->getAll();

        $this->render('supplier/content-list', [
            'pageTitle' => 'Lista de Proveedores',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Proveedores', 'url' => ''],
            ],
            'suppliers' => $suppliers,
            'supplier' => $supplier,
        ]);
    }

    public function createAction(): void
    {
        $this->requireRole('Administrator');

        $supplier = $this->getModel();

        if ($this->isPost()) {
            $name = $this->getPost('name');
            $location = $this->getPost('location');

            if ($supplier->create($name, $location)) {
                $this->redirect('supplier?success=created');
            }
        }

        $this->render('supplier/content-form', [
            'pageTitle' => 'Nuevo Proveedor',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Proveedores', 'url' => BASE_URL . 'supplier'],
                ['name' => 'Crear', 'url' => ''],
            ],
            'supplier' => $supplier,
        ]);
    }

    public function editAction(): void
    {
        $this->requireRole('Administrator');

        $id_supplier = (int) $this->getParam('id', 0);
        $supplier = $this->getModel();

        if ($this->isPost()) {
            $name = $this->getPost('name');
            $location = $this->getPost('location');

            if ($supplier->update($id_supplier, $name, $location)) {
                $this->redirect('supplier?success=updated');
            }
        }

        $supplierData = $supplier->getById($id_supplier);

        if (!$supplierData) {
            $this->redirect('supplier?error=not_found');
        }

        $this->render('supplier/content-form', [
            'pageTitle' => 'Editar Proveedor',
            'breadcrumb' => [
                ['name' => 'Dashboard', 'url' => BASE_URL],
                ['name' => 'Proveedores', 'url' => BASE_URL . 'supplier'],
                ['name' => 'Editar', 'url' => ''],
            ],
            'supplierData' => $supplierData,
            'supplier' => $supplier,
        ]);
    }

    public function deleteAction(): void
    {
        $this->requireRole('Administrator');

        $id_supplier = (int) $this->getParam('id', 0);
        $supplier = $this->getModel();

        if ($supplier->delete($id_supplier)) {
            $this->redirect('supplier?success=deleted');
        } else {
            $this->redirect('supplier?error=delete_failed');
        }
    }
}
