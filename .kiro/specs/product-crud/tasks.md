# Plan de Implementación: Product CRUD

## Descripción general

Implementar el módulo CRUD de productos siguiendo exactamente el patrón MVC del módulo de usuarios existente. El módulo incluye: migración SQL, modelo con historial de precios y soft delete, controlador con enrutamiento por `?action`, tres vistas PHP y tests unitarios en `tests/ProductTest.php`. Todo el trabajo se realiza en la rama `feature/product-crud`.

---

## Tareas

- [x] 1. Crear rama git y migración de base de datos
  - Crear la rama `feature/product-crud` desde la rama actual con `git checkout -b feature/product-crud`
  - Crear el archivo `script_db/07_05_2026_create_products_table.sql` con los tres pasos del diseño:
    1. `CREATE TABLE IF NOT EXISTS products` sin FK en `price_id`
    2. `CREATE TABLE IF NOT EXISTS prices` con FK a `products.id_product`
    3. `ALTER TABLE products ADD CONSTRAINT fk_products_current_price FOREIGN KEY (price_id) REFERENCES prices(id_price)`
  - Verificar que el script usa `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4` igual que la migración de usuarios
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [x] 2. Implementar el modelo `model/product/Product.php`
  - [x] 2.1 Crear la clase `Product` con constructor y destructor siguiendo el patrón de `model/user/User.php`
    - Propiedades públicas `$errors = []` y `$messages = []`
    - Constructor que abre conexión mysqli a `expense_db` y configura charset utf8
    - Destructor que cierra la conexión
    - _Requirements: 1.2, 2.1_

  - [x] 2.2 Implementar `getAllProducts(): array`
    - Ejecutar la consulta JOIN de `products` con `prices` usando `p.price_id = pr.id_price`
    - Filtrar `WHERE p.status = 1 ORDER BY p.created_at DESC`
    - Retornar array con campos: `id_product`, `name`, `description`, `status`, `created_at`, `price`
    - Retornar array vacío si no hay resultados
    - _Requirements: 1.1, 1.2, 1.4_

  - [ ]* 2.3 Escribir test de propiedad para `getAllProducts()`
    - **Property 1: getAllProducts returns only active products sorted descending**
    - **Validates: Requirements 1.1, 1.4**

  - [x] 2.4 Implementar `getProductById(int $id_product): ?array`
    - Consulta JOIN `products`/`prices` con `WHERE p.id_product = ? AND p.status = 1`
    - Retornar array asociativo con todos los campos del diseño si existe y está activo
    - Retornar `null` si no existe o `status = 0`
    - Escapar `$id_product` con `real_escape_string`
    - _Requirements: 6.1, 6.2, 6.3_

  - [ ]* 2.5 Escribir test de propiedad para `getProductById()`
    - **Property 9: getProductById returns null for soft-deleted products**
    - **Validates: Requirements 6.3**

  - [x] 2.6 Implementar `createProduct(string $name, ?string $description, float $price): bool`
    - Validar que `$name` no esté vacío ni sea solo espacios; agregar error y retornar `false` si falla
    - Validar que `$price` sea numérico y mayor a 0; agregar error y retornar `false` si falla
    - Escapar todos los valores con `real_escape_string`
    - Secuencia atómica: INSERT products → capturar `last_insert_id()` → INSERT prices → capturar `id_price` → UPDATE products SET price_id
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

  - [ ]* 2.7 Escribir test de propiedad para `createProduct()`
    - **Property 3: price_id always references the Current_Price (create)**
    - **Validates: Requirements 2.2**

  - [ ]* 2.8 Escribir test de propiedad para validación de nombre vacío
    - **Property 4: Empty name is rejected**
    - **Validates: Requirements 2.3, 3.2**

  - [ ]* 2.9 Escribir test de propiedad para validación de precio inválido
    - **Property 5: Invalid price is rejected**
    - **Validates: Requirements 2.4, 4.3**

  - [x] 2.10 Implementar `updateProduct(int $id_product, string $name, ?string $description): bool`
    - Validar que `$name` no esté vacío; agregar error y retornar `false` si falla
    - Verificar que el producto existe y tiene `status = 1`; agregar error y retornar `false` si no
    - Ejecutar `UPDATE products SET name, description WHERE id_product`
    - _Requirements: 3.1, 3.2, 3.3, 3.4_

  - [ ]* 2.11 Escribir test de propiedad para operaciones sobre productos inactivos/inexistentes
    - **Property 6: Operations on inactive or non-existent products fail**
    - **Validates: Requirements 3.3, 5.4, 6.2, 6.3**

  - [x] 2.12 Implementar `updatePrice(int $id_product, float $price): bool`
    - Validar que `$price` sea numérico y mayor a 0; agregar error y retornar `false` si falla
    - Verificar que el producto existe y tiene `status = 1`; agregar error y retornar `false` si no
    - Secuencia: INSERT prices → capturar `id_price` → UPDATE products SET price_id
    - Preservar todos los registros anteriores en `prices` (no borrar historial)
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [ ]* 2.13 Escribir test de propiedad para `updatePrice()`
    - **Property 3: price_id always references the Current_Price (update)**
    - **Validates: Requirements 4.1, 4.2**

  - [x] 2.14 Implementar `deleteProduct(int $id_product): bool`
    - Verificar que el producto existe y tiene `status = 1`; agregar error y retornar `false` si no
    - Ejecutar `UPDATE products SET status = 0 WHERE id_product`
    - No borrar registros de `prices` asociados
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [ ]* 2.15 Escribir test de propiedad para soft delete
    - **Property 7: Soft delete preserves all data**
    - **Validates: Requirements 5.1, 5.2**

  - [x] 2.16 Implementar `getPriceHistory(int $id_product): array`
    - Consultar `SELECT id_price, price, created_at FROM prices WHERE id_product = ? ORDER BY created_at DESC`
    - Retornar array vacío si no hay registros
    - _Requirements: 7.1, 7.2_

  - [ ]* 2.17 Escribir test de propiedad para historial de precios
    - **Property 8: Price history is complete and ordered**
    - **Validates: Requirements 4.4, 7.1, 7.2**

