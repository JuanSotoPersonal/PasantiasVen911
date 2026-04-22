<?php

namespace App\modelos;

use App\Config\Database;
use PDO;

require_once 'app/Config/Database.php';

class EventoModelo {

    private $conexion;
    private string $tabla_sistema = 'eventos_sistema';
    private string $tabla_fichas  = 'eventos_fichas';

    public function __construct() {
        $database = new Database();
        $this->conexion = $database->obtenerConexion();
    }

    //--------------------------------------------------------------------
    // Registra un evento general del sistema en eventos_sistema.
    //
    // @param int|null $usuario_id    NULL para acciones del sistema sin sesión activa
    // @param string   $tipo_accion   INSERT | UPDATE | DELETE | LOGIN | LOGOUT | CAMBIO_ESTADO
    // @param string   $tabla         Nombre de la tabla afectada 
    // @param int|null $registro_id   ID del registro afectado
    // @param array|null $anterior    Estado previo 
    // @param array|null $nuevo       Estado nuevo 
    // @param string|null $descripcion Nota legible por humanos
    //--------------------------------------------------------------------
    public function registrarEvento(
        ?int    $usuario_id,
        string  $tipo_accion,
        string  $tabla,
        ?int    $registro_id = null,
        ?array  $anterior    = null,
        ?array  $nuevo       = null,
        ?string $descripcion = null
    ): void {
        try {
            $query = "INSERT INTO {$this->tabla_sistema}
                        (usuario_id, tipo_accion, tabla_afectada, registro_id, valor_anterior, valor_nuevo, descripcion)
                      VALUES
                        (:usuario_id, :tipo_accion, :tabla, :registro_id, :anterior, :nuevo, :descripcion)";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':usuario_id',  $usuario_id,  $usuario_id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':tipo_accion', $tipo_accion, PDO::PARAM_STR);
            $stmt->bindValue(':tabla',       $tabla,       PDO::PARAM_STR);
            $stmt->bindValue(':registro_id', $registro_id, $registro_id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':anterior',    $anterior  ? json_encode($anterior,  JSON_UNESCAPED_UNICODE) : null, PDO::PARAM_STR);
            $stmt->bindValue(':nuevo',       $nuevo     ? json_encode($nuevo,     JSON_UNESCAPED_UNICODE) : null, PDO::PARAM_STR);
            $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmt->execute();
        } catch (\Exception $e) {
            error_log("[EventoModelo] Error en registrarEvento: " . $e->getMessage());
        }
    }

    //--------------------------------------------------------------------
    // Registra un evento del ciclo de vida de una ficha en eventos_fichas.
    // Se llama en creación, modificación, cambio de estado, despacho y cierre.
    //
    // @param int      $ficha_id         Ficha afectada (requerido)
    // @param int|null $usuario_id        Operador (NULL = acción automática del sistema)
    // @param string   $tipo_evento       CREACION | MODIFICACION | CAMBIO_ESTADO | PLAN_ACCION | DESPACHO | CIERRE
    // @param string|null $estado_anterior Estado previo de la ficha
    // @param string|null $estado_nuevo    Estado nuevo de la ficha
    // @param array|null  $anterior        Snapshot previo (JSON)
    // @param array|null  $nuevo           Snapshot nuevo (JSON)
    // @param string|null $descripcion     Nota legible
    //--------------------------------------------------------------------
    public function registrarEventoFicha(
        int     $ficha_id,
        ?int    $usuario_id      = null,
        string  $tipo_evento     = 'MODIFICACION',
        ?string $estado_anterior = null,
        ?string $estado_nuevo    = null,
        ?array  $anterior        = null,
        ?array  $nuevo           = null,
        ?string $descripcion     = null
    ): void {
        try {
            $query = "INSERT INTO {$this->tabla_fichas}
                        (ficha_id, usuario_id, tipo_evento, estado_anterior, estado_nuevo, valor_anterior, valor_nuevo, descripcion)
                      VALUES
                        (:ficha_id, :usuario_id, :tipo_evento, :estado_anterior, :estado_nuevo, :anterior, :nuevo, :descripcion)";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':ficha_id',        $ficha_id,       PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id',      $usuario_id,     $usuario_id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':tipo_evento',     $tipo_evento,    PDO::PARAM_STR);
            $stmt->bindValue(':estado_anterior', $estado_anterior, PDO::PARAM_STR);
            $stmt->bindValue(':estado_nuevo',    $estado_nuevo,   PDO::PARAM_STR);
            $stmt->bindValue(':anterior',        $anterior ? json_encode($anterior, JSON_UNESCAPED_UNICODE) : null, PDO::PARAM_STR);
            $stmt->bindValue(':nuevo',           $nuevo    ? json_encode($nuevo,    JSON_UNESCAPED_UNICODE) : null, PDO::PARAM_STR);
            $stmt->bindValue(':descripcion',     $descripcion,    PDO::PARAM_STR);
            $stmt->execute();
        } catch (\Exception $e) {
            error_log("[EventoModelo] Error en registrarEventoFicha: " . $e->getMessage());
        }
    }

