<?php

namespace App\Models;

use App\Config\Database;
use PDO;

require_once 'app/Config/Database.php';

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Busca un usuario por su nombre de usuario (email o string de acceso).
     * Retorna el registro con un INNER JOIN a `roles` para traer también 
     * el nombre del rol.
     *
     * @param string $username
     * @return array|false
     */
    public function getUsuarioByUsername($username) {
        $query = "SELECT u.*, r.nombre as nombre_rol 
                  FROM " . $this->table_name . " u
                  INNER JOIN roles r ON u.rol_id = r.id
                  WHERE u.usuario = :usuario AND u.estado = 'activo'
                  LIMIT 0,1";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario', $username, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
