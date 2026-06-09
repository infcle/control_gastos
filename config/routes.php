<?php
/**
 * Tabla de rutas de la aplicación.
 *
 * Formato: 'uri-pattern' => ['ControllerClass', 'methodName']
 * Usar :param para segmentos dinámicos (ej: :id, :token)
 */
return [
    // Login
    'login'                    => ['LoginController', 'indexAction'],
    'login/logout'             => ['LoginController', 'logoutAction'],

    // Home / Dashboard
    ''                         => ['HomeController', 'indexAction'],
    '/'                        => ['HomeController', 'indexAction'],

    // Users
    'user'                     => ['UserController', 'listAction'],
    'user/create'              => ['UserController', 'createAction'],
    'user/edit/:id'            => ['UserController', 'editAction'],
    'user/delete/:id'          => ['UserController', 'deleteAction'],
    'user/toggle-status/:id'   => ['UserController', 'toggleStatusAction'],
    'user/change-password/:id' => ['UserController', 'changePasswordAction'],
    'user/profile'             => ['UserController', 'profileAction'],

    // Categories
    'category'                 => ['CategoryController', 'listAction'],
    'category/create'          => ['CategoryController', 'createAction'],
    'category/edit/:id'        => ['CategoryController', 'editAction'],
    'category/delete/:id'      => ['CategoryController', 'deleteAction'],

    // Suppliers
    'supplier'                 => ['SupplierController', 'listAction'],
    'supplier/create'          => ['SupplierController', 'createAction'],
    'supplier/edit/:id'        => ['SupplierController', 'editAction'],
    'supplier/delete/:id'      => ['SupplierController', 'deleteAction'],

    // Products
    'product'                  => ['ProductController', 'listAction'],
    'product/create'           => ['ProductController', 'createAction'],
    'product/edit/:id'         => ['ProductController', 'editAction'],
    'product/delete/:id'       => ['ProductController', 'deleteAction'],
    'product/toggle-status/:id' => ['ProductController', 'toggleStatusAction'],
    'product/price-history/:id' => ['ProductController', 'priceHistoryAction'],

    // Purchases
    'purchase'                 => ['PurchaseController', 'listAction'],
    'purchase/create'          => ['PurchaseController', 'createAction'],
    'purchase/view/:id'        => ['PurchaseController', 'viewAction'],
    'purchase/delete/:id'      => ['PurchaseController', 'deleteAction'],
];
