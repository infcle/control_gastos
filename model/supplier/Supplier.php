<?php
require_once __DIR__ . '/../../config/database.php';

class Supplier
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
        $sql = "SELECT id_supplier, name, location, created_at
                FROM suppliers
                WHERE deleted_at IS NULL
                ORDER BY name ASC";

        $result = $this->db_connection->query($sql);
        $suppliers = array();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $suppliers[] = $row;
            }
        }

        return $suppliers;
    }

    public function getById($id)
    {
        $id = $this->db_connection->real_escape_string($id);
        $sql = "SELECT id_supplier, name, location, created_at
                FROM suppliers
                WHERE id_supplier = '$id' AND deleted_at IS NULL";

        $result = $this->db_connection->query($sql);

        if ($result && $result->num_rows == 1) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function create($name, $location)
    {
        // Validaciones
        if (empty($name)) {
            $this->errors[] = "El nombre del proveedor es requerido.";
            return false;
        }

        $name = $this->db_connection->real_escape_string($name);
        $location = $this->db_connection->real_escape_string($location);

        // Verificar si ya existe un proveedor con ese nombre
        $check_sql = "SELECT id_supplier FROM suppliers WHERE name = '$name' AND deleted_at IS NULL";
        $check_result = $this->db_connection->query($check_sql);

        if ($check_result && $check_result->num_rows > 0) {
            $this->errors[] = "El nombre del proveedor ya existe.";
            return false;
        }

        // Insertar proveedor
        $sql = "INSERT INTO suppliers (name, location, created_at)
                VALUES ('$name', '$location', NOW())";

        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Proveedor creado exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al crear el proveedor: " . $this->db_connection->error;
            return false;
        }
    }

    public function update($id, $name, $location)
    {
        // Validaciones
        if (empty($name)) {
            $this->errors[] = "El nombre del proveedor es requerido.";
            return false;
        }

        $id = $this->db_connection->real_escape_string($id);
        $name = $this->db_connection->real_escape_string($name);
        $location = $this->db_connection->real_escape_string($location);

        // Verificar si el proveedor existe
        $check_sql = "SELECT id_supplier FROM suppliers WHERE id_supplier = '$id' AND deleted_at IS NULL";
        $check_result = $this->db_connection->query($check_sql);

        if (!$check_result || $check_result->num_rows == 0) {
            $this->errors[] = "Proveedor no encontrado.";
            return false;
        }

        // Verificar si el nombre ya existe (excluyendo el proveedor actual)
        $name_check_sql = "SELECT id_supplier FROM suppliers
                          WHERE name = '$name' AND id_supplier != '$id' AND deleted_at IS NULL";
        $name_check_result = $this->db_connection->query($name_check_sql);

        if ($name_check_result && $name_check_result->num_rows > 0) {
            $this->errors[] = "El nombre del proveedor ya existe.";
            return false;
        }

        // Actualizar proveedor
        $sql = "UPDATE suppliers SET name = '$name', location = '$location' WHERE id_supplier = '$id'";

        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Proveedor actualizado exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al actualizar el proveedor: " . $this->db_connection->error;
            return false;
        }
    }

    public function delete($id)
    {
        $id = $this->db_connection->real_escape_string($id);

        // Verificar si el proveedor existe
        $check_sql = "SELECT id_supplier FROM suppliers WHERE id_supplier = '$id' AND deleted_at IS NULL";
        $check_result = $this->db_connection->query($check_sql);

        if (!$check_result || $check_result->num_rows == 0) {
            $this->errors[] = "Proveedor no encontrado.";
            return false;
        }

        // Eliminación lógica
        $sql = "UPDATE suppliers SET deleted_at = NOW() WHERE id_supplier = '$id'";

        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Proveedor eliminado exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al eliminar el proveedor: " . $this->db_connection->error;
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
