<?php

namespace App\modelos;

use App\Config\Database;
use PDO;

require_once 'app/Config/Database.php';

class LogModelo {
    private $conn;
    private $table_name = "logs_sistema";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    //--------------------------------------------------------------------
    // Registra un evento de auditoría en la tabla logs_sistema.
    // Ajustado a la estructura real de la base de datos.
    //
    // @param int     $usuario_id      ID del administrador que realiza la acción.
    // @param string  $accion          INSERT, UPDATE, DELETE, LOGIN, LOGOUT, CAMBIO_ESTADO.
    // @param string  $tabla_afectada  Nombre de la tabla modificada.
    // @param int|null $registro_id    ID del registro afectado.
    // @param array|null $anterior     Datos antes del cambio.
    // @param array|null $nuevo        Datos después del cambio.
    // @param string|null $detalles    Cualquier nota adicional.
    //--------------------------------------------------------------------
    public function registrar(
        int $usuario_id, 
        string $accion, 
        string $tabla_afectada, 
        ?int $registro_id = null, 
        ?array $anterior = null, 
        ?array $nuevo = null, 
        ?string $detalles = null
    ): void {
        try {
            $query = "INSERT INTO {$this->table_name}
                        (usuario_id, accion, tabla_afectada, registro_id, valor_anterior, valor_nuevo, detalles)
                      VALUES
                        (:usuario_id, :accion, :tabla_afectada, :registro_id, :anterior, :nuevo, :detalles)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':usuario_id',     $usuario_id,            PDO::PARAM_INT);
            $stmt->bindValue(':accion',         $accion,                PDO::PARAM_STR);
            $stmt->bindValue(':tabla_afectada', $tabla_afectada,        PDO::PARAM_STR);
            $stmt->bindValue(':registro_id',    $registro_id,           $registro_id ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':anterior',       $anterior ? json_encode($anterior, JSON_UNESCAPED_UNICODE) : null, PDO::PARAM_STR);
            $stmt->bindValue(':nuevo',          $nuevo ? json_encode($nuevo, JSON_UNESCAPED_UNICODE) : null,       PDO::PARAM_STR);
            $stmt->bindValue(':detalles',       $detalles,              PDO::PARAM_STR);
            
            $stmt->execute();
        } catch (\Exception $e) {
            error_log("[LogModelo] Error al registrar log: " . $e->getMessage());
        }
    }

    //--------------------------------------------------------------------
    // Retorna todos los registros de logs ordenados por fecha descendente.
    // Incluye el nombre del usuario que realizó la acción.
    //--------------------------------------------------------------------
    public function obtenerTodos(): array {
        try {
            $query = "SELECT l.*, u.usuario as nombre_admin 
                      FROM {$this->table_name} l
                      LEFT JOIN usuarios u ON l.usuario_id = u.id
                      ORDER BY l.fecha DESC";
                      
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[LogModelo] Error en obtenerTodos: " . $e->getMessage());
            return [];
        }
    }
}
