<?php

namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

require_once 'app/Config/Database.php';

class UsuarioModel {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en constructor: " . $e->getMessage());
            die("Error de conexión a la base de datos.");
        }
    }

    //--------------------------------------------------------------------
    // Retorna todos los usuarios con su nombre de rol, filtrados por estado.
    //--------------------------------------------------------------------
    public function getAll(string $estado = 'activo'): array {
        try {
            $query = "SELECT u.id, u.usuario, u.nombre_completo, u.cedula,
                             u.codigo_operador, u.estado, u.rol_id, r.nombre AS nombre_rol
                      FROM {$this->table_name} u
                      INNER JOIN roles r ON u.rol_id = r.id
                      WHERE u.estado = :estado
                      ORDER BY u.id ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en getAll: " . $e->getMessage());
            return [];
        }
    }

    //--------------------------------------------------------------------
    // Retorna usuarios filtrados por rol_id y estado (para DataTables por rol)
    //--------------------------------------------------------------------
    public function getByRol(int $rolId, string $estado = 'activo'): array {
        try {
            $query = "SELECT u.id, u.usuario, u.nombre_completo, u.cedula,
                             u.codigo_operador, u.estado, u.rol_id, r.nombre AS nombre_rol
                      FROM {$this->table_name} u
                      INNER JOIN roles r ON u.rol_id = r.id
                      WHERE u.rol_id = :rol_id AND u.estado = :estado
                      ORDER BY u.id ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':rol_id', $rolId, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en getByRol: " . $e->getMessage());
            return [];
        }
    }

    //--------------------------------------------------------------------
    // Retorna un usuario por ID.
    //--------------------------------------------------------------------
    public function getById(int $id): array|false {
        try {
            $query = "SELECT u.*, r.nombre AS nombre_rol
                      FROM {$this->table_name} u
                      LEFT JOIN roles r ON u.rol_id = r.id
                      WHERE u.id = :id
                      LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en getById: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Verifica si un nombre de usuario ya existe (excluyendo un ID específico).
    //--------------------------------------------------------------------
    public function usuarioExists(string $usuario, int $excludeId = 0): bool {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table_name}
                      WHERE usuario = :usuario AND id != :exclude_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en usuarioExists: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Verifica si un código de operador ya existe (excluyendo un ID específico).
    //--------------------------------------------------------------------
    public function codigoExists(string $codigo, int $excludeId = 0): bool {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table_name}
                      WHERE codigo_operador = :codigo AND id != :exclude_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':codigo', $codigo, PDO::PARAM_STR);
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en codigoExists: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Verifica si una cédula ya existe (excluyendo un ID específico).
    //--------------------------------------------------------------------
    public function cedulaExists(string $cedula, int $excludeId = 0): bool {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table_name}
                      WHERE cedula = :cedula AND id != :exclude_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cedula',     $cedula,    PDO::PARAM_STR);
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en cedulaExists: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Crea un nuevo usuario. La contraseña ya debe venir hasheada. 
    //--------------------------------------------------------------------
    public function create(array $data): bool {
        try {
            $query = "INSERT INTO {$this->table_name}
                        (usuario, password, nombre_completo, cedula, rol_id, codigo_operador, estado, 
                         pregunta_1_id, pregunta_2_id, respuesta_1, respuesta_2)
                      VALUES
                        (:usuario, :password, :nombre_completo, :cedula, :rol_id, :codigo_operador, :estado,
                         :p1, :p2, :r1, :r2)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':usuario',         $data['usuario'],PDO::PARAM_STR);
            $stmt->bindValue(':password',        $data['password'],PDO::PARAM_STR);
            $stmt->bindValue(':nombre_completo', $data['nombre_completo'],PDO::PARAM_STR);
            $stmt->bindValue(':cedula',          $data['cedula'],$data['cedula'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':rol_id',          $data['rol_id'],PDO::PARAM_INT);
            $stmt->bindValue(':codigo_operador', $data['codigo_operador'], $data['codigo_operador'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':estado',          $data['estado'],PDO::PARAM_STR);
            
            // Nuevos campos de seguridad (pueden ser nulos para otros roles)
            $stmt->bindValue(':p1', $data['pregunta_1_id'] ?? null, $data['pregunta_1_id'] ?? null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':p2', $data['pregunta_2_id'] ?? null, $data['pregunta_2_id'] ?? null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':r1', $data['respuesta_1'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':r2', $data['respuesta_2'] ?? null, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en create: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Actualiza nombre_completo, cedula, usuario, rol y codigo_operador.
    //--------------------------------------------------------------------
    public function updateInfo(int $id, array $data): bool {
        try {
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
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en updateInfo: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Actualiza únicamente la contraseña (ya hasheada).
    //--------------------------------------------------------------------
    public function updatePassword(int $id, string $hashedPassword): bool {
        try {
            $query = "UPDATE {$this->table_name} SET password = :password WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':id',       $id,             PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en updatePassword: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Alterna el estado entre activo/inactivo.
    //--------------------------------------------------------------------
    public function toggleEstado(int $id): array|false {
        try {
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
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en toggleEstado: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Retorna todos los roles disponibles.
    //--------------------------------------------------------------------
    public function getRoles(): array {
        try {
            $stmt = $this->conn->prepare("SELECT id, nombre FROM roles ORDER BY id ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en getRoles: " . $e->getMessage());
            return [];
        }
    }

    //--------------------------------------------------------------------
    // Verifica las respuestas de seguridad de un usuario.
    //--------------------------------------------------------------------
    public function verifySecurityAnswers(int $id, string $ans1, string $ans2): bool {
        try {
            $query = "SELECT respuesta_1, respuesta_2 FROM {$this->table_name} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) return false;

            // Comparación flexible e insensible a mayúsculas
            return password_verify(strtolower($ans1), $user['respuesta_1']) && 
                   password_verify(strtolower($ans2), $user['respuesta_2']);
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en verifySecurityAnswers: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Obtiene las preguntas (texto) asignadas a un usuario específico.
    //--------------------------------------------------------------------
    public function getUserQuestions(int $id): array|false {
        try {
            $query = "SELECT p1.pregunta as p1_texto, p2.pregunta as p2_texto
                      FROM {$this->table_name} u
                      JOIN preguntas_seguridad p1 ON u.pregunta_1_id = p1.id
                      JOIN preguntas_seguridad p2 ON u.pregunta_2_id = p2.id
                      WHERE u.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en getUserQuestions: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // [UNIFICADO] Busca un usuario por su nombre de usuario (login).
    //--------------------------------------------------------------------
    public function getUsuarioByUsername($username): array|false {
        try {
            $query = "SELECT u.*, r.nombre as nombre_rol 
                      FROM {$this->table_name} u
                      INNER JOIN roles r ON u.rol_id = r.id
                      WHERE u.usuario = :usuario AND u.estado = 'activo'
                      LIMIT 1";
                      
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario', $username, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en getUsuarioByUsername: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // [UNIFICADO] Retorna la cantidad total de usuarios registrados en el sistema.
    //--------------------------------------------------------------------
    public function countUsers(): int {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table_name}";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en countUsers: " . $e->getMessage());
            return 0;
        }
    }

    //--------------------------------------------------------------------
    // [REFAC] Actualiza las preguntas de seguridad (trasladado del controlador).
    //--------------------------------------------------------------------
    public function updateSecurityFields(int $id, array $data): bool {
        try {
            $sql = "UPDATE {$this->table_name} 
                    SET pregunta_1_id = :p1, pregunta_2_id = :p2, 
                        respuesta_1 = :r1, respuesta_2 = :r2 
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':p1', $data['pregunta_1_id'], PDO::PARAM_INT);
            $stmt->bindValue(':p2', $data['pregunta_2_id'], PDO::PARAM_INT);
            $stmt->bindValue(':r1', $data['respuesta_1'], PDO::PARAM_STR);
            $stmt->bindValue(':r2', $data['respuesta_2'], PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[UsuarioModel] Error en updateSecurityFields: " . $e->getMessage());
            return false;
        }
    }
}
