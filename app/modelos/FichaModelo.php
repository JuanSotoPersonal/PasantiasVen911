<?php

namespace App\modelos;

use App\Config\Database;
use PDO;
use Exception;

require_once 'app/Config/Database.php';

class FichaModelo {

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ================================================================
    // FICHAS — Paginación Server-Side
    // ================================================================

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

            // Filtro por estado y por visibilidad de rol
            $condiciones = ['1=1'];
            if ($estado !== 'todos') {
                $condiciones[] = 'f.estado_ficha = :estado';
            }
            // Operador solo ve sus propias fichas
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

            $stmt = $this->conn->prepare($query);
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

            $stmt = $this->conn->prepare(
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
            $stmt = $this->conn->prepare($query);
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

    // ================================================================
    // FICHAS — CRUD
    // ================================================================

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
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[FichaModelo] Error en obtenerPorId: " . $e->getMessage());
            return false;
        }
    }

    public function crear(array $datos): int|false {
        try {
            $this->conn->beginTransaction();

            // 1. Upsert del solicitante por cédula
            $solicitanteId = $this->guardarSolicitante($datos);
            if (!$solicitanteId) {
                $this->conn->rollBack();
                return false;
            }

            // 2. Insertar la ficha
            $query = "INSERT INTO fichas_emergencia
                        (parroquia_id, direccion_exacta, caso_id, descripcion_caso, solicitante_id, id_user, estado_ficha)
                      VALUES
                        (:parroquia_id, :direccion, :caso_id, :descripcion, :solicitante_id, :id_user, 'Pendiente')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':parroquia_id',   $datos['parroquia_id'],   PDO::PARAM_INT);
            $stmt->bindValue(':direccion',       $datos['direccion_exacta'], PDO::PARAM_STR);
            $stmt->bindValue(':caso_id',         $datos['caso_id'],        PDO::PARAM_INT);
            $stmt->bindValue(':descripcion',     $datos['descripcion_caso'], PDO::PARAM_STR);
            $stmt->bindValue(':solicitante_id',  $solicitanteId,           PDO::PARAM_INT);
            $stmt->bindValue(':id_user',         $datos['id_user'],        PDO::PARAM_INT);
            $stmt->execute();

            $fichaId = (int)$this->conn->lastInsertId();
            $this->conn->commit();
            return $fichaId;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("[FichaModelo] Error en crear: " . $e->getMessage());
            return false;
        }
    }

    public function actualizar(int $id, array $datos, int $idOwner): bool {
        try {
            $this->conn->beginTransaction();

            $solicitanteId = $this->guardarSolicitante($datos);
            if (!$solicitanteId) {
                $this->conn->rollBack();
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
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':parroquia_id',  $datos['parroquia_id'],    PDO::PARAM_INT);
            $stmt->bindValue(':direccion',      $datos['direccion_exacta'], PDO::PARAM_STR);
            $stmt->bindValue(':caso_id',        $datos['caso_id'],         PDO::PARAM_INT);
            $stmt->bindValue(':descripcion',    $datos['descripcion_caso'], PDO::PARAM_STR);
            $stmt->bindValue(':solicitante_id', $solicitanteId,            PDO::PARAM_INT);
            $stmt->bindValue(':id_owner',       $idOwner,                  PDO::PARAM_INT);
            $stmt->bindValue(':id',             $id,                       PDO::PARAM_INT);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("[FichaModelo] Error en actualizar: " . $e->getMessage());
            return false;
        }
    }

    public function cambiarEstado(int $id, string $nuevoEstado, int $idOwner): bool {
        $estadosValidos = ['Pendiente', 'En Proceso', 'Atendido', 'Cerrado', 'Finalizado'];
        if (!in_array($nuevoEstado, $estadosValidos, true)) return false;

        try {
            $horaCierre = in_array($nuevoEstado, ['Cerrado', 'Finalizado']) ? 'hora_cierre = NOW(),' : '';
            $query = "UPDATE fichas_emergencia
                      SET estado_ficha = :estado, {$horaCierre} id_owner = :id_owner
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':estado',   $nuevoEstado, PDO::PARAM_STR);
            $stmt->bindValue(':id_owner', $idOwner,     PDO::PARAM_INT);
            $stmt->bindValue(':id',       $id,          PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("[FichaModelo] Error en cambiarEstado: " . $e->getMessage());
            return false;
        }
    }

    // ================================================================
    // SOLICITANTES — Upsert por cédula
    // ================================================================

    private function guardarSolicitante(array $datos): int|false {
        try {
            $cedula = trim($datos['cedula_solicitante'] ?? '');

            if ($cedula !== '') {
                // Buscar si ya existe por cédula
                $stmt = $this->conn->prepare(
                    "SELECT id FROM solicitantes WHERE cedula = :cedula LIMIT 1"
                );
                $stmt->bindValue(':cedula', $cedula, PDO::PARAM_STR);
                $stmt->execute();
                $existente = $stmt->fetchColumn();

                if ($existente) {
                    // Actualizar datos
                    $update = $this->conn->prepare(
                        "UPDATE solicitantes SET nombre_solicitante = :nombre, telefono1 = :tel1, telefono2 = :tel2
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

            // Insertar nuevo solicitante
            $insert = $this->conn->prepare(
                "INSERT INTO solicitantes (cedula, nombre_solicitante, telefono1, telefono2)
                 VALUES (:cedula, :nombre, :tel1, :tel2)"
            );
            $insert->bindValue(':cedula', $cedula ?: null, $cedula ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $insert->bindValue(':nombre', $datos['nombre_solicitante'], PDO::PARAM_STR);
            $insert->bindValue(':tel1',   $datos['telefono1'],           PDO::PARAM_STR);
            $insert->bindValue(':tel2',   $datos['telefono2'] ?? null,   PDO::PARAM_STR);
            $insert->execute();
            return (int)$this->conn->lastInsertId();
        } catch (Exception $e) {
            error_log("[FichaModelo] Error en guardarSolicitante: " . $e->getMessage());
            return false;
        }
    }

    // ================================================================
    // CATÁLOGOS EMBEBIDOS (solo Admin)
    // ================================================================

    // --- Tipos de Emergencia ---
    public function obtenerTiposEmergencia(): array {
        $stmt = $this->conn->query("SELECT id, nombre FROM tipos_emergencia ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearTipoEmergencia(string $nombre): bool {
        $stmt = $this->conn->prepare("INSERT INTO tipos_emergencia (nombre) VALUES (:nombre)");
        return $stmt->execute([':nombre' => $nombre]);
    }

    public function actualizarTipoEmergencia(int $id, string $nombre): bool {
        $stmt = $this->conn->prepare("UPDATE tipos_emergencia SET nombre = :nombre WHERE id = :id");
        return $stmt->execute([':nombre' => $nombre, ':id' => $id]);
    }

    public function eliminarTipoEmergencia(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM tipos_emergencia WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // --- Casos ---
    public function obtenerCasos(?int $tipoId = null): array {
        if ($tipoId) {
            $stmt = $this->conn->prepare(
                "SELECT c.id, c.nombre_caso, c.descripcion, c.tipo_emergencia_id, t.nombre AS tipo_emergencia
                 FROM casos c INNER JOIN tipos_emergencia t ON c.tipo_emergencia_id = t.id
                 WHERE c.tipo_emergencia_id = :tipo_id ORDER BY c.nombre_caso ASC"
            );
            $stmt->execute([':tipo_id' => $tipoId]);
        } else {
            $stmt = $this->conn->query(
                "SELECT c.id, c.nombre_caso, c.descripcion, c.tipo_emergencia_id, t.nombre AS tipo_emergencia
                 FROM casos c INNER JOIN tipos_emergencia t ON c.tipo_emergencia_id = t.id
                 ORDER BY t.nombre ASC, c.nombre_caso ASC"
            );
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearCaso(int $tipoId, string $nombre, string $descripcion): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO casos (tipo_emergencia_id, nombre_caso, descripcion) VALUES (:tipo_id, :nombre, :descripcion)"
        );
        return $stmt->execute([':tipo_id' => $tipoId, ':nombre' => $nombre, ':descripcion' => $descripcion]);
    }

    public function actualizarCaso(int $id, int $tipoId, string $nombre, string $descripcion): bool {
        $stmt = $this->conn->prepare(
            "UPDATE casos SET tipo_emergencia_id = :tipo_id, nombre_caso = :nombre, descripcion = :descripcion WHERE id = :id"
        );
        return $stmt->execute([':tipo_id' => $tipoId, ':nombre' => $nombre, ':descripcion' => $descripcion, ':id' => $id]);
    }

    public function eliminarCaso(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM casos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // --- Municipios ---
    public function obtenerMunicipios(): array {
        $stmt = $this->conn->query("SELECT id, nombre_municipio FROM municipios ORDER BY nombre_municipio ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearMunicipio(string $nombre): bool {
        $stmt = $this->conn->prepare("INSERT INTO municipios (nombre_municipio) VALUES (:nombre)");
        return $stmt->execute([':nombre' => $nombre]);
    }

    public function actualizarMunicipio(int $id, string $nombre): bool {
        $stmt = $this->conn->prepare("UPDATE municipios SET nombre_municipio = :nombre WHERE id = :id");
        return $stmt->execute([':nombre' => $nombre, ':id' => $id]);
    }

    public function eliminarMunicipio(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM municipios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // --- Parroquias ---
    public function obtenerParroquias(?int $municipioId = null): array {
        if ($municipioId) {
            $stmt = $this->conn->prepare(
                "SELECT p.id, p.nombre_parroquia, p.municipio_id, m.nombre_municipio
                 FROM parroquias p INNER JOIN municipios m ON p.municipio_id = m.id
                 WHERE p.municipio_id = :municipio_id ORDER BY p.nombre_parroquia ASC"
            );
            $stmt->execute([':municipio_id' => $municipioId]);
        } else {
            $stmt = $this->conn->query(
                "SELECT p.id, p.nombre_parroquia, p.municipio_id, m.nombre_municipio
                 FROM parroquias p INNER JOIN municipios m ON p.municipio_id = m.id
                 ORDER BY m.nombre_municipio ASC, p.nombre_parroquia ASC"
            );
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearParroquia(int $municipioId, string $nombre): bool {
        $stmt = $this->conn->prepare(
            "INSERT INTO parroquias (municipio_id, nombre_parroquia) VALUES (:municipio_id, :nombre)"
        );
        return $stmt->execute([':municipio_id' => $municipioId, ':nombre' => $nombre]);
    }

    public function actualizarParroquia(int $id, int $municipioId, string $nombre): bool {
        $stmt = $this->conn->prepare(
            "UPDATE parroquias SET municipio_id = :municipio_id, nombre_parroquia = :nombre WHERE id = :id"
        );
        return $stmt->execute([':municipio_id' => $municipioId, ':nombre' => $nombre, ':id' => $id]);
    }

    public function eliminarParroquia(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM parroquias WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
