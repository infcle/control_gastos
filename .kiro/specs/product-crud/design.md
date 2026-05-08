# Design Document: Product CRUD

## Overview

Este documento describe el diseño técnico del módulo de gestión de productos (`product-crud`) para el sistema `expense_db`. El módulo sigue el patrón MVC ya establecido en el módulo de usuarios, extendiendo la arquitectura existente con dos nuevas entidades: `products` y `prices`.

La característica central del diseño es la relación 1-a-muchos entre productos y precios: cada producto mantiene un puntero `price_id` al precio más reciente (Current_Price), mientras que la tabla `prices` actúa como historial inmutable. La eliminación es siempre lógica (`status = 0`), preservando el historial completo.

### Decisiones de diseño clave

- **FK circular resuelta en dos pasos**: `products` se crea sin FK en `price_id`; la constraint se agrega con `ALTER TABLE` después de crear `prices`.
- **`description` es nullable**: permite crear productos sin descripción.
- **Soft delete exclusivo**: no existe eliminación física de productos ni de precios.
- **Patrón de vistas idéntico al módulo `user`**: tres archivos PHP en `view/product/`.
- **Acceso restringido a `Administrator`**: misma lógica de sesión que el controlador de usuarios.

---

## Architecture

El módulo sigue la arquitectura MVC del proyecto sin introducir nuevas capas ni dependencias.

```
HTTP Request
     │
     ▼
controller/product/index.php   ← enruta por ?action=
     │
     ├── model/product/Product.php   ← toda la lógica de datos
     │        │
     │        └── mysqli (expense_db)
     │                 ├── products
     │                 └── prices
     │
     └── view/product/
              ├── content-list.php
              ├── content-form.php
              └── content-price.php
                       │
                       ▼
              view/template/layout.php   ← plantilla compartida
```

### Flujo de una petición típica (crear producto)

```
POST controller/product/?action=create
  → Product_Controller lee $_POST
  → llama Product_Model::createProduct($name, $description, $price)
      → valida inputs
      → INSERT INTO products (name, description, status) VALUES (...)
      → $id_product = last_insert_id()
      → INSERT INTO prices (id_product, price) VALUES (...)
      → $id_price = last_insert_id()
      → UPDATE products SET price_id = $id_price WHERE id_product = $id_product
      → return true
  → redirect a ?action=list&success=created
```

---

## Components and Interfaces

### Product_Controller — `controller/product/index.php`

Responsabilidades: autenticación de sesión, enrutamiento por `?action`, llamada al modelo, redirección o renderizado de vista.

| Acción | Método HTTP | Descripción |
|---|---|---|
| `list` (default) | GET | Lista todos los productos activos |
| `create` | GET / POST | Formulario y procesamiento de creación |
| `edit` | GET / POST | Formulario y procesamiento de edición |
| `update_price` | GET / POST | Formulario y procesamiento de actualización de precio |
| `delete` | GET | Soft delete y redirección |

Variables que el controlador expone a las vistas:

| Variable | Tipo | Descripción |
|---|---|---|
| `$product` | `Product` | Instancia del modelo (acceso a `$product->errors`) |
| `$products` | `array` | Lista de productos (acción `list`) |
| `$productData` | `array\|null` | Datos de un producto (acciones `edit`, `update_price`) |
| `$pageTitle` | `string` | Título de la página |
| `$breadcrumb` | `array` | Migas de pan |
| `$content` | `string` | Ruta al archivo de vista parcial |

### Product_Model — `model/product/Product.php`

Interfaz pública:

```php
class Product {
    public array $errors   = [];
    public array $messages = [];

    // Lectura
    public function getAllProducts(): array
    public function getProductById(int $id_product): ?array
    public function getPriceHistory(int $id_product): array

    // Escritura
    public function createProduct(string $name, ?string $description, float $price): bool
    public function updateProduct(int $id_product, string $name, ?string $description): bool
    public function updatePrice(int $id_product, float $price): bool
    public function deleteProduct(int $id_product): bool
}
```

### Vistas — `view/product/`

