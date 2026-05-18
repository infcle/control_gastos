<?php
require_once __DIR__ . '/../../config/database.php';

class Login
{
    private $db_connection = null;
    public $errors = array();
    public $messages = array();

    public function __construct()
    {
        session_start();
        if (isset($_GET["logout"])) {
            $this->exit();
        } elseif (isset($_POST["btnSingIn"])) {
            $this->verifyUser();
        }
    }

    private function verifyUser()
    {
        if (empty($_POST['user_name'])) {
            $this->errors[] = "Debe escribir un nombre de usuario o email.";
        } elseif (empty($_POST['password'])) {
            $this->errors[] = "Debe escribir una contraseña.";
        } elseif (!empty($_POST['user_name']) && !empty($_POST['password'])) {
            // conexion a la base de datos
            $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (!$this->db_connection->set_charset("utf8")) {
                $this->errors[] = $this->db_connection->error;
            }
            if (!$this->db_connection->connect_errno) {
                $user_name = $this->db_connection->real_escape_string($_POST['user_name']);
                $sql = "SELECT u.id_user, u.username, u.password, r.name as role_name, r.id_rol
                            FROM users u, roles r
                            WHERE u.username = '{$user_name}' AND u.status=1 AND u.id_rol = r.id_rol";
                
                $result_of_login_check = $this->db_connection->query($sql);
                
                if ($result_of_login_check->num_rows == 1) {
                    $result_row = $result_of_login_check->fetch_object();
                    
                    if (password_verify($_POST['password'], $result_row->password)) {
                        $_SESSION['id_user'] = $result_row->id_user;
                        $_SESSION['user_name'] = $result_row->username;
                        $_SESSION['rol'] = $result_row->role_name;
                        $_SESSION['id_rol'] = $result_row->id_rol;
                        $_SESSION['user_login_status'] = 1;
                        // Obtener foto de perfil
                        $pic_sql = "SELECT profile_picture FROM users WHERE id_user = '{$result_row->id_user}'";
                        $pic_result = $this->db_connection->query($pic_sql);
                        if ($pic_result && $pic_result->num_rows == 1) {
                            $pic_row = $pic_result->fetch_object();
                            $_SESSION['profile_picture'] = $pic_row->profile_picture;
                        }
                    } else {
                        $this->errors[] = "Usuario y/o contraseña no coinciden.";
                    }
                } else {
                    $this->errors[] = "Usuario y/o contraseña no coinciden.";
                }
            } else {
                $this->errors[] = "Problema de conexión de base de datos.";
            }
        }
        // Cerrar conexión
        if ($this->db_connection) {
            $this->db_connection->close();
        }
    }

    public function exit()
    {
        $_SESSION = array();
        session_destroy();
        $this->messages[] = "Has sido desconectado.";
        header("location: " . BASE_URL);
    }

    public function isConected()
    {
        if (isset($_SESSION['user_login_status']) and $_SESSION['user_login_status'] == 1) {
            return true;
        }
        return false;
    }
}