- [x] 3. Checkpoint — Verificar el modelo
  - Asegurarse de que todos los métodos del modelo están implementados y los tests pasan. Consultar al usuario si surgen dudas.

- [x] 4. Implementar el controlador `controller/product/index.php`
  - [x] 4.1 Crear el archivo del controlador con autenticación de sesión y control de acceso
    - Incluir `config/app_config.php` y `session_start()`
    - Redirigir a `BASE_URL . 'controller/login/'` si no hay sesión activa
    - Redirigir a `BASE_URL` si el rol no es `Administrator`
    - Incluir `model/product/Product.php` e instanciar `$product = new Product()`
    - _Requirements: 8.1, 8.2_

  - [x] 4.2 Implementar las acciones `list`, `create`, `edit`, `update_price` y `delete`
    - Acción `list` (default): llamar `$product->getAllProducts()`, asignar a `$products`, apuntar `$content` a `view/product/content-list.php`
    - Acción `create` GET: apuntar `$content` a `view/product/content-form.php`
    - Acción `create` POST: leer `$_POST['name']`, `$_POST['description']`, `$_POST['price']`; llamar `createProduct()`; redirigir con `success=created` o re-renderizar con errores
    - Acción `edit` GET: llamar `getProductById($id)`; apuntar `$content` a `view/product/content-form.php`
    - Acción `edit` POST: llamar `updateProduct()`; redirigir con `success=updated` o re-renderizar con errores
    - Acción `update_price` GET: llamar `getProductById($id)` y `getPriceHistory($id)`; apuntar `$content` a `view/product/content-price.php`
    - Acción `update_price` POST: llamar `updatePrice()`; redirigir con `success=price_updated` o re-renderizar con errores
    - Acción `delete`: llamar `deleteProduct()`; redirigir con `success=deleted` o `error=delete_failed`
    - Incluir `view/template/layout.php` al final
    - _Requirements: 1.3, 2.5, 2.6, 3.4, 4.5, 5.3, 5.5_