| Archivo | Variables requeridas | Descripción |
|---|---|---|
| `content-list.php` | `$products`, `$product` | Tabla de productos activos con acciones |
| `content-form.php` | `$product`, `$productData?` | Formulario crear/editar (reutilizado) |
| `content-price.php` | `$product`, `$productData` | Formulario actualización de precio + historial |

---

## Data Models

### Diagrama entidad-relación

```
┌─────────────────────────────────────┐
│              products               │
├─────────────────────────────────────┤
│ id_product  INT PK AUTO_INCREMENT   │
│ name        VARCHAR(255) NOT NULL   │
│ description TEXT NULL               │
│ price_id    INT NULL  ──────────┐   │
│ status      TINYINT(1) DEFAULT 1│   │
│ created_at  TIMESTAMP DEFAULT NOW│  │
└─────────────────────────────────────┘
          │ 1                    │ FK (added via ALTER TABLE)
          │                      │
          ▼ N                    ▼
┌─────────────────────────────────────┐
│               prices                │
├─────────────────────────────────────┤
│ id_price    INT PK AUTO_INCREMENT   │
│ id_product  INT NOT NULL FK ────────┘
│ price       DECIMAL(10,2) NOT NULL  │
│ created_at  TIMESTAMP DEFAULT NOW   │
└─────────────────────────────────────┘
```

### Script de migración

Archivo: `script_db/07_05_2026_create_products_table.sql`

