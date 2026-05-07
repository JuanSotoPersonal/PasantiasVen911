<?php
/**
 * MODELO: DespachoModelo
 * Propósito: Gestionar el flujo operativo del Centro de Despacho.
 * 
 * FLUJO DE NEGOCIO:
 * 1. El Operador crea una ficha (estado: Pendiente)
 * 2. El Despachador ve TODAS las fichas Pendientes y En Proceso (sin filtro de usuario)
 * 3. El Despachador "toma" una ficha → id_owner = usuario actual, estado → En Proceso
 * 4. Desde la ficha tomada, el Despachador asigna organismos de respuesta
 * 5. El Despachador avanza el estatus de cada despacho (Asignado → En Camino → En Sitio → Liberado)
 * 6. Solo Despachador y Administrador pueden cambiar estados de fichas
 */

namespace App\modelos;

use App\Config\Database;
use PDO;
use Exception;

require_once 'app/Config/Database.php';

class DespachoModelo {

    // ///////////////////////////////////////////////////////////////////
    // 1. ATRIBUTOS Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    private $conexion;

    /**
     * Inicializa la conexión centralizada a la base de datos.
     */
    public function __construct() {
        $database       = new Database();
        $this->conexion = $database->obtenerConexion();
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. TABLA PRINCIPAL: FICHAS ACTIVAS (PENDIENTE + EN PROCESO)
    //    Estas consultas alimentan la DataTable principal del módulo.
    //    Son globales: todos los despachadores ven todas las fichas activas.
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna fichas en estado Pendiente y En Proceso paginadas para el DataTable.
     * La tabla es global: cualquier despachador de cualquier turno puede operar sobre ellas.
     */
    public function obtenerFichasPaginado(
        int    $inicio,
        int    $cantidad,
        string $busqueda,
        int    $colOrden,
        string $dirOrden
    ): array {
        $columnas = [
            0 => 'f.id',
            1 => 's.nombre_solicitante',
            2 => 'c.nombre_caso',
            3 => 'p.nombre_parroquia',
            4 => 'f.estado_ficha',
            5 => 'u_owner.nombre_completo',
            6 => 'f.fecha_creacion',
        ];
        $columnaOrden = $columnas[$colOrden] ?? 'f.fecha_creacion';
        $dirOrden     = strtolower($dirOrden) === 'asc' ? 'ASC' : 'DESC';

        try {
            $busquedaLike = '%' . $busqueda . '%';

            $query = "SELECT
                        f.id,
                        f.estado_ficha,
                        f.descripcion_caso,
                        f.direccion_exacta,
                        f.fecha_creacion,
                        f.id_owner,
                        s.nombre_solicitante,
                        s.telefono1,
                        c.nombre_caso,
                        t.nombre              AS tipo_emergencia,
                        p.nombre_parroquia,
                        m.nombre_municipio,
                        u_owner.nombre_completo AS nombre_owner,
                        COALESCE(dc.total_despachos, 0) AS total_despachos
                      FROM fichas_emergencia f
                      INNER JOIN solicitantes       s       ON f.solicitante_id      = s.id
                      INNER JOIN casos              c       ON f.caso_id             = c.id
                      INNER JOIN tipos_emergencia   t       ON c.tipo_emergencia_id  = t.id
                      INNER JOIN parroquias         p       ON f.parroquia_id        = p.id
                      INNER JOIN municipios         m       ON p.municipio_id        = m.id
                      LEFT  JOIN usuarios           u_owner ON f.id_owner            = u_owner.id
                      LEFT  JOIN (
                          SELECT ficha_id, COUNT(*) AS total_despachos
                          FROM despachos_organismos
                          GROUP BY ficha_id
                      ) dc ON dc.ficha_id = f.id
                      WHERE f.estado_ficha IN ('Pendiente', 'En Proceso')
                        AND (:busqueda = ''
                          OR s.nombre_solicitante  LIKE :b1
                          OR c.nombre_caso         LIKE :b2
                          OR p.nombre_parroquia    LIKE :b3
                          OR f.descripcion_caso    LIKE :b4
                          OR f.id                  LIKE :b5
                          OR u_owner.nombre_completo LIKE :b6
                        )
                      ORDER BY FIELD(f.estado_ficha, 'Pendiente', 'En Proceso'), {$columnaOrden} {$dirOrden}
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
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en obtenerFichasPaginado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Total de fichas activas (Pendiente + En Proceso) sin filtro de búsqueda.
     */
    public function contarFichas(): int {
        try {
            $stmt = $this->conexion->prepare(
                "SELECT COUNT(*) FROM fichas_emergencia
                 WHERE estado_ficha IN ('Pendiente', 'En Proceso')"
            );
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en contarFichas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Total de fichas activas aplicando filtro de búsqueda.
     */
    public function contarFichasFiltradas(string $busqueda): int {
        try {
            $busquedaLike = '%' . $busqueda . '%';
            $query = "SELECT COUNT(*)
                      FROM fichas_emergencia f
                      INNER JOIN solicitantes     s ON f.solicitante_id = s.id
                      INNER JOIN casos            c ON f.caso_id        = c.id
                      INNER JOIN parroquias       p ON f.parroquia_id   = p.id
                      LEFT  JOIN usuarios         u ON f.id_owner       = u.id
                      WHERE f.estado_ficha IN ('Pendiente', 'En Proceso')
                        AND (:busqueda = ''
                          OR s.nombre_solicitante LIKE :b1
                          OR c.nombre_caso        LIKE :b2
                          OR p.nombre_parroquia   LIKE :b3
                          OR f.descripcion_caso   LIKE :b4
                          OR f.id                 LIKE :b5
                          OR u.nombre_completo    LIKE :b6
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
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en contarFichasFiltradas: " . $e->getMessage());
            return 0;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. TABLA PROPIA: FICHAS EN PROCESO TOMADAS POR EL DESPACHADOR ACTUAL
    //    Estas consultas alimentan la segunda DataTable del módulo.
    //    Filtran por id_owner = usuario actual para mostrar solo sus fichas activas.
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna fichas En Proceso donde id_owner es el despachador actual.
     * Permite al despachador ver su carga de trabajo personal sin
     * perder visibilidad de la cola global.
     */
    public function obtenerFichasPropiasP­aginado(
        int    $usuarioId,
        int    $inicio,
        int    $cantidad,
        string $busqueda,
        int    $colOrden,
        string $dirOrden
    ): array {
        $columnas = [
            0 => 'f.id',
            1 => 's.nombre_solicitante',
            2 => 'c.nombre_caso',
            3 => 'p.nombre_parroquia',
            4 => 'f.estado_ficha',
            5 => 'u_owner.nombre_completo',
            6 => 'f.fecha_creacion',
        ];
        $columnaOrden = $columnas[$colOrden] ?? 'f.fecha_creacion';
        $dirOrden     = strtolower($dirOrden) === 'asc' ? 'ASC' : 'DESC';

        try {
            $busquedaLike = '%' . $busqueda . '%';

            $query = "SELECT
                        f.id,
                        f.estado_ficha,
                        f.descripcion_caso,
                        f.direccion_exacta,
                        f.fecha_creacion,
                        f.id_owner,
                        s.nombre_solicitante,
                        s.telefono1,
                        c.nombre_caso,
                        t.nombre              AS tipo_emergencia,
                        p.nombre_parroquia,
                        m.nombre_municipio,
                        u_owner.nombre_completo AS nombre_owner,
                        COALESCE(dc.total_despachos, 0) AS total_despachos
                      FROM fichas_emergencia f
                      INNER JOIN solicitantes       s       ON f.solicitante_id      = s.id
                      INNER JOIN casos              c       ON f.caso_id             = c.id
                      INNER JOIN tipos_emergencia   t       ON c.tipo_emergencia_id  = t.id
                      INNER JOIN parroquias         p       ON f.parroquia_id        = p.id
                      INNER JOIN municipios         m       ON p.municipio_id        = m.id
                      LEFT  JOIN usuarios           u_owner ON f.id_owner            = u_owner.id
                      LEFT  JOIN (
                          SELECT ficha_id, COUNT(*) AS total_despachos
                          FROM despachos_organismos
                          GROUP BY ficha_id
                      ) dc ON dc.ficha_id = f.id
                      WHERE f.estado_ficha IN ('Pendiente', 'En Proceso')
                        AND f.id_owner = :usuario_id
                        AND (:busqueda = ''
                          OR s.nombre_solicitante   LIKE :b1
                          OR c.nombre_caso          LIKE :b2
                          OR p.nombre_parroquia     LIKE :b3
                          OR f.descripcion_caso     LIKE :b4
                          OR f.id                   LIKE :b5
                        )
                      ORDER BY FIELD(f.estado_ficha, 'Pendiente', 'En Proceso'), {$columnaOrden} {$dirOrden}
                      LIMIT :cantidad OFFSET :inicio";

            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':usuario_id', $usuarioId,    PDO::PARAM_INT);
            $stmt->bindValue(':busqueda',   $busqueda,     PDO::PARAM_STR);
            $stmt->bindValue(':b1',         $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b2',         $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b3',         $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b4',         $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b5',         $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':cantidad',   $cantidad,     PDO::PARAM_INT);
            $stmt->bindValue(':inicio',     $inicio,       PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en obtenerFichasPropiasP­aginado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Total de fichas activas del despachador actual (sin filtro de búsqueda).
     */
    public function contarFichasPropias(int $usuarioId): int {
        try {
            $stmt = $this->conexion->prepare(
                "SELECT COUNT(*) FROM fichas_emergencia
                 WHERE estado_ficha IN ('Pendiente', 'En Proceso')
                   AND id_owner = :usuario_id"
            );
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en contarFichasPropias: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Total de fichas propias del despachador aplicando filtro de búsqueda.
     */
    public function contarFichasPropiasF­iltradas(int $usuarioId, string $busqueda): int {
        try {
            $busquedaLike = '%' . $busqueda . '%';
            $query = "SELECT COUNT(*)
                      FROM fichas_emergencia f
                      INNER JOIN solicitantes s ON f.solicitante_id = s.id
                      INNER JOIN casos        c ON f.caso_id        = c.id
                      INNER JOIN parroquias   p ON f.parroquia_id   = p.id
                      WHERE f.estado_ficha IN ('Pendiente', 'En Proceso')
                        AND f.id_owner = :usuario_id
                        AND (:busqueda = ''
                          OR s.nombre_solicitante LIKE :b1
                          OR c.nombre_caso        LIKE :b2
                          OR p.nombre_parroquia   LIKE :b3
                          OR f.descripcion_caso   LIKE :b4
                          OR f.id                 LIKE :b5
                        )";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':usuario_id', $usuarioId,    PDO::PARAM_INT);
            $stmt->bindValue(':busqueda',   $busqueda,     PDO::PARAM_STR);
            $stmt->bindValue(':b1',         $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b2',         $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b3',         $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b4',         $busquedaLike, PDO::PARAM_STR);
            $stmt->bindValue(':b5',         $busquedaLike, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en contarFichasPropiasF­iltradas: " . $e->getMessage());
            return 0;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. ACCIÓN DE TOMAR FICHA
    //    Registra que un despachador asume la responsabilidad de la ficha.
    // ///////////////////////////////////////////////////////////////////


    /**
     * El despachador "toma" una ficha pendiente.
     * Actualiza id_owner al usuario actual y cambia estado a "En Proceso".
     * Esta acción es idempotente: si la ficha ya está En Proceso, solo actualiza el owner.
     *
     * @param int $fichaId   ID de la ficha a tomar.
     * @param int $usuarioId ID del despachador que toma la ficha.
     */
    public function tomarFicha(int $fichaId, int $usuarioId): bool {
        try {
            $stmt = $this->conexion->prepare(
                "UPDATE fichas_emergencia
                 SET estado_ficha = 'En Proceso',
                     id_owner     = :id_owner
                 WHERE id = :id
                   AND estado_ficha IN ('Pendiente', 'En Proceso')"
            );
            $stmt->bindValue(':id_owner', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':id',       $fichaId,   PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en tomarFicha: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el estado y el id_owner actual de una ficha.
     * Usado para validar elegibilidad antes de operaciones de despacho.
     */
    public function obtenerInfoFicha(int $fichaId): array|false {
        try {
            $stmt = $this->conexion->prepare(
                "SELECT id, estado_ficha, id_owner, id_user FROM fichas_emergencia WHERE id = :id LIMIT 1"
            );
            $stmt->bindValue(':id', $fichaId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en obtenerInfoFicha: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el detalle completo de una ficha con todos sus JOINs.
     * Usado para cargar el resumen en el modal de gestión de despachos.
     */
    public function obtenerFichaPorId(int $fichaId): array|false {
        try {
            $query = "SELECT
                        f.id,
                        f.estado_ficha,
                        f.descripcion_caso,
                        f.direccion_exacta,
                        f.fecha_creacion,
                        f.id_owner,
                        s.nombre_solicitante,
                        s.telefono1,
                        s.telefono2,
                        c.nombre_caso,
                        t.nombre              AS tipo_emergencia,
                        p.nombre_parroquia,
                        m.nombre_municipio,
                        u_creador.nombre_completo AS nombre_creador,
                        u_owner.nombre_completo   AS nombre_owner
                      FROM fichas_emergencia f
                      INNER JOIN solicitantes     s         ON f.solicitante_id    = s.id
                      INNER JOIN casos            c         ON f.caso_id           = c.id
                      INNER JOIN tipos_emergencia t         ON c.tipo_emergencia_id = t.id
                      INNER JOIN parroquias       p         ON f.parroquia_id      = p.id
                      INNER JOIN municipios       m         ON p.municipio_id      = m.id
                      LEFT  JOIN usuarios         u_creador ON f.id_user           = u_creador.id
                      LEFT  JOIN usuarios         u_owner   ON f.id_owner          = u_owner.id
                      WHERE f.id = :id LIMIT 1";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':id', $fichaId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en obtenerFichaPorId: " . $e->getMessage());
            return false;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. DESPACHOS POR FICHA
    //    Gestión de los organismos asignados a una ficha específica.
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna todos los despachos (organismos) asignados a una ficha.
     * Incluye nombre del organismo y del despachador responsable.
     */
    public function obtenerDespachosDeFicha(int $fichaId): array {
        try {
            $query = "SELECT
                        d.id,
                        d.ficha_id,
                        d.unidad_designada,
                        d.mando_acargo,
                        d.persona_atiende,
                        d.hora_despacho,
                        d.estatus_despacho,
                        d.motivo_cancelacion,
                        o.nombre_organismo,
                        u.nombre_completo AS nombre_despachador
                      FROM despachos_organismos d
                      INNER JOIN organismos o ON d.organismo_id  = o.id
                      LEFT  JOIN usuarios   u ON d.despachador_id = u.id
                      WHERE d.ficha_id = :ficha_id
                      ORDER BY d.hora_despacho ASC";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':ficha_id', $fichaId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en obtenerDespachosDeFicha: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el detalle de un despacho individual por su ID.
     */
    public function obtenerPorId(int $id): array|false {
        try {
            $query = "SELECT d.*, o.nombre_organismo, u.nombre_completo AS nombre_despachador
                      FROM despachos_organismos d
                      INNER JOIN organismos o ON d.organismo_id  = o.id
                      LEFT  JOIN usuarios   u ON d.despachador_id = u.id
                      WHERE d.id = :id LIMIT 1";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en obtenerPorId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra un nuevo despacho de organismo sobre una ficha.
     */
    public function crear(array $datos): int|false {
        try {
            $query = "INSERT INTO despachos_organismos
                        (ficha_id, organismo_id, unidad_designada, mando_acargo, persona_atiende, estatus_despacho, despachador_id)
                      VALUES
                        (:ficha_id, :organismo_id, :unidad, :mando, :persona, 'Asignado', :despachador_id)";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':ficha_id',      $datos['ficha_id'],      PDO::PARAM_INT);
            $stmt->bindValue(':organismo_id',  $datos['organismo_id'],  PDO::PARAM_INT);
            $stmt->bindValue(':unidad',        $datos['unidad_designada'],  PDO::PARAM_STR);
            $stmt->bindValue(':mando',         $datos['mando_acargo'],  PDO::PARAM_STR);
            $stmt->bindValue(':persona',       $datos['persona_atiende'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':despachador_id', $datos['despachador_id'], PDO::PARAM_INT);
            $stmt->execute();
            return (int)$this->conexion->lastInsertId();
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en crear: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos operativos de campo de un despacho existente.
     */
    public function actualizar(int $id, array $datos): bool {
        try {
            $stmt = $this->conexion->prepare(
                "UPDATE despachos_organismos SET
                    unidad_designada = :unidad,
                    mando_acargo     = :mando,
                    persona_atiende  = :persona
                 WHERE id = :id"
            );
            $stmt->bindValue(':unidad',  $datos['unidad_designada'], PDO::PARAM_STR);
            $stmt->bindValue(':mando',   $datos['mando_acargo'],     PDO::PARAM_STR);
            $stmt->bindValue(':persona', $datos['persona_atiende'] ?? null, PDO::PARAM_STR);
            $stmt->bindValue(':id',      $id,                        PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en actualizar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Avanza el estatus de un despacho: Asignado → En Camino → En Sitio → Liberado.
     * Valida que el valor sea uno de los estatus permitidos por el enum.
     */
    public function cambiarEstado(int $id, string $nuevoEstado): bool {
        $estatusValidos = ['Asignado', 'En Camino', 'En Sitio', 'Liberado'];
        if (!in_array($nuevoEstado, $estatusValidos, true)) {
            return false;
        }
        try {
            $stmt = $this->conexion->prepare(
                "UPDATE despachos_organismos SET estatus_despacho = :estado WHERE id = :id"
            );
            $stmt->bindValue(':estado', $nuevoEstado, PDO::PARAM_STR);
            $stmt->bindValue(':id',     $id,          PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en cambiarEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancela un despacho de organismo activo (no Liberado ni ya Cancelado).
     * Persiste el estado 'Cancelado' y registra el motivo de cancelación.
     *
     * @param int    $id               ID del despacho a cancelar.
     * @param string $motivoCancelacion Motivo estructurado del catálogo.
     * @param string $descripcion      Descripción libre opcional del operador.
     */
    public function cancelar(int $id, string $motivoCancelacion, string $descripcion = ''): bool {
        try {
            $motivoCompleto = $descripcion !== ''
                ? "{$motivoCancelacion}: {$descripcion}"
                : $motivoCancelacion;

            $stmt = $this->conexion->prepare(
                "UPDATE despachos_organismos
                 SET estatus_despacho    = 'Cancelado',
                     motivo_cancelacion  = :motivo
                 WHERE id = :id
                   AND estatus_despacho NOT IN ('Liberado', 'Cancelado')"
            );
            $stmt->bindValue(':motivo', $motivoCompleto, PDO::PARAM_STR);
            $stmt->bindValue(':id',     $id,             PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en cancelar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cuenta los despachos de una ficha que aún NO están en estado terminal.
     * Un despacho es "activo" si está en: Asignado, En Camino, En Sitio.
     * Liberado y Cancelado son terminales y se consideran resueltos.
     * Se usa para bloquear el cierre de fichas con organismos pendientes.
     */
    public function contarDespachosActivos(int $fichaId): int {
        try {
            $stmt = $this->conexion->prepare(
                "SELECT COUNT(*) FROM despachos_organismos
                 WHERE ficha_id = :ficha_id
                   AND estatus_despacho NOT IN ('Liberado', 'Cancelado')"
            );
            $stmt->bindValue(':ficha_id', $fichaId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en contarDespachosActivos: " . $e->getMessage());
            return 0;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 5. CATÁLOGO DE ORGANISMOS
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna organismos activos para el selector del modal de asignación.
     */
    public function obtenerOrganismos(): array {
        try {
            $stmt = $this->conexion->prepare(
                "SELECT id, nombre_organismo FROM organismos WHERE estado = 1 ORDER BY nombre_organismo ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[DespachoModelo] Error en obtenerOrganismos: " . $e->getMessage());
            return [];
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 6. EDICIÓN OPERACIONAL DE FICHA (DESDE EL MÓDULO DE DESPACHO)
    //    Solo actualiza los campos que el despachador puede ajustar
    //    durante el manejo de la emergencia, sin cascadas de catálogos.
    // ///////////////////////////////////////////////////////////////////

    /**
     * Actualiza los campos operacionales de una ficha desde el módulo de despacho.
     * Modifica: descripcion_caso, direccion_exacta en fichas_emergencia
     *           telefono1, telefono2 en solicitantes (via solicitante_id).
     * Registra id_owner para mantener la trazabilidad del despachador activo.
     *
     * @param int   $fichaId      ID de la ficha a actualizar.
     * @param array $datos        Campos a actualizar (descripcion, direccion, tel1, tel2).
     * @param int   $usuarioId    Despachador que realiza la edición (nuevo id_owner).
     */
    public function actualizarFichaOperacional(int $fichaId, array $datos, int $usuarioId): bool {
        try {
            $this->conexion->beginTransaction();

            // 6.1 Actualizar campos de la ficha
            $stmt = $this->conexion->prepare(
                "UPDATE fichas_emergencia
                 SET descripcion_caso = :descripcion,
                     direccion_exacta = :direccion,
                     id_owner         = :id_owner
                 WHERE id = :id"
            );
            $stmt->bindValue(':descripcion', $datos['descripcion_caso'], \PDO::PARAM_STR);
            $stmt->bindValue(':direccion',   $datos['direccion_exacta'], \PDO::PARAM_STR);
            $stmt->bindValue(':id_owner',    $usuarioId,                 \PDO::PARAM_INT);
            $stmt->bindValue(':id',          $fichaId,                   \PDO::PARAM_INT);
            $stmt->execute();

            // 6.2 Actualizar teléfonos del solicitante (identificado via FK de la ficha)
            $stmtTel = $this->conexion->prepare(
                "UPDATE solicitantes s
                 INNER JOIN fichas_emergencia f ON f.solicitante_id = s.id
                 SET s.telefono1 = :tel1,
                     s.telefono2 = :tel2
                 WHERE f.id = :ficha_id"
            );
            $stmtTel->bindValue(':tel1',     $datos['telefono1'],         \PDO::PARAM_STR);
            $stmtTel->bindValue(':tel2',     $datos['telefono2'] ?? null, \PDO::PARAM_STR);
            $stmtTel->bindValue(':ficha_id', $fichaId,                    \PDO::PARAM_INT);
            $stmtTel->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("[DespachoModelo] Error en actualizarFichaOperacional: " . $e->getMessage());
            return false;
        }
    }
}

