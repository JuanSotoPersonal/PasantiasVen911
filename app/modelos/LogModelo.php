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
    // Retorna registros de log paginados con búsqueda y ordenamiento.
    // Usado por DataTables en modo serverSide.
    //
    // @param int    $inicio     Offset (primer registro de la página).
    // @param int    $cantidad   Registros por página (length).
    // @param string $busqueda   Texto de búsqueda global.
    // @param int    $colOrden   Índice de columna por la que ordenar.
    // @param string $dirOrden   'asc' o 'desc'.
    //--------------------------------------------------------------------
    public function obtenerPaginado(int $inicio, int $cantidad, string $busqueda, int $colOrden, string $dirOrden): array {
        // Mapa seguro: índice DataTable -> columna SQL
        $columnas = [
            0 => 'l.accion',
            1 => 'l.tabla_afectada',
            2 => 'l.registro_id',
            3 => 'u.usuario',
            4 => 'l.fecha',
        ];
        $columnaOrden = $columnas[$colOrden] ?? 'l.fecha';
        $dirOrden     = strtolower($dirOrden) === 'asc' ? 'ASC' : 'DESC';

        try {
            $busquedaLike = '%' . $busqueda . '%';
            $query = "SELECT l.*, u.usuario AS nombre_admin
                      FROM {$this->table_name} l
                      LEFT JOIN usuarios u ON l.usuario_id = u.id
                      WHERE (:busqueda = ''
                          OR l.accion          LIKE :b1
                          OR l.tabla_afectada  LIKE :b2
                          OR u.usuario         LIKE :b3
                          OR l.detalles        LIKE :b4
                      )
                      ORDER BY {$columnaOrden} {$dirOrden}
                      LIMIT :cantidad OFFSET :inicio";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':busqueda', $busqueda,      PDO::PARAM_STR);
            $stmt->bindValue(':b1',       $busquedaLike,  PDO::PARAM_STR);
            $stmt->bindValue(':b2',       $busquedaLike,  PDO::PARAM_STR);
            $stmt->bindValue(':b3',       $busquedaLike,  PDO::PARAM_STR);
            $stmt->bindValue(':b4',       $busquedaLike,  PDO::PARAM_STR);
            $stmt->bindValue(':cantidad', $cantidad,      PDO::PARAM_INT);
            $stmt->bindValue(':inicio',   $inicio,        PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[LogModelo] Error en obtenerPaginado: " . $e->getMessage());
            return [];
        }
    }

    //--------------------------------------------------------------------
    // Retorna el total absoluto de registros (sin filtros).
    //--------------------------------------------------------------------
    public function contarTodos(): int {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table_name}");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("[LogModelo] Error en contarTodos: " . $e->getMessage());
            return 0;
        }
    }

    //--------------------------------------------------------------------
    // Retorna el total de registros que coinciden con una búsqueda.
    //--------------------------------------------------------------------
    public function contarFiltrados(string $busqueda): int {
        try {
            $busquedaLike = '%' . $busqueda . '%';
            $query = "SELECT COUNT(*)
                      FROM {$this->table_name} l
                      LEFT JOIN usuarios u ON l.usuario_id = u.id
                      WHERE (:busqueda = ''
                          OR l.accion         LIKE :b1
                          OR l.tabla_afectada LIKE :b2
                          OR u.usuario        LIKE :b3
                          OR l.detalles       LIKE :b4
                      )";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':busqueda', $busqueda,     PDO::PARAM_STR);
            $stmt->bindValue(':b1',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b2',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b3',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b4',       $busquedaLike, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("[LogModelo] Error en contarFiltrados: " . $e->getMessage());
            return 0;
        }
    }
}
