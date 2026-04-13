<?php

namespace App\modelos;

use App\Config\Database;
use PDO;
use Exception;

require_once 'app/Config/Database.php';

class UsuarioModelo {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en constructor: " . $e->getMessage());
            die("Error de conexión a la base de datos.");
        }
    }

    //--------------------------------------------------------------------
    // Retorna todos los usuarios con su nombre de rol, filtrados por estado.
    //--------------------------------------------------------------------
    public function obtenerTodos(string $estado = 'activo'): array {
        try {
            $query = "SELECT u.id, u.usuario, u.nombre_completo, u.cedula,
                             u.estado, u.rol_id, r.nombre AS nombre_rol
                      FROM {$this->table_name} u
                      INNER JOIN roles r ON u.rol_id = r.id
                      WHERE u.estado = :estado
                      ORDER BY u.id ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    //--------------------------------------------------------------------
    // Retorna usuarios filtrados por rol_id y estado (para DataTables por rol)
    //--------------------------------------------------------------------
    public function obtenerPorRol(int $rolId, string $estado = 'activo'): array {
        try {
            $query = "SELECT u.id, u.usuario, u.nombre_completo, u.cedula,
                             u.estado, u.rol_id, r.nombre AS nombre_rol
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
            error_log("[UsuarioModelo] Error en obtenerPorRol: " . $e->getMessage());
            return [];
        }
    }

    //--------------------------------------------------------------------
    // Retorna un usuario por ID.
    //--------------------------------------------------------------------
    public function obtenerPorId(int $id): array|false {
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
            error_log("[UsuarioModelo] Error en obtenerPorId: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Verifica si un nombre de usuario ya existe (excluyendo un ID específico).
    //--------------------------------------------------------------------
    public function existeUsuario(string $usuario, int $excludeId = 0): bool {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table_name}
                      WHERE usuario = :usuario AND id != :exclude_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en existeUsuario: " . $e->getMessage());
            return false;
        }
    }


    //--------------------------------------------------------------------
    // Verifica si una cédula ya existe (excluyendo un ID específico).
    //--------------------------------------------------------------------
    public function existeCedula(string $cedula, int $excludeId = 0): bool {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table_name}
                      WHERE cedula = :cedula AND id != :exclude_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':cedula',     $cedula,    PDO::PARAM_STR);
            $stmt->bindParam(':exclude_id', $excludeId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en existeCedula: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Crea un nuevo usuario. La contraseña ya debe venir hasheada. 
    //--------------------------------------------------------------------
    public function crear(array $datos): bool {
        try {
            $query = "INSERT INTO {$this->table_name}
                        (usuario, password, nombre_completo, cedula, rol_id, estado,
                         pregunta_1_id, pregunta_2_id, respuesta_1, respuesta_2)
                      VALUES
                        (:usuario, :password, :nombre_completo, :cedula, :rol_id, :estado,
                         :p1, :p2, :r1, :r2)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':usuario',         $datos['usuario'],         PDO::PARAM_STR);
            $stmt->bindValue(':password',        $datos['password'],        PDO::PARAM_STR);
            $stmt->bindValue(':nombre_completo', $datos['nombre_completo'], PDO::PARAM_STR);
            $stmt->bindValue(':cedula',          $datos['cedula'],          $datos['cedula'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':rol_id',          $datos['rol_id'],          PDO::PARAM_INT);
            $stmt->bindValue(':estado',          $datos['estado'],          PDO::PARAM_STR);
            
            // Nuevos campos de seguridad (pueden ser nulos para otros roles)
            $stmt->bindValue(':p1', $datos['pregunta_1_id'] ?? null, $datos['pregunta_1_id'] ?? null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':p2', $datos['pregunta_2_id'] ?? null, $datos['pregunta_2_id'] ?? null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':r1', $datos['respuesta_1'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':r2', $datos['respuesta_2'] ?? null, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en crear: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Actualiza nombre_completo, cedula, usuario y rol.
    //--------------------------------------------------------------------
    public function actualizarInformacion(int $id, array $datos): bool {
        try {
            $query = "UPDATE {$this->table_name}
                      SET nombre_completo = :nombre_completo,
                          cedula          = :cedula,
                          usuario         = :usuario,
                          rol_id          = :rol_id
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':nombre_completo', $datos['nombre_completo'], PDO::PARAM_STR);
            $stmt->bindValue(':cedula',          $datos['cedula'],          $datos['cedula'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':usuario',         $datos['usuario'],         PDO::PARAM_STR);
            $stmt->bindValue(':rol_id',          $datos['rol_id'],          PDO::PARAM_INT);
            $stmt->bindValue(':id',              $id,                       PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en actualizarInformacion: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Actualiza únicamente la contraseña (ya hasheada).
    //--------------------------------------------------------------------
    public function actualizarContrasena(int $id, string $contrasenaHasheada): bool {
        try {
            $query = "UPDATE {$this->table_name} SET password = :password WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $contrasenaHasheada, PDO::PARAM_STR);
            $stmt->bindParam(':id',       $id,             PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en actualizarContrasena: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Alterna el estado entre activo/inactivo.
    //--------------------------------------------------------------------
    public function alternarEstado(int $id): array|false {
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
            error_log("[UsuarioModelo] Error en alternarEstado: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Retorna todos los roles disponibles.
    //--------------------------------------------------------------------
    public function obtenerRoles(): array {
        try {
            $stmt = $this->conn->prepare("SELECT id, nombre FROM roles ORDER BY id ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en obtenerRoles: " . $e->getMessage());
            return [];
        }
    }

    //--------------------------------------------------------------------
    // Verifica las respuestas de seguridad de un usuario.
    //--------------------------------------------------------------------
    public function verificarRespuestasSeguridad(int $id, string $ans1, string $ans2): bool {
        try {
            $query = "SELECT respuesta_1, respuesta_2 FROM {$this->table_name} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) return false;

            // Comparación flexible e insensible a mayúsculas
            return password_verify(strtolower($ans1), $usuario['respuesta_1']) && 
                   password_verify(strtolower($ans2), $usuario['respuesta_2']);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en verificarRespuestasSeguridad: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Obtiene las preguntas (texto) asignadas a un usuario específico.
    //--------------------------------------------------------------------
    public function obtenerPreguntasUsuario(int $id): array|false {
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
            error_log("[UsuarioModelo] Error en obtenerPreguntasUsuario: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Busca un usuario por su nombre de usuario (login).
    //--------------------------------------------------------------------
    public function obtenerUsuarioPorNombre($nombreUsuario): array|false {
        try {
            $query = "SELECT u.*, r.nombre as nombre_rol 
                      FROM {$this->table_name} u
                      INNER JOIN roles r ON u.rol_id = r.id
                      WHERE u.usuario = :usuario AND u.estado = 'activo'
                      LIMIT 1";
                      
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario', $nombreUsuario, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en obtenerUsuarioPorNombre: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Retorna la cantidad total de usuarios registrados en el sistema.
    //--------------------------------------------------------------------
    public function contarUsuarios(): int {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table_name}";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en contarUsuarios: " . $e->getMessage());
            return 0;
        }
    }

    //--------------------------------------------------------------------
    // Actualiza las preguntas de seguridad 
    //--------------------------------------------------------------------
    public function actualizarCamposSeguridad(int $id, array $datos): bool {
        try {
            $sql = "UPDATE {$this->table_name} 
                    SET pregunta_1_id = :p1, pregunta_2_id = :p2, 
                        respuesta_1 = :r1, respuesta_2 = :r2 
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':p1', $datos['pregunta_1_id'], PDO::PARAM_INT);
            $stmt->bindValue(':p2', $datos['pregunta_2_id'], PDO::PARAM_INT);
            $stmt->bindValue(':r1', $datos['respuesta_1'], PDO::PARAM_STR);
            $stmt->bindValue(':r2', $datos['respuesta_2'], PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en actualizarCamposSeguridad: " . $e->getMessage());
            return false;
        }
    }

    //--------------------------------------------------------------------
    // Retorna la cantidad de usuarios agrupados por su estado.
    //--------------------------------------------------------------------
    public function contarPorEstado(): array {
        try {
            $query = "SELECT estado, COUNT(*) as total 
                      FROM {$this->table_name} 
                      GROUP BY estado";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en contarPorEstado: " . $e->getMessage());
            return [];
        }
    }

    //--------------------------------------------------------------------
    // Retorna los permisos de un rol como mapa [modulo => [permiso, ...]]
    // Se carga una única vez en sesión al hacer login para evitar queries
    // en cada petición (patrón de carga temprana).
    //--------------------------------------------------------------------
    public function obtenerPermisosDeRol(int $rolId): array {
        try {
            $query = "SELECT m.clave AS modulo, p.clave AS permiso
                      FROM rol_permiso rp
                      JOIN permisos p  ON rp.permiso_id = p.id
                      JOIN modulos  m  ON p.modulo_id   = m.id
                      WHERE rp.rol_id = :rol_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':rol_id', $rolId, PDO::PARAM_INT);
            $stmt->execute();
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Construir mapa: ['fichas' => ['ver', 'crear'], 'usuarios' => ['ver', ...]]
            $permisos = [];
            foreach ($filas as $fila) {
                $permisos[$fila['modulo']][] = $fila['permiso'];
            }
            return $permisos;
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en obtenerPermisosDeRol: " . $e->getMessage());
            return [];
        }
    }
}
