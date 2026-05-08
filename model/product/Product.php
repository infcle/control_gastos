<?php
require_once __DIR__ . '/../../config/database.php';

class Product
{
    private $db_connection = null;
    public $errors = array();
    public $messages = array();

    public function __construct()
    {
        $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$this->db_connection->set_charset("utf8")) {
            $this->errors[] = $this->db_connection->error;
        }
    }

    /**
     * Retorna todos los productos activos con su precio actual,
     * ordenados por fecha de creación descendente.
     */
    public function getAllProducts(): array
    {
        $sql = "SELECT p.id_product, p.name, p.description, p.status, p.created_at,
                       pr.price
                FROM   products p
                JOIN   prices   pr ON p.price_id = pr.id_price
                WHERE  p.status = 1
                ORDER  BY p.created_at DESC";

        $result = $this->db_connection->query($sql);
        $products = array();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }

        return $products;
    }

    /**
     * Retorna los datos completos de un producto activo por su ID,
     * o null si no existe o está inactivo.
     */
    public function getProductById(int $id_product): ?array
    {
        $id_product = $this->db_connection->real_escape_string($id_product);

        $sql = "SELECT p.id_product, p.name, p.description, p.status, p.created_at,
                       pr.price
                FROM   products p
                JOIN   prices   pr ON p.price_id = pr.id_price
                WHERE  p.id_product = '$id_product'
                  AND  p.status = 1";

        $result = $this->db_connection->query($sql);

        if ($result && $result->num_rows == 1) {
            return $result->fetch_assoc();
        }

        return null;
    }

    /**
     * Crea un nuevo producto con su precio inicial.
     * Secuencia: INSERT products → last_insert_id() → INSERT prices → last_insert_id() → UPDATE products SET price_id
     */
    public function createProduct(string $name, ?string $description, float $price): bool
    {
        // Validar nombre
        if (empty(trim($name))) {
            $this->errors[] = "El nombre del producto es requerido.";
            return false;
        }

        // Validar precio
        if (!is_numeric($price) || $price <= 0) {
            $this->errors[] = "El precio debe ser un valor numérico mayor a 0.";
            return false;
        }

        // Escapar valores
        $name        = $this->db_connection->real_escape_string(trim($name));
        $description = $description !== null
            ? $this->db_connection->real_escape_string($description)
            : null;
        $price       = $this->db_connection->real_escape_string($price);

        $desc_value = $description !== null ? "'$description'" : "NULL";

        // 1. Insertar producto
        $sql_product = "INSERT INTO products (name, description, status, created_at)
                        VALUES ('$name', $desc_value, 1, NOW())";

        if (!$this->db_connection->query($sql_product)) {
            $this->errors[] = "Error al crear el producto: " . $this->db_connection->error;
            return false;
        }

        $id_product = $this->db_connection->insert_id;

        // 2. Insertar precio
        $sql_price = "INSERT INTO prices (id_product, price, created_at)
                      VALUES ('$id_product', '$price', NOW())";

        if (!$this->db_connection->query($sql_price)) {
            $this->errors[] = "Error al registrar el precio: " . $this->db_connection->error;
            return false;
        }

        $id_price = $this->db_connection->insert_id;

        // 3. Actualizar price_id en el producto
        $sql_update = "UPDATE products SET price_id = '$id_price' WHERE id_product = '$id_product'";

        if (!$this->db_connection->query($sql_update)) {
            $this->errors[] = "Error al actualizar el precio del producto: " . $this->db_connection->error;
            return false;
        }

        $this->messages[] = "Producto creado exitosamente.";
        return true;
    }

    /**
     * Actualiza el nombre y descripción de un producto activo.
     */
    public function updateProduct(int $id_product, string $name, ?string $description): bool
    {
        // Validar nombre
        if (empty(trim($name))) {
            $this->errors[] = "El nombre del producto es requerido.";
            return false;
        }

        // Verificar que el producto existe y está activo
        $id_escaped = $this->db_connection->real_escape_string($id_product);
        $check_sql  = "SELECT id_product FROM products WHERE id_product = '$id_escaped' AND status = 1";
        $check_result = $this->db_connection->query($check_sql);

        if (!$check_result || $check_result->num_rows == 0) {
            $this->errors[] = "El producto no existe o está inactivo.";
            return false;
        }

        // Escapar valores
        $name        = $this->db_connection->real_escape_string(trim($name));
        $description = $description !== null
            ? $this->db_connection->real_escape_string($description)
            : null;

        $desc_value = $description !== null ? "'$description'" : "NULL";

        $sql = "UPDATE products
                SET    name = '$name', description = $desc_value
                WHERE  id_product = '$id_escaped'";

        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Producto actualizado exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al actualizar el producto: " . $this->db_connection->error;
            return false;
        }
    }

    /**
     * Actualiza el precio de un producto activo preservando el historial.
     * Secuencia: INSERT prices → last_insert_id() → UPDATE products SET price_id
     */
    public function updatePrice(int $id_product, float $price): bool
    {
        // Validar precio
        if (!is_numeric($price) || $price <= 0) {
            $this->errors[] = "El precio debe ser un valor numérico mayor a 0.";
            return false;
        }

        // Verificar que el producto existe y está activo
        $id_escaped = $this->db_connection->real_escape_string($id_product);
        $check_sql  = "SELECT id_product FROM products WHERE id_product = '$id_escaped' AND status = 1";
        $check_result = $this->db_connection->query($check_sql);

        if (!$check_result || $check_result->num_rows == 0) {
            $this->errors[] = "El producto no existe o está inactivo.";
            return false;
        }

        $price_escaped = $this->db_connection->real_escape_string($price);

        // 1. Insertar nuevo precio (preserva historial)
        $sql_price = "INSERT INTO prices (id_product, price, created_at)
                      VALUES ('$id_escaped', '$price_escaped', NOW())";

        if (!$this->db_connection->query($sql_price)) {
            $this->errors[] = "Error al registrar el nuevo precio: " . $this->db_connection->error;
            return false;
        }

        $id_price = $this->db_connection->insert_id;

        // 2. Actualizar price_id en el producto
        $sql_update = "UPDATE products SET price_id = '$id_price' WHERE id_product = '$id_escaped'";

        if (!$this->db_connection->query($sql_update)) {
            $this->errors[] = "Error al actualizar el precio del producto: " . $this->db_connection->error;
            return false;
        }

        $this->messages[] = "Precio actualizado exitosamente.";
        return true;
    }

    /**
     * Realiza un soft delete del producto (status = 0).
     * No elimina los registros de prices asociados.
     */
    public function deleteProduct(int $id_product): bool
    {
        // Verificar que el producto existe y está activo
        $id_escaped = $this->db_connection->real_escape_string($id_product);
        $check_sql  = "SELECT id_product FROM products WHERE id_product = '$id_escaped' AND status = 1";
        $check_result = $this->db_connection->query($check_sql);

        if (!$check_result || $check_result->num_rows == 0) {
            $this->errors[] = "El producto no existe o está inactivo.";
            return false;
        }

        $sql = "UPDATE products SET status = 0 WHERE id_product = '$id_escaped'";

        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Producto eliminado exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al eliminar el producto: " . $this->db_connection->error;
            return false;
        }
    }

    /**
     * Retorna el historial de precios de un producto ordenado por fecha descendente.
     */
    public function getPriceHistory(int $id_product): array
    {
        $id_escaped = $this->db_connection->real_escape_string($id_product);

        $sql = "SELECT id_price, price, created_at
                FROM   prices
                WHERE  id_product = '$id_escaped'
                ORDER  BY id_price DESC";

        $result = $this->db_connection->query($sql);
        $history = array();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $history[] = $row;
            }
        }

        return $history;
    }

    public function __destruct()
    {
        if ($this->db_connection) {
            $this->db_connection->close();
        }
    }
}
?>