```sql
-- Step 1: Create products table WITHOUT the FK on price_id
-- (avoids circular dependency with prices)
CREATE TABLE IF NOT EXISTS `products` (
  `id_product`  INT(11)       NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(255)  NOT NULL,
  `description` TEXT          NULL,
  `price_id`    INT(11)       NULL,
  `status`      TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 2: Create prices table with FK to products
CREATE TABLE IF NOT EXISTS `prices` (
  `id_price`   INT(11)        NOT NULL AUTO_INCREMENT,
  `id_product` INT(11)        NOT NULL,
  `price`      DECIMAL(10,2)  NOT NULL,
  `created_at` TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_price`),
  CONSTRAINT `fk_prices_product`
    FOREIGN KEY (`id_product`) REFERENCES `products` (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 3: Add FK on products.price_id now that prices exists
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_current_price`
    FOREIGN KEY (`price_id`) REFERENCES `prices` (`id_price`);
```

### Invariante de Current_Price

`products.price_id` siempre apunta al `id_price` más reciente de la tabla `prices` para ese producto. Esta invariante se mantiene en dos operaciones:

1. **`createProduct`**: INSERT products → INSERT prices → UPDATE products.price_id
2. **`updatePrice`**: INSERT prices → UPDATE products.price_id

Ambas operaciones son atómicas desde la perspectiva del modelo (se ejecutan en secuencia dentro del mismo método).

### Consulta principal de listado

```sql
SELECT p.id_product, p.name, p.description, p.status, p.created_at,
       pr.price
FROM   products p
JOIN   prices   pr ON p.price_id = pr.id_price
WHERE  p.status = 1
ORDER  BY p.created_at DESC
```

### Consulta de historial de precios

```sql
SELECT id_price, price, created_at
FROM   prices
WHERE  id_product = ?
ORDER  BY created_at DESC
```

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

Antes de listar las propiedades finales, se identificaron las siguientes redundancias:

- **2.3 y 3.2** (nombre vacío en create y en edit) son el mismo error de validación → se combinan en **Property 4: Empty name is rejected**.
- **2.4 y 4.3** (precio inválido en create y en update_price) son el mismo error de validación → se combinan en **Property 5: Invalid price is rejected**.
- **2.2 y 4.2** (price_id apunta al precio más reciente tras create y tras update) son la misma invariante → se combinan en **Property 3: price_id invariant**.
- **3.3 y 5.4** (ID inexistente/inactivo en update y en delete) son el mismo error de validación → se combinan en **Property 6: Operations on inactive/non-existent products fail**.
- **6.1 y 1.2** (estructura del array devuelto) se combinan en **Property 2: Product data structure**.

---

### Property 1: getAllProducts returns only active products sorted descending

*For any* set of products in the database with mixed status values, `getAllProducts()` SHALL return only records with `status = 1`, ordered by `created_at` descending.

**Validates: Requirements 1.1, 1.4**

---

### Property 2: Product data structure is complete

*For any* active product returned by `getAllProducts()` or `getProductById()`, the result SHALL contain the fields `id_product`, `name`, `description`, `status`, `created_at`, and the `price` value of the Current_Price.

**Validates: Requirements 1.2, 6.1**

---

### Property 3: price_id always references the Current_Price

*For any* valid product creation or price update operation, after the operation completes, `products.price_id` SHALL equal the `id_price` of the most recently inserted record in `prices` for that product.

**Validates: Requirements 2.2, 4.1, 4.2**

---

### Property 4: Empty name is rejected

*For any* string that is empty or composed entirely of whitespace, passing it as the `name` argument to `createProduct()` or `updateProduct()` SHALL return `false` and populate `$errors` with at least one message.

**Validates: Requirements 2.3, 3.2**

---

### Property 5: Invalid price is rejected

*For any* value that is not a numeric value strictly greater than 0 (including zero, negative numbers, and non-numeric strings), passing it as the `price` argument to `createProduct()` or `updatePrice()` SHALL return `false` and populate `$errors` with at least one message.

**Validates: Requirements 2.4, 4.3**

---

### Property 6: Operations on inactive or non-existent products fail

*For any* `id_product` that either does not exist in the database or corresponds to a product with `status = 0`, calling `updateProduct()`, `updatePrice()`, or `deleteProduct()` with that ID SHALL return `false` and populate `$errors` with at least one message.

**Validates: Requirements 3.3, 5.4, 6.2, 6.3**

---

### Property 7: Soft delete preserves all data

*For any* active product with N associated price records, after calling `deleteProduct()` successfully, the product record SHALL still exist in the database with `status = 0`, and the count of associated price records SHALL remain N.

**Validates: Requirements 5.1, 5.2**

---

### Property 8: Price history is complete and ordered

*For any* product that has had M price updates applied (including the initial creation price), `getPriceHistory()` SHALL return exactly M records ordered by `created_at` descending, and the count SHALL increase by exactly 1 after each call to `updatePrice()`.

**Validates: Requirements 4.4, 7.1, 7.2**

---

### Property 9: getProductById returns null for soft-deleted products

*For any* product that has been soft-deleted (status = 0), `getProductById()` called with that product's `id_product` SHALL return `null`.

**Validates: Requirements 6.3**

---

## Error Handling

### Validaciones en Product_Model

| Condición | Mensaje de error | Método(s) |
|---|---|---|
| `name` vacío o solo espacios | "El nombre del producto es requerido." | `createProduct`, `updateProduct` |
| `price` no numérico o ≤ 0 | "El precio debe ser un valor numérico mayor a 0." | `createProduct`, `updatePrice` |
| `id_product` no existe o `status = 0` | "El producto no existe o está inactivo." | `updateProduct`, `updatePrice`, `deleteProduct` |
| Error de BD en INSERT/UPDATE | Mensaje del driver mysqli | Todos los métodos de escritura |

### Manejo en Product_Controller

El controlador sigue el mismo patrón que `controller/user/index.php`:

- **Éxito**: `header("location: " . CONTROLLER_URL . "product/?success=<clave>"); exit();`
- **Error de modelo**: re-renderiza la vista de formulario; los errores se muestran desde `$product->errors`.
- **Fallo en delete**: `header("location: " . CONTROLLER_URL . "product/?error=delete_failed"); exit();`

### Claves de mensajes flash

| Clave `success` | Significado |
|---|---|
| `created` | Producto creado |
| `updated` | Producto editado |
| `price_updated` | Precio actualizado |
| `deleted` | Producto eliminado (soft) |

| Clave `error` | Significado |
|---|---|
| `delete_failed` | Fallo en soft delete |

### Seguridad

- Todos los valores de usuario se escapan con `$this->db_connection->real_escape_string()` antes de interpolarse en SQL, siguiendo el patrón existente en `User.php`.
- Las vistas usan `htmlspecialchars()` en todos los valores de salida.
- El acceso al módulo requiere sesión activa con rol `Administrator`; de lo contrario se redirige antes de cualquier operación.

---

## Testing Strategy

### Enfoque dual

El módulo se prueba con dos tipos de tests complementarios:

1. **Tests de ejemplo** (`tests/ProductTest.php`): verifican comportamientos concretos, casos de borde y flujos de integración con la base de datos real.
2. **Tests de propiedades**: verifican invariantes universales sobre rangos de inputs generados aleatoriamente.

### Tests de ejemplo — `tests/ProductTest.php`

Sigue exactamente la misma estructura que `tests/UserTest.php` (clase con métodos `testXxx`, salida por consola con emojis, limpieza en `__destruct`).

Métodos requeridos:

| Método | Qué verifica |
|---|---|
| `testGetAllProducts()` | `getAllProducts()` retorna array; estructura de campos |
| `testCreateProduct()` | Creación válida; registro en `prices`; `price_id` actualizado; rechazo de nombre vacío; rechazo de precio inválido |
| `testGetProductById()` | Retorna datos correctos para ID válido; retorna `null` para ID inexistente; retorna `null` para producto inactivo |
| `testUpdateProduct()` | Actualiza `name` y `description`; falla con nombre vacío; falla con ID inactivo |
| `testUpdatePrice()` | Inserta nuevo registro en `prices`; actualiza `price_id`; preserva historial; falla con precio inválido |
| `testSoftDelete()` | `status = 0` tras delete; registro y precios preservados en BD |
| `testGetPriceHistory()` | Retorna todos los registros en orden descendente; retorna array vacío si no hay precios |

### Tests de propiedades

Se utiliza **[eris](https://github.com/giorgiosironi/eris)** (librería PBT para PHP) o, como alternativa más ligera, **[QuickCheck for PHP](https://github.com/steos/php-quickcheck)**. Cada test se configura con mínimo 100 iteraciones.

Cada test de propiedad referencia su propiedad del diseño con un comentario:

```php
// Feature: product-crud, Property 3: price_id always references the Current_Price
```

| Propiedad | Test de propiedad |
|---|---|
| Property 1 | Genera N productos con `created_at` aleatorios y `status` mixto; verifica que `getAllProducts()` retorna solo activos en orden descendente |
| Property 2 | Para cualquier producto creado, verifica que `getAllProducts()` y `getProductById()` retornan todos los campos requeridos |
| Property 3 | Para cualquier (name, price) válido, tras `createProduct()` y tras `updatePrice()`, verifica que `price_id` == `id_price` del último INSERT en `prices` |
| Property 4 | Genera strings vacíos y de solo espacios; verifica que `createProduct()` y `updateProduct()` retornan `false` con `$errors` no vacío |
| Property 5 | Genera valores inválidos (0, negativos, strings); verifica que `createProduct()` y `updatePrice()` retornan `false` con `$errors` no vacío |
| Property 6 | Genera IDs inexistentes o de productos inactivos; verifica que `updateProduct()`, `updatePrice()`, `deleteProduct()` retornan `false` |
| Property 7 | Para cualquier producto con N precios, tras `deleteProduct()`, verifica `status = 0` y count de precios = N |
| Property 8 | Para cualquier producto con M precios insertados, verifica que `getPriceHistory()` retorna M registros en orden descendente |
| Property 9 | Para cualquier producto soft-deleted, verifica que `getProductById()` retorna `null` |

### Tests de integración / smoke

- Verificar que el archivo de migración existe en `script_db/` con el nombre correcto.
- Verificar que el script contiene `CREATE TABLE IF NOT EXISTS products`, `CREATE TABLE IF NOT EXISTS prices` y el `ALTER TABLE` para la FK circular.
- Verificar que el controlador redirige a login cuando no hay sesión activa.
- Verificar que el controlador redirige a `BASE_URL` cuando el rol no es `Administrator`.
