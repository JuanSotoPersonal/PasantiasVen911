<?php

namespace App\Models;

use App\Config\Database;
use PDO;

require_once 'app/Config/Database.php';

class UsuarioModel {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    //--------------------------------------------------------------------
    // Retorna todos los usuarios con su nombre de rol.
    //--------------------------------------------------------------------
    public function getAll(): array {
        $query = "SELECT u.id, u.usuario, u.nombre_completo, u.cedula,
                         u.codigo_operador, u.estado, u.rol_id, r.nombre AS nombre_rol
                  FROM {$this->table_name} u
                  INNER JOIN roles r ON u.rol_id = r.id
                  ORDER BY u.id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //--------------------------------------------------------------------
    // Retorna usuarios filtrados por rol_id (para DataTables por rol)
    //--------------------------------------------------------------------
    public function getByRol(int $rolId): array {
        $query = "SELECT u.id, u.usuario, u.nombre_completo, u.cedula,
                         u.codigo_operador, u.estado, u.rol_id, r.nombre AS nombre_rol
                  FROM {$this->table_name} u
                  INNER JOIN roles r ON u.rol_id = r.id
                  WHERE u.rol_id = :rol_id
                  ORDER BY u.id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rol_id', $rolId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //--------------------------------------------------------------------
    // Retorna un usuario por ID.
    //--------------------------------------------------------------------
    public function getById(int $id): array|false {
        $query = "SELECT u.*, r.nombre AS nombre_rol
                  FROM {$this->table_name} u
                  INNER JOIN roles r ON u.rol_id = r.id
                  WHERE u.id = :id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //--------------------------------------------------------------------
    // Verifica si un nombre de usuario ya existe (excluyendo un ID específico).
    //--------------------------------------------------------------------
    public function usuarioExists(string $usuario, int $excludeId = 0): bool {
        $query = "SELECT COUNT(*) FROM {$this->table_name}
                  WHERE usuario = :usuario AND id != :exclude_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    //--------------------------------------------------------------------
    // Verifica si un código de operador ya existe (excluyendo un ID específico).
    //--------------------------------------------------------------------
    public function codigoExists(string $codigo, int $excludeId = 0): bool {
        $query = "SELECT COUNT(*) FROM {$this->table_name}
                  WHERE codigo_operador = :codigo AND id != :exclude_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
        $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    //--------------------------------------------------------------------
    // Verifica si una cédula ya existe (excluyendo un ID específico).
    //--------------------------------------------------------------------
    public function cedulaExists(string $cedula, int $excludeId = 0): bool {
        $query = "SELECT COUNT(*) FROM {$this->table_name}
                  WHERE cedula = :cedula AND id != :exclude_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula',     $cedula,    PDO::PARAM_STR);
        $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    //--------------------------------------------------------------------
    // Crea un nuevo usuario. La contraseña ya debe venir hasheada. 
    //--------------------------------------------------------------------
    public function create(array $data): bool {
        $query = "INSERT INTO {$this->table_name}
                    (usuario, password, nombre_completo, cedula, rol_id, codigo_operador, estado)
                  VALUES
                    (:usuario, :password, :nombre_completo, :cedula, :rol_id, :codigo_operador, :estado)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':usuario',         $data['usuario'],PDO::PARAM_STR);
        $stmt->bindValue(':password',        $data['password'],PDO::PARAM_STR);
        $stmt->bindValue(':nombre_completo', $data['nombre_completo'],PDO::PARAM_STR);
        $stmt->bindValue(':cedula',          $data['cedula'],$data['cedula'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':rol_id',          $data['rol_id'],PDO::PARAM_INT);
        $stmt->bindValue(':codigo_operador', $data['codigo_operador'], $data['codigo_operador'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':estado',          $data['estado'],PDO::PARAM_STR);
        return $stmt->execute();
    }

    //--------------------------------------------------------------------
    // Actualiza nombre_completo, cedula, usuario, rol y codigo_operador.
    //--------------------------------------------------------------------
    public function updateInfo(int $id, array $data): bool {
        $query = "UPDATE {$this->table_name}
                  SET nombre_completo = :nombre_completo,
                      cedula          = :cedula,
                      usuario         = :usuario,
                      rol_id          = :rol_id,
                      codigo_operador = :codigo_operador
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nombre_completo', $data['nombre_completo'],PDO::PARAM_STR);
        $stmt->bindValue(':cedula',          $data['cedula'],$data['cedula'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':usuario',         $data['usuario'],PDO::PARAM_STR);
        $stmt->bindValue(':rol_id',          $data['rol_id'],PDO::PARAM_INT);
        $stmt->bindValue(':codigo_operador', $data['codigo_operador'], $data['codigo_operador'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':id',              $id,PDO::PARAM_INT);
        return $stmt->execute();
    }

    //--------------------------------------------------------------------
    // Actualiza únicamente la contraseña (ya hasheada).
    //--------------------------------------------------------------------
    public function updatePassword(int $id, string $hashedPassword): bool {
        $query = "UPDATE {$this->table_name} SET password = :password WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':id',       $id,             PDO::PARAM_INT);
        return $stmt->execute();
    }

    //--------------------------------------------------------------------
    // Alterna el estado entre activo/inactivo.
    //--------------------------------------------------------------------
    public function toggleEstado(int $id): array|false {
        // Primero obtenemos el estado actual
        $query = "SELECT estado FROM {$this->table_name} WHERE id = :id LIMIT 1";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;

        $nuevoEstado = ($row['estado'] === 'activo') ? 'inactivo' : 'activo';

        $update = "UPDATE {$this->table_name} SET estado = :estado WHERE id = :id";
        $stmt2  = $this->conn->prepare($update);
        $stmt2->bindParam(':estado', $nuevoEstado, PDO::PARAM_STR);
        $stmt2->bindParam(':id',     $id,          PDO::PARAM_INT);
        $stmt2->execute();

        return ['nuevo_estado' => $nuevoEstado];
    }

    //--------------------------------------------------------------------
    // Retorna todos los roles disponibles.
    //--------------------------------------------------------------------
    public function getRoles(): array {
        $stmt = $this->conn->prepare("SELECT id, nombre FROM roles ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
