<?php
/**
 * MODELO: FichaModelo
 * Propósito: Gestionar el ciclo de vida completo de las Fichas de Emergencia,
 * incluyendo la gestión de solicitantes, catálogos geográficos y organismos.
 */

namespace App\modelos;

use App\Config\Database;
use PDO;
use Exception;

require_once 'app/Config/Database.php';

class FichaModelo {

    // ///////////////////////////////////////////////////////////////////
    // 1. ATRIBUTOS Y CONSTRUCTOR
    // ///////////////////////////////////////////////////////////////////

    private $conexion;

    /**
     * Inicializa la conexión centralizada a la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->conexion = $database->obtenerConexion();
    }

    // ///////////////////////////////////////////////////////////////////
    // 2. FICHAS — PAGINACIÓN (SERVER-SIDE)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Retorna fichas paginadas compatibles con DataTables.
     * Implementa filtros por estado, búsqueda global y visibilidad por rol.
     */
    public function obtenerPaginado(
        int    $inicio,
        int    $cantidad,
        string $busqueda,
        int    $colOrden,
        string $dirOrden,
        string $estado = 'todos',
        int    $usuarioId = 0,
        int    $rolId = 0
    ): array {
        $columnas = [
            0 => 'f.id',
            1 => 'solicitante.nombre_solicitante',
            2 => 'c.nombre_caso',
            3 => 'p.nombre_parroquia',
            4 => 'f.estado_ficha',
            5 => 'f.fecha_creacion',
        ];
        $columnaOrden = $columnas[$colOrden] ?? 'f.fecha_creacion';
        $dirOrden     = strtolower($dirOrden) === 'asc' ? 'ASC' : 'DESC';

        try {
            $busquedaLike = '%' . $busqueda . '%';

            // 2.1 Definición de condiciones dinámicas
            $condiciones = ['1=1'];
            if ($estado !== 'todos') {
                $condiciones[] = 'f.estado_ficha = :estado';
            }
            
            // Regla de negocio: El operador (Rol 2) solo visualiza su propia gestión
            if ($rolId == 2 && $usuarioId > 0) {
                $condiciones[] = 'f.id_user = :id_user';
            }

            $where = implode(' AND ', $condiciones);

            $query = "SELECT
                        f.id,
                        f.estado_ficha,
                        f.direccion_exacta,
                        f.descripcion_caso,
                        f.fecha_creacion,
                        f.hora_cierre,
                        solicitante.nombre_solicitante,
                        solicitante.telefono1,
                        solicitante.cedula AS cedula_solicitante,
                        c.nombre_caso,
                        t.nombre AS tipo_emergencia,
                        p.nombre_parroquia,
                        m.nombre_municipio,
                        creador.nombre_completo AS nombre_creador,
                        f.id_user,
                        f.id_owner
                      FROM fichas_emergencia f
                      INNER JOIN solicitantes     solicitante ON f.solicitante_id = solicitante.id
                      INNER JOIN casos             c           ON f.caso_id        = c.id
                      INNER JOIN tipos_emergencia  t           ON c.tipo_emergencia_id = t.id
                      INNER JOIN parroquias         p           ON f.parroquia_id   = p.id
                      INNER JOIN municipios         m           ON p.municipio_id   = m.id
                      LEFT  JOIN usuarios           creador     ON f.id_user        = creador.id
                      WHERE {$where}
                        AND (:busqueda = ''
                          OR f.id                        LIKE :b1
                          OR solicitante.nombre_solicitante LIKE :b2
                          OR c.nombre_caso               LIKE :b3
                          OR t.nombre                    LIKE :b4
                          OR p.nombre_parroquia          LIKE :b5
                          OR f.estado_ficha              LIKE :b6
                        )
                      ORDER BY {$columnaOrden} {$dirOrden}
                      LIMIT :cantidad OFFSET :inicio";

            $stmt = $this->conexion->prepare($query);
            if ($estado !== 'todos') {
                $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            }
            if ($rolId == 2 && $usuarioId > 0) {
                $stmt->bindValue(':id_user', $usuarioId, PDO::PARAM_INT);
            }
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
            error_log("[FichaModelo] Error en obtenerPaginado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conteo total de fichas sin filtros de búsqueda (Total absoluto).
     */
    public function contarTodos(string $estado = 'todos', int $usuarioId = 0, int $rolId = 0): int {
        try {
            $condiciones = ['1=1'];
            if ($estado !== 'todos') {
                $condiciones[] = 'f.estado_ficha = :estado';
            }
            if ($rolId == 2 && $usuarioId > 0) {
                $condiciones[] = 'f.id_user = :id_user';
            }
            $where = implode(' AND ', $condiciones);

            $stmt = $this->conexion->prepare(
                "SELECT COUNT(*) FROM fichas_emergencia f WHERE {$where}"
            );
            if ($estado !== 'todos') {
                $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            }
            if ($rolId == 2 && $usuarioId > 0) {
                $stmt->bindValue(':id_user', $usuarioId, PDO::PARAM_INT);
            }
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("[FichaModelo] Error en contarTodos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Conteo de fichas aplicando los filtros de búsqueda (Total filtrado).
     */
    public function contarFiltrados(string $busqueda, string $estado = 'todos', int $usuarioId = 0, int $rolId = 0): int {
        try {
            $busquedaLike = '%' . $busqueda . '%';
            $condiciones = ['1=1'];
            if ($estado !== 'todos') $condiciones[] = 'f.estado_ficha = :estado';
            if ($rolId == 2 && $usuarioId > 0) $condiciones[] = 'f.id_user = :id_user';
            $where = implode(' AND ', $condiciones);

            $query = "SELECT COUNT(*)
                      FROM fichas_emergencia f
                      INNER JOIN solicitantes    s  ON f.solicitante_id = s.id
                      INNER JOIN casos           c  ON f.caso_id = c.id
                      INNER JOIN tipos_emergencia t  ON c.tipo_emergencia_id = t.id
                      INNER JOIN parroquias       p  ON f.parroquia_id = p.id
                      WHERE {$where}
                        AND (:busqueda = ''
                          OR f.id           LIKE :b1
                          OR s.nombre_solicitante LIKE :b2
                          OR c.nombre_caso   LIKE :b3
                          OR t.nombre        LIKE :b4
                          OR p.nombre_parroquia LIKE :b5
                          OR f.estado_ficha  LIKE :b6
                        )";
            $stmt = $this->conexion->prepare($query);
            if ($estado !== 'todos') $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            if ($rolId == 2 && $usuarioId > 0) $stmt->bindValue(':id_user', $usuarioId, PDO::PARAM_INT);
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
            error_log("[FichaModelo] Error en contarFiltrados: " . $e->getMessage());
            return 0;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 3. FICHAS — CRUD (LECTURA/ESCRITURA)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Obtiene el detalle completo de una ficha por su ID.
     */
    public function obtenerPorId(int $id): array|false {
        try {
            $query = "SELECT f.*,
                        solicitante.nombre_solicitante, solicitante.cedula AS cedula_solicitante,
                        solicitante.telefono1, solicitante.telefono2,
                        c.nombre_caso, c.tipo_emergencia_id,
                        t.nombre AS tipo_emergencia,
                        p.nombre_parroquia, p.municipio_id,
                        m.nombre_municipio
                      FROM fichas_emergencia f
                      INNER JOIN solicitantes    solicitante ON f.solicitante_id = solicitante.id
                      INNER JOIN casos             c          ON f.caso_id        = c.id
                      INNER JOIN tipos_emergencia  t          ON c.tipo_emergencia_id = t.id
                      INNER JOIN parroquias         p          ON f.parroquia_id   = p.id
                      INNER JOIN municipios         m          ON p.municipio_id   = m.id
                      WHERE f.id = :id LIMIT 1";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[FichaModelo] Error en obtenerPorId: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea una nueva ficha. Implementa transaccionalidad para asegurar
     * la integridad entre el solicitante y la ficha.
     */
    public function crear(array $datos): int|false {
        try {
            $this->conexion->beginTransaction();

            $solicitanteId = $this->guardarSolicitante($datos);
            if (!$solicitanteId) {
                $this->conexion->rollBack();
                return false;
            }

            $query = "INSERT INTO fichas_emergencia
                        (parroquia_id, direccion_exacta, caso_id, descripcion_caso, solicitante_id, id_user, estado_ficha)
                      VALUES
                        (:parroquia_id, :direccion, :caso_id, :descripcion, :solicitante_id, :id_user, 'Pendiente')";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':parroquia_id',   $datos['parroquia_id'],   PDO::PARAM_INT);
            $stmt->bindValue(':direccion',       $datos['direccion_exacta'], PDO::PARAM_STR);
            $stmt->bindValue(':caso_id',         $datos['caso_id'],        PDO::PARAM_INT);
            $stmt->bindValue(':descripcion',     $datos['descripcion_caso'], PDO::PARAM_STR);
            $stmt->bindValue(':solicitante_id',  $solicitanteId,           PDO::PARAM_INT);
            $stmt->bindValue(':id_user',         $datos['id_user'],        PDO::PARAM_INT);
            $stmt->execute();

            $fichaId = (int)$this->conexion->lastInsertId();
            $this->conexion->commit();
            return $fichaId;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("[FichaModelo] Error en crear: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de una ficha existente y registra quién realizó el cambio (id_owner).
     */
    public function actualizar(int $id, array $datos, int $idOwner): bool {
        try {
            $this->conexion->beginTransaction();

            $solicitanteId = $this->guardarSolicitante($datos);
            if (!$solicitanteId) {
                $this->conexion->rollBack();
                return false;
            }

            $query = "UPDATE fichas_emergencia SET
                        parroquia_id    = :parroquia_id,
                        direccion_exacta = :direccion,
                        caso_id          = :caso_id,
                        descripcion_caso = :descripcion,
                        solicitante_id   = :solicitante_id,
                        id_owner         = :id_owner
                      WHERE id = :id";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindValue(':parroquia_id',  $datos['parroquia_id'],    PDO::PARAM_INT);
            $stmt->bindValue(':direccion',      $datos['direccion_exacta'], PDO::PARAM_STR);
            $stmt->bindValue(':caso_id',        $datos['caso_id'],         PDO::PARAM_INT);
            $stmt->bindValue(':descripcion',    $datos['descripcion_caso'], PDO::PARAM_STR);
            $stmt->bindValue(':solicitante_id', $solicitanteId,            PDO::PARAM_INT);
            $stmt->bindValue(':id_owner',       $idOwner,                  PDO::PARAM_INT);
            $stmt->bindValue(':id',             $id,                       PDO::PARAM_INT);
            $stmt->execute();

            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("[FichaModelo] Error en actualizar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gestiona la transición de estados de la ficha.
     * Al Cerrar una ficha requiere obligatoriamente un motivo_cierre.
     * Estados terminales: Atendido (positivo) y Cerrado (con motivo).
     */
    public function cambiarEstado(int $id, string $nuevoEstado, int $idOwner, string $motivoCierre = '', string $tipoMotivo = ''): bool {
        $estadosValidos = ['Pendiente', 'En Proceso', 'Atendido', 'Cerrado'];
        if (!in_array($nuevoEstado, $estadosValidos, true)) return false;

        try {
            // Solo se registra hora_cierre y motivo al pasar al estado Cerrado
            if ($nuevoEstado === 'Cerrado') {
                $query = "UPDATE fichas_emergencia
                          SET estado_ficha = :estado,
                              hora_cierre  = NOW(),
                              motivo_cierre = :motivo,
                              tipo_motivo_cierre = :tipo_motivo,
                              id_owner     = :id_owner
                          WHERE id = :id";
                $stmt = $this->conexion->prepare($query);
                $stmt->bindValue(':estado',      $nuevoEstado,  PDO::PARAM_STR);
                $stmt->bindValue(':motivo',      $motivoCierre, PDO::PARAM_STR);
                $stmt->bindValue(':tipo_motivo', $tipoMotivo,   PDO::PARAM_STR);
                $stmt->bindValue(':id_owner',    $idOwner,      PDO::PARAM_INT);
                $stmt->bindValue(':id',          $id,           PDO::PARAM_INT);
            } else {
                $query = "UPDATE fichas_emergencia
                          SET estado_ficha = :estado,
                              id_owner     = :id_owner
                          WHERE id = :id";
                $stmt = $this->conexion->prepare($query);
                $stmt->bindValue(':estado',   $nuevoEstado, PDO::PARAM_STR);
                $stmt->bindValue(':id_owner', $idOwner,     PDO::PARAM_INT);
                $stmt->bindValue(':id',       $id,          PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[FichaModelo] Error en cambiarEstado: " . $e->getMessage());
            return false;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 4. GESTIÓN DE SOLICITANTES (UPSERT POR CÉDULA)
    // ///////////////////////////////////////////////////////////////////

    /**
     * Busca o registra a un solicitante basándose en su cédula.
     * Si existe, actualiza su información de contacto.
     */
    private function guardarSolicitante(array $datos): int|false {
        try {
            $cedula = trim($datos['cedula_solicitante'] ?? '');

            if ($cedula !== '') {
                $stmt = $this->conexion->prepare(
                    "SELECT id FROM solicitantes WHERE cedula = :cedula LIMIT 1"
                );
                $stmt->bindValue(':cedula', $cedula, PDO::PARAM_STR);
                $stmt->execute();
                $existente = $stmt->fetchColumn();

                if ($existente) {
                    $update = $this->conexion->prepare(
                        "UPDATE solicitantes SET nombre_solicitante = :nombre, 
                                telefono1 = :tel1, telefono2 = :tel2
                         WHERE id = :id"
                    );
                    $update->bindValue(':nombre', $datos['nombre_solicitante'], PDO::PARAM_STR);
                    $update->bindValue(':tel1',   $datos['telefono1'],          PDO::PARAM_STR);
                    $update->bindValue(':tel2',   $datos['telefono2'] ?? null,  PDO::PARAM_STR);
                    $update->bindValue(':id',     (int)$existente,              PDO::PARAM_INT);
                    $update->execute();
                    return (int)$existente;
                }
            }

            $insert = $this->conexion->prepare(
                "INSERT INTO solicitantes (cedula, nombre_solicitante, telefono1, telefono2)
                 VALUES (:cedula, :nombre, :tel1, :tel2)"
            );
            $insert->bindValue(':cedula', $cedula ?: null, $cedula ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $insert->bindValue(':nombre', $datos['nombre_solicitante'], PDO::PARAM_STR);
            $insert->bindValue(':tel1',   $datos['telefono1'],           PDO::PARAM_STR);
            $insert->bindValue(':tel2',   $datos['telefono2'] ?? null,   PDO::PARAM_STR);
            $insert->execute();
            return (int)$this->conexion->lastInsertId();
        } catch (Exception $e) {
            error_log("[FichaModelo] Error en guardarSolicitante: " . $e->getMessage());
            return false;
        }
    }

    // ///////////////////////////////////////////////////////////////////
    // 5. CATÁLOGOS: EMERGENCIAS Y CASOS
    // ///////////////////////////////////////////////////////////////////

    // --- Tipos de Emergencia ---
    public function obtenerTiposEmergencia(int $estado = 1): array {
        $stmt = $this->conexion->prepare("SELECT id, nombre, descripcion, estado FROM tipos_emergencia WHERE estado = :estado ORDER BY nombre ASC");
        $stmt->execute([':estado' => $estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearTipoEmergencia(string $nombre, string $descripcion = ''): bool {
        $stmt = $this->conexion->prepare("INSERT INTO tipos_emergencia (nombre, descripcion, estado) VALUES (:nombre, :descripcion, 1)");
        $stmt->bindValue(':nombre',      $nombre,      PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function actualizarTipoEmergencia(int $id, string $nombre, string $descripcion = ''): bool {
        $stmt = $this->conexion->prepare("UPDATE tipos_emergencia SET nombre = :nombre, descripcion = :descripcion WHERE id = :id");
        $stmt->bindValue(':nombre',      $nombre,      PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindValue(':id',          $id,          PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toggleEstadoTipoEmergencia(int $id): bool {
        $stmt = $this->conexion->prepare("UPDATE tipos_emergencia SET estado = 1 - estado WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // --- Casos ---
    public function obtenerCasos(?int $tipoId = null, int $estado = 1): array {
        $sql = "SELECT c.id, c.nombre_caso, c.descripcion, c.tipo_emergencia_id, t.nombre AS tipo_emergencia, c.estado
                FROM casos c INNER JOIN tipos_emergencia t ON c.tipo_emergencia_id = t.id
                WHERE c.estado = :estado ";
        if ($tipoId) $sql .= " AND c.tipo_emergencia_id = :tipo_id ";
        $sql .= " ORDER BY t.nombre ASC, c.nombre_caso ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':estado', $estado, PDO::PARAM_INT);
        if ($tipoId) $stmt->bindValue(':tipo_id', $tipoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearCaso(int $tipoId, string $nombre, string $descripcion): bool {
        $stmt = $this->conexion->prepare(
            "INSERT INTO casos (tipo_emergencia_id, nombre_caso, descripcion, estado) VALUES (:tipo_id, :nombre, :descripcion, 1)"
        );
        $stmt->bindValue(':tipo_id',     $tipoId,      PDO::PARAM_INT);
        $stmt->bindValue(':nombre',      $nombre,      PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function actualizarCaso(int $id, int $tipoId, string $nombre, string $descripcion): bool {
        $stmt = $this->conexion->prepare(
            "UPDATE casos SET tipo_emergencia_id = :tipo_id, nombre_caso = :nombre, descripcion = :descripcion WHERE id = :id"
        );
        $stmt->bindValue(':tipo_id',     $tipoId,      PDO::PARAM_INT);
        $stmt->bindValue(':nombre',      $nombre,      PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindValue(':id',          $id,          PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toggleEstadoCaso(int $id): bool {
        $stmt = $this->conexion->prepare("UPDATE casos SET estado = 1 - estado WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ///////////////////////////////////////////////////////////////////
    // 6. CATÁLOGOS: GEOGRAFÍA (MUNICIPIOS / PARROQUIAS)
    // ///////////////////////////////////////////////////////////////////

    // --- Municipios ---
    public function obtenerMunicipios(int $estado = 1): array {
        $stmt = $this->conexion->prepare("SELECT id, nombre_municipio, descripcion, estado FROM municipios WHERE estado = :estado ORDER BY nombre_municipio ASC");
        $stmt->execute([':estado' => $estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearMunicipio(string $nombre, string $descripcion = ''): bool {
        $stmt = $this->conexion->prepare("INSERT INTO municipios (nombre_municipio, descripcion, estado) VALUES (:nombre, :descripcion, 1)");
        $stmt->bindValue(':nombre',      $nombre,      PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function actualizarMunicipio(int $id, string $nombre, string $descripcion = ''): bool {
        $stmt = $this->conexion->prepare("UPDATE municipios SET nombre_municipio = :nombre, descripcion = :descripcion WHERE id = :id");
        $stmt->bindValue(':nombre',      $nombre,      PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindValue(':id',          $id,          PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toggleEstadoMunicipio(int $id): bool {
        $stmt = $this->conexion->prepare("UPDATE municipios SET estado = 1 - estado WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // --- Parroquias ---
    public function obtenerParroquias(?int $municipioId = null, int $estado = 1): array {
        $sql = "SELECT p.id, p.nombre_parroquia, p.descripcion, p.municipio_id, m.nombre_municipio, p.estado
                FROM parroquias p INNER JOIN municipios m ON p.municipio_id = m.id
                WHERE p.estado = :estado ";
        if ($municipioId) $sql .= " AND p.municipio_id = :municipio_id ";
        $sql .= " ORDER BY m.nombre_municipio ASC, p.nombre_parroquia ASC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':estado', $estado, PDO::PARAM_INT);
        if ($municipioId) $stmt->bindValue(':municipio_id', $municipioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearParroquia(int $municipioId, string $nombre, string $descripcion = ''): bool {
        $stmt = $this->conexion->prepare(
            "INSERT INTO parroquias (municipio_id, nombre_parroquia, descripcion, estado) VALUES (:municipio_id, :nombre, :descripcion, 1)"
        );
        $stmt->bindValue(':municipio_id', $municipioId, PDO::PARAM_INT);
        $stmt->bindValue(':nombre',       $nombre,       PDO::PARAM_STR);
        $stmt->bindValue(':descripcion',  $descripcion,  PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function actualizarParroquia(int $id, int $municipioId, string $nombre, string $descripcion = ''): bool {
        $stmt = $this->conexion->prepare(
            "UPDATE parroquias SET municipio_id = :municipio_id, nombre_parroquia = :nombre, descripcion = :descripcion WHERE id = :id"
        );
        $stmt->bindValue(':municipio_id', $municipioId, PDO::PARAM_INT);
        $stmt->bindValue(':nombre',       $nombre,       PDO::PARAM_STR);
        $stmt->bindValue(':descripcion',  $descripcion,  PDO::PARAM_STR);
        $stmt->bindValue(':id',           $id,           PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toggleEstadoParroquia(int $id): bool {
        $stmt = $this->conexion->prepare("UPDATE parroquias SET estado = 1 - estado WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ///////////////////////////////////////////////////////////////////
    // 7. CATÁLOGOS: ORGANISMOS
    // ///////////////////////////////////////////////////////////////////

    /**
     * Valida si un nombre ya existe en un catálogo específico para evitar duplicados.
     */
    private function existeNombreCatalogo(string $tabla, string $columna, string $nombre, ?int $idActual = null): bool {
        $sql = "SELECT COUNT(*) FROM {$tabla} WHERE {$columna} = :nombre AND estado = 1";
        if ($idActual) $sql .= " AND id != :id";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        if ($idActual) $stmt->bindValue(':id', $idActual, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    public function obtenerOrganismos(int $estado = 1): array {
        $stmt = $this->conexion->prepare("SELECT id, nombre_organismo, descripcion, estado FROM organismos WHERE estado = :estado ORDER BY nombre_organismo ASC");
        $stmt->execute([':estado' => $estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearOrganismo(string $nombre, string $descripcion = ''): bool {
        if ($this->existeNombreCatalogo('organismos', 'nombre_organismo', $nombre)) {
            throw new Exception("El organismo '{$nombre}' ya está registrado y activo.");
        }
        $stmt = $this->conexion->prepare("INSERT INTO organismos (nombre_organismo, descripcion, estado) VALUES (:nombre, :descripcion, 1)");
        $stmt->bindValue(':nombre',      $nombre,      PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function actualizarOrganismo(int $id, string $nombre, string $descripcion = ''): bool {
        if ($this->existeNombreCatalogo('organismos', 'nombre_organismo', $nombre, $id)) {
            throw new Exception("Ya existe otro organismo activo con el nombre '{$nombre}'.");
        }
        $stmt = $this->conexion->prepare("UPDATE organismos SET nombre_organismo = :nombre, descripcion = :descripcion WHERE id = :id");
        $stmt->bindValue(':nombre',      $nombre,      PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindValue(':id',          $id,          PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toggleEstadoOrganismo(int $id): bool {
        $stmt = $this->conexion->prepare("UPDATE organismos SET estado = 1 - estado WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ///////////////////////////////////////////////////////////////////
    // 8. CATÁLOGOS: MOTIVOS DE CIERRE
    // ///////////////////////////////////////////////////////////////////

    public function obtenerMotivosCierre(int $estado = 1): array {
        $stmt = $this->conexion->prepare("SELECT id, nombre, descripcion, estado FROM motivos_cierre WHERE estado = :estado ORDER BY nombre ASC");
        $stmt->execute([':estado' => $estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearMotivoCierre(string $nombre, string $descripcion = ''): bool {
        if ($this->existeNombreCatalogo('motivos_cierre', 'nombre', $nombre)) {
            throw new \Exception("El motivo '{$nombre}' ya está registrado y activo.");
        }
        $stmt = $this->conexion->prepare("INSERT INTO motivos_cierre (nombre, descripcion, estado) VALUES (:nombre, :descripcion, 1)");
        $stmt->bindValue(':nombre',      $nombre,      PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function actualizarMotivoCierre(int $id, string $nombre, string $descripcion = ''): bool {
        if ($this->existeNombreCatalogo('motivos_cierre', 'nombre', $nombre, $id)) {
            throw new \Exception("Ya existe otro motivo activo con el nombre '{$nombre}'.");
        }
        $stmt = $this->conexion->prepare("UPDATE motivos_cierre SET nombre = :nombre, descripcion = :descripcion WHERE id = :id");
        $stmt->bindValue(':nombre',      $nombre,      PDO::PARAM_STR);
        $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindValue(':id',          $id,          PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toggleEstadoMotivoCierre(int $id): bool {
        $stmt = $this->conexion->prepare("UPDATE motivos_cierre SET estado = 1 - estado WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
