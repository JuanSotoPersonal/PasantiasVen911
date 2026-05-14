<?php
/**
 * MODELO: EventoModelo
 * Propósito: Gestionar el registro y consulta de auditoría de todo el sistema.
 * Registra eventos generales (configuración, usuarios) y eventos de fichas (operatividad).
 */

namespace App\modelos;

use App\Config\Database;
use PDO;

require_once 'app/Config/Database.php';

class EventoModelo {

    // ///////////////////////////////////////////////////////////////////
    // 1. ATRIBUTOS Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    private $conexion;
    private string $tabla_sistema = 'eventos_sistema';
    private string $tabla_fichas  = 'eventos_fichas';

    /**
     * Constructor: Inicializa la conexión centralizada.
     */
    public function __construct() {
        $database = new Database();
        $this->conexion = $database->obtenerConexion();
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. MÉTODOS DE REGISTRO (LOGGING)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Proxy Asíncrono: Encola un evento general del sistema en RabbitMQ.
     */
    public function registrarEvento(
        ?int    $usuario_id,
        string  $tipo_accion,
        string  $tabla,
        ?int    $registro_id = null,
        ?array  $anterior    = null,
        ?array  $nuevo       = null,
        ?string $descripcion = null
    ): void {
        require_once 'app/Helpers/Notificador.php';
        \App\Helpers\Notificador::encolarTrabajo([
            'action' => 'registrar_auditoria_sistema',
            'datos' => [
                'usuario_id'  => $usuario_id,
                'tipo_accion' => $tipo_accion,
                'tabla'       => $tabla,
                'registro_id' => $registro_id,
                'anterior'    => $anterior,
                'nuevo'       => $nuevo,
                'descripcion' => $descripcion
            ]
        ]);
    }

    /**
     * (Método Interno / Worker) Inserta realmente el log en la BD.
     */
    public function insertarEventoSistemaReal(
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
            error_log("[EventoModelo] Error en insertarEventoSistemaReal: " . $e->getMessage());
        }
    }

    /**
     * Proxy Asíncrono: Encola un evento de ficha en RabbitMQ.
     */
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
        require_once 'app/Helpers/Notificador.php';
        \App\Helpers\Notificador::encolarTrabajo([
            'action' => 'registrar_auditoria_ficha',
            'datos' => [
                'ficha_id'        => $ficha_id,
                'usuario_id'      => $usuario_id,
                'tipo_evento'     => $tipo_evento,
                'estado_anterior' => $estado_anterior,
                'estado_nuevo'    => $estado_nuevo,
                'anterior'        => $anterior,
                'nuevo'           => $nuevo,
                'descripcion'     => $descripcion
            ]
        ]);
    }

    /**
     * (Método Interno / Worker) Inserta realmente el evento de la ficha en la BD.
     */
    public function insertarEventoFichaReal(
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
            error_log("[EventoModelo] Error en insertarEventoFichaReal: " . $e->getMessage());
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. AUDITORÍA DEL SISTEMA (DATATABLES)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Consulta paginada para la tabla de eventos globales del sistema.
     */
    public function obtenerPaginado(int $inicio, int $cantidad, string $busqueda, int $colOrden, string $dirOrden): array {
        $columnas = [
            0 => 'e.tipo_accion', 1 => 'e.tabla_afectada', 2 => 'e.registro_id', 
            3 => 'u.usuario', 4 => 'e.fecha',
        ];
        $columnaOrden = $columnas[$colOrden] ?? 'e.fecha';
        $dirOrden     = strtolower($dirOrden) === 'asc' ? 'ASC' : 'DESC';

        try {
            $busquedaLike = '%' . $busqueda . '%';
            $query = "SELECT 
                        e.id, e.usuario_id, e.tipo_accion, e.tabla_afectada, e.registro_id, 
                        e.valor_anterior, e.valor_nuevo, e.descripcion, e.fecha, 
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

    /**
     * Conteo total de registros de sistema.
     */
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

    /**
     * Conteo filtrado de registros de sistema.
     */
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

    // ///////////////////////////////////////////////////////////////////
    // 4. HISTORIAL DE FICHAS (DETALLES Y DATATABLES)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Obtiene el flujo cronológico completo de una sola ficha.
     */
    public function obtenerEventosPorFicha(int $ficha_id): array {
        try {
            $query = "SELECT ef.id, ef.ficha_id, ef.usuario_id, ef.tipo_evento, ef.estado_anterior,
                             ef.estado_nuevo, ef.valor_anterior, ef.valor_nuevo, ef.descripcion, ef.fecha,
                             u.nombre_completo AS nombre_operador,
                             u.usuario         AS usuario_operador
                      FROM {$this->tabla_fichas} ef
                      LEFT JOIN usuarios u ON ef.usuario_id = u.id
                      WHERE ef.ficha_id = :ficha_id
                      ORDER BY ef.fecha ASC
                      LIMIT 500";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':ficha_id', $ficha_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("[EventoModelo] Error en obtenerEventosPorFicha: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Consulta paginada para la tabla general de eventos de fichas.
     */
    public function obtenerPaginadoFichas(int $inicio, int $cantidad, string $busqueda, int $colOrden, string $dirOrden): array {
        $columnas = [
            0 => 'ef.tipo_evento', 1 => 'ef.ficha_id', 2 => 'ef.estado_anterior',
            3 => 'ef.estado_nuevo', 4 => 'u.usuario', 5 => 'ef.fecha',
        ];
        $columnaOrden = $columnas[$colOrden] ?? 'ef.fecha';
        $dirOrden     = strtolower($dirOrden) === 'asc' ? 'ASC' : 'DESC';

        try {
            $busquedaLike = '%' . $busqueda . '%';
            $query = "SELECT ef.id, ef.ficha_id, ef.usuario_id, ef.tipo_evento, ef.estado_anterior,
                             ef.estado_nuevo, ef.valor_anterior, ef.valor_nuevo, ef.descripcion, ef.fecha,
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

    /**
     * Conteo total de registros en el historial de fichas.
     */
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

    /**
     * Conteo filtrado de registros en el historial de fichas.
     */
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

    // ///////////////////////////////////////////////////////////////////
    // 5. HISTORIAL INTEGRADO POR USUARIO
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna el historial combinado de un usuario (Acciones de Sistema + Fichas).
     * Utiliza UNION ALL para consolidar la línea de tiempo del operador.
     * Limitado a los últimos 100 eventos para evitar full scan sin cota.
     */
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
                      ORDER BY fecha DESC
                      LIMIT 100";

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
