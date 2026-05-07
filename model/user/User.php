<?php
require_once __DIR__ . '/../../config/database.php';

class User
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

    public function getAllUsers()
    {
        $sql = "SELECT u.id_user, u.username, u.email, u.status, u.created_at, r.name as role_name
                FROM users u 
                LEFT JOIN roles r ON u.id_rol = r.id_rol 
                ORDER BY u.created_at DESC";
        
        $result = $this->db_connection->query($sql);
        $users = array();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        return $users;
    }

    public function getUserById($id_user)
    {
        $id_user = $this->db_connection->real_escape_string($id_user);
        $sql = "SELECT u.id_user, u.username, u.email, u.status, u.id_rol, r.name as role_name
                FROM users u 
                LEFT JOIN roles r ON u.id_rol = r.id_rol 
                WHERE u.id_user = '$id_user'";
        
        $result = $this->db_connection->query($sql);
        
        if ($result && $result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function getAllRoles()
    {
        $sql = "SELECT id_rol, name FROM roles WHERE status = 1 ORDER BY name";
        $result = $this->db_connection->query($sql);
        $roles = array();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $roles[] = $row;
            }
        }
        
        return $roles;
    }

    public function createUser($username, $email, $password, $id_rol)
    {
        // Validaciones
        if (empty($username)) {
            $this->errors[] = "El nombre de usuario es requerido.";
            return false;
        }
        
        if (empty($email)) {
            $this->errors[] = "El email es requerido.";
            return false;
        }
        
        if (empty($password)) {
            $this->errors[] = "La contraseña es requerida.";
            return false;
        }
        
        if (empty($id_rol)) {
            $this->errors[] = "El rol es requerido.";
            return false;
        }
        
        // Verificar si el usuario ya existe
        $username = $this->db_connection->real_escape_string($username);
        $email = $this->db_connection->real_escape_string($email);
        $id_rol = $this->db_connection->real_escape_string($id_rol);
        
        $check_sql = "SELECT id_user FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = $this->db_connection->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            $this->errors[] = "El nombre de usuario o email ya existe.";
            return false;
        }
        
        // Hashear contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar usuario
        $sql = "INSERT INTO users (username, email, password, id_rol, status, created_at) 
                VALUES ('$username', '$email', '$hashed_password', '$id_rol', 1, NOW())";
        
        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Usuario creado exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al crear el usuario: " . $this->db_connection->error;
            return false;
        }
    }

    public function updateUser($id_user, $username, $email, $id_rol, $status)
    {
        // Validaciones
        if (empty($username)) {
            $this->errors[] = "El nombre de usuario es requerido.";
            return false;
        }
        
        if (empty($email)) {
            $this->errors[] = "El email es requerido.";
            return false;
        }
        
        if (empty($id_rol)) {
            $this->errors[] = "El rol es requerido.";
            return false;
        }
        
        // Escapar valores
        $id_user = $this->db_connection->real_escape_string($id_user);
        $username = $this->db_connection->real_escape_string($username);
        $email = $this->db_connection->real_escape_string($email);
        $id_rol = $this->db_connection->real_escape_string($id_rol);
        $status = $this->db_connection->real_escape_string($status);
        
        // Verificar si el usuario/email ya existe (excluyendo el usuario actual)
        $check_sql = "SELECT id_user FROM users 
                     WHERE (username = '$username' OR email = '$email') AND id_user != '$id_user'";
        $check_result = $this->db_connection->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            $this->errors[] = "El nombre de usuario o email ya existe.";
            return false;
        }
        
        // Actualizar usuario
        $sql = "UPDATE users 
                SET username = '$username', email = '$email', id_rol = '$id_rol', status = '$status' 
                WHERE id_user = '$id_user'";
        
        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Usuario actualizado exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al actualizar el usuario: " . $this->db_connection->error;
            return false;
        }
    }

    public function updatePassword($id_user, $new_password)
    {
        if (empty($new_password)) {
            $this->errors[] = "La contraseña es requerida.";
            return false;
        }
        
        if (strlen($new_password) < 6) {
            $this->errors[] = "La contraseña debe tener al menos 6 caracteres.";
            return false;
        }
        
        $id_user = $this->db_connection->real_escape_string($id_user);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password = '$hashed_password' WHERE id_user = '$id_user'";
        
        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Contraseña actualizada exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al actualizar la contraseña: " . $this->db_connection->error;
            return false;
        }
    }

    public function deleteUser($id_user)
    {
        $id_user = $this->db_connection->real_escape_string($id_user);
        
        // No permitir eliminar al usuario admin (id = 1)
        if ($id_user == 1) {
            $this->errors[] = "No se puede eliminar al usuario administrador.";
            return false;
        }
        
        $sql = "DELETE FROM users WHERE id_user = '$id_user'";
        
        if ($this->db_connection->query($sql)) {
            $this->messages[] = "Usuario eliminado exitosamente.";
            return true;
        } else {
            $this->errors[] = "Error al eliminar el usuario: " . $this->db_connection->error;
            return false;
        }
    }

    public function toggleUserStatus($id_user)
    {
        $id_user = $this->db_connection->real_escape_string($id_user);
        
        // No permitir desactivar al usuario admin (id = 1)
        if ($id_user == 1) {
            $this->errors[] = "No se puede desactivar al usuario administrador.";
            return false;
        }
        
        // Obtener estado actual
        $sql = "SELECT status FROM users WHERE id_user = '$id_user'";
        $result = $this->db_connection->query($sql);
        
        if ($result && $result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $new_status = $row['status'] == 1 ? 0 : 1;
            
            $update_sql = "UPDATE users SET status = '$new_status' WHERE id_user = '$id_user'";
            
            if ($this->db_connection->query($update_sql)) {
                $status_text = $new_status == 1 ? "activado" : "desactivado";
                $this->messages[] = "Usuario $status_text exitosamente.";
                return true;
            }
        }
        
        $this->errors[] = "Error al cambiar el estado del usuario.";
        return false;
    }

    public function __destruct()
    {
        if ($this->db_connection) {
            $this->db_connection->close();
        }
    }
}
?>
