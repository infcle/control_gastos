# Requirements Document

## Introduction

Este documento describe los requisitos para el CRUD de productos del sistema de control de gastos (`expense_db`). La funcionalidad permite gestionar un catálogo de productos con historial de precios, siguiendo el mismo patrón arquitectónico MVC ya establecido en el módulo de usuarios. Los productos se eliminan de forma lógica (soft delete) y cada cambio de precio queda registrado en una tabla de historial.

## Glossary

- **Product_Controller**: Controlador PHP ubicado en `controller/product/index.php` que gestiona las acciones HTTP del módulo de productos.
- **Product_Model**: Clase PHP ubicada en `model/product/Product.php` que encapsula toda la lógica de acceso a datos de productos y precios.
- **Product_View**: Archivos de vista PHP ubicados en `view/product/` siguiendo el mismo patrón del módulo de usuarios (`view/user/`). Los archivos esperados son `view/product/content-list.php`, `view/product/content-form.php` y `view/product/content-price.php`.
- **Product**: Entidad del catálogo con campos `id_product`, `name`, `description`, `price_id`, `status` y `created_at`.
- **Price**: Entidad de historial de precios con campos `id_price`, `id_product`, `price` y `created_at`.
- **Soft_Delete**: Mecanismo de eliminación lógica que establece `status = 0` en lugar de borrar el registro físicamente.
- **Active_Product**: Producto cuyo campo `status` tiene valor `1`.
- **Inactive_Product**: Producto cuyo campo `status` tiene valor `0` (eliminado lógicamente).
- **Current_Price**: El registro de la tabla `prices` con el mayor `id_price` asociado a un producto dado. El campo `price_id` en `products` siempre referencia a este registro más reciente.
- **Migration**: Script SQL versionado por fecha ubicado en `script_db/`, con formato `DD_MM_YYYY_<descripcion>.sql`.
- **Administrator**: Rol de usuario con acceso completo al módulo de productos.
- **System**: El sistema de control de gastos en su conjunto.

---

## Requirements

### Requirement 1: Listado de productos

**User Story:** As an Administrator, I want to list all active products with their current price, so that I can have a complete view of the product catalog.

#### Acceptance Criteria

1. WHEN the Administrator accesses the product list action, THE Product_Controller SHALL retrieve all Active_Products ordered by `created_at` descending.
2. THE Product_Model SHALL return for each product: `id_product`, `name`, `description`, `status`, `created_at`, and the `price` value of the Current_Price.
3. WHILE the product list is displayed, THE Product_Controller SHALL pass the product array to `view/product/content-list.php`.
4. IF no Active_Products exist in the database, THEN THE Product_Controller SHALL pass an empty array to `view/product/content-list.php`.

---

### Requirement 2: Creación de producto

**User Story:** As an Administrator, I want to create a new product with an initial price, so that I can add items to the catalog.

#### Acceptance Criteria

1. WHEN the Administrator submits the create form with valid data, THE Product_Model SHALL insert a new record in the `products` table with `status = 1`.
2. WHEN a new product is inserted, THE Product_Model SHALL insert a corresponding record in the `prices` table and update the `price_id` field of the product with the new `id_price`, so that `price_id` always references the Current_Price.
3. IF the `name` field is empty, THEN THE Product_Model SHALL add an error message to the `errors` array and return `false`.
4. IF the `price` field is not a numeric value greater than 0, THEN THE Product_Model SHALL add an error message to the `errors` array and return `false`.
5. WHEN the product is created successfully, THE Product_Controller SHALL redirect to the product list with a `success=created` query parameter.
6. IF the Product_Model returns `false`, THEN THE Product_Controller SHALL re-render `view/product/content-form.php` displaying the errors from `Product_Model::$errors`.

---

### Requirement 3: Edición de producto

**User Story:** As an Administrator, I want to edit the name and description of an existing product, so that I can keep the catalog information up to date.

#### Acceptance Criteria

1. WHEN the Administrator submits the edit form with valid data, THE Product_Model SHALL update the `name` and `description` fields of the product identified by `id_product`.
2. IF the `name` field is empty on edit, THEN THE Product_Model SHALL add an error message to the `errors` array and return `false`.
3. IF the `id_product` does not correspond to an existing Active_Product, THEN THE Product_Model SHALL add an error message to the `errors` array and return `false`.
4. WHEN the product is updated successfully, THE Product_Controller SHALL redirect to the product list with a `success=updated` query parameter.

---

### Requirement 4: Actualización de precio

**User Story:** As an Administrator, I want to update the price of a product, so that I can maintain an accurate price history.

#### Acceptance Criteria

1. WHEN the Administrator submits a new price for a product, THE Product_Model SHALL insert a new record in the `prices` table with the `id_product` and the new `price` value.
2. WHEN a new price record is inserted, THE Product_Model SHALL update the `price_id` field of the corresponding product with the new `id_price`, so that `price_id` always references the Current_Price.
3. IF the new `price` value is not a numeric value greater than 0, THEN THE Product_Model SHALL add an error message to the `errors` array and return `false`.
4. THE Product_Model SHALL preserve all previous records in the `prices` table when a new price is added, maintaining the full price history.
5. WHEN the price is updated successfully, THE Product_Controller SHALL redirect to the product list with a `success=price_updated` query parameter.

