<?php
/**
 * MODELO: UsuarioModelo
 * Descripción: Gestiona todas las operaciones lógicas y de base de datos relacionadas
 * con la entidad de Usuarios, incluyendo autenticación, permisos y auditoría.
 */

namespace App\modelos;

use App\Config\Database;
use PDO;
use Exception;

require_once 'app/Config/Database.php';

class UsuarioModelo {

    // ///////////////////////////////////////////////////////////////////
    // 1. ATRIBUTOS Y CONFIGURACIÓN
    // ///////////////////////////////////////////////////////////////////

    private $conexion;
    private $table_name = "usuarios";

    // ///////////////////////////////////////////////////////////////////
    // 2. CONSTRUCTOR (CONEXIÓN A BD)
    // ///////////////////////////////////////////////////////////////////

    public function __construct() {
        try {
            $database = new Database();
            $this->conexion = $database->obtenerConexion();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en constructor: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos.");
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. MÉTODOS DE CONSULTA (LECTURA)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna usuarios con su nombre de rol, filtrados por estado.
     * NOTA: Método sin callers activos en controladores. LIMIT defensivo aplicado.
     */
    public function obtenerTodos(string $estado = 'activo'): array {
        try {
            $query = "SELECT u.id, u.usuario, u.nombre_completo, u.cedula,
                             u.estado, u.rol_id, r.nombre AS nombre_rol
                      FROM {$this->table_name} u
                      INNER JOIN roles r ON u.rol_id = r.id
                      WHERE u.estado = :estado
                      ORDER BY u.id ASC
                      LIMIT 500";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna un usuario específico por su ID primario.
     */
    public function obtenerPorId(int $id): array|false {
        try {
            $query = "SELECT u.*, r.nombre AS nombre_rol
                      FROM {$this->table_name} u
                      LEFT JOIN roles r ON u.rol_id = r.id
                      WHERE u.id = :id
                      LIMIT 1";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en obtenerPorId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca un usuario por su nombre de usuario (login), solo si está activo.
     */
    public function obtenerUsuarioPorNombre($nombreUsuario): array|false {
        try {
            $query = "SELECT u.*, r.nombre as nombre_rol 
                      FROM {$this->table_name} u
                      INNER JOIN roles r ON u.rol_id = r.id
                      WHERE u.usuario = :usuario AND u.estado = 'activo'
                      LIMIT 1";
                      
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':usuario', $nombreUsuario, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en obtenerUsuarioPorNombre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna todos los roles registrados en el sistema.
     */
    public function obtenerRoles(): array {
        try {
            $stmt = $this->conexion->prepare("SELECT id, nombre FROM roles ORDER BY id ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en obtenerRoles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conteo total de usuarios registrados (Dashboard).
     */
    public function contarUsuarios(): int {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table_name}";
            $stmt = $this->conexion->prepare($query);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en contarUsuarios: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Conteo de usuarios agrupados por su estado actual (Dashboard).
     */
    public function contarPorEstado(): array {
        try {
            $query = "SELECT estado, COUNT(*) as total 
                      FROM {$this->table_name} 
                      GROUP BY estado";
            $stmt = $this->conexion->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en contarPorEstado: " . $e->getMessage());
            return [];
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. MÉTODOS DE VALIDACIÓN Y EXISTENCIA
    // ///////////////////////////////////////////////////////////////////

    /**
     * Verifica si un nombre de usuario ya está en uso.
     */
    public function existeUsuario(string $usuario, int $excludeId = 0): bool {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table_name}
                      WHERE usuario = :usuario AND id != :exclude_id";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':usuario',    $usuario,   PDO::PARAM_STR);
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en existeUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si una cédula de identidad ya está registrada.
     */
    public function existeCedula(string $cedula, int $excludeId = 0): bool {
        try {
            $query = "SELECT COUNT(*) FROM {$this->table_name}
                      WHERE cedula = :cedula AND id != :exclude_id";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':cedula',     $cedula,    PDO::PARAM_STR);
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en existeCedula: " . $e->getMessage());
            return false;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 5. MÉTODOS DE ESCRITURA (CREATE/UPDATE)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Registra un nuevo usuario en el sistema.
     */
    public function crear(array $datos): bool {
        try {
            $query = "INSERT INTO {$this->table_name}
                        (usuario, password, nombre_completo, cedula, rol_id, estado,
                         pregunta_1_id, pregunta_2_id, respuesta_1, respuesta_2)
                      VALUES
                        (:usuario, :password, :nombre_completo, :cedula, :rol_id, :estado,
                         :p1, :p2, :r1, :r2)";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':usuario',         $datos['usuario'],         PDO::PARAM_STR);
            $stmt->bindValue(':password',        $datos['password'],        PDO::PARAM_STR);
            $stmt->bindValue(':nombre_completo', $datos['nombre_completo'], PDO::PARAM_STR);
            $stmt->bindValue(':cedula',          $datos['cedula'],          $datos['cedula'] ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':rol_id',          $datos['rol_id'],          PDO::PARAM_INT);
            $stmt->bindValue(':estado',          $datos['estado'],          PDO::PARAM_STR);
            
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

    /**
     * Actualiza la información básica de un usuario.
     */
    public function actualizarInformacion(int $id, array $datos): bool {
        try {
            $query = "UPDATE {$this->table_name}
                      SET nombre_completo = :nombre_completo,
                          cedula          = :cedula,
                          usuario         = :usuario,
                          rol_id          = :rol_id
                      WHERE id = :id";

            $stmt = $this->conexion->prepare($query);
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

    /**
     * Cambia la contraseña de un usuario (proceso de reseteo o cambio manual).
     */
    public function actualizarContrasena(int $id, string $contrasenaHasheada): bool {
        try {
            $query = "UPDATE {$this->table_name} SET password = :password WHERE id = :id";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':password', $contrasenaHasheada, PDO::PARAM_STR);
            $stmt->bindValue(':id',       $id,                 PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en actualizarContrasena: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Alterna el estado entre activo/inactivo (Soft-delete).
     */
    public function alternarEstado(int $id): array|false {
        try {
            $query = "SELECT estado FROM {$this->table_name} WHERE id = :id LIMIT 1";
            $stmt  = $this->conexion->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) return false;

            $nuevoEstado = ($row['estado'] === 'activo') ? 'inactivo' : 'activo';

            $update = "UPDATE {$this->table_name} SET estado = :estado WHERE id = :id";
            $stmt2  = $this->conexion->prepare($update);
            $stmt2->bindValue(':estado', $nuevoEstado, PDO::PARAM_STR);
            $stmt2->bindValue(':id',     $id,          PDO::PARAM_INT);
            $stmt2->execute();

            return ['nuevo_estado' => $nuevoEstado];
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en alternarEstado: " . $e->getMessage());
            return false;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 6. MÉTODOS DE SEGURIDAD Y PERMISOS
    // ///////////////////////////////////////////////////////////////////

    /**
     * Valida las respuestas de seguridad (insensible a mayúsculas).
     */
    public function verificarRespuestasSeguridad(int $id, string $ans1, string $ans2): bool {
        try {
            $query = "SELECT respuesta_1, respuesta_2 FROM {$this->table_name} WHERE id = :id";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) return false;

            return password_verify(strtolower($ans1), $usuario['respuesta_1']) && 
                   password_verify(strtolower($ans2), $usuario['respuesta_2']);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en verificarRespuestasSeguridad: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el texto de las preguntas de seguridad asignadas.
     */
    public function obtenerPreguntasUsuario(int $id): array|false {
        try {
            $query = "SELECT p1.pregunta as p1_texto, p2.pregunta as p2_texto
                      FROM {$this->table_name} u
                      JOIN preguntas_seguridad p1 ON u.pregunta_1_id = p1.id
                      JOIN preguntas_seguridad p2 ON u.pregunta_2_id = p2.id
                      WHERE u.id = :id";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en obtenerPreguntasUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza las preguntas y respuestas de seguridad del perfil.
     */
    public function actualizarCamposSeguridad(int $id, array $datos): bool {
        try {
            $sql = "UPDATE {$this->table_name} 
                    SET pregunta_1_id = :p1, pregunta_2_id = :p2, 
                        respuesta_1 = :r1, respuesta_2 = :r2 
                    WHERE id = :id";
            $stmt = $this->conexion->prepare($sql);
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

    /**
     * Retorna el mapa de permisos [modulo => [permisos]] de un rol.
     */
    public function obtenerPermisosDeRol(int $rolId): array {
        try {
            $query = "SELECT m.clave AS modulo, p.clave AS permiso
                      FROM rol_permiso rp
                      JOIN permisos p  ON rp.permiso_id = p.id
                      JOIN modulos  m  ON p.modulo_id   = m.id
                      WHERE rp.rol_id = :rol_id";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':rol_id', $rolId, PDO::PARAM_INT);
            $stmt->execute();
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    // ///////////////////////////////////////////////////////////////////
    // 7. MÉTODOS PARA DATATABLES (SERVER-SIDE)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna registros paginados filtrados por rol_id.
     */
    public function obtenerPorRol(int $rolId, string $estado = 'activo', int $inicio = 0, int $cantidad = 10, string $busqueda = '', int $colOrden = 0, string $dirOrden = 'asc'): array {
        $columnasOrdenables = [0 => 'u.id', 1 => 'u.nombre_completo', 2 => 'u.usuario', 3 => 'u.cedula', 4 => 'u.estado'];
        $columnaOrden = $columnasOrdenables[$colOrden] ?? 'u.id';
        $dirOrden     = strtolower($dirOrden) === 'asc' ? 'ASC' : 'DESC';

        try {
            $busquedaLike = '%' . $busqueda . '%';
            $query = "SELECT u.id, u.usuario, u.nombre_completo, u.cedula,
                             u.estado, u.rol_id, r.nombre AS nombre_rol
                      FROM {$this->table_name} u
                      INNER JOIN roles r ON u.rol_id = r.id
                      WHERE u.rol_id = :rol_id
                        AND u.estado = :estado
                        AND (:busqueda = ''
                          OR u.nombre_completo LIKE :b1
                          OR u.usuario         LIKE :b2
                          OR u.cedula          LIKE :b3
                        )
                      ORDER BY {$columnaOrden} {$dirOrden}
                      LIMIT :cantidad OFFSET :inicio";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':rol_id',   $rolId,        PDO::PARAM_INT);
            $stmt->bindValue(':estado',   $estado,       PDO::PARAM_STR);
            $stmt->bindValue(':busqueda', $busqueda,     PDO::PARAM_STR);
            $stmt->bindValue(':b1',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b2',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b3',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':cantidad', $cantidad,     PDO::PARAM_INT);
            $stmt->bindValue(':inicio',   $inicio,       PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en obtenerPorRol: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conteo total de usuarios por rol (DataTables).
     */
    public function contarPorRol(int $rolId, string $estado = 'activo'): int {
        try {
            $stmt = $this->conexion->prepare(
                "SELECT COUNT(*) FROM {$this->table_name}
                 WHERE rol_id = :rol_id AND estado = :estado"
            );
            $stmt->bindValue(':rol_id', $rolId,  PDO::PARAM_INT);
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en contarPorRol: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Conteo filtrado de usuarios por rol (DataTables).
     * Sin JOIN con roles: el COUNT solo usa columnas de la tabla usuarios.
     */
    public function contarFiltradosPorRol(int $rolId, string $estado, string $busqueda): int {
        try {
            $busquedaLike = '%' . $busqueda . '%';
            $query = "SELECT COUNT(*)
                      FROM {$this->table_name} u
                      WHERE u.rol_id = :rol_id
                        AND u.estado = :estado
                        AND (:busqueda = ''
                          OR u.nombre_completo LIKE :b1
                          OR u.usuario         LIKE :b2
                          OR u.cedula          LIKE :b3
                        )";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':rol_id',   $rolId,        PDO::PARAM_INT);
            $stmt->bindValue(':estado',   $estado,       PDO::PARAM_STR);
            $stmt->bindValue(':busqueda', $busqueda,     PDO::PARAM_STR);
            $stmt->bindValue(':b1',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b2',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b3',       $busquedaLike, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en contarFiltradosPorRol: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna registros paginados generales compatibles con DataTables server-side.
     */
    public function obtenerPaginadoUsuarios(
        int    $inicio,
        int    $cantidad,
        string $busqueda,
        int    $colOrden,
        string $dirOrden,
        string $estado = 'activo'
    ): array {
        $columnasOrdenables = [
            0 => 'u.id', 1 => 'u.nombre_completo', 2 => 'u.usuario', 
            3 => 'u.cedula', 4 => 'r.nombre', 5 => 'u.estado'
        ];
        $columnaOrden = $columnasOrdenables[$colOrden] ?? 'u.id';
        $dirOrden     = strtolower($dirOrden) === 'asc' ? 'ASC' : 'DESC';

        try {
            $busquedaLike = '%' . $busqueda . '%';
            $condicionEstado = ($estado === 'todos') ? '1=1' : 'u.estado = :estado';

            $query = "SELECT u.id, u.usuario, u.nombre_completo, u.cedula,
                             u.estado, u.rol_id, r.nombre AS nombre_rol
                      FROM {$this->table_name} u
                      INNER JOIN roles r ON u.rol_id = r.id
                      WHERE {$condicionEstado}
                        AND (:busqueda = ''
                          OR u.nombre_completo LIKE :b1
                          OR u.usuario         LIKE :b2
                          OR u.cedula          LIKE :b3
                          OR r.nombre          LIKE :b4
                        )
                      ORDER BY {$columnaOrden} {$dirOrden}
                      LIMIT :cantidad OFFSET :inicio";

            $stmt = $this->conexion->prepare($query);
            if ($estado !== 'todos') {
                $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            }
            $stmt->bindValue(':busqueda', $busqueda,     PDO::PARAM_STR);
            $stmt->bindValue(':b1',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b2',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b3',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b4',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':cantidad', $cantidad,     PDO::PARAM_INT);
            $stmt->bindValue(':inicio',   $inicio,       PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en obtenerPaginadoUsuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna el total absoluto de usuarios registrados.
     */
    public function contarTodosUsuarios(string $estado = 'activo'): int {
        try {
            $condicionEstado = ($estado === 'todos') ? '' : 'WHERE estado = :estado';
            $stmt = $this->conexion->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} {$condicionEstado}"
            );
            if ($estado !== 'todos') {
                $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            }
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en contarTodosUsuarios: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna el total de usuarios que coinciden con los filtros aplicados.
     */
    public function contarFiltradosUsuarios(string $busqueda, string $estado = 'activo'): int {
        try {
            $busquedaLike    = '%' . $busqueda . '%';
            $condicionEstado = ($estado === 'todos') ? '1=1' : 'u.estado = :estado';
            $query = "SELECT COUNT(*)
                      FROM {$this->table_name} u
                      INNER JOIN roles r ON u.rol_id = r.id
                      WHERE {$condicionEstado}
                        AND (:busqueda = ''
                          OR u.nombre_completo LIKE :b1
                          OR u.usuario         LIKE :b2
                          OR u.cedula          LIKE :b3
                          OR r.nombre          LIKE :b4
                        )";
            $stmt = $this->conexion->prepare($query);
            if ($estado !== 'todos') {
                $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            }
            $stmt->bindValue(':busqueda', $busqueda,     PDO::PARAM_STR);
            $stmt->bindValue(':b1',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b2',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b3',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b4',       $busquedaLike, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[UsuarioModelo] Error en contarFiltradosUsuarios: " . $e->getMessage());
            return 0;
        }
    }
}
