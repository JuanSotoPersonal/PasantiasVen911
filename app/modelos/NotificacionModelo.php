<?php
/**
 * MODELO: NotificacionModelo
 * Propósito: Gestionar el sistema de alertas y notificaciones internas para los usuarios,
 * permitiendo el seguimiento de eventos relevantes (ej. asignación de fichas).
 */

namespace App\modelos;

use App\Config\Database;
use PDO;
use Exception;

require_once 'app/Config/Database.php';

class NotificacionModelo {

    // ///////////////////////////////////////////////////////////////////
    // 1. ATRIBUTOS Y CONEXIÓN
    // ///////////////////////////////////////////////////////////////////

    private $conexion;
    private $tabla = 'notificaciones';

    /**
     * Constructor: Inicializa la comunicación con la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conexion = $database->obtenerConexion();
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. MÉTODOS DE CONSULTA (LECTURA)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Recupera las últimas 20 notificaciones pendientes de lectura para un usuario.
     */
    public function obtenerNoLeidas(int $usuario_id): array {
        try {
            $sql = "SELECT id, tipo, titulo, mensaje, leido, fecha_creacion
                    FROM {$this->tabla}
                    WHERE usuario_recibe_id = :uid AND leido = 0
                    ORDER BY fecha_creacion DESC
                    LIMIT 20";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':uid', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[NotificacionModelo] Error en obtenerNoLeidas: " . $e->getMessage());
            return [];
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. MÉTODOS DE ACTUALIZACIÓN (ESTADO)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Cambia el estado de una notificación específica a 'leída'.
     */
    public function marcarLeida(int $id_notif, int $usuario_id): bool {
        try {
            $sql = "UPDATE {$this->tabla}
                    SET leido = 1
                    WHERE id = :id AND usuario_recibe_id = :uid";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':id',  $id_notif,   PDO::PARAM_INT);
            $stmt->bindParam(':uid', $usuario_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[NotificacionModelo] Error en marcarLeida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marca como leídas todas las notificaciones pendientes de un usuario específico.
     */
    public function marcarTodasLeidas(int $usuario_id): bool {
        try {
            $sql = "UPDATE {$this->tabla} SET leido = 1 WHERE usuario_recibe_id = :uid AND leido = 0";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':uid', $usuario_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[NotificacionModelo] Error en marcarTodasLeidas: " . $e->getMessage());
            return false;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. MÉTODOS DE ESCRITURA (CREACIÓN)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Genera una nueva notificación dirigida a un usuario, vinculada opcionalmente a una ficha.
     */
    public function crear(int $usuario_recibe_id, string $tipo, string $titulo, string $mensaje, ?int $ficha_id = null): int|false {
        try {
            $sql = "INSERT INTO {$this->tabla} (usuario_recibe_id, ficha_id, tipo, titulo, mensaje)
                    VALUES (:uid, :ficha_id, :tipo, :titulo, :mensaje)";

            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':uid',      $usuario_recibe_id, PDO::PARAM_INT);
            $stmt->bindValue(':ficha_id', $ficha_id,          $ficha_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindParam(':tipo',     $tipo,              PDO::PARAM_STR);
            $stmt->bindParam(':titulo',   $titulo,            PDO::PARAM_STR);
            $stmt->bindParam(':mensaje',  $mensaje,           PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return (int)$this->conexion->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("[NotificacionModelo] Error en crear: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los IDs de usuario que pertenecen a un rol específico.
     * Útil para notificaciones masivas por jerarquía.
     */
    public function obtenerUsuariosPorRol(int $rol_id): array {
        try {
            $sql = "SELECT id FROM usuarios WHERE rol_id = :rol_id AND estado = 'activo'";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':rol_id', $rol_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            error_log("[NotificacionModelo] Error en obtenerUsuariosPorRol: " . $e->getMessage());
            return [];
        }
    }
}
