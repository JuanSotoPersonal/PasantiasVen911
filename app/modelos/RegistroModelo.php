<?php
/**
 * MODELO: RegistroModelo
 * Propósito: Gestionar los procesos iniciales de registro de usuarios,
 * validación de llaves de activación y catálogo de seguridad.
 */

namespace App\modelos;

use App\Config\Database;
use PDO;
use Exception;

require_once 'app/Config/Database.php';

class RegistroModelo {

    // ///////////////////////////////////////////////////////////////////
    // 1. ATRIBUTOS Y CONEXIÓN
    // ///////////////////////////////////////////////////////////////////

    private $conexion;

    /**
     * Constructor: Establece el vínculo con el manejador de base de datos.
     */
    public function __construct() {
        try {
            $database = new Database();
            $this->conexion = $database->obtenerConexion();
        } catch (Exception $e) {
            error_log("[RegistroModelo] Error en constructor: " . $e->getMessage());
            die("Error de conexión a la base de datos.");
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. MÉTODOS DE CONSULTA (LECTURA)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Obtiene todas las preguntas de seguridad predefinidas en el sistema.
     */
    public function obtenerPreguntasSeguridad(): array {
        try {
            $query = "SELECT id, pregunta FROM preguntas_seguridad ORDER BY id ASC";
            $stmt = $this->conexion->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[RegistroModelo] Error en obtenerPreguntasSeguridad: " . $e->getMessage());
            return [];
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. MÉTODOS DE VALIDACIÓN Y SEGURIDAD
    // ///////////////////////////////////////////////////////////////////

    /**
     * Valida si la llave de activación ingresada coincide con la del sistema.
     */
    public function validarLlaveActivacion(string $key): bool {
        try {
            $query = "SELECT COUNT(*) FROM configuracion_sistema WHERE llave_activacion = :key";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':key', $key, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("[RegistroModelo] Error en validarLlaveActivacion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna la llave de activación registrada (Uso administrativo/configuración).
     */
    public function obtenerLlaveActivacion(): string {
        try {
            $query = "SELECT llave_activacion FROM configuracion_sistema LIMIT 1";
            $stmt = $this->conexion->prepare($query);
            $stmt->execute();
            return (string)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[RegistroModelo] Error en obtenerLlaveActivacion: " . $e->getMessage());
            return "";
        }
    }
}
