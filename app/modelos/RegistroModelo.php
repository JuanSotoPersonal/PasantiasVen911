<?php

namespace App\modelos;

use App\Config\Database;
use PDO;
use Exception;

require_once 'app/Config/Database.php';

class RegistroModelo {
    private $conexion;

    public function __construct() {
        try {
            $database = new Database();
            $this->conexion = $database->obtenerConexion();
        } catch (Exception $e) {
            error_log("[RegistroModelo] Error en constructor: " . $e->getMessage());
            die("Error de conexión a la base de datos.");
        }
    }

    //--------------------------------------------------------------------
    // Obtiene todas las preguntas de seguridad predefinidas.
    //--------------------------------------------------------------------
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

    //--------------------------------------------------------------------
    // Valida si la llave de activación es correcta.
    //--------------------------------------------------------------------
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

    //--------------------------------------------------------------------
    // Obtiene la llave de activación (para debug o referencia, aunque debería ser secreta).
    //--------------------------------------------------------------------
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

