<?php
require_once __DIR__ . '/../../config/database.php';

class Purchase
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
        $sql = "SELECT p.id_purchase, p.purchase_date, p.observation, p.created_at,
                       u.username AS user_name
                FROM purchases p
                LEFT JOIN users u ON p.id_user = u.id_user
                WHERE p.deleted_at IS NULL
                ORDER BY p.purchase_date DESC, p.created_at DESC";

        $result = $this->db_connection->query($sql);
        $purchases = array();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Count active details for each purchase
                $details_count = $this->countDetailsByPurchaseId($row['id_purchase']);
                $row['items_count'] = $details_count;
                $purchases[] = $row;
            }
        }

        return $purchases;
    }

    public function getById($id)
    {
        $id = $this->db_connection->real_escape_string($id);

        $sql = "SELECT p.id_purchase, p.purchase_date, p.observation, p.created_at,
                       u.username AS user_name
                FROM purchases p
                LEFT JOIN users u ON p.id_user = u.id_user
                WHERE p.id_purchase = '$id' AND p.deleted_at IS NULL";

        $result = $this->db_connection->query($sql);

        if ($result && $result->num_rows == 1) {
            $purchase = $result->fetch_assoc();
            $purchase['details'] = $this->getDetailsByPurchaseId($id);
            return $purchase;
        }

        return null;
    }

    public function getDetailsByPurchaseId($id_purchase)
    {
        $id_purchase = $this->db_connection->real_escape_string($id_purchase);

        $sql = "SELECT pd.id_purchase_detail, pd.id_product, pd.id_supplier,
                       pd.quantity, pd.unit_price, pd.observation,
                       pr.name AS product_name,
                       s.name AS supplier_name
                FROM purchase_details pd
                LEFT JOIN products pr ON pd.id_product = pr.id_product
                LEFT JOIN suppliers s ON pd.id_supplier = s.id_supplier
                WHERE pd.id_purchase = '$id_purchase' AND pd.deleted_at IS NULL
                ORDER BY pd.id_purchase_detail ASC";

        $result = $this->db_connection->query($sql);
        $details = array();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $details[] = $row;
            }
        }

        return $details;
    }

    private function countDetailsByPurchaseId($id_purchase)
    {
        $id_purchase = $this->db_connection->real_escape_string($id_purchase);

        $sql = "SELECT COUNT(*) AS total
                FROM purchase_details
                WHERE id_purchase = '$id_purchase' AND deleted_at IS NULL";

        $result = $this->db_connection->query($sql);

        if ($result && $row = $result->fetch_assoc()) {
            return $row['total'];
        }

        return 0;
    }

    public function create($purchase_date, $id_user, $observation, $details)
    {
        // Validaciones
        if (empty($purchase_date)) {
            $this->errors[] = "La fecha es requerida.";
            return false;
        }

        if (empty($id_user)) {
            $this->errors[] = "El usuario es requerido.";
            return false;
        }

        // Validar que la fecha no sea futura
        if ($purchase_date > date('Y-m-d')) {
            $this->errors[] = "La fecha de compra no puede ser futura.";
            return false;
        }

        if (empty($details) || !is_array($details)) {
            $this->errors[] = "Debe agregar al menos un detalle a la compra.";
            return false;
        }

        $purchase_date = $this->db_connection->real_escape_string($purchase_date);
        $id_user = $this->db_connection->real_escape_string($id_user);
        $observation = $this->db_connection->real_escape_string($observation);

        // Iniciar transacción
        $this->db_connection->begin_transaction();

        try {
            // Insertar cabecera de compra
            $sql = "INSERT INTO purchases (purchase_date, id_user, observation, created_at)
                    VALUES ('$purchase_date', '$id_user', '$observation', NOW())";

            if (!$this->db_connection->query($sql)) {
                throw new Exception("Error al crear la compra: " . $this->db_connection->error);
            }

            $id_purchase = $this->db_connection->insert_id;

            // Insertar detalles
            foreach ($details as $detail) {
                $id_product = $this->db_connection->real_escape_string($detail['id_product']);
                $id_supplier = $this->db_connection->real_escape_string($detail['id_supplier']);
                $quantity = $this->db_connection->real_escape_string($detail['quantity']);
                $unit_price = $this->db_connection->real_escape_string($detail['unit_price']);
                $detail_observation = isset($detail['observation']) ? $this->db_connection->real_escape_string($detail['observation']) : '';

                // Validar cantidad
                if ($quantity <= 0) {
                    throw new Exception("La cantidad debe ser mayor a cero.");
                }

                // Validar precio unitario
                if ($unit_price < 0) {
                    throw new Exception("El precio unitario no puede ser negativo.");
                }

                // Validar que el producto exista
                $check_product = "SELECT id_product FROM products WHERE id_product = '$id_product' AND deleted_at IS NULL";
                $check_product_result = $this->db_connection->query($check_product);
                if (!$check_product_result || $check_product_result->num_rows == 0) {
                    throw new Exception("Producto no encontrado (ID: $id_product).");
                }

                // Validar que el proveedor exista
                $check_supplier = "SELECT id_supplier FROM suppliers WHERE id_supplier = '$id_supplier' AND deleted_at IS NULL";
                $check_supplier_result = $this->db_connection->query($check_supplier);
                if (!$check_supplier_result || $check_supplier_result->num_rows == 0) {
                    throw new Exception("Proveedor no encontrado (ID: $id_supplier).");
                }

                $detail_sql = "INSERT INTO purchase_details (id_purchase, id_product, id_supplier, quantity, unit_price, observation, created_at)
                               VALUES ('$id_purchase', '$id_product', '$id_supplier', '$quantity', '$unit_price', '$detail_observation', NOW())";

                if (!$this->db_connection->query($detail_sql)) {
                    throw new Exception("Error al agregar detalle: " . $this->db_connection->error);
                }
            }

            $this->db_connection->commit();
            $this->messages[] = "Compra creada exitosamente.";
            return $id_purchase;

        } catch (Exception $e) {
            $this->db_connection->rollback();
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function delete($id)
    {
        $id = $this->db_connection->real_escape_string($id);

        // Verificar si la compra existe
        $check_sql = "SELECT id_purchase FROM purchases WHERE id_purchase = '$id' AND deleted_at IS NULL";
        $check_result = $this->db_connection->query($check_sql);

        if (!$check_result || $check_result->num_rows == 0) {
            $this->errors[] = "Compra no encontrada.";
            return false;
        }

        // Iniciar transacción para soft delete cascade
        $this->db_connection->begin_transaction();

        try {
            // Soft delete en detalles
            $details_sql = "UPDATE purchase_details SET deleted_at = NOW() WHERE id_purchase = '$id'";
            if (!$this->db_connection->query($details_sql)) {
                throw new Exception("Error al eliminar los detalles: " . $this->db_connection->error);
            }

            // Soft delete en cabecera
            $sql = "UPDATE purchases SET deleted_at = NOW() WHERE id_purchase = '$id'";
            if (!$this->db_connection->query($sql)) {
                throw new Exception("Error al eliminar la compra: " . $this->db_connection->error);
            }

            $this->db_connection->commit();
            $this->messages[] = "Compra eliminada exitosamente.";
            return true;

        } catch (Exception $e) {
            $this->db_connection->rollback();
            $this->errors[] = $e->getMessage();
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