- [x] 5. Implementar las vistas en `view/product/`
  - [x] 5.1 Crear `view/product/content-list.php`
    - Mostrar alertas de éxito/error con las claves: `created`, `updated`, `price_updated`, `deleted`, `delete_failed`
    - Mostrar errores de `$product->errors` si existen
    - Cabecera con título "Gestión de Productos" y botón "Nuevo Producto" que apunta a `?action=create`
    - Tabla con columnas: Nombre, Descripción, Precio actual, Estado, Acciones
    - Por cada producto: botón Editar (`?action=edit&id=`), botón Precio (`?action=update_price&id=`), botón Eliminar con confirmación (`?action=delete&id=`)
    - Usar `htmlspecialchars()` en todos los valores de salida
    - Mensaje "No hay productos registrados" con enlace a crear si el array está vacío
    - _Requirements: 1.3, 1.4_

  - [x] 5.2 Crear `view/product/content-form.php`
    - Mostrar errores de `$product->errors` si existen
    - Formulario reutilizable para crear y editar: detectar si `$productData` está definido para determinar el modo
    - Campo `name` (requerido, maxlength 255) con valor pre-rellenado en edición
    - Campo `description` (textarea, opcional) con valor pre-rellenado en edición
    - Campo `price` (requerido, type number, step 0.01, min 0.01) solo visible en modo creación
    - Action del formulario: `?action=create` o `?action=edit&id=`
    - Botones Guardar y Cancelar (vuelve a `?action=list`)
    - _Requirements: 2.6, 3.4_

  - [x] 5.3 Crear `view/product/content-price.php`
    - Mostrar errores de `$product->errors` si existen
    - Mostrar nombre del producto como contexto
    - Formulario con campo `price` (requerido, type number, step 0.01, min 0.01)
    - Action del formulario: `?action=update_price&id=`
    - Tabla de historial de precios con columnas: Precio, Fecha; datos de `$priceHistory`
    - Mensaje "Sin historial de precios" si el array está vacío
    - _Requirements: 4.5, 7.1_

- [x] 6. Checkpoint — Verificar controlador y vistas
  - Asegurarse de que el flujo completo (list → create → edit → update_price → delete) funciona sin errores de PHP. Consultar al usuario si surgen dudas.

- [x] 7. Implementar los tests en `tests/ProductTest.php`
  - [-] 7.1 Crear la clase `ProductTest` con la misma estructura que `tests/UserTest.php`
    - Constructor con supresión de warnings y `session_start()`
    - Destructor con limpieza de datos de prueba (DELETE WHERE name LIKE 'test_%')
    - Método `runAllTests()` que invoca todos los métodos `testXxx()`
    - Bloque de ejecución directa al final del archivo
    - _Requirements: 10.1_

  - [~] 7.2 Implementar `testGetAllProducts()`
    - Verificar que `getAllProducts()` retorna un array
    - Si hay resultados, verificar que el primer elemento tiene los campos `id_product`, `name`, `price`
    - _Requirements: 10.2_

  - [~] 7.3 Implementar `testCreateProduct()`
    - Crear producto con datos válidos; verificar retorno `true`
    - Verificar que existe un registro en `prices` para el producto creado
    - Verificar que `price_id` del producto apunta al `id_price` insertado
    - Verificar que `createProduct()` retorna `false` con nombre vacío y `$errors` no vacío
    - Verificar que `createProduct()` retorna `false` con precio 0, negativo y string no numérico
    - _Requirements: 10.3, 10.4, 10.5_

  - [~] 7.4 Implementar `testSoftDelete()`
    - Crear un producto de prueba
    - Llamar `deleteProduct()` y verificar retorno `true`
    - Consultar directamente la BD y verificar que el registro existe con `status = 0`
    - Verificar que los registros en `prices` siguen existiendo
    - _Requirements: 10.6_

  - [~] 7.5 Implementar `testUpdatePrice()`
    - Crear un producto de prueba con precio inicial
    - Llamar `updatePrice()` con un nuevo precio
    - Verificar que se insertó un nuevo registro en `prices`
    - Verificar que `price_id` del producto fue actualizado al nuevo `id_price`
    - _Requirements: 10.7_

  - [~] 7.6 Implementar `testGetPriceHistory()`
    - Crear un producto e insertar múltiples precios con `updatePrice()`
    - Verificar que `getPriceHistory()` retorna todos los registros
    - Verificar que el orden es descendente por `created_at`
    - Verificar que retorna array vacío para un `id_product` sin precios
    - _Requirements: 10.8_

- [~] 8. Checkpoint final — Ejecutar todos los tests
  - Ejecutar `php tests/ProductTest.php` y verificar que todos los tests pasan con ✅
  - Asegurarse de que no quedan datos de prueba en la base de datos
  - Consultar al usuario si algún test falla antes de continuar.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia los requisitos específicos para trazabilidad
- El orden de las tareas es importante: la migración debe ejecutarse antes de correr los tests
- Los tests de propiedades requieren instalar **eris** (`composer require giorgiosironi/eris --dev`) o **php-quickcheck** como dependencia de desarrollo
- Todos los valores de usuario deben escaparse con `real_escape_string()` y las salidas HTML con `htmlspecialchars()`, siguiendo el patrón de `model/user/User.php`
- La variable `$priceHistory` debe ser expuesta por el controlador en la acción `update_price` para que `content-price.php` pueda renderizar el historial
