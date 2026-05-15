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

    public function getAllProducts()
    {
        $sql = "SELECT p.id_product, p.name, p.description, pr.price, p.status, p.created_at, p.updated_at
                FROM products p 
                LEFT JOIN prices pr ON p.price_id = pr.id_price 
                WHERE p.deleted_at IS NULL 
                ORDER BY p.created_at DESC";
        
        $result = $this->db_connection->query($sql);
        $products = array();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        
        return $products;
    }

    public function getProductById($id_product)
    {
        $id_product = $this->db_connection->real_escape_string($id_product);
        $sql = "SELECT p.id_product, p.name, p.description, p.price_id, pr.price, p.status, p.created_at, p.updated_at
                FROM products p 
                LEFT JOIN prices pr ON p.price_id = pr.id_price 
                WHERE p.id_product = '$id_product' AND p.deleted_at IS NULL";
        
        $result = $this->db_connection->query($sql);
        
        if ($result && $result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function getAllPrices()
    {
        $sql = "SELECT id_price, price, created_at FROM prices ORDER BY created_at DESC";
        $result = $this->db_connection->query($sql);
        $prices = array();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $prices[] = $row;
            }
        }
        
        return $prices;
    }

    public function createProduct($name, $description, $price_id)
    {
        // Validations
        if (empty($name)) {
            $this->errors[] = "Product name is required.";
            return false;
        }
        
        if (empty($price_id)) {
            $this->errors[] = "Price is required.";
            return false;
        }
        
        // Verify if product already exists
        $name = $this->db_connection->real_escape_string($name);
        $description = $this->db_connection->real_escape_string($description);
        $price_id = $this->db_connection->real_escape_string($price_id);
        
        $check_sql = "SELECT id_product FROM products WHERE name = '$name' AND deleted_at IS NULL";
        $check_result = $this->db_connection->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            $this->errors[] = "Product with this name already exists.";
            return false;
        }
        
        // Insert product
        $sql = "INSERT INTO products (name, description, price_id, status, created_at) 
                VALUES ('$name', '$description', '$price_id', 1, NOW())";
        
        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Product created successfully.";
            return true;
        } else {
            $this->errors[] = "Error creating product: " . $this->db_connection->error;
            return false;
        }
    }

    public function createPrice($price)
    {
        if (empty($price) || $price < 0) {
            $this->errors[] = "Valid price is required.";
            return false;
        }
        
        $price = $this->db_connection->real_escape_string($price);
        
        $sql = "INSERT INTO prices (price, created_at) VALUES ('$price', NOW())";
        
        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Price created successfully.";
            return $this->db_connection->insert_id;
        } else {
            $this->errors[] = "Error creating price: " . $this->db_connection->error;
            return false;
        }
    }

    public function updateProduct($id_product, $name, $description, $price_id, $status)
    {
        // Validations
        if (empty($name)) {
            $this->errors[] = "Product name is required.";
            return false;
        }
        
        if (empty($price_id)) {
            $this->errors[] = "Price is required.";
            return false;
        }
        
        // Escape values
        $id_product = $this->db_connection->real_escape_string($id_product);
        $name = $this->db_connection->real_escape_string($name);
        $description = $this->db_connection->real_escape_string($description);
        $price_id = $this->db_connection->real_escape_string($price_id);
        $status = $this->db_connection->real_escape_string($status);
        
        // Check if product exists and is not deleted
        $check_sql = "SELECT id_product FROM products 
                     WHERE id_product = '$id_product' AND deleted_at IS NULL";
        $check_result = $this->db_connection->query($check_sql);
        
        if (!$check_result || $check_result->num_rows == 0) {
            $this->errors[] = "Product not found.";
            return false;
        }
        
        // Check if name already exists (excluding current product)
        $name_check_sql = "SELECT id_product FROM products 
                           WHERE name = '$name' AND id_product != '$id_product' AND deleted_at IS NULL";
        $name_check_result = $this->db_connection->query($name_check_sql);
        
        if ($name_check_result && $name_check_result->num_rows > 0) {
            $this->errors[] = "Product with this name already exists.";
            return false;
        }
        
        // Update product
        $sql = "UPDATE products SET 
                    name = '$name', 
                    description = '$description', 
                    price_id = '$price_id', 
                    status = '$status',
                    updated_at = NOW()
                 WHERE id_product = '$id_product'";
        
        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Product updated successfully.";
            return true;
        } else {
            $this->errors[] = "Error updating product: " . $this->db_connection->error;
            return false;
        }
    }

    public function deleteProduct($id_product)
    {
        $id_product = $this->db_connection->real_escape_string($id_product);
        
        // Check if product exists
        $check_sql = "SELECT id_product FROM products WHERE id_product = '$id_product' AND deleted_at IS NULL";
        $check_result = $this->db_connection->query($check_sql);
        
        if (!$check_result || $check_result->num_rows == 0) {
            $this->errors[] = "Product not found.";
            return false;
        }
        
        // Logical delete - update deleted_at timestamp
        $sql = "UPDATE products SET deleted_at = NOW() WHERE id_product = '$id_product'";
        
        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Product deleted successfully.";
            return true;
        } else {
            $this->errors[] = "Error deleting product: " . $this->db_connection->error;
            return false;
        }
    }

    public function toggleProductStatus($id_product)
    {
        $id_product = $this->db_connection->real_escape_string($id_product);
        
        // Check if product exists
        $check_sql = "SELECT id_product, status FROM products WHERE id_product = '$id_product' AND deleted_at IS NULL";
        $check_result = $this->db_connection->query($check_sql);
        
        if (!$check_result || $check_result->num_rows == 0) {
            $this->errors[] = "Product not found.";
            return false;
        }
        
        $current_status = $check_result->fetch_assoc()['status'];
        $new_status = $current_status == 1 ? 0 : 1;
        
        $sql = "UPDATE products SET status = '$new_status', updated_at = NOW() WHERE id_product = '$id_product'";
        
        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Product status updated successfully.";
            return true;
        } else {
            $this->errors[] = "Error updating product status: " . $this->db_connection->error;
            return false;
        }
    }

    public function getProductPriceHistory($product_id)
    {
        $product_id = $this->db_connection->real_escape_string($product_id);
        
        $sql = "SELECT p.name, pr.price, pr.created_at as price_date
                FROM products p
                LEFT JOIN prices pr ON p.price_id = pr.id_price
                WHERE p.id_product = '$product_id'
                ORDER BY pr.created_at DESC";
        
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
