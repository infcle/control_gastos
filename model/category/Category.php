<?php
require_once __DIR__ . '/../../config/database.php';

class Category
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

    public function getAll()
    {
        $sql = "SELECT id_category, name, description, created_at
                FROM categories
                WHERE deleted_at IS NULL
                ORDER BY name ASC";

        $result = $this->db_connection->query($sql);
        $categories = array();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }

        return $categories;
    }

    public function getById($id)
    {
        $id = $this->db_connection->real_escape_string($id);
        $sql = "SELECT id_category, name, description, created_at
                FROM categories
                WHERE id_category = '$id' AND deleted_at IS NULL";

        $result = $this->db_connection->query($sql);

        if ($result && $result->num_rows == 1) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function create($name, $description)
    {
        // Validaciones
        if (empty($name)) {
            $this->errors[] = "El nombre de la categoría es requerido.";
            return false;
        }

        $name = $this->db_connection->real_escape_string($name);
        $description = $this->db_connection->real_escape_string($description);

        // Verificar si ya existe una categoría con ese nombre
        $check_sql = "SELECT id_category FROM categories WHERE name = '$name' AND deleted_at IS NULL";
        $check_result = $this->db_connection->query($check_sql);

        if ($check_result && $check_result->num_rows > 0) {
            $this->errors[] = "El nombre de la categoría ya existe.";
            return false;
        }

        // Insertar categoría
        $sql = "INSERT INTO categories (name, description, created_at)
                VALUES ('$name', '$description', NOW())";

        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Categoría creada exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al crear la categoría: " . $this->db_connection->error;
            return false;
        }
    }

    public function update($id, $name, $description)
    {
        // Validaciones
        if (empty($name)) {
            $this->errors[] = "El nombre de la categoría es requerido.";
            return false;
        }

        $id = $this->db_connection->real_escape_string($id);
        $name = $this->db_connection->real_escape_string($name);
        $description = $this->db_connection->real_escape_string($description);

        // Verificar si la categoría existe
        $check_sql = "SELECT id_category FROM categories WHERE id_category = '$id' AND deleted_at IS NULL";
        $check_result = $this->db_connection->query($check_sql);

        if (!$check_result || $check_result->num_rows == 0) {
            $this->errors[] = "Categoría no encontrada.";
            return false;
        }

        // Verificar si el nombre ya existe (excluyendo la categoría actual)
        $name_check_sql = "SELECT id_category FROM categories
                          WHERE name = '$name' AND id_category != '$id' AND deleted_at IS NULL";
        $name_check_result = $this->db_connection->query($name_check_sql);

        if ($name_check_result && $name_check_result->num_rows > 0) {
            $this->errors[] = "El nombre de la categoría ya existe.";
            return false;
        }

        // Actualizar categoría
        $sql = "UPDATE categories SET name = '$name', description = '$description' WHERE id_category = '$id'";

        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Categoría actualizada exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al actualizar la categoría: " . $this->db_connection->error;
            return false;
        }
    }

    public function delete($id)
    {
        $id = $this->db_connection->real_escape_string($id);

        // Verificar si la categoría existe
        $check_sql = "SELECT id_category FROM categories WHERE id_category = '$id' AND deleted_at IS NULL";
        $check_result = $this->db_connection->query($check_sql);

        if (!$check_result || $check_result->num_rows == 0) {
            $this->errors[] = "Categoría no encontrada.";
            return false;
        }

        // FK guard: verificar si hay productos activos que referencien esta categoría
        $column_check = $this->db_connection->query("SHOW COLUMNS FROM products LIKE 'id_category'");
        if ($column_check && $column_check->num_rows > 0) {
            $fk_sql = "SELECT id_product FROM products WHERE id_category = '$id' AND deleted_at IS NULL";
            $fk_result = $this->db_connection->query($fk_sql);

            if ($fk_result && $fk_result->num_rows > 0) {
                $this->errors[] = "No se puede eliminar la categoría porque está siendo utilizada por productos activos.";
                return false;
            }
        }

        // Eliminación lógica
        $sql = "UPDATE categories SET deleted_at = NOW() WHERE id_category = '$id'";

        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Categoría eliminada exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al eliminar la categoría: " . $this->db_connection->error;
            return false;
        }
    }

    public function __destruct()
    {
        if ($this->db_connection) {
            $this->db_connection->close();
        }
    }
}
?>