    //--------------------------------------------------------------------
    // Retorna registros paginados de eventos_sistema compatible con DataTables.
    //
    // @param int    $inicio     Offset (primer registro de la página)
    // @param int    $cantidad   Registros por página
    // @param string $busqueda   Texto de búsqueda global
    // @param int    $colOrden   Índice de columna (0-4)
    // @param string $dirOrden   'asc' o 'desc'
    //--------------------------------------------------------------------
    public function obtenerPaginado(int $inicio, int $cantidad, string $busqueda, int $colOrden, string $dirOrden): array {
        $columnas = [
            0 => 'e.tipo_accion',
            1 => 'e.tabla_afectada',
            2 => 'e.registro_id',
            3 => 'u.usuario',
            4 => 'e.fecha',
        ];
        $columnaOrden = $columnas[$colOrden] ?? 'e.fecha';
        $dirOrden     = strtolower($dirOrden) === 'asc' ? 'ASC' : 'DESC';

        try {
            $busquedaLike = '%' . $busqueda . '%';
            $query = "SELECT 
                        e.id, 
                        e.usuario_id, 
                        e.tipo_accion, 
                        e.tabla_afectada, 
                        e.registro_id, 
                        e.valor_anterior, 
                        e.valor_nuevo, 
                        e.descripcion, 
                        e.fecha, 
                        u.usuario AS nombre_admin
                      FROM {$this->tabla_sistema} e
                      LEFT JOIN usuarios u ON e.usuario_id = u.id
                      WHERE (:busqueda = ''
                          OR e.tipo_accion      LIKE :b1
                          OR e.tabla_afectada   LIKE :b2
                          OR u.usuario          LIKE :b3
                          OR e.descripcion      LIKE :b4
                      )
                      ORDER BY {$columnaOrden} {$dirOrden}
                      LIMIT :cantidad OFFSET :inicio";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':busqueda', $busqueda,     PDO::PARAM_STR);
            $stmt->bindValue(':b1',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b2',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b3',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b4',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':cantidad', $cantidad,     PDO::PARAM_INT);
            $stmt->bindValue(':inicio',   $inicio,       PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[EventoModelo] Error en obtenerPaginado: " . $e->getMessage());
            return [];
        }
    }

    //--------------------------------------------------------------------
    // Retorna el total absoluto de registros en eventos_sistema.
    //--------------------------------------------------------------------
    public function contarTodos(): int {
        try {
            $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM {$this->tabla_sistema}");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("[EventoModelo] Error en contarTodos: " . $e->getMessage());
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
                      FROM {$this->tabla_sistema} e
                      LEFT JOIN usuarios u ON e.usuario_id = u.id
                      WHERE (:busqueda = ''
                          OR e.tipo_accion     LIKE :b1
                          OR e.tabla_afectada  LIKE :b2
                          OR u.usuario         LIKE :b3
                          OR e.descripcion     LIKE :b4
                      )";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':busqueda', $busqueda,     PDO::PARAM_STR);
            $stmt->bindValue(':b1',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b2',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b3',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b4',       $busquedaLike, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("[EventoModelo] Error en contarFiltrados: " . $e->getMessage());
            return 0;
        }
    }

    //--------------------------------------------------------------------
    // Obtiene todos los eventos de una ficha en orden cronológico.
    // Útil para mostrar el historial completo de una emergencia.
    //--------------------------------------------------------------------
    public function obtenerEventosPorFicha(int $ficha_id): array {
        try {
            $query = "SELECT ef.*,
                             u.nombre_completo AS nombre_operador,
                             u.usuario         AS usuario_operador
                      FROM {$this->tabla_fichas} ef
                      LEFT JOIN usuarios u ON ef.usuario_id = u.id
                      WHERE ef.ficha_id = :ficha_id
                      ORDER BY ef.fecha ASC";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':ficha_id', $ficha_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[EventoModelo] Error en obtenerEventosPorFicha: " . $e->getMessage());
            return [];
        }
    }

    //--------------------------------------------------------------------
    // Retorna registros paginados de eventos_fichas compatible con DataTables.
    //--------------------------------------------------------------------
    public function obtenerPaginadoFichas(int $inicio, int $cantidad, string $busqueda, int $colOrden, string $dirOrden): array {
        $columnas = [
            0 => 'ef.tipo_evento',
            1 => 'ef.ficha_id',
            2 => 'ef.estado_anterior',
            3 => 'ef.estado_nuevo',
            4 => 'u.usuario',
            5 => 'ef.fecha',
        ];
        $columnaOrden = $columnas[$colOrden] ?? 'ef.fecha';
        $dirOrden     = strtolower($dirOrden) === 'asc' ? 'ASC' : 'DESC';

        try {
            $busquedaLike = '%' . $busqueda . '%';
            $query = "SELECT 
                        ef.*, 
                        u.usuario AS nombre_admin
                      FROM {$this->tabla_fichas} ef
                      LEFT JOIN usuarios u ON ef.usuario_id = u.id
                      WHERE (:busqueda = ''
                          OR ef.tipo_evento      LIKE :b1
                          OR ef.estado_anterior  LIKE :b2
                          OR ef.estado_nuevo     LIKE :b3
                          OR u.usuario           LIKE :b4
                          OR ef.descripcion      LIKE :b5
                          OR ef.ficha_id         LIKE :b6
                      )
                      ORDER BY {$columnaOrden} {$dirOrden}
                      LIMIT :cantidad OFFSET :inicio";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':busqueda', $busqueda,     PDO::PARAM_STR);
            $stmt->bindValue(':b1',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b2',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b3',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b4',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b5',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b6',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':cantidad', $cantidad,     PDO::PARAM_INT);
            $stmt->bindValue(':inicio',   $inicio,       PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[EventoModelo] Error en obtenerPaginadoFichas: " . $e->getMessage());
            return [];
        }
    }

    //--------------------------------------------------------------------
    // Retorna el total absoluto de registros en eventos_fichas.
    //--------------------------------------------------------------------
    public function contarTodosFichas(): int {
        try {
            $stmt = $this->conexion->prepare("SELECT COUNT(*) FROM {$this->tabla_fichas}");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("[EventoModelo] Error en contarTodosFichas: " . $e->getMessage());
            return 0;
        }
    }

    //--------------------------------------------------------------------
    // Retorna el total de registros que coinciden con una búsqueda en fichas.
    //--------------------------------------------------------------------
    public function contarFiltradosFichas(string $busqueda): int {
        try {
            $busquedaLike = '%' . $busqueda . '%';
            $query = "SELECT COUNT(*)
                      FROM {$this->tabla_fichas} ef
                      LEFT JOIN usuarios u ON ef.usuario_id = u.id
                      WHERE (:busqueda = ''
                          OR ef.tipo_evento      LIKE :b1
                          OR ef.estado_anterior  LIKE :b2
                          OR ef.estado_nuevo     LIKE :b3
                          OR u.usuario           LIKE :b4
                          OR ef.descripcion      LIKE :b5
                          OR ef.ficha_id         LIKE :b6
                      )";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':busqueda', $busqueda,     PDO::PARAM_STR);
            $stmt->bindValue(':b1',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b2',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b3',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b4',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b5',       $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b6',       $busquedaLike, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("[EventoModelo] Error en contarFiltradosFichas: " . $e->getMessage());
            return 0;
        }
    }

    //--------------------------------------------------------------------
    // Historial completo de un usuario: acciones de sistema + fichas.
    // Retorna un array UNION ordenado por fecha descendente.
    //--------------------------------------------------------------------
    public function obtenerEventosPorUsuario(int $usuario_id): array {
        try {
            $query = "SELECT
                        'sistema'                       AS origen,
                        e.tipo_accion                   AS tipo,
                        e.tabla_afectada                AS contexto,
                        e.descripcion,
                        e.fecha
                      FROM eventos_sistema e
                      WHERE e.usuario_id = :uid1
                      UNION ALL
                      SELECT
                        'ficha'                         AS origen,
                        ef.tipo_evento                  AS tipo,
                        CONCAT('Ficha #', ef.ficha_id)  AS contexto,
                        ef.descripcion,
                        ef.fecha
                      FROM eventos_fichas ef
                      WHERE ef.usuario_id = :uid2
                      ORDER BY fecha DESC";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':uid1', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':uid2', $usuario_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[EventoModelo] Error en obtenerEventosPorUsuario: " . $e->getMessage());
            return [];
        }
    }
}