---

### Requirement 5: Eliminación lógica de producto

**User Story:** As an Administrator, I want to logically delete a product, so that it is no longer visible in the catalog without losing its historical data.

#### Acceptance Criteria

1. WHEN the Administrator triggers the delete action for a product, THE Product_Model SHALL set `status = 0` on the corresponding record in the `products` table.
2. THE Product_Model SHALL preserve the product record and all associated `prices` records in the database after a Soft_Delete.
3. WHEN the Soft_Delete is executed successfully, THE Product_Controller SHALL redirect to the product list with a `success=deleted` query parameter.
4. IF the `id_product` does not correspond to an existing Active_Product, THEN THE Product_Model SHALL add an error message to the `errors` array and return `false`.
5. WHEN the delete action fails, THE Product_Controller SHALL redirect to the product list with an `error=delete_failed` query parameter.

---

### Requirement 6: Consulta de producto por ID

**User Story:** As an Administrator, I want to retrieve a single product by its ID, so that I can populate the edit and price update forms.

#### Acceptance Criteria

1. WHEN `getProductById` is called with a valid `id_product`, THE Product_Model SHALL return an associative array containing `id_product`, `name`, `description`, `status`, `price_id`, and the `price` value of the Current_Price.
2. WHEN `getProductById` is called with an `id_product` that does not exist in the database, THE Product_Model SHALL return `null`.
3. WHEN `getProductById` is called with an `id_product` corresponding to an Inactive_Product, THE Product_Model SHALL return `null`.

---

### Requirement 7: Historial de precios

**User Story:** As an Administrator, I want to view the full price history of a product, so that I can audit price changes over time.

#### Acceptance Criteria

1. WHEN `getPriceHistory` is called with a valid `id_product`, THE Product_Model SHALL return all records from the `prices` table associated with that product, ordered by `created_at` descending.
2. IF no price records exist for the given `id_product`, THEN THE Product_Model SHALL return an empty array.

---

### Requirement 8: Control de acceso

**User Story:** As a System, I want to restrict product management to Administrators, so that unauthorized users cannot modify the product catalog.

#### Acceptance Criteria

1. WHEN a non-authenticated user accesses any product action, THE Product_Controller SHALL redirect to the login URL defined in `BASE_URL . 'controller/login/'`.
2. WHEN an authenticated user with a role other than `Administrator` accesses any product action, THE Product_Controller SHALL redirect to `BASE_URL`.

---

### Requirement 9: Migraciones de base de datos

**User Story:** As a developer, I want versioned SQL migration scripts for the products and prices tables, so that the database schema can be applied consistently across environments.

#### Acceptance Criteria

1. THE System SHALL provide a Migration file named with the format `DD_MM_YYYY_create_products_table.sql` located in `script_db/`.
2. THE Migration SHALL create the `products` table with columns: `id_product` (INT, PK, AUTO_INCREMENT), `name` (VARCHAR(255), NOT NULL), `description` (TEXT, NULL), `price_id` (INT, NULL), `status` (TINYINT(1), DEFAULT 1), `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP). The `price_id` column SHALL NOT have a foreign key constraint at table creation time to avoid a circular dependency with the `prices` table.
3. THE Migration SHALL create the `prices` table with columns: `id_price` (INT, PK, AUTO_INCREMENT), `id_product` (INT, NOT NULL, FK to `products.id_product`), `price` (DECIMAL(10,2), NOT NULL), `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP).
4. THE Migration SHALL add the foreign key constraint on `products.price_id` referencing `prices.id_price` via a separate `ALTER TABLE` statement executed after both tables are created, resolving the circular dependency.
5. THE Migration SHALL use `CREATE TABLE IF NOT EXISTS` to allow safe re-execution without errors.

---

### Requirement 10: Tests del modelo de productos

**User Story:** As a developer, I want unit tests for the Product_Model, so that I can verify the correctness of all CRUD operations.

#### Acceptance Criteria

1. THE System SHALL provide a test file `tests/ProductTest.php` following the same structure as `tests/UserTest.php`.
2. WHEN `testGetAllProducts` is executed, THE ProductTest SHALL verify that `getAllProducts()` returns an array.
3. WHEN `testCreateProduct` is executed, THE ProductTest SHALL verify that a product with valid data is created and that a corresponding price record is inserted.
4. WHEN `testCreateProduct` is executed, THE ProductTest SHALL verify that `createProduct()` returns `false` when `name` is empty.
5. WHEN `testCreateProduct` is executed, THE ProductTest SHALL verify that `createProduct()` returns `false` when `price` is not a numeric value greater than 0.
6. WHEN `testSoftDelete` is executed, THE ProductTest SHALL verify that after calling `deleteProduct()`, the product record still exists in the database with `status = 0`.
7. WHEN `testUpdatePrice` is executed, THE ProductTest SHALL verify that calling `updatePrice()` inserts a new record in `prices` and updates `price_id` on the product.
8. WHEN `testGetPriceHistory` is executed, THE ProductTest SHALL verify that `getPriceHistory()` returns all price records for a product in descending order of `created_at`.
